# DBHelper - Admin UI Architecture
_SOURCE: Admin UI layer public class signatures_
# Admin UI layer public class signatures
```
// Structure of documents
└── src/
    └── classes/
        └── DBHelper/
            └── Admin/
                └── BaseCollectionListBuilder.php
                └── BaseDBRecordSelectionTieIn.php
                └── DBHelperAdminException.php
                └── DBRecordSelectionTieInInterface.php
                └── Requests/
                    ├── BaseDBRecordRequestType.php
                └── Screens/
                    ├── Action/
                    │   ├── BaseRecordAction.php
                    │   ├── BaseRecordCreateAction.php
                    │   ├── BaseRecordDeleteAction.php
                    │   ├── BaseRecordListAction.php
                    │   ├── BaseRecordSettingsAction.php
                    │   ├── BaseRecordStatusAction.php
                    ├── Mode/
                    │   ├── BaseRecordCreateMode.php
                    │   ├── BaseRecordListMode.php
                    │   ├── BaseRecordMode.php
                    ├── Submode/
                    │   └── BaseRecordCreateSubmode.php
                    │   └── BaseRecordDeleteSubmode.php
                    │   └── BaseRecordListSubmode.php
                    │   └── BaseRecordSettingsSubmode.php
                    │   └── BaseRecordStatusSubmode.php
                    │   └── BaseRecordSubmode.php
                └── Traits/
                    └── RecordCreateScreenInterface.php
                    └── RecordCreateScreenTrait.php
                    └── RecordDeleteScreenInterface.php
                    └── RecordDeleteScreenTrait.php
                    └── RecordEditScreenInterface.php
                    └── RecordEditScreenTrait.php
                    └── RecordListScreenInterface.php
                    └── RecordListScreenTrait.php
                    └── RecordScreenInterface.php
                    └── RecordScreenTrait.php
                    └── RecordSettingsScreenInterface.php
                    └── RecordSettingsScreenTrait.php
                    └── RecordStatusScreenInterface.php
                    └── RecordStatusScreenTrait.php

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
###  Path: `/src/classes/DBHelper/Admin/DBHelperAdminException.php`

```php
namespace DBHelper\Admin;

use Application_Exception as Application_Exception;

/**
 * @package DBHelper
 * @subpackage Admin Screens
 */
class DBHelperAdminException extends Application_Exception
{
	public const ERROR_NO_RECORD_IN_REQUEST = 169701;
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
###  Path: `/src/classes/DBHelper/Admin/Traits/RecordCreateScreenTrait.php`

```php
namespace DBHelper\Admin\Traits;

/**
 * @see RecordCreateScreenInterface
 */
trait RecordCreateScreenTrait
{
	public function isUserAllowedEditing(): bool
	{
		/* ... */
	}


	final public function isEditMode(): bool
	{
		/* ... */
	}
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
###  Path: `/src/classes/DBHelper/Admin/Traits/RecordDeleteScreenTrait.php`

```php
namespace DBHelper\Admin\Traits;

use AppUtils\OperationResult as OperationResult;
use DBHelper\BaseCollection\DBHelperCollectionInterface as DBHelperCollectionInterface;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;

/**
 * @see RecordDeleteScreenInterface
 */
trait RecordDeleteScreenTrait
{
	abstract public function createCollection(): DBHelperCollectionInterface;


	public function getNavigationTitle(): string
	{
		/* ... */
	}


	public function getTitle(): string
	{
		/* ... */
	}


	public function getURLName(): string
	{
		/* ... */
	}
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
###  Path: `/src/classes/DBHelper/Admin/Traits/RecordEditScreenTrait.php`

```php
namespace DBHelper\Admin\Traits;

use DBHelper\BaseCollection\DBHelperCollectionInterface as DBHelperCollectionInterface;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

/**
 * @see RecordEditScreenInterface
 */
trait RecordEditScreenTrait
{
	public function isEditMode(): bool
	{
		/* ... */
	}


	public function getRecord(): DBHelperRecordInterface
	{
		/* ... */
	}


	public function getCollection(): DBHelperCollectionInterface
	{
		/* ... */
	}


	public function getRecordMissingURL(): string|AdminURLInterface
	{
		/* ... */
	}
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


	public function hasFilterSettings(): bool
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
###  Path: `/src/classes/DBHelper/Admin/Traits/RecordScreenTrait.php`

```php
namespace DBHelper\Admin\Traits;

use DBHelper\BaseCollection\DBHelperCollectionInterface as DBHelperCollectionInterface;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use UI_DataGrid as UI_DataGrid;

/**
 * @see RecordScreenInterface
 */
trait RecordScreenTrait
{
	public function getRecord(): DBHelperRecordInterface
	{
		/* ... */
	}


	public function getCollection(): DBHelperCollectionInterface
	{
		/* ... */
	}


	/**
	 * Updated to automatically add the record's primary
	 * key value to the form's hidden variables. Also adds
	 * the parent record's ID if present.
	 *
	 * @inheritDoc
	 */
	public function createFormableForm(string $name, $defaultData = []): self
	{
		/* ... */
	}
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
###  Path: `/src/classes/DBHelper/Admin/Traits/RecordSettingsScreenTrait.php`

```php
namespace DBHelper\Admin\Traits;

use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;
use Application_Exception as Application_Exception;
use Application_Formable_RecordSettings as Application_Formable_RecordSettings;
use Application_Formable_RecordSettings_Extended as Application_Formable_RecordSettings_Extended;
use Application_Formable_RecordSettings_ValueSet as Application_Formable_RecordSettings_ValueSet;
use Application_Media as Application_Media;
use DBHelper\BaseCollection\BaseChildCollection as BaseChildCollection;
use DBHelper\BaseCollection\DBHelperCollectionInterface as DBHelperCollectionInterface;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use Throwable as Throwable;
use UI as UI;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;
use UI_Themes_Theme_ContentRenderer as UI_Themes_Theme_ContentRenderer;

/**
 * Trait for administration screens that are used to
 * edit the settings of a DBHelper record. Handles
 * fetching the record, building the form and all the
 * rest needed to handle the settings.
 *
 * Works both with the edit and create screens.
 *
 * Usage:
 *
 * 1) Extend one of the premade screen classes (ex: Application_Admin_Area_Mode_Submode_CollectionEdit)
 * 2) Implement the abstract methods
 * 3) Implement any of the optional overridable methods
 *
 * After this, you can choose between handling form elements
 * manually in the admin screen, or if a SettingsManager can
 * be used instead, which further automates the process.
 *
 * Manual mode
 *
 * 4) Override the methods required when no settings manager is present
 * 5) You may need to use `_filterFormValues`
 * 6) You may need to use `_handleAfterSave`
 *
 * SettingsManager mode
 *
 * 4) Override the `getSettingsManager` method
 *
 * @package Application
 * @subpackage Admin
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see RecordSettingsScreenInterface
 */
trait RecordSettingsScreenTrait
{
	/**
	 * Retrieves the administration screen that can be used to delete the record, if any.
	 * @return AdminScreenInterface|NULL
	 */
	public function getDeleteScreen(): ?AdminScreenInterface
	{
		/* ... */
	}


	/**
	 * Retrieves the default form values to use for the form.
	 *
	 * @return array<string,mixed>
	 */
	public function getDefaultFormValues(): array
	{
		/* ... */
	}


	public function getNavigationTitle(): string
	{
		/* ... */
	}


	public function getURLName(): string
	{
		/* ... */
	}


	public function getFormName(): string
	{
		/* ... */
	}


	public function isReadonly(): bool
	{
		/* ... */
	}


	public function getSuccessURL(DBHelperRecordInterface $record): string|AdminURLInterface
	{
		/* ... */
	}


	/**
	 * @return array<string,mixed>
	 * @throws Application_Exception
	 */
	public function getSettingsFormValues(): array
	{
		/* ... */
	}


	public function getDeleteConfirmMessage(): string
	{
		/* ... */
	}


	public function getCancelLabel(): string
	{
		/* ... */
	}


	public function getSettingsManager(): ?Application_Formable_RecordSettings
	{
		/* ... */
	}


	public function getAbstract(): string
	{
		/* ... */
	}


	/**
	 * Retrieves a list of form element names that must
	 * be present in the form.
	 *
	 * NOTE: Only used if not using a settings manager.
	 *
	 * @return string[]
	 */
	public function getSettingsKeyNames(): array
	{
		/* ... */
	}
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
###  Path: `/src/classes/DBHelper/Admin/Traits/RecordStatusScreenTrait.php`

```php
namespace DBHelper\Admin\Traits;

use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;
use UI_PropertiesGrid as UI_PropertiesGrid;
use UI_Themes_Theme_ContentRenderer as UI_Themes_Theme_ContentRenderer;

/**
 * @see RecordStatusScreenInterface
 */
trait RecordStatusScreenTrait
{
	public function getURLName(): string
	{
		/* ... */
	}


	public function getNavigationTitle(): string
	{
		/* ... */
	}


	abstract public function getRecordStatusURL(): string|AdminURLInterface;
}


```
---
**File Statistics**
- **Size**: 28.22 KB
- **Lines**: 1141
File: `modules/db-helper/architecture-ui.md`
