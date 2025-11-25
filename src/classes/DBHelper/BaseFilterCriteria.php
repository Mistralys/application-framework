<?php
/**
 * @package DBHelper
 * @subpackage FilterCriteria
 */

declare(strict_types=1);

use Application\Collection\IntegerCollectionItemInterface;
use DBHelper\BaseCollection\DBHelperCollectionInterface;
use DBHelper\BaseFilterCriteria\BaseCollectionFilteringInterface;
use DBHelper\BaseFilterCriteria\IntegerCollectionFilteringInterface;
use DBHelper\DBHelperFilterCriteriaInterface;
use DBHelper\Interfaces\DBHelperRecordInterface;

/**
 * Base class for filter criteria to be used in conjunction
 * with DB record collections. Automatically configures the
 * application filter criteria class to be used with a record
 * collection.
 * 
 * The basic usage for this is to extend this class, for example:
 * 
 * <pre>
 * class MyClassName_FilterCriteria extends DBHelper_BaseFilterCriteria
 * {
 *     protected function prepareQuery()
 *     {
 *         // optional JOINs, WHEREs, etc.
 *     }
 * }
 * </pre>
 * 
 * In the collection, simply specify the name of the class, like so:
 * 
 * <pre>
 * public function getRecordFiltersClassName()
 * {
 *     return 'MyClassName_FilterCriteria';
 * }
 * </pre>
 * 
 * @package DBHelper
 * @subpackage FilterCriteria
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class DBHelper_BaseFilterCriteria extends Application_FilterCriteria_DatabaseExtended implements DBHelperFilterCriteriaInterface
{
    protected IntegerCollectionFilteringInterface $collection;
    protected string $recordTableName;
    protected string $recordPrimaryName;

    /**
     * The collection uses a reduced interface to limit its scope as
     * much as possible (only the methods required for filtering).
     *
     * Using the full collection interface would couple this class
     * too tightly to the collection implementation. This way, there
     * is more freedom in how the collection is implemented.
     *
     * @param IntegerCollectionFilteringInterface $collection
     */
    public function __construct(IntegerCollectionFilteringInterface $collection)
    {
        $this->collection = $collection;
        $this->recordTableName = $collection->getRecordTableName();
        $this->recordPrimaryName = $collection->getRecordPrimaryName();

        parent::__construct($collection);

        $this->setOrderBy(
            $collection->getRecordDefaultSortKey(),
            $collection->getRecordDefaultSortDir()
        );
    }

    protected function init() : void
    {
        
    }

    protected function _initCustomColumns() : void
    {

    }
    
    public function getSearchFields() : array
    {
        $fields = $this->collection->getRecordSearchableKeys();
        $result = array();
        foreach($fields as $field) {
            if(!str_contains($field, '.')) {
                $field = sprintf(
                    "%s.`%s`",
                    $this->resolveTableSelector(),
                    $field
                );
            }
            
            $result[] = $field; 
        }
        
        return $result;
    }
    
   /**
    * This is called before the query is built, and
    * allows for joins, where conditions and the like
    * to be configured.
    */
    protected function prepareQuery() : void
    {
        
    }
    
    public function getQuery() : string|DBHelper_StatementBuilder
    {
        $this->prepareQuery();
        
        // ensure we use any required foreign key values from the collection
        $foreignKeys = $this->collection->getForeignKeys();
        foreach($foreignKeys as $key => $value) {
            $this->addWhereColumnEquals($key, $value);
        }
        
        return sprintf(
            /** @lang text */
            "SELECT 
                {WHAT} 
            FROM 
                %s 
            {JOINS} 
            {WHERE} 
            {GROUPBY} 
            {ORDERBY} 
            {LIMIT}",
            $this->resolveTableFrom()
        );
    }
    
    protected function getSelect() : array
    {
        return array(
            sprintf(
                "%s.`%s`",
                $this->resolveTableSelector(),
                $this->collection->getRecordPrimaryName()
            )
        );
    }
    
    protected function resolveTableFrom() : string
    {
        $from = '`'.$this->recordTableName.'`';
        
        if(isset($this->selectAlias)) {
            $from .= ' AS '.$this->selectAlias;
        }
        
        return $from;
    }
    
    protected function resolveTableSelector() : string
    {
        return $this->selectAlias ?? ('`' . $this->recordTableName . '`');
    }

    /**
     * @return IntegerCollectionItemInterface[]
     * @throws DBHelper_Exception
     */
    public function getItemsObjects() : array
    {
        $primaryName = $this->collection->getRecordPrimaryName();

        $records = array();
        foreach($this->getItems() as $item) {
            $records[] = $this->collection->getByID((int)$item[$primaryName]);
        }

        return $records;
    }

    /**
     * @return DBHelper_BaseFilterCriteria_Record[]
     * @throws DBHelper_Exception
     */
    public function getItemsDetailed() : array
    {
        $primaryName = $this->collection->getRecordPrimaryName();
        $items = $this->getItems();
        
        $total = count($items);
        $records = array();
        for($i=0; $i < $total; $i++)
        {
            $records[] = new DBHelper_BaseFilterCriteria_Record(
                $items[$i],
                $this->collection->getByID((int)$items[$i][$primaryName])
            );
        }

        return $records;
    }
    
   /**
    * Retrieves the primary keys for all items in the current selection.
    * @return integer[]
    */
    public function getIDs() : array
    {
        $items = $this->getItems();
        $ids = array();
        foreach($items as $item) {
            $ids[] = (int)$item[$this->recordPrimaryName];
        }
        
        return $ids;
    }
}