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

    public function findMatches($terms): void
    {
        $ids = array();

        foreach($terms as $name)
        {
            if(is_numeric($name) && $this->idExists((int)$name))
            {
                $ids = array($name);
            }
            else
            {
                array_push($ids, ...$this->findMatchesBySearch($name));
            }
        }

        $ids = array_unique($ids);

        if(empty($ids)) {
            return;
        }

        foreach($ids as $id)
        {
            $record = $this->getByID((int)$id);

            $this->addResult(
                $this->renderLabel($record),
                $this->getURL($record)
            );
        }
    }

    private function findMatchesBySearch(string $name) : array
    {
        $split = self::splitSearchTerm($name, $this->getSearchColumns());

        $query = str_replace(
            '{WHERE}',
            $split['where'],
            $this->getQuerySQL()
        );

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
