<?php
/**
 * File containing the {@link DBHelper_BaseRecord} class.
 * @package Application
 * @subpackage Core
 * @see DBHelper_BaseRecord
 */

use Application\Collection\CollectionItemInterface;
use Application\Collection\IntegerCollectionItemInterface;
use Application\Exception\DisposableDisposedException;
use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper_Exception;
use DBHelper\BaseRecord\Event\KeyModifiedEvent;

/**
 * Base container class for a single record in a database. 
 * Has a skeleton to retrieve information about the records
 * table, and can load and access record data. 
 *
 * @package Application
 * @subpackage Core
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class DBHelper_BaseRecord
    implements
    IntegerCollectionItemInterface,
    Application_Interfaces_Loggable,
    Application_Interfaces_Disposable
{
    use Application_Traits_Loggable;
    use Application_Traits_Disposable;
    use Application_Traits_Eventable;

    public const ERROR_RECORD_DOES_NOT_EXIST = 13301;
    public const ERROR_RECORD_KEY_UNKNOWN = 13302;

    /**
     * @deprecated Use {@see self::STUB_ID} instead.
     */
    public const DUMMY_ID = self::STUB_ID;
    public const STUB_ID = -1;

    /**
     * @var array<string,mixed>|NULL
     */
    protected ?array $recordData = null;
    protected string $recordTypeName;
    protected string $recordTable;
    protected string $recordPrimaryName;
    protected bool $isDummy = false;
   
   /**
    * @var string[]
    */
    protected array $customModified = array();
    
   /**
    * @var DBHelper_BaseCollection
    */
    protected $collection;

    protected int $recordID;
    protected string $instanceID;
    protected static int $instanceCounter = 0;
    protected string $parentPrimaryName;

    /**
     * @param int|string $primary_id
     * @param DBHelper_BaseCollection $collection
     * @throws Application_Exception|DBHelper_Exception
     */
    public function __construct($primary_id, DBHelper_BaseCollection $collection)
    {
        self::$instanceCounter++;

        $this->collection = $collection;
        $this->recordTable = $collection->getRecordTableName();
        $this->recordPrimaryName = $collection->getRecordPrimaryName();
        $this->parentPrimaryName = $collection->getParentPrimaryName();
        $this->recordTypeName = $collection->getRecordTypeName();
        $this->recordID = (int)$primary_id;
        $this->instanceID = (string)self::$instanceCounter;

        if($this->recordID === self::STUB_ID)
        {
            $this->constructDummy();
            return;
        }
        
        $this->refreshData();
        
        $this->recordKeys = array_keys($this->recordData);
        
        $this->init();
    }

    /**
     * @return array<string,mixed>
     */
    public function getRecordData(): array
    {
        return (array)$this->recordData;
    }

    private function constructDummy() : void
    {
        $this->isDummy = true;

        $this->recordData = array(
            $this->recordPrimaryName => self::STUB_ID
        );

        $this->recordKeys = array_keys($this->recordData);

        $this->initDummy();
    }

    /**
     * @return string
     */
    public function getInstanceID(): string
    {
        return $this->instanceID;
    }

    /**
     * @throws Application_Exception
     * @throws DisposableDisposedException
     * @throws DBHelper_Exception
     */
    public function refreshData() : void
    {
        $this->requireNotDisposed('Refreshing the record\'s data from DB.');

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

        $initial = !isset($this->recordData);

        if(!$initial) {
            $this->log('Refreshing the internal data.');
        }

        $this->recordData = DBHelper::fetch(
            $query,
            $where
        );

        if(empty($this->recordData)) {
            throw new Application_Exception(
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

    protected function init()
    {
        
    }

   /**
    * Used to initialize dummy records: called instead
    * of the init() method when using dummies.
    */
    protected function initDummy()
    {
        
    }

    /**
     * @return string
     */
    public function getRecordTable()
    {
        return $this->recordTable;
    }

    /**
     * @return string
     */
    public function getRecordPrimaryName() : string
    {
        return $this->recordPrimaryName;
    }

    public function getParentPrimaryName() : string
    {
        return $this->parentPrimaryName;
    }

    /**
     * @return string
     */
    public function getRecordTypeName() : string
    {
        return $this->recordTypeName;
    }
    
   /**
    * Whether this is a dummy record that is used only to
    * access information on this record type.
    * 
    * @return boolean
    */
    public function isDummy()
    {
        return $this->isDummy;
    }
    
   /**
    * Retrieves the collection used to access records like this.
    * @return DBHelper_BaseCollection
    */
    public function getCollection()
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

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     * @throws DisposableDisposedException
     */
    public function getRecordKey($name, $default=null)
    {
        $this->requireNotDisposed('Get a record data key');

        if(isset($this->recordData[$name])) {
            return $this->recordData[$name];
        }
        
        return $default;
    }

    /**
     * Retrieves a data key as an integer. Converts the value to int,
     * so beware using this on non-integer keys.
     *
     * @param string $name
     * @param int $default
     * @return int
     * @throws DisposableDisposedException
     */
    public function getRecordIntKey(string $name, int $default=0) : int
    {
        $value = $this->getRecordKey($name);
        if($value !== null && $value !== '') {
            return intval($value);
        }
        
        return $default;
    }

    /**
     * Retrieves a data key as a float. Converts the value to float,
     * so beware using this on non-float keys.
     *
     * @param string $name
     * @param float $default
     * @return float
     * @throws DisposableDisposedException
     */
    public function getRecordFloatKey(string $name, float $default=0) : float
    {
        $value = $this->getRecordKey($name);
        if($value !== null && $value !== '') {
            return floatval($value);
        }

        return $default;
    }

    /**
     * Retrieves a data key, ensuring that it is a string.
     *
     * @param string $name
     * @param string $default
     * @return string
     * @throws DisposableDisposedException
     */
    public function getRecordStringKey(string $name, string $default='') : string
    {
        $value = $this->getRecordKey($name);
        if(!empty($value) && is_string($value)) {
            return $value;
        }
        
        return $default;
    }

    /**
     * Retrieves a data key as a DateTime object.
     * @param string $name
     * @param DateTime|null $default
     * @return DateTime|null
     * @throws Exception
     */
    public function getRecordDateKey(string $name, ?DateTime $default=null) : ?DateTime
    {
        $value = $this->getRecordKey($name);
        if($value !== null) {
            return new DateTime($value);
        }
        
        return $default;
    }

    /**
     * Treats a key as a string boolean value and returns
     * the current value as a boolean.
     *
     * @param string $name
     * @param boolean $default
     * @return boolean
     * @throws DisposableDisposedException
     * @throws ConvertHelper_Exception
     */
    protected function getRecordBooleanKey($name, $default=false) : bool
    {
        $value = $this->getRecordKey($name, $default);
        if($value===null) {
            $value = $default;
        }
        
        return ConvertHelper::string2bool($value);
    }

    /**
     * @param string $name
     * @return bool
     * @throws DisposableDisposedException
     */
    protected function recordKeyExists(string $name) : bool
    {
        $this->requireNotDisposed('Checking if a key exists.');

        return in_array($name, $this->recordKeys);
    }

    /**
     * @var string[]
     */
    protected $modified = array();

    /**
     * Converts a boolean value to its string representation to use
     * as internal value for a property.
     *
     * @param string $name
     * @param boolean $boolean
     * @param boolean $yesno Whether to use the "yes/no" notation. Otherwise "true/false" is used.
     * @return boolean Whether the value has changed.
     * @throws Application_Exception
     * @throws DisposableDisposedException
     * @throws ConvertHelper_Exception
     */
    public function setRecordBooleanKey(string $name, bool $boolean, bool $yesno=true) : bool
    {
        $value = ConvertHelper::boolStrict2string($boolean, $yesno);
        return $this->setRecordKey($name, $value);
    }

    /**
     * @param string $name
     * @param DateTime $date
     * @return bool
     * @throws Application_Exception
     * @throws DisposableDisposedException
     * @throws ConvertHelper_Exception
     */
    public function setRecordDateKey(string $name, DateTime $date) : bool
    {
        return $this->setRecordKey($name, $date->format('Y-m-d H:i:s'));
    }

    /**
     * @param string $name
     * @return bool
     * @throws DisposableDisposedException
     */
    public function hasKey(string $name) : bool
    {
        $this->requireNotDisposed('Check if a data key is present.');

        return array_key_exists($name, $this->recordData);
    }

    /**
     * Sets the value of a data key of the record. If the data key has been
     * registered, the {@link recordKeyModified()} method is also called
     * to notify of changes.
     *
     * @param string $name
     * @param mixed $value
     * @return boolean
     * @throws Application_Exception
     * @throws DisposableDisposedException
     * @throws ConvertHelper_Exception
     */
    public function setRecordKey(string $name, $value) : bool
    {
        if($this->isDummy) {
            return false;
        }

        $this->requireNotDisposed('Setting a record key');
        
        $this->requireKey($name);
        
        $previous = $this->getRecordKey($name);
        if(ConvertHelper::areStringsEqual($value, $previous)) {
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

        if(!in_array($name, $this->modified))
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
     * @throws DisposableDisposedException|DBHelper_Exception
     */
    protected function requireKey(string $name) : bool
    {
        if($this->isDummy || $this->recordKeyExists($name)) {
            return true;
        }
        
        throw new DBHelper_Exception(
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
        if($this->isDummy) {
            return false;
        }
        
        if(!empty($key) && $this->requireKey($key)) {
            return in_array($key, $this->modified);
        }
        
        return !empty($this->modified) || !empty($this->customModified);
    }

    /**
     * Checks whether any structural data keys have been modified.
     * @return bool
     */
    public function isStructureModified() : bool
    {
        if($this->isDummy) {
            return false;
        }

        foreach($this->registeredKeys as $key => $info) {
            if($info['isStructural'] && in_array($key, $this->modified)) {
                return true;
            }
        }

        return false;
    }
    
   /**
    * Retrieves the names of all keys that have been modified since the last save.
    * @return string[]
    */
    public function getModifiedKeys() : array
    {
        return $this->modified;
    }

    /**
     * Saves all changes in the record. Only the modified keys
     * are saved each time using the internal changes tracking.
     *
     * @param bool $silent Whether to not process the post save events.
     *                       The postSave() method will still be called, but
     *                       the context will reflect the silent mode. This
     *                       has to be checked manually.
     *
     * @return boolean Whether there was anything to save.
     * @throws DisposableDisposedException
     * @throws DBHelper_Exception|ConvertHelper_Exception
     */
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

        $this->postSave($context);
        
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

    /**
     * Like {@see DBHelper_BaseRecord::save()}, but
     * returns $this instead of the boolean status.
     *
     * @param bool $silent
     * @return $this
     * @throws DisposableDisposedException
     * @throws DBHelper_Exception
     */
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
     * @param string[] $columns
     */
    protected function fixUTF8($columns)
    {
        foreach($this->recordData as $key => $value) {
            if(in_array($key, $columns)) {
                $this->recordData[$key] = ConvertHelper::string2utf8($value);
            }
        }
    }

    /**
     * @param string[] $customKeys
     * @return string[]
     */
    protected function getWhereKeys($customKeys=array())
    {
        $where = $this->collection->getForeignKeys();
        $where[$this->recordPrimaryName] = $this->getID();
        return array_merge($customKeys, $where);
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

    /**
     * Retrieves the record's parent record: this is only
     * available if the record's collection has a parent
     * collection.
     *
     * @return DBHelper_BaseRecord|NULL
     */
    public function getParentRecord()
    {
        return $this->collection->getParentRecord();
    }

    // region: Event handling

    /**
     * Called right after successfully saving the
     * record. Can be extended to add any tasks that
     * the record type may need after saving.
     */
    protected function postSave(DBHelper_BaseCollection_OperationContext_Save $context) : void
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
    protected function recordRegisteredKeyBeforeModified(string $name, string $label, bool $isStructural, $oldValue, $newValue) : bool
    {
        return true;
    }
    
   /**
    * This gets called whenever the value of a data key registered 
    * with {@link registerRecordKey()} has been modified. Use this to handle
    * these changes automatically as needed, for example to add changelog
    * entries.
    *
    * NOTE: This is not related to the collection's registered keys.
    *  
    * @param string $name Name of the data key
    * @param string $label Human-readable label of the key
    * @param boolean $isStructural Whether changing this key means it's a structural (critical) change
    * @param string $oldValue The previous value
    * @param string $newValue The new value
    */
    abstract protected function recordRegisteredKeyModified($name, $label, $isStructural, $oldValue, $newValue);
    
    private function triggerKeyModified(string $name, $oldValue, $newValue, bool $structural=false, bool $isCustom=false) : void
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

    /**
     * Adds a listener for the event {@see KeyModifiedEvent}.
     *
     * NOTE: The callback gets the event instance as sole argument.
     *
     * @param callable $callback
     * @return Application_EventHandler_EventableListener
     */
    public function onKeyModified(callable $callback) : Application_EventHandler_EventableListener
    {
        return $this->addEventListener(KeyModifiedEvent::EVENT_NAME, $callback);
    }

   /**
    * This is called once when the record has been created, 
    * and allows the record to run any additional initializations
    * it may need.
    *
    * @param DBHelper_BaseCollection_OperationContext_Create $context
    */
    final public function onCreated(DBHelper_BaseCollection_OperationContext_Create $context) : void
    {
        $this->_onCreated($context);
    }
    
    protected function _onCreated(DBHelper_BaseCollection_OperationContext_Create $context) : void
    {
        
    }
    
   /**
    * Called when the record has been deleted by the 
    * collection. 
    * 
    * @param DBHelper_BaseCollection_OperationContext_Delete $context
    */
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
        if(in_array($name, $this->customModified)) {
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

    /**
     * @return array<string,mixed>
     * @throws DisposableDisposedException
     */
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
