<?php

declare(strict_types=1);

namespace DBHelper\Interfaces;

use Application\Collection\IntegerCollectionItemInterface;
use Application\Disposables\DisposableDisposedException;
use Application_EventHandler_EventableListener;
use Application\Disposables\DisposableInterface;
use AppUtils\ConvertHelper_Exception;
use AppUtils\Microtime;
use DateTime;
use DBHelper\BaseCollection\DBHelperCollectionInterface;
use DBHelper\BaseRecord\BaseRecordException;
use DBHelper_BaseCollection_OperationContext_Create;
use DBHelper_BaseCollection_OperationContext_Delete;
use DBHelper_Exception;

interface DBHelperRecordInterface extends IntegerCollectionItemInterface, DisposableInterface
{
    /**
     * Whether this is a stub record that is used only to
     * access information on this record type.
     *
     * @return boolean
     */
    public function isStub() : bool;

    /**
     * Retrieves the collection used to access records like this.
     * @return DBHelperCollectionInterface
     */
    public function getCollection() : DBHelperCollectionInterface;

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getRecordKey(string $name, mixed $default=null) : mixed;

    /**
     * Retrieves a data key as an integer. Converts the value to int,
     * so beware using this on non-integer keys.
     *
     * @param string $name
     * @param int $default
     * @return int
     * @throws DisposableDisposedException
     */
    public function getRecordIntKey(string $name, int $default=0) : int;

    /**
     * Retrieves a data key as a DateTime object.
     * @param string $name
     * @param DateTime|null $default
     * @return DateTime|null
     */
    public function getRecordDateKey(string $name, ?DateTime $default=null) : ?DateTime;

    public function getRecordMicrotimeKey(string $name) : ?Microtime;

    /**
     * Retrieves a data key as a DateTime object, throwing an exception if the key has no value.
     * @param string $name
     * @return Microtime
     * @throws BaseRecordException
     */
    public function requireRecordMicrotimeKey(string $name) : Microtime;

    /**
     * @return array<string,mixed>
     */
    public function getRecordData(): array;

    /**
     * Gets a unique identifier for this record object instance.
     * @return string
     */
    public function getInstanceID(): string;

    /**
     * Reloads the record's data from the database.
     * @throws DisposableDisposedException
     * @throws BaseRecordException
     */
    public function refreshData() : void;

    public function getRecordTable() : string;

    public function getRecordPrimaryName() : string;

    public function getRecordTypeName() : string;

    /**
     * Retrieves a data key as a float. Converts the value to float,
     * so beware using this on non-float keys.
     *
     * @param string $name
     * @param float $default
     * @return float
     * @throws DisposableDisposedException
     */
    public function getRecordFloatKey(string $name, float $default=0.0) : float;

    /**
     * Retrieves a data key, ensuring that it is a string.
     *
     * @param string $name
     * @param string $default
     * @return string
     * @throws DisposableDisposedException
     */
    public function getRecordStringKey(string $name, string $default='') : string;

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
    public function getRecordBooleanKey(string $name, bool $default=false) : bool;

    /**
     * Checks if the specified record key exists.
     * @param string $name
     * @return bool
     * @throws DisposableDisposedException
     */
    public function recordKeyExists(string $name) : bool;

    /**
     * Converts a boolean value to its string representation to use
     * as internal value for a property.
     *
     * @param string $name
     * @param boolean $boolean
     * @param boolean $yesno Whether to use the "yes/no" notation. Otherwise, "true/false" is used.
     * @return boolean Whether the value has changed.
     * @throws DisposableDisposedException
     * @throws ConvertHelper_Exception
     */
    public function setRecordBooleanKey(string $name, bool $boolean, bool $yesno=true) : bool;

    /**
     * @param string $name
     * @param DateTime $date
     * @return bool
     * @throws DisposableDisposedException
     * @throws ConvertHelper_Exception
     */
    public function setRecordDateKey(string $name, DateTime $date) : bool;

    /**
     * Sets the value of a data key of the record. If the data key has been
     * registered, the {@see \DBHelper_BaseRecord::recordRegisteredKeyModified()}
     * and {@see \DBHelper_BaseRecord::recordRegisteredKeyBeforeModified() are
     * also called to notify of changes.
     *
     * @param string $name
     * @param mixed $value
     * @return boolean
     * @throws DisposableDisposedException
     * @throws ConvertHelper_Exception
     */
    public function setRecordKey(string $name, mixed $value) : bool;

    /**
     * Throws an exception if the record does not have the specified key.
     * @param string $name
     * @return bool
     * @throws DisposableDisposedException
     * @throws BaseRecordException
     */
    public function requireRecordKeyExists(string $name) : bool;

    /**
     * Whether the record has been modified since the last save, or
     * just the specified key.
     *
     * @param string|NULL $key A single data key to check, or any key if NULL.
     * @return boolean
     */
    public function isModified(?string $key=null) : bool;

    /**
     * Checks whether any structural data keys have been modified.
     *
     * > NOTE: This method only works if the record has registered
     * > structural keys through the method {@see \DBHelper_BaseRecord::registerRecordKey()}.
     *
     * @return bool
     */
    public function hasStructuralChanges() : bool;

    /**
     * Retrieves the names of all keys that have been modified since the last save.
     * @return string[]
     */
    public function getModifiedKeys() : array;

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
     * @throws DBHelper_Exception
     */
    public function save(bool $silent=false) : bool;

    /**
     * Like {@see self::save()}, but
     * returns $this instead of the boolean status.
     *
     * @param bool $silent
     * @return $this
     * @throws DisposableDisposedException
     * @throws DBHelper_Exception
     */
    public function saveChained(bool $silent=false) : self;

    /**
     * Retrieves the record's parent record: this is only
     * relevant if the record's collection has a parent
     * collection. It will return NULL otherwise.
     *
     * @return DBHelperRecordInterface|NULL
     */
    public function getParentRecord() : ?DBHelperRecordInterface;

    /**
     * @return array<string,mixed>
     * @throws DisposableDisposedException
     */
    public function getFormValues() : array;

    /**
     * Adds a listener for the event {@see KeyModifiedEvent}.
     *
     * NOTE: The callback gets the event instance as sole argument.
     *
     * @param callable $callback
     * @return Application_EventHandler_EventableListener
     */
    public function onKeyModified(callable $callback) : Application_EventHandler_EventableListener;

    /**
     * This is called once when the record has been created,
     * and allows the record to run any additional initializations
     * it may need.
     *
     * @param DBHelper_BaseCollection_OperationContext_Create $context
     */
    public function onCreated(DBHelper_BaseCollection_OperationContext_Create $context) : void;

    /**
     * Called when the record has been deleted by the
     * collection.
     *
     * @param DBHelper_BaseCollection_OperationContext_Delete $context
     */
    public function onDeleted(DBHelper_BaseCollection_OperationContext_Delete $context) : void;

    public function onBeforeDelete(DBHelper_BaseCollection_OperationContext_Delete $context) : void;
}
