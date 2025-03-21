<?php
/**
 * File containing the {@link DBHelper_FetchBase} class.
 * @package Helpers
 * @subpackage DBHelper
 * @see DBHelper_FetchBase
 */

declare(strict_types=1);

/**
 * Abstract base class for fetcher classes.
 *
 * @package Helpers
 * @subpackage DBHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class DBHelper_FetchBase
{
    protected string $table;
    protected static int $placeholderCounter = 0;

   /**
    * @var array<int|string,mixed>
    */
    protected array $data = array();
    
   /**
    * @var string[]
    */
    protected array $where = array();
    
    public function __construct(string $table)
    {
        $this->table = $table;
    }
    
   /**
    * @param string $column
    * @param string|int|float|null $value
    * @return $this
    */
    public function whereValue(string $column, $value) : self
    {
        if($value === null) {
            return $this->whereNull($column);
        }

        $placeholder = $this->createPlaceholder();
        
        $this->data[$placeholder] = $value;
        
        return $this->addWhere(sprintf(
            '    %s=:%s',
            $this->escapeColumn($column),
            $placeholder
        ));
    }
    
   /**
    * Adds a where statement to ensure the column does not
    * match the specified value.
    * 
    * @param string $column
    * @param string|null|int|float $value
    * @return $this
    */
    public function whereValueNot(string $column, $value) : self
    {
        if(is_null($value)) {
            return $this->whereNotNull($column);
        }
        
        $placeholder = $this->createPlaceholder();
        
        $this->data[$placeholder] = $value;
        
        return $this->addWhere(sprintf(
            '    %s!=:%s',
            $this->escapeColumn($column),
            $placeholder
        ));
    }
    
   /**
    * Adds several column values to limit the result to.
    * 
    * @param array<string,mixed> $values
    * @return $this
    */
    public function whereValues(array $values) : self
    {
        foreach($values as $column => $value)
        {
            $this->whereValue($column, $value);
        }
        
        return $this;
    }
    
   /**
    * Adds a where column `is null` statement.
    * 
    * @param string $column
    * @return $this
    */
    public function whereNull(string $column) : self
    {
        return $this->addWhere(sprintf(
            '    %s IS NULL',
            $this->escapeColumn($column)
        ));
    }
    
   /**
    * Adds a where column `is not null` statement.
    * 
    * @param string $column
    * @return $this
    */
    public function whereNotNull(string $column) : self
    {
        return $this->addWhere(sprintf(
            '    %s IS NOT NULL',
            $this->escapeColumn($column)
        ));
    }
    
   /**
    * Adds a custom where statement.
    * 
    * @param string $where
    * @return $this
    */
    protected function addWhere(string $where) : self
    {
        $this->where[] = $where;
        
        return $this;
    }
    
    protected function createPlaceholder() : string
    {
        self::$placeholderCounter++;
        
        return 'ph'.self::$placeholderCounter;
    }
    
    protected function escapeColumn(string $column) : string
    {
        if(strpos($column, '`')) {
            return $column;
        }

        $parts = explode('.', $column);
        
        $keep = array();
        
        foreach($parts as $part)
        {
            $keep[] = '`'.trim($part, '`').'`';
        }
        
        return implode('.', $keep);
    }
    
    protected function buildWhere() : string
    {
        if(empty($this->where))
        {
            return '    1';
        }
        
        return implode(PHP_EOL.'AND'.PHP_EOL, $this->where);
    }

    /**
     * @param string $name
     * @param array $values
     * @return $this
     */
    public function whereValueIN(string $name, array $values) : self
    {
        return $this->buildIN('IN', $name, $values);
    }

    /**
     * @param string $name
     * @param array $values
     * @return $this
     */
    public function whereValueNOT_IN(string $name, array $values) : self
    {
        return $this->buildIN('NOT IN', $name, $values);
    }

    /**
     * @param string $type
     * @param string $name
     * @param array<string|int,mixed> $values
     * @return $this
     */
    private function buildIN(string $type, string $name, array $values) : self
    {
        $in = array();

        foreach ($values as $value)
        {
            $placeholder = $this->createPlaceholder();
            $this->data[$placeholder] = $value;
            $in[] = ':'.$placeholder;
        }

        $this->where[] = sprintf(
            "%s %s(%s)",
            $this->escapeColumn($name),
            $type,
            implode(', ', $in)
        );

        return $this;
    }
}
