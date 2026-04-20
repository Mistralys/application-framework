# DBHelper - Core Architecture
_SOURCE: Core public class signatures_
# Core public class signatures
```
// Structure of documents
└── src/
    └── classes/
        └── DBHelper/
            └── Attributes/
                ├── UncachedQuery.php
            └── BaseCollection.php
            └── BaseCollection/
                ├── BaseChildCollection.php
                ├── ChildCollectionInterface.php
                ├── DBHelperCollectionException.php
                ├── DBHelperCollectionInterface.php
                ├── Event/
                │   ├── AfterCreateRecordEvent.php
                │   ├── AfterDeleteRecordEvent.php
                │   ├── BeforeCreateRecordEvent.php
                ├── Keys.php
                ├── Keys/
                │   ├── Key.php
                ├── OperationContext.php
                ├── OperationContext/
                │   └── Create.php
                │   └── Delete.php
                │   └── Save.php
            └── BaseFilterCriteria.php
            └── BaseFilterCriteria/
                ├── BaseCollectionFilteringInterface.php
                ├── IntegerCollectionFilteringInterface.php
                ├── Record.php
                ├── StringCollectionFilteringInterface.php
            └── BaseFilterSettings.php
            └── BaseRecord.php
            └── BaseRecord/
                ├── BaseRecordDecorator.php
                ├── BaseRecordException.php
                ├── Event/
                │   └── KeyModifiedEvent.php
            └── BaseRecordSettings.php
            └── CaseStatement.php
            └── DBHelper.php
            └── DBHelperFilterCriteriaInterface.php
            └── DBHelperFilterSettingsInterface.php
            └── DataTable.php
            └── DataTable/
                ├── Events/
                │   └── KeysDeleted.php
                │   └── KeysSaved.php
            └── Event.php
            └── Exception.php
            └── Exception/
                ├── BaseErrorRenderer.php
                ├── CLIErrorRenderer.php
                ├── HTMLErrorRenderer.php
            └── FetchBase.php
            └── FetchKey.php
            └── FetchMany.php
            └── FetchOne.php
            └── Interfaces/
                ├── DBHelperRecordInterface.php
            └── OperationTypes.php
            └── StatementBuilder.php
            └── StatementBuilder/
                ├── ValueDefinition.php
                ├── ValuesContainer.php
            └── TrackedQuery.php
            └── Traits/
                └── AfterRecordCreatedEventTrait.php
                └── BeforeCreateEventTrait.php
                └── LooseDBRecordInterface.php
                └── LooseDBRecordTrait.php
                └── RecordDecoratorInterface.php
                └── RecordDecoratorTrait.php
                └── RecordKeyHandlersTrait.php

```
###  Path: `/src/classes/DBHelper/Attributes/UncachedQuery.php`

```php
namespace DBHelper\Attributes;

use Attribute as Attribute;

#[Attribute]
class UncachedQuery
{
}


```
###  Path: `/src/classes/DBHelper/Attributes/UncachedQuery.php`

```php
namespace DBHelper\Attributes;

use Attribute as Attribute;

#[Attribute]
class UncachedQuery
{
}


```
###  Path: `/src/classes/DBHelper/BaseCollection.php`

```php
namespace ;

use AppUtils\ClassHelper as ClassHelper;
use AppUtils\ConvertHelper as ConvertHelper;
use Application\AppFactory as AppFactory;
use Application\Disposables\Attributes\DisposedAware as DisposedAware;
use Application\Disposables\DisposableDisposedException as DisposableDisposedException;
use Application\Disposables\DisposableTrait as DisposableTrait;
use Application\EventHandler\Eventables\EventableListener as EventableListener;
use Application\EventHandler\Eventables\EventableTrait as EventableTrait;
use DBHelper\BaseCollection\DBHelperCollectionException as DBHelperCollectionException;
use DBHelper\BaseCollection\DBHelperCollectionInterface as DBHelperCollectionInterface;
use DBHelper\BaseCollection\Event\AfterDeleteRecordEvent as AfterDeleteRecordEvent;
use DBHelper\DBHelperFilterCriteriaInterface as DBHelperFilterCriteriaInterface;
use DBHelper\DBHelperFilterSettingsInterface as DBHelperFilterSettingsInterface;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use DBHelper\Traits\AfterRecordCreatedEventTrait as AfterRecordCreatedEventTrait;
use DBHelper\Traits\BeforeCreateEventTrait as BeforeCreateEventTrait;

/**
 * Base management class for a collection of database records
 * from the same table. Has methods to retrieve records and
 * access information about records.
 *
 * > NOTE: Requires the primary key to be an integer `auto_increment`
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
	use EventableTrait;
	use Application_Traits_Loggable;
	use BeforeCreateEventTrait;
	use AfterRecordCreatedEventTrait;

	public function getRecordRequestPrimaryName(): string
	{
		/* ... */
	}


	public function getRecordDefaultSortDir(): string
	{
		/* ... */
	}


	final public function setupComplete(): void
	{
		/* ... */
	}


	public function getParentRecord(): ?DBHelperRecordInterface
	{
		/* ... */
	}


	final public function getInstanceID(): string
	{
		/* ... */
	}


	public function getForeignKeys(): array
	{
		/* ... */
	}


	public function getRecordSearchableKeys(): array
	{
		/* ... */
	}


	public function getRecordSearchableLabels(): array
	{
		/* ... */
	}


	final public function getDataGridName(): string
	{
		/* ... */
	}


	public function getByID(int $record_id): DBHelperRecordInterface
	{
		/* ... */
	}


	final public function refreshRecordsData(): void
	{
		/* ... */
	}


	public function resetCollection(): self
	{
		/* ... */
	}


	public function getByRequest(): ?DBHelperRecordInterface
	{
		/* ... */
	}


	final public function registerRequestParams(): void
	{
		/* ... */
	}


	public function getByKey(string $key, string $value): ?DBHelperRecordInterface
	{
		/* ... */
	}


	public function idExists(int $record_id): bool
	{
		/* ... */
	}


	public function createStubRecord(): DBHelperRecordInterface
	{
		/* ... */
	}


	/**
	 * Retrieves all records from the database, ordered by the default sorting key.
	 *
	 * @return DBHelperRecordInterface[]
	 * @cached
	 */
	public function getAll(): array
	{
		/* ... */
	}


	final public function countRecords(): int
	{
		/* ... */
	}


	public function getFilterCriteria(): DBHelperFilterCriteriaInterface
	{
		/* ... */
	}


	public function getFilterSettings(): DBHelper_BaseFilterSettings
	{
		/* ... */
	}


	/**
	 * @param array<string,mixed> $data
	 * @param bool $silent
	 * @param array<string,mixed> $options Available options:
	 *                     - {@see DBHelperCollectionInterface::OPTION_CUSTOM_RECORD_ID} : int - Use a custom record ID when creating the record.
	 * @return DBHelperRecordInterface
	 */
	public function createNewRecord(array $data = [], bool $silent = false, array $options = []): DBHelperRecordInterface
	{
		/* ... */
	}


	final public function hasRecordIDTable(): bool
	{
		/* ... */
	}


	final public function onBeforeCreateRecord(callable $callback): EventableListener
	{
		/* ... */
	}


	final public function onAfterCreateRecord(callable $callback): EventableListener
	{
		/* ... */
	}


	final public function onAfterDeleteRecord(callable $callback): EventableListener
	{
		/* ... */
	}


	public function deleteRecord(DBHelperRecordInterface $record, bool $silent = false): void
	{
		/* ... */
	}


	/**
	 * @return array<string,string|null|number|array>
	 * @throws DisposableDisposedException
	 */
	#[DisposedAware]
	final public function describe(): array
	{
		/* ... */
	}


	final public function isRecordLoaded(int $recordID): bool
	{
		/* ... */
	}


	public function getChildDisposables(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/BaseCollection/BaseChildCollection.php`

```php
namespace DBHelper\BaseCollection;

use DBHelper as DBHelper;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use DBHelper_BaseCollection as DBHelper_BaseCollection;
use DBHelper_Exception as DBHelper_Exception;

/**
 * DBHelper collection that requires a parent record to be bound
 * from another DBHelper collection.
 *
 * > NOTE: Child collections can be nested, i.e. a child collection
 * > can itself have further child collections.
 *
 * ## Usage
 *
 * 1. Extend this class, implement the abstract methods
 * 2. When creating the collection, specify the parent record in your {@see DBHelper::createCollection()} call.
 *
 * @package DBHelper
 * @subpackage Base Collection
 */
abstract class BaseChildCollection extends DBHelper_BaseCollection implements ChildCollectionInterface
{
	/**
	 * This is only available if the collection has a parent collection.
	 *
	 * @return DBHelperRecordInterface
	 */
	public function getParentRecord(): DBHelperRecordInterface
	{
		/* ... */
	}


	final public function bindParentRecord(?DBHelperRecordInterface $record): void
	{
		/* ... */
	}


	public function getParentCollection(): DBHelperCollectionInterface
	{
		/* ... */
	}


	public function resetCollection(): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/BaseCollection/BaseChildCollection.php`

```php
namespace DBHelper\BaseCollection;

use DBHelper as DBHelper;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use DBHelper_BaseCollection as DBHelper_BaseCollection;
use DBHelper_Exception as DBHelper_Exception;

/**
 * DBHelper collection that requires a parent record to be bound
 * from another DBHelper collection.
 *
 * > NOTE: Child collections can be nested, i.e. a child collection
 * > can itself have further child collections.
 *
 * ## Usage
 *
 * 1. Extend this class, implement the abstract methods
 * 2. When creating the collection, specify the parent record in your {@see DBHelper::createCollection()} call.
 *
 * @package DBHelper
 * @subpackage Base Collection
 */
abstract class BaseChildCollection extends DBHelper_BaseCollection implements ChildCollectionInterface
{
	/**
	 * This is only available if the collection has a parent collection.
	 *
	 * @return DBHelperRecordInterface
	 */
	public function getParentRecord(): DBHelperRecordInterface
	{
		/* ... */
	}


	final public function bindParentRecord(?DBHelperRecordInterface $record): void
	{
		/* ... */
	}


	public function getParentCollection(): DBHelperCollectionInterface
	{
		/* ... */
	}


	public function resetCollection(): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/BaseCollection/ChildCollectionInterface.php`

```php
namespace DBHelper\BaseCollection;

use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;

interface ChildCollectionInterface extends DBHelperCollectionInterface
{
	/**
	 * @return class-string<DBHelperCollectionInterface>
	 */
	public function getParentCollectionClass(): string;


	/**
	 * @return DBHelperRecordInterface Mandatory parent record for child collections.
	 */
	public function getParentRecord(): DBHelperRecordInterface;


	public function getParentCollection(): DBHelperCollectionInterface;
}


```
###  Path: `/src/classes/DBHelper/BaseCollection/ChildCollectionInterface.php`

```php
namespace DBHelper\BaseCollection;

use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;

interface ChildCollectionInterface extends DBHelperCollectionInterface
{
	/**
	 * @return class-string<DBHelperCollectionInterface>
	 */
	public function getParentCollectionClass(): string;


	/**
	 * @return DBHelperRecordInterface Mandatory parent record for child collections.
	 */
	public function getParentRecord(): DBHelperRecordInterface;


	public function getParentCollection(): DBHelperCollectionInterface;
}


```
###  Path: `/src/classes/DBHelper/BaseCollection/DBHelperCollectionException.php`

```php
namespace DBHelper\BaseCollection;

use DBHelper_Exception as DBHelper_Exception;

class DBHelperCollectionException extends DBHelper_Exception
{
	public const ERROR_NO_PARENT_RECORD_BOUND = 16505;
	public const ERROR_IDTABLE_SAME_TABLE_NAME = 16501;
	public const ERROR_CANNOT_START_TWICE = 16506;
	public const ERROR_CANNOT_DELETE_OTHER_COLLECTION_RECORD = 16507;
	public const ERROR_FILTER_SETTINGS_CLASS_NOT_FOUND = 16512;
	public const ERROR_FILTER_CRITERIA_CLASS_NOT_FOUND = 16511;
	public const ERROR_MISSING_REQUIRED_KEYS = 16510;
	public const ERROR_COLLECTION_ALREADY_HAS_PARENT = 16504;
	public const ERROR_CREATE_RECORD_CANCELLED = 16509;
}


```
###  Path: `/src/classes/DBHelper/BaseCollection/DBHelperCollectionException.php`

```php
namespace DBHelper\BaseCollection;

use DBHelper_Exception as DBHelper_Exception;

class DBHelperCollectionException extends DBHelper_Exception
{
	public const ERROR_NO_PARENT_RECORD_BOUND = 16505;
	public const ERROR_IDTABLE_SAME_TABLE_NAME = 16501;
	public const ERROR_CANNOT_START_TWICE = 16506;
	public const ERROR_CANNOT_DELETE_OTHER_COLLECTION_RECORD = 16507;
	public const ERROR_FILTER_SETTINGS_CLASS_NOT_FOUND = 16512;
	public const ERROR_FILTER_CRITERIA_CLASS_NOT_FOUND = 16511;
	public const ERROR_MISSING_REQUIRED_KEYS = 16510;
	public const ERROR_COLLECTION_ALREADY_HAS_PARENT = 16504;
	public const ERROR_CREATE_RECORD_CANCELLED = 16509;
}


```
###  Path: `/src/classes/DBHelper/BaseCollection/DBHelperCollectionInterface.php`

```php
namespace DBHelper\BaseCollection;

use AppUtils\Request as Request;
use Application\Collection\IntegerCollectionInterface as IntegerCollectionInterface;
use Application\EventHandler\Eventables\EventableListener as EventableListener;
use DBHelper\BaseCollection\Event\AfterCreateRecordEvent as AfterCreateRecordEvent;
use DBHelper\BaseCollection\Event\AfterDeleteRecordEvent as AfterDeleteRecordEvent;
use DBHelper\BaseCollection\Event\BeforeCreateRecordEvent as BeforeCreateRecordEvent;
use DBHelper\BaseFilterCriteria\IntegerCollectionFilteringInterface as IntegerCollectionFilteringInterface;
use DBHelper\DBHelperFilterCriteriaInterface as DBHelperFilterCriteriaInterface;
use DBHelper\DBHelperFilterSettingsInterface as DBHelperFilterSettingsInterface;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;

interface DBHelperCollectionInterface extends IntegerCollectionInterface, IntegerCollectionFilteringInterface
{
	public const SORT_DIR_ASC = 'ASC';
	public const VALUE_UNDEFINED = '__undefined';
	public const EVENT_BEFORE_CREATE_RECORD = 'BeforeCreateRecord';
	public const EVENT_AFTER_DELETE_RECORD = 'AfterDeleteRecord';
	public const SORT_DIR_DESC = 'DESC';
	public const EVENT_AFTER_CREATE_RECORD = 'AfterCreateRecord';
	public const OPTION_CUSTOM_RECORD_ID = '__custom_record_id';

	/**
	 * @return class-string<DBHelperRecordInterface>
	 */
	public function getRecordClassName(): string;


	/**
	 * @return class-string<DBHelperFilterCriteriaInterface>
	 */
	public function getRecordFiltersClassName(): string;


	/**
	 * @return class-string<DBHelperFilterSettingsInterface>
	 */
	public function getRecordFilterSettingsClassName(): string;


	/**
	 * @return string
	 */
	public function getRecordDefaultSortKey(): string;


	/**
	 * Retrieves the searchable columns as an associative array
	 * with column name => human-readable label pairs.
	 *
	 * @return array<string,string>
	 */
	public function getRecordSearchableColumns(): array;


	/**
	 * The name of the table storing the records.
	 *
	 * @return string
	 */
	public function getRecordTableName(): string;


	/**
	 * The name of the database column storing the primary key.
	 *
	 * @return string
	 */
	public function getRecordPrimaryName(): string;


	/**
	 * @return string
	 */
	public function getRecordTypeName(): string;


	/**
	 * Human-readable label of the collection, e.g. "Products".
	 *
	 * @return string
	 */
	public function getCollectionLabel(): string;


	/**
	 * Human-readable label of the records, e.g. "Product".
	 *
	 * @return string
	 */
	public function getRecordLabel(): string;


	/**
	 * Attempts to retrieve a record by its ID as specified in the request.
	 *
	 * Uses the request parameter name as returned by {@see self::getRecordRequestPrimaryName()},
	 * with {@see self::getRecordPrimaryName()} as fallback.
	 *
	 * @return DBHelperRecordInterface|NULL
	 */
	public function getByRequest(): ?DBHelperRecordInterface;


	/**
	 * Registers parameter names in the {@see Request} class to
	 * validate request values for this collection. Automatically
	 * called when using {@see self::getByRequest()}.
	 *
	 * @return void
	 */
	public function registerRequestParams(): void;


	/**
	 * Retrieves a single record by a specific record key.
	 * Note that if the key is not unique, the first one
	 * in the result set is used, using the default sorting
	 * key.
	 *
	 * @param string $key
	 * @param string $value
	 * @return DBHelperRecordInterface|NULL
	 */
	public function getByKey(string $key, string $value): ?DBHelperRecordInterface;


	/**
	 * Checks whether a record with the specified ID exists in the database.
	 *
	 * @param integer $record_id
	 * @return boolean
	 */
	public function idExists(int $record_id): bool;


	/**
	 * Creates a stub record of this collection, which can
	 * be used to access the API that may not be available
	 * statically.
	 *
	 * @return DBHelperRecordInterface
	 */
	public function createStubRecord(): DBHelperRecordInterface;


	/**
	 * Retrieves all records from the database, ordered by the default sorting key.
	 *
	 * @return DBHelperRecordInterface[]
	 */
	public function getAll(): array;


	/**
	 * Counts the number of records in total.
	 * @return int
	 */
	public function countRecords(): int;


	/**
	 * Creates the filter criteria for this collection of records,
	 * which is used to query the records.
	 *
	 * @return DBHelperFilterCriteriaInterface
	 */
	public function getFilterCriteria(): DBHelperFilterCriteriaInterface;


	/**
	 * Creates the filter settings for this collection of records,
	 * which is used to configure the filtering options used in lists.
	 *
	 * @return DBHelperFilterSettingsInterface
	 */
	public function getFilterSettings(): DBHelperFilterSettingsInterface;


	/**
	 * Creates a new record with the specified data.
	 *
	 * > NOTE: This does not do any kind of validation,
	 * > you have to ensure that the required keys are
	 * > all present in the data set.
	 *
	 * > NOTE: It is possible to use the {@see DBHelperCollectionInterface::onBeforeCreateRecord()}
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
	 * @return DBHelperRecordInterface
	 */
	public function createNewRecord(array $data = [], bool $silent = false, array $options = []): DBHelperRecordInterface;


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
	 * @return EventableListener
	 * @see BeforeCreateRecordEvent
	 */
	public function onBeforeCreateRecord(callable $callback): EventableListener;


	/**
	 * Listens to any new records created in the collection.
	 * This allows tasks to execute on the collection level
	 * when records are created, as compared to the record's
	 * own created event handled via {@see DBHelperRecordInterface::onCreated()}.
	 *
	 * @param callable(AfterCreateRecordEvent) : void $callback
	 * @return EventableListener
	 * @see AfterCreateRecordEvent
	 */
	public function onAfterCreateRecord(callable $callback): EventableListener;


	/**
	 * Listens to any records deleted from the collection.
	 *
	 * The callback gets an instance of the event:
	 * {@see AfterDeleteRecordEvent}
	 *
	 * @param callable(AfterDeleteRecordEvent) : void $callback
	 * @return EventableListener
	 * @see AfterDeleteRecordEvent
	 */
	public function onAfterDeleteRecord(callable $callback): EventableListener;


	/**
	 * Deletes a record from the collection.
	 *
	 * @param DBHelperRecordInterface $record
	 * @param bool $silent Whether to delete the record silently, without processing events afterwards.
	 *                      The _onDeleted method will still be called for cleanup tasks, but the context
	 *                      will reflect the silent state. The method implementation must check this manually.
	 */
	public function deleteRecord(DBHelperRecordInterface $record, bool $silent = false): void;


	/**
	 * Checks whether a record with the specified ID is loaded in memory.
	 * @param int $recordID
	 * @return bool
	 */
	public function isRecordLoaded(int $recordID): bool;


	/**
	 * Gets the name of the request parameter used to fetch
	 * a collection record when using {@see DBHelperCollectionInterface::getByRequest()}.
	 * Defaults to the same name as the primary key.
	 *
	 * @return string
	 */
	public function getRecordRequestPrimaryName(): string;


	public function getRecordDefaultSortDir(): string;


	/**
	 * Called by the DBHelper once the collection configuration
	 * has been completed.
	 */
	public function setupComplete(): void;


	/**
	 * If the collection has a parent record, it is returned here.
	 *
	 * > NOTE: You can check if the collection is a child collection
	 * > with instanceof {@see BaseChildCollection}, then this method
	 * > will no longer return `null`.
	 *
	 * @return DBHelperRecordInterface|NULL
	 * @see BaseChildCollection::getParentRecord()
	 */
	public function getParentRecord(): ?DBHelperRecordInterface;


	public function getInstanceID(): string;


	/**
	 * Retrieves the foreign keys that should be included in
	 * all queries, as an associative array with key => value pairs.
	 *
	 * @return array<string,string>
	 */
	public function getForeignKeys(): array;


	/**
	 * Gets the names of the keys that are searchable
	 * in this collection.
	 *
	 * @return string[]
	 */
	public function getRecordSearchableKeys(): array;


	/**
	 * Gets the human-readable labels of the searchable fields
	 * in this collection.
	 *
	 * @return string[]
	 */
	public function getRecordSearchableLabels(): array;


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
	public function getDataGridName(): string;


	/**
	 * Retrieves a record by its ID.
	 *
	 * @param int $record_id
	 * @return DBHelperRecordInterface
	 */
	public function getByID(int $record_id): DBHelperRecordInterface;


	/**
	 * Refreshes all data currently loaded in memory with
	 * data from the database.
	 */
	public function refreshRecordsData(): void;


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
	public function resetCollection(): self;
}


```
###  Path: `/src/classes/DBHelper/BaseCollection/DBHelperCollectionInterface.php`

```php
namespace DBHelper\BaseCollection;

use AppUtils\Request as Request;
use Application\Collection\IntegerCollectionInterface as IntegerCollectionInterface;
use Application\EventHandler\Eventables\EventableListener as EventableListener;
use DBHelper\BaseCollection\Event\AfterCreateRecordEvent as AfterCreateRecordEvent;
use DBHelper\BaseCollection\Event\AfterDeleteRecordEvent as AfterDeleteRecordEvent;
use DBHelper\BaseCollection\Event\BeforeCreateRecordEvent as BeforeCreateRecordEvent;
use DBHelper\BaseFilterCriteria\IntegerCollectionFilteringInterface as IntegerCollectionFilteringInterface;
use DBHelper\DBHelperFilterCriteriaInterface as DBHelperFilterCriteriaInterface;
use DBHelper\DBHelperFilterSettingsInterface as DBHelperFilterSettingsInterface;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;

interface DBHelperCollectionInterface extends IntegerCollectionInterface, IntegerCollectionFilteringInterface
{
	public const SORT_DIR_ASC = 'ASC';
	public const VALUE_UNDEFINED = '__undefined';
	public const EVENT_BEFORE_CREATE_RECORD = 'BeforeCreateRecord';
	public const EVENT_AFTER_DELETE_RECORD = 'AfterDeleteRecord';
	public const SORT_DIR_DESC = 'DESC';
	public const EVENT_AFTER_CREATE_RECORD = 'AfterCreateRecord';
	public const OPTION_CUSTOM_RECORD_ID = '__custom_record_id';

	/**
	 * @return class-string<DBHelperRecordInterface>
	 */
	public function getRecordClassName(): string;


	/**
	 * @return class-string<DBHelperFilterCriteriaInterface>
	 */
	public function getRecordFiltersClassName(): string;


	/**
	 * @return class-string<DBHelperFilterSettingsInterface>
	 */
	public function getRecordFilterSettingsClassName(): string;


	/**
	 * @return string
	 */
	public function getRecordDefaultSortKey(): string;


	/**
	 * Retrieves the searchable columns as an associative array
	 * with column name => human-readable label pairs.
	 *
	 * @return array<string,string>
	 */
	public function getRecordSearchableColumns(): array;


	/**
	 * The name of the table storing the records.
	 *
	 * @return string
	 */
	public function getRecordTableName(): string;


	/**
	 * The name of the database column storing the primary key.
	 *
	 * @return string
	 */
	public function getRecordPrimaryName(): string;


	/**
	 * @return string
	 */
	public function getRecordTypeName(): string;


	/**
	 * Human-readable label of the collection, e.g. "Products".
	 *
	 * @return string
	 */
	public function getCollectionLabel(): string;


	/**
	 * Human-readable label of the records, e.g. "Product".
	 *
	 * @return string
	 */
	public function getRecordLabel(): string;


	/**
	 * Attempts to retrieve a record by its ID as specified in the request.
	 *
	 * Uses the request parameter name as returned by {@see self::getRecordRequestPrimaryName()},
	 * with {@see self::getRecordPrimaryName()} as fallback.
	 *
	 * @return DBHelperRecordInterface|NULL
	 */
	public function getByRequest(): ?DBHelperRecordInterface;


	/**
	 * Registers parameter names in the {@see Request} class to
	 * validate request values for this collection. Automatically
	 * called when using {@see self::getByRequest()}.
	 *
	 * @return void
	 */
	public function registerRequestParams(): void;


	/**
	 * Retrieves a single record by a specific record key.
	 * Note that if the key is not unique, the first one
	 * in the result set is used, using the default sorting
	 * key.
	 *
	 * @param string $key
	 * @param string $value
	 * @return DBHelperRecordInterface|NULL
	 */
	public function getByKey(string $key, string $value): ?DBHelperRecordInterface;


	/**
	 * Checks whether a record with the specified ID exists in the database.
	 *
	 * @param integer $record_id
	 * @return boolean
	 */
	public function idExists(int $record_id): bool;


	/**
	 * Creates a stub record of this collection, which can
	 * be used to access the API that may not be available
	 * statically.
	 *
	 * @return DBHelperRecordInterface
	 */
	public function createStubRecord(): DBHelperRecordInterface;


	/**
	 * Retrieves all records from the database, ordered by the default sorting key.
	 *
	 * @return DBHelperRecordInterface[]
	 */
	public function getAll(): array;


	/**
	 * Counts the number of records in total.
	 * @return int
	 */
	public function countRecords(): int;


	/**
	 * Creates the filter criteria for this collection of records,
	 * which is used to query the records.
	 *
	 * @return DBHelperFilterCriteriaInterface
	 */
	public function getFilterCriteria(): DBHelperFilterCriteriaInterface;


	/**
	 * Creates the filter settings for this collection of records,
	 * which is used to configure the filtering options used in lists.
	 *
	 * @return DBHelperFilterSettingsInterface
	 */
	public function getFilterSettings(): DBHelperFilterSettingsInterface;


	/**
	 * Creates a new record with the specified data.
	 *
	 * > NOTE: This does not do any kind of validation,
	 * > you have to ensure that the required keys are
	 * > all present in the data set.
	 *
	 * > NOTE: It is possible to use the {@see DBHelperCollectionInterface::onBeforeCreateRecord()}
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
	 * @return DBHelperRecordInterface
	 */
	public function createNewRecord(array $data = [], bool $silent = false, array $options = []): DBHelperRecordInterface;


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
	 * @return EventableListener
	 * @see BeforeCreateRecordEvent
	 */
	public function onBeforeCreateRecord(callable $callback): EventableListener;


	/**
	 * Listens to any new records created in the collection.
	 * This allows tasks to execute on the collection level
	 * when records are created, as compared to the record's
	 * own created event handled via {@see DBHelperRecordInterface::onCreated()}.
	 *
	 * @param callable(AfterCreateRecordEvent) : void $callback
	 * @return EventableListener
	 * @see AfterCreateRecordEvent
	 */
	public function onAfterCreateRecord(callable $callback): EventableListener;


	/**
	 * Listens to any records deleted from the collection.
	 *
	 * The callback gets an instance of the event:
	 * {@see AfterDeleteRecordEvent}
	 *
	 * @param callable(AfterDeleteRecordEvent) : void $callback
	 * @return EventableListener
	 * @see AfterDeleteRecordEvent
	 */
	public function onAfterDeleteRecord(callable $callback): EventableListener;


	/**
	 * Deletes a record from the collection.
	 *
	 * @param DBHelperRecordInterface $record
	 * @param bool $silent Whether to delete the record silently, without processing events afterwards.
	 *                      The _onDeleted method will still be called for cleanup tasks, but the context
	 *                      will reflect the silent state. The method implementation must check this manually.
	 */
	public function deleteRecord(DBHelperRecordInterface $record, bool $silent = false): void;


	/**
	 * Checks whether a record with the specified ID is loaded in memory.
	 * @param int $recordID
	 * @return bool
	 */
	public function isRecordLoaded(int $recordID): bool;


	/**
	 * Gets the name of the request parameter used to fetch
	 * a collection record when using {@see DBHelperCollectionInterface::getByRequest()}.
	 * Defaults to the same name as the primary key.
	 *
	 * @return string
	 */
	public function getRecordRequestPrimaryName(): string;


	public function getRecordDefaultSortDir(): string;


	/**
	 * Called by the DBHelper once the collection configuration
	 * has been completed.
	 */
	public function setupComplete(): void;


	/**
	 * If the collection has a parent record, it is returned here.
	 *
	 * > NOTE: You can check if the collection is a child collection
	 * > with instanceof {@see BaseChildCollection}, then this method
	 * > will no longer return `null`.
	 *
	 * @return DBHelperRecordInterface|NULL
	 * @see BaseChildCollection::getParentRecord()
	 */
	public function getParentRecord(): ?DBHelperRecordInterface;


	public function getInstanceID(): string;


	/**
	 * Retrieves the foreign keys that should be included in
	 * all queries, as an associative array with key => value pairs.
	 *
	 * @return array<string,string>
	 */
	public function getForeignKeys(): array;


	/**
	 * Gets the names of the keys that are searchable
	 * in this collection.
	 *
	 * @return string[]
	 */
	public function getRecordSearchableKeys(): array;


	/**
	 * Gets the human-readable labels of the searchable fields
	 * in this collection.
	 *
	 * @return string[]
	 */
	public function getRecordSearchableLabels(): array;


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
	public function getDataGridName(): string;


	/**
	 * Retrieves a record by its ID.
	 *
	 * @param int $record_id
	 * @return DBHelperRecordInterface
	 */
	public function getByID(int $record_id): DBHelperRecordInterface;


	/**
	 * Refreshes all data currently loaded in memory with
	 * data from the database.
	 */
	public function refreshRecordsData(): void;


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
	public function resetCollection(): self;
}


```
###  Path: `/src/classes/DBHelper/BaseCollection/Event/AfterCreateRecordEvent.php`

```php
namespace DBHelper\BaseCollection\Event;

use Application\EventHandler\Eventables\BaseEventableEvent as BaseEventableEvent;
use DBHelper\BaseCollection\DBHelperCollectionInterface as DBHelperCollectionInterface;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use DBHelper_BaseCollection_OperationContext_Create as DBHelper_BaseCollection_OperationContext_Create;

class AfterCreateRecordEvent extends BaseEventableEvent
{
	public const EVENT_NAME = 'AfterCreateRecord';

	public function getName(): string
	{
		/* ... */
	}


	public function getCollection(): DBHelperCollectionInterface
	{
		/* ... */
	}


	public function getRecord(): DBHelperRecordInterface
	{
		/* ... */
	}


	public function getContext(): DBHelper_BaseCollection_OperationContext_Create
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/BaseCollection/Event/AfterCreateRecordEvent.php`

```php
namespace DBHelper\BaseCollection\Event;

use Application\EventHandler\Eventables\BaseEventableEvent as BaseEventableEvent;
use DBHelper\BaseCollection\DBHelperCollectionInterface as DBHelperCollectionInterface;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use DBHelper_BaseCollection_OperationContext_Create as DBHelper_BaseCollection_OperationContext_Create;

class AfterCreateRecordEvent extends BaseEventableEvent
{
	public const EVENT_NAME = 'AfterCreateRecord';

	public function getName(): string
	{
		/* ... */
	}


	public function getCollection(): DBHelperCollectionInterface
	{
		/* ... */
	}


	public function getRecord(): DBHelperRecordInterface
	{
		/* ... */
	}


	public function getContext(): DBHelper_BaseCollection_OperationContext_Create
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/BaseCollection/Event/AfterDeleteRecordEvent.php`

```php
namespace DBHelper\BaseCollection\Event;

use Application\EventHandler\Eventables\BaseEventableEvent as BaseEventableEvent;
use DBHelper\BaseCollection\DBHelperCollectionInterface as DBHelperCollectionInterface;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use DBHelper_BaseCollection_OperationContext_Delete as DBHelper_BaseCollection_OperationContext_Delete;

class AfterDeleteRecordEvent extends BaseEventableEvent
{
	public const EVENT_NAME = 'AfterDeleteRecord';

	public function getName(): string
	{
		/* ... */
	}


	public function getCollection(): DBHelperCollectionInterface
	{
		/* ... */
	}


	public function getRecord(): DBHelperRecordInterface
	{
		/* ... */
	}


	public function getContext(): DBHelper_BaseCollection_OperationContext_Delete
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/BaseCollection/Event/AfterDeleteRecordEvent.php`

```php
namespace DBHelper\BaseCollection\Event;

use Application\EventHandler\Eventables\BaseEventableEvent as BaseEventableEvent;
use DBHelper\BaseCollection\DBHelperCollectionInterface as DBHelperCollectionInterface;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use DBHelper_BaseCollection_OperationContext_Delete as DBHelper_BaseCollection_OperationContext_Delete;

class AfterDeleteRecordEvent extends BaseEventableEvent
{
	public const EVENT_NAME = 'AfterDeleteRecord';

	public function getName(): string
	{
		/* ... */
	}


	public function getCollection(): DBHelperCollectionInterface
	{
		/* ... */
	}


	public function getRecord(): DBHelperRecordInterface
	{
		/* ... */
	}


	public function getContext(): DBHelper_BaseCollection_OperationContext_Delete
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/BaseCollection/Event/BeforeCreateRecordEvent.php`

```php
namespace DBHelper\BaseCollection\Event;

use Application\EventHandler\Eventables\BaseEventableEvent as BaseEventableEvent;
use DBHelper\BaseCollection\DBHelperCollectionInterface as DBHelperCollectionInterface;

class BeforeCreateRecordEvent extends BaseEventableEvent
{
	public function getCollection(): DBHelperCollectionInterface
	{
		/* ... */
	}


	/**
	 * @return array<string,mixed>
	 */
	public function getRecordData(): array
	{
		/* ... */
	}


	public function getName(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/BaseCollection/Event/BeforeCreateRecordEvent.php`

```php
namespace DBHelper\BaseCollection\Event;

use Application\EventHandler\Eventables\BaseEventableEvent as BaseEventableEvent;
use DBHelper\BaseCollection\DBHelperCollectionInterface as DBHelperCollectionInterface;

class BeforeCreateRecordEvent extends BaseEventableEvent
{
	public function getCollection(): DBHelperCollectionInterface
	{
		/* ... */
	}


	/**
	 * @return array<string,mixed>
	 */
	public function getRecordData(): array
	{
		/* ... */
	}


	public function getName(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/BaseCollection/Keys.php`

```php
namespace ;

use Application\Disposables\DisposableInterface as DisposableInterface;
use Application\Disposables\DisposableTrait as DisposableTrait;
use Application\EventHandler\Eventables\EventableTrait as EventableTrait;
use DBHelper\BaseCollection\DBHelperCollectionInterface as DBHelperCollectionInterface;

class DBHelper_BaseCollection_Keys implements DisposableInterface
{
	use Application_Traits_Loggable;
	use EventableTrait;
	use DisposableTrait;

	public const ERROR_KEY_ALREADY_REGISTERED = 71401;

	/**
	 * @return DBHelper_BaseCollection_Keys_Key[]
	 */
	public function getAll(): array
	{
		/* ... */
	}


	/**
	 * @return DBHelper_BaseCollection_Keys_Key[]
	 */
	public function getRequired(): array
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @return DBHelper_BaseCollection_Keys_Key
	 * @throws DBHelper_Exception
	 */
	public function register(string $name): DBHelper_BaseCollection_Keys_Key
	{
		/* ... */
	}


	public function getChildDisposables(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/BaseCollection/Keys.php`

```php
namespace ;

use Application\Disposables\DisposableInterface as DisposableInterface;
use Application\Disposables\DisposableTrait as DisposableTrait;
use Application\EventHandler\Eventables\EventableTrait as EventableTrait;
use DBHelper\BaseCollection\DBHelperCollectionInterface as DBHelperCollectionInterface;

class DBHelper_BaseCollection_Keys implements DisposableInterface
{
	use Application_Traits_Loggable;
	use EventableTrait;
	use DisposableTrait;

	public const ERROR_KEY_ALREADY_REGISTERED = 71401;

	/**
	 * @return DBHelper_BaseCollection_Keys_Key[]
	 */
	public function getAll(): array
	{
		/* ... */
	}


	/**
	 * @return DBHelper_BaseCollection_Keys_Key[]
	 */
	public function getRequired(): array
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @return DBHelper_BaseCollection_Keys_Key
	 * @throws DBHelper_Exception
	 */
	public function register(string $name): DBHelper_BaseCollection_Keys_Key
	{
		/* ... */
	}


	public function getChildDisposables(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/BaseCollection/Keys/Key.php`

```php
namespace ;

use AppUtils\Microtime as Microtime;
use Application\AppFactory as AppFactory;
use DBHelper\BaseRecord\BaseRecordException as BaseRecordException;

class DBHelper_BaseCollection_Keys_Key
{
	public function getName(): string
	{
		/* ... */
	}


	public function isRequired(): bool
	{
		/* ... */
	}


	/**
	 * Retrieves the default value for the key.
	 *
	 * IMPORTANT: Also check with `hasDefault()` if
	 * this default value should be used at all. Since
	 * NULL is a valid default value, that is the only
	 * way to check if it's an intentional NULL.
	 *
	 * @return string|null
	 */
	public function getDefault(): ?string
	{
		/* ... */
	}


	public function makeRequired(bool $required = true): DBHelper_BaseCollection_Keys_Key
	{
		/* ... */
	}


	/**
	 * Sets a callback to validate the key value. This must
	 * throw an exception if the value does not match, or it
	 * will have no effect.
	 *
	 * The callback method gets passed the following parameters:
	 *
	 * 1. The value to validate
	 * 2. The full data set being validated (for lookups)
	 * 3. The key instance
	 *
	 * If the value is not valid, the method must throw an exception.
	 *
	 * @param callable(mixed, array<string,mixed>, DBHelper_BaseCollection_Keys_Key) : void $callback
	 * @return $this
	 */
	public function setValidation(callable $callback): self
	{
		/* ... */
	}


	/**
	 * Sets a regular expression validation for the key.
	 *
	 * The regex must be a full regex, including delimiters.
	 *
	 * @param string $regex
	 * @return $this
	 */
	public function setRegexValidation(string $regex): self
	{
		/* ... */
	}


	/**
	 * Whether a default value has been specified.
	 *
	 * @return bool
	 */
	public function hasDefault(): bool
	{
		/* ... */
	}


	public function setDefault(?string $default): DBHelper_BaseCollection_Keys_Key
	{
		/* ... */
	}


	/**
	 * Sets a generation callback function that will be used to generate
	 * the key's value if none has been specified. Takes precedence before
	 * any value set via {@see DBHelper_BaseCollection_Keys_Key::setDefault()}.
	 *
	 * The callback method gets the following parameters:
	 *
	 * 1. The key instance, {@see DBHelper_BaseCollection_Keys_Key}
	 * 2. The full data set array (to enable lookups)
	 *
	 * The method must return the generated value.
	 *
	 * @param callable(DBHelper_BaseCollection_Keys_Key, array<string,mixed>): mixed $callback
	 * @return $this
	 */
	public function setGenerator(callable $callback): DBHelper_BaseCollection_Keys_Key
	{
		/* ... */
	}


	public function hasGenerator(): bool
	{
		/* ... */
	}


	/**
	 * Generates the key's value according to the generation
	 * callback that was set via {@see DBHelper_BaseCollection_Keys_Key::setGenerator()}.
	 *
	 * @param array $data
	 * @return mixed
	 * @throws DBHelper_Exception
	 *
	 * @see DBHelper_BaseCollection_Keys_Key::setGenerator()
	 * @see BaseRecordException::ERROR_CANNOT_GENERATE_KEY_VALUE
	 */
	public function generateValue(array $data): mixed
	{
		/* ... */
	}


	/**
	 * Validates the value using the configured validation.
	 *
	 * @param mixed $value
	 * @param array<string,mixed> $dataSet The full data set being used, for looking up other values for the validation.
	 */
	public function validate(mixed $value, array $dataSet): void
	{
		/* ... */
	}


	public function setMicrotimeGenerator(): self
	{
		/* ... */
	}


	public function setMicrotimeValidation(): self
	{
		/* ... */
	}


	public function setUserValidation(): self
	{
		/* ... */
	}


	/**
	 * @param string[] $allowedValues
	 * @return $this
	 */
	public function setEnumValidation(array $allowedValues): self
	{
		/* ... */
	}


	public function setCurrentUserGenerator(): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/BaseCollection/Keys/Key.php`

```php
namespace ;

use AppUtils\Microtime as Microtime;
use Application\AppFactory as AppFactory;
use DBHelper\BaseRecord\BaseRecordException as BaseRecordException;

class DBHelper_BaseCollection_Keys_Key
{
	public function getName(): string
	{
		/* ... */
	}


	public function isRequired(): bool
	{
		/* ... */
	}


	/**
	 * Retrieves the default value for the key.
	 *
	 * IMPORTANT: Also check with `hasDefault()` if
	 * this default value should be used at all. Since
	 * NULL is a valid default value, that is the only
	 * way to check if it's an intentional NULL.
	 *
	 * @return string|null
	 */
	public function getDefault(): ?string
	{
		/* ... */
	}


	public function makeRequired(bool $required = true): DBHelper_BaseCollection_Keys_Key
	{
		/* ... */
	}


	/**
	 * Sets a callback to validate the key value. This must
	 * throw an exception if the value does not match, or it
	 * will have no effect.
	 *
	 * The callback method gets passed the following parameters:
	 *
	 * 1. The value to validate
	 * 2. The full data set being validated (for lookups)
	 * 3. The key instance
	 *
	 * If the value is not valid, the method must throw an exception.
	 *
	 * @param callable(mixed, array<string,mixed>, DBHelper_BaseCollection_Keys_Key) : void $callback
	 * @return $this
	 */
	public function setValidation(callable $callback): self
	{
		/* ... */
	}


	/**
	 * Sets a regular expression validation for the key.
	 *
	 * The regex must be a full regex, including delimiters.
	 *
	 * @param string $regex
	 * @return $this
	 */
	public function setRegexValidation(string $regex): self
	{
		/* ... */
	}


	/**
	 * Whether a default value has been specified.
	 *
	 * @return bool
	 */
	public function hasDefault(): bool
	{
		/* ... */
	}


	public function setDefault(?string $default): DBHelper_BaseCollection_Keys_Key
	{
		/* ... */
	}


	/**
	 * Sets a generation callback function that will be used to generate
	 * the key's value if none has been specified. Takes precedence before
	 * any value set via {@see DBHelper_BaseCollection_Keys_Key::setDefault()}.
	 *
	 * The callback method gets the following parameters:
	 *
	 * 1. The key instance, {@see DBHelper_BaseCollection_Keys_Key}
	 * 2. The full data set array (to enable lookups)
	 *
	 * The method must return the generated value.
	 *
	 * @param callable(DBHelper_BaseCollection_Keys_Key, array<string,mixed>): mixed $callback
	 * @return $this
	 */
	public function setGenerator(callable $callback): DBHelper_BaseCollection_Keys_Key
	{
		/* ... */
	}


	public function hasGenerator(): bool
	{
		/* ... */
	}


	/**
	 * Generates the key's value according to the generation
	 * callback that was set via {@see DBHelper_BaseCollection_Keys_Key::setGenerator()}.
	 *
	 * @param array $data
	 * @return mixed
	 * @throws DBHelper_Exception
	 *
	 * @see DBHelper_BaseCollection_Keys_Key::setGenerator()
	 * @see BaseRecordException::ERROR_CANNOT_GENERATE_KEY_VALUE
	 */
	public function generateValue(array $data): mixed
	{
		/* ... */
	}


	/**
	 * Validates the value using the configured validation.
	 *
	 * @param mixed $value
	 * @param array<string,mixed> $dataSet The full data set being used, for looking up other values for the validation.
	 */
	public function validate(mixed $value, array $dataSet): void
	{
		/* ... */
	}


	public function setMicrotimeGenerator(): self
	{
		/* ... */
	}


	public function setMicrotimeValidation(): self
	{
		/* ... */
	}


	public function setUserValidation(): self
	{
		/* ... */
	}


	/**
	 * @param string[] $allowedValues
	 * @return $this
	 */
	public function setEnumValidation(array $allowedValues): self
	{
		/* ... */
	}


	public function setCurrentUserGenerator(): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/BaseCollection/OperationContext.php`

```php
namespace ;

use AppUtils\Interfaces\OptionableInterface as OptionableInterface;
use AppUtils\Traits\OptionableTrait as OptionableTrait;
use DBHelper\BaseCollection\DBHelperCollectionInterface as DBHelperCollectionInterface;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;

/**
 * Abstract base class for contexts used when a
 * collection record is created or deleted, to
 * ensure that the operation is authentic.
 *
 * @package Application
 * @subpackage DBHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class DBHelper_BaseCollection_OperationContext implements OptionableInterface
{
	use OptionableTrait;

	public function getDefaultOptions(): array
	{
		/* ... */
	}


	public function getID(): string
	{
		/* ... */
	}


	public function makeSilent(): void
	{
		/* ... */
	}


	public function isSilent(): bool
	{
		/* ... */
	}


	/**
	 * @return DBHelperRecordInterface
	 */
	public function getRecord(): DBHelperRecordInterface
	{
		/* ... */
	}


	public function getCollection(): DBHelperCollectionInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/BaseCollection/OperationContext.php`

```php
namespace ;

use AppUtils\Interfaces\OptionableInterface as OptionableInterface;
use AppUtils\Traits\OptionableTrait as OptionableTrait;
use DBHelper\BaseCollection\DBHelperCollectionInterface as DBHelperCollectionInterface;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;

/**
 * Abstract base class for contexts used when a
 * collection record is created or deleted, to
 * ensure that the operation is authentic.
 *
 * @package Application
 * @subpackage DBHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class DBHelper_BaseCollection_OperationContext implements OptionableInterface
{
	use OptionableTrait;

	public function getDefaultOptions(): array
	{
		/* ... */
	}


	public function getID(): string
	{
		/* ... */
	}


	public function makeSilent(): void
	{
		/* ... */
	}


	public function isSilent(): bool
	{
		/* ... */
	}


	/**
	 * @return DBHelperRecordInterface
	 */
	public function getRecord(): DBHelperRecordInterface
	{
		/* ... */
	}


	public function getCollection(): DBHelperCollectionInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/BaseCollection/OperationContext/Create.php`

```php
namespace ;

/**
 * Context class used when a collection record is deleted,
 * to ensure that the delete operation is authentic.
 *
 * @package Application
 * @subpackage DBHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DBHelper_BaseCollection_OperationContext_Create extends DBHelper_BaseCollection_OperationContext
{
}


```
###  Path: `/src/classes/DBHelper/BaseCollection/OperationContext/Create.php`

```php
namespace ;

/**
 * Context class used when a collection record is deleted,
 * to ensure that the delete operation is authentic.
 *
 * @package Application
 * @subpackage DBHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DBHelper_BaseCollection_OperationContext_Create extends DBHelper_BaseCollection_OperationContext
{
}


```
###  Path: `/src/classes/DBHelper/BaseCollection/OperationContext/Delete.php`

```php
namespace ;

/**
 * Context class used when a collection record is deleted,
 * to ensure that the delete operation is authentic.
 *
 * @package Application
 * @subpackage DBHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DBHelper_BaseCollection_OperationContext_Delete extends DBHelper_BaseCollection_OperationContext
{
}


```
###  Path: `/src/classes/DBHelper/BaseCollection/OperationContext/Delete.php`

```php
namespace ;

/**
 * Context class used when a collection record is deleted,
 * to ensure that the delete operation is authentic.
 *
 * @package Application
 * @subpackage DBHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DBHelper_BaseCollection_OperationContext_Delete extends DBHelper_BaseCollection_OperationContext
{
}


```
###  Path: `/src/classes/DBHelper/BaseCollection/OperationContext/Save.php`

```php
namespace ;

/**
 * Context class used when a collection record has been saved
 * after changes.
 *
 * @package Application
 * @subpackage DBHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DBHelper_BaseCollection_OperationContext_Save extends DBHelper_BaseCollection_OperationContext
{
}


```
###  Path: `/src/classes/DBHelper/BaseCollection/OperationContext/Save.php`

```php
namespace ;

/**
 * Context class used when a collection record has been saved
 * after changes.
 *
 * @package Application
 * @subpackage DBHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DBHelper_BaseCollection_OperationContext_Save extends DBHelper_BaseCollection_OperationContext
{
}


```
###  Path: `/src/classes/DBHelper/BaseFilterCriteria.php`

```php
namespace ;

use Application\Collection\IntegerCollectionItemInterface as IntegerCollectionItemInterface;
use DBHelper\BaseCollection\DBHelperCollectionInterface as DBHelperCollectionInterface;
use DBHelper\BaseFilterCriteria\BaseCollectionFilteringInterface as BaseCollectionFilteringInterface;
use DBHelper\BaseFilterCriteria\IntegerCollectionFilteringInterface as IntegerCollectionFilteringInterface;
use DBHelper\DBHelperFilterCriteriaInterface as DBHelperFilterCriteriaInterface;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;

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
	public function getSearchFields(): array
	{
		/* ... */
	}


	public function getQuery(): string|DBHelper_StatementBuilder
	{
		/* ... */
	}


	/**
	 * @return IntegerCollectionItemInterface[]
	 * @throws DBHelper_Exception
	 */
	public function getItemsObjects(): array
	{
		/* ... */
	}


	/**
	 * @return DBHelper_BaseFilterCriteria_Record[]
	 * @throws DBHelper_Exception
	 */
	public function getItemsDetailed(): array
	{
		/* ... */
	}


	public function getIDKeyName(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/BaseFilterCriteria/BaseCollectionFilteringInterface.php`

```php
namespace DBHelper\BaseFilterCriteria;

use Application\Collection\BaseCollectionInterface as BaseCollectionInterface;

/**
 * Interface for collections that provide filtering capabilities
 * via filter criteria classes. It exposes methods needed by the
 * filter criteria to interact with the collection.
 *
 * > NOTE: This is the base interface. Use the type-specific
 * > interfaces that extend this one for your collections:
 * >
 * > - {@see IntegerCollectionFilteringInterface}
 * > - {@see StringCollectionFilteringInterface}
 *
 * @package DBHelper
 * @subpackage FilterCriteria
 */
interface BaseCollectionFilteringInterface extends BaseCollectionInterface
{
	public function getRecordPrimaryName(): string;


	public function getRecordTableName(): string;


	public function getForeignKeys(): array;


	public function getRecordSearchableKeys(): array;


	public function getRecordDefaultSortKey(): string;


	public function getRecordDefaultSortDir(): string;
}


```
###  Path: `/src/classes/DBHelper/BaseFilterCriteria/BaseCollectionFilteringInterface.php`

```php
namespace DBHelper\BaseFilterCriteria;

use Application\Collection\BaseCollectionInterface as BaseCollectionInterface;

/**
 * Interface for collections that provide filtering capabilities
 * via filter criteria classes. It exposes methods needed by the
 * filter criteria to interact with the collection.
 *
 * > NOTE: This is the base interface. Use the type-specific
 * > interfaces that extend this one for your collections:
 * >
 * > - {@see IntegerCollectionFilteringInterface}
 * > - {@see StringCollectionFilteringInterface}
 *
 * @package DBHelper
 * @subpackage FilterCriteria
 */
interface BaseCollectionFilteringInterface extends BaseCollectionInterface
{
	public function getRecordPrimaryName(): string;


	public function getRecordTableName(): string;


	public function getForeignKeys(): array;


	public function getRecordSearchableKeys(): array;


	public function getRecordDefaultSortKey(): string;


	public function getRecordDefaultSortDir(): string;
}


```
###  Path: `/src/classes/DBHelper/BaseFilterCriteria/IntegerCollectionFilteringInterface.php`

```php
namespace DBHelper\BaseFilterCriteria;

use Application\Collection\IntegerCollectionInterface as IntegerCollectionInterface;

/**
 * Interface for integer-based collections that provide filtering
 * capabilities via filter criteria classes.
 *
 * > NOTE: This does not add any own methods. It brings together
 * > the base filter collection interface and the integer collection
 * > interface for type safety.
 *
 * @package DBHelper
 * @subpackage FilterCriteria
 */
interface IntegerCollectionFilteringInterface extends BaseCollectionFilteringInterface, IntegerCollectionInterface
{
}


```
###  Path: `/src/classes/DBHelper/BaseFilterCriteria/IntegerCollectionFilteringInterface.php`

```php
namespace DBHelper\BaseFilterCriteria;

use Application\Collection\IntegerCollectionInterface as IntegerCollectionInterface;

/**
 * Interface for integer-based collections that provide filtering
 * capabilities via filter criteria classes.
 *
 * > NOTE: This does not add any own methods. It brings together
 * > the base filter collection interface and the integer collection
 * > interface for type safety.
 *
 * @package DBHelper
 * @subpackage FilterCriteria
 */
interface IntegerCollectionFilteringInterface extends BaseCollectionFilteringInterface, IntegerCollectionInterface
{
}


```
###  Path: `/src/classes/DBHelper/BaseFilterCriteria/Record.php`

```php
namespace ;

use AppUtils\ClassHelper as ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException as BaseClassHelperException;
use Application\Collection\IntegerCollectionItemInterface as IntegerCollectionItemInterface;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;

class DBHelper_BaseFilterCriteria_Record
{
	/**
	 * Returns the underlying record object, in the minimum
	 * form of an {@see IntegerCollectionItemInterface}.
	 * You may want to use {@see self::getDBRecord()} instead.
	 *
	 * @return IntegerCollectionItemInterface
	 */
	public function getRecord(): IntegerCollectionItemInterface
	{
		/* ... */
	}


	/**
	 * Assumes that the record is a {@see DBHelperRecordInterface},
	 * and returns it. Throws an exception otherwise.
	 *
	 * > NOTE: Except in rare cases where a DB collection has been
	 * > mocked, the records are of this type. You may safely use
	 * > this method unless otherwise documented.
	 *
	 * @return DBHelperRecordInterface
	 * @throws BaseClassHelperException
	 */
	public function getDBRecord(): DBHelperRecordInterface
	{
		/* ... */
	}


	public function getID(): int
	{
		/* ... */
	}


	public function hasColumn(string $name): bool
	{
		/* ... */
	}


	/**
	 * @return array<string,string|int|float|NULL>
	 */
	public function getColumns(): array
	{
		/* ... */
	}


	public function getColumn(string $name): string
	{
		/* ... */
	}


	public function getColumnInt(string $name): int
	{
		/* ... */
	}


	public function getColumnDate(string $name): ?DateTime
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/BaseFilterCriteria/Record.php`

```php
namespace ;

use AppUtils\ClassHelper as ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException as BaseClassHelperException;
use Application\Collection\IntegerCollectionItemInterface as IntegerCollectionItemInterface;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;

class DBHelper_BaseFilterCriteria_Record
{
	/**
	 * Returns the underlying record object, in the minimum
	 * form of an {@see IntegerCollectionItemInterface}.
	 * You may want to use {@see self::getDBRecord()} instead.
	 *
	 * @return IntegerCollectionItemInterface
	 */
	public function getRecord(): IntegerCollectionItemInterface
	{
		/* ... */
	}


	/**
	 * Assumes that the record is a {@see DBHelperRecordInterface},
	 * and returns it. Throws an exception otherwise.
	 *
	 * > NOTE: Except in rare cases where a DB collection has been
	 * > mocked, the records are of this type. You may safely use
	 * > this method unless otherwise documented.
	 *
	 * @return DBHelperRecordInterface
	 * @throws BaseClassHelperException
	 */
	public function getDBRecord(): DBHelperRecordInterface
	{
		/* ... */
	}


	public function getID(): int
	{
		/* ... */
	}


	public function hasColumn(string $name): bool
	{
		/* ... */
	}


	/**
	 * @return array<string,string|int|float|NULL>
	 */
	public function getColumns(): array
	{
		/* ... */
	}


	public function getColumn(string $name): string
	{
		/* ... */
	}


	public function getColumnInt(string $name): int
	{
		/* ... */
	}


	public function getColumnDate(string $name): ?DateTime
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/BaseFilterCriteria/StringCollectionFilteringInterface.php`

```php
namespace DBHelper\BaseFilterCriteria;

interface StringCollectionFilteringInterface extends BaseCollectionFilteringInterface
{
	public function idExists(string $record_id): bool;
}


```
###  Path: `/src/classes/DBHelper/BaseFilterCriteria/StringCollectionFilteringInterface.php`

```php
namespace DBHelper\BaseFilterCriteria;

interface StringCollectionFilteringInterface extends BaseCollectionFilteringInterface
{
	public function idExists(string $record_id): bool;
}


```
###  Path: `/src/classes/DBHelper/BaseFilterSettings.php`

```php
namespace ;

use DBHelper\BaseCollection\DBHelperCollectionInterface as DBHelperCollectionInterface;
use DBHelper\DBHelperFilterSettingsInterface as DBHelperFilterSettingsInterface;

abstract class DBHelper_BaseFilterSettings extends Application_FilterSettings implements DBHelperFilterSettingsInterface
{
	public function getCollection(): DBHelperCollectionInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/BaseRecord.php`

```php
namespace ;

use AppUtils\ConvertHelper as ConvertHelper;
use Application\Disposables\Attributes\DisposedAware as DisposedAware;
use Application\Disposables\DisposableDisposedException as DisposableDisposedException;
use Application\Disposables\DisposableTrait as DisposableTrait;
use Application\EventHandler\Eventables\EventableListener as EventableListener;
use Application\EventHandler\Eventables\EventableTrait as EventableTrait;
use DBHelper\BaseCollection\DBHelperCollectionInterface as DBHelperCollectionInterface;
use DBHelper\BaseRecord\BaseRecordException as BaseRecordException;
use DBHelper\BaseRecord\Event\KeyModifiedEvent as KeyModifiedEvent;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use DBHelper\Traits\RecordKeyHandlersTrait as RecordKeyHandlersTrait;

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
	use EventableTrait;
	use RecordKeyHandlersTrait;

	public const ERROR_RECORD_DOES_NOT_EXIST = 13301;
	public const ERROR_RECORD_KEY_UNKNOWN = 13302;

	public function getRecordData(): array
	{
		/* ... */
	}


	public function getInstanceID(): string
	{
		/* ... */
	}


	#[DisposedAware]
	final public function refreshData(): void
	{
		/* ... */
	}


	public function getRecordTable(): string
	{
		/* ... */
	}


	public function getRecordPrimaryName(): string
	{
		/* ... */
	}


	public function getRecordTypeName(): string
	{
		/* ... */
	}


	public function isStub(): bool
	{
		/* ... */
	}


	/**
	 * Retrieves the collection used to access records like this.
	 * @return DBHelperCollectionInterface
	 */
	public function getCollection(): DBHelperCollectionInterface
	{
		/* ... */
	}


	public function getID(): int
	{
		/* ... */
	}


	#[DisposedAware]
	public function getRecordKey(string $name, mixed $default = null): mixed
	{
		/* ... */
	}


	#[DisposedAware]
	public function recordKeyExists(string $name): bool
	{
		/* ... */
	}


	#[DisposedAware]
	public function setRecordKey(string $name, mixed $value): bool
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @return bool
	 * @throws DisposableDisposedException
	 * @throws BaseRecordException
	 */
	#[DisposedAware]
	public function requireRecordKeyExists(string $name): bool
	{
		/* ... */
	}


	/**
	 * Whether the record has been modified since the last save, or
	 * just the specified key.
	 *
	 * @param string|NULL $key A single data key to check, or any key if NULL.
	 * @return boolean
	 */
	public function isModified(?string $key = null): bool
	{
		/* ... */
	}


	public function hasStructuralChanges(): bool
	{
		/* ... */
	}


	public function getModifiedKeys(): array
	{
		/* ... */
	}


	public function save(bool $silent = false): bool
	{
		/* ... */
	}


	public function saveChained(bool $silent = false): self
	{
		/* ... */
	}


	public function getParentRecord(): ?DBHelperRecordInterface
	{
		/* ... */
	}


	public function onKeyModified(callable $callback): EventableListener
	{
		/* ... */
	}


	final public function onCreated(DBHelper_BaseCollection_OperationContext_Create $context): void
	{
		/* ... */
	}


	final public function onDeleted(DBHelper_BaseCollection_OperationContext_Delete $context): void
	{
		/* ... */
	}


	final public function onBeforeDelete(DBHelper_BaseCollection_OperationContext_Delete $context): void
	{
		/* ... */
	}


	public function getFormValues(): array
	{
		/* ... */
	}


	public function getChildDisposables(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/BaseRecord/BaseRecordDecorator.php`

```php
namespace DBHelper\BaseRecord;

use Application\Disposables\DisposableTrait as DisposableTrait;
use Application\EventHandler\Eventables\EventableTrait as EventableTrait;
use Application_Traits_Loggable as Application_Traits_Loggable;
use DBHelper\Traits\RecordDecoratorInterface as RecordDecoratorInterface;
use DBHelper\Traits\RecordDecoratorTrait as RecordDecoratorTrait;

/**
 * Abstract base class that can be used to implement a record decorator.
 * It uses the {@see RecordDecoratorTrait} to forward method calls to the
 * decorated record.
 *
 * Alternatively, use the {@see RecordDecoratorTrait} directly in your own
 * class along with the other traits used here.
 *
 * @package DBHelper
 * @subpackage Decorators
 */
abstract class BaseRecordDecorator implements RecordDecoratorInterface
{
	use RecordDecoratorTrait;
	use Application_Traits_Loggable;
	use DisposableTrait;
	use EventableTrait;
}


```
###  Path: `/src/classes/DBHelper/BaseRecord/BaseRecordDecorator.php`

```php
namespace DBHelper\BaseRecord;

use Application\Disposables\DisposableTrait as DisposableTrait;
use Application\EventHandler\Eventables\EventableTrait as EventableTrait;
use Application_Traits_Loggable as Application_Traits_Loggable;
use DBHelper\Traits\RecordDecoratorInterface as RecordDecoratorInterface;
use DBHelper\Traits\RecordDecoratorTrait as RecordDecoratorTrait;

/**
 * Abstract base class that can be used to implement a record decorator.
 * It uses the {@see RecordDecoratorTrait} to forward method calls to the
 * decorated record.
 *
 * Alternatively, use the {@see RecordDecoratorTrait} directly in your own
 * class along with the other traits used here.
 *
 * @package DBHelper
 * @subpackage Decorators
 */
abstract class BaseRecordDecorator implements RecordDecoratorInterface
{
	use RecordDecoratorTrait;
	use Application_Traits_Loggable;
	use DisposableTrait;
	use EventableTrait;
}


```
###  Path: `/src/classes/DBHelper/BaseRecord/BaseRecordException.php`

```php
namespace DBHelper\BaseRecord;

use DBHelper_Exception as DBHelper_Exception;

class BaseRecordException extends DBHelper_Exception
{
	public const ERROR_CANNOT_GENERATE_KEY_VALUE = 87601;
	public const ERROR_RECORD_KEY_INVALID_MICROTIME = 87602;
	public const ERROR_RECORD_KEY_INVALID_USER = 87603;
}


```
###  Path: `/src/classes/DBHelper/BaseRecord/BaseRecordException.php`

```php
namespace DBHelper\BaseRecord;

use DBHelper_Exception as DBHelper_Exception;

class BaseRecordException extends DBHelper_Exception
{
	public const ERROR_CANNOT_GENERATE_KEY_VALUE = 87601;
	public const ERROR_RECORD_KEY_INVALID_MICROTIME = 87602;
	public const ERROR_RECORD_KEY_INVALID_USER = 87603;
}


```
###  Path: `/src/classes/DBHelper/BaseRecord/Event/KeyModifiedEvent.php`

```php
namespace DBHelper\BaseRecord\Event;

use Application\EventHandler\Eventables\BaseEventableEvent as BaseEventableEvent;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;

class KeyModifiedEvent extends BaseEventableEvent
{
	public const EVENT_NAME = 'KeyModified';

	public function getName(): string
	{
		/* ... */
	}


	public function getRecord(): DBHelperRecordInterface
	{
		/* ... */
	}


	public function getKeyName(): string
	{
		/* ... */
	}


	public function getOldValue()
	{
		/* ... */
	}


	public function getNewValue()
	{
		/* ... */
	}


	public function getKeyLabel(): ?string
	{
		/* ... */
	}


	public function isStructural(): bool
	{
		/* ... */
	}


	public function isCustomField(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/BaseRecord/Event/KeyModifiedEvent.php`

```php
namespace DBHelper\BaseRecord\Event;

use Application\EventHandler\Eventables\BaseEventableEvent as BaseEventableEvent;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;

class KeyModifiedEvent extends BaseEventableEvent
{
	public const EVENT_NAME = 'KeyModified';

	public function getName(): string
	{
		/* ... */
	}


	public function getRecord(): DBHelperRecordInterface
	{
		/* ... */
	}


	public function getKeyName(): string
	{
		/* ... */
	}


	public function getOldValue()
	{
		/* ... */
	}


	public function getNewValue()
	{
		/* ... */
	}


	public function getKeyLabel(): ?string
	{
		/* ... */
	}


	public function isStructural(): bool
	{
		/* ... */
	}


	public function isCustomField(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/BaseRecordSettings.php`

```php
namespace DBHelper;

use Application_Formable_RecordSettings_Extended as Application_Formable_RecordSettings_Extended;
use Application_Formable_RecordSettings_ValueSet as Application_Formable_RecordSettings_ValueSet;
use Application_Interfaces_Formable as Application_Interfaces_Formable;
use DBHelper\BaseCollection\DBHelperCollectionInterface as DBHelperCollectionInterface;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;

/**
 * Base class for managing the form used to create or
 * edit DBHelper records.
 *
 * @package DBHelper
 * @subpackage Collection
 */
abstract class BaseRecordSettings extends Application_Formable_RecordSettings_Extended
{
}


```
###  Path: `/src/classes/DBHelper/CaseStatement.php`

```php
namespace ;

use AppUtils\Interfaces\StringableInterface as StringableInterface;

/**
 * Helper class used to build `CASE` SQL statements.
 *
 * @package DBHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DBHelper_CaseStatement implements StringableInterface
{
	/**
	 * @param string|DBHelper_StatementBuilder $sourceColumn
	 * @return DBHelper_CaseStatement
	 */
	public static function create($sourceColumn): DBHelper_CaseStatement
	{
		/* ... */
	}


	public function addIntString(int $case, string $value): DBHelper_CaseStatement
	{
		/* ... */
	}


	public function addString(string $case, string $value): DBHelper_CaseStatement
	{
		/* ... */
	}


	public function addInt(int $case, int $value): DBHelper_CaseStatement
	{
		/* ... */
	}


	public function addStringInt(string $case, int $value): DBHelper_CaseStatement
	{
		/* ... */
	}


	/**
	 * @return string
	 */
	public function render(): string
	{
		/* ... */
	}


	public function __toString()
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/DBHelper.php`

```php
namespace ;

use AppUtils\ClassHelper as ClassHelper;
use AppUtils\ConvertHelper as ConvertHelper;
use AppUtils\ConvertHelper_Exception as ConvertHelper_Exception;
use AppUtils\FileHelper\FolderInfo as FolderInfo;
use AppUtils\Highlighter as Highlighter;
use AppUtils\Interfaces\StringableInterface as StringableInterface;
use AppUtils\Microtime as Microtime;
use Application\ConfigSettings\AppConfig as AppConfig;
use DBHelper\BaseCollection\BaseChildCollection as BaseChildCollection;
use DBHelper\BaseCollection\DBHelperCollectionInterface as DBHelperCollectionInterface;
use DBHelper\Exception\CLIErrorRenderer as CLIErrorRenderer;
use DBHelper\Exception\HTMLErrorRenderer as HTMLErrorRenderer;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use DBHelper\TrackedQuery as TrackedQuery;

/**
 * Simple database utility class used tu run queries against
 * the database using the main PDO object. Does not abstract
 * database access by design; merely simplifies the code
 * required to run a query.
 *
 * Queries themselves have to be created manually, making it
 * easier to maintain individual queries as opposed to a
 * know-it-all approach that is often quite hermetic.
 *
 * @package Helpers
 * @subpackage DBHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DBHelper
{
	public const ERROR_EXECUTING_QUERY = 33871001;
	public const ERROR_PREPARING_QUERY = 33871002;
	public const ERROR_INSERTING = 33871003;
	public const ERROR_FETCHING = 33871004;
	public const ERROR_CONNECTING = 33871005;
	public const ERROR_CANNOT_ROLL_BACK_TRANSACTION = 33871008;
	public const ERROR_CANNOT_COMMIT_TRANSACTION = 33871009;
	public const ERROR_CANNOT_START_TRANSACTION = 33871010;
	public const ERROR_NO_ACTIVE_STATEMENT = 33871011;
	public const ERROR_TRANSACTION_REQUIRED_FOR_OPERATION = 33871012;
	public const ERROR_CONNECTING_NO_DRIVER = 33871013;
	public const ERROR_NOT_A_DBHELPER_COLLECTION = 33871014;
	public const ERROR_NO_PARENT_RECORD_SPECIFIED = 33871015;
	public const ERROR_INVALID_PARENT_RECORD = 33871016;
	public const ERROR_INVALID_TABLE_NAME = 338701017;
	public const ERROR_INVALID_COLUMN_NAME = 338701018;
	public const ERROR_DB_NOT_REGISTERED = 338701019;
	public const ERROR_CANNOT_CONVERT_OBJECT = 338701021;
	public const ERROR_CANNOT_CONVERT_ARRAY = 338701022;
	public const ERROR_CANNOT_CONVERT_RESOURCE = 338701023;
	public const ERROR_EMPTY_WHERE = 338701024;
	public const INSERTORUPDATE_UPDATE = 'upd';

	/**
	 * Executes a query string with the specified variables.
	 *
	 * Uses the <code>PDO->prepare()</code> method to prepare the query.
	 * The result is returned by the <code>PDOStatement->execute()</code> method.
	 *
	 * If the query fails, the error information can be accessed via
	 * {@see self::getErrorMessage()}.
	 *
	 * @param int $operationType
	 * @param string|DBHelper_StatementBuilder $statementOrBuilder The full SQL query to run with placeholders for variables
	 * @param array<string,mixed> $variables Associative array with placeholders and values to replace in the query
	 * @param bool $exceptionOnError
	 * @return boolean
	 * @throws DBHelper_Exception
	 * @throws JsonException
	 * @see getErrorCode()
	 * @see getErrorMessage()
	 */
	public static function execute(
		int $operationType,
		$statementOrBuilder,
		array $variables = [],
		bool $exceptionOnError = true,
	): bool
	{
		/* ... */
	}


	/**
	 * Converts all values in the variable collection to
	 * database compatible values, converting them as
	 * necessary.
	 *
	 * @param array<string,string|number|StringableInterface|Microtime|DateTime|bool|NULL> $variables
	 * @return array<string,string|NULL>
	 * @throws DBHelper_Exception
	 *
	 * @see ConvertHelper::ERROR_INVALID_BOOLEAN_STRING
	 * @see DBHelper::ERROR_CANNOT_CONVERT_OBJECT
	 * @see DBHelper::ERROR_CANNOT_CONVERT_ARRAY
	 */
	public static function filterVariablesForDB(array $variables): array
	{
		/* ... */
	}


	/**
	 * @param mixed $value
	 * @return string|NULL
	 * @throws DBHelper_Exception
	 *
	 * @see ConvertHelper::ERROR_INVALID_BOOLEAN_STRING
	 * @see DBHelper::ERROR_CANNOT_CONVERT_OBJECT
	 * @see DBHelper::ERROR_CANNOT_CONVERT_ARRAY
	 */
	public static function filterValueForDB($value): ?string
	{
		/* ... */
	}


	/**
	 * Runs an insert query and returns the insert ID if applicable.
	 * Note that this method requires a full INSERT query, it does
	 * not automate anything. The only difference to the {@link execute()}
	 * method is that it returns the insert ID.
	 *
	 * For tables that have no autoincrement fields, this will return
	 * a null value. As it triggers an exception in all cases where
	 * something could go wrong, there is no need to check the return
	 * value of this method.
	 *
	 * @param string|DBHelper_StatementBuilder $statementOrBuilder
	 * @param array<string,mixed> $variables
	 * @return string
	 * @throws DBHelper_Exception
	 */
	public static function insert($statementOrBuilder, array $variables = []): string
	{
		/* ... */
	}


	/**
	 * Like `insert`, but converts the result to an integer.
	 *
	 * @param string|DBHelper_StatementBuilder $statementOrBuilder
	 * @param array $variables
	 * @return int
	 *
	 * @throws DBHelper_Exception
	 */
	public static function insertInt($statementOrBuilder, array $variables = []): int
	{
		/* ... */
	}


	/**
	 * Returns the error message from the last query that was run, if any.
	 * @return string
	 * @see getErrorCode()
	 */
	public static function getErrorMessage(): string
	{
		/* ... */
	}


	/**
	 * Returns the error code from the last query that was run, if any.
	 * @return string
	 * @see getErrorMessage()
	 */
	public static function getErrorCode(): string
	{
		/* ... */
	}


	/**
	 * Retrieves the raw SQL query string from the last query, if any.
	 * @return string
	 */
	public static function getSQL(): string
	{
		/* ... */
	}


	/**
	 * @return array{0:string,1:array<string,mixed>}|null
	 */
	public static function getActiveQuery(): ?array
	{
		/* ... */
	}


	/**
	 * @param string $query
	 * @param array<string,mixed> $variables
	 * @return string
	 */
	public static function formatQuery(string $query, array $variables): string
	{
		/* ... */
	}


	public static function getSQLHighlighted(): string
	{
		/* ... */
	}


	/**
	 * Runs an update query. This is an alias for the {@link execute()}
	 * method, which exists for semantic purposes and the possibility
	 * to add specific functionality at a later time. It is recommended
	 * to use this method if you run UPDATE queries.
	 *
	 * @param string|DBHelper_StatementBuilder $statementOrBuilder The full SQL query to run with placeholders for variables
	 * @param array<string,string|number|StringableInterface|Microtime|DateTime|bool|NULL> $variables Associative array with placeholders and values to replace in the query
	 * @return boolean
	 * @throws DBHelper_Exception
	 */
	public static function update($statementOrBuilder, array $variables = []): bool
	{
		/* ... */
	}


	/**
	 * Runs a "DELETE" query. This is an alias for the {@link execute()}
	 * method, which exists for semantic purposes and the possibility
	 * to add specific functionality at a later time. It is recommended
	 * to use this method if you run DELETE queries.
	 *
	 * @param string|DBHelper_StatementBuilder $statementOrBuilder The full SQL query to run with placeholders for variables
	 * @param array<string,string|number|StringableInterface|Microtime|DateTime|bool|NULL> $variables Associative array with placeholders and values to replace in the query
	 * @return boolean
	 * @throws DBHelper_Exception
	 * @throws JsonException
	 */
	public static function delete($statementOrBuilder, array $variables = []): bool
	{
		/* ... */
	}


	/**
	 * Fetches a single entry as an associative array from a SELECT query.
	 * @param string|DBHelper_StatementBuilder $statementOrBuilder The full SQL query to run with placeholders for variables
	 * @param array<string,string|number|StringableInterface|Microtime|DateTime|bool|NULL> $variables Associative array with placeholders and values to replace in the query
	 * @return array<int|string,string|int|float|NULL>|NULL
	 * @throws DBHelper_Exception
	 * @throws JsonException
	 */
	public static function fetch($statementOrBuilder, array $variables = []): ?array
	{
		/* ... */
	}


	/**
	 * Like {@link fetch()}, but builds the query dynamically to
	 * fetch data from a single table.
	 *
	 * @param string $table The table name
	 * @param array<string,string|number|StringableInterface|Microtime|DateTime|bool|NULL> $where Any "WHERE" column values required
	 * @param string[] $columnNames The columns to fetch. Defaults to all columns if empty.
	 * @return array<int|string,string|int|float|NULL>|NULL
	 * @throws DBHelper_Exception
	 */
	public static function fetchData(string $table, array $where = [], array $columnNames = []): ?array
	{
		/* ... */
	}


	/**
	 * Fetches all entries matching a SELECT query, as an indexed array
	 * with associative arrays for each record.
	 *
	 * @param string|DBHelper_StatementBuilder $statementOrBuilder The full SQL query to run with placeholders for variables
	 * @param array<string,string|number|StringableInterface|Microtime|DateTime|bool|NULL> $variables Associative array with placeholders and values to replace in the query
	 * @return array<int,array<int|string,string|int|float|NULL>>
	 * @throws DBHelper_Exception
	 */
	public static function fetchAll(string|DBHelper_StatementBuilder $statementOrBuilder, array $variables = []): array
	{
		/* ... */
	}


	/**
	 * Retrieves the current query count. This has to be
	 * done at the end of the request to be accurate for the total
	 * number of queries in a request.
	 *
	 * @return int
	 */
	public static function getQueryCount(): int
	{
		/* ... */
	}


	public static function getLimitSQL(int $limit = 0, int $offset = 0): string
	{
		/* ... */
	}


	/**
	 * Retrieves all queries executed so far, optionally restricted
	 * to only the specified types.
	 *
	 * > NOTE: Only available if the tracking of queries is enabled,
	 * > see {@see self::isQueryTrackingEnabled()}.
	 *
	 * @param int[] $types
	 * @return TrackedQuery[]
	 */
	public static function getQueries(array $types = []): array
	{
		/* ... */
	}


	public static function isQueryTrackingEnabled(): bool
	{
		/* ... */
	}


	public static function setQueryTrackingEnabled(bool $enabled): void
	{
		/* ... */
	}


	public static function enableQueryTracking(): void
	{
		/* ... */
	}


	public static function disableQueryTracking(): void
	{
		/* ... */
	}


	/**
	 * Retrieves information about all queries made up to this point,
	 * but only write operations.
	 *
	 * @return TrackedQuery[]
	 */
	public static function getWriteQueries(): array
	{
		/* ... */
	}


	/**
	 * @return TrackedQuery[]
	 */
	public static function getSelectQueries(): array
	{
		/* ... */
	}


	public static function countSelectQueries(): int
	{
		/* ... */
	}


	public static function countWriteQueries(): int
	{
		/* ... */
	}


	/**
	 * @return int
	 */
	public static function countQueries(): int
	{
		/* ... */
	}


	/**
	 * Retrieves a list of all tables present in the
	 * database; Only shows the tables that the user
	 * has access to. Returns an indexed array with
	 * table names.
	 *
	 * @throws DBHelper_Exception
	 * @return string[]
	 * @cached
	 */
	public static function getTablesList(): array
	{
		/* ... */
	}


	/**
	 * Truncates the specified table (deletes all rows if any).
	 * Returns true on success, false on failure.
	 *
	 * @param string $tableName
	 * @throws DBHelper_Exception
	 * @return boolean
	 */
	public static function truncate(string $tableName): bool
	{
		/* ... */
	}


	/**
	 * Starts a new transaction.
	 *
	 * NOTE: The transaction must be committed or rolled
	 * back once all statements have been run.
	 *
	 * @return boolean
	 * @throws DBHelper_Exception
	 * @see commitTransaction()
	 * @see rollbackTransaction()
	 */
	public static function startTransaction(): bool
	{
		/* ... */
	}


	public static function startConditional(): void
	{
		/* ... */
	}


	public static function commitConditional(): void
	{
		/* ... */
	}


	/**
	 * Checks whether a transaction has been started.
	 * @return boolean
	 */
	public static function isTransactionStarted(): bool
	{
		/* ... */
	}


	/**
	 * Commits a previously started transaction by applying
	 * all changes made permanently.
	 *
	 * @return boolean
	 * @throws DBHelper_Exception
	 * @see rollbackTransaction()
	 * @see startTransaction()
	 */
	public static function commitTransaction(): bool
	{
		/* ... */
	}


	/**
	 * Rolls back a previously started transaction by cancelling
	 * all changes.
	 *
	 * @throws DBHelper_Exception
	 * @return boolean
	 * @see commitTransaction()
	 * @see startTransaction()
	 */
	public static function rollbackTransaction(): bool
	{
		/* ... */
	}


	public static function rollbackConditional(): void
	{
		/* ... */
	}


	public static function init(): void
	{
		/* ... */
	}


	public static function registerDB(
		string $id,
		string $name,
		string $username,
		string $password,
		string $host,
		int $port = 0,
	): void
	{
		/* ... */
	}


	public static function selectDB(string $id): void
	{
		/* ... */
	}


	public static function hasListener(string $eventName): bool
	{
		/* ... */
	}


	/**
	 * @param callable $handler
	 * @param mixed|NULL $data
	 * @return int
	 */
	public static function onInit(callable $handler, $data = null): int
	{
		/* ... */
	}


	/**
	 * Removes all listeners to the specified event, if any.
	 * @param string $eventName
	 */
	public static function removeListeners(string $eventName): void
	{
		/* ... */
	}


	/**
	 * Removes a specific event listener by its ID if it exists.
	 * @param int $listenerID
	 */
	public static function removeListener(int $listenerID): void
	{
		/* ... */
	}


	/**
	 * @return array{name:string,username:string,password:string,host:string,port:int}
	 * @throws DBHelper_Exception
	 */
	public static function getSelectedDB(): array
	{
		/* ... */
	}


	/**
	 * Retrieves the PDO database connection object for
	 * the currently selected DB account.
	 *
	 * @throws DBHelper_Exception
	 * @return PDO
	 */
	public static function getDB(): PDO
	{
		/* ... */
	}


	/**
	 * Retrieves the active database's URI, without authentication information.
	 *
	 * @return string
	 * @throws DBHelper_Exception
	 */
	public static function getDBUri(): string
	{
		/* ... */
	}


	/**
	 * Simple helper method for retrieving the counter from a
	 * count query: the result of the query merely has to
	 * contain a "count" field which is then returned. In all
	 * other cases, this will return 0.
	 *
	 * @param string|DBHelper_StatementBuilder $statementOrBuilder
	 * @param array<string,mixed> $variables
	 * @param string $columName
	 * @return int
	 *
	 * @throws DBHelper_Exception
	 * @throws JsonException
	 */
	public static function fetchCount($statementOrBuilder, array $variables = [], string $columName = 'count'): int
	{
		/* ... */
	}


	/**
	 * Builds a limit statement to append to a query string
	 * using the specified limit and offset values. Returns
	 * an empty string if both are set to 0.
	 *
	 * @param int $limit
	 * @param int $offset
	 * @return string
	 */
	public static function buildLimitStatement(int $limit = 0, int $offset = 0): string
	{
		/* ... */
	}


	/**
	 * Utility method that either inserts or updates an existing record.
	 *
	 * @param string $table
	 * @param array<string,mixed> $data
	 * @param string[] $primaryFieldNames
	 * @return string The insert ID in case of an insert operation, or the update status code.
	 * @throws ConvertHelper_Exception
	 * @throws DBHelper_Exception
	 * @throws JsonException
	 * @see DBHelper::INSERTORUPDATE_UPDATE
	 */
	public static function insertOrUpdate(string $table, array $data, array $primaryFieldNames): string
	{
		/* ... */
	}


	/**
	 * Updates a table by building the query dynamically.
	 *
	 * @param string $table
	 * @param array<string,mixed> $data
	 * @param string[] $primaryFields
	 * @throws DBHelper_Exception
	 * @return boolean
	 */
	public static function updateDynamic(string $table, array $data, array $primaryFields): bool
	{
		/* ... */
	}


	/**
	 * Builds a list of columns to set values for, with value placeholders
	 * named after the column names.
	 *
	 * @param array<string,string|number|NULL> $data
	 * @return string
	 */
	public static function buildSetStatement(array $data): string
	{
		/* ... */
	}


	/**
	 * Enables debugging, which will output all queries as they are run.
	 */
	public static function enableDebugging(): void
	{
		/* ... */
	}


	/**
	 * Disables the debug mode.
	 */
	public static function disableDebugging(): void
	{
		/* ... */
	}


	/**
	 * @return string[]
	 * @throws DBHelper_Exception
	 * @deprecated Use {@see DBHelper::getTablesList()} instead.
	 * @see DBHelper::getTablesList()
	 */
	public static function fetchTableNames(): array
	{
		/* ... */
	}


	public static function dropTables(): bool
	{
		/* ... */
	}


	public static function getDropTablesQuery(): string
	{
		/* ... */
	}


	public static function setLogCallback(callable $callback): void
	{
		/* ... */
	}


	public static function countAffectedRows(): int
	{
		/* ... */
	}


	/**
	 * Checks whether the specified column exists in the target table.
	 *
	 * @param string $tableName
	 * @param string $columnName
	 * @return boolean
	 * @throws ConvertHelper_Exception
	 * @throws DBHelper_Exception
	 */
	public static function columnExists(string $tableName, string $columnName): bool
	{
		/* ... */
	}


	/**
	 * Fetches a single row using the {@link fetch()} method, and returns
	 * the specified key from the result set. Returns null if the key is
	 * not found.
	 *
	 * @param string $key
	 * @param string|DBHelper_StatementBuilder $statementOrBuilder
	 * @param array<string,mixed> $variables
	 * @return string|int|float|NULL
	 *
	 * @throws DBHelper_Exception
	 * @throws JsonException
	 */
	public static function fetchKey(string $key, $statementOrBuilder, array $variables = []): string|int|float|null
	{
		/* ... */
	}


	public static function createFetchKey(string $key, string $table): DBHelper_FetchKey
	{
		/* ... */
	}


	public static function createFetchOne(string $table): DBHelper_FetchOne
	{
		/* ... */
	}


	public static function createFetchMany(string $table): DBHelper_FetchMany
	{
		/* ... */
	}


	/**
	 * Fetches a key, and returns an integer value.
	 *
	 * @param string $key
	 * @param string|DBHelper_StatementBuilder $statementOrBuilder
	 * @param array<string,mixed> $variables
	 * @return integer
	 *
	 * @throws DBHelper_Exception
	 * @throws JsonException
	 */
	public static function fetchKeyInt(string $key, $statementOrBuilder, array $variables = []): int
	{
		/* ... */
	}


	/**
	 * Fetches all matching rows using the {@link fetchAll()} method, and
	 * returns an indexed array with all available values for the specified
	 * key in the result set. Returns an empty array if the key is not found,
	 * or if no records match.
	 *
	 * @param string $key
	 * @param string|DBHelper_StatementBuilder $statementOrBuilder
	 * @param array<string,mixed> $variables
	 * @return array<int,string|int|float|NULL>
	 *
	 * @throws DBHelper_Exception
	 */
	public static function fetchAllKey(string $key, $statementOrBuilder, array $variables = []): array
	{
		/* ... */
	}


	/**
	 * Like <code>fetchAllKey</code>, but enforces int
	 * values for all values that were found.
	 *
	 * @param string $key
	 * @param string|DBHelper_StatementBuilder $statementOrBuilder
	 * @param array<string,mixed> $variables
	 * @return int[]
	 *
	 * @throws DBHelper_Exception
	 */
	public static function fetchAllKeyInt(string $key, $statementOrBuilder, array $variables = []): array
	{
		/* ... */
	}


	/**
	 * Allows only column names with lowercase letters,
	 * no numbers, and underscores. Must begin with a
	 * letter, and end with a letter.
	 *
	 * @param string $column
	 * @throws DBHelper_Exception
	 */
	public static function validateColumnName(string $column): void
	{
		/* ... */
	}


	/**
	 * Allows only table names with lowercase letters,
	 * no numbers, and underscores. Must begin with a
	 * letter, and end with a letter.
	 *
	 * @param string $name
	 * @throws DBHelper_Exception
	 */
	public static function validateTableName(string $name): void
	{
		/* ... */
	}


	/**
	 * Deletes all records from the target table matching the
	 * specified column values, if any. Otherwise, all records
	 * are deleted.
	 *
	 * @param string $table The target table name.
	 * @param array<string,string|number|StringableInterface|Microtime|DateTime|bool|NULL> $where Associative array with column > value pairs for the where statement.
	 * @return boolean
	 */
	public static function deleteRecords(string $table, array $where = []): bool
	{
		/* ... */
	}


	/**
	 * Checks whether the specified key exists in the target table.
	 *
	 * Example:
	 *
	 * <pre>
	 * // with a single key value
	 * keyExists('tablename', array('primary_key' => 2));
	 *
	 * // with a complex key value
	 * keyExists(
	 *     'tablename',
	 *     array(
	 *         'first_key' => 5,
	 *         'second_key' => 'text',
	 *         'third_key' => 'yes'
	 *     )
	 * );
	 * </pre>
	 *
	 * @param string $table
	 * @param array<string,string|number|StringableInterface|Microtime|DateTime|bool|NULL> $where Associative array with key => value pairs to check
	 * @return boolean
	 * @throws ConvertHelper_Exception
	 * @throws DBHelper_Exception
	 */
	public static function keyExists(string $table, array $where): bool
	{
		/* ... */
	}


	/**
	 * Checks whether the specified table name exists.
	 * Warning: Case sensitive!
	 *
	 * @param string $tableName
	 * @return boolean
	 */
	public static function tableExists(string $tableName): bool
	{
		/* ... */
	}


	/**
	 * Inserts a record by building the SQL statement dynamically from
	 * the provided data set. Note that no validation happens here: you
	 * must ensure that all required columns have a value.
	 *
	 * Note: columns with a null value are assumed to be nullable and will
	 * be set to NULL accordingly.
	 *
	 * @param string $tableName
	 * @param array<string,string|number|StringableInterface|Microtime|DateTime|bool|NULL> $data
	 * @return string The insert ID, if any
	 */
	public static function insertDynamic(string $tableName, array $data = []): string
	{
		/* ... */
	}


	/**
	 * Checks whether the specified table column is an auto
	 * increment column.
	 *
	 * @param string $tableName
	 * @param string $columnName
	 * @return boolean
	 * @throws ConvertHelper_Exception
	 * @throws DBHelper_Exception
	 */
	public static function isAutoIncrementColumn(string $tableName, string $columnName): bool
	{
		/* ... */
	}


	/**
	 * Utility method that can be used to enforce an active
	 * DB transaction. Throws an exception if a DB transaction
	 * is not present.
	 *
	 * @param string $operationLabel A short description for the operation that requires the transaction, added to the exception details
	 * @throws DBHelper_Exception
	 */
	public static function requireTransaction(string $operationLabel): void
	{
		/* ... */
	}


	/**
	 * Checks whether a record matching the specified
	 * fields exists in the table.
	 *
	 * @param string $table
	 * @param array $where
	 * @return boolean
	 */
	public static function recordExists(string $table, array $where): bool
	{
		/* ... */
	}


	/**
	 * Builds the condition statement for the selected fields
	 * from a list of fields and values, connected by <code>AND</code>.
	 * The bound variable names match the field names.
	 *
	 * @param array<string,string|number|StringableInterface|Microtime|DateTime|bool|NULL> $params
	 * @return string
	 */
	public static function buildWhereFieldsStatement(array $params): string
	{
		/* ... */
	}


	/**
	 * Adds an event listener that is called before a database write operation.
	 * @param callable $eventCallback
	 * @param mixed|NULL $data
	 * @return int
	 */
	public static function onBeforeWriteOperation(callable $eventCallback, $data = null): int
	{
		/* ... */
	}


	/**
	 * Clears/resets the internal collection instances cache,
	 * which will force them to be created anew if requested.
	 *
	 * @return void
	 */
	public static function clearCollections(): void
	{
		/* ... */
	}


	/**
	 * @param string $class
	 * @param DBHelperRecordInterface|NULL $parentRecord
	 * @param bool $newInstance
	 * @return DBHelperCollectionInterface
	 * @throws DBHelper_Exception
	 */
	public static function createCollection(
		string $class,
		?DBHelperRecordInterface $parentRecord = null,
		bool $newInstance = false,
	): DBHelperCollectionInterface
	{
		/* ... */
	}


	/**
	 * Fetches all fields from other tables in the database that
	 * have relations to the specified field.
	 *
	 * NOTE: Results are cached within the request.
	 *
	 * @param string $tableName
	 * @param string $fieldName
	 * @return array<int,array{tablename:string,columname:string}>
	 * @throws ConvertHelper_Exception
	 * @throws DBHelper_Exception
	 * @see https://stackoverflow.com/a/31972009/2298192
	 */
	public static function getRelationsForField(string $tableName, string $fieldName): array
	{
		/* ... */
	}


	public static function escapeName(string $name): string
	{
		/* ... */
	}


	public static function escapeTableColumn(string $table, string $name): string
	{
		/* ... */
	}


	public static function resetTrackedQueries(): void
	{
		/* ... */
	}


	public static function getDriverName(): ?string
	{
		/* ... */
	}


	/**
	 * Builds a SQL LIKE statement for the specified column,
	 * search term, and case sensitivity setting. Attempts to
	 * use the most efficient syntax for the active database
	 * driver.
	 *
	 * @param string $column
	 * @param string $searchTerm
	 * @param bool $caseSensitive
	 * @return string
	 */
	public static function buildLIKEStatement(string $column, string $searchTerm, bool $caseSensitive): string
	{
		/* ... */
	}


	public static function getAPIMethodsFolder(): FolderInfo
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/DBHelperFilterCriteriaInterface.php`

```php
namespace DBHelper;

use Application\FilterCriteria\FilterCriteriaDBExtendedInterface as FilterCriteriaDBExtendedInterface;
use DBHelper\BaseFilterCriteria\IntegerCollectionFilteringInterface as IntegerCollectionFilteringInterface;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use DBHelper_BaseFilterCriteria_Record as DBHelper_BaseFilterCriteria_Record;

interface DBHelperFilterCriteriaInterface extends FilterCriteriaDBExtendedInterface
{
	/**
	 * @return DBHelperRecordInterface[]
	 */
	public function getItemsObjects(): array;


	/**
	 * @return DBHelper_BaseFilterCriteria_Record[]
	 */
	public function getItemsDetailed(): array;
}


```
###  Path: `/src/classes/DBHelper/DBHelperFilterSettingsInterface.php`

```php
namespace DBHelper;

use Application\FilterSettings\FilterSettingsInterface as FilterSettingsInterface;

interface DBHelperFilterSettingsInterface extends FilterSettingsInterface
{
}


```
###  Path: `/src/classes/DBHelper/DataTable.php`

```php
namespace ;

use AppUtils\ConvertHelper as ConvertHelper;
use AppUtils\Microtime as Microtime;
use Application\Application as Application;
use Application\EventHandler\Eventables\EventableInterface as EventableInterface;
use Application\EventHandler\Eventables\EventableListener as EventableListener;
use Application\EventHandler\Eventables\EventableTrait as EventableTrait;

/**
 * Data table handler for records: Handles fetching
 * data keys for a record from a DB table that contains
 * name => value pair data records.
 *
 * The data table must have at minimum three columns:
 *
 * 1. The record ID
 * 2. The data key name (default `name`*)
 * 3. The data key value (default `value`*)
 *
 * * The defaults can be changed.
 *
 * **Name column size limitation**
 *
 * Make sure you set the maximum key name length to match
 * the size of the column in the database to avoid problems:
 * The class will automatically replace key names that are
 * longer with an MD5 hash (internally only).
 *
 *   > Note: Because of the MD5 conversion, the minimum size
 *   > for the name column is 32 characters.
 *
 * @package DBHelper
 * @subpackage DataTables
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @author Emre Celebi <emre.celebi@ionos.com>
 */
class DBHelper_DataTable implements Application_Interfaces_Loggable, EventableInterface
{
	use Application_Traits_Loggable;
	use EventableTrait;

	public const EVENT_KEYS_SAVED = 'KeysSaved';
	public const EVENT_KEYS_DELETED = 'KeysDeleted';
	public const ERROR_INVALID_MAX_KEY_NAME_LENGTH = 97301;

	/**
	 * The max key length may not be smaller than this.
	 * @see DBHelper_DataTable::setMaxKeyNameLength()
	 */
	public const MIN_MAX_KEY_NAME_LENGTH = 32;

	/**
	 * Enables or disables auto saving. When enabled, data keys
	 * will be saved to the database each time they are modified.
	 * Otherwise, the `save()` method must be called manually.
	 *
	 * @param bool $enabled
	 * @return $this
	 */
	public function setAutoSave(bool $enabled): DBHelper_DataTable
	{
		/* ... */
	}


	public function getLogIdentifier(): string
	{
		/* ... */
	}


	public function getKey(string $name): string
	{
		/* ... */
	}


	public function resetCache(): DBHelper_DataTable
	{
		/* ... */
	}


	public function isKeyExists(string $name): bool
	{
		/* ... */
	}


	public function getIntKey(string $name): int
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @return Application_User|null
	 * @throws Application_Exception
	 */
	public function getUserKey(string $name): ?Application_User
	{
		/* ... */
	}


	public function getDateTimeKey(string $name): ?Microtime
	{
		/* ... */
	}


	public function getBoolKey(string $name): bool
	{
		/* ... */
	}


	public function setIntKey(string $name, int $value): bool
	{
		/* ... */
	}


	public function setUserKey(string $name, Application_User $user): bool
	{
		/* ... */
	}


	public function setBoolKey(string $name, bool $value): bool
	{
		/* ... */
	}


	public function setDateTimeKey(string $name, Microtime $value): bool
	{
		/* ... */
	}


	public function setKey(string $name, string $value): bool
	{
		/* ... */
	}


	public function deleteKey(string $name): bool
	{
		/* ... */
	}


	public function hasModifiedKeys(): bool
	{
		/* ... */
	}


	public function hasDeletedKeys(): bool
	{
		/* ... */
	}


	public function save(): bool
	{
		/* ... */
	}


	public function getStorageKeyName(string $name): string
	{
		/* ... */
	}


	/**
	 * Adds a listener for the `KeysSaved` event.
	 *
	 * Listener arguments:
	 *
	 * 1. `DBHelper_DataTable` The data table instance
	 * 2. `string[]` The names of the keys that were saved
	 *
	 * @param callable $callback
	 * @return EventableListener
	 */
	public function addKeysSavedListener(callable $callback): EventableListener
	{
		/* ... */
	}


	/**
	 * Adds a listener for the `KeysDeleted` event.
	 *
	 * Listener arguments:
	 *
	 * 1. `DBHelper_DataTable` The data table instance
	 * 2. `string[]` The names of the keys that were deleted
	 *
	 * @param callable $callback
	 * @return EventableListener
	 */
	public function addKeysDeletedListener(callable $callback): EventableListener
	{
		/* ... */
	}


	public function isAutoSaveEnabled(): bool
	{
		/* ... */
	}


	/**
	 * Sets the name of the database column used to store
	 * the record names.
	 *
	 * @param string $name
	 * @return $this
	 */
	public function setNameColumnName(string $name): DBHelper_DataTable
	{
		/* ... */
	}


	/**
	 * Sets the name of the database column used to store
	 * the record values.
	 *
	 * @param string $name
	 * @return $this
	 */
	public function setValueColumnName(string $name): DBHelper_DataTable
	{
		/* ... */
	}


	/**
	 * @param int $length
	 * @return $this
	 *
	 * @throws DBHelper_Exception
	 * @see DBHelper_DataTable::ERROR_INVALID_MAX_KEY_NAME_LENGTH
	 */
	public function setMaxKeyNameLength(int $length): DBHelper_DataTable
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/DataTable/Events/KeysDeleted.php`

```php
namespace ;

use AppUtils\ClassHelper as ClassHelper;
use Application\EventHandler\Eventables\BaseEventableEvent as BaseEventableEvent;

class DBHelper_DataTable_Events_KeysDeleted extends BaseEventableEvent
{
	public const EVENT_NAME = 'KeysDeleted';

	public function getName(): string
	{
		/* ... */
	}


	public function getKeyNames(): array
	{
		/* ... */
	}


	public function getDataTable(): DBHelper_DataTable
	{
		/* ... */
	}


	public function getSubject(): object
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/DataTable/Events/KeysDeleted.php`

```php
namespace ;

use AppUtils\ClassHelper as ClassHelper;
use Application\EventHandler\Eventables\BaseEventableEvent as BaseEventableEvent;

class DBHelper_DataTable_Events_KeysDeleted extends BaseEventableEvent
{
	public const EVENT_NAME = 'KeysDeleted';

	public function getName(): string
	{
		/* ... */
	}


	public function getKeyNames(): array
	{
		/* ... */
	}


	public function getDataTable(): DBHelper_DataTable
	{
		/* ... */
	}


	public function getSubject(): object
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/DataTable/Events/KeysSaved.php`

```php
namespace ;

use AppUtils\ClassHelper as ClassHelper;
use Application\EventHandler\Eventables\BaseEventableEvent as BaseEventableEvent;

class DBHelper_DataTable_Events_KeysSaved extends BaseEventableEvent
{
	public const EVENT_NAME = 'KeysSaved';

	public function getName(): string
	{
		/* ... */
	}


	public function getKeyNames(): array
	{
		/* ... */
	}


	public function getDataTable(): DBHelper_DataTable
	{
		/* ... */
	}


	public function getSubject(): object
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/DataTable/Events/KeysSaved.php`

```php
namespace ;

use AppUtils\ClassHelper as ClassHelper;
use Application\EventHandler\Eventables\BaseEventableEvent as BaseEventableEvent;

class DBHelper_DataTable_Events_KeysSaved extends BaseEventableEvent
{
	public const EVENT_NAME = 'KeysSaved';

	public function getName(): string
	{
		/* ... */
	}


	public function getKeyNames(): array
	{
		/* ... */
	}


	public function getDataTable(): DBHelper_DataTable
	{
		/* ... */
	}


	public function getSubject(): object
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/Event.php`

```php
namespace ;

/**
 * DBHelper-specific event class.
 *
 * @package Helpers
 * @subpackage DBHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DBHelper_Event
{
	public function getType()
	{
		/* ... */
	}


	/**
	 * Retrieves all arguments of the event as an array.
	 * @return mixed[]
	 */
	public function getArguments()
	{
		/* ... */
	}


	/**
	 * Retrieves the argument at the specified index, or null
	 * if it does not exist. The index is Zero-Based.
	 *
	 * @param int $index
	 * @return NULL|mixed
	 */
	public function getArgument($index)
	{
		/* ... */
	}


	public function isWriteOperation()
	{
		/* ... */
	}


	public function getStatement($formatted = false)
	{
		/* ... */
	}


	public function getVariables()
	{
		/* ... */
	}


	/**
	 * Checks whether the event should be cancelled.
	 * @return boolean
	 */
	public function isCancelled()
	{
		/* ... */
	}


	public function getCancelReason()
	{
		/* ... */
	}


	/**
	 * Specifies that the event should be cancelled. This is only
	 * possible if the event is callable.
	 *
	 * @param string $reason The reason for which the event was cancelled
	 * @return DBHelper_Event
	 */
	public function cancel($reason)
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/Exception.php`

```php
namespace ;

use AppUtils\BaseException as BaseException;
use Application\Application as Application;

/**
 * DBHelper-specific exception.
 *
 * @package Helpers
 * @subpackage DBHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DBHelper_Exception extends BaseException
{
	public const ERROR_KEY_VALIDATION_FAILED = 186901;
}


```
###  Path: `/src/classes/DBHelper/Exception/BaseErrorRenderer.php`

```php
namespace DBHelper\Exception;

use AppUtils\ConvertHelper\JSONConverter as JSONConverter;
use AppUtils\Interfaces\RenderableInterface as RenderableInterface;
use AppUtils\StringBuilder as StringBuilder;
use AppUtils\Traits\RenderableTrait as RenderableTrait;
use DBHelper as DBHelper;
use PDOException as PDOException;

abstract class BaseErrorRenderer implements RenderableInterface
{
	use RenderableTrait;

	abstract public function getEmptyMessageText(): string;


	public function render(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/Exception/BaseErrorRenderer.php`

```php
namespace DBHelper\Exception;

use AppUtils\ConvertHelper\JSONConverter as JSONConverter;
use AppUtils\Interfaces\RenderableInterface as RenderableInterface;
use AppUtils\StringBuilder as StringBuilder;
use AppUtils\Traits\RenderableTrait as RenderableTrait;
use DBHelper as DBHelper;
use PDOException as PDOException;

abstract class BaseErrorRenderer implements RenderableInterface
{
	use RenderableTrait;

	abstract public function getEmptyMessageText(): string;


	public function render(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/Exception/CLIErrorRenderer.php`

```php
namespace DBHelper\Exception;

use DBHelper as DBHelper;

class CLIErrorRenderer extends BaseErrorRenderer
{
	public function getEmptyMessageText(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/Exception/CLIErrorRenderer.php`

```php
namespace DBHelper\Exception;

use DBHelper as DBHelper;

class CLIErrorRenderer extends BaseErrorRenderer
{
	public function getEmptyMessageText(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/Exception/HTMLErrorRenderer.php`

```php
namespace DBHelper\Exception;

use DBHelper as DBHelper;

class HTMLErrorRenderer extends BaseErrorRenderer
{
	public function getEmptyMessageText(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/Exception/HTMLErrorRenderer.php`

```php
namespace DBHelper\Exception;

use DBHelper as DBHelper;

class HTMLErrorRenderer extends BaseErrorRenderer
{
	public function getEmptyMessageText(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/FetchBase.php`

```php
namespace ;

/**
 * Abstract base class for fetcher classes.
 *
 * @package Helpers
 * @subpackage DBHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class DBHelper_FetchBase
{
	/**
	 * @param string $column
	 * @param string|int|float|null $value
	 * @return $this
	 */
	public function whereValue(string $column, $value): self
	{
		/* ... */
	}


	/**
	 * Adds a where statement to ensure the column does not
	 * match the specified value.
	 *
	 * @param string $column
	 * @param string|null|int|float $value
	 * @return $this
	 */
	public function whereValueNot(string $column, $value): self
	{
		/* ... */
	}


	/**
	 * Adds several column values to limit the result to.
	 *
	 * @param array<string,mixed> $values
	 * @return $this
	 */
	public function whereValues(array $values): self
	{
		/* ... */
	}


	/**
	 * Adds a where column `is null` statement.
	 *
	 * @param string $column
	 * @return $this
	 */
	public function whereNull(string $column): self
	{
		/* ... */
	}


	/**
	 * Adds a where column `is not null` statement.
	 *
	 * @param string $column
	 * @return $this
	 */
	public function whereNotNull(string $column): self
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @param array $values
	 * @return $this
	 */
	public function whereValueIN(string $name, array $values): self
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @param array $values
	 * @return $this
	 */
	public function whereValueNOT_IN(string $name, array $values): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/FetchKey.php`

```php
namespace ;

/**
 * Specialized class used to fetch a single column value from a table.
 *
 * @package Helpers
 * @subpackage DBHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DBHelper_FetchKey extends DBHelper_FetchBase
{
	public function exists(): bool
	{
		/* ... */
	}


	public function fetchInt(): int
	{
		/* ... */
	}


	public function fetchString(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/FetchMany.php`

```php
namespace ;

/**
 * Specialized class used to fetch multiple records from a table.
 *
 * @package Helpers
 * @subpackage DBHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DBHelper_FetchMany extends DBHelper_FetchOne
{
	/**
	 * @return array<int,array<int|string,string|int|float|NULL>>
	 */
	public function fetch(): array
	{
		/* ... */
	}


	/**
	 * Retrieves only the specified column from all results.
	 * Note: Values are converted to strings.
	 *
	 * @param string $column
	 * @return string[]
	 */
	public function fetchColumn(string $column): array
	{
		/* ... */
	}


	/**
	 * Retrieves only the specified column from all results, converted to Integer.
	 *
	 * @param string $column
	 * @return int[]
	 */
	public function fetchColumnInt(string $column): array
	{
		/* ... */
	}


	public function groupBy(string $column)
	{
		/* ... */
	}


	public function orderBy(string $column, string $direction = 'ASC'): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/FetchOne.php`

```php
namespace ;

/**
 * Specialized class used to fetch a single column value from a table.
 *
 * @package Helpers
 * @subpackage DBHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DBHelper_FetchOne extends DBHelper_FetchBase
{
	/**
	 * Selects a single column to fetch from the result.
	 *
	 * @param string $column
	 * @return $this
	 */
	public function selectColumn(string $column)
	{
		/* ... */
	}


	/**
	 * Selects several columns to fetch in the result.
	 *
	 * @param string|array ...$args Either an array with column names (first parameter), or column names as method parameters.
	 * @return $this
	 */
	public function selectColumns(...$args)
	{
		/* ... */
	}


	/**
	 * @return array<int|string, mixed>
	 */
	public function fetch(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/Interfaces/DBHelperRecordInterface.php`

```php
namespace DBHelper\Interfaces;

use AppUtils\ConvertHelper_Exception as ConvertHelper_Exception;
use AppUtils\Microtime as Microtime;
use Application\Collection\IntegerCollectionItemInterface as IntegerCollectionItemInterface;
use Application\Disposables\Attributes\DisposedAware as DisposedAware;
use Application\Disposables\DisposableDisposedException as DisposableDisposedException;
use Application\Disposables\DisposableInterface as DisposableInterface;
use Application\EventHandler\Eventables\EventableListener as EventableListener;
use Application_Users_User as Application_Users_User;
use DBHelper\BaseCollection\DBHelperCollectionInterface as DBHelperCollectionInterface;
use DBHelper\BaseRecord\BaseRecordException as BaseRecordException;
use DBHelper_BaseCollection_OperationContext_Create as DBHelper_BaseCollection_OperationContext_Create;
use DBHelper_BaseCollection_OperationContext_Delete as DBHelper_BaseCollection_OperationContext_Delete;
use DBHelper_Exception as DBHelper_Exception;
use DateTime as DateTime;

interface DBHelperRecordInterface extends IntegerCollectionItemInterface, DisposableInterface
{
	public const STUB_ID = -1;

	/**
	 * Whether this is a stub record that is used only to
	 * access information on this record type.
	 *
	 * @return boolean
	 */
	public function isStub(): bool;


	/**
	 * Retrieves the collection used to access records like this.
	 * @return DBHelperCollectionInterface
	 */
	public function getCollection(): DBHelperCollectionInterface;


	/**
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getRecordKey(string $name, mixed $default = null): mixed;


	/**
	 * Retrieves a data key as an integer. Converts the value to int,
	 * so beware using this on non-integer keys.
	 *
	 * @param string $name
	 * @param int $default
	 * @return int
	 * @throws DisposableDisposedException
	 */
	public function getRecordIntKey(string $name, int $default = 0): int;


	/**
	 * Retrieves a data key as a DateTime object.
	 * @param string $name
	 * @param DateTime|null $default
	 * @return DateTime|null
	 */
	public function getRecordDateKey(string $name, ?DateTime $default = null): ?DateTime;


	public function getRecordMicrotimeKey(string $name): ?Microtime;


	/**
	 * Retrieves a data key as a DateTime object, throwing an exception if the key has no value.
	 * @param string $name
	 * @return Microtime
	 * @throws BaseRecordException
	 */
	public function requireRecordMicrotimeKey(string $name): Microtime;


	public function getRecordUserKey(string $name): ?Application_Users_User;


	public function requireRecordUserKey(string $name): Application_Users_User;


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
	public function refreshData(): void;


	public function getRecordTable(): string;


	public function getRecordPrimaryName(): string;


	public function getRecordTypeName(): string;


	/**
	 * Retrieves a data key as a float. Converts the value to float,
	 * so beware using this on non-float keys.
	 *
	 * @param string $name
	 * @param float $default
	 * @return float
	 * @throws DisposableDisposedException
	 */
	public function getRecordFloatKey(string $name, float $default = 0.0): float;


	/**
	 * Retrieves a data key, ensuring that it is a string.
	 *
	 * @param string $name
	 * @param string $default
	 * @return string
	 * @throws DisposableDisposedException
	 */
	public function getRecordStringKey(string $name, string $default = ''): string;


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
	public function getRecordBooleanKey(string $name, bool $default = false): bool;


	/**
	 * Checks if the specified record key exists.
	 * @param string $name
	 * @return bool
	 * @throws DisposableDisposedException
	 */
	public function recordKeyExists(string $name): bool;


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
	public function setRecordBooleanKey(string $name, bool $boolean, bool $yesno = true): bool;


	/**
	 * @param string $name
	 * @param DateTime $date
	 * @return bool
	 * @throws DisposableDisposedException
	 * @throws ConvertHelper_Exception
	 */
	public function setRecordDateKey(string $name, DateTime $date): bool;


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
	public function setRecordKey(string $name, mixed $value): bool;


	/**
	 * Throws an exception if the record does not have the specified key.
	 * @param string $name
	 * @return bool
	 * @throws DisposableDisposedException
	 * @throws BaseRecordException
	 */
	public function requireRecordKeyExists(string $name): bool;


	/**
	 * Whether the record has been modified since the last save, or
	 * just the specified key.
	 *
	 * @param string|NULL $key A single data key to check, or any key if NULL.
	 * @return boolean
	 */
	public function isModified(?string $key = null): bool;


	/**
	 * Checks whether any structural data keys have been modified.
	 *
	 * > NOTE: This method only works if the record has registered
	 * > structural keys through the method {@see \DBHelper_BaseRecord::registerRecordKey()}.
	 *
	 * @return bool
	 */
	public function hasStructuralChanges(): bool;


	/**
	 * Retrieves the names of all keys that have been modified since the last save.
	 * @return string[]
	 */
	public function getModifiedKeys(): array;


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
	public function save(bool $silent = false): bool;


	/**
	 * Like {@see self::save()}, but
	 * returns $this instead of the boolean status.
	 *
	 * @param bool $silent
	 * @return $this
	 * @throws DisposableDisposedException
	 * @throws DBHelper_Exception
	 */
	public function saveChained(bool $silent = false): self;


	/**
	 * Retrieves the record's parent record: this is only
	 * relevant if the record's collection has a parent
	 * collection. It will return NULL otherwise.
	 *
	 * @return DBHelperRecordInterface|NULL
	 */
	public function getParentRecord(): ?DBHelperRecordInterface;


	/**
	 * @return array<string,mixed>
	 * @throws DisposableDisposedException
	 */
	public function getFormValues(): array;


	/**
	 * Adds a listener for the event {@see KeyModifiedEvent}.
	 *
	 * NOTE: The callback gets the event instance as sole argument.
	 *
	 * @param callable $callback
	 * @return EventableListener
	 */
	public function onKeyModified(callable $callback): EventableListener;


	/**
	 * This is called once when the record has been created,
	 * and allows the record to run any additional initializations
	 * it may need.
	 *
	 * @param DBHelper_BaseCollection_OperationContext_Create $context
	 */
	public function onCreated(DBHelper_BaseCollection_OperationContext_Create $context): void;


	/**
	 * Called when the record has been deleted by the
	 * collection.
	 *
	 * @param DBHelper_BaseCollection_OperationContext_Delete $context
	 */
	public function onDeleted(DBHelper_BaseCollection_OperationContext_Delete $context): void;


	public function onBeforeDelete(DBHelper_BaseCollection_OperationContext_Delete $context): void;
}


```
###  Path: `/src/classes/DBHelper/Interfaces/DBHelperRecordInterface.php`

```php
namespace DBHelper\Interfaces;

use AppUtils\ConvertHelper_Exception as ConvertHelper_Exception;
use AppUtils\Microtime as Microtime;
use Application\Collection\IntegerCollectionItemInterface as IntegerCollectionItemInterface;
use Application\Disposables\Attributes\DisposedAware as DisposedAware;
use Application\Disposables\DisposableDisposedException as DisposableDisposedException;
use Application\Disposables\DisposableInterface as DisposableInterface;
use Application\EventHandler\Eventables\EventableListener as EventableListener;
use Application_Users_User as Application_Users_User;
use DBHelper\BaseCollection\DBHelperCollectionInterface as DBHelperCollectionInterface;
use DBHelper\BaseRecord\BaseRecordException as BaseRecordException;
use DBHelper_BaseCollection_OperationContext_Create as DBHelper_BaseCollection_OperationContext_Create;
use DBHelper_BaseCollection_OperationContext_Delete as DBHelper_BaseCollection_OperationContext_Delete;
use DBHelper_Exception as DBHelper_Exception;
use DateTime as DateTime;

interface DBHelperRecordInterface extends IntegerCollectionItemInterface, DisposableInterface
{
	public const STUB_ID = -1;

	/**
	 * Whether this is a stub record that is used only to
	 * access information on this record type.
	 *
	 * @return boolean
	 */
	public function isStub(): bool;


	/**
	 * Retrieves the collection used to access records like this.
	 * @return DBHelperCollectionInterface
	 */
	public function getCollection(): DBHelperCollectionInterface;


	/**
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getRecordKey(string $name, mixed $default = null): mixed;


	/**
	 * Retrieves a data key as an integer. Converts the value to int,
	 * so beware using this on non-integer keys.
	 *
	 * @param string $name
	 * @param int $default
	 * @return int
	 * @throws DisposableDisposedException
	 */
	public function getRecordIntKey(string $name, int $default = 0): int;


	/**
	 * Retrieves a data key as a DateTime object.
	 * @param string $name
	 * @param DateTime|null $default
	 * @return DateTime|null
	 */
	public function getRecordDateKey(string $name, ?DateTime $default = null): ?DateTime;


	public function getRecordMicrotimeKey(string $name): ?Microtime;


	/**
	 * Retrieves a data key as a DateTime object, throwing an exception if the key has no value.
	 * @param string $name
	 * @return Microtime
	 * @throws BaseRecordException
	 */
	public function requireRecordMicrotimeKey(string $name): Microtime;


	public function getRecordUserKey(string $name): ?Application_Users_User;


	public function requireRecordUserKey(string $name): Application_Users_User;


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
	public function refreshData(): void;


	public function getRecordTable(): string;


	public function getRecordPrimaryName(): string;


	public function getRecordTypeName(): string;


	/**
	 * Retrieves a data key as a float. Converts the value to float,
	 * so beware using this on non-float keys.
	 *
	 * @param string $name
	 * @param float $default
	 * @return float
	 * @throws DisposableDisposedException
	 */
	public function getRecordFloatKey(string $name, float $default = 0.0): float;


	/**
	 * Retrieves a data key, ensuring that it is a string.
	 *
	 * @param string $name
	 * @param string $default
	 * @return string
	 * @throws DisposableDisposedException
	 */
	public function getRecordStringKey(string $name, string $default = ''): string;


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
	public function getRecordBooleanKey(string $name, bool $default = false): bool;


	/**
	 * Checks if the specified record key exists.
	 * @param string $name
	 * @return bool
	 * @throws DisposableDisposedException
	 */
	public function recordKeyExists(string $name): bool;


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
	public function setRecordBooleanKey(string $name, bool $boolean, bool $yesno = true): bool;


	/**
	 * @param string $name
	 * @param DateTime $date
	 * @return bool
	 * @throws DisposableDisposedException
	 * @throws ConvertHelper_Exception
	 */
	public function setRecordDateKey(string $name, DateTime $date): bool;


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
	public function setRecordKey(string $name, mixed $value): bool;


	/**
	 * Throws an exception if the record does not have the specified key.
	 * @param string $name
	 * @return bool
	 * @throws DisposableDisposedException
	 * @throws BaseRecordException
	 */
	public function requireRecordKeyExists(string $name): bool;


	/**
	 * Whether the record has been modified since the last save, or
	 * just the specified key.
	 *
	 * @param string|NULL $key A single data key to check, or any key if NULL.
	 * @return boolean
	 */
	public function isModified(?string $key = null): bool;


	/**
	 * Checks whether any structural data keys have been modified.
	 *
	 * > NOTE: This method only works if the record has registered
	 * > structural keys through the method {@see \DBHelper_BaseRecord::registerRecordKey()}.
	 *
	 * @return bool
	 */
	public function hasStructuralChanges(): bool;


	/**
	 * Retrieves the names of all keys that have been modified since the last save.
	 * @return string[]
	 */
	public function getModifiedKeys(): array;


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
	public function save(bool $silent = false): bool;


	/**
	 * Like {@see self::save()}, but
	 * returns $this instead of the boolean status.
	 *
	 * @param bool $silent
	 * @return $this
	 * @throws DisposableDisposedException
	 * @throws DBHelper_Exception
	 */
	public function saveChained(bool $silent = false): self;


	/**
	 * Retrieves the record's parent record: this is only
	 * relevant if the record's collection has a parent
	 * collection. It will return NULL otherwise.
	 *
	 * @return DBHelperRecordInterface|NULL
	 */
	public function getParentRecord(): ?DBHelperRecordInterface;


	/**
	 * @return array<string,mixed>
	 * @throws DisposableDisposedException
	 */
	public function getFormValues(): array;


	/**
	 * Adds a listener for the event {@see KeyModifiedEvent}.
	 *
	 * NOTE: The callback gets the event instance as sole argument.
	 *
	 * @param callable $callback
	 * @return EventableListener
	 */
	public function onKeyModified(callable $callback): EventableListener;


	/**
	 * This is called once when the record has been created,
	 * and allows the record to run any additional initializations
	 * it may need.
	 *
	 * @param DBHelper_BaseCollection_OperationContext_Create $context
	 */
	public function onCreated(DBHelper_BaseCollection_OperationContext_Create $context): void;


	/**
	 * Called when the record has been deleted by the
	 * collection.
	 *
	 * @param DBHelper_BaseCollection_OperationContext_Delete $context
	 */
	public function onDeleted(DBHelper_BaseCollection_OperationContext_Delete $context): void;


	public function onBeforeDelete(DBHelper_BaseCollection_OperationContext_Delete $context): void;
}


```
###  Path: `/src/classes/DBHelper/OperationTypes.php`

```php
namespace ;

/**
 * Helper class that is used to keep track of database operations,
 * and retrieve information about operation types.
 *
 * @package Helpers
 * @subpackage DBHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DBHelper_OperationTypes
{
	const TYPE_INSERT = 1;
	const TYPE_UPDATE = 2;
	const TYPE_DELETE = 3;
	const TYPE_DROP = 4;
	const TYPE_SET = 5;
	const TYPE_SHOW = 6;
	const TYPE_SELECT = 7;
	const TYPE_TRUNCATE = 8;
	const TYPE_TRANSACTION = 9;
	const TYPE_ALTER = 10;

	/**
	 * Checks if the specified type ID is a write operation.
	 * @param int $typeID
	 * @return boolean
	 */
	public static function isWriteOperation($typeID)
	{
		/* ... */
	}


	public static function init()
	{
		/* ... */
	}


	/**
	 * Retrieves all type IDs for operations that write to the database.
	 * @return int[]
	 */
	public static function getWriteTypes()
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/StatementBuilder.php`

```php
namespace ;

/**
 * SQL statement builder utility, used to create statements
 * with human-readable placeholders for table names, field
 * names and the like.
 *
 * Usage:
 *
 * <pre>
 * $sql = (string)statementBuilder("SELECT * FROM {table_name} WHERE {field}=1")
 *     ->table('table_name', 'actual_table_name')
 *     ->field('field', 'field_name');
 * </pre>
 *
 * @package DBHelper
 * @subpackage StatementBuilder
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DBHelper_StatementBuilder extends DBHelper_StatementBuilder_ValuesContainer implements UI_Renderable_Interface
{
	use UI_Traits_RenderableGeneric;

	public const ERROR_PLACEHOLDER_NOT_FOUND = 94101;
	public const ERROR_UNFILLED_PLACEHOLDER_DETECTED = 94102;

	public function getTemplate(): string
	{
		/* ... */
	}


	/**
	 * @return string
	 *
	 * @throws DBHelper_Exception
	 * @see DBHelper_StatementBuilder::ERROR_UNFILLED_PLACEHOLDER_DETECTED
	 */
	public function render(): string
	{
		/* ... */
	}


	/**
	 * @param string $subject
	 * @return string[]
	 */
	public static function detectPlaceholderNames(string $subject): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/StatementBuilder/ValueDefinition.php`

```php
namespace DBHelper\StatementBuilder;

use DBHelper_StatementBuilder_ValuesContainer as DBHelper_StatementBuilder_ValuesContainer;

/**
 * Stores information on a single placeholder value
 * in a statement builder.
 *
 * @package DBHelper
 * @subpackage StatementBuilder
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ValueDefinition
{
	public function getName(): string
	{
		/* ... */
	}


	public function getValue(): string
	{
		/* ... */
	}


	public function isWrapTicks(): bool
	{
		/* ... */
	}


	public function isStringLiteral(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/StatementBuilder/ValueDefinition.php`

```php
namespace DBHelper\StatementBuilder;

use DBHelper_StatementBuilder_ValuesContainer as DBHelper_StatementBuilder_ValuesContainer;

/**
 * Stores information on a single placeholder value
 * in a statement builder.
 *
 * @package DBHelper
 * @subpackage StatementBuilder
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ValueDefinition
{
	public function getName(): string
	{
		/* ... */
	}


	public function getValue(): string
	{
		/* ... */
	}


	public function isWrapTicks(): bool
	{
		/* ... */
	}


	public function isStringLiteral(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/StatementBuilder/ValuesContainer.php`

```php
namespace ;

use DBHelper\StatementBuilder\ValueDefinition as ValueDefinition;

/**
 * Companion class to the statement builder, used to
 * store placeholder names for fields, tables and more.
 *
 * @package DBHelper
 * @subpackage StatementBuilder
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DBHelper_StatementBuilder_ValuesContainer
{
	public const ERROR_UNKNOWN_PLACEHOLDER_NAME = 95501;
	public const VALUE_TYPE_SYMBOL = 1;
	public const VALUE_TYPE_INTEGER = 2;
	public const VALUE_TYPE_STRING_LITERAL = 3;
	public const VALUE_TYPE_RAW = 4;

	/**
	 * @param string $tableName
	 * @param string $value
	 * @return $this
	 */
	public function table(string $tableName, string $value): self
	{
		/* ... */
	}


	/**
	 * @param string $alias
	 * @param string $value
	 * @return $this
	 */
	public function alias(string $alias, string $value): self
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @param int $value
	 * @return $this
	 */
	public function int(string $name, int $value): self
	{
		/* ... */
	}


	/**
	 * Adds a placeholder for a raw value, which will be
	 * inserted as-is, without any transformations.
	 *
	 * @param string $name
	 * @param string $value
	 * @return $this
	 */
	public function val(string $name, string $value): self
	{
		/* ... */
	}


	public function text(string $name, string $value): self
	{
		/* ... */
	}


	/**
	 * @param string $fieldName
	 * @param string $value
	 * @return $this
	 */
	public function field(string $fieldName, string $value): self
	{
		/* ... */
	}


	public function hasValue(string $placeholderName): bool
	{
		/* ... */
	}


	public function getValueDef(string $placeholderName): ValueDefinition
	{
		/* ... */
	}


	public function getValue(string $placeholderName): string
	{
		/* ... */
	}


	/**
	 * @param DBHelper_StatementBuilder_ValuesContainer $container
	 * @return $this
	 */
	public function setContainer(DBHelper_StatementBuilder_ValuesContainer $container): self
	{
		/* ... */
	}


	/**
	 * Creates a new statement builder that inherits
	 * this container's placeholder values.
	 *
	 * @param string $template
	 * @return DBHelper_StatementBuilder
	 */
	public function statement(string $template): DBHelper_StatementBuilder
	{
		/* ... */
	}


	public function getField(string $name): ValueDefinition
	{
		/* ... */
	}


	public function getTable(string $name): ValueDefinition
	{
		/* ... */
	}


	public function getAlias(string $name): ValueDefinition
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/StatementBuilder/ValuesContainer.php`

```php
namespace ;

use DBHelper\StatementBuilder\ValueDefinition as ValueDefinition;

/**
 * Companion class to the statement builder, used to
 * store placeholder names for fields, tables and more.
 *
 * @package DBHelper
 * @subpackage StatementBuilder
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DBHelper_StatementBuilder_ValuesContainer
{
	public const ERROR_UNKNOWN_PLACEHOLDER_NAME = 95501;
	public const VALUE_TYPE_SYMBOL = 1;
	public const VALUE_TYPE_INTEGER = 2;
	public const VALUE_TYPE_STRING_LITERAL = 3;
	public const VALUE_TYPE_RAW = 4;

	/**
	 * @param string $tableName
	 * @param string $value
	 * @return $this
	 */
	public function table(string $tableName, string $value): self
	{
		/* ... */
	}


	/**
	 * @param string $alias
	 * @param string $value
	 * @return $this
	 */
	public function alias(string $alias, string $value): self
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @param int $value
	 * @return $this
	 */
	public function int(string $name, int $value): self
	{
		/* ... */
	}


	/**
	 * Adds a placeholder for a raw value, which will be
	 * inserted as-is, without any transformations.
	 *
	 * @param string $name
	 * @param string $value
	 * @return $this
	 */
	public function val(string $name, string $value): self
	{
		/* ... */
	}


	public function text(string $name, string $value): self
	{
		/* ... */
	}


	/**
	 * @param string $fieldName
	 * @param string $value
	 * @return $this
	 */
	public function field(string $fieldName, string $value): self
	{
		/* ... */
	}


	public function hasValue(string $placeholderName): bool
	{
		/* ... */
	}


	public function getValueDef(string $placeholderName): ValueDefinition
	{
		/* ... */
	}


	public function getValue(string $placeholderName): string
	{
		/* ... */
	}


	/**
	 * @param DBHelper_StatementBuilder_ValuesContainer $container
	 * @return $this
	 */
	public function setContainer(DBHelper_StatementBuilder_ValuesContainer $container): self
	{
		/* ... */
	}


	/**
	 * Creates a new statement builder that inherits
	 * this container's placeholder values.
	 *
	 * @param string $template
	 * @return DBHelper_StatementBuilder
	 */
	public function statement(string $template): DBHelper_StatementBuilder
	{
		/* ... */
	}


	public function getField(string $name): ValueDefinition
	{
		/* ... */
	}


	public function getTable(string $name): ValueDefinition
	{
		/* ... */
	}


	public function getAlias(string $name): ValueDefinition
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/TrackedQuery.php`

```php
namespace DBHelper;

use AppUtils\ConvertHelper as ConvertHelper;
use AppUtils\FileHelper as FileHelper;
use AppUtils\Highlighter as Highlighter;
use AppUtils\ThrowableInfo\ThrowableCall as ThrowableCall;
use DBHelper as DBHelper;
use DBHelper_Exception as DBHelper_Exception;
use DBHelper_OperationTypes as DBHelper_OperationTypes;
use DBHelper_StatementBuilder as DBHelper_StatementBuilder;

/**
 * Utility class that holds information on a single query
 * executed during this request. See {@see DBHelper::getQueries()}.
 *
 * @package DBHelper
 */
class TrackedQuery
{
	/**
	 * @return ThrowableCall[]
	 */
	public function getFullTrace(): array
	{
		/* ... */
	}


	public function getOperationTypeID(): int
	{
		/* ... */
	}


	public function isSelect(): bool
	{
		/* ... */
	}


	public function isDelete(): bool
	{
		/* ... */
	}


	public function isInsert(): bool
	{
		/* ... */
	}


	public function isUpdate(): bool
	{
		/* ... */
	}


	public function isWriteOperation(): bool
	{
		/* ... */
	}


	public function getDuration(): float
	{
		/* ... */
	}


	/**
	 * @return array<string,string>
	 */
	public function getVariables(): array
	{
		/* ... */
	}


	/**
	 * @return DBHelper_StatementBuilder|string
	 */
	public function getStatement()
	{
		/* ... */
	}


	public function getSQLFormatted(): string
	{
		/* ... */
	}


	public function getSQLHighlighted(): string
	{
		/* ... */
	}


	/**
	 * Gets the most likely function call from which the query originated.
	 * @return ThrowableCall|null
	 */
	public function getOriginator(): ?ThrowableCall
	{
		/* ... */
	}


	/**
	 * Gets all calls in the trace that are not part of the DBHelper package.
	 * @return ThrowableCall[]
	 */
	public function getFilteredTrace(): array
	{
		/* ... */
	}


	/**
	 * Gets a string representation of the call trace that led to this query.
	 * @return string
	 */
	public function trace2string(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/Traits/AfterRecordCreatedEventTrait.php`

```php
namespace DBHelper\Traits;

use AppUtils\ClassHelper as ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException as BaseClassHelperException;
use DBHelper\BaseCollection\DBHelperCollectionInterface as DBHelperCollectionInterface;
use DBHelper\BaseCollection\Event\AfterCreateRecordEvent as AfterCreateRecordEvent;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use DBHelper_BaseCollection_OperationContext_Create as DBHelper_BaseCollection_OperationContext_Create;

trait AfterRecordCreatedEventTrait
{
}


```
###  Path: `/src/classes/DBHelper/Traits/AfterRecordCreatedEventTrait.php`

```php
namespace DBHelper\Traits;

use AppUtils\ClassHelper as ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException as BaseClassHelperException;
use DBHelper\BaseCollection\DBHelperCollectionInterface as DBHelperCollectionInterface;
use DBHelper\BaseCollection\Event\AfterCreateRecordEvent as AfterCreateRecordEvent;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use DBHelper_BaseCollection_OperationContext_Create as DBHelper_BaseCollection_OperationContext_Create;

trait AfterRecordCreatedEventTrait
{
}


```
###  Path: `/src/classes/DBHelper/Traits/BeforeCreateEventTrait.php`

```php
namespace DBHelper\Traits;

use AppUtils\ClassHelper as ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException as BaseClassHelperException;
use DBHelper\BaseCollection\DBHelperCollectionException as DBHelperCollectionException;
use DBHelper\BaseCollection\DBHelperCollectionInterface as DBHelperCollectionInterface;
use DBHelper\BaseCollection\Event\BeforeCreateRecordEvent as BeforeCreateRecordEvent;

trait BeforeCreateEventTrait
{
}


```
###  Path: `/src/classes/DBHelper/Traits/BeforeCreateEventTrait.php`

```php
namespace DBHelper\Traits;

use AppUtils\ClassHelper as ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException as BaseClassHelperException;
use DBHelper\BaseCollection\DBHelperCollectionException as DBHelperCollectionException;
use DBHelper\BaseCollection\DBHelperCollectionInterface as DBHelperCollectionInterface;
use DBHelper\BaseCollection\Event\BeforeCreateRecordEvent as BeforeCreateRecordEvent;

trait BeforeCreateEventTrait
{
}


```
###  Path: `/src/classes/DBHelper/Traits/LooseDBRecordInterface.php`

```php
namespace DBHelper\Traits;

use DBHelper_Exception as DBHelper_Exception;
use DateTime as DateTime;

/**
 * Interface for the {@see LooseDBRecordTrait} trait.
 *
 * @package DBHelper
 * @subpackage LooseRecord
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see LooseDBRecordTrait
 */
interface LooseDBRecordInterface
{
	public const ERROR_CANNOT_LOAD_RECORD = 66701;
	public const ERROR_COULD_NOT_SAVE_DATA = 66702;
	public const ERROR_UNKNOWN_DATA_KEY = 66703;

	/**
	 * The name of the database table in which the records are stored.
	 * @return string
	 */
	public function getRecordTable(): string;


	/**
	 * Name of the table column in which the primary keys are stored.
	 * @return string
	 */
	public function getRecordPrimaryName(): string;


	/**
	 * The record's ID.
	 * @return int
	 */
	public function getID(): int;


	/**
	 * Saves the record to the database, if it has been modified.
	 * @return bool
	 * @throws DBHelper_Exception
	 *
	 * @see LooseDBRecordInterface::ERROR_COULD_NOT_SAVE_DATA
	 */
	public function save(): bool;


	/**
	 * Checks whether any changes are pending to be saved.
	 * @return bool
	 */
	public function isModified(): bool;


	/**
	 * Retrieves the specified column's value.
	 *
	 * > NOTE: Trying to retrieve the value of unknown
	 * > columns will not throw an error. It will simply
	 * > return an empty string.
	 *
	 * @param string $name
	 * @return string
	 */
	public function getDataKey(string $name): string;


	/**
	 * Retrieves a data key value, and converts it to int.
	 * @param string $name
	 * @return int
	 */
	public function getDataKeyInt(string $name): int;


	/**
	 * Retrieves a data key value, and converts it to boolean.
	 *
	 * Supported column values are `true`, `false`, `yes`, `no`.
	 *
	 * @param string $name
	 * @return bool
	 */
	public function getDataKeyBool(string $name): bool;


	/**
	 * Retrieves a data key value, and converts it to a datetime instance.
	 * @param string $name
	 * @return DateTime
	 */
	public function getDataKeyDate(string $name): DateTime;


	/**
	 * Sets a data key value.
	 *
	 * NOTE: Will throw an exception if the column does
	 * not exist in the record.
	 *
	 * @param string $name
	 * @param string $value
	 * @return bool
	 * @throws DBHelper_Exception
	 *
	 * @see LooseDBRecordInterface::ERROR_UNKNOWN_DATA_KEY
	 */
	public function setDataKey(string $name, string $value): bool;
}


```
###  Path: `/src/classes/DBHelper/Traits/LooseDBRecordInterface.php`

```php
namespace DBHelper\Traits;

use DBHelper_Exception as DBHelper_Exception;
use DateTime as DateTime;

/**
 * Interface for the {@see LooseDBRecordTrait} trait.
 *
 * @package DBHelper
 * @subpackage LooseRecord
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see LooseDBRecordTrait
 */
interface LooseDBRecordInterface
{
	public const ERROR_CANNOT_LOAD_RECORD = 66701;
	public const ERROR_COULD_NOT_SAVE_DATA = 66702;
	public const ERROR_UNKNOWN_DATA_KEY = 66703;

	/**
	 * The name of the database table in which the records are stored.
	 * @return string
	 */
	public function getRecordTable(): string;


	/**
	 * Name of the table column in which the primary keys are stored.
	 * @return string
	 */
	public function getRecordPrimaryName(): string;


	/**
	 * The record's ID.
	 * @return int
	 */
	public function getID(): int;


	/**
	 * Saves the record to the database, if it has been modified.
	 * @return bool
	 * @throws DBHelper_Exception
	 *
	 * @see LooseDBRecordInterface::ERROR_COULD_NOT_SAVE_DATA
	 */
	public function save(): bool;


	/**
	 * Checks whether any changes are pending to be saved.
	 * @return bool
	 */
	public function isModified(): bool;


	/**
	 * Retrieves the specified column's value.
	 *
	 * > NOTE: Trying to retrieve the value of unknown
	 * > columns will not throw an error. It will simply
	 * > return an empty string.
	 *
	 * @param string $name
	 * @return string
	 */
	public function getDataKey(string $name): string;


	/**
	 * Retrieves a data key value, and converts it to int.
	 * @param string $name
	 * @return int
	 */
	public function getDataKeyInt(string $name): int;


	/**
	 * Retrieves a data key value, and converts it to boolean.
	 *
	 * Supported column values are `true`, `false`, `yes`, `no`.
	 *
	 * @param string $name
	 * @return bool
	 */
	public function getDataKeyBool(string $name): bool;


	/**
	 * Retrieves a data key value, and converts it to a datetime instance.
	 * @param string $name
	 * @return DateTime
	 */
	public function getDataKeyDate(string $name): DateTime;


	/**
	 * Sets a data key value.
	 *
	 * NOTE: Will throw an exception if the column does
	 * not exist in the record.
	 *
	 * @param string $name
	 * @param string $value
	 * @return bool
	 * @throws DBHelper_Exception
	 *
	 * @see LooseDBRecordInterface::ERROR_UNKNOWN_DATA_KEY
	 */
	public function setDataKey(string $name, string $value): bool;
}


```
###  Path: `/src/classes/DBHelper/Traits/LooseDBRecordTrait.php`

```php
namespace ;

use AppUtils\ConvertHelper\JSONConverter as JSONConverter;
use DBHelper\Traits\LooseDBRecordInterface as LooseDBRecordInterface;

/**
 * Trait for working with database records, independently of a
 * full-fledged DB records collection.
 *
 * Use this as a drop-in to load data from the database by ID,
 * with everything required to access and update the data.
 *
 * ## Usage
 *
 * - Use the trait: {@see LooseDBRecordTrait}
 * - Add the interface: {@see LooseDBRecordInterface}
 *
 * @package DBHelper
 * @subpackage LooseRecord
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see LooseDBRecordInterface
 */
trait LooseDBRecordTrait
{
	abstract public function getRecordTable(): string;


	abstract public function getRecordPrimaryName(): string;


	public function getID(): int
	{
		/* ... */
	}


	/**
	 * Saves the current data set of the record.
	 *
	 * @return bool Whether there were any changes to save.
	 * @throws DBHelper_Exception If the record data could not be saved to the database.
	 *
	 * @see LooseDBRecordInterface::ERROR_COULD_NOT_SAVE_DATA
	 */
	public function save(): bool
	{
		/* ... */
	}


	public function isModified(): bool
	{
		/* ... */
	}


	public function getDataKey(string $name): string
	{
		/* ... */
	}


	public function getDataKeyInt(string $name): int
	{
		/* ... */
	}


	public function getDataKeyBool(string $name): bool
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @return DateTime
	 * @throws Exception If the date could not be parsed.
	 */
	public function getDataKeyDate(string $name): DateTime
	{
		/* ... */
	}


	/**
	 * Sets a data key value.
	 *
	 * NOTE: The value is not saved directly in the database.
	 * The `save()` method needs to be called separately.
	 *
	 * @param string $name
	 * @param string $value
	 * @return bool
	 * @throws DBHelper_Exception If the data key is not known.
	 *
	 * @see LooseDBRecordTrait::save()
	 * @see LooseDBRecordInterface::ERROR_UNKNOWN_DATA_KEY
	 */
	public function setDataKey(string $name, string $value): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/Traits/LooseDBRecordTrait.php`

```php
namespace ;

use AppUtils\ConvertHelper\JSONConverter as JSONConverter;
use DBHelper\Traits\LooseDBRecordInterface as LooseDBRecordInterface;

/**
 * Trait for working with database records, independently of a
 * full-fledged DB records collection.
 *
 * Use this as a drop-in to load data from the database by ID,
 * with everything required to access and update the data.
 *
 * ## Usage
 *
 * - Use the trait: {@see LooseDBRecordTrait}
 * - Add the interface: {@see LooseDBRecordInterface}
 *
 * @package DBHelper
 * @subpackage LooseRecord
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see LooseDBRecordInterface
 */
trait LooseDBRecordTrait
{
	abstract public function getRecordTable(): string;


	abstract public function getRecordPrimaryName(): string;


	public function getID(): int
	{
		/* ... */
	}


	/**
	 * Saves the current data set of the record.
	 *
	 * @return bool Whether there were any changes to save.
	 * @throws DBHelper_Exception If the record data could not be saved to the database.
	 *
	 * @see LooseDBRecordInterface::ERROR_COULD_NOT_SAVE_DATA
	 */
	public function save(): bool
	{
		/* ... */
	}


	public function isModified(): bool
	{
		/* ... */
	}


	public function getDataKey(string $name): string
	{
		/* ... */
	}


	public function getDataKeyInt(string $name): int
	{
		/* ... */
	}


	public function getDataKeyBool(string $name): bool
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @return DateTime
	 * @throws Exception If the date could not be parsed.
	 */
	public function getDataKeyDate(string $name): DateTime
	{
		/* ... */
	}


	/**
	 * Sets a data key value.
	 *
	 * NOTE: The value is not saved directly in the database.
	 * The `save()` method needs to be called separately.
	 *
	 * @param string $name
	 * @param string $value
	 * @return bool
	 * @throws DBHelper_Exception If the data key is not known.
	 *
	 * @see LooseDBRecordTrait::save()
	 * @see LooseDBRecordInterface::ERROR_UNKNOWN_DATA_KEY
	 */
	public function setDataKey(string $name, string $value): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/Traits/RecordDecoratorInterface.php`

```php
namespace DBHelper\Traits;

use DBHelper\BaseRecord\BaseRecordDecorator as BaseRecordDecorator;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;

/**
 * Interface for a class that acts as a decorator for a {@see DBHelperRecordInterface}.
 *
 * ## Usage via trait
 *
 * 1. Implement this interface in your decorator class.
 * 2. Use the trait {@see RecordDecoratorTrait} to automatically forward method calls to the decorated record.
 * 3. Use additional traits as illustrated by {@see BaseRecordDecorator}.
 * 4. Implement the remaining methods.
 *
 * ## Usage via base class
 *
 * 1. Extend the class {@see BaseRecordDecorator}.
 * 2. Implement the remaining interface methods.
 *
 * @package DBHelper
 * @subpackage Decorators
 */
interface RecordDecoratorInterface extends DBHelperRecordInterface
{
	public function getDecoratedRecord(): DBHelperRecordInterface;
}


```
###  Path: `/src/classes/DBHelper/Traits/RecordDecoratorInterface.php`

```php
namespace DBHelper\Traits;

use DBHelper\BaseRecord\BaseRecordDecorator as BaseRecordDecorator;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;

/**
 * Interface for a class that acts as a decorator for a {@see DBHelperRecordInterface}.
 *
 * ## Usage via trait
 *
 * 1. Implement this interface in your decorator class.
 * 2. Use the trait {@see RecordDecoratorTrait} to automatically forward method calls to the decorated record.
 * 3. Use additional traits as illustrated by {@see BaseRecordDecorator}.
 * 4. Implement the remaining methods.
 *
 * ## Usage via base class
 *
 * 1. Extend the class {@see BaseRecordDecorator}.
 * 2. Implement the remaining interface methods.
 *
 * @package DBHelper
 * @subpackage Decorators
 */
interface RecordDecoratorInterface extends DBHelperRecordInterface
{
	public function getDecoratedRecord(): DBHelperRecordInterface;
}


```
###  Path: `/src/classes/DBHelper/Traits/RecordDecoratorTrait.php`

```php
namespace DBHelper\Traits;

use AppUtils\Microtime as Microtime;
use Application\EventHandler\Eventables\EventableListener as EventableListener;
use Application_Users_User as Application_Users_User;
use DBHelper\BaseCollection\DBHelperCollectionInterface as DBHelperCollectionInterface;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use DBHelper_BaseCollection_OperationContext_Create as DBHelper_BaseCollection_OperationContext_Create;
use DBHelper_BaseCollection_OperationContext_Delete as DBHelper_BaseCollection_OperationContext_Delete;
use DateTime as DateTime;

/**
 * Trait used to implement the {@see RecordDecoratorInterface} by forwarding
 * method calls to the decorated record.
 *
 * > NOTE: Additional traits are typically used in conjunction with this trait,
 * > as demonstrated by {@see BaseRecordDecorator}.
 *
 * @package DBHelper
 * @subpackage Decorators
 *
 * @see RecordDecoratorInterface
 */
trait RecordDecoratorTrait
{
	public function getLabel(): string
	{
		/* ... */
	}


	public function isStub(): bool
	{
		/* ... */
	}


	public function getCollection(): DBHelperCollectionInterface
	{
		/* ... */
	}


	public function getRecordKey(string $name, mixed $default = null): mixed
	{
		/* ... */
	}


	public function getRecordIntKey(string $name, int $default = 0): int
	{
		/* ... */
	}


	public function getRecordDateKey(string $name, ?DateTime $default = null): ?DateTime
	{
		/* ... */
	}


	public function getRecordMicrotimeKey(string $name): ?Microtime
	{
		/* ... */
	}


	public function requireRecordMicrotimeKey(string $name): Microtime
	{
		/* ... */
	}


	public function getRecordData(): array
	{
		/* ... */
	}


	public function getInstanceID(): string
	{
		/* ... */
	}


	public function refreshData(): void
	{
		/* ... */
	}


	public function getRecordTable(): string
	{
		/* ... */
	}


	public function getRecordPrimaryName(): string
	{
		/* ... */
	}


	public function getRecordTypeName(): string
	{
		/* ... */
	}


	public function getRecordFloatKey(string $name, float $default = 0.0): float
	{
		/* ... */
	}


	public function getRecordStringKey(string $name, string $default = ''): string
	{
		/* ... */
	}


	public function getRecordBooleanKey(string $name, bool $default = false): bool
	{
		/* ... */
	}


	public function getRecordUserKey(string $name): ?Application_Users_User
	{
		/* ... */
	}


	public function requireRecordUserKey(string $name): Application_Users_User
	{
		/* ... */
	}


	public function recordKeyExists(string $name): bool
	{
		/* ... */
	}


	public function setRecordBooleanKey(string $name, bool $boolean, bool $yesno = true): bool
	{
		/* ... */
	}


	public function setRecordDateKey(string $name, DateTime $date): bool
	{
		/* ... */
	}


	public function setRecordKey(string $name, mixed $value): bool
	{
		/* ... */
	}


	public function requireRecordKeyExists(string $name): bool
	{
		/* ... */
	}


	public function isModified(?string $key = null): bool
	{
		/* ... */
	}


	public function hasStructuralChanges(): bool
	{
		/* ... */
	}


	public function getModifiedKeys(): array
	{
		/* ... */
	}


	public function save(bool $silent = false): bool
	{
		/* ... */
	}


	public function saveChained(bool $silent = false): self
	{
		/* ... */
	}


	public function getParentRecord(): ?DBHelperRecordInterface
	{
		/* ... */
	}


	public function getFormValues(): array
	{
		/* ... */
	}


	public function onKeyModified(callable $callback): EventableListener
	{
		/* ... */
	}


	public function onCreated(DBHelper_BaseCollection_OperationContext_Create $context): void
	{
		/* ... */
	}


	public function onDeleted(DBHelper_BaseCollection_OperationContext_Delete $context): void
	{
		/* ... */
	}


	public function onBeforeDelete(DBHelper_BaseCollection_OperationContext_Delete $context): void
	{
		/* ... */
	}


	public function getChildDisposables(): array
	{
		/* ... */
	}


	public function getID(): int
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/Traits/RecordDecoratorTrait.php`

```php
namespace DBHelper\Traits;

use AppUtils\Microtime as Microtime;
use Application\EventHandler\Eventables\EventableListener as EventableListener;
use Application_Users_User as Application_Users_User;
use DBHelper\BaseCollection\DBHelperCollectionInterface as DBHelperCollectionInterface;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use DBHelper_BaseCollection_OperationContext_Create as DBHelper_BaseCollection_OperationContext_Create;
use DBHelper_BaseCollection_OperationContext_Delete as DBHelper_BaseCollection_OperationContext_Delete;
use DateTime as DateTime;

/**
 * Trait used to implement the {@see RecordDecoratorInterface} by forwarding
 * method calls to the decorated record.
 *
 * > NOTE: Additional traits are typically used in conjunction with this trait,
 * > as demonstrated by {@see BaseRecordDecorator}.
 *
 * @package DBHelper
 * @subpackage Decorators
 *
 * @see RecordDecoratorInterface
 */
trait RecordDecoratorTrait
{
	public function getLabel(): string
	{
		/* ... */
	}


	public function isStub(): bool
	{
		/* ... */
	}


	public function getCollection(): DBHelperCollectionInterface
	{
		/* ... */
	}


	public function getRecordKey(string $name, mixed $default = null): mixed
	{
		/* ... */
	}


	public function getRecordIntKey(string $name, int $default = 0): int
	{
		/* ... */
	}


	public function getRecordDateKey(string $name, ?DateTime $default = null): ?DateTime
	{
		/* ... */
	}


	public function getRecordMicrotimeKey(string $name): ?Microtime
	{
		/* ... */
	}


	public function requireRecordMicrotimeKey(string $name): Microtime
	{
		/* ... */
	}


	public function getRecordData(): array
	{
		/* ... */
	}


	public function getInstanceID(): string
	{
		/* ... */
	}


	public function refreshData(): void
	{
		/* ... */
	}


	public function getRecordTable(): string
	{
		/* ... */
	}


	public function getRecordPrimaryName(): string
	{
		/* ... */
	}


	public function getRecordTypeName(): string
	{
		/* ... */
	}


	public function getRecordFloatKey(string $name, float $default = 0.0): float
	{
		/* ... */
	}


	public function getRecordStringKey(string $name, string $default = ''): string
	{
		/* ... */
	}


	public function getRecordBooleanKey(string $name, bool $default = false): bool
	{
		/* ... */
	}


	public function getRecordUserKey(string $name): ?Application_Users_User
	{
		/* ... */
	}


	public function requireRecordUserKey(string $name): Application_Users_User
	{
		/* ... */
	}


	public function recordKeyExists(string $name): bool
	{
		/* ... */
	}


	public function setRecordBooleanKey(string $name, bool $boolean, bool $yesno = true): bool
	{
		/* ... */
	}


	public function setRecordDateKey(string $name, DateTime $date): bool
	{
		/* ... */
	}


	public function setRecordKey(string $name, mixed $value): bool
	{
		/* ... */
	}


	public function requireRecordKeyExists(string $name): bool
	{
		/* ... */
	}


	public function isModified(?string $key = null): bool
	{
		/* ... */
	}


	public function hasStructuralChanges(): bool
	{
		/* ... */
	}


	public function getModifiedKeys(): array
	{
		/* ... */
	}


	public function save(bool $silent = false): bool
	{
		/* ... */
	}


	public function saveChained(bool $silent = false): self
	{
		/* ... */
	}


	public function getParentRecord(): ?DBHelperRecordInterface
	{
		/* ... */
	}


	public function getFormValues(): array
	{
		/* ... */
	}


	public function onKeyModified(callable $callback): EventableListener
	{
		/* ... */
	}


	public function onCreated(DBHelper_BaseCollection_OperationContext_Create $context): void
	{
		/* ... */
	}


	public function onDeleted(DBHelper_BaseCollection_OperationContext_Delete $context): void
	{
		/* ... */
	}


	public function onBeforeDelete(DBHelper_BaseCollection_OperationContext_Delete $context): void
	{
		/* ... */
	}


	public function getChildDisposables(): array
	{
		/* ... */
	}


	public function getID(): int
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/Traits/RecordKeyHandlersTrait.php`

```php
namespace DBHelper\Traits;

use AppUtils\ConvertHelper as ConvertHelper;
use AppUtils\Microtime as Microtime;
use Application\AppFactory as AppFactory;
use Application\Disposables\DisposableDisposedException as DisposableDisposedException;
use Application_Users_User as Application_Users_User;
use DBHelper\BaseRecord\BaseRecordException as BaseRecordException;
use DateTime as DateTime;

trait RecordKeyHandlersTrait
{
	/**
	 * Retrieves a data key as an integer. Converts the value to int,
	 * so beware using this on non-integer keys.
	 *
	 * @param string $name
	 * @param int $default
	 * @return int
	 * @throws DisposableDisposedException
	 */
	public function getRecordIntKey(string $name, int $default = 0): int
	{
		/* ... */
	}


	public function getRecordFloatKey(string $name, float $default = 0.0): float
	{
		/* ... */
	}


	public function getRecordStringKey(string $name, string $default = ''): string
	{
		/* ... */
	}


	public function getRecordDateKey(string $name, ?DateTime $default = null): ?DateTime
	{
		/* ... */
	}


	public function getRecordMicrotimeKey(string $name): ?Microtime
	{
		/* ... */
	}


	public function requireRecordMicrotimeKey(string $name): Microtime
	{
		/* ... */
	}


	public function getRecordUserKey(string $name): ?Application_Users_User
	{
		/* ... */
	}


	public function requireRecordUserKey(string $name): Application_Users_User
	{
		/* ... */
	}


	public function getRecordBooleanKey(string $name, bool $default = false): bool
	{
		/* ... */
	}


	public function setRecordBooleanKey(string $name, bool $boolean, bool $yesno = true): bool
	{
		/* ... */
	}


	public function setRecordDateKey(string $name, DateTime $date): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/Traits/RecordKeyHandlersTrait.php`

```php
namespace DBHelper\Traits;

use AppUtils\ConvertHelper as ConvertHelper;
use AppUtils\Microtime as Microtime;
use Application\AppFactory as AppFactory;
use Application\Disposables\DisposableDisposedException as DisposableDisposedException;
use Application_Users_User as Application_Users_User;
use DBHelper\BaseRecord\BaseRecordException as BaseRecordException;
use DateTime as DateTime;

trait RecordKeyHandlersTrait
{
	/**
	 * Retrieves a data key as an integer. Converts the value to int,
	 * so beware using this on non-integer keys.
	 *
	 * @param string $name
	 * @param int $default
	 * @return int
	 * @throws DisposableDisposedException
	 */
	public function getRecordIntKey(string $name, int $default = 0): int
	{
		/* ... */
	}


	public function getRecordFloatKey(string $name, float $default = 0.0): float
	{
		/* ... */
	}


	public function getRecordStringKey(string $name, string $default = ''): string
	{
		/* ... */
	}


	public function getRecordDateKey(string $name, ?DateTime $default = null): ?DateTime
	{
		/* ... */
	}


	public function getRecordMicrotimeKey(string $name): ?Microtime
	{
		/* ... */
	}


	public function requireRecordMicrotimeKey(string $name): Microtime
	{
		/* ... */
	}


	public function getRecordUserKey(string $name): ?Application_Users_User
	{
		/* ... */
	}


	public function requireRecordUserKey(string $name): Application_Users_User
	{
		/* ... */
	}


	public function getRecordBooleanKey(string $name, bool $default = false): bool
	{
		/* ... */
	}


	public function setRecordBooleanKey(string $name, bool $boolean, bool $yesno = true): bool
	{
		/* ... */
	}


	public function setRecordDateKey(string $name, DateTime $date): bool
	{
		/* ... */
	}
}


```
---
**File Statistics**
- **Size**: 167.6 KB
- **Lines**: 7868
File: `modules/db-helper/architecture-core.md`
