<?php
/**
 * File containing the {@link DBHelper_FetchMany} class.
 * @package Helpers
 * @subpackage DBHelper
 * @see DBHelper_FetchMany
 */

declare(strict_types=1);

/**
 * Specialized class used to fetch multiple records from a table.
 *
 * @package Helpers
 * @subpackage DBHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 */
class DBHelper_FetchMany extends DBHelper_FetchOne
{
    /**
     * @var string[]
     */
    protected $groupBy = array();

    /**
     * @var string
     */
    protected $orderField = '';

    /**
     * @var string
     */
    protected $orderDir = 'ASC';

    /**
     * @return array<int,array<int|string,string|int|float|NULL>>
     */
    public function fetch() : array
    {
        return DBHelper::fetchAll($this->renderQuery(), $this->data);
    }

    protected function renderQuery(): string
    {
        return
            parent::renderQuery().
            $this->buildGroupBy().
            $this->buildOrderBy();
    }

    /**
    * Retrieves only the specified column from all results.
    * Note: Values are converted to strings.
    *
    * @param string $column
    * @return string[]
    */
    public function fetchColumn(string $column) : array
    {
        $result = array();
        $items = $this->fetch(); 
        
        foreach($items as $item)
        {
            $value = '';
            
            if(isset($item[$column]))
            {
                $value = (string)$item[$column];
            }
            
            $result[] = $value;
        }
        
        return $result;
    }
    
   /**
    * Retrieves only the specified column from all results, converted to Integer.
    * 
    * @param string $column
    * @return int[]
    */
    public function fetchColumnInt(string $column) : array
    {
        $result = array();
        $items = $this->fetchColumn($column);
        
        foreach($items as $item)
        {
            $result[] = intval($item);
        }
        
        return $result;
    }

    public function groupBy(string $column)
    {
        if(!in_array($column, $this->groupBy))
        {
            $this->groupBy[] = $column;
        }

        return $this;
    }

    protected function buildGroupBy() : string
    {
        if(empty($this->groupBy))
        {
            return '';
        }

        $parts = array();
        foreach($this->groupBy as $column)
        {
            $parts[] = $this->escapeColumn($column);
        }

        return ' GROUP BY '.implode(', ', $parts);
    }

    protected function buildOrderBy() : string
    {
        if(empty($this->orderField))
        {
            return '';
        }

        return ' ORDER BY '.$this->orderField.' '.$this->orderDir;
    }

    public function orderBy(string $column, string $direction='ASC') : self
    {
        $this->orderField = $column;
        $this->orderDir = $direction;

        return $this;
    }
}
