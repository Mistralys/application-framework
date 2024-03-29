<?php
/**
 * File containing the class {@see Application_FilterCriteria_Database_ColumnUsage}.
 *
 * @package Application
 * @subpackage FilterCriteria
 * @see Application_FilterCriteria_Database_ColumnUsage
 */

declare(strict_types=1);

/**
 * Column usage utility class: offers an easy-to-use interface
 * to get the usage status of a custom column. Easily check if
 * a custom column is used in the filter criteria, and where
 * exactly.
 *
 * @package Application
 * @subpackage FilterCriteria
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_FilterCriteria_DatabaseExtended::checkColumnUsage()
 */
class Application_FilterCriteria_Database_ColumnUsage
{
    /**
     * @var Application_FilterCriteria_DatabaseExtended
     */
    private $filters;

    /**
     * @var bool
     */
    private $inSelect = false;

    /**
     * @var bool
     */
    private $inWhere = false;

    /**
     * @var bool
     */
    private $inGroupBys = false;

    /**
     * @var bool
     */
    private $inJoins = false;

    /**
     * @var bool
     */
    private $inOrderBy = false;

    /**
     * @var Application_FilterCriteria_Database_CustomColumn
     */
    private $column;

    public function __construct(Application_FilterCriteria_DatabaseExtended $filters, Application_FilterCriteria_Database_CustomColumn $column)
    {
        $this->filters = $filters;
        $this->column = $column;

        $this->checkOrderBy();
        $this->checkSelects();
        $this->checkWheres();
        $this->checkGroupBys();
        $this->checkJoins();
    }

    public function isInOrderBy() : bool
    {
        return $this->inOrderBy;
    }

    public function isInSelect() : bool
    {
        return $this->inSelect;
    }

    public function isInWhere() : bool
    {
        return $this->inWhere;
    }

    public function isInGroupBy() : bool
    {
        return $this->inGroupBys;
    }

    public function isInJoin() : bool
    {
        return $this->inJoins;
    }

    public function isInUse() : bool
    {
        return
            $this->isInSelect()
            ||
            $this->isInGroupBy()
            ||
            $this->isInWhere()
            ||
            $this->isInJoin()
            ||
            $this->isInOrderBy();
    }

    private function checkOrderBy() : void
    {
        $orderField = $this->filters->getOrderField();

        $this->inOrderBy = $this->column->isFoundInString($orderField);
    }

    private function checkSelects() : void
    {
        // No select columns are available in COUNT mode.
        if($this->filters->isCount())
        {
            return;
        }

        $selects = $this->filters->getSelects();

        foreach ($selects as $select)
        {
            if ($this->column->isFoundInString($select))
            {
                $this->inSelect = true;
                return;
            }
        }
    }

    private function checkWheres() : void
    {
        $wheres = $this->filters->getWheres();
        foreach ($wheres as $where)
        {
            if ($this->column->isFoundInString($where))
            {
                $this->inWhere = true;
                return;
            }
        }
    }

    private function checkGroupBys() : void
    {
        $groupBys = $this->filters->getGroupBys();
        foreach ($groupBys as $groupBy)
        {
            if ($this->column->isFoundInString($groupBy))
            {
                $this->inGroupBys = true;
                return;
            }
        }
    }

    private function checkJoins() : void
    {
        $joins = $this->filters->getJoins();
        foreach ($joins as $join)
        {
            $joinStatement = $join->getStatement();

            if ($this->column->isFoundInString($joinStatement))
            {
                $this->inJoins = true;
                return;
            }
        }
    }
}

