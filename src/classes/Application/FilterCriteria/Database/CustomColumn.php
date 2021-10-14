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

    /**
     * @var NamedClosure|DBHelper_StatementBuilder
     */
    private $source;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Application_FilterCriteria_Database
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
     * @param Application_FilterCriteria_Database $filters
     * @param string $name
     * @param NamedClosure|DBHelper_StatementBuilder $source
     */
    public function __construct(Application_FilterCriteria_Database $filters, string $name, $source)
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
     * Retrieves the SQL statement required to access the column's value.
     *
     * NOTE: This can be simply a field name, but also a full sub-query.
     * To use this statement in a `SELECT`, use the method
     * {@see Application_FilterCriteria_Database_CustomColumn::getSelect()}
     * instead.
     *
     * @return string
     * @throws Application_Exception
     * @see Application_FilterCriteria_Database_CustomColumn::getSelect()
     */
    public function getStatement() : string
    {
        if($this->source instanceof DBHelper_StatementBuilder)
        {
            return (string)$this->source;
        }

        $result = call_user_func($this->source);

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
     * NOTE: Use {@see Application_FilterCriteria_Database_CustomColumn::getStatement()}
     * for the SQL to access the column's value.
     *
     * @return string
     * @throws Application_Exception
     *
     * @see Application_FilterCriteria_Database_CustomColumn::getStatement()
     * @see Application_FilterCriteria_Database_CustomColumn::ERROR_SELECT_STATEMENT_NOT_A_STRING
     */
    public function getSelect() : string
    {
        return $this->getStatement().' AS `'.$this->name.'`';
    }
}
