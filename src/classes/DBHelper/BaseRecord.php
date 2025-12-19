<?php
/**
 * @package Application
 * @subpackage Core
 */

declare(strict_types=1);

use Application\Disposables\Attributes\DisposedAware;
use Application\Disposables\DisposableDisposedException;
use Application\Disposables\DisposableTrait;
use AppUtils\ConvertHelper;
use DBHelper\BaseCollection\DBHelperCollectionInterface;
use DBHelper\BaseRecord\BaseRecordException;
use DBHelper\BaseRecord\Event\KeyModifiedEvent;
use DBHelper\Interfaces\DBHelperRecordInterface;
use DBHelper\Traits\RecordKeyHandlersTrait;

/**
 * Base container class for a single record in a database. 
 * Has a skeleton to retrieve information about the records
 * table, and can load and access record data. 
 *
 * @package Application
 * @subpackage Core
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class DBHelper_BaseRecord implements DBHelperRecordInterface
{
    use Application_Traits_Loggable;
    use DisposableTrait;
    use Application_Traits_Eventable;
    use RecordKeyHandlersTrait;

    public const int ERROR_RECORD_DOES_NOT_EXIST = 13301;
    public const int ERROR_RECORD_KEY_UNKNOWN = 13302;

    /**
     * @var array<string,mixed>|NULL
     */
    protected ?array $recordData = null;
    protected string $recordTypeName;
    protected string $recordTable;
    protected string $recordPrimaryName;
    protected bool $isStub = false;
   
   /**
    * @var string[]
    */
    protected array $customModified = array();
    
    protected DBHelperCollectionInterface $collection;

    protected int $recordID;
    protected string $instanceID;
    protected static int $instanceCounter = 0;

    /**
     * @param int|string $primary_id
     * @param DBHelperCollectionInterface $collection
     * @throws Application_Exception|DBHelper_Exception
     */
    public function __construct(int|string $primary_id, DBHelperCollectionInterface $collection)
    {
        self::$instanceCounter++;

        $this->collection = $collection;
        $this->recordTable = $collection->getRecordTableName();
        $this->recordPrimaryName = $collection->getRecordPrimaryName();
        $this->recordTypeName = $collection->getRecordTypeName();
        $this->recordID = (int)$primary_id;
        $this->instanceID = 'DBR'.self::$instanceCounter;

        if($this->recordID === DBHelperRecordInterface::STUB_ID)
        {
            $this->constructStub();
            return;
        }
        
        $this->refreshData();
        
        $this->recordKeys = array_keys($this->recordData);
        
        $this->init();
    }

    public function getRecordData(): array
    {
        return (array)$this->recordData;
    }

    private function constructStub() : void
    {
        $this->isStub = true;

        $this->recordData = array(
            $this->recordPrimaryName => DBHelperRecordInterface::STUB_ID
        );

        $this->recordKeys = array_keys($this->recordData);

        $this->initStub();
    }

    public function getInstanceID(): string
    {
        return $this->instanceID;
    }

    #[DisposedAware]
    final public function refreshData() : void
    {
        $this->requireNotDisposed('Refreshing the record\'s data from DB.');

        $initial = !isset($this->recordData);

        if(!$initial) {
            $this->log('Refreshing the internal data.');
        }

        $this->recordData = $this->loadData();

        if(empty($this->recordData)) {
            throw new BaseRecordException(
                'Record not found',
                sprintf(
                    'Tried to retrieve a [%s] with primary id [%s] from table [%s].',
                    $this->recordTypeName,
                    $this->recordID,
                    $this->recordTable
                ),
                self::ERROR_RECORD_DOES_NOT_EXIST
            );
        }

        if(!$initial)
        {
            $this->_onDataRefreshed();
        }
    }

    protected function loadData() : array
    {
        $where = $this->collection->getForeignKeys();
        $where[$this->recordPrimaryName] = $this->recordID;

        $query = sprintf(
            "SELECT
                *
            FROM
                `%s`
            WHERE
                %s",
            $this->recordTable,
            DBHelper::buildWhereFieldsStatement($where)
        );

        return DBHelper::fetch(
            $query,
            $where
        );
    }

    protected function init() : void
    {
        
    }

   /**
    * Used to initialize stub records: called instead
    * of the init() method when using stubs.
    */
    protected function initStub() : void
    {
        
    }

    public function getRecordTable() : string
    {
        return $this->recordTable;
    }

    public function getRecordPrimaryName() : string
    {
        return $this->recordPrimaryName;
    }

    public function getRecordTypeName() : string
    {
        return $this->recordTypeName;
    }
    
    public function isStub() : bool
    {
        return $this->isStub;
    }
    
   /**
    * Retrieves the collection used to access records like this.
    * @return DBHelperCollectionInterface
    */
    public function getCollection() : DBHelperCollectionInterface
    {
        return $this->collection;
    }

    /**
     * @var string[]
     */
    protected array $recordKeys = array();
    
    public function getID() : int
    {
        return $this->recordID;
    }

    #[DisposedAware]
    public function getRecordKey(string $name, mixed $default=null) : mixed
    {
        $this->requireNotDisposed('Get a record data key');

        return $this->recordData[$name] ?? $default;
    }

    #[DisposedAware]
    public function recordKeyExists(string $name) : bool
    {
        $this->requireNotDisposed('Checking if a key exists.');

        return in_array($name, $this->recordKeys, true);
    }

    /**
     * @var string[]
     */
    protected array $modified = array();

    #[DisposedAware]
    public function setRecordKey(string $name, mixed $value) : bool
    {
        if($this->isStub) {
            return false;
        }

        $this->requireNotDisposed('Setting a record key');
        
        $this->requireRecordKeyExists($name);
        
        $previous = $this->getRecordKey($name);
        if(ConvertHelper::areVariablesEqual($value, $previous)) {
            return false;
        }

        if( isset($this->registeredKeys[$name])
                &&
            !$this->recordRegisteredKeyBeforeModified(
                $name,
                $this->registeredKeys[$name]['label'],
                $this->registeredKeys[$name]['isStructural'],
                $previous,
                $value
            )
        ) {
            $this->log(sprintf('Modifying the record key [%s] has been disallowed, ignoring the change.', $name));
            return false;
        }
        
        $this->recordData[$name] = $value;

        $this->log(sprintf('Data key [%s] has been modified.', $name));

        if(!in_array($name, $this->modified, true))
        {
            $this->modified[] = $name;

            $this->triggerKeyModified(
                $name,
                $previous,
                $value
            );
        }
        
        return true;
    }

    /**
     * @param string $name
     * @return bool
     * @throws DisposableDisposedException
     * @throws BaseRecordException
     */
    #[DisposedAware]
    public function requireRecordKeyExists(string $name) : bool
    {
        if($this->isStub || $this->recordKeyExists($name)) {
            return true;
        }
        
        throw new BaseRecordException(
            'Unknown record key',
            sprintf(
                'Cannot set key [%s] of [%s] record, it does not exist. Available keys are: [%s].',
                $name,
                $this->recordTypeName,
                implode(',', $this->recordKeys)
            ),
            self::ERROR_RECORD_KEY_UNKNOWN
        );
    }
    
   /**
    * Whether the record has been modified since the last save, or
    * just the specified key.
    * 
    * @param string|NULL $key A single data key to check, or any key if NULL.
    * @return boolean
    */
    public function isModified(?string $key=null) : bool
    {
        if($this->isStub) {
            return false;
        }
        
        if(!empty($key) && $this->requireRecordKeyExists($key)) {
            return in_array($key, $this->modified, true);
        }
        
        return !empty($this->modified) || !empty($this->customModified);
    }

    public function hasStructuralChanges() : bool
    {
        if($this->isStub) {
            return false;
        }

        return array_any(
            $this->registeredKeys,
            fn($info, $key) => $info['isStructural'] && in_array($key, $this->modified, true)
        );
    }
    
    public function getModifiedKeys() : array
    {
        return $this->modified;
    }

    public function save(bool $silent=false) : bool
    {
        if(!$this->isModified()) {
            return false;
        }

        $this->requireNotDisposed('Save the record');
        
        DBHelper::requireTransaction(sprintf('Save %s record [%s]', $this->recordTypeName, $this->getID()));

        $this->log('Saving the record.');

        $this->saveDataKeys();
        $this->saveCustomKeys();

        $context = new DBHelper_BaseCollection_OperationContext_Save($this);

        if($silent)
        {
            $context->makeSilent();
        }

        $this->_postSave($context);
        
        return true;
    }

    private function saveCustomKeys() : void
    {
        // are there any custom fields that were modified?
        if(!empty($this->customModified))
        {
            $this->log('Custom fields were modified, saving.');

            $this->saveCustomFields($this->customModified);

            $this->customModified = array();
        }
        else
        {
            $this->log('No custom fields were modified, skipping.');
        }
    }

    private function saveDataKeys() : void
    {
        $sets = array();
        $keys = array_keys($this->recordData);
        $saveKeys = array();
        foreach($keys as $key) {
            if($key === $this->recordPrimaryName || !$this->isModified($key)) {
                continue;
            }

            $saveKeys[] = $key;

            $sets[] = sprintf(
                "`%s`=:%s",
                $key,
                $key
            );
        }

        // This can be empty if only some custom fields
        // were modified, and no actual record keys were
        // modified.
        if(empty($sets))
        {
            return;
        }

        $where = $this->collection->getForeignKeys();
        $where[$this->recordPrimaryName] = $this->getID();

        $query = sprintf(
            "UPDATE
                `%s`
            SET
                %s
            WHERE
                %s",
            $this->recordTable,
            implode(',', $sets),
            DBHelper::buildWhereFieldsStatement($where)
        );

        // Only use the keys that were modified
        $data = array();
        foreach($saveKeys as $key)
        {
            $data[$key] = $this->recordData[$key];
        }

        // Add the where keys, ensuring that they
        // get overwritten if present.
        $data = array_merge($data, $where);

        DBHelper::update($query, $data);

        $this->modified = array();
    }

    public function saveChained(bool $silent=false) : self
    {
        $this->save($silent);
        return $this;
    }

    /**
     * @param string[] $names
     */
    protected function saveCustomFields(array $names) : void
    {
        
    }
    
    /**
     * @var array<string,array{label:string,isStructural:bool}>
     */
    protected array $registeredKeys = array();
    
   /**
    * Registers a record key, to enable tracking changes made to its value.
    * Whenever a registered key is modified, the {@link recordRegisteredKeyModified()}
    * method is called.
    * 
    * This is usually called in the record's {@link init()} method.
    * 
    * @param string $name The key name (of the database column)
    * @param string $label Human-readable label of the key
    * @param boolean $isStructural Whether changing this key means it's a structural (critical) change
    */
    protected function registerRecordKey(string $name, string $label, bool $isStructural=false) : void
    {
        $this->registeredKeys[$name] = array(
            'label' => $label,
            'isStructural' => $isStructural
        );
    }

    public function getParentRecord() : ?DBHelperRecordInterface
    {
        return $this->collection->getParentRecord();
    }

    // region: Event handling

    /**
     * Called right after successfully saving the
     * record. Can be extended to add any tasks that
     * the record type may need after saving.
     */
    protected function _postSave(DBHelper_BaseCollection_OperationContext_Save $context) : void
    {

    }

    protected function _onDataRefreshed() : void
    {
    }

    /**
     * Checks if the registered record key is allowed to be modified before the
     * change is applied.
     *
     * @param string $name
     * @param string $label
     * @param bool $isStructural
     * @param mixed $oldValue
     * @param mixed $newValue
     * @return bool True to allow the change, false to abort it.
     */
    protected function recordRegisteredKeyBeforeModified(string $name, string $label, bool $isStructural, mixed $oldValue, mixed $newValue) : bool
    {
        return true;
    }
    
   /**
    * This gets called whenever the value of a data key registered 
    * with {@link registerRecordKey()} has been modified. Use this to handle
    * these changes automatically as needed, for example to add changelog
    * entries.
    *
    * > NOTE: This is not related to the collection's registered keys,
    * > but the record's own registered keys, as is typically done in
    * > the {@see self::init()} method, using the method {@see self::registerRecordKey()}.
    *  
    * @param string $name Name of the data key
    * @param string $label Human-readable label of the key
    * @param boolean $isStructural Whether changing this key means it's a structural (critical) change
    * @param mixed $oldValue The previous value
    * @param mixed $newValue The new value
    */
    abstract protected function recordRegisteredKeyModified($name, $label, $isStructural, $oldValue, $newValue);
    
    private function triggerKeyModified(string $name, mixed $oldValue, mixed $newValue, bool $structural=false, bool $isCustom=false) : void
    {
        $label = null;
        $isStructural = $structural;

        if(isset($this->registeredKeys[$name])) {
            $label = $this->registeredKeys[$name]['label'];
            $isStructural = $this->registeredKeys[$name]['isStructural'];

            $this->recordRegisteredKeyModified(
                $name,
                $label,
                $isStructural,
                $oldValue,
                $newValue
            );
        }

        $this->triggerEvent(
            KeyModifiedEvent::EVENT_NAME,
            array(
                $this,
                $name,
                $oldValue,
                $newValue,
                $label,
                $isStructural,
                $isCustom
            ),
            KeyModifiedEvent::class
        );
    }

    public function onKeyModified(callable $callback) : Application_EventHandler_EventableListener
    {
        return $this->addEventListener(KeyModifiedEvent::EVENT_NAME, $callback);
    }

    final public function onCreated(DBHelper_BaseCollection_OperationContext_Create $context) : void
    {
        $this->_onCreated($context);
    }
    
    protected function _onCreated(DBHelper_BaseCollection_OperationContext_Create $context) : void
    {
        
    }
    
    final public function onDeleted(DBHelper_BaseCollection_OperationContext_Delete $context) : void
    {
        $this->_onDeleted($context);
    }

    final public function onBeforeDelete(DBHelper_BaseCollection_OperationContext_Delete $context) : void
    {
        $this->_onBeforeDelete($context);
    }
    
   /**
    * Can be extended to run any cleanup
    * tasks that may be needed when the record
    * has been deleted.
    */
    protected function _onDeleted(DBHelper_BaseCollection_OperationContext_Delete $context) : void
    {
        
    }

    protected function _onBeforeDelete(DBHelper_BaseCollection_OperationContext_Delete $context) : void
    {

    }

    // endregion

    protected function setCustomModified(string $name, bool $structural=false, $oldValue=null, $newValue=null) : void
    {
        if(in_array($name, $this->customModified, true)) {
            return;
        }

        $this->log(sprintf('CustomFields | [%s] | Modified.', $name));
        
        $this->customModified[] = $name;

        $this->triggerKeyModified(
            $name,
            $oldValue,
            $newValue,
            $structural,
            true
        );
    }

    public function getFormValues() : array
    {
        $this->requireNotDisposed('Get form values');

        return $this->recordData;
    }

    protected function _getIdentification() : string
    {
        return sprintf(
            '%s [#%s]',
            $this->getRecordTypeName(),
            $this->getID()
        );
    }

    protected function _getIdentificationDisposed(): string
    {
        return sprintf(
            '%s [#%s]',
            $this->getRecordTypeName(),
            $this->getID()
        );
    }

    protected function _dispose() : void
    {
        $this->recordData = array();
        $this->registeredKeys = array();
        $this->modified = array();
    }

    public function getChildDisposables() : array
    {
        return array();
    }
}
