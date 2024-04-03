<?php
/**
 * File containing the {@link DBHelper_BaseCollection} class.
 * @package Application
 * @subpackage DBHelper
 * @see DBHelper_BaseCollection
 */

use Application\AppFactory;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException;
use AppUtils\ConvertHelper;
use AppUtils\NamedClosure;
use AppUtils\Request_Exception;
use DBHelper\BaseCollection\Event\AfterCreateRecordEvent;
use DBHelper\BaseCollection\Event\AfterDeleteRecordEvent;
use DBHelper\BaseCollection\Event\BeforeCreateRecordEvent;

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

    public const ERROR_IDTABLE_SAME_TABLE_NAME = 16501;
    public const ERROR_COLLECTION_HAS_NO_PARENT = 16502;
    public const ERROR_BINDING_RECORD_NOT_ALLOWED = 16503;
    public const ERROR_COLLECTION_ALREADY_HAS_PARENT = 16504;
    public const ERROR_NO_PARENT_RECORD_BOUND = 16505;
    public const ERROR_CANNOT_START_TWICE = 16506;
    public const ERROR_CANNOT_DELETE_OTHER_COLLECTION_RECORD = 16507;
    public const ERROR_INVALID_EVENT_TYPE = 16508;
    public const ERROR_CREATE_RECORD_CANCELLED = 16509;
    public const ERROR_MISSING_REQUIRED_KEYS = 16510;
    public const ERROR_FILTER_CRITERIA_CLASS_NOT_FOUND = 16511;
    public const ERROR_FILTER_SETTINGS_CLASS_NOT_FOUND = 16512;

    public const SORT_DIR_ASC = 'ASC';
    public const SORT_DIR_DESC = 'DESC';

    public const VALUE_UNDEFINED = '__undefined';

    protected string $recordIDTable;

    /**
     * @var class-string
     */
    protected string $recordClassName;
    protected string $recordSortKey;
    protected string $recordSortDir;
    protected string $recordPrimaryName;
    protected string $recordTable;
    protected ?DBHelper_BaseRecord $dummyRecord = null;

    /**
     * @var class-string
     */
    protected string $recordFiltersClassName;

    /**
     * @var class-string
     */
    protected string $recordFilterSettingsClassName;
    protected string $instanceID;
    protected bool $requiresParent = false;
    protected bool $started = false;
    protected DBHelper_BaseCollection_Keys $keys;
    protected static int $instanceCounter = 0;
    /**
     * @var array<string,string>
     */
    protected array $foreignKeys = array();

    /**
     * @var DBHelper_BaseRecord[]
     */
    protected array $records = array();

    /**
    * NOTE: classes extending this class may not create
    * constructors with parameters. The interface must
    * stay parameter-less to stay compatible with the
    * <code>DBHelper::createCollection()</code> method.
    * 
    * NOTE: Extend the <code>init()</code> method to 
    * handle any required initialization once the 
    * collection has been fully set up.
    * 
    * @see DBHelper::createCollection()
    * @see DBHelper_BaseCollection::init()
    */
    public function __construct()
    {
        self::$instanceCounter++;

        $this->instanceID = (string)self::$instanceCounter;
        $this->recordClassName = $this->getRecordClassName();
        $this->recordSortDir = $this->getRecordDefaultSortDir();
        $this->recordSortKey = $this->getRecordDefaultSortKey();
        $this->recordFiltersClassName = $this->getRecordFiltersClassName();
        $this->recordFilterSettingsClassName = $this->getRecordFilterSettingsClassName();
        $this->recordPrimaryName = $this->getRecordPrimaryName();
        $this->recordTable = $this->getRecordTableName();
        $this->requiresParent = $this->hasParentCollection();
        $this->keys = new DBHelper_BaseCollection_Keys($this);

        $this->postConstruct();

        $this->_registerKeys();
    }

    protected ?DBHelper_BaseRecord $parentRecord = null;
    
    public function bindParentRecord(DBHelper_BaseRecord $record) : void
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
                $record->getParentPrimaryName(),
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

    // region: Abstract & extensible methods

    /**
     * @return string
     */
    abstract public function getRecordClassName() : string;

    /**
     * @return string
     */
    abstract public function getRecordFiltersClassName() : string;

    /**
     * @return string
     */
    abstract public function getRecordFilterSettingsClassName() : string;

    /**
     * @return string
     */
    abstract public function getRecordDefaultSortKey() : string;

    /**
     * Retrieves the searchable columns as an associative array
     * with column name => human-readable label pairs.
     *
     * @return array<string,string>
     */
    abstract public function getRecordSearchableColumns() : array;

    /**
     * The name of the table storing the records.
     *
     * @return string
     */
    abstract public function getRecordTableName() : string;

    /**
     * The name of the database column storing the primary key.
     *
     * @return string
     */
    abstract public function getRecordPrimaryName() : string;

    /**
     * Retrieves the name of the primary key, when this collection
     * is used as a parent collection for another collection.
     *
     * Defaults to the same as {@see self::getRecordPrimaryName()},
     * but can be overridden to use a different column.
     *
     * @return string
     */
    public function getParentPrimaryName() : string
    {
        return $this->getRecordPrimaryName();
    }

    /**
     * @return string
     */
    abstract public function getRecordTypeName() : string;

    /**
     * Human-readable label of the collection, e.g. "Products".
     *
     * @return string
     */
    abstract public function getCollectionLabel() : string;

    /**
     * Human-readable label of the records, e.g. "Product".
     *
     * @return string
     */
    abstract public function getRecordLabel() : string;

    /**
     * Retrieves a list of properties available in the
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
     * @deprecated Not used anymore.
     */
    abstract public function getRecordProperties() : array;

    /**
     * @return string
     */
    public function getParentCollectionClass() : string
    {
        return '';
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
    protected function init() : void
    {
    }

    /**
     * Allows formally registering the available data keys
     * for the record's database columns. Use the internal
     * property {@see DBHelper_BaseCollection::$keys} to
     * register them.
     *
     * @return void
     */
    protected function _registerKeys() : void
    {
    }

    /**
     * Can be extended to use a different name than the primary
     * column for specifying a record ID in a request when working
     * with {@see DBHelper_BaseCollection::getByRequest()}.
     *
     * @return string
     */
    public function getRecordRequestPrimaryName() : string
    {
        return $this->getRecordPrimaryName();
    }

    // endregion

    /**
     * @return string
     */
    public function getRecordDefaultSortDir() : string
    {
        return self::SORT_DIR_ASC;
    }

    /**
     * @return bool
     */
    public function hasParentCollection() : bool
    {
        $parentClass = $this->getParentCollectionClass();

        return !empty($parentClass);
    }

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

    /**
     * Ensures a return value by throwing an exception if the collection
     * has no parent record. Check beforehand with {@see self::hasParentCollection()}.
     *
     * @return DBHelper_BaseRecord
     * @throws DBHelper_Exception {@see self::ERROR_COLLECTION_HAS_NO_PARENT}
     */
    public function requireParentRecord() : DBHelper_BaseRecord
    {
        $record = $this->getParentRecord();
        if($record !== null) {
            return $record;
        }

        throw new DBHelper_Exception(
            'No parent record available.',
            'The collection has no parent record.',
            self::ERROR_COLLECTION_HAS_NO_PARENT
        );
    }
    
    public function getInstanceID() : string
    {
        return $this->instanceID;
    }
    
   /**
    * Sets a foreign key/column that should be included in all queries.
    * This is supposed to be used internally in the constructor as needed.
    * 
    * @param string $name
    * @param string $value
    * @return $this
    */
    protected function setForeignKey(string $name, string $value) : self
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
     * Retrieves a record by its ID.
     *
     * @param integer $record_id
     * @return DBHelper_BaseRecord
     * @throws Application_Exception_DisposableDisposed
     * @throws DBHelper_Exception
     */
    public function getByID(int $record_id) : DBHelper_BaseRecord
    {
        $this->requireNotDisposed('Get a record by its ID.');

        if(isset($this->records[$record_id])) {
            return $this->records[$record_id];
        }
        
        $this->checkParentRecord();

        $class = $this->resolveRecordClass($record_id);
        $record = new $class($record_id, $this);
        $this->records[$record_id] = $record;
        
        return $record;
    }

    /**
     * @param int $record_id
     * @return class-string
     */
    protected function resolveRecordClass(int $record_id) : string
    {
        return $this->recordClassName;
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
     *
     * Uses the request parameter name as returned by {@see self::getRecordRequestPrimaryName()},
     * with {@see self::getRecordPrimaryName()} as fallback.
     *
     * @return DBHelper_BaseRecord|NULL
     * @throws Application_Exception_DisposableDisposed
     * @throws DBHelper_Exception
     * @throws Request_Exception
     */
    public function getByRequest() : ?DBHelper_BaseRecord
    {
        $request = AppFactory::createRequest();

        $this->registerRequestParams();

        $record_id = (int)$request->getParam($this->getRecordRequestPrimaryName());
        if($record_id) {
            return $this->getByID($record_id);
        }

        $record_id = (int)$request->getParam($this->getRecordPrimaryName());
        if($record_id) {
            return $this->getByID($record_id);
        }

        return null;
    }

    /**
     * @return void
     * @throws Request_Exception
     */
    public function registerRequestParams() : void
    {
        $request = AppFactory::createRequest();
        $paramName = $this->getRecordRequestPrimaryName();

        if($request->hasRegisteredParam($paramName)) {
            return;
        }

        $request->registerParam($paramName)
            ->setInteger()
            ->setCallback(array($this, 'idExists'));

        $request->registerParam($this->getRecordPrimaryName())
            ->setInteger()
            ->setCallback(array($this, 'idExists'));
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
        if($key === $this->recordPrimaryName)
        {
            return $this->getByID((int)$value);
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
     * @param integer|string|NULL $record_id
     * @return boolean
     * @throws Application_Exception_DisposableDisposed
     * @throws DBHelper_Exception
     * @throws JsonException
     */
    public function idExists($record_id) : bool
    {
        $this->requireNotDisposed('Check if record ID exists.');

        $record_id = (int)$record_id;
        
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

        return $id !== null;
    }

    /**
     * Creates a dummy record of this collection, which can
     * be used to access the API that may not be available
     * statically.
     *
     * @return DBHelper_BaseRecord
     * @throws Application_Exception|DBHelper_Exception
     */
    public function createDummyRecord() : DBHelper_BaseRecord
    {
        if(isset($this->dummyRecord)) {
            return $this->dummyRecord;
        }
        
        $this->dummyRecord = $this->getByID(DBHelper_BaseRecord::DUMMY_ID);
        
        if(isset($this->recordIDTable) && $this->recordIDTable === $this->recordTable) {
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
     *
     * @return DBHelper_BaseRecord[]
     *
     */
    public function getAll() : array
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
     * @throws Application_Exception_DisposableDisposed
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     * @throws DBHelper_Exception
     */
    public function getFilterCriteria() : DBHelper_BaseFilterCriteria
    {
        $this->requireNotDisposed('Get filter criteria');

        if(empty($this->recordFiltersClassName))
        {
            throw new DBHelper_Exception(
                'Filter criteria class not specified.',
                sprintf(
                    'No filter criteria class has been specified in collection [%s].',
                    get_class($this)
                ),
                self::ERROR_FILTER_CRITERIA_CLASS_NOT_FOUND
            );
        }

        $class = ClassHelper::requireResolvedClass($this->recordFiltersClassName);

        return ClassHelper::requireObjectInstanceOf(
            DBHelper_BaseFilterCriteria::class,
            new $class($this)
        );
    }

    /**
     * @return DBHelper_BaseFilterSettings
     *
     * @throws Application_Exception_DisposableDisposed
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     * @throws DBHelper_Exception
     */
    public function getFilterSettings() : DBHelper_BaseFilterSettings
    {
        $this->requireNotDisposed('Get filter settings.');

        if(empty($this->recordFilterSettingsClassName))
        {
            throw new DBHelper_Exception(
                'Filter settings class not specified.',
                sprintf(
                    'No filter settings class has been specified for collection [%s].',
                    get_class($this)
                ),
                self::ERROR_FILTER_SETTINGS_CLASS_NOT_FOUND
            );
        }

        $class = ClassHelper::requireResolvedClass($this->recordFilterSettingsClassName);

        return ClassHelper::requireObjectInstanceOf(
            DBHelper_BaseFilterSettings::class,
            new $class($this)
        );
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
        
        if($event !== null && $event->isCancelled())
        {
            throw new DBHelper_Exception(
                'Creating new record has been cancelled.',
                sprintf(
                    'The event has been cancelled. Reason given: %s',
                    $event->getCancelReason()
                ),
                self::ERROR_CREATE_RECORD_CANCELLED
            );
        }

        // use a special table for generating the record id?
        if(isset($this->recordIDTable)) 
        {
            $record_id = (int)DBHelper::insert(sprintf(
                "INSERT INTO
                    `%s`
                SET `%s` = DEFAULT",
                $this->recordIDTable,
                $this->recordPrimaryName
            ));
            
            $data[$this->recordPrimaryName] = $record_id;
            
            DBHelper::insertDynamic(
                $this->recordTable,
                $data
            );
        } 
        else 
        {
            $record_id = (int)DBHelper::insertDynamic(
                $this->recordTable,
                $data
            );
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

        $this->triggerAfterCreateRecord($record, $context);
        
        return $record;
    }

    /**
     * @param array<string,mixed> $data
     * @throws DBHelper_Exception
     */
    final protected function verifyData(array $data) : void
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
            $value = $data[$key->getName()] ?? null;

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
    final protected function fillDefaults(array &$data) : void
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

    // region: Event handling

    public const EVENT_BEFORE_CREATE_RECORD = 'BeforeCreateRecord';
    public const EVENT_AFTER_CREATE_RECORD = 'AfterCreateRecord';
    public const EVENT_AFTER_DELETE_RECORD = 'AfterDeleteRecord';

    /**
     * Listens to any new records being created, and allows
     * reviewing the data set before the record is added to
     * the database. It allows canceling the event if needed.
     *
     * NOTE: If the aim is to validate the record's data set,
     * you should register the data keys instead. This allows
     * finer control, with per-key validation callbacks and more.
     * See {@see DBHelper_BaseCollection::_registerKeys()}
     * for details.
     *
     * @param callable $callback
     * @return Application_EventHandler_EventableListener
     * @see BeforeCreateRecordEvent
     */
    final public function onBeforeCreateRecord(callable $callback) : Application_EventHandler_EventableListener
    {
        return $this->addEventListener(self::EVENT_BEFORE_CREATE_RECORD, $callback);
    }

    /**
     * Listens to any new records created in the collection.
     * This allows tasks to execute on the collection level
     * when records are created, as compared to the record's
     * own created event handled via {@see DBHelper_BaseRecord::onCreated()}.
     *
     * @param callable $callback
     * @return Application_EventHandler_EventableListener
     * @see AfterCreateRecordEvent
     */
    final public function onAfterCreateRecord(callable $callback) : Application_EventHandler_EventableListener
    {
        return $this->addEventListener(self::EVENT_AFTER_CREATE_RECORD, $callback);
    }

    /**
     * Listens to any records deleted from the collection.
     *
     * The callback gets an instance of the event:
     * {@see AfterDeleteRecordEvent}
     *
     * @param callable $callback
     * @return Application_EventHandler_EventableListener
     * @see AfterDeleteRecordEvent
     */
    final public function onAfterDeleteRecord(callable $callback) : Application_EventHandler_EventableListener
    {
        return $this->addEventListener(self::EVENT_AFTER_DELETE_RECORD, $callback);
    }

    protected function triggerAfterDeleteRecord(DBHelper_BaseRecord $record, DBHelper_BaseCollection_OperationContext_Delete $context) : void
    {
        $this->triggerEvent(
            self::EVENT_AFTER_DELETE_RECORD,
            array(
                $this,
                $record,
                $context
            ),
            AfterDeleteRecordEvent::class
        );
    }

    /**
     * Triggers the BeforeCreatedRecord event.
     *
     * @param array<string,mixed> $data
     *
     * @return BeforeCreateRecordEvent
     * @throws Application_Exception
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     */
    final protected function triggerBeforeCreateRecord(array $data) : ?BeforeCreateRecordEvent
    {
        $event = $this->triggerEvent(
            self::EVENT_BEFORE_CREATE_RECORD,
            array(
                $this,
                $data
            )
        );

        if($event !== null)
        {
            return ClassHelper::requireObjectInstanceOf(
                BeforeCreateRecordEvent::class,
                $event
            );
        }

        return null;
    }

    /**
     * Triggered after a new record has been created, and after the record's
     * {@see DBHelper_BaseRecord::onCreated()} method has been called.
     *
     * @param DBHelper_BaseRecord $record
     * @param DBHelper_BaseCollection_OperationContext_Create $context
     * @return AfterCreateRecordEvent
     *
     * @throws Application_Exception
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     */
    final protected function triggerAfterCreateRecord(DBHelper_BaseRecord $record, DBHelper_BaseCollection_OperationContext_Create $context) : ?AfterCreateRecordEvent
    {
        $event = $this->triggerEvent(
            self::EVENT_AFTER_CREATE_RECORD,
            array(
                $this,
                $record,
                $context
            )
        );

        if($event !== null)
        {
            return ClassHelper::requireObjectInstanceOf(
                AfterCreateRecordEvent::class,
                $event
            );
        }

        return null;
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

    // endregion

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
    protected function setIDTable(string $tableName) : void
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

        $this->triggerAfterDeleteRecord($record, $context);
    }

    /**
     * @return array<string,string|null|number|array>
     * @throws Application_Exception_DisposableDisposed
     */
    public function describe() : array
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

    protected ?string $logPrefix = null;

    public function getLogIdentifier() : string
    {
        return $this->getIdentification();
    }

    public function getIdentification() : string
    {
        if(!isset($this->logPrefix))
        {
            $this->logPrefix = ucfirst($this->getRecordTypeName()).' collection';
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
