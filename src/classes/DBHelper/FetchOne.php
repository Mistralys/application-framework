<?php
/**
 * File containing the {@link DBHelper_FetchKey} class.
 * @package Helpers
 * @subpackage DBHelper
 * @see DBHelper_FetchKey
 */

declare(strict_types=1);

/**
 * Specialized class used to fetch a single column value from a table.
 *
 * @package Helpers
 * @subpackage DBHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * 
 */
class DBHelper_FetchOne extends DBHelper_FetchBase
{
   /**
    * @var string
    */
    protected $select = '*';
    
   /**
    * Selects a single column to fetch from the result.
    * 
    * @param string $column
    * @return $this
    */
    public function selectColumn(string $column)
    {
        $this->select = $this->escapeColumn($column);
        return $this;
    }
    
   /**
    * Selects several columns to fetch in the result.
    * 
    * @param string|array ...$args Either an array with column names (first parameter), or column names as method parameters.
    * @return $this
    */
    public function selectColumns(...$args)
    {
        if(empty($args))
        {
            return $this;
        }
        
        if(is_array($args[0]))
        {
            $args = $args[0];
        }
        
        $columns = array_map(array($this, 'escapeColumn'), $args);
        $this->select = implode(', ', $columns);
        
        return $this;
    }
    
    public function fetch() : array
    {
        $result = DBHelper::fetch($this->renderQuery(), $this->data);

        if($result !== null)
        {
            return $result;
        }
        
        return array();
    }
    
    protected function renderQuery() : string
    {
        return sprintf(
            "SELECT
                %s
            FROM
                `%s`
            WHERE
                %s",
            $this->select,
            $this->table,
            $this->buildWhere()
        );
    }
}
