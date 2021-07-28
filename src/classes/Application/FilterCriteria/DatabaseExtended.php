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
    private $customInitialized = false;

    /**
     * @var array<string,Application_FilterCriteria_Database_CustomColumn>
     */
    protected $customColumns = array();

    protected function initQuery() : void
    {
        $this->initCustomColumns();
    }

    abstract protected function _initCustomColumns() : void;

    protected function initCustomColumns() : void
    {
        if($this->customInitialized)
        {
            return;
        }

        $this->customInitialized = true;

        $this->_initCustomColumns();
    }

    protected function buildJoins() : string
    {
        foreach ($this->customColumns as $column)
        {
            if(!$column->isEnabled() || !$column->hasJOINs())
            {
                continue;
            }

            $joins = $column->getJOINs();
            foreach($joins as $join)
            {
                $this->addJoin($join);
            }
        }

        return parent::buildJoins();
    }

    protected function collectSelects() : array
    {
        $this->initCustomColumns();

        $selects = parent::collectSelects();

        return array_merge($selects, $this->getCustomSelects());
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
        return isset($this->customColumns[$columnID]) && $this->customColumns[$columnID]->isEnabled();
    }

    /**
     * Registers a new custom column that is used to add
     * additional, dynamic select statements to the query.
     *
     * @param string $columnID
     * @param NamedClosure $callback
     * @return Application_FilterCriteria_Database_CustomColumn
     *
     * @throws Application_Exception
     * @see Application_FilterCriteria_DatabaseExtended::ERROR_CANNOT_REGISTER_COLUMN_AGAIN
     */
    protected function registerCustomColumn(string $columnID, NamedClosure $callback) : Application_FilterCriteria_Database_CustomColumn
    {
        if(!isset($this->customColumns[$columnID]))
        {
            $column = new Application_FilterCriteria_Database_CustomColumn($this, $columnID, $callback);
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
        if(isset($this->customColumns[$columnID]))
        {
            return $this->customColumns[$columnID];
        }

        throw $this->createMissingColumnException($columnID);
    }
}
