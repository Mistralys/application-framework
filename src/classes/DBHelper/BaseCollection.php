<?php
/**
 * @package Application
 * @subpackage DBHelper
 */

declare(strict_types=1);

use Application\AppFactory;
use Application\Disposables\Attributes\DisposedAware;
use Application\Disposables\DisposableDisposedException;
use Application\Disposables\DisposableTrait;
use AppUtils\ClassHelper;
use AppUtils\ConvertHelper;
use DBHelper\BaseCollection\DBHelperCollectionException;
use DBHelper\BaseCollection\DBHelperCollectionInterface;
use DBHelper\BaseCollection\Event\AfterDeleteRecordEvent;
use DBHelper\DBHelperFilterCriteriaInterface;
use DBHelper\DBHelperFilterSettingsInterface;
use DBHelper\Interfaces\DBHelperRecordInterface;
use DBHelper\Traits\AfterRecordCreatedEventTrait;
use DBHelper\Traits\BeforeCreateEventTrait;

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
    use DisposableTrait;
    use Application_Traits_Eventable;
    use Application_Traits_Loggable;
    use BeforeCreateEventTrait;
    use AfterRecordCreatedEventTrait;

    protected ?string $recordIDTable;

    /**
     * @var class-string<DBHelperRecordInterface>
     */
    protected string $recordClassName;
    protected string $recordSortKey;
    protected string $recordSortDir;
    protected string $recordPrimaryName;
    protected string $recordTable;
    protected ?DBHelperRecordInterface $dummyRecord = null;

    /**
     * @var class-string<DBHelperFilterCriteriaInterface>
     */
    protected string $recordFiltersClassName;

    /**
     * @var class-string<DBHelperFilterSettingsInterface>
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
     * @var DBHelperRecordInterface[]
     */
    protected array $records = array();
    private ?string $recordIDTablePrimaryName = null;

    /**
    * NOTE: classes extending this class may not create
    * constructors with parameters. The interface must
    * stay parameter-less to stay compatible with the
    * <code>DBHelper::createCollection()</code> method.
    * 
    * > NOTE: Extend the {@see DBHelper_BaseCollection::init()} method to
    * > handle any required initialization once the
    * > collection has been fully set up.
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
     * @return class-string<DBHelperRecordInterface>
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
        return DBHelperCollectionInterface::SORT_DIR_ASC;
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
    
    public function getParentRecord() : ?DBHelperRecordInterface
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

    public function getByID($record_id) : DBHelperRecordInterface
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

    public function getByRequest() : ?DBHelperRecordInterface
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

    public function getByKey(string $key, string $value) : ?DBHelperRecordInterface
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

    final public function idExists(int $record_id) : bool
    {
        $this->requireNotDisposed('Check if record ID exists.');

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

    public function createStubRecord() : DBHelperRecordInterface
    {
        if(isset($this->dummyRecord)) {
            return $this->dummyRecord;
        }
        
        $this->dummyRecord = $this->getByID(DBHelperRecordInterface::STUB_ID);
        
        if(isset($this->recordIDTable) && $this->recordIDTable === $this->recordTable) {
            throw new DBHelperCollectionException(
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
     * @var DBHelperRecordInterface[]|null
     */
    private ?array $allRecords = null;

    /**
     * Retrieves all records from the database, ordered by the default sorting key.
     *
     * @return DBHelperRecordInterface[]
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

    public function getFilterCriteria() : DBHelperFilterCriteriaInterface
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

    public function createNewRecord(array $data=array(), bool $silent=false, array $options=array()) : DBHelperRecordInterface
    {
        $this->requireNotDisposed('Create a new record');

        $data = array_merge($data, $this->foreignKeys);

        $this->fillDefaults($data);
        $this->verifyData($data);

        DBHelper::requireTransaction('Create a new '.$this->getRecordTypeName());

        $this->log('Creating a new record.');

        $this->handleOnBeforeCreateRecord($data);

        $customID = null;
        if(isset($options[DBHelperCollectionInterface::OPTION_CUSTOM_RECORD_ID])) {
            $customID = (int)$options[DBHelperCollectionInterface::OPTION_CUSTOM_RECORD_ID];
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

        $this->handleAfterRecordCreated($record, $silent, $options);

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

            if($value !== DBHelperCollectionInterface::VALUE_UNDEFINED)
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

        return DBHelperCollectionInterface::VALUE_UNDEFINED;
    }

    // region: Event handling

    final public function onBeforeCreateRecord(callable $callback) : Application_EventHandler_EventableListener
    {
        return $this->addEventListener(DBHelperCollectionInterface::EVENT_BEFORE_CREATE_RECORD, $callback);
    }

    final public function onAfterCreateRecord(callable $callback) : Application_EventHandler_EventableListener
    {
        return $this->addEventListener(DBHelperCollectionInterface::EVENT_AFTER_CREATE_RECORD, $callback);
    }

    final public function onAfterDeleteRecord(callable $callback) : Application_EventHandler_EventableListener
    {
        return $this->addEventListener(DBHelperCollectionInterface::EVENT_AFTER_DELETE_RECORD, $callback);
    }

    final protected function triggerAfterDeleteRecord(DBHelperRecordInterface $record, DBHelper_BaseCollection_OperationContext_Delete $context) : void
    {
        $this->triggerEvent(
            DBHelperCollectionInterface::EVENT_AFTER_DELETE_RECORD,
            array(
                $this,
                $record,
                $context
            ),
            AfterDeleteRecordEvent::class
        );
    }

    // endregion

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

    public function deleteRecord(DBHelperRecordInterface $record, bool $silent=false) : void
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
    #[DisposedAware]
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
