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
 * @see Application_FilterCriteria_DatabaseExtended::registerCustomColumn()
 */
class Application_FilterCriteria_Database_CustomColumn
{
    public const ERROR_SELECT_STATEMENT_NOT_A_STRING = 90401;

    /**
     * @var NamedClosure|NULL
     */
    private $closure = null;

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

        if($source instanceof DBHelper_StatementBuilder)
        {
            $this->statement = (string)$source;
        }
        else
        {
            $this->closure = $source;
        }
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

    public function hasRequiredJoins() : bool
    {
        return !empty($this->requiredJoinIDs);
    }

    /**
     * @var string[]
     */
    private $groupBys = array();

    public function requireGroupBy($groupBy) : Application_FilterCriteria_Database_CustomColumn
    {
        $statement = (string)$groupBy;

        if(!in_array($statement, $this->groupBys))
        {
            $this->groupBys[] = $statement;
        }

        return $this;
    }

    /**
     * Whether the column should be enabled in the filter
     * criteria, and added to the query's SELECT statement.
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
        if($enabled !== $this->enabled)
        {
            $this->enabled = $enabled;
            $this->filters->handleColumnModified($this);
        }

        return $this;
    }

    /**
     * Retrieves the SQL statement required to access the column's value,
     * without the appended `AS` statement.
     *
     * @return string
     * @throws Application_Exception
     * @see Application_FilterCriteria_Database_CustomColumn::getPrimarySelectValue()
     */
    public function getSQLStatement() : string
    {
        if(empty($this->statement))
        {
            $this->statement = $this->renderStatement();
        }

        return $this->statement;
    }

    /**
     * @return string
     * @throws Application_Exception
     */
    private function renderStatement() : string
    {
        if(!isset($this->closure))
        {
            return $this->statement;
        }

        $result = call_user_func($this->closure, $this);

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

    public function getUsage() : Application_FilterCriteria_Database_ColumnUsage
    {
        return $this->filters->checkColumnUsage($this);
    }

    public function isComplexQuery() : bool
    {
        return $this->isSubQuery() || $this->isCaseQuery();
    }

    public function isCaseQuery() : bool
    {
        return strpos($this->getSQLStatement(), 'CASE') !== false;
    }

    public function isSubQuery() : bool
    {
        return strpos($this->getSQLStatement(), 'SELECT') !== false;
    }

    // region: Getting flavored markers

    /**
     * Retrieves the statement required to access the
     * value of the column. This is typically the alias
     * used in the SELECT statement, if available. Otherwise,
     * and if the column is a sub-query, this will be
     * the full statement.
     *
     * @return string
     */
    public function getJoinValue() : string
    {
        return $this->generateMarker(self::COMPONENT_JOIN);
    }

    public function getGroupByValue() : string
    {
        return $this->generateMarker(self::COMPONENT_GROUP_BY);
    }

    public function getOrderByValue() : string
    {
        return $this->generateMarker(self::COMPONENT_ORDER_BY);
    }

    public function getWhereValue() : string
    {
        return $this->generateMarker(self::COMPONENT_WHERE);
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
    public function getPrimarySelectValue() : string
    {
        return $this->generateMarker(self::COMPONENT_SELECT_PRIMARY);
    }

    public function getSecondarySelectValue() : string
    {
        return $this->generateMarker(self::COMPONENT_SELECT_SECONDARY);
    }

    // endregion

    // region: Managing markers

    public const COMPONENT_WHERE = 'where';
    public const COMPONENT_ORDER_BY = 'order_by';
    public const COMPONENT_GROUP_BY = 'group_by';
    public const COMPONENT_SELECT_PRIMARY = 'select_primary';
    public const COMPONENT_SELECT_SECONDARY = 'select_secondary';
    public const COMPONENT_JOIN = 'join';

    public const MARKER_SUFFIX = '$$';

    /**
     * @var string
     */
    private $markerRegex = '';

    private function generateMarkerRegex() : string
    {
        if($this->markerRegex !== '')
        {
            return $this->markerRegex;
        }

        $components = array(
            self::COMPONENT_SELECT_PRIMARY,
            self::COMPONENT_SELECT_SECONDARY,
            self::COMPONENT_WHERE,
            self::COMPONENT_ORDER_BY,
            self::COMPONENT_GROUP_BY,
            self::COMPONENT_JOIN
        );

        $this->markerRegex = sprintf(
            '/%1$s%2$s_(%3$s)%1$s/sU',
            preg_quote(self::MARKER_SUFFIX, '/'),
            preg_quote($this->getName(), '/'),
            implode('|', $components)
        );

        return $this->markerRegex;
    }

    public function getPrimarySelectMarker() : string
    {
        return $this->generateMarker(self::COMPONENT_SELECT_PRIMARY);
    }

    public function getSecondarySelectMarker() : string
    {
        return $this->generateMarker(self::COMPONENT_SELECT_SECONDARY);
    }

    public function getOrderByMarker() : string
    {
        return $this->generateMarker(self::COMPONENT_ORDER_BY);
    }

    public function getGroupByMarker() : string
    {
        return $this->generateMarker(self::COMPONENT_GROUP_BY);
    }

    public function getWhereMarker() : string
    {
        return $this->generateMarker(self::COMPONENT_WHERE);
    }

    public function getJoinMarker() : string
    {
        return $this->generateMarker(self::COMPONENT_JOIN);
    }

    private function generateMarker(string $component='') : string
    {
        $name = self::MARKER_SUFFIX .$this->getName();

        if(!empty($component))
        {
            return $name.'_'.$component. self::MARKER_SUFFIX;
        }

        return $name. self::MARKER_SUFFIX;
    }

    // endregion

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
    public function addCustomPlaceholders(array $placeholders) : Application_FilterCriteria_Database_CustomColumn
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
    public function addCustomPlaceholder(string $name, $value) : Application_FilterCriteria_Database_CustomColumn
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
        return preg_match($this->generateMarkerRegex(), $subject) === 1;
    }

    public function canUseAlias() : bool
    {
        return $this->getUsage()->isInSelect() && !$this->isSubQuery();
    }

    // region: Rendering markers

    public function replaceMarkers(string $query) : string
    {
        if(!$this->isFoundInString($query))
        {
            return $query;
        }

        $replaces = array();
        $replaces[$this->getWhereMarker()] = $this->renderWhereValue();
        $replaces[$this->getPrimarySelectMarker()] = $this->renderPrimarySelectValue();
        $replaces[$this->getSecondarySelectMarker()] = $this->renderSecondarySelectValue();
        $replaces[$this->getGroupByMarker()] = $this->renderGroupByValue();
        $replaces[$this->getOrderByMarker()] = $this->renderOrderByValue();
        $replaces[$this->getJoinMarker()] = $this->renderJoinValue();

        return str_replace(array_keys($replaces), array_values($replaces), $query);
    }

    private function renderPrimarySelectValue() : string
    {
        return $this->getSQLStatement().' AS '.$this->getSelectAlias();
    }

    private function renderSecondarySelectValue() : string
    {
        return $this->getSQLStatement();
    }

    private function renderWhereValue() : string
    {
        return $this->renderValue(false);
    }

    private function renderJoinValue() : string
    {
        return $this->renderValue(false);
    }

    private function renderOrderByValue() : string
    {
        return $this->renderValue(true);
    }

    private function renderGroupByValue() : string
    {
        return $this->renderValue(false);
    }

    private function renderValue(bool $complexAliasAllowed) : string
    {
        // When counting records, no column aliases may be
        // used, since the select statements are not present.
        // Also, when a column comes from a join, it is safer
        // to use the full SQL as well, to ensure that no ambiguous
        // error messages are generated.
        if($this->filters->isCount())
        {
            return $this->getSQLStatement();
        }

        // Complex queries are sub-queries for example, which
        // do not allow using the field alias. We have to duplicate
        // the full SQL statement.
        if(!$complexAliasAllowed && $this->isComplexQuery())
        {
            return $this->getSQLStatement();
        }

        /*
         * @TODO Review if there are any valid cases to use this
         *
         * Problem: It is impossible to determine if the
         * alias can safely be used, for example when it
         * has the same name as an existing column from
         * a joined table.
         *
         * Also, since these queries are automatically
         * generated, the benefit of using aliases is
         * arguably minimal.
         *
        if($this->canUseAlias())
        {
            return $this->getSelectAlias();
        }*/

        return $this->getSQLStatement();
    }

    // endregion
}
