<?php
/**
 * @package Application
 * @subpackage Lookup Items
 */

declare(strict_types=1);

namespace Application\LookupItems;

use Application_LookupItems_Result;
use AppUtils\Interfaces\StringableInterface;
use DBHelper;
use UI\AdminURLs\AdminURLInterface;
use UI_Exception;

/**
 * Base abstract class for a data type in the item lookup dialog.
 *
 * @package Application
 * @subpackage Lookup Items
 */
abstract class BaseLookupItem
{
    abstract public function getFieldLabel() : string;

    /**
     * @return string
     */
    abstract public function getFieldDescription() : string;

    protected string $id;

    /**
     * @var Application_LookupItems_Result[]
     */
    protected array $results = array();

    public function getID() : string
    {
        return getClassTypeName($this);
    }

    /**
     * @return array<string,string>
     */
    public function toArray() : array
    {
        return array(
            'id' => $this->getID(),
            'field_label' => $this->getFieldLabel(),
            'field_description' => $this->getFieldDescription()
        );
    }

    /**
     * @param string|number|StringableInterface $label
     * @param string|AdminURLInterface $url
     * @return void
     * @throws UI_Exception
     */
    protected function addResult($label, $url) : void
    {
        $result = new Application_LookupItems_Result($this, $label, $url);
        $this->results[] = $result;
    }

    /**
     * @return Application_LookupItems_Result[]
     */
    public function getResults() : array
    {
        return $this->results;
    }

    /**
     * Resets the instance state, clearing accumulated results and
     * custom WHERE constraints. Call this to safely reuse the same
     * instance for multiple independent queries.
     *
     * @return $this
     */
    public function reset() : self
    {
        $this->results = array();
        $this->where = array();
        return $this;
    }

    /**
     * Retrieves a javascript statement to open the lookup
     * dialog for this item, with optional preset search
     * terms.
     *
     * The value must be a JS statement that evaluates to
     * a search terms string. Examples:
     *
     * <pre>
     * getJSSearch("'search terms'");
     * getJSSearch("$('#elementID').val()");
     * </pre>
     *
     * @param string $valueStatement
     * @return string
     */
    public function getJSSearch(string $valueStatement) : string
    {
        return sprintf(
            "Driver.DialogLookup('%s:' + %s);",
            $this->getID(),
            $valueStatement
        );
    }

    /**
     * @return string[]
     */
    abstract protected function getSearchColumns() : array;
    abstract protected function idExists(int $id) : bool;
    abstract protected function getByID(int $id) : object;
    abstract protected function renderLabel(object $record) : string;
    abstract protected function getURL(object $record) : string;
    abstract protected function getQuerySQL() : string;
    abstract protected function getPrimaryName() : string;

    /**
     * Finds IDs of matching records without rendering UI labels or URLs.
     * Intended for use by API method classes that need the lookup system's
     * fast single-table queries as a performance alternative to filter criteria.
     *
     * If {@see self::setLimit()} has been called, the result is capped accordingly:
     * the SQL LIMIT is applied per-query (performance optimization), and the global
     * cap is enforced via array_slice() after deduplication.
     *
     * If {@see self::addWhere()} has been called, the constraint is applied to
     * every SQL query.
     *
     * @param array<int,string|int> $terms
     * @return int[]
     */
    public function findMatchingIDs(array $terms) : array
    {
        $ids = array();

        foreach($terms as $name)
        {
            if(is_numeric($name) && $this->idExists((int)$name))
            {
                $ids[] = (int)$name;
            }
            else
            {
                array_push($ids, ...$this->findMatchesBySearch((string)$name));
            }
        }

        $ids = array_unique($ids);

        if($this->limit > 0)
        {
            $ids = array_slice($ids, 0, $this->limit);
        }

        return array_values($ids);
    }

    public function findMatches($terms): void
    {
        $ids = $this->findMatchingIDs($terms);

        foreach($ids as $id)
        {
            $record = $this->getByID($id);

            $this->addResult(
                $this->renderLabel($record),
                $this->getURL($record)
            );
        }
    }

    /**
     * @var string[]
     */
    private array $where = array();

    private int $limit = 0;

    /**
     * Caps the number of IDs returned by {@see self::findMatchingIDs()}.
     * When greater than zero, the SQL query per search term is also
     * limited (per-query performance optimization), and the global
     * result count is capped after deduplication.
     *
     * A value of 0 means no limit (default).
     *
     * @param int $limit
     * @return $this
     */
    public function setLimit(int $limit) : self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Adds a custom WHERE statement to the query.
     * If multiple statements are added, they are joined
     * with AND.
     *
     * @param string $statement
     * @return $this
     */
    public function addWhere(string $statement) : self
    {
        $this->where[] = $statement;
        return $this;
    }

    private function findMatchesBySearch(string $name) : array
    {
        $split = self::splitSearchTerm($name, $this->getSearchColumns());

        // Build the WHERE clause locally: combine persistent constraints with the
        // per-term clause without mutating $this->where. This ensures that a second
        // call for a different term does not inherit the previous term's WHERE clause.
        $whereParts = $this->where;
        $whereParts[] = $split['where'];

        $query = str_replace(
            '{WHERE}',
            implode(' AND ', $whereParts),
            $this->getQuerySQL()
        );

        if($this->limit > 0)
        {
            $query .= ' LIMIT '.$this->limit;
        }

        return DBHelper::fetchAllKeyInt(
            $this->getPrimaryName(),
            $query,
            $split['variables']
        );
    }

    /**
     * @param string $name
     * @param string[] $searchFields
     * @return array{variables:array<string,string>, where:string}
     */
    public static function splitSearchTerm(string $name, array $searchFields) : array
    {
        $parts = explode(' ', $name);

        $variables = array();
        $likes = array();
        foreach($parts as $idx => $part) {
            $name = 'part'.($idx+1);
            $variables[$name] = '%'.$part.'%';
            $likes[] = 'LIKE :'.$name;
        }

        $wheres = array();
        foreach($searchFields as $field)
        {
            $fieldWheres = array();
            foreach($likes as $like) {
                $fieldWheres[] = sprintf(
                    '%s %s',
                    $field,
                    $like
                );
            }

            $wheres[] = sprintf(
                '(%s)'.PHP_EOL,
                implode(' AND ', $fieldWheres)
            );
        }

        return array(
            'variables' => $variables,
            'where' => "(".implode(' OR '.PHP_EOL, $wheres).")"
        );
    }
}
