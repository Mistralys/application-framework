<?php

declare(strict_types=1);

namespace DBHelper\BaseCollection;

use Application\Collection\IntegerCollectionInterface;
use Application\Exception\DisposableDisposedException;
use Application_EventHandler_EventableListener;
use AppUtils\Request;
use DBHelper\BaseCollection\Event\AfterCreateRecordEvent;
use DBHelper\BaseCollection\Event\AfterDeleteRecordEvent;
use DBHelper\BaseCollection\Event\BeforeCreateRecordEvent;
use DBHelper_BaseFilterCriteria;
use DBHelper_BaseFilterSettings;
use DBHelper_BaseRecord;

interface DBHelperCollectionInterface extends IntegerCollectionInterface
{
    /**
     * @return class-string<DBHelper_BaseRecord>
     */
    public function getRecordClassName() : string;

    /**
     * @return class-string<DBHelper_BaseFilterCriteria>
     */
    public function getRecordFiltersClassName() : string;

    /**
     * @return class-string<DBHelper_BaseFilterSettings>
     */
    public function getRecordFilterSettingsClassName() : string;

    /**
     * @return string
     */
    public function getRecordDefaultSortKey() : string;

    /**
     * Retrieves the searchable columns as an associative array
     * with column name => human-readable label pairs.
     *
     * @return array<string,string>
     */
    public function getRecordSearchableColumns() : array;

    /**
     * The name of the table storing the records.
     *
     * @return string
     */
    public function getRecordTableName() : string;

    /**
     * The name of the database column storing the primary key.
     *
     * @return string
     */
    public function getRecordPrimaryName() : string;

    /**
     * @return string
     */
    public function getRecordTypeName() : string;

    /**
     * Human-readable label of the collection, e.g. "Products".
     *
     * @return string
     */
    public function getCollectionLabel() : string;

    /**
     * Human-readable label of the records, e.g. "Product".
     *
     * @return string
     */
    public function getRecordLabel() : string;

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
    public function getRecordProperties() : array;

    /**
     * Checks whether a specific column value exists
     * in any of the collection's records.
     *
     * @param string $keyName
     * @param string $value
     * @return integer|boolean The record's ID, or false if not found.
     */
    public function recordKeyValueExists(string $keyName, string $value) : int|bool;

    /**
     * Attempts to retrieve a record by its ID as specified in the request.
     *
     * Uses the request parameter name as returned by {@see self::getRecordRequestPrimaryName()},
     * with {@see self::getRecordPrimaryName()} as fallback.
     *
     * @return DBHelper_BaseRecord|NULL
     */
    public function getByRequest() : ?DBHelper_BaseRecord;

    /**
     * Registers parameter names in the {@see Request} class to
     * validate request values for this collection. Automatically
     * called when using {@see self::getByRequest()}.
     *
     * @return void
     */
    public function registerRequestParams() : void;

    /**
     * Retrieves a single record by a specific record key.
     * Note that if the key is not unique, the first one
     * in the result set is used, using the default sorting
     * key.
     *
     * @param string $key
     * @param string $value
     * @return DBHelper_BaseRecord|NULL
     */
    public function getByKey(string $key, string $value) : ?DBHelper_BaseRecord;

    /**
     * Checks whether a record with the specified ID exists in the database.
     *
     * @param integer|string|NULL $record_id
     * @return boolean
     */
    public function idExists($record_id) : bool;

    /**
     * Creates a stub record of this collection, which can
     * be used to access the API that may not be available
     * statically.
     *
     * @return DBHelper_BaseRecord
     */
    public function createDummyRecord() : DBHelper_BaseRecord;

    /**
     * Retrieves all records from the database, ordered by the default sorting key.
     *
     * @return DBHelper_BaseRecord[]
     */
    public function getAll() : array;

    /**
     * Counts the number of records in total.
     * @return int
     */
    public function countRecords() : int;

    /**
     * Creates the filter criteria for this collection of records,
     * which is used to query the records.
     *
     * @return DBHelper_BaseFilterCriteria
     */
    public function getFilterCriteria() : DBHelper_BaseFilterCriteria;

    /**
     * Creates the filter settings for this collection of records,
     * which is used to configure the filtering options used in lists.
     *
     * @return DBHelper_BaseFilterSettings
     */
    public function getFilterSettings() : DBHelper_BaseFilterSettings;

    /**
     * Creates a new record with the specified data.
     *
     * > NOTE: This does not do any kind of validation,
     * > you have to ensure that the required keys are
     * > all present in the data set.
     *
     * > NOTE: It is possible to use the {@see \DBHelper_BaseCollection::onBeforeCreateRecord()}
     * > method to verify the data, and cancel the event
     * > as needed.
     *
     * @param array<string,mixed> $data
     * @param bool $silent Whether to not execute any events after
     *                       creating the record. The _onCreated() method
     *                       will still be called, but the context will
     *                       reflect the silent flag to manually handle the
     *                       situation.
     * @param array<string,mixed> $options Options that are passed on to the record's
     *                       onCreated() method, and which can be used for
     *                       custom initialization routines.
     *                       Official options are:
     *                       - {@see self::OPTION_CUSTOM_RECORD_ID}: Specify a custom
     *                         ID to use for the record. Can fail if this is not available.
     * @return DBHelper_BaseRecord
     */
    public function createNewRecord(array $data=array(), bool $silent=false, array $options=array()) : DBHelper_BaseRecord;

    /**
     * Whether the collection has a separate database table dedicated
     * to generating the record IDs.
     *
     * @return bool
     */
    public function hasRecordIDTable(): bool;

    /**
     * Listens to any new records being created, and allows
     * reviewing the data set before the record is added to
     * the database. It allows canceling the event if needed.
     *
     * > NOTE: If the aim is to validate the record's data set,
     * > you should register the data keys instead. This allows
     * > finer control, with per-key validation callbacks and more.
     * > See {@see DBHelper_BaseCollection::_registerKeys()}
     * > for details.
     *
     * @param callable(BeforeCreateRecordEvent) : void $callback
     * @return Application_EventHandler_EventableListener
     * @see BeforeCreateRecordEvent
     */
    public function onBeforeCreateRecord(callable $callback) : Application_EventHandler_EventableListener;

    /**
     * Listens to any new records created in the collection.
     * This allows tasks to execute on the collection level
     * when records are created, as compared to the record's
     * own created event handled via {@see DBHelper_BaseRecord::onCreated()}.
     *
     * @param callable(AfterCreateRecordEvent) : void $callback
     * @return Application_EventHandler_EventableListener
     * @see AfterCreateRecordEvent
     */
    public function onAfterCreateRecord(callable $callback) : Application_EventHandler_EventableListener;

    /**
     * Listens to any records deleted from the collection.
     *
     * The callback gets an instance of the event:
     * {@see AfterDeleteRecordEvent}
     *
     * @param callable(AfterDeleteRecordEvent) : void $callback
     * @return Application_EventHandler_EventableListener
     * @see AfterDeleteRecordEvent
     */
    public function onAfterDeleteRecord(callable $callback) : Application_EventHandler_EventableListener;

    /**
     * Deletes a record from the collection.
     *
     * @param DBHelper_BaseRecord $record
     * @param bool $silent Whether to delete the record silently, without processing events afterwards.
     *                      The _onDeleted method will still be called for cleanup tasks, but the context
     *                      will reflect the silent state. The method implementation must check this manually.
     */
    public function deleteRecord(DBHelper_BaseRecord $record, bool $silent=false) : void;

    /**
     * Checks whether a record with the specified ID is loaded in memory.
     * @param int $recordID
     * @return bool
     */
    public function isRecordLoaded(int $recordID) : bool;

    /**
     * Gets the name of the request parameter used to fetch
     * a collection record when using {@see DBHelper_BaseCollection::getByRequest()}.
     * Defaults to the same name as the primary key.
     *
     * @return string
     */
    public function getRecordRequestPrimaryName() : string;

    public function getRecordDefaultSortDir() : string;

    /**
     * Called by the DBHelper once the collection configuration
     * has been completed.
     */
    public function setupComplete() : void;

    /**
     * If the collection has a parent record, it is returned here.
     *
     * > NOTE: You can check if the collection is a child collection
     * > with instanceof {@see BaseChildCollection}, then this method
     * > will no longer return `null`.
     *
     * @return DBHelper_BaseRecord|NULL
     * @see BaseChildCollection::getParentRecord()
     */
    public function getParentRecord() : ?DBHelper_BaseRecord;

    public function getInstanceID() : string;

    /**
     * Retrieves the foreign keys that should be included in
     * all queries, as an associative array with key => value pairs.
     *
     * @return array<string,string>
     */
    public function getForeignKeys() : array;

    /**
     * Gets the names of the keys that are searchable
     * in this collection.
     *
     * @return string[]
     */
    public function getRecordSearchableKeys() : array;

    /**
     * Gets the human-readable labels of the searchable fields
     * in this collection.
     *
     * @return string[]
     */
    public function getRecordSearchableLabels() : array;

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
     * @see RecordListScreenTrait
     */
    public function getDataGridName() : string;

    /**
     * Retrieves a record by its ID.
     *
     * @param int|string $record_id
     * @return DBHelper_BaseRecord
     */
    public function getByID($record_id) : DBHelper_BaseRecord;

    /**
     * Refreshes all data currently loaded in memory with
     * data from the database.
     */
    public function refreshRecordsData() : void;

    /**
     * Resets the internal records instance cache.
     * Forces all records to be fetched anew from the
     * database as requested.
     *
     * > NOTE: Records that were already loaded are disposed,
     * > and may not be used anymore.
     *
     * @return $this
     */
    public function resetCollection() : self;
}
