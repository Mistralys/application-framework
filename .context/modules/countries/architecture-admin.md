# Countries - Admin UI Architecture (Public API)
_SOURCE: CountryAdminURLs, MainAdminURLs, CountryScreens, CountryRequestType, CountriesArea, screen modes, create wizard steps, view screens, mode/view traits_
# CountryAdminURLs, MainAdminURLs, CountryScreens, CountryRequestType, CountriesArea, screen modes, create wizard steps, view screens, mode/view traits
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── Countries/
                └── Admin/
                    └── CountryAdminURLs.php
                    └── CountryRequestType.php
                    └── CountryScreens.php
                    └── MainAdminURLs.php
                    └── Screens/
                        ├── CountriesArea.php
                        ├── Mode/
                        │   └── Create/
                        │       ├── BaseCreateStep.php
                        │       ├── ConfirmStep.php
                        │       ├── CountrySettingsStep.php
                        │       ├── CreateWizardException.php
                        │       ├── SourceCountrySelectionStep.php
                        │   └── CreateScreen.php
                        │   └── ListScreen.php
                        │   └── View/
                        │       ├── SettingsScreen.php
                        │       ├── StatusScreen.php
                        │   └── ViewScreen.php
                    └── Traits/
                        └── CountryModeInterface.php
                        └── CountryModeTrait.php
                        └── CountryRequestInterface.php
                        └── CountryRequestTrait.php
                        └── CountryViewInterface.php
                        └── CountryViewTrait.php

```
###  Path: `/src/classes/Application/Countries/Admin/CountryAdminURLs.php`

```php
namespace Application\Countries\Admin;

use Application\Countries\Admin\Screens\CountriesArea as CountriesArea;
use Application\Countries\Admin\Screens\Mode\View\SettingsScreen as SettingsScreen;
use Application\Countries\Admin\Screens\Mode\View\StatusScreen as StatusScreen;
use Application\Countries\Admin\Screens\Mode\ViewScreen as ViewScreen;
use Application_Countries as Application_Countries;
use Application_Countries_Country as Application_Countries_Country;
use UI\AdminURLs\AdminURL as AdminURL;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

class CountryAdminURLs
{
	public function status(): AdminURLInterface
	{
		/* ... */
	}


	public function settings(): AdminURLInterface
	{
		/* ... */
	}


	public function view(): AdminURLInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/Admin/CountryRequestType.php`

```php
namespace Application\Countries\Admin;

use AppUtils\ClassHelper as ClassHelper;
use Application\AppFactory as AppFactory;
use Application_Countries as Application_Countries;
use Application_Countries_Country as Application_Countries_Country;
use DBHelper\Admin\Requests\BaseDBRecordRequestType as BaseDBRecordRequestType;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

class CountryRequestType extends BaseDBRecordRequestType
{
	public function getCollection(): Application_Countries
	{
		/* ... */
	}


	public function getRecordMissingURL(): AdminURLInterface
	{
		/* ... */
	}


	public function getRecord(): ?Application_Countries_Country
	{
		/* ... */
	}


	public function getRecordOrRedirect(): Application_Countries_Country
	{
		/* ... */
	}


	public function requireRecord(): Application_Countries_Country
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/Admin/CountryScreens.php`

```php
namespace Application\Countries\Admin;

use Application\Admin\BaseScreenRights as BaseScreenRights;
use Application\Countries\Admin\Screens\CountriesArea as CountriesArea;
use Application\Countries\Admin\Screens\Mode\CreateScreen as CreateScreen;
use Application\Countries\Admin\Screens\Mode\ListScreen as ListScreen;
use Application\Countries\Rights\CountryScreenRights as CountryScreenRights;

class CountryScreens extends BaseScreenRights
{
	public const SCREEN_AREA = CountriesArea::class;
	public const SCREEN_LIST = ListScreen::class;
	public const SCREEN_CREATE = CreateScreen::class;
	public const SCREEN_VIEW = '';

	public const SCREEN_RIGHTS = array(
	        self::SCREEN_LIST => CountryScreenRights::SCREEN_LIST,
	        self::SCREEN_CREATE => CountryScreenRights::SCREEN_CREATE,
	        self::SCREEN_VIEW => CountryScreenRights::SCREEN_VIEW,
	        self::SCREEN_AREA => CountryScreenRights::SCREEN_AREA,
	    );
}


```
###  Path: `/src/classes/Application/Countries/Admin/MainAdminURLs.php`

```php
namespace Application\Countries\Admin;

use Application\Countries\Admin\Screens\CountriesArea as CountriesArea;
use Application\Countries\Admin\Screens\Mode\CreateScreen as CreateScreen;
use Application\Countries\Admin\Screens\Mode\ListScreen as ListScreen;
use UI\AdminURLs\AdminURL as AdminURL;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

class MainAdminURLs
{
	public function list(): AdminURLInterface
	{
		/* ... */
	}


	public function create(): AdminURLInterface
	{
		/* ... */
	}


	public function area(): AdminURLInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/Admin/Screens/CountriesArea.php`

```php
namespace Application\Countries\Admin\Screens;

use Application\Admin\BaseArea as BaseArea;
use Application\Countries\Admin\Screens\Mode\ListScreen as ListScreen;
use Application\Countries\Rights\CountryScreenRights as CountryScreenRights;
use UI as UI;
use UI_Icon as UI_Icon;

class CountriesArea extends BaseArea
{
	public const URL_NAME = 'countries';

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


	public function getDefaultMode(): string
	{
		/* ... */
	}


	public function getDefaultSubscreenClass(): string
	{
		/* ... */
	}


	public function getNavigationGroup(): string
	{
		/* ... */
	}


	public function getNavigationIcon(): ?UI_Icon
	{
		/* ... */
	}


	public function getDependencies(): array
	{
		/* ... */
	}


	public function isCore(): bool
	{
		/* ... */
	}


	public function getRequiredRight(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/Admin/Screens/Mode/Create/BaseCreateStep.php`

```php
namespace Application\Countries\Admin\Screens\Mode\Create;

use Application\AppFactory as AppFactory;
use Application\Countries\Admin\Screens\Mode\CreateScreen as CreateScreen;
use Application_Admin_Wizard_Step as Application_Admin_Wizard_Step;
use Application_Countries as Application_Countries;

/**
 * @property CreateScreen $wizard
 */
abstract class BaseCreateStep extends Application_Admin_Wizard_Step
{
	public function isMode(): bool
	{
		/* ... */
	}


	public function isSubmode(): bool
	{
		/* ... */
	}


	public function isAction(): bool
	{
		/* ... */
	}


	public function initDone(): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/Admin/Screens/Mode/Create/ConfirmStep.php`

```php
namespace Application\Countries\Admin\Screens\Mode\Create;

use Application\AppFactory as AppFactory;
use Application_Countries_Country as Application_Countries_Country;
use Application_Interfaces_Admin_Wizard_Step_Confirmation as Application_Interfaces_Admin_Wizard_Step_Confirmation;
use Application_Traits_Admin_Wizard_Step_Confirmation as Application_Traits_Admin_Wizard_Step_Confirmation;
use UI_PropertiesGrid as UI_PropertiesGrid;

class ConfirmStep extends BaseCreateStep implements Application_Interfaces_Admin_Wizard_Step_Confirmation
{
	use Application_Traits_Admin_Wizard_Step_Confirmation;

	public function getID(): string
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	public function getAbstract(): string
	{
		/* ... */
	}


	public function getCreatedCountry(): Application_Countries_Country
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/Admin/Screens/Mode/Create/CountrySettingsStep.php`

```php
namespace Application\Countries\Admin\Screens\Mode\Create;

use Application\AppFactory as AppFactory;
use Application\Countries\CountrySettingsManager as CountrySettingsManager;
use Application_Formable_RecordSettings as Application_Formable_RecordSettings;
use Application_Interfaces_Admin_Wizard_SettingsManagerStep as Application_Interfaces_Admin_Wizard_SettingsManagerStep;
use Application_Traits_Admin_Wizard_SettingsManagerStep as Application_Traits_Admin_Wizard_SettingsManagerStep;

class CountrySettingsStep extends BaseCreateStep implements Application_Interfaces_Admin_Wizard_SettingsManagerStep
{
	use Application_Traits_Admin_Wizard_SettingsManagerStep;

	public const STEP_NAME = 'CountrySettings';

	public function getID(): string
	{
		/* ... */
	}


	public function getDefaultFormValues(): array
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	public function getAbstract(): string
	{
		/* ... */
	}


	public function createSettingsManager(): Application_Formable_RecordSettings
	{
		/* ... */
	}


	public function getCountryLabel(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/Admin/Screens/Mode/Create/CreateWizardException.php`

```php
namespace Application\Countries\Admin\Screens\Mode\Create;

use Application\Countries\CountryException as CountryException;

class CreateWizardException extends CountryException
{
	public const ERROR_NO_COUNTRY_SELECTED = 177501;
}


```
###  Path: `/src/classes/Application/Countries/Admin/Screens/Mode/Create/SourceCountrySelectionStep.php`

```php
namespace Application\Countries\Admin\Screens\Mode\Create;

use AppLocalize\Localization as Localization;
use AppLocalize\Localization\Countries\CountryCollection as CountryCollection;
use AppLocalize\Localization\Countries\CountryInterface as CountryInterface;
use UI as UI;

class SourceCountrySelectionStep extends BaseCreateStep
{
	public const STEP_NAME = 'SourceCountrySelection';
	public const REQUEST_PARAM_ISO = 'iso';
	public const DATA_KEY_ISO = 'iso';

	public function getID(): string
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}


	public function _process(): bool
	{
		/* ... */
	}


	public function getCountry(): ?CountryInterface
	{
		/* ... */
	}


	public function requireCountry(): CountryInterface
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	public function getAbstract(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/Admin/Screens/Mode/CreateScreen.php`

```php
namespace Application\Countries\Admin\Screens\Mode;

use AppUtils\ClassHelper as ClassHelper;
use Application\Admin\Wizard\BaseWizardMode as BaseWizardMode;
use Application\AppFactory as AppFactory;
use Application\Countries\Admin\Screens\Mode\Create\ConfirmStep as ConfirmStep;
use Application\Countries\Admin\Screens\Mode\Create\CountrySettingsStep as CountrySettingsStep;
use Application\Countries\Admin\Screens\Mode\Create\SourceCountrySelectionStep as SourceCountrySelectionStep;
use Application\Countries\Admin\Traits\CountryModeInterface as CountryModeInterface;
use Application\Countries\Admin\Traits\CountryModeTrait as CountryModeTrait;
use Application\Countries\Rights\CountryScreenRights as CountryScreenRights;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

class CreateScreen extends BaseWizardMode implements CountryModeInterface
{
	use CountryModeTrait;

	public const URL_NAME = 'create';
	public const WIZARD_ID = 'CreateAppCountry';

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


	public function getNavigationTitle(): string
	{
		/* ... */
	}


	public function getWizardID(): string
	{
		/* ... */
	}


	public function getClassBase(): string
	{
		/* ... */
	}


	public function getCanceledURL(): AdminURLInterface
	{
		/* ... */
	}


	public function getSuccessMessage(): string
	{
		/* ... */
	}


	public function getSuccessURL(): string
	{
		/* ... */
	}


	public function getStepConfirm(): ConfirmStep
	{
		/* ... */
	}


	public function getStepSourceCountry(): SourceCountrySelectionStep
	{
		/* ... */
	}


	public function getStepSettings(): CountrySettingsStep
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/Admin/Screens/Mode/ListScreen.php`

```php
namespace Application\Countries\Admin\Screens\Mode;

use AppUtils\ClassHelper as ClassHelper;
use Application\AppFactory as AppFactory;
use Application\Countries\Admin\Traits\CountryModeInterface as CountryModeInterface;
use Application\Countries\Admin\Traits\CountryModeTrait as CountryModeTrait;
use Application\Countries\Rights\CountryScreenRights as CountryScreenRights;
use Application_Countries_Country as Application_Countries_Country;
use Application_Countries_FilterCriteria as Application_Countries_FilterCriteria;
use DBHelper\Admin\Screens\Mode\BaseRecordListMode as BaseRecordListMode;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use DBHelper_BaseFilterCriteria_Record as DBHelper_BaseFilterCriteria_Record;
use UI as UI;
use UI_DataGrid_Entry as UI_DataGrid_Entry;

/**
 * @property Application_Countries_FilterCriteria $filters
 */
class ListScreen extends BaseRecordListMode implements CountryModeInterface
{
	use CountryModeTrait;

	public const URL_NAME = 'list';
	public const COL_LABEL = 'label';
	public const COL_ISO = 'iso';
	public const COL_CURRENCY = 'currency';
	public const COL_LANGUAGE = 'language';
	public const COL_LOCALE_CODE = 'locale_code';

	public function getURLName(): string
	{
		/* ... */
	}


	public function getRequiredRight(): string
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


	public function getBackOrCancelURL(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/Admin/Screens/Mode/View/SettingsScreen.php`

```php
namespace Application\Countries\Admin\Screens\Mode\View;

use Application\AppFactory as AppFactory;
use Application\Countries\Admin\Traits\CountryViewInterface as CountryViewInterface;
use Application\Countries\Admin\Traits\CountryViewTrait as CountryViewTrait;
use Application\Countries\CountrySettingsManager as CountrySettingsManager;
use Application\Countries\Rights\CountryScreenRights as CountryScreenRights;
use Application_Countries as Application_Countries;
use Application_Countries_Country as Application_Countries_Country;
use DBHelper\Admin\Screens\Submode\BaseRecordSettingsSubmode as BaseRecordSettingsSubmode;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;

/**
 * @property Application_Countries_Country $record
 */
class SettingsScreen extends BaseRecordSettingsSubmode implements CountryViewInterface
{
	use CountryViewTrait;

	public const URL_NAME = 'settings';

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


	public function getSettingsManager(): CountrySettingsManager
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


	public function createCollection(): Application_Countries
	{
		/* ... */
	}


	public function getSuccessMessage(DBHelperRecordInterface $record): string
	{
		/* ... */
	}


	public function getBackOrCancelURL(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/Admin/Screens/Mode/View/StatusScreen.php`

```php
namespace Application\Countries\Admin\Screens\Mode\View;

use Application\AppFactory as AppFactory;
use Application\Countries\Admin\Traits\CountryViewInterface as CountryViewInterface;
use Application\Countries\Admin\Traits\CountryViewTrait as CountryViewTrait;
use Application\Countries\Rights\CountryScreenRights as CountryScreenRights;
use Application_Countries as Application_Countries;
use Application_Countries_Country as Application_Countries_Country;
use DBHelper\Admin\Screens\Submode\BaseRecordStatusSubmode as BaseRecordStatusSubmode;
use DBHelper\Admin\Screens\Submode\BaseRecordSubmode as BaseRecordSubmode;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;
use UI_PropertiesGrid as UI_PropertiesGrid;

/**
 * @property Application_Countries_Country $record
 */
class StatusScreen extends BaseRecordStatusSubmode implements CountryViewInterface
{
	use CountryViewTrait;

	public const URL_NAME = 'status';

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


	public function getRecordMissingURL(): AdminURLInterface
	{
		/* ... */
	}


	public function getRecordStatusURL(): AdminURLInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/Admin/Screens/Mode/ViewScreen.php`

```php
namespace Application\Countries\Admin\Screens\Mode;

use Application\Countries\Admin\Screens\Mode\View\StatusScreen as StatusScreen;
use Application\Countries\Admin\Traits\CountryModeInterface as CountryModeInterface;
use Application\Countries\Admin\Traits\CountryModeTrait as CountryModeTrait;
use Application\Countries\Rights\CountryScreenRights as CountryScreenRights;
use Application_Countries_Country as Application_Countries_Country;
use DBHelper\Admin\Screens\Mode\BaseRecordMode as BaseRecordMode;
use UI as UI;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

/**
 * @property Application_Countries_Country $record
 */
class ViewScreen extends BaseRecordMode implements CountryModeInterface
{
	use CountryModeTrait;

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


	public function getRecordMissingURL(): AdminURLInterface
	{
		/* ... */
	}


	public function getDefaultSubmode(): string
	{
		/* ... */
	}


	public function getDefaultSubscreenClass(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/Admin/Traits/CountryModeInterface.php`

```php
namespace Application\Countries\Admin\Traits;

use Application\Admin\ClassLoaderScreenInterface as ClassLoaderScreenInterface;
use Application\Interfaces\Admin\AdminModeInterface as AdminModeInterface;

interface CountryModeInterface extends AdminModeInterface, ClassLoaderScreenInterface
{
}


```
###  Path: `/src/classes/Application/Countries/Admin/Traits/CountryModeTrait.php`

```php
namespace Application\Countries\Admin\Traits;

use Application\AppFactory as AppFactory;
use Application\Countries\Admin\Screens\CountriesArea as CountriesArea;
use Application_Countries as Application_Countries;

trait CountryModeTrait
{
	public function getParentScreenClass(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/Admin/Traits/CountryRequestInterface.php`

```php
namespace Application\Countries\Admin\Traits;

use Application\Countries\Admin\CountryRequestType as CountryRequestType;
use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;

interface CountryRequestInterface extends AdminScreenInterface
{
	public function getCountryRequest(): CountryRequestType;
}


```
###  Path: `/src/classes/Application/Countries/Admin/Traits/CountryRequestTrait.php`

```php
namespace Application\Countries\Admin\Traits;

use Application\Countries\Admin\CountryRequestType as CountryRequestType;

/**
 * @see CountryRequestInterface
 */
trait CountryRequestTrait
{
	public function getCountryRequest(): CountryRequestType
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/Admin/Traits/CountryViewInterface.php`

```php
namespace Application\Countries\Admin\Traits;

use Application\Admin\ClassLoaderScreenInterface as ClassLoaderScreenInterface;
use Application\Interfaces\Admin\AdminSubmodeInterface as AdminSubmodeInterface;

interface CountryViewInterface extends AdminSubmodeInterface, ClassLoaderScreenInterface
{
}


```
###  Path: `/src/classes/Application/Countries/Admin/Traits/CountryViewTrait.php`

```php
namespace Application\Countries\Admin\Traits;

use Application\Countries\Admin\Screens\Mode\ViewScreen as ViewScreen;

trait CountryViewTrait
{
	public function getParentScreenClass(): string
	{
		/* ... */
	}
}


```
---
**File Statistics**
- **Size**: 20.54 KB
- **Lines**: 940
File: `modules/countries/architecture-admin.md`
