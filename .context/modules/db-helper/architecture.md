# DBHelper Architecture
_SOURCE: DBHelper Public Interfaces and DTOs_
# DBHelper Public Interfaces and DTOs
```
// Structure of documents
└── src/
    └── classes/
        └── DBHelper/
            └── Admin/
                ├── BaseCollectionListBuilder.php
                ├── BaseDBRecordSelectionTieIn.php
                ├── DBRecordSelectionTieInInterface.php
                ├── Requests/
                │   ├── BaseDBRecordRequestType.php
                ├── Screens/
                │   ├── Action/
                │   │   ├── BaseRecordAction.php
                │   │   ├── BaseRecordCreateAction.php
                │   │   ├── BaseRecordDeleteAction.php
                │   │   ├── BaseRecordListAction.php
                │   │   ├── BaseRecordSettingsAction.php
                │   │   ├── BaseRecordStatusAction.php
                │   ├── Mode/
                │   │   ├── BaseRecordCreateMode.php
                │   │   ├── BaseRecordListMode.php
                │   │   ├── BaseRecordMode.php
                │   ├── Submode/
                │   │   └── BaseRecordCreateSubmode.php
                │   │   └── BaseRecordDeleteSubmode.php
                │   │   └── BaseRecordListSubmode.php
                │   │   └── BaseRecordSettingsSubmode.php
                │   │   └── BaseRecordStatusSubmode.php
                │   │   └── BaseRecordSubmode.php
                ├── Traits/
                │   └── RecordCreateScreenInterface.php
                │   └── RecordDeleteScreenInterface.php
                │   └── RecordEditScreenInterface.php
                │   └── RecordListScreenInterface.php
                │   └── RecordListScreenTrait.php
                │   └── RecordScreenInterface.php
                │   └── RecordSettingsScreenInterface.php
                │   └── RecordStatusScreenInterface.php
            └── BaseCollection.php
            └── BaseCollection/
                ├── BaseChildCollection.php
                ├── ChildCollectionInterface.php
                ├── DBHelperCollectionInterface.php
                ├── OperationContext.php
            └── BaseFilterCriteria.php
            └── BaseFilterCriteria/
                ├── BaseCollectionFilteringInterface.php
                ├── IntegerCollectionFilteringInterface.php
                ├── StringCollectionFilteringInterface.php
            └── BaseFilterSettings.php
            └── BaseRecord.php
            └── BaseRecord/
                ├── BaseRecordDecorator.php
            └── BaseRecordSettings.php
            └── DBHelper.php
            └── DBHelperFilterCriteriaInterface.php
            └── DBHelperFilterSettingsInterface.php
            └── Exception/
                ├── BaseErrorRenderer.php
            └── FetchBase.php
            └── Interfaces/
                ├── DBHelperRecordInterface.php
            └── Traits/
                └── LooseDBRecordInterface.php
                └── LooseDBRecordTrait.php
                └── RecordDecoratorInterface.php

```
###  Path: `/src/classes/DBHelper/Admin/BaseCollectionListBuilder.php`

```php
namespace DBHelper\Admin;

use AppUtils\ConvertHelper\JSONConverter as JSONConverter;
use Application\Interfaces\FilterCriteriaInterface as FilterCriteriaInterface;
use DBHelper\BaseCollection\DBHelperCollectionInterface as DBHelperCollectionInterface;
use DBHelper\DBHelperFilterSettingsInterface as DBHelperFilterSettingsInterface;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use DBHelper_Exception as DBHelper_Exception;
use UI\DataGrid\BaseListBuilder as BaseListBuilder;

abstract class BaseCollectionListBuilder extends BaseListBuilder
{
	abstract public function getCollection(): DBHelperCollectionInterface;


	public function getRecordTypeLabelSingular(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/Admin/BaseDBRecordSelectionTieIn.php`

```php
namespace DBHelper\Admin;

use Application\Collection\Admin\BaseRecordSelectionTieIn as BaseRecordSelectionTieIn;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;

/**
 * Tie-in class for selecting a DB record from a short list
 * in an administration screen.
 *
 * For documentation, see the base class {@see BaseRecordSelectionTieIn}.
 * The current class is a DBHelper-specific implementation of that class.
 *
 * @package DBHelper
 * @subpackage Admin Screens
 */
abstract class BaseDBRecordSelectionTieIn extends BaseRecordSelectionTieIn implements DBRecordSelectionTieInInterface
{
	public function getRecordID(): ?int
	{
		/* ... */
	}


	public function getRequestPrimaryVarName(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/Admin/DBRecordSelectionTieInInterface.php`

```php
namespace DBHelper\Admin;

use Application\Collection\Admin\RecordSelectionTieInInterface as RecordSelectionTieInInterface;
use DBHelper\BaseCollection\DBHelperCollectionInterface as DBHelperCollectionInterface;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;

/**
 * Interface for admin-screen tie-in classes that
 * select a DB record from a short list.
 * See the class {@see BaseDBRecordSelectionTieIn} for
 * the implementation.
 *
 * @package DBHelper
 * @subpackage Admin Screens
 *
 * @method DBHelperRecordInterface[] getSelectableRecords()
 */
interface DBRecordSelectionTieInInterface extends RecordSelectionTieInInterface
{
	public function getCollection(): DBHelperCollectionInterface;


	public function getRecordID(): ?int;
}


```
###  Path: `/src/classes/DBHelper/Admin/Requests/BaseDBRecordRequestType.php`

```php
namespace DBHelper\Admin\Requests;

use AppUtils\ClassHelper as ClassHelper;
use Application\Admin\RequestTypes\BaseRequestType as BaseRequestType;
use Application\Admin\RequestTypes\RequestTypeInterface as RequestTypeInterface;
use DBHelper\BaseCollection\DBHelperCollectionInterface as DBHelperCollectionInterface;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;

/**
 * @implements RequestTypeInterface<DBHelperRecordInterface>
 */
abstract class BaseDBRecordRequestType extends BaseRequestType
{
	abstract public function getCollection(): DBHelperCollectionInterface;


	public function getRecord(): ?DBHelperRecordInterface
	{
		/* ... */
	}


	public function getRecordOrRedirect(): DBHelperRecordInterface
	{
		/* ... */
	}


	public function requireRecord(): DBHelperRecordInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/Admin/Screens/Action/BaseRecordAction.php`

```php
namespace DBHelper\Admin\Screens\Action;

use Application\Admin\Area\Mode\Submode\BaseAction as BaseAction;
use DBHelper\Admin\Traits\RecordScreenInterface as RecordScreenInterface;
use DBHelper\Admin\Traits\RecordScreenTrait as RecordScreenTrait;

/**
 * Abstract base class for an admin "action" screen that works with
 * a DBHelper collection record. It has methods to load the
 * record automatically from the request.
 *
 * @package DBHelper
 * @subpackage Admin
 */
abstract class BaseRecordAction extends BaseAction implements RecordScreenInterface
{
	use RecordScreenTrait;
}


```
###  Path: `/src/classes/DBHelper/Admin/Screens/Action/BaseRecordCreateAction.php`

```php
namespace DBHelper\Admin\Screens\Action;

use Application_Admin_Area_Mode_Submode_Action as Application_Admin_Area_Mode_Submode_Action;
use DBHelper\Admin\Traits\RecordCreateScreenInterface as RecordCreateScreenInterface;
use DBHelper\Admin\Traits\RecordCreateScreenTrait as RecordCreateScreenTrait;
use DBHelper\Admin\Traits\RecordSettingsScreenTrait as RecordSettingsScreenTrait;

abstract class BaseRecordCreateAction extends Application_Admin_Area_Mode_Submode_Action implements RecordCreateScreenInterface
{
	use RecordSettingsScreenTrait;
	use RecordCreateScreenTrait;

	public function getDefaultSubscreenClass(): null
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/Admin/Screens/Action/BaseRecordDeleteAction.php`

```php
namespace DBHelper\Admin\Screens\Action;

use DBHelper\Admin\Traits\RecordDeleteScreenInterface as RecordDeleteScreenInterface;
use DBHelper\Admin\Traits\RecordDeleteScreenTrait as RecordDeleteScreenTrait;

abstract class BaseRecordDeleteAction extends BaseRecordAction implements RecordDeleteScreenInterface
{
	use RecordDeleteScreenTrait;
}


```
###  Path: `/src/classes/DBHelper/Admin/Screens/Action/BaseRecordListAction.php`

```php
namespace DBHelper\Admin\Screens\Action;

use Application\Admin\Area\Mode\Submode\BaseAction as BaseAction;
use DBHelper\Admin\Traits\RecordListScreenInterface as RecordListScreenInterface;
use DBHelper\Admin\Traits\RecordListScreenTrait as RecordListScreenTrait;

abstract class BaseRecordListAction extends BaseAction implements RecordListScreenInterface
{
	use RecordListScreenTrait;
}


```
###  Path: `/src/classes/DBHelper/Admin/Screens/Action/BaseRecordSettingsAction.php`

```php
namespace DBHelper\Admin\Screens\Action;

use Application\Admin\Area\Mode\Submode\BaseAction as BaseAction;
use DBHelper\Admin\Traits\RecordEditScreenInterface as RecordEditScreenInterface;
use DBHelper\Admin\Traits\RecordEditScreenTrait as RecordEditScreenTrait;
use DBHelper\Admin\Traits\RecordSettingsScreenTrait as RecordSettingsScreenTrait;

abstract class BaseRecordSettingsAction extends BaseAction implements RecordEditScreenInterface
{
	use RecordSettingsScreenTrait;
	use RecordEditScreenTrait;
}


```
###  Path: `/src/classes/DBHelper/Admin/Screens/Action/BaseRecordStatusAction.php`

```php
namespace DBHelper\Admin\Screens\Action;

use DBHelper\Admin\Traits\RecordStatusScreenInterface as RecordStatusScreenInterface;
use DBHelper\Admin\Traits\RecordStatusScreenTrait as RecordStatusScreenTrait;

abstract class BaseRecordStatusAction extends BaseRecordAction implements RecordStatusScreenInterface
{
	use RecordStatusScreenTrait;
}


```
###  Path: `/src/classes/DBHelper/Admin/Screens/Mode/BaseRecordCreateMode.php`

```php
namespace DBHelper\Admin\Screens\Mode;

use Application\Admin\Area\BaseMode as BaseMode;
use DBHelper\Admin\Traits\RecordCreateScreenInterface as RecordCreateScreenInterface;
use DBHelper\Admin\Traits\RecordCreateScreenTrait as RecordCreateScreenTrait;
use DBHelper\Admin\Traits\RecordSettingsScreenTrait as RecordSettingsScreenTrait;

abstract class BaseRecordCreateMode extends BaseMode implements RecordCreateScreenInterface
{
	use RecordSettingsScreenTrait;
	use RecordCreateScreenTrait;

	public function getDefaultSubmode(): string
	{
		/* ... */
	}


	public function getDefaultSubscreenClass(): ?string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/Admin/Screens/Mode/BaseRecordListMode.php`

```php
namespace DBHelper\Admin\Screens\Mode;

use Application\Admin\Area\BaseMode as BaseMode;
use DBHelper\Admin\Traits\RecordListScreenInterface as RecordListScreenInterface;
use DBHelper\Admin\Traits\RecordListScreenTrait as RecordListScreenTrait;

abstract class BaseRecordListMode extends BaseMode implements RecordListScreenInterface
{
	use RecordListScreenTrait;

	public function getDefaultSubmode(): string
	{
		/* ... */
	}


	public function getDefaultSubscreenClass(): ?string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/Admin/Screens/Mode/BaseRecordMode.php`

```php
namespace DBHelper\Admin\Screens\Mode;

use Application\Admin\Area\BaseMode as BaseMode;
use DBHelper\Admin\Traits\RecordScreenInterface as RecordScreenInterface;
use DBHelper\Admin\Traits\RecordScreenTrait as RecordScreenTrait;

abstract class BaseRecordMode extends BaseMode implements RecordScreenInterface
{
	use RecordScreenTrait;
}


```
###  Path: `/src/classes/DBHelper/Admin/Screens/Submode/BaseRecordCreateSubmode.php`

```php
namespace DBHelper\Admin\Screens\Submode;

use Application\Admin\Area\Mode\BaseSubmode as BaseSubmode;
use DBHelper\Admin\Traits\RecordCreateScreenInterface as RecordCreateScreenInterface;
use DBHelper\Admin\Traits\RecordCreateScreenTrait as RecordCreateScreenTrait;
use DBHelper\Admin\Traits\RecordSettingsScreenTrait as RecordSettingsScreenTrait;

abstract class BaseRecordCreateSubmode extends BaseSubmode implements RecordCreateScreenInterface
{
	use RecordSettingsScreenTrait;
	use RecordCreateScreenTrait;

	public function getDefaultAction(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/Admin/Screens/Submode/BaseRecordDeleteSubmode.php`

```php
namespace DBHelper\Admin\Screens\Submode;

use Application\Admin\ClassLoaderScreenInterface as ClassLoaderScreenInterface;
use Application_Admin_Area_Mode_Submode as Application_Admin_Area_Mode_Submode;
use DBHelper\Admin\Traits\RecordDeleteScreenInterface as RecordDeleteScreenInterface;
use DBHelper\Admin\Traits\RecordDeleteScreenTrait as RecordDeleteScreenTrait;

abstract class BaseRecordDeleteSubmode extends Application_Admin_Area_Mode_Submode implements RecordDeleteScreenInterface, ClassLoaderScreenInterface
{
	use RecordDeleteScreenTrait;

	public function getDefaultAction(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/Admin/Screens/Submode/BaseRecordListSubmode.php`

```php
namespace DBHelper\Admin\Screens\Submode;

use Application\Admin\Area\Mode\BaseSubmode as BaseSubmode;
use DBHelper\Admin\Traits\RecordListScreenInterface as RecordListScreenInterface;
use DBHelper\Admin\Traits\RecordListScreenTrait as RecordListScreenTrait;

abstract class BaseRecordListSubmode extends BaseSubmode implements RecordListScreenInterface
{
	use RecordListScreenTrait;

	public function getDefaultAction(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/Admin/Screens/Submode/BaseRecordSettingsSubmode.php`

```php
namespace DBHelper\Admin\Screens\Submode;

use Application\Admin\Area\Mode\BaseSubmode as BaseSubmode;
use DBHelper\Admin\Traits\RecordEditScreenInterface as RecordEditScreenInterface;
use DBHelper\Admin\Traits\RecordEditScreenTrait as RecordEditScreenTrait;
use DBHelper\Admin\Traits\RecordSettingsScreenTrait as RecordSettingsScreenTrait;

abstract class BaseRecordSettingsSubmode extends BaseSubmode implements RecordEditScreenInterface
{
	use RecordSettingsScreenTrait;
	use RecordEditScreenTrait;

	public function getDefaultAction(): string
	{
		/* ... */
	}


	public function getDefaultSubscreenClass(): ?string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/Admin/Screens/Submode/BaseRecordStatusSubmode.php`

```php
namespace DBHelper\Admin\Screens\Submode;

use DBHelper\Admin\Traits\RecordStatusScreenInterface as RecordStatusScreenInterface;
use DBHelper\Admin\Traits\RecordStatusScreenTrait as RecordStatusScreenTrait;

abstract class BaseRecordStatusSubmode extends BaseRecordSubmode implements RecordStatusScreenInterface
{
	use RecordStatusScreenTrait;

	public function getDefaultAction(): string
	{
		/* ... */
	}


	public function getDefaultSubscreenClass(): ?string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/Admin/Screens/Submode/BaseRecordSubmode.php`

```php
namespace DBHelper\Admin\Screens\Submode;

use Application\Admin\Area\Mode\BaseSubmode as BaseSubmode;
use DBHelper\Admin\Traits\RecordScreenInterface as RecordScreenInterface;
use DBHelper\Admin\Traits\RecordScreenTrait as RecordScreenTrait;

/**
 * @package DBHelper
 * @subpackage Admin
 */
abstract class BaseRecordSubmode extends BaseSubmode implements RecordScreenInterface
{
	use RecordScreenTrait;
}


```
###  Path: `/src/classes/DBHelper/Admin/Traits/RecordCreateScreenInterface.php`

```php
namespace DBHelper\Admin\Traits;

interface RecordCreateScreenInterface extends RecordSettingsScreenInterface
{
}


```
###  Path: `/src/classes/DBHelper/Admin/Traits/RecordDeleteScreenInterface.php`

```php
namespace DBHelper\Admin\Traits;

/**
 * @see RecordDeleteScreenTrait
 */
interface RecordDeleteScreenInterface extends RecordScreenInterface
{
	public const URL_NAME = 'delete';

	public function getBackOrCancelURL(): string;
}


```
###  Path: `/src/classes/DBHelper/Admin/Traits/RecordEditScreenInterface.php`

```php
namespace DBHelper\Admin\Traits;

/**
 * Interface for screens that allow editing of a record.
 *
 * This is an extension of the interface {@see RecordSettingsScreenInterface}
 * with the difference that a record is required for editing.
 *
 * @package DBHelper
 * @subpackage Admin
 * @see RecordEditScreenTrait
 */
interface RecordEditScreenInterface extends RecordSettingsScreenInterface, RecordScreenInterface
{
	public function isUserAllowedEditing(): bool;


	/**
	 * Whether the record can be edited at all.
	 *
	 * @return bool
	 */
	public function isEditable(): bool;
}


```
###  Path: `/src/classes/DBHelper/Admin/Traits/RecordListScreenInterface.php`

```php
namespace DBHelper\Admin\Traits;

use AppUtils\Interfaces\StringableInterface as StringableInterface;
use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

interface RecordListScreenInterface extends AdminScreenInterface
{
	public const URL_NAME_DEFAULT = 'list';

	public function getGridName(): string;


	public function getBackOrCancelURL(): string|AdminURLInterface;


	/**
	 * @return array<string,string|int|float|StringableInterface|NULL>
	 */
	public function getPersistVars(): array;
}


```
###  Path: `/src/classes/DBHelper/Admin/Traits/RecordListScreenTrait.php`

```php
namespace DBHelper\Admin\Traits;

use AppUtils\Interfaces\StringableInterface as StringableInterface;
use DBHelper\BaseCollection\DBHelperCollectionInterface as DBHelperCollectionInterface;
use DBHelper\DBHelperFilterCriteriaInterface as DBHelperFilterCriteriaInterface;
use DBHelper\DBHelperFilterSettingsInterface as DBHelperFilterSettingsInterface;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use DBHelper_BaseFilterCriteria_Record as DBHelper_BaseFilterCriteria_Record;
use DateTime as DateTime;
use UI_DataGrid as UI_DataGrid;
use UI_DataGrid_Entry as UI_DataGrid_Entry;
use UI_Themes_Theme_ContentRenderer as UI_Themes_Theme_ContentRenderer;

/**
 * Trait used for simplify displaying lists of DBHelper records:
 * handles all the required configuration, and offers a standardized
 * interface of overloadable methods to set it up.
 *
 * Usage:
 *
 * # Implement the abstract methods
 * # Overload <code>validateRequest</code> as needed (called directly after _handleActions)
 * # Overload <code>configureFilters</code> to customize the filter criteria as needed
 * # Overload <code>_handleSidebar</code> if needed, taking care to call the parent method
 * # Overload <code>getURLName()</code> if different from "list"
 *
 * @package Application
 * @subpackage Admin
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see RecordListScreenInterface
 */
trait RecordListScreenTrait
{
	protected DBHelperCollectionInterface $collection;
	protected string $gridName;
	protected UI_DataGrid $grid;
	protected DBHelperFilterSettingsInterface $filterSettings;
	protected DBHelperFilterCriteriaInterface $filters;
	protected bool $filtersAdded = false;


	public function getURLName(): string
	{
		/* ... */
	}


	public function getGridName(): string
	{
		/* ... */
	}


	public function getPersistVars(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/DBHelper/Admin/Traits/RecordScreenInterface.php`

```php
namespace DBHelper\Admin\Traits;

use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;
use Application\Interfaces\Admin\MissingRecordInterface as MissingRecordInterface;
use DBHelper\BaseCollection\DBHelperCollectionInterface as DBHelperCollectionInterface;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;

/**
 * @see RecordScreenTrait
 */
interface RecordScreenInterface extends AdminScreenInterface, MissingRecordInterface
{
	public function getRecord(): DBHelperRecordInterface;


	public function getCollection(): DBHelperCollectionInterface;
}


```
###  Path: `/src/classes/DBHelper/Admin/Traits/RecordSettingsScreenInterface.php`

```php
namespace DBHelper\Admin\Traits;

use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;
use Application_Formable_RecordSettings as Application_Formable_RecordSettings;
use DBHelper\BaseCollection\DBHelperCollectionInterface as DBHelperCollectionInterface;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

/**
 * Interface for admin screens that display a settings form for
 * a DB item collection record.
 *
 * Supports both creating new records and editing existing ones.
 *
 * @package Application
 * @subpackage Admin
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see RecordSettingsScreenTrait
 */
interface RecordSettingsScreenInterface extends AdminScreenInterface
{
	public const ERROR_UNKNOWN_SETTING_KEY = 17901;
	public const ERROR_MISSING_REQUIRED_METHOD = 17902;

	public function createCollection(): DBHelperCollectionInterface;


	public function getBackOrCancelURL(): string|AdminURLInterface;


	/**
	 * Retrieves the name of the HTML form tag.
	 *
	 * @return string
	 */
	public function getFormName(): string;


	/**
	 * The URL to redirect to once the record has been created.
	 *
	 * @param DBHelperRecordInterface $record
	 * @return string|AdminURLInterface
	 */
	public function getSuccessURL(DBHelperRecordInterface $record): string|AdminURLInterface;


	/**
	 * @param DBHelperRecordInterface $record
	 * @return string
	 */
	public function getSuccessMessage(DBHelperRecordInterface $record): string;


	/**
	 * Retrieves the form values to use once the form has been
	 * submitted and validated.
	 *
	 * @return array<string,mixed>
	 */
	public function getSettingsFormValues(): array;


	/**
	 * @return string[]
	 */
	public function getSettingsKeyNames(): array;


	/**
	 * @return Application_Formable_RecordSettings|NULL
	 */
	public function getSettingsManager(): ?Application_Formable_RecordSettings;


	public function isEditMode(): bool;


	public function getDeleteScreen(): ?AdminScreenInterface;


	/**
	 * @return array<string,mixed>
	 */
	public function getDefaultFormValues(): array;


	public function getDeleteConfirmMessage(): string;


	public function isUserAllowedEditing(): bool;
}


```
###  Path: `/src/classes/DBHelper/Admin/Traits/RecordStatusScreenInterface.php`

```php
namespace DBHelper\Admin\Traits;

use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

interface RecordStatusScreenInterface extends RecordScreenInterface
{
	public const URL_NAME = 'status';

	public function getRecordStatusURL(): string|AdminURLInterface;
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
	use Application_Traits_Eventable;
	use Application_Traits_Loggable;
	use BeforeCreateEventTrait;
	use AfterRecordCreatedEventTrait;

	protected ?string $recordIDTable;

	/** @var class-string<DBHelperRecordInterface> */
	protected string $recordClassName;
	protected string $recordSortKey;
	protected string $recordSortDir;
	protected string $recordPrimaryName;
	protected string $recordTable;
	protected ?DBHelperRecordInterface $dummyRecord = null;

	/** @var class-string<DBHelperFilterCriteriaInterface> */
	protected string $recordFiltersClassName;

	/** @var class-string<DBHelperFilterSettingsInterface> */
	protected string $recordFilterSettingsClassName;
	protected string $instanceID;
	protected bool $started = false;
	protected DBHelper_BaseCollection_Keys $keys;
	protected static int $instanceCounter = 0;

	/** @var array<string,string> */
	protected array $foreignKeys = [];

	/** @var DBHelperRecordInterface[] */
	protected array $records = [];
	private ?string $recordIDTablePrimaryName = null;

	/** @var array<int,bool> */
	private array $idLookup = [];

	/** @var DBHelperRecordInterface[]|null */
	private ?array $allRecords = null;
	protected ?string $logPrefix = null;


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


	final public function onBeforeCreateRecord(callable $callback): Application_EventHandler_EventableListener
	{
		/* ... */
	}


	final public function onAfterCreateRecord(callable $callback): Application_EventHandler_EventableListener
	{
		/* ... */
	}


	final public function onAfterDeleteRecord(callable $callback): Application_EventHandler_EventableListener
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
	protected ?DBHelperRecordInterface $parentRecord = null;


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
###  Path: `/src/classes/DBHelper/BaseCollection/DBHelperCollectionInterface.php`

```php
namespace DBHelper\BaseCollection;

use AppUtils\Request as Request;
use Application\Collection\IntegerCollectionInterface as IntegerCollectionInterface;
use Application_EventHandler_EventableListener as Application_EventHandler_EventableListener;
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
	 * @return Application_EventHandler_EventableListener
	 * @see BeforeCreateRecordEvent
	 */
	public function onBeforeCreateRecord(callable $callback): Application_EventHandler_EventableListener;


	/**
	 * Listens to any new records created in the collection.
	 * This allows tasks to execute on the collection level
	 * when records are created, as compared to the record's
	 * own created event handled via {@see DBHelperRecordInterface::onCreated()}.
	 *
	 * @param callable(AfterCreateRecordEvent) : void $callback
	 * @return Application_EventHandler_EventableListener
	 * @see AfterCreateRecordEvent
	 */
	public function onAfterCreateRecord(callable $callback): Application_EventHandler_EventableListener;


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
	public function onAfterDeleteRecord(callable $callback): Application_EventHandler_EventableListener;


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

	protected string $contextID;
	protected DBHelperCollectionInterface $collection;
	protected DBHelperRecordInterface $record;
	protected bool $silent = false;
	protected static int $contextIDCounter = 0;


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
	protected IntegerCollectionFilteringInterface $collection;
	protected string $recordTableName;
	protected string $recordPrimaryName;


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
	protected DBHelperCollectionInterface $collection;


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
	use Application_Traits_Eventable;
	use RecordKeyHandlersTrait;

	public const ERROR_RECORD_DOES_NOT_EXIST = 13301;
	public const ERROR_RECORD_KEY_UNKNOWN = 13302;

	/** @var array<string,mixed>|NULL */
	protected ?array $recordData = null;
	protected string $recordTypeName;
	protected string $recordTable;
	protected string $recordPrimaryName;
	protected bool $isStub = false;

	/** @var string[] */
	protected array $customModified = [];
	protected DBHelperCollectionInterface $collection;
	protected int $recordID;
	protected string $instanceID;
	protected static int $instanceCounter = 0;

	/** @var string[] */
	protected array $recordKeys = [];

	/** @var string[] */
	protected array $modified = [];

	/** @var array<string,array{label:string,isStructural:bool}> */
	protected array $registeredKeys = [];


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


	public function onKeyModified(callable $callback): Application_EventHandler_EventableListener
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
use Application_Traits_Eventable as Application_Traits_Eventable;
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
	use Application_Traits_Eventable;
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
	 * Counter for the number of queries that were run during a request.
	 * @var int
	 */
	protected static int $queryCount = 0;

	/** @var int */
	protected static int $queryCountRead = 0;

	/** @var int */
	protected static int $queryCountWrite = 0;

	/**
	 * The statement object for the last query that was run, if any.
	 * @var PDOStatement|NULL
	 */
	protected static ?PDOStatement $activeStatement = null;

	/**
	 * @var array<string,PDO|NULL>
	 * @see getDB()
	 */
	protected static array $db = ['main' => null, 'admin' => null];
	protected static float $startTime;

	/** @var array{0:string,1:array<string,mixed>}|null */
	protected static ?array $activeQuery = null;
	protected static bool $queryLogging = false;

	/** @var TrackedQuery[] */
	protected static array $queries = [];
	private static bool $queryTracking = false;

	/** @var string[]|null */
	private static ?array $tablesList = null;

	/**
	 * Used to keep track of transactions.
	 * @var boolean
	 */
	protected static bool $transactionStarted = false;

	/** @var string */
	protected static string $selectedDB = 'main';

	/** @var PDO|NULL */
	protected static ?PDO $activeDB = null;

	/** @var array<string,array{name:string,username:string,password:string,host:string,port:int}> */
	protected static array $databases = [];
	protected static int $eventCounter = 0;

	/** @var array<string,array<int,array{id:int,callback:callable,data:mixed|NULL}>> */
	protected static array $eventHandlers = [];
	protected static bool $debugging = false;

	/** @var callable|NULL */
	protected static $logCallback = null;

	/** @var array<string,bool> */
	protected static array $cachedColumnExist = [];

	/** @var array<string,DBHelperCollectionInterface> */
	protected static array $collections = [];

	/** @var array<string,array<int,array{tablename:string,columname:string}>> */
	private static array $fieldRelations = [];


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
	 * @return array<int|string,mixed>|NULL
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
	 * @return NULL|array
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
	 * @return array<int,array<string,string>>
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
	 * @return string|NULL
	 *
	 * @throws DBHelper_Exception
	 * @throws JsonException
	 */
	public static function fetchKey(string $key, $statementOrBuilder, array $variables = []): ?string
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
	 * @return string[]
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

	protected StringBuilder $message;
	protected ?PDOException $exception;


	abstract public function getEmptyMessageText(): string;


	public function render(): string
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
	protected string $table;
	protected static int $placeholderCounter = 0;

	/** @var array<int|string,mixed> */
	protected array $data = [];

	/** @var string[] */
	protected array $where = [];


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
###  Path: `/src/classes/DBHelper/Interfaces/DBHelperRecordInterface.php`

```php
namespace DBHelper\Interfaces;

use AppUtils\ConvertHelper_Exception as ConvertHelper_Exception;
use AppUtils\Microtime as Microtime;
use Application\Collection\IntegerCollectionItemInterface as IntegerCollectionItemInterface;
use Application\Disposables\Attributes\DisposedAware as DisposedAware;
use Application\Disposables\DisposableDisposedException as DisposableDisposedException;
use Application\Disposables\DisposableInterface as DisposableInterface;
use Application_EventHandler_EventableListener as Application_EventHandler_EventableListener;
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
	 * @return Application_EventHandler_EventableListener
	 */
	public function onKeyModified(callable $callback): Application_EventHandler_EventableListener;


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
	/** @var array<string,string|int|float|bool> */
	protected array $recordData;
	protected int $recordID;
	protected string $recordTable;
	protected string $recordPrimary;
	protected bool $recordModified = false;

	/** @var string[] */
	protected array $recordKeyNames;


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