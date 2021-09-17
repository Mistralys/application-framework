<?php
/**
 * File containing the {@link DBHelper_BaseCollection} class.
 * @package Application
 * @subpackage DBHelper
 * @see DBHelper_BaseCollection
 */

use AppUtils\ConvertHelper;
use AppUtils\NamedClosure;
use AppUtils\Request_Exception;

/**
 * Base management class for a collection of database records
 * from the same table. Has methods to retrieve records, and
 * access information about records. 
 *
 * NOTE: Requires the primary key to be an integer auto_increment
 * column.
 *
 * This is meant to be extended, in conjunction with 
 * a custom record class based on the {@link DBHelper_BaseRecord}
 * class skeleton. Implement the abstract methods, and the
 * collection is ready to go.
 * 
 * @package Application
 * @subpackage DBHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class DBHelper_BaseCollection implements Application_CollectionInterface
{
    use Application_Traits_Disposable;
    use Application_Traits_Eventable;
    use Application_Traits_Loggable;

    const ERROR_IDTABLE_SAME_TABLE_NAME = 16501;
    const ERROR_COLLECTION_HAS_NO_PARENT = 16502;
    const ERROR_BINDING_RECORD_NOT_ALLOWED = 16503;
    const ERROR_COLLECTION_ALREADY_HAS_PARENT = 16504;
    const ERROR_NO_PARENT_RECORD_BOUND = 16505;
    const ERROR_CANNOT_START_TWICE = 16506;
    const ERROR_CANNOT_DELETE_OTHER_COLLECTION_RECORD = 16507;
    const ERROR_INVALID_EVENT_TYPE = 16508;
    const ERROR_CREATE_RECORD_CANCELLED = 16509;
    const ERROR_MISSING_REQUIRED_KEYS = 16510;
    const ERROR_FILTER_CRITERIA_CLASS_NOT_FOUND = 16511;
    const ERROR_FILTER_SETTINGS_CLASS_NOT_FOUND = 16512;

    const SORT_DIR_ASC = 'ASC';
    const SORT_DIR_DESC = 'DESC';

    const VALUE_UNDEFINED = '__undefined';

    /**
     * @return string
     */
    abstract public function getRecordClassName();

    /**
     * @return string
     */
    abstract public function getRecordFiltersClassName();

    /**
     * @return string
     */
    abstract public function getRecordFilterSettingsClassName();

    /**
     * @return string
     */
    abstract public function getRecordDefaultSortKey();
    
   /**
    * Retrieves the searchable columns as an associative array
    * with column name => human readable label pairs.
    * 
    * @return array[string]string
    */
    abstract public function getRecordSearchableColumns();

    /**
     * The name of the table storing the records.
     *
     * @return string
     */
    abstract public function getRecordTableName();

    /**
     * The name of the database column storing the primary key.
     *
     * @return string
     */
    abstract public function getRecordPrimaryName();

    /**
     * @return string
     */
    abstract public function getRecordTypeName();

    /**
     * Human readable label of the collection, e.g. "Products".
     *
     * @return string
     */
    abstract public function getCollectionLabel();

    /**
     * Human readable label of the records, e.g. "Product".
     *
     * @return string
     */
    abstract public function getRecordLabel();

    /**
     * Retrieves a list of properties availabable in the
     * collection's records, in the following format:
     *
     * <pre>
     * array(
     *    array(
     *        'key' => 'alias',
     *        'name' => 'Alias',
     *        'type' => 'string'
     *    )
     * )
     * </pre>
     *
     * @return array<int,array<string,string>>
     */
    abstract public function getRecordProperties();
    
   /**
    * Retrieves the name of the data grid used to 
    * display the collection items.
    * 
    * It is used to namespace the grid's filter settings,
    * which allows inheriting settings between data grids
    * when using the same name.
    *
    * The CollectionList admin screen classes automatically
    * use this name.
    *
    * @return string
    * 
    * @see Application_Traits_Admin_CollectionList
    * @see Application_Admin_Area_Mode_CollectionList
    * @see Application_Admin_Area_Mode_Submode_CollectionList
    * @see Application_Admin_Area_Mode_Submode_Action_CollectionList
    */
    public function getDataGridName() : string
    {
        return $this->getRecordTypeName().'-datagrid';
    }

    /**
     * @return string
     */
    public function getRecordDefaultSortDir()
    {
        return self::SORT_DIR_ASC;
    }

    /**
     * @return string
     */
    public function getParentCollectionClass()
    {
        return '';
    }

    /**
     * @return bool
     */
    public function hasParentCollection()
    {
        $parentClass = $this->getParentCollectionClass();

        return !empty($parentClass);
    }

    /**
     * @var string
     */
    protected $recordIDTable;

    /**
     * @var string
     */
    protected $recordClassName;

    /**
     * @var string
     */
    protected $recordSortKey;

    /**
     * @var string
     */
    protected $recordSortDir;
    
   /**
    * @var DBHelper_BaseRecord|NULL
    */
    protected $dummyRecord;

    /**
     * @var string
     */
    protected $recordFiltersClassName;

    /**
     * @var string
     */
    protected $recordFilterSettingsClassName;

    /**
     * @var string
     */
    protected $instanceID;

    /**
     * @var bool
     */
    protected $requiresParent = false;
    
   /**
    * @var Application_EventHandler
    */
    protected $eventHandler;

    /**
     * @var DBHelper_BaseCollection_Keys
     */
    protected $keys;

    protected static $instanceCounter = 0;
    
   /**
    * NOTE: classes extending this class may not create
    * constructors with parameters. The interface must
    * stay parameter-less to stay compatible with the
    * <code>DBHelper::createCollection()</code> method.
    * 
    * NOTE: Extend the <code>init()</code> method to 
    * handle any required initialization once the 
    * collectiopn has been fully set up.
    * 
    * @see DBHelper::createCollection()
    * @see DBHelper_BaseCollection::init()
    */
    public function __construct()
    {
        self::$instanceCounter++;

        $this->instanceID = strval(self::$instanceCounter);
        $this->recordClassName = $this->getRecordClassName();
        $this->recordSortDir = $this->getRecordDefaultSortDir();
        $this->recordSortKey = $this->getRecordDefaultSortKey();
        $this->recordFiltersClassName = $this->getRecordFiltersClassName();
        $this->recordFilterSettingsClassName = $this->getRecordFilterSettingsClassName();
        $this->recordPrimaryName = $this->getRecordPrimaryName();
        $this->recordTable = $this->getRecordTableName();
        $this->requiresParent = $this->hasParentCollection();
        $this->eventHandler = new Application_EventHandler();
        $this->keys = new DBHelper_BaseCollection_Keys($this);

        $this->postConstruct();

        $this->_registerKeys();
    }

    /**
     * Called after the class has been initialized, but
     * before the keys are registered.
     */
    protected function postConstruct() : void
    {

    }
    
   /**
    * Can be overwritten to initialize needed tasks and properties.
    */
    protected function init()
    {
    }

    protected function _registerKeys() : void
    {
    }
    
   /**
    * @var DBHelper_BaseRecord|NULL
    */
    protected $parentRecord = null;
    
    public function bindParentRecord(DBHelper_BaseRecord $record)
    {
        if(isset($this->parentRecord)) {
            throw new DBHelper_Exception(
                'Record already bound',
                sprintf(
                    'Cannot bind record [%s, ID %s], already bound to record [%s, ID %s].',
                    get_class($record),
                    $record->getID(),
                    get_class($this->parentRecord),
                    $this->parentRecord->getID()
                ),
                self::ERROR_COLLECTION_ALREADY_HAS_PARENT
            );
        }
        
        if($this->hasParentCollection())
        {
            $this->parentRecord = $record; 
            $this->setForeignKey(
                $record->getRecordPrimaryName(), 
                (string)$record->getID()
            );

            $callback = array($this, 'callback_parentRecordDisposed');

            $this->parentRecord->onDisposed(NamedClosure::fromClosure(
                Closure::fromCallable($callback),
                ConvertHelper::callback2string($callback)
            ));
            return;
        }
        
        throw new DBHelper_Exception(
            'Binding a record is not allowed',
            sprintf(
                'The collection [%s] is not configured as a subcollection, and thus cannot be bound to a specific record. Tried binding to a [%s].',
                get_class($this),
                get_class($record)
            ),
            self::ERROR_BINDING_RECORD_NOT_ALLOWED
        );
    }

    /**
     * Fetch a fresh instance of the parent record of the
     * collection when that record instance has been disposed.
     * If the record does not exist anymore, no changes are
     * made - an exception will be thrown if the record is
     * accessed.
     */
    private function callback_parentRecordDisposed() : void
    {
        $collection = $this->parentRecord->getCollection();

        if($collection->idExists($this->parentRecord->getID()))
        {
            $this->parentRecord = $collection->getByID($this->parentRecord->getID());
            return;
        }

        $this->dispose();
    }
    
    protected $started = false;
    
   /**
    * Called by the DBHelper once the collection configuration 
    * has been completed.
    * 
    * @throws DBHelper_Exception
    */
    public function setupComplete() : void
    {
        if($this->started) 
        {
            throw new DBHelper_Exception(
                'Cannot start a collection twice.',
                sprintf(
                    'The collection [%s] has already been started, and may not be started again.',
                    get_class($this)
                ),
                self::ERROR_CANNOT_START_TWICE
            );
        }
        
        $this->started = true;

        $this->init();
    }
    
   /**
    * This is only available if the collection has a parent collection.
    * 
    * @return DBHelper_BaseRecord|NULL
    */
    public function getParentRecord() : ?DBHelper_BaseRecord
    {
        return $this->parentRecord;
    }
    
    public function getInstanceID() : string
    {
        return $this->instanceID;
    }
    
   /**
    * @var array<string,string>
    */
    protected $foreignKeys = array();
    
   /**
    * Sets a foreign key/column that should be included in all queries.
    * This is supposed to be used internally in the constructor as needed.
    * 
    * @param string $name
    * @param string $value
    * @return $this
    */
    protected function setForeignKey(string $name, string $value)
    {
        $this->foreignKeys[$name] = $value;
        return $this;
    }
    
   /**
    * Retrieves the foreign keys that should be included in
    * all queries, as an associative array with key => value pairs.
    * 
    * @return array<string,string>
    */
    public function getForeignKeys() : array
    {
        return $this->foreignKeys;
    }

    /**
     * @return string[]
     */
    public function getRecordSearchableKeys() : array
    {
        $columns = $this->getRecordSearchableColumns();
        return array_keys($columns);
    }

    /**
     * @return string[]
     */
    public function getRecordSearchableLabels() : array
    {
        $columns = $this->getRecordSearchableColumns();
        return array_values($columns);
    }
    
   /**
    * @var DBHelper_BaseRecord[]
    */
    protected $records = array();

    /**
     * Retrieves a record by its ID.
     *
     * @param integer $record_id
     * @return DBHelper_BaseRecord
     * @throws Application_Exception_DisposableDisposed
     * @throws DBHelper_Exception
     */
    public function getByID(int $record_id)
    {
        $this->requireNotDisposed('Get a record by its ID.');

        if(isset($this->records[$record_id])) {
            return $this->records[$record_id];
        }
        
        $this->checkParentRecord();

        $record = new $this->recordClassName($record_id, $this);
        $this->records[$record_id] = $record;
        
        return $record;
    }

    /**
     * Refreshes all loaded record's data from the database.
     *
     * @throws Application_Exception
     * @throws Application_Exception_DisposableDisposed
     * @throws DBHelper_Exception
     */
    public function refreshRecordsData() : void
    {
        $this->requireNotDisposed('Refresh records data from DB.');

        $this->log(sprintf('Refreshing data for [%s] records.', count($this->records)));

        foreach($this->records as $record)
        {
            $record->refreshData();
        }
    }

   /**
    * Resets the internal records instance cache.
    * Forces all records to be fetched anew from the
    * database as requested.
    *
    * NOTE: Records that were already loaded are disposed,
    * and may not be used anymore.
    */
    public function resetCollection() : void
    {
        $this->log(sprintf('Resetting the collection. [%s] records were loaded.', count($this->records)));

        foreach($this->records as $record)
        {
            $record->dispose();
        }

        $this->records = array();

        // Also refresh the parent record, in case that collection
        // has been reset as well.
        if(isset($this->parentRecord))
        {
            $this->parentRecord = $this->parentRecord->getCollection()->getByID($this->parentRecord->getID());
        }
    }

    /**
     * @throws DBHelper_Exception
     * @see DBHelper_BaseCollection::ERROR_NO_PARENT_RECORD_BOUND
     */
    protected function checkParentRecord() : void
    {
        if($this->requiresParent !== true) {
            return;
        }
        
        if($this->parentRecord !== null) {
            return;
        }
        
        throw new DBHelper_Exception(
            'No parent record bound',
            sprintf(
                'Collections of type [%s] need a parent record to be set.',
                get_class($this)
            ),
            self::ERROR_NO_PARENT_RECORD_BOUND
        );
    }

    /**
     * Attempts to retrieve a record by its ID as specified in the request.
     * @return DBHelper_BaseRecord|NULL
     * @throws Application_Exception_DisposableDisposed
     * @throws DBHelper_Exception
     * @throws Request_Exception
     */
    public function getByRequest()
    {
        $request = Application_Request::getInstance();
        
        $record_id = $request->registerParam($this->getRecordPrimaryName())
        ->setInteger()
        ->setCallback(array($this, 'idExists'))
        ->get();
        
        if($record_id) {
            return $this->getByID($record_id);
        }
        
        return null;
    }

    /**
     * Retrieves a single record by a specific record key.
     * Note that if the key is not unique, the first one
     * in the result set is used, using the default sorting
     * key.
     *
     * @param string $key
     * @param string $value
     * @return DBHelper_BaseRecord|NULL
     * @throws Application_Exception_DisposableDisposed
     * @throws DBHelper_Exception
     */
    public function getByKey(string $key, string $value) : ?DBHelper_BaseRecord
    {
        if($key == $this->recordPrimaryName) 
        {
            return $this->getByID(intval($value));
        }
        
        $where = $this->foreignKeys;
        $where[$key] = $value;
        
        $query = sprintf(
            "SELECT
                `%s`
            FROM
                `%s`
            WHERE
                %s
            ORDER BY
                `%s` %s
            LIMIT 
                0,1",
            $this->recordPrimaryName,
            $this->recordTable,
            DBHelper::buildWhereFieldsStatement($where),
            $this->recordSortKey,
            $this->recordSortDir
        );
        
        $id = DBHelper::fetchKeyInt(
            $this->recordPrimaryName,
            $query,
            $where
        );
        
        if($id > 0) 
        {
            return $this->getByID($id);
        }
        
        return null;
    }

    /**
     * Checks whether a record with the specified ID exists in the database.
     *
     * @param integer $record_id
     * @return boolean
     * @throws Application_Exception_DisposableDisposed
     */
    public function idExists($record_id) : bool
    {
        $this->requireNotDisposed('Check if record ID exists.');

        $record_id = intval($record_id);
        
        if(isset($this->records[$record_id])) {
            return true;
        }
        
        $where = $this->foreignKeys;
        $where[$this->recordPrimaryName] = $record_id;
        
        $query = sprintf( 
            "SELECT
                `%s`
            FROM
                `%s`
            WHERE
                %s",
            $this->recordPrimaryName,
            $this->recordTable,
            DBHelper::buildWhereFieldsStatement($where)
        );

        $id = DBHelper::fetchKey(
            $this->recordPrimaryName, 
            $query,
            $where
        );
        
        if($id !== null) {
            return true;
        }
        
        return false;
    }

    /**
     * @var string
     */
    protected $recordPrimaryName;

    /**
     * @var string
     */
    protected $recordTable;

    /**
     * Creates a dummy record of this collection, which can
     * be used to access the API that may not be available
     * statically.
     *
     * @return DBHelper_BaseRecord
     * @throws Application_Exception|DBHelper_Exception
     */
    public function createDummyRecord()
    {
        if(isset($this->dummyRecord)) {
            return $this->dummyRecord;
        }
        
        $this->dummyRecord = $this->getByID(DBHelper_BaseRecord::DUMMY_ID);
        
        if(isset($this->recordIDTable) && $this->recordIDTable == $this->recordTable) {
            throw new Application_Exception(
                'Duplicate DB collection tables',
                sprintf(
                    'The DBHelper collection [%s] has the same table [%s] defined as record table and ID table.',
                    get_class($this),
                    $this->recordIDTable
                ),
                self::ERROR_IDTABLE_SAME_TABLE_NAME
            );
        } 
        
        return $this->dummyRecord;
    }

    /**
     * Retrieves all records from the database, ordered by the default sorting key.
     * @return DBHelper_BaseRecord[]
     *
     * @throws Application_Exception_DisposableDisposed
     * @throws Application_Exception_UnexpectedInstanceType
     * @throws DBHelper_Exception
     */
    public function getAll()
    {
        return $this->getFilterCriteria()->getItemsObjects();
    }

    /**
     * Counts the amount of records in total.
     * @return int
     * @throws Application_Exception
     * @throws Application_Exception_DisposableDisposed
     * @throws DBHelper_Exception
     */
    public function countRecords() : int
    {
        $this->requireNotDisposed('Count the amount of records');

        return $this->getFilterCriteria()->countItems();
    }

    /**
     * Creates the filter criteria for this collection of records,
     * which is used to query the records.
     *
     * @return DBHelper_BaseFilterCriteria
     * @throws Application_Exception_UnexpectedInstanceType
     * @throws DBHelper_Exception|Application_Exception_DisposableDisposed
     */
    public function getFilterCriteria() : DBHelper_BaseFilterCriteria
    {
        $this->requireNotDisposed('Get filter criteria');

        if(empty($this->recordFiltersClassName) || !class_exists($this->recordFiltersClassName))
        {
            throw new DBHelper_Exception(
                'Filter criteria class not found.',
                sprintf(
                    'Filter criteria class [%s] not found for collection [%s].',
                    $this->recordFiltersClassName,
                    get_class($this)
                ),
                self::ERROR_FILTER_CRITERIA_CLASS_NOT_FOUND
            );
        }

        $filters = new $this->recordFiltersClassName($this);

        if($filters instanceof DBHelper_BaseFilterCriteria)
        {
            return $filters;
        }

        throw new Application_Exception_UnexpectedInstanceType(DBHelper_BaseFilterCriteria::class, $filters);
    }

    /**
     * @return DBHelper_BaseFilterSettings
     * @throws Application_Exception_UnexpectedInstanceType
     * @throws DBHelper_Exception|Application_Exception_DisposableDisposed
     */
    public function getFilterSettings() : DBHelper_BaseFilterSettings
    {
        $this->requireNotDisposed('Get filter settings.');

        if(empty($this->recordFilterSettingsClassName) || !class_exists($this->recordFilterSettingsClassName))
        {
            throw new DBHelper_Exception(
                'Filter settings class not found.',
                sprintf(
                    'Filter settings class [%s] not found for collection [%s].',
                    $this->recordFilterSettingsClassName,
                    get_class($this)
                ),
                self::ERROR_FILTER_SETTINGS_CLASS_NOT_FOUND
            );
        }

        $filters = new $this->recordFilterSettingsClassName($this);

        if($filters instanceof DBHelper_BaseFilterSettings)
        {
            return $filters;
        }

        throw new Application_Exception_UnexpectedInstanceType(DBHelper_BaseFilterSettings::class, $filters);
    }

    /**
     * Creates a new record with the specified data.
     *
     * NOTE: This does not do any kind of validation,
     * you have to ensure that the required keys are
     * all present in the data set.
     *
     * NOTE: It is possible to use the onBeforeCreateRecord()
     * method to verify the data, and cancel the event
     * as needed.
     *
     * @param array $data
     * @param bool $silent Whether to not execute any events after
     *                       creating the record. The _onCreated() method
     *                       will still be called, but the context will
     *                       reflect the silent flag to manually handle the
     *                       situation.
     * @param array<string,mixed> $options Options that are passed on to the record's
     *                       onCreated() method, and which can be used for
     *                       custom initialization routines.
     * @return DBHelper_BaseRecord
     * @throws Application_Exception_DisposableDisposed
     * @throws DBHelper_Exception
     */
    public function createNewRecord(array $data=array(), bool $silent=false, array $options=array())
    {
        $this->requireNotDisposed('Create a new record');

        $data = array_merge($data, $this->foreignKeys);

        $this->fillDefaults($data);
        $this->verifyData($data);

        DBHelper::requireTransaction('Create a new '.$this->getRecordTypeName());

        $this->log('Creating a new record.');

        $event = $this->triggerBeforeCreateRecord($data);
        
        if($event->isCancelled())
        {
            throw new DBHelper_Exception(
                'Creating new record has been cancelled.',
                sprintf(
                    'The event has been cancelled. Reason given: '.$event->getCancelReason()
                ),
                self::ERROR_CREATE_RECORD_CANCELLED
            );
        }
        
        // use a special table for generating the record id?
        if(isset($this->recordIDTable)) 
        {
            $record_id = intval(DBHelper::insert(sprintf(
                "INSERT INTO
                    `%s`
                SET `%s` = DEFAULT",
                $this->recordIDTable,
                $this->recordPrimaryName
            )));
            
            $data[$this->recordPrimaryName] = $record_id;
            
            DBHelper::insertDynamic(
                $this->recordTable,
                $data
            );
        } 
        else 
        {
            $record_id = intval(DBHelper::insertDynamic(
                $this->recordTable, 
                $data
            ));
        }

        $this->log(sprintf('Created with ID [%s].', $record_id));

        $record = $this->getByID($record_id);
        
        $context = new DBHelper_BaseCollection_OperationContext_Create($record);
        $context->setOptions($options);

        if($silent)
        {
            $context->makeSilent();
        }

        $record->onCreated($context);
        
        return $record;
    }

    /**
     * @param array<string,mixed> $data
     * @throws DBHelper_Exception
     */
    protected final function verifyData(array $data) : void
    {
        $keys = $this->keys->getRequired();
        $missing = array();

        foreach($keys as $key)
        {
            $name = $key->getName();

            if(!array_key_exists($name, $data))
            {
                $missing[] = $name;
            }
        }

        if(!empty($missing))
        {
            throw new DBHelper_Exception(
                'Missing required keys in record data set',
                sprintf(
                    'The data keys [%s] are missing in the [%s] record data set.',
                    implode(', ', $missing),
                    $this->getRecordTypeName()
                ),
                self::ERROR_MISSING_REQUIRED_KEYS
            );
        }

        $keys = $this->keys->getAll();

        foreach($keys as $key)
        {
            $value = $data[$key->getName()];

            $key->validate($value, $data);
        }
    }

    /**
     * Fills data keys with default values when they have
     * not been added in the data set, and if the key has
     * a default value specified.
     *
     * @param array $data
     */
    protected final function fillDefaults(array &$data) : void
    {
        $keys = $this->keys->getAll();

        foreach($keys as $key)
        {
            $name = $key->getName();

            if(array_key_exists($name, $data)) {
                continue;
            }

            $value = $this->resolveDefaultValue($key, $data);

            if($value !== self::VALUE_UNDEFINED)
            {
                $data[$name] = $value;
            }
        }
    }

    private function resolveDefaultValue(DBHelper_BaseCollection_Keys_Key $key, array $data)
    {
        if($key->hasGenerator())
        {
            return $key->generateValue($data);
        }

        if($key->hasDefault())
        {
            return $key->getDefault();
        }

        return self::VALUE_UNDEFINED;
    }

    /**
    * Adds a listener to the BeforeCreateRecord event.
    * 
    * @param callable $callback
    * @param string $sourceLabel
    */
    public final function onBeforeCreateRecord($callback, string $sourceLabel='')
    {
        $this->eventHandler->addListener(
            'BeforeCreateRecord', 
            $callback,
            $sourceLabel
        );
    }
    
   /**
    * Triggers the BeforeCreatedRecord event.
    * 
    * @throws DBHelper_Exception
    * @return DBHelper_BaseCollection_Event_BeforeCreateRecord
    */
    protected final function triggerBeforeCreateRecord(array $data) : DBHelper_BaseCollection_Event_BeforeCreateRecord
    {
        $event = $this->eventHandler->trigger(
            'BeforeCreateRecord',
            array(
                $this,
                $data
            ),
            'DBHelper_BaseCollection_Event_BeforeCreateRecord'
        );
        
        if($event instanceof DBHelper_BaseCollection_Event_BeforeCreateRecord)
        {
            return $event;
        }
        
        throw new DBHelper_Exception(
            'Invalid event type',
            sprintf(
                'Expected class of type [%s], given [%s].',
                DBHelper_BaseCollection_Event_BeforeCreateRecord::class,
                \AppUtils\parseVariable($event)->enableType()->toString()
            ),
            self::ERROR_INVALID_EVENT_TYPE
        );
    }

    /**
     * Checks whether a specific column value exists
     * in any of the collection's records.
     *
     * @param string $keyName
     * @param string $value
     * @return integer|boolean The record's ID, or false if not found.
     * @throws Application_Exception_DisposableDisposed
     */
    public function recordKeyValueExists(string $keyName, string $value)
    {
        $this->requireNotDisposed('Check if a record key value exists.');

        $primary = $this->getRecordPrimaryName();
    
        $where = $this->foreignKeys;
        $where[$keyName] = $value;
        
        $query = sprintf(
            "SELECT
                `%s`
            FROM
                `%s`
            WHERE
                %s",
            $primary,
            $this->getRecordTableName(),
            DBHelper::buildWhereFieldsStatement($where)
        );
    
        $id = DBHelper::fetchKeyInt(
            $primary,
            $query,
            $where
        );
    
        if($id > 0) 
        {
            return $id;
        }
    
        return false;
    }

    /**
     * @param string $tableName
     */
    protected function setIDTable($tableName)
    {
        $this->recordIDTable = $tableName;
    }

    /**
     * Deletes a record.
     *
     * @param DBHelper_BaseRecord $record
     * @param bool $silent Whether to delete the record silently, without processing events afterwards.
     *                      The _onDeleted method will still be called for cleanup tasks, but the context
     *                      will reflect the silent state. The method implementation must check this manually.
     * @throws Application_Exception_DisposableDisposed
     * @throws DBHelper_Exception
     */
    public function deleteRecord(DBHelper_BaseRecord $record, bool $silent=false) : void
    {
        $this->requireNotDisposed('Delete a record.');

        $this->log(sprintf(
            'Deleting the record [%s] | Silent mode: [%s].',
            $record->getID(),
            ConvertHelper::boolStrict2string($silent)
        ));

        DBHelper::requireTransaction('Delete a record');
        
        if(!is_a($record, $this->getRecordClassName(), true))
        {
            throw new DBHelper_Exception(
                'Cannot delete a record of another collection',
                sprintf(
                    'The record [%s] is not an instance of [%s].',
                    get_class($record),
                    $this->getRecordClassName()
                ),
                self::ERROR_CANNOT_DELETE_OTHER_COLLECTION_RECORD
            );
        }
        
        $record_id = $record->getID();
        
        $where = $this->foreignKeys;
        $where[$this->recordPrimaryName] = $record_id;

        if(isset($this->records[$record_id])) 
        {
            unset($this->records[$record_id]);
        }

        $context = new DBHelper_BaseCollection_OperationContext_Delete($record);

        $record->onBeforeDelete($context);

        DBHelper::deleteRecords(
            $this->recordTable,
            $where
        );
        
        if($silent)
        {
            $context->makeSilent();
        }
        
        $record->onDeleted($context);
    }
    
    public function describe()
    {
        $this->requireNotDisposed('Describe the collection.');

        return array(
            'class' => get_class($this),
            'recordClassName' => $this->getRecordClassName(),
            'defaultSortDir' => $this->getRecordDefaultSortDir(),
            'defaultSortKey' => $this->getRecordDefaultSortKey(),
            'filtersClassName' => $this->getRecordFiltersClassName(),
            'filterSettingsClassName' => $this->getRecordFilterSettingsClassName(),
            'primaryName' => $this->getRecordPrimaryName(),
            'searchableColumns' => $this->getRecordSearchableColumns(),
            'searchableKeys' => $this->getRecordSearchableKeys(),
            'collectionLabel' => $this->getCollectionLabel(),
            'recordLabel' => $this->getRecordLabel(),
            'recordProperties' => $this->getRecordProperties(),
            'tableName' => $this->getRecordTableName(),
            'typeName' => $this->getRecordTypeName()
        );
    }

    /**
     * @var string
     */
    protected $logPrefix;

    public function getLogIdentifier() : string
    {
        return $this->getIdentification();
    }

    public function getIdentification() : string
    {
        if(!isset($this->logPrefix))
        {
            $this->logPrefix = ucfirst($this->getRecordTypeName()).' collection | ';
        }

        return $this->logPrefix;
    }

    public function isRecordLoaded(int $recordID) : bool
    {
        return isset($this->records[$recordID]);
    }

     protected function _dispose() : void
     {
         unset($this->dummyRecord);
     }

     public function getChildDisposables() : array
     {
         $disposables = $this->records;
         $disposables[] = $this->keys;

         return $disposables;
     }
}
