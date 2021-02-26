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
 */
class DBHelper_FetchKey extends DBHelper_FetchBase
{
   /**
    * @var string
    */
    protected $key;
    
    public function __construct(string $key, string $table)
    {
        parent::__construct($table);
        
        $this->key = $key;
    }
    
    public function exists() : bool
    {
        return $this->fetchString() !== '';
    }
    
    public function fetchInt() : int
    {
        return intval($this->fetchString());
    }
    
    public function fetchString() : string
    {
        $query = sprintf(
            "SELECT
                %s
            FROM
                `%s`
            WHERE
                %s",
            $this->escapeColumn($this->key),
            $this->table,
            $this->buildWhere()
        );
        
        $data = DBHelper::fetch($query, $this->data);
        
        if(is_array($data) && isset($data[$this->key])) 
        {
            return (string)$data[$this->key];
        }
        
        return '';
    }
}
