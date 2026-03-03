# Application Sets - Administration Interface
_SOURCE: Public Class Signatures_
# Public Class Signatures
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── AppSets/
                └── Admin/
                    └── AppSetAdminURLs.php
                    └── AppSetScreenRights.php
                    └── Screens/
                        ├── AppSetsDevelMode.php
                        ├── Submode/
                        │   └── CreateSetSubmode.php
                        │   └── SetsListSubmode.php
                        │   └── View/
                        │       ├── DeleteAction.php
                        │       ├── DocumentationAction.php
                        │       ├── SettingsAction.php
                        │       ├── StatusAction.php
                        │   └── ViewSubmode.php
                    └── Traits/
                        └── SubmodeInterface.php
                        └── SubmodeTrait.php
                        └── ViewActionInterface.php
                        └── ViewActionTrait.php

```
###  Path: `/src/classes/Application/AppSets/Admin/AppSetAdminURLs.php`

```php
namespace Application\AppSets\Admin;

use Application\AppSets\Admin\Screens\Submode\View\DeleteAction as DeleteAction;
use Application\AppSets\Admin\Screens\Submode\View\DocumentationAction as DocumentationAction;
use Application\AppSets\Admin\Screens\Submode\View\SettingsAction as SettingsAction;
use Application\AppSets\Admin\Screens\Submode\View\StatusAction as StatusAction;
use Application\AppSets\AppSet as AppSet;
use Application\AppSets\AppSetsCollection as AppSetsCollection;
use Application\Development\Admin\Screens\DevelArea as DevelArea;
use Application\Sets\Admin\Screens\AppSetsDevelMode as AppSetsDevelMode;
use Application\Sets\Admin\Screens\Submode\ViewSubmode as ViewSubmode;
use UI\AdminURLs\AdminURL as AdminURL;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

final class AppSetAdminURLs
{
	public function delete(): AdminURLInterface
	{
		/* ... */
	}


	public function status(): AdminURLInterface
	{
		/* ... */
	}


	public function documentation(): AdminURLInterface
	{
		/* ... */
	}


	public function settings(): AdminURLInterface
	{
		/* ... */
	}


	public function makeActive(): AdminURLInterface
	{
		/* ... */
	}


	public function view(): AdminURLInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/AppSets/Admin/AppSetScreenRights.php`

```php
namespace Application\Sets\Admin;

use Application_User as Application_User;

final class AppSetScreenRights
{
	public const SCREEN_APP_SETS = Application_User::RIGHT_DEVELOPER;
	public const SCREEN_APP_SETS_CREATE = Application_User::RIGHT_DEVELOPER;
	public const SCREEN_DELETE_SET = Application_User::RIGHT_DEVELOPER;
	public const SCREEN_EDIT_SET = Application_User::RIGHT_DEVELOPER;
	public const SCREEN_LIST = Application_User::RIGHT_DEVELOPER;
	public const SCREEN_VIEW_STATUS = Application_User::RIGHT_DEVELOPER;
	public const SCREEN_VIEW = Application_User::RIGHT_DEVELOPER;
}


```
###  Path: `/src/classes/Application/AppSets/Admin/Screens/AppSetsDevelMode.php`

```php
namespace Application\Sets\Admin\Screens;

use Application\Admin\Area\BaseMode as BaseMode;
use Application\Admin\Traits\DevelModeInterface as DevelModeInterface;
use Application\Admin\Traits\DevelModeTrait as DevelModeTrait;
use Application\Sets\Admin\AppSetScreenRights as AppSetScreenRights;
use Application\Sets\Admin\Screens\Submode\SetsListSubmode as SetsListSubmode;
use UI as UI;

final class AppSetsDevelMode extends BaseMode implements DevelModeInterface
{
	use DevelModeTrait;

	public const URL_NAME = 'appsets';

	public function getURLName(): string
	{
		/* ... */
	}


	public function getRequiredRight(): string
	{
		/* ... */
	}


	public function getTitle(): string
	{
		/* ... */
	}


	public function getNavigationTitle(): string
	{
		/* ... */
	}


	public function getDevCategory(): string
	{
		/* ... */
	}


	public function getDefaultSubmode(): string
	{
		/* ... */
	}


	/**
	 * @return class-string<SetsListSubmode>
	 */
	public function getDefaultSubscreenClass(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/AppSets/Admin/Screens/Submode/CreateSetSubmode.php`

```php
namespace Application\Sets\Admin\Screens\Submode;

use Application\AppFactory as AppFactory;
use Application\AppSets\AppSetSettingsManager as AppSetSettingsManager;
use Application\AppSets\AppSetsCollection as AppSetsCollection;
use Application\Sets\Admin\AppSetScreenRights as AppSetScreenRights;
use Application\Sets\Admin\Traits\SubmodeInterface as SubmodeInterface;
use Application\Sets\Admin\Traits\SubmodeTrait as SubmodeTrait;
use DBHelper\Admin\Screens\Submode\BaseRecordCreateSubmode as BaseRecordCreateSubmode;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

final class CreateSetSubmode extends BaseRecordCreateSubmode implements SubmodeInterface
{
	use SubmodeTrait;

	public const URL_NAME = 'create';

	public function getURLName(): string
	{
		/* ... */
	}


	public function getRequiredRight(): string
	{
		/* ... */
	}


	public function getTitle(): string
	{
		/* ... */
	}


	public function getNavigationTitle(): string
	{
		/* ... */
	}


	public function createCollection(): AppSetsCollection
	{
		/* ... */
	}


	public function getSettingsManager(): AppSetSettingsManager
	{
		/* ... */
	}


	public function getBackOrCancelURL(): string
	{
		/* ... */
	}


	public function getSuccessMessage(DBHelperRecordInterface $record): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/AppSets/Admin/Screens/Submode/SetsListSubmode.php`

```php
namespace Application\Sets\Admin\Screens\Submode;

use Application\AppSets\AppSet as AppSet;
use Application\AppSets\AppSetsCollection as AppSetsCollection;
use Application\Sets\Admin\AppSetScreenRights as AppSetScreenRights;
use Application\Sets\Admin\Traits\SubmodeInterface as SubmodeInterface;
use Application\Sets\Admin\Traits\SubmodeTrait as SubmodeTrait;
use DBHelper\Admin\Screens\Submode\BaseRecordListSubmode as BaseRecordListSubmode;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use DBHelper_BaseFilterCriteria_Record as DBHelper_BaseFilterCriteria_Record;
use UI as UI;
use UI_DataGrid_Entry as UI_DataGrid_Entry;

final class SetsListSubmode extends BaseRecordListSubmode implements SubmodeInterface
{
	use SubmodeTrait;

	public const URL_NAME = 'list';
	public const COL_ID = 'id';
	public const COL_ALIAS = 'alias';
	public const COL_ACTIVE = 'active';
	public const COL_DEFAULT_AREA = 'default';
	public const COL_ENABLED = 'enabled';
	public const COL_LABEL = 'label';

	public function getURLName(): string
	{
		/* ... */
	}


	public function getRequiredRight(): string
	{
		/* ... */
	}


	public function getTitle(): string
	{
		/* ... */
	}


	public function getNavigationTitle(): string
	{
		/* ... */
	}


	public function getBackOrCancelURL(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/AppSets/Admin/Screens/Submode/View/DeleteAction.php`

```php
namespace Application\AppSets\Admin\Screens\Submode\View;

use AppUtils\ClassHelper as ClassHelper;
use AppUtils\OperationResult as OperationResult;
use Application\AppSets\AppSet as AppSet;
use Application\AppSets\AppSetsCollection as AppSetsCollection;
use Application\Sets\Admin\AppSetScreenRights as AppSetScreenRights;
use Application\Sets\Admin\Traits\SubmodeInterface as SubmodeInterface;
use Application\Sets\Admin\Traits\SubmodeTrait as SubmodeTrait;
use Application\Sets\Admin\Traits\ViewActionInterface as ViewActionInterface;
use Application\Sets\Admin\Traits\ViewActionTrait as ViewActionTrait;
use DBHelper\Admin\Screens\Action\BaseRecordDeleteAction as BaseRecordDeleteAction;
use DBHelper\Admin\Screens\Submode\BaseRecordDeleteSubmode as BaseRecordDeleteSubmode;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

final class DeleteAction extends BaseRecordDeleteAction implements ViewActionInterface
{
	use ViewActionTrait;

	public const URL_NAME = 'delete';

	public function getURLName(): string
	{
		/* ... */
	}


	public function getRequiredRight(): string
	{
		/* ... */
	}


	public function getTitle(): string
	{
		/* ... */
	}


	public function getNavigationTitle(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/AppSets/Admin/Screens/Submode/View/DocumentationAction.php`

```php
namespace Application\AppSets\Admin\Screens\Submode\View;

use Application\Sets\Admin\AppSetScreenRights as AppSetScreenRights;
use Application\Sets\Admin\Traits\ViewActionInterface as ViewActionInterface;
use Application\Sets\Admin\Traits\ViewActionTrait as ViewActionTrait;
use DBHelper\Admin\Screens\Action\BaseRecordAction as BaseRecordAction;
use UI_Themes_Theme_ContentRenderer as UI_Themes_Theme_ContentRenderer;

final class DocumentationAction extends BaseRecordAction implements ViewActionInterface
{
	use ViewActionTrait;

	public const URL_NAME = 'documentation';

	public function getURLName(): string
	{
		/* ... */
	}


	public function getRequiredRight(): string
	{
		/* ... */
	}


	public function getTitle(): string
	{
		/* ... */
	}


	public function getNavigationTitle(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/AppSets/Admin/Screens/Submode/View/SettingsAction.php`

```php
namespace Application\AppSets\Admin\Screens\Submode\View;

use Application\AppSets\AppSetSettingsManager as AppSetSettingsManager;
use Application\Sets\Admin\AppSetScreenRights as AppSetScreenRights;
use Application\Sets\Admin\Traits\ViewActionInterface as ViewActionInterface;
use Application\Sets\Admin\Traits\ViewActionTrait as ViewActionTrait;
use DBHelper\Admin\Screens\Action\BaseRecordSettingsAction as BaseRecordSettingsAction;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;

final class SettingsAction extends BaseRecordSettingsAction implements ViewActionInterface
{
	use ViewActionTrait;

	public const URL_NAME = 'settings';

	public function getURLName(): string
	{
		/* ... */
	}


	public function getRequiredRight(): string
	{
		/* ... */
	}


	public function getTitle(): string
	{
		/* ... */
	}


	public function getNavigationTitle(): string
	{
		/* ... */
	}


	public function isUserAllowedEditing(): bool
	{
		/* ... */
	}


	public function isEditable(): bool
	{
		/* ... */
	}


	public function getSettingsManager(): AppSetSettingsManager
	{
		/* ... */
	}


	public function getSuccessMessage(DBHelperRecordInterface $record): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/AppSets/Admin/Screens/Submode/View/StatusAction.php`

```php
namespace Application\AppSets\Admin\Screens\Submode\View;

use Application\AppSets\AppSet as AppSet;
use Application\Sets\Admin\AppSetScreenRights as AppSetScreenRights;
use Application\Sets\Admin\Traits\ViewActionInterface as ViewActionInterface;
use Application\Sets\Admin\Traits\ViewActionTrait as ViewActionTrait;
use DBHelper\Admin\Screens\Action\BaseRecordStatusAction as BaseRecordStatusAction;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use UI as UI;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;
use UI_PropertiesGrid as UI_PropertiesGrid;

/**
 * @property AppSet $record
 */
final class StatusAction extends BaseRecordStatusAction implements ViewActionInterface
{
	use ViewActionTrait;

	public const URL_NAME = 'status';
	public const REQUEST_PARAM_SET_ACTIVE = 'set_active';

	public function getURLName(): string
	{
		/* ... */
	}


	public function getTitle(): string
	{
		/* ... */
	}


	public function getRequiredRight(): string
	{
		/* ... */
	}


	public function getRecordStatusURL(): AdminURLInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/AppSets/Admin/Screens/Submode/ViewSubmode.php`

```php
namespace Application\Sets\Admin\Screens\Submode;

use Application\AppSets\Admin\Screens\Submode\View\StatusAction as StatusAction;
use Application\AppSets\AppSet as AppSet;
use Application\AppSets\AppSetsCollection as AppSetsCollection;
use Application\Sets\Admin\AppSetScreenRights as AppSetScreenRights;
use Application\Sets\Admin\Traits\SubmodeInterface as SubmodeInterface;
use Application\Sets\Admin\Traits\SubmodeTrait as SubmodeTrait;
use DBHelper\Admin\Screens\Submode\BaseRecordSubmode as BaseRecordSubmode;
use UI as UI;

/**
 * @property AppSet $record
 */
final class ViewSubmode extends BaseRecordSubmode implements SubmodeInterface
{
	use SubmodeTrait;

	public const URL_NAME = 'view';

	public function getURLName(): string
	{
		/* ... */
	}


	public function getNavigationTitle(): string
	{
		/* ... */
	}


	public function getTitle(): string
	{
		/* ... */
	}


	public function getRequiredRight(): string
	{
		/* ... */
	}


	public function getDefaultAction(): string
	{
		/* ... */
	}


	public function getDefaultSubscreenClass(): string
	{
		/* ... */
	}


	public function getRecordMissingURL(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/AppSets/Admin/Traits/SubmodeInterface.php`

```php
namespace Application\Sets\Admin\Traits;

use Application\Admin\ClassLoaderScreenInterface as ClassLoaderScreenInterface;
use Application\Interfaces\Admin\AdminSubmodeInterface as AdminSubmodeInterface;

interface SubmodeInterface extends AdminSubmodeInterface, ClassLoaderScreenInterface
{
}


```
###  Path: `/src/classes/Application/AppSets/Admin/Traits/SubmodeTrait.php`

```php
namespace Application\Sets\Admin\Traits;

use AppUtils\ClassHelper as ClassHelper;
use Application\AppSets\AppSet as AppSet;
use Application\Sets\Admin\Screens\AppSetsDevelMode as AppSetsDevelMode;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;

trait SubmodeTrait
{
	public function getDefaultAction(): string
	{
		/* ... */
	}


	public function getDefaultSubscreenClass(): null
	{
		/* ... */
	}


	public function getParentScreenClass(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/AppSets/Admin/Traits/ViewActionInterface.php`

```php
namespace Application\Sets\Admin\Traits;

use Application\Admin\ClassLoaderScreenInterface as ClassLoaderScreenInterface;
use Application\Interfaces\Admin\AdminActionInterface as AdminActionInterface;

interface ViewActionInterface extends AdminActionInterface, ClassLoaderScreenInterface
{
}


```
###  Path: `/src/classes/Application/AppSets/Admin/Traits/ViewActionTrait.php`

```php
namespace Application\Sets\Admin\Traits;

use AppUtils\ClassHelper as ClassHelper;
use Application\AppSets\AppSet as AppSet;
use Application\AppSets\AppSetsCollection as AppSetsCollection;
use Application\Sets\Admin\Screens\Submode\ViewSubmode as ViewSubmode;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

trait ViewActionTrait
{
	public function getParentScreenClass(): string
	{
		/* ... */
	}


	public function getRecordMissingURL(): string|AdminURLInterface
	{
		/* ... */
	}


	public function getBackOrCancelURL(): string
	{
		/* ... */
	}


	public function createCollection(): AppSetsCollection
	{
		/* ... */
	}


	public function getRecord(): AppSet
	{
		/* ... */
	}


	public function getCollection(): AppSetsCollection
	{
		/* ... */
	}
}


```
---
**File Statistics**
- **Size**: 15.31 KB
- **Lines**: 700
File: `modules/application-sets/admin-interface.md`
