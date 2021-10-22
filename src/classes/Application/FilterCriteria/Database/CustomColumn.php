<?php
/**
 * File containing the class {@see Application_FilterCriteria_Database_CustomColumn}.
 *
 * @package Application
 * @subpackage FilterCriteria
 * @see Application_FilterCriteria_Database_CustomColumn
 */

declare(strict_types=1);

use AppUtils\NamedClosure;

/**
 * Container for the configuration of individual custom columns
 * to use in a database filter criteria class.
 *
 * @package Application
 * @subpackage FilterCriteria
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_FilterCriteria_Database::registerCustomColumn()
 */
class Application_FilterCriteria_Database_CustomColumn
{
    const ERROR_SELECT_STATEMENT_NOT_A_STRING = 90401;

    const COMPONENT_VALUE = 'value';
    const COMPONENT_SELECT = 'select';

    /**
     * @var NamedClosure|DBHelper_StatementBuilder
     */
    private $source;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Application_FilterCriteria_DatabaseExtended
     */
    private $filters;

    /**
     * @var bool
     */
    private $enabled = false;

    /**
     * @var string[]
     */
    private $requiredJoinIDs = array();

    /**
     * @var array<string,mixed>
     */
    private $placeholders = array();

    /**
     * @var string
     */
    private $statement = '';

    /**
     * @param Application_FilterCriteria_DatabaseExtended $filters
     * @param string $name
     * @param NamedClosure|DBHelper_StatementBuilder $source
     */
    public function __construct(Application_FilterCriteria_DatabaseExtended $filters, string $name, $source)
    {
        $this->filters = $filters;
        $this->name = $name;
        $this->source = $source;
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    public function isEnabled() : bool
    {
        return $this->enabled;
    }

    /**
     * Shorthand for registering the `JOIN` statement
     * and requiring it for the column.
     *
     * @param string $joinID
     * @param string|DBHelper_StatementBuilder $statement
     * @return $this
     * @throws DBHelper_Exception
     */
    public function addJoin(string $joinID, $statement) : Application_FilterCriteria_Database_CustomColumn
    {
        $join = $this->filters->registerJoin($joinID, $statement);

        return $this->requireJoin($join->getID());
    }

    /**
     * Marks the column as requiring a specific `JOIN` statement
     * by its ID (as registered using {@see Application_FilterCriteria_Database::registerJoin()}).
     *
     * @return $this
     */
    public function requireJoin(string $joinID) : Application_FilterCriteria_Database_CustomColumn
    {
        $this->requiredJoinIDs[] = $joinID;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getRequiredJoinIDs() : array
    {
        return $this->requiredJoinIDs;
    }

    public function hasJoins() : bool
    {
        return !empty($this->requiredJoinIDs);
    }

    /**
     * Whether the column should be enabled in the filter
     * criteria, and added to the query.
     *
     * NOTE: In most cases, this is done automatically as
     * soon as the column is used in the query. It can be
     * used manually to force the column to be present.
     *
     * @param bool $enabled
     * @return $this
     */
    public function setEnabled(bool $enabled) : Application_FilterCriteria_Database_CustomColumn
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * Retrieves the SQL statement required to access the column's value,
     * without appended `AS` statement.
     *
     * @return string
     * @throws Application_Exception
     * @see Application_FilterCriteria_Database_CustomColumn::getSelect()
     */
    public function getSQLStatement() : string
    {
        if(empty($this->statement))
        {
            $this->statement = $this->renderStatement();
        }

        return $this->statement;
    }

    private function renderStatement() : string
    {
        if($this->source instanceof DBHelper_StatementBuilder)
        {
            return (string)$this->source;
        }

        $result = call_user_func($this->source, $this);

        if(is_string($result))
        {
            return $result;
        }

        throw new Application_Exception(
            'Invalid custom column select value',
            sprintf(
                'The callback for the custom column [%s] did not return a string, but [%s].',
                $this->name,
                gettype($result)
            ),
            self::ERROR_SELECT_STATEMENT_NOT_A_STRING
        );
    }

    /**
     * Gets the statement to use in the query's `SELECT` part:
     * includes the alias `column AS alias` where `alias` is the
     * column's name.
     *
     * NOTE: Use {@see Application_FilterCriteria_Database_CustomColumn::getSQLStatement()}
     * for the SQL to access the column's value.
     *
     * @return string
     *
     * @see Application_FilterCriteria_Database_CustomColumn::getSQLStatement()
     * @see Application_FilterCriteria_Database_CustomColumn::ERROR_SELECT_STATEMENT_NOT_A_STRING
     */
    public function getSelect() : string
    {
        return $this->generateMarker(self::COMPONENT_SELECT);
    }

    public function getUsage() : Application_FilterCriteria_Database_ColumnUsage
    {
        return $this->filters->checkColumnUsage($this);
    }

    public function isSubQuery() : bool
    {
        return strstr($this->getSQLStatement(), 'SELECT') !== false;
    }

    /**
     * Retrieves the statement required to access the
     * value of the column. This is typically the alias
     * used in the SELECT statement, if available. Otherwise,
     * and if the column is a sub-query, this will be
     * the full statement.
     *
     * @return string
     */
    public function getValueStatement() : string
    {
        return $this->generateMarker(self::COMPONENT_VALUE);
    }

    public function getGroupBy() : string
    {
        return $this->getValueStatement();
    }

    public function getOrderBy() : string
    {
        return $this->getValueStatement();
    }

    public function getWhere() : string
    {
        return $this->getValueStatement();
    }

    public function getSelectMarker() : string
    {
        return $this->generateMarker(self::COMPONENT_SELECT);
    }

    public function getValueMarker() : string
    {
        return $this->generateMarker(self::COMPONENT_VALUE);
    }

    private function generateMarker(string $component='') : string
    {
        $name = '$$'.$this->getName();

        if(!empty($component))
        {
            return $name.'_'.$component.'$$';
        }

        return $name.'$$';
    }

    /**
     * Retrieves the alias under which the column
     * is saved in the query's select statement.
     * Already includes the backtick quotes.
     *
     * @return string
     */
    public function getSelectAlias() : string
    {
        return '`'.$this->name.'`';
    }

    /**
     * @param array<string,mixed> $placeholders
     * @return $this
     */
    public function addCustomPlaceholders(array $placeholders)
    {
        foreach ($placeholders as $name => $value)
        {
            $this->addCustomPlaceholder($name, $value);
        }

        return $this;
    }

    /**
     * Adds a placeholder and its value, which is tied
     * to this column. If the column is used in a query,
     * these values will automatically be added to the
     * query values.
     *
     * NOTE: Must be a placeholder name, for example added
     * via {@see Application_FilterCriteria_Database::generatePlaceholder()}.
     *
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function addCustomPlaceholder(string $name, $value)
    {
        $this->placeholders[$name] = $value;
        return $this;
    }

    /**
     * @return array<string,mixed>
     */
    public function getPlaceholders() : array
    {
        return $this->placeholders;
    }

    public function hasPlaceholders() : bool
    {
        return !empty($this->placeholders);
    }

    /**
     * Whether the target string contains any placeholders
     * of the custom column.
     *
     * @param string $subject
     * @return bool
     */
    public function isFoundInString(string $subject) : bool
    {
        return
            strstr($subject, $this->getValueMarker()) !== false
            ||
            strstr($subject, $this->getSelectMarker()) !== false;
    }

    public function replacePlaceholders(string $query) : string
    {
        if(!$this->isFoundInString($query))
        {
            return $query;
        }

        $replaces = array(
            $this->getValueMarker() => $this->renderValue()
        );

        if($this->getUsage()->isInSelect())
        {
            $replaces[$this->getSelectMarker()] = $this->renderSelect();
        }

        return str_replace(array_keys($replaces), array_values($replaces), $query);
    }

    public function canUseAlias() : bool
    {
        return $this->getUsage()->isInSelect() && !$this->isSubQuery();
    }

    private function renderSelect() : string
    {
        return $this->getSQLStatement().' AS '.$this->getSelectAlias();
    }

    private function renderValue() : string
    {
        if($this->canUseAlias())
        {
            return $this->getSelectAlias();
        }

        return $this->getSQLStatement();
    }
}
