# Admin - Core Architecture
<INSTRUCTION>
# Admin Module — Core Architecture

Public API surface of the admin screen system: the Skeleton base class,
area/mode/submode base classes, screen rights, request types, and URL helper.

</INSTRUCTION>
------------------------------------------------------------
_SOURCE: Root-level classes and interfaces_
# Root-level classes and interfaces
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── Admin/
                └── AdminException.php
                └── AdminScreenStubInterface.php
                └── BaseArea.php
                └── BaseScreenRights.php
                └── ClassLoaderScreenInterface.php
                └── ClassLoaderScreenTrait.php
                └── ScreenException.php
                └── ScreenRightsContainerInterface.php
                └── ScreenRightsContainerTrait.php
                └── ScreenRightsInterface.php
                └── Skeleton.php
                └── URL.php
                └── WizardException.php

```
###  Path: `/src/classes/Application/Admin/AdminException.php`

```php
namespace ;

use Application\Exception\ApplicationException as ApplicationException;

class AdminException extends ApplicationException
{
	public const ERROR_SCREEN_SOURCE_NOT_FOUND = 188401;
	public const ERROR_SCREEN_INDEX_INVALID = 188404;
	public const ERROR_SCREEN_SUBSCREEN_NOT_FOUND = 188405;
	public const ERROR_INVALID_APP_SOURCE_FOLDER = 188406;
	public const ERROR_ADMIN_AREA_NOT_FOUND = 188407;
}


```
###  Path: `/src/classes/Application/Admin/AdminScreenStubInterface.php`

```php
namespace Application\Admin;

use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;

/**
 * Interface for admin screen stubs: Any screen that implements this
 * interface will be ignored by the admin screen indexer.
 *
 * @package Admin
 * @subpackage Screens
 */
interface AdminScreenStubInterface extends AdminScreenInterface
{
}


```
###  Path: `/src/classes/Application/Admin/BaseArea.php`

```php
namespace Application\Admin;

use Application\Interfaces\AllowableMigrationInterface as AllowableMigrationInterface;
use Application\Traits\AllowableMigrationTrait as AllowableMigrationTrait;
use Application_Admin_Area as Application_Admin_Area;

abstract class BaseArea extends Application_Admin_Area implements AllowableMigrationInterface, ClassLoaderScreenInterface
{
	use AllowableMigrationTrait;
	use ClassLoaderScreenTrait;
}


```
###  Path: `/src/classes/Application/Admin/BaseScreenRights.php`

```php
namespace Application\Admin;

use AdminException as AdminException;
use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;
use Application_User as Application_User;

/**
 * Utility abstract class to implement a class that can access
 * the required rights for admin screens.
 *
 * <h2>Usage</h2>
 *
 * 1. Create a new class that extends this one.
 * 2. Implement the abstract method `_registerRights()`.
 * 3. Use the `register()` method to register the rights for each screen.
 *
 * <h2>Recommended</h2>
 *
 * 1. Use a separate enum class to set screen rights.
 * 2. Define a constant that associates the screen classes with their rights.
 * 3. Iterate over the constant to register the screens.
 *
 * @package Application
 * @subpackage Admin
 */
abstract class BaseScreenRights implements ScreenRightsInterface
{
	public const ERROR_SCREEN_CLASS_NOT_FOUND = 156701;
	public const ERROR_SCREEN_CLASS_ALREADY_REGISTERED = 156702;

	/**
	 * Gets the right required for the target screen.
	 *
	 * > NOTE: Will throw an exception if the screen has no
	 * > right registered. Use {@see self::screenExists()} to
	 * > check if a screen has a right registered.
	 *
	 * @param AdminScreenInterface|class-string<AdminScreenInterface> $screen
	 * @return string
	 * @throws AdminException {@see self::ERROR_SCREEN_CLASS_NOT_FOUND}
	 */
	public function getByScreen(AdminScreenInterface|string $screen): string
	{
		/* ... */
	}


	/**
	 * Returns whether a screen class has a right registered.
	 *
	 * @param AdminScreenInterface|class-string $screen
	 * @return bool
	 * @throws AdminException
	 */
	public function screenExists($screen): bool
	{
		/* ... */
	}


	/**
	 * @return array<class-string,string>
	 */
	public function getAll(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Admin/ClassLoaderScreenInterface.php`

```php
namespace Application\Admin;

use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;

interface ClassLoaderScreenInterface extends AdminScreenInterface
{
	/**
	 * @return class-string<AdminScreenInterface>|null
	 */
	public function getDefaultSubscreenClass(): ?string;


	/**
	 * @return class-string<AdminScreenInterface>|null
	 */
	public function getParentScreenClass(): ?string;
}


```
###  Path: `/src/classes/Application/Admin/ClassLoaderScreenTrait.php`

```php
namespace Application\Admin;

trait ClassLoaderScreenTrait
{
}


```
###  Path: `/src/classes/Application/Admin/ScreenException.php`

```php
namespace Application\Admin;

use AdminException as AdminException;
use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;
use Throwable as Throwable;

/**
 * Specialized admin screen exception, which automatically
 * adds screen-related information to the exception's developer
 * details.
 *
 * @package Application
 * @subpackage Exceptions
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ScreenException extends AdminException
{
	public const NO_RECORD_SPECIFIED_IN_REQUEST = 185101;

	public function getScreen(): AdminScreenInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Admin/ScreenRightsContainerInterface.php`

```php
namespace Application\Admin;

/**
 * Interface for the {@see ScreenRightsContainerTrait} trait.
 *
 * @package Application
 * @subpackage Admin
 *
 * @see ScreenRightsContainerTrait
 */
interface ScreenRightsContainerInterface
{
	public function getAdminScreens(): ScreenRightsInterface;
}


```
###  Path: `/src/classes/Application/Admin/ScreenRightsContainerTrait.php`

```php
namespace Application\Admin;

/**
 * Trait used by any classes that give access to
 * admin screen definitions.
 *
 * @package Application
 * @subpackage Admin
 *
 * @see ScreenRightsContainerInterface
 */
trait ScreenRightsContainerTrait
{
	public function getAdminScreens(): ScreenRightsInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Admin/ScreenRightsInterface.php`

```php
namespace Application\Admin;

use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;

/**
 * @package Application
 * @subpackage Admin
 */
interface ScreenRightsInterface
{
	/**
	 * Fetches the right for a specific admin screen class.
	 * @param AdminScreenInterface|class-string<AdminScreenInterface> $screen
	 * @return string
	 */
	public function getByScreen(AdminScreenInterface|string $screen): string;


	/**
	 * @param AdminScreenInterface|class-string<AdminScreenInterface> $screen
	 * @return bool
	 */
	public function screenExists(AdminScreenInterface|string $screen): bool;


	/**
	 * Fetches rights by admin screen class.
	 * @return array<class-string<AdminScreenInterface>,string>
	 */
	public function getAll(): array;
}


```
###  Path: `/src/classes/Application/Admin/Skeleton.php`

```php
namespace ;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use AppUtils\ClassHelper as ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException as BaseClassHelperException;
use AppUtils\FileHelper_Exception as FileHelper_Exception;
use AppUtils\Interfaces\StringableInterface as StringableInterface;
use Application\AppFactory as AppFactory;
use Application\Application as Application;
use Application\EventHandler\Eventables\EventableTrait as EventableTrait;
use Application\Framework\AppFolder as AppFolder;
use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;
use Application\Revisionable\RevisionableInterface as RevisionableInterface;
use Application\Traits\Admin\ScreenAccessTrait as ScreenAccessTrait;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

/**
 * Base class for administration screens. This has all the
 * common functionality that screens can use.
 *
 * NOTE: Other screen methods are available in the admin
 * screen trait, which is used for common methods that
 * do not fit into the skeleton.
 *
 * @package Application
 * @subpackage Admin
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Admin_Area
 * @see Application_Admin_Area_Mode
 * @see Application_Admin_Area_Mode_Submode
 * @see Application_Admin_Area_Mode_Submode_Action
 *
 * @see AdminScreenInterface
 * @see Application_Traits_Admin_Screen
 */
abstract class Application_Admin_Skeleton extends Application_Formable implements AdminScreenInterface, Application_Interfaces_Admin_LockableScreen
{
	use Application_Traits_Loggable;
	use ScreenAccessTrait;
	use EventableTrait;

	public const ERROR_NO_LOCKING_PRIMARY = 13001;
	public const ERROR_NO_LOCK_LABEL_METHOD_PRESENT = 13002;
	public const ERROR_NO_SUCH_CHILD_ADMIN_SCREEN = 13003;
	public const ERROR_LOCK_MANAGER_NOT_SET = 13004;
	public const LOCK_MODE_PRIMARYLESS = 'primaryless';
	public const LOCK_MODE_PRIMARYBASED = 'primarybased';

	public function getUser(): Application_User
	{
		/* ... */
	}


	public function getLockManagerPrimary()
	{
		/* ... */
	}


	public function getLockMode()
	{
		/* ... */
	}


	/**
	 * Retrieves the label of the record being locked in
	 * this screen. By default this uses the {@link getLockManagerPrimary()}
	 * method to retrieve the item of the type {@link Application_LockableRecord_Interface},
	 * to use its label. Otherwise, it is expected to
	 * override this method to provide a label.
	 *
	 * @throws Application_Exception
	 * @return string
	 */
	public function getLockLabel()
	{
		/* ... */
	}


	public function startUI(): void
	{
		/* ... */
	}


	/**
	 * @return Application_Driver
	 */
	public function getDriver()
	{
		/* ... */
	}


	/**
	 * @return UI_Page_Breadcrumb
	 */
	public function getBreadcrumb(): UI_Page_Breadcrumb
	{
		/* ... */
	}


	/**
	 * Adds a success message, and redirects to the target URL.
	 *
	 * @param string|number|UI_Renderable_Interface $message
	 * @param array<string,string|number>|string|AdminURLInterface $paramsOrURL
	 * @return never
	 */
	public function redirectWithSuccessMessage($message, $paramsOrURL)
	{
		/* ... */
	}


	/**
	 * Adds an error message, and redirects to the target URL.
	 *
	 * @param string|number|UI_Renderable_Interface $message
	 * @param array<string,string|number>|string|AdminURLInterface $paramsOrURL
	 * @return never
	 */
	public function redirectWithErrorMessage($message, $paramsOrURL)
	{
		/* ... */
	}


	/**
	 * Adds an informational message, and redirects to the target URL.
	 *
	 * @param string|number|UI_Renderable_Interface $message
	 * @param array<string,string|number>|string|AdminURLInterface $paramsOrURL
	 * @return never
	 */
	public function redirectWithInfoMessage($message, $paramsOrURL)
	{
		/* ... */
	}


	/**
	 * @param string|array<string,string|int|float>|AdminURLInterface $paramsOrURL
	 * @return never
	 * @throws Application_Exception
	 */
	public function redirectTo($paramsOrURL)
	{
		/* ... */
	}


	public function renderTitleSubline($text)
	{
		/* ... */
	}


	/**
	 * Retrieves the page request parameters specific to this
	 * administration screen (mode / submode, etc...)
	 *
	 * @return array<string,string>
	 */
	public function getPageParams(): array
	{
		/* ... */
	}


	/**
	 * Retrieves a list of all available page request parameter names.
	 *
	 * @return string[]
	 */
	public static function getPageParamNames(): array
	{
		/* ... */
	}


	/**
	 * Checks whether simulation mode is active, which can be enabled by
	 * setting the <code>simulate_only</code> request parameter to <code>yes</code>.
	 * The use that is logged in additionally needs to be a developer for this
	 * to work.
	 *
	 * @return boolean
	 */
	public function isSimulationEnabled(): bool
	{
		/* ... */
	}


	/**
	 * Whether this administration screen can be locked using the lock manager.
	 * @return boolean
	 */
	public function isLockable(): bool
	{
		/* ... */
	}


	public function isLocked(): bool
	{
		/* ... */
	}


	/**
	 * @return Application_LockManager|NULL
	 */
	public function getLockManager(): ?Application_LockManager
	{
		/* ... */
	}


	public function requireLockManager(): Application_LockManager
	{
		/* ... */
	}


	/**
	 * Starts a DB transaction safely, using current simulation settings.
	 */
	public function startTransaction(): void
	{
		/* ... */
	}


	/**
	 * Check transaction is started or not.
	 *
	 * @return bool
	 */
	public function isTransactionStarted(): bool
	{
		/* ... */
	}


	/**
	 * Ends a DB transaction safely, using current simulation settings.
	 */
	public function endTransaction(): void
	{
		/* ... */
	}


	public function isAdminMode(): bool
	{
		/* ... */
	}


	/**
	 * For administration screens that support it: retrieves
	 * the help object instance and renders it.
	 *
	 * @return string
	 */
	public function renderHelp(): string
	{
		/* ... */
	}


	public function getActiveScreen(): AdminScreenInterface
	{
		/* ... */
	}


	public function isLocatedInApp(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Admin/URL.php`

```php
namespace ;

use AppUtils\Interfaces\StringableInterface as StringableInterface;
use AppUtils\URLInfo as URLInfo;
use Application\Exception\ApplicationException as ApplicationException;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

/**
 * Application URL parser: this is used to access information
 * about an application-internal URL. To create an instance,
 * use the method {@link Application_Driver::parseURL()}.
 *
 * @package Application
 * @subpackage Core
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see Application_Driver::parseURL()
 */
class Application_URL
{
	public const ERROR_INCOMPLETE_URL = 29801;

	/**
	 * Retrieves the path to the dispatcher file that handled
	 * the request in the URL. <code>index.php</code> is stripped
	 * to keep this consistent: filenames are only included if
	 * they are not an index file. An empty dispatcher means the
	 * main index.php file.
	 *
	 * @return string
	 */
	public function getDispatcher(): string
	{
		/* ... */
	}


	/**
	 * Retrieves the path to the target administration screen, if any.
	 * This is formatted like:
	 *
	 * <code>page.mode.submode.action</code>
	 *
	 * Example:
	 *
	 * <code>devel.maintenance</code>
	 *
	 * @return string
	 */
	public function getScreenPath(): string
	{
		/* ... */
	}


	public function getHash(): string
	{
		/* ... */
	}


	/**
	 * Retrieves all parameters beyond those selecting
	 * the target administration screen.
	 *
	 * @return string[]
	 */
	public function getParams(): array
	{
		/* ... */
	}


	/**
	 * Checks whether the URL has parameters.
	 * @return bool
	 */
	public function hasParams(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Admin/WizardException.php`

```php
namespace ;

class Application_Admin_WizardException extends AdminException
{
	public const ERROR_UNSUPPORTED_STEP_ACTION = 165801;
}


```
_SOURCE: Area base classes and mode hierarchy_
# Area base classes and mode hierarchy
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── Admin/
                └── Area/
                    └── BaseMode.php
                    └── Events/
                        ├── UIHandlingCompleteEvent.php
                    └── Mode/
                        └── BaseSubmode.php
                        └── Submode/
                            └── BaseAction.php

```
###  Path: `/src/classes/Application/Admin/Area/BaseMode.php`

```php
namespace Application\Admin\Area;

use Application\Interfaces\AllowableMigrationInterface as AllowableMigrationInterface;
use Application\Traits\AllowableMigrationTrait as AllowableMigrationTrait;
use Application_Admin_Area_Mode as Application_Admin_Area_Mode;

abstract class BaseMode extends Application_Admin_Area_Mode implements AllowableMigrationInterface
{
	use AllowableMigrationTrait;
}


```
###  Path: `/src/classes/Application/Admin/Area/Events/UIHandlingCompleteEvent.php`

```php
namespace Application\Admin\Area\Events;

use Application\EventHandler\Event\BaseEvent as BaseEvent;
use Application\Interfaces\Admin\AdminAreaInterface as AdminAreaInterface;

class UIHandlingCompleteEvent extends BaseEvent
{
	public const EVENT_NAME = 'UIHandlingComplete';

	public function getName(): string
	{
		/* ... */
	}


	public function getArea(): AdminAreaInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Admin/Area/Mode/BaseSubmode.php`

```php
namespace Application\Admin\Area\Mode;

use Application\Interfaces\AllowableMigrationInterface as AllowableMigrationInterface;
use Application\Traits\AllowableMigrationTrait as AllowableMigrationTrait;
use Application_Admin_Area_Mode_Submode as Application_Admin_Area_Mode_Submode;

abstract class BaseSubmode extends Application_Admin_Area_Mode_Submode implements AllowableMigrationInterface
{
	use AllowableMigrationTrait;
}


```
###  Path: `/src/classes/Application/Admin/Area/Mode/Submode/BaseAction.php`

```php
namespace Application\Admin\Area\Mode\Submode;

use Application\Interfaces\AllowableMigrationInterface as AllowableMigrationInterface;
use Application\Traits\AllowableMigrationTrait as AllowableMigrationTrait;
use Application_Admin_Area_Mode_Submode_Action as Application_Admin_Area_Mode_Submode_Action;

abstract class BaseAction extends Application_Admin_Area_Mode_Submode_Action implements AllowableMigrationInterface
{
	use AllowableMigrationTrait;

	public function getDefaultSubscreenClass(): null
	{
		/* ... */
	}
}


```
_SOURCE: Request type helpers_
# Request type helpers
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── Admin/
                └── RequestTypes/
                    └── BaseRequestType.php
                    └── RequestTypeInterface.php

```
###  Path: `/src/classes/Application/Admin/RequestTypes/BaseRequestType.php`

```php
namespace Application\Admin\RequestTypes;

use Application\Admin\ScreenException as ScreenException;
use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;

/**
 * Base implementation of {@see RequestTypeInterface}.
 *
 * @package Admin
 * @subpackage Request Types
 */
abstract class BaseRequestType implements RequestTypeInterface
{
	public function getRecordOrRedirect()
	{
		/* ... */
	}


	public function requireRecord()
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Admin/RequestTypes/RequestTypeInterface.php`

```php
namespace Application\Admin\RequestTypes;

use Application\Exception\ApplicationException as ApplicationException;
use Application\Interfaces\Admin\MissingRecordInterface as MissingRecordInterface;

/**
 * Interface for request types that deal with fetching records
 * from the current request. A base implementation is provided
 * by {@see BaseRequestType}.
 *
 * @package Admin
 * @subpackage Request Types
 *
 * @template T of object
 *
 * @see BaseRequestType
 */
interface RequestTypeInterface extends MissingRecordInterface
{
	/**
	 * Gets the record specified in the request, or null
	 * if none has been specified, or no such record exists
	 * (depending on the implementing logic).
	 *
	 * @return T|null
	 */
	public function getRecord();


	/**
	 * Gets the record specified in the request, or redirects
	 * to the appropriate URL if no such record exists
	 * (as specified in {@see self::getRecordMissingURL()}).
	 *
	 * @return T
	 */
	public function getRecordOrRedirect();


	/**
	 * Gets the record specified in the request, or throws an exception
	 * if no such record exists.
	 *
	 * @return T
	 * @throws ApplicationException If no record has been specified in the request.
	 */
	public function requireRecord();
}


```
_SOURCE: Developer mode interface and trait_
# Developer mode interface and trait
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── Admin/
                └── Traits/
                    └── DevelModeInterface.php
                    └── DevelModeTrait.php

```
###  Path: `/src/classes/Application/Admin/Traits/DevelModeInterface.php`

```php
namespace Application\Admin\Traits;

use Application\Admin\ClassLoaderScreenInterface as ClassLoaderScreenInterface;
use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;

interface DevelModeInterface extends AdminScreenInterface, ClassLoaderScreenInterface
{
	public function getDevCategory(): string;
}


```
###  Path: `/src/classes/Application/Admin/Traits/DevelModeTrait.php`

```php
namespace Application\Admin\Traits;

use Application\Development\Admin\Screens\DevelArea as DevelArea;

trait DevelModeTrait
{
	/**
	 * @return class-string<DevelArea>
	 */
	public function getParentScreenClass(): string
	{
		/* ... */
	}


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