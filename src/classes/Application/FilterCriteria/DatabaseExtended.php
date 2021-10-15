<?php
/**
 * File containing the class {@see Application_FilterCriteria_DatabaseExtended}.
 *
 * @package Application
 * @subpackage FilterCriteria
 * @see Application_FilterCriteria_DatabaseExtended
 */

declare(strict_types=1);

use AppUtils\NamedClosure;

/**
 * Formalizes the use of custom columns in the filter criteria,
 * by defining abstract methods to set up the custom columns to
 * use.
 *
 * @package Application
 * @subpackage FilterCriteria
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Application_FilterCriteria_DatabaseExtended extends Application_FilterCriteria_Database
{
    const ERROR_CANNOT_REGISTER_COLUMN_AGAIN = 90501;

    /**
     * @var bool
     */
    private $customColumnsInitialized = false;

    /**
     * @var array<string,Application_FilterCriteria_Database_CustomColumn>
     */
    protected $customColumns = array();

    abstract protected function _initCustomColumns() : void;

    private function initCustomColumns() : void
    {
        if($this->customColumnsInitialized)
        {
            return;
        }

        $this->customColumnsInitialized = true;

        $this->_initCustomColumns();
    }

    public function getSelects() : array
    {
        $selects = array_merge(parent::getSelects(), $this->getCustomSelects());

        return array_map('strval', $selects);
    }

    /**
     * Fetches all select statements for custom columns.
     *
     * @return string[]
     *
     * @throws Application_Exception
     * @see Application_FilterCriteria_Database_CustomColumn::ERROR_SELECT_STATEMENT_NOT_A_STRING
     */
    protected function getCustomSelects() : array
    {
        $this->initCustomColumns();

        $result = array();

        foreach ($this->customColumns as $column)
        {
            if(!$column->isEnabled())
            {
                continue;
            }

            $result[] = $column->getSelect();
        }
        
        return $result;
    }

    public function isColumnInSelect(Application_FilterCriteria_Database_CustomColumn $column) : bool
    {
        $selects = $this->getSelects();
        $search = $column->getStatement();

        foreach($selects as $select)
        {
            if(strstr($select, $search) !== false)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Fetches the custom column's select statement from the target method.
     *
     * @param string $columnID
     * @return string
     *
     * @throws Application_Exception
     * @see Application_FilterCriteria_Database::ERROR_CUSTOM_COLUMN_NOT_REGISTERED
     */
    protected function getCustomSelect(string $columnID) : string
    {
        $this->initCustomColumns();

        if(isset($this->customColumns[$columnID]))
        {
            return $this->customColumns[$columnID]->getSelect();
        }

        throw $this->createMissingColumnException($columnID);
    }

    /**
     * Select a custom column that is not directly available in the
     * target table. This requires the column to have been registered
     * first, using the {@see Application_FilterCriteria_Database::registerCustomColumn()}
     * method.
     *
     * To use this feature, it is recommended to use the extended
     * filter class {@see Application_FilterCriteria_DatabaseExtended}.
     * It formalizes the use of custom columns.
     *
     * @param string $columnID The name of the custom column
     * @param bool $enable Whether to enable this column. Allows turning it on and off at will.
     * @return $this
     *
     * @see Application_FilterCriteria_Database::registerCustomColumn()
     *
     * @throws Application_Exception
     * @see Application_FilterCriteria_Database::ERROR_CUSTOM_COLUMN_NOT_REGISTERED
     */
    protected function withCustomColumn(string $columnID, bool $enable=true)
    {
        $this->initCustomColumns();

        if(isset($this->customColumns[$columnID]))
        {
            $this->customColumns[$columnID]->setEnabled($enable);
            return $this;
        }

        throw $this->createMissingColumnException($columnID);
    }

    protected function createMissingColumnException(string $columnID) : Application_Exception
    {
        return new Application_Exception(
            'Custom column not registered',
            sprintf(
                'The custom column [%s] is not available, it has not been registered in the filters class [%s]. Registered columns are: [%s].',
                $columnID,
                get_class($this),
                implode(', ', array_keys($this->customColumns))
            ),
            self::ERROR_CUSTOM_COLUMN_NOT_REGISTERED
        );
    }

    /**
     * Check if the specified custom column has been added,
     * and is enabled. Returns false if it has been added,
     * but is not enabled.
     *
     * @param string $columnID
     * @return bool
     */
    public function hasCustomColumn(string $columnID) : bool
    {
        $this->initCustomColumns();

        return isset($this->customColumns[$columnID]) && $this->customColumns[$columnID]->isEnabled();
    }

    protected function registerCustomSelect(string $template, string $columnID='') : Application_FilterCriteria_Database_CustomColumn
    {
        if(empty($columnID))
        {
            $columnID = $template;
        }

        return $this->registerCustomColumn($columnID, $this->statement($template));
    }

    /**
     * @param string|DBHelper_StatementBuilder $countryIDColumn
     * @param string $columnID
     * @return Application_FilterCriteria_Database_CustomColumn
     */
    protected function registerCountryLabelSelect($countryIDColumn, string $columnID) : Application_FilterCriteria_Database_CustomColumn
    {
        return $this->registerCustomSelect(
            $this->renderCountriesCase($countryIDColumn),
            $columnID
        );
    }

    protected function registerUserNameSelect($userIDColumn, string $columnID) : Application_FilterCriteria_Database_CustomColumn
    {
        $query = statementBuilder(
        "
            (
                SELECT
                    CONCAT({table_users}.{first_name}, ' ', {table_users}.{last_name})
                FROM
                    {table_users}
                WHERE
                    {table_users}.{users_primary}=%s
            )"
        )
            ->table('{table_users}', Application_Users::TABLE_NAME)
            ->field('{first_name}', Application_Users_User::COL_FIRST_NAME)
            ->field('{last_name}', Application_Users_User::COL_LAST_NAME)
            ->field('{users_primary}', Application_Users::PRIMARY_NAME);

        return $this->registerCustomSelect(
            sprintf((string)$query, (string)$userIDColumn),
            $columnID
        );
    }

    /**
     * @param string|DBHelper_StatementBuilder $countryIDColumn
     * @return string
     */
    private function renderCountriesCase($countryIDColumn) : string
    {
        $countries = Application_Countries::getInstance();
        $all = $countries->getAll(false);
        $case = DBHelper_CaseStatement::create($countryIDColumn);

        foreach($all as $country)
        {
            $case->addIntString($country->getID(), $country->getLocalizedLabel());
        }

        return $case->render();
    }

    /**
     * Registers a new custom column that is used to add
     * additional, dynamic select statements to the query.
     *
     * @param string $columnID
     * @param NamedClosure|DBHelper_StatementBuilder $source
     * @return Application_FilterCriteria_Database_CustomColumn
     *
     * @throws Application_Exception
     * @see Application_FilterCriteria_DatabaseExtended::ERROR_CANNOT_REGISTER_COLUMN_AGAIN
     */
    protected function registerCustomColumn(string $columnID, $source) : Application_FilterCriteria_Database_CustomColumn
    {
        if(!isset($this->customColumns[$columnID]))
        {
            $column = new Application_FilterCriteria_Database_CustomColumn($this, $columnID, $source);
            $this->customColumns[$columnID] = $column;
            return $column;
        }

        throw new Application_Exception(
            'Can register custom columns only once',
            sprintf(
                'The column [%s] has already been registered.',
                $columnID
            ),
            self::ERROR_CANNOT_REGISTER_COLUMN_AGAIN
        );
    }

    /**
     * Fetches a custom column instance.
     *
     * @param string $columnID
     * @return Application_FilterCriteria_Database_CustomColumn
     * @throws Application_Exception
     */
    public function getCustomColumn(string $columnID) : Application_FilterCriteria_Database_CustomColumn
    {
        $this->initCustomColumns();

        if(isset($this->customColumns[$columnID]))
        {
            return $this->customColumns[$columnID];
        }

        throw $this->createMissingColumnException($columnID);
    }

    public function setOrderBy($fieldName, string $orderDir = 'ASC')
    {
        foreach($this->customColumns as $column)
        {
            if(strstr($fieldName, $column->getSelectAlias()) || strstr($fieldName, $column->getStatement()))
            {
                $column->setEnabled(true);
                $this->addSelectColumn($column->getSelect());

                $fieldName = $column->getOrderBy();
                break;
            }
        }

        return parent::setOrderBy($fieldName, $orderDir);
    }

    public function isColumnInUse(Application_FilterCriteria_Database_CustomColumn $column) : bool
    {
        $statement = $column->getStatement();
        $alias = $column->getSelectAlias();

        $selects = $this->getSelects();
        foreach($selects as $select)
        {
            if(strstr($select, $statement) || strstr($select, $alias))
            {
                return true;
            }
        }

        $wheres = $this->getWheres();
        foreach($wheres as $where)
        {
            if(strstr($where, $statement) || strstr($where, $alias))
            {
                return true;
            }
        }

        $groupBys = $this->getGroupBys();
        foreach($groupBys as $groupBy)
        {
            if(strstr($groupBy, $statement) || strstr($groupBy, $alias))
            {
                return true;
            }
        }

        $joins = $this->getJoins();
        foreach($joins as $join)
        {
            $joinStatement = $join->getStatement();

            if(strstr($joinStatement, $statement) || strstr($joinStatement, $alias))
            {
                return true;
            }
        }

        return false;
    }

    protected function _applyFilters() : void
    {
        parent::_applyFilters();

        $this->initCustomColumns();
        $this->autoEnableColumns();
        $this->initJoins();
    }

    protected function initJoins() : void
    {
        foreach ($this->customColumns as $column)
        {
            if(!$column->isEnabled() || !$column->hasJoins())
            {
                continue;
            }

            $IDs = $column->getRequiredJoinIDs();

            foreach($IDs as $joinID)
            {
                $this->requireJoin($joinID);
            }
        }
    }

    protected function autoEnableColumns() : void
    {
        foreach($this->customColumns as $column)
        {
            if($column->isEnabled())
            {
                continue;
            }

            if($this->isColumnInUse($column))
            {
                $column->setEnabled(true);
            }
        }
    }

    public function getGroupBys() : array
    {
        $groupBy = $this->groupBy;

        $custom = $this->detectCustomColumnUsage();

        foreach($custom as $column)
        {
            $statement = $column->getGroupBy();

            if(!in_array($statement, $groupBy))
            {
                $groupBy[] = $statement;
            }
        }

        return $groupBy;
    }

    /**
     * Detects all custom columns that are used in the
     * query, from the select statements to the order by
     * columns.
     *
     * @return Application_FilterCriteria_Database_CustomColumn[]
     * @throws Application_Exception
     */
    public function detectCustomColumnUsage() : array
    {
        $this->initCustomColumns();

        $select = $this->getSelects();
        $custom = array();

        foreach($select as $statement)
        {
            foreach($this->customColumns as $column)
            {
                if(strstr($statement, $column->getSelect()))
                {
                    $custom[$column->getName()] = $column;
                }
            }
        }

        if(!empty($this->orderField))
        {
            foreach ($this->customColumns as $column)
            {
                if($this->orderField === $column->getStatement())
                {
                    $custom[$column->getName()] = $column;
                }
            }
        }

        return array_values($custom);
    }
}
