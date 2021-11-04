<?php
/**
 * File containing the {@link DBHelper_BaseCollection} class.
 * @package Application
 * @subpackage Core
 * @see DBHelper_BaseCollection
 */

/**
 * Base class for filter criteria to be used in conjunction
 * with DB record collections. Automatically configures the
 * application filter criteria class to be used with a records
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
 * In the collection, simply specifiy the name of the class, like so:
 * 
 * <pre>
 * public function getRecordFiltersClassName()
 * {
 *     return 'MyClassName_FilterCriteria';
 * }
 * </pre>
 * 
 * @package Application
 * @subpackage Core
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class DBHelper_BaseFilterCriteria extends Application_FilterCriteria_DatabaseExtended
{
   /**
    * @var DBHelper_BaseCollection
    */
    protected $collection;

    /**
     * @var string
     */
    protected $recordTableName;

    /**
     * @var string
     */
    protected $recordPrimaryName;
    
    public function __construct(DBHelper_BaseCollection $collection)
    {
        $this->collection = $collection;
        $this->recordTableName = $collection->getRecordTableName();
        $this->recordPrimaryName = $collection->getRecordPrimaryName();

        parent::__construct($collection);

        $this->setOrderBy($collection->getRecordDefaultSortKey(), $collection->getRecordDefaultSortDir());
    }

    protected function init() : void
    {
        
    }

    protected function _initCustomColumns() : void
    {

    }
    
    protected function getSearchFields()
    {
        $fields = $this->collection->getRecordSearchableKeys();
        $result = array();
        foreach($fields as $field) {
            if(!strstr($field, '.')) {
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
    
    public function getQuery()
    {
        $this->prepareQuery();
        
        // ensure we use any required foreign key values from the collection
        $foreignKeys = $this->collection->getForeignKeys();
        foreach($foreignKeys as $key => $value) {
            $this->addWhereColumnEquals($key, $value);
        }
        
        return sprintf(
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
    
    protected function getSelect()
    {
        return array(
            sprintf(
                "%s.`%s`",
                $this->resolveTableSelector(),
                $this->collection->getRecordPrimaryName()
            )
        );
    }
    
    protected function resolveTableFrom()
    {
        $from = '`'.$this->recordTableName.'`';
        
        if(isset($this->selectAlias)) {
            $from .= ' AS '.$this->selectAlias;
        }
        
        return $from;
    }
    
    protected function resolveTableSelector()
    {
        if(isset($this->selectAlias)) {
            return $this->selectAlias;
        }
        
        return '`'.$this->recordTableName.'`';
    }
    
   /**
    * Retrieves all matching record instances.
    * @return DBHelper_BaseRecord[]
    */
    public function getItemsObjects()
    {
        $items = $this->getItemsDetailed();
        $result = array();

        foreach($items as $item)
        {
            $result[] = $item->getRecord();
        }

        return $result;
    }

    /**
     * @return DBHelper_BaseFilterCriteria_Record[]
     * @throws Application_Exception|DBHelper_Exception
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
    public function getIDs()
    {
        $items = $this->getItems();
        $ids = array();
        foreach($items as $item) {
            $ids[] = (int)$item[$this->recordPrimaryName];
        }
        
        return $ids;
    }
}