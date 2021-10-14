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
    private $initDone = false;

    /**
     * @var array<string,Application_FilterCriteria_Database_CustomColumn>
     */
    protected $customColumns = array();

    protected function initQuery() : void
    {
        if($this->initDone)
        {
            return;
        }

        $this->initDone = true;

        $this->registerStatementValues();
        $this->registerJoins();
        $this->initCustomColumns();
    }

    abstract protected function _registerStatementValues(DBHelper_StatementBuilder_ValuesContainer $container) : void;

    abstract protected function _initCustomColumns() : void;

    abstract protected function _registerJoins() : void;

    private function registerStatementValues() : void
    {
        $this->_registerStatementValues($this->createStatementValues());
    }

    private function registerJoins() : void
    {
        $this->_registerJoins();
    }

    private function initCustomColumns() : void
    {
        $this->_initCustomColumns();
    }

    protected function buildJoins() : string
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

        return parent::buildJoins();
    }

    protected function collectSelects() : array
    {
        $selects = array_merge(parent::collectSelects(), $this->getCustomSelects());

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
        $this->initQuery();

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
        $this->initQuery();

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
        $this->initQuery();

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
        $this->initQuery();

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
        return $this->registerCustomSelect(
            sprintf(
                "(
                    SELECT
                        CONCAT(`known_users`.`firstname`, ' ', `known_users`.`lastname`)
                    FROM
                        `known_users`
                    WHERE
                        `known_users`.`user_id`=%s
                )",
                (string)$userIDColumn
            ),
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
        $cases = array();
        foreach($all as $country)
        {
            $cases[] = sprintf(
                "WHEN '%s' THEN '%s'",
                $country->getID(),
                $country->getLocalizedLabel()
            );
        }

        return sprintf(
            "CASE %s
                %s
                ELSE
                ''
                END
                ",
            $countryIDColumn,
            implode(PHP_EOL, $cases)
        );
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
        $this->initQuery();

        if(isset($this->customColumns[$columnID]))
        {
            return $this->customColumns[$columnID];
        }

        throw $this->createMissingColumnException($columnID);
    }

    public function setOrderBy($fieldName, string $orderDir = 'ASC')
    {
        $this->initQuery();

        $compareName = trim($fieldName, '`');

        foreach($this->customColumns as $column)
        {
            if($column->getName() === $compareName)
            {
                $column->setEnabled(true);
                $this->addSelectColumn($column->getSelect());
            }
        }

        return parent::setOrderBy($fieldName, $orderDir);
    }

    /**
     * Overridden to handle custom columns that are used in the
     * query, without having been expressly enabled. They are
     * silently enabled as needed, and the query generated anew.
     *
     * @param bool $isCount
     * @return string
     * @throws Application_Exception
     */
    protected function buildQuery(bool $isCount=false) : string
    {
        $query = parent::buildQuery($isCount);
        $found = false;

        foreach($this->customColumns as $column)
        {
            if($column->isEnabled())
            {
                continue;
            }

            if(strstr($query, $column->getStatement()) === false)
            {
                continue;
            }

            $column->setEnabled(true);
            $found = true;
        }

        if(!$found)
        {
            return $query;
        }

        return parent::buildQuery($isCount);
    }
}
