<?php
/**
 * @package Application
 * @subpackage DBHelper
 */

declare(strict_types=1);

use Application\AppFactory;
use Application\Exception\DisposableDisposedException;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException;
use AppUtils\ConvertHelper;
use DBHelper\Admin\Traits\RecordListScreenTrait;
use DBHelper\BaseCollection\BaseChildCollection;
use DBHelper\BaseCollection\DBHelperCollectionException;
use DBHelper\BaseCollection\DBHelperCollectionInterface;
use DBHelper\BaseCollection\Event\AfterCreateRecordEvent;
use DBHelper\BaseCollection\Event\AfterDeleteRecordEvent;
use DBHelper\BaseCollection\Event\BeforeCreateRecordEvent;

/**
 * Base management class for a collection of database records
 * from the same table. Has methods to retrieve records, and
 * access information about records. 
 *
 * > NOTE: Requires the primary key to be an integer auto_increment
 * > column.
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
abstract class DBHelper_BaseCollection implements DBHelperCollectionInterface
{
    use Application_Traits_Disposable;
    use Application_Traits_Eventable;
    use Application_Traits_Loggable;

    public const string SORT_DIR_ASC = 'ASC';
    public const string SORT_DIR_DESC = 'DESC';

    public const string VALUE_UNDEFINED = '__undefined';

    protected ?string $recordIDTable;

    /**
     * @var class-string<DBHelper_BaseRecord>
     */
    protected string $recordClassName;
    protected string $recordSortKey;
    protected string $recordSortDir;
    protected string $recordPrimaryName;
    protected string $recordTable;
    protected ?DBHelper_BaseRecord $dummyRecord = null;

    /**
     * @var class-string<DBHelper_BaseFilterCriteria>
     */
    protected string $recordFiltersClassName;

    /**
     * @var class-string<DBHelper_BaseFilterSettings>
     */
    protected string $recordFilterSettingsClassName;
    protected string $instanceID;
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
    private ?string $recordIDTablePrimaryName = null;

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
        $this->keys = new DBHelper_BaseCollection_Keys($this);

        $this->postConstruct();

        $this->_registerKeys();
    }

    // region: Extensible methods

    /**
     * @param int $record_id
     * @return class-string<DBHelper_BaseRecord>
     */
    protected function resolveRecordClass(int $record_id) : string
    {
        return $this->recordClassName;
    }

    /**
     * Ensures that all prerequisites are met to instantiate
     * a record of the collection.
     *
     * @return void
     */
    protected function checkRecordPrerequisites() : void
    {

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

    public function getRecordRequestPrimaryName() : string
    {
        return $this->getRecordPrimaryName();
    }

    // endregion

    public function getRecordDefaultSortDir() : string
    {
        return self::SORT_DIR_ASC;
    }

    final public function setupComplete() : void
    {
        if($this->started) 
        {
            throw new DBHelperCollectionException(
                'Cannot start a collection twice.',
                sprintf(
                    'The collection [%s] has already been started, and may not be started again.',
                    get_class($this)
                ),
                DBHelperCollectionException::ERROR_CANNOT_START_TWICE
            );
        }
        
        $this->started = true;

        $this->init();
    }
    
    public function getParentRecord() : ?DBHelper_BaseRecord
    {
        return null;
    }

    final public function getInstanceID() : string
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
    final protected function setForeignKey(string $name, string $value) : self
    {
        $this->foreignKeys[$name] = $value;

        $this->invalidateMemoryCache();

        return $this;
    }
    
    public function getForeignKeys() : array
    {
        return $this->foreignKeys;
    }

    public function getRecordSearchableKeys() : array
    {
        $columns = $this->getRecordSearchableColumns();
        return array_keys($columns);
    }

    public function getRecordSearchableLabels() : array
    {
        $columns = $this->getRecordSearchableColumns();
        return array_values($columns);
    }

    final public function getDataGridName() : string
    {
        return $this->getRecordTypeName().'-datagrid';
    }

    public function getByID($record_id) : DBHelper_BaseRecord
    {
        $record_id = (int)$record_id;

        $this->requireNotDisposed('Get a record by its ID.');

        if(isset($this->records[$record_id])) {
            return $this->records[$record_id];
        }
        
        $this->checkRecordPrerequisites();

        $class = $this->resolveRecordClass($record_id);
        $record = new $class($record_id, $this);
        $this->records[$record_id] = $record;
        
        return $record;
    }

    final public function refreshRecordsData() : void
    {
        $this->requireNotDisposed('Refresh records data from DB.');

        $this->log(sprintf('Refreshing data for [%s] records.', count($this->records)));

        foreach($this->records as $record)
        {
            $record->refreshData();
        }
    }

    public function resetCollection() : self
    {
        $this->log(sprintf('Resetting the collection. [%s] records were loaded.', count($this->records)));

        foreach($this->records as $record)
        {
            $record->dispose();
        }

        $this->records = array();

        $this->invalidateMemoryCache();

        return $this;
    }

    private function invalidateMemoryCache() : void
    {
        $this->log('Invalidating the internal memory cache.');

        $this->allRecords = null;
        $this->idLookup = array();
    }

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

    final public function registerRequestParams() : void
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
     * @var array<int,bool>
     */
    private array $idLookup = array();

    final public function idExists($record_id) : bool
    {
        $this->requireNotDisposed('Check if record ID exists.');

        $record_id = (int)$record_id;

        if(isset($this->idLookup[$record_id])) {
            return $this->idLookup[$record_id];
        }

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

        $this->idLookup[$record_id] = $id !== null;

        return $this->idLookup[$record_id];
    }

    public function createDummyRecord() : DBHelper_BaseRecord
    {
        if(isset($this->dummyRecord)) {
            return $this->dummyRecord;
        }
        
        $this->dummyRecord = $this->getByID(DBHelper_BaseRecord::STUB_ID);
        
        if(isset($this->recordIDTable) && $this->recordIDTable === $this->recordTable) {
            throw new Application_Exception(
                'Duplicate DB collection tables',
                sprintf(
                    'The DBHelper collection [%s] has the same table [%s] defined as record table and ID table.',
                    get_class($this),
                    $this->recordIDTable
                ),
                DBHelperCollectionException::ERROR_IDTABLE_SAME_TABLE_NAME
            );
        } 
        
        return $this->dummyRecord;
    }

    /**
     * @var DBHelper_BaseRecord[]|null
     */
    private ?array $allRecords = null;

    /**
     * Retrieves all records from the database, ordered by the default sorting key.
     *
     * @return DBHelper_BaseRecord[]
     * @cached
     */
    public function getAll() : array
    {
        if(isset($this->allRecords)) {
            return $this->allRecords;
        }

        $this->allRecords = $this->getFilterCriteria()->getItemsObjects();

        return $this->allRecords;
    }

    final public function countRecords() : int
    {
        $this->requireNotDisposed('Count the amount of records');

        return $this->getFilterCriteria()->countItems();
    }

    public function getFilterCriteria() : DBHelper_BaseFilterCriteria
    {
        $this->requireNotDisposed('Get filter criteria');

        if(empty($this->recordFiltersClassName))
        {
            throw new DBHelperCollectionException(
                'Filter criteria class not specified.',
                sprintf(
                    'No filter criteria class has been specified in collection [%s].',
                    get_class($this)
                ),
                DBHelperCollectionException::ERROR_FILTER_CRITERIA_CLASS_NOT_FOUND
            );
        }

        $class = ClassHelper::requireResolvedClass($this->recordFiltersClassName);

        return ClassHelper::requireObjectInstanceOf(
            DBHelper_BaseFilterCriteria::class,
            new $class($this)
        );
    }

    public function getFilterSettings() : DBHelper_BaseFilterSettings
    {
        $this->requireNotDisposed('Get filter settings.');

        if(empty($this->recordFilterSettingsClassName))
        {
            throw new DBHelperCollectionException(
                'Filter settings class not specified.',
                sprintf(
                    'No filter settings class has been specified for collection [%s].',
                    get_class($this)
                ),
                DBHelperCollectionException::ERROR_FILTER_SETTINGS_CLASS_NOT_FOUND
            );
        }

        $class = ClassHelper::requireResolvedClass($this->recordFilterSettingsClassName);

        return ClassHelper::requireObjectInstanceOf(
            DBHelper_BaseFilterSettings::class,
            new $class($this)
        );
    }

    public const string OPTION_CUSTOM_RECORD_ID = '__custom_record_id';

    public function createNewRecord(array $data=array(), bool $silent=false, array $options=array()) : DBHelper_BaseRecord
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
            throw new DBHelperCollectionException(
                'Creating new record has been cancelled.',
                sprintf(
                    'The event has been cancelled. Reason given: %s',
                    $event->getCancelReason()
                ),
                DBHelperCollectionException::ERROR_CREATE_RECORD_CANCELLED
            );
        }

        $customID = null;
        if(isset($options[self::OPTION_CUSTOM_RECORD_ID])) {
            $customID = (int)$options[self::OPTION_CUSTOM_RECORD_ID];
        }

        // use a special table for generating the record id?
        if(isset($this->recordIDTable))
        {
            $primary = 'DEFAULT';
            if($customID !== null) {
                $primary = $customID;
            }

            $record_id = (int)DBHelper::insert(sprintf(
                "INSERT INTO
                    `%s`
                SET `%s` = %s",
                $this->recordIDTable,
                $this->recordIDTablePrimaryName,
                $primary
            ));
            
            $data[$this->recordPrimaryName] = $record_id;
            
            DBHelper::insertDynamic(
                $this->recordTable,
                $data
            );
        } 
        else 
        {
            if($customID !== null) {
                $data[$this->recordPrimaryName] = $customID;
            }

            $record_id = (int)DBHelper::insertDynamic(
                $this->recordTable,
                $data
            );
        }

        $this->log(sprintf('Created with ID [%s].', $record_id));

        $this->idLookup[$record_id] = true;
        $this->allRecords = null;

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

    final public function hasRecordIDTable(): bool
    {
        return isset($this->recordIDTable);
    }

    /**
     * @param array<string,mixed> $data
     * @throws DBHelperCollectionException
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
            throw new DBHelperCollectionException(
                'Missing required keys in record data set',
                sprintf(
                    'The data keys [%s] are missing in the [%s] record data set.',
                    implode(', ', $missing),
                    $this->getRecordTypeName()
                ),
                DBHelperCollectionException::ERROR_MISSING_REQUIRED_KEYS
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

    public const string EVENT_BEFORE_CREATE_RECORD = 'BeforeCreateRecord';
    public const string EVENT_AFTER_CREATE_RECORD = 'AfterCreateRecord';
    public const string EVENT_AFTER_DELETE_RECORD = 'AfterDeleteRecord';

    final public function onBeforeCreateRecord(callable $callback) : Application_EventHandler_EventableListener
    {
        return $this->addEventListener(self::EVENT_BEFORE_CREATE_RECORD, $callback);
    }

    final public function onAfterCreateRecord(callable $callback) : Application_EventHandler_EventableListener
    {
        return $this->addEventListener(self::EVENT_AFTER_CREATE_RECORD, $callback);
    }

    final public function onAfterDeleteRecord(callable $callback) : Application_EventHandler_EventableListener
    {
        return $this->addEventListener(self::EVENT_AFTER_DELETE_RECORD, $callback);
    }

    final protected function triggerAfterDeleteRecord(DBHelper_BaseRecord $record, DBHelper_BaseCollection_OperationContext_Delete $context) : void
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
     * @return BeforeCreateRecordEvent|NULL
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
     * @return AfterCreateRecordEvent|NULL
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
            ),
            AfterCreateRecordEvent::class
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

    // endregion

    final public function recordKeyValueExists(string $keyName, string $value) : int|bool
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
     * Sets the table that should be used to generate new record
     * primary key values. A new row is inserted here when
     * adding new records to the collection, to determine their
     * primary key value.
     *
     * It must be a table with an auto-increment key and no other
     * mandatory columns.
     *
     * @param string $tableName
     * @param string|NULL $primaryName Optional: Use if this is not the collection's primary key name.
     */
    final protected function setIDTable(string $tableName, ?string $primaryName=null) : void
    {
        $this->recordIDTable = $tableName;
        $this->recordIDTablePrimaryName = $primaryName ?? $this->recordPrimaryName;
    }

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
            throw new DBHelperCollectionException(
                'Cannot delete a record of another collection',
                sprintf(
                    'The record [%s] is not an instance of [%s].',
                    get_class($record),
                    $this->getRecordClassName()
                ),
                DBHelperCollectionException::ERROR_CANNOT_DELETE_OTHER_COLLECTION_RECORD
            );
        }
        
        $record_id = $record->getID();
        
        $where = $this->foreignKeys;
        $where[$this->recordPrimaryName] = $record_id;

        $this->idLookup[$record_id] = false;
        $this->allRecords = null;

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
     * @throws DisposableDisposedException
     */
    final public function describe() : array
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

    protected function _getIdentification() : string
    {
        if(!isset($this->logPrefix)) {
            $this->logPrefix = ucfirst($this->getRecordTypeName()).' collection';
        }

        return $this->logPrefix;
    }

    protected function _getIdentificationDisposed() : string
    {
        return $this->_getIdentification();
    }

    final public function isRecordLoaded(int $recordID) : bool
    {
        return isset($this->records[$recordID]);
    }

     protected function _dispose() : void
     {
         $this->dummyRecord = null;

         $this->invalidateMemoryCache();
     }

     public function getChildDisposables() : array
     {
         $disposables = $this->records;
         $disposables[] = $this->keys;

         return $disposables;
     }
}
