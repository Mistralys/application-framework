# API Clients - Admin UI (Public API)
_SOURCE: APIClientsArea, ClientsListMode, CreateClientMode, ViewClientMode, ClientStatusSubmode, ClientSettingsSubmode, APIKeysSubmode, APIKeysListAction, CreateAPIKeyAction, APIKeyStatusAction, APIKeySettingsAction, URL classes, screen rights, request types, traits_
# APIClientsArea, ClientsListMode, CreateClientMode, ViewClientMode, ClientStatusSubmode, ClientSettingsSubmode, APIKeysSubmode, APIKeysListAction, CreateAPIKeyAction, APIKeyStatusAction, APIKeySettingsAction, URL classes, screen rights, request types, traits
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── API/
                └── Admin/
                    └── APIClientRecordURLs.php
                    └── APICollectionURLs.php
                    └── APIKeyCollectionURLs.php
                    └── APIKeyURLs.php
                    └── APIScreenRights.php
                    └── RequestTypes/
                        ├── APIClientRequestInterface.php
                        ├── APIClientRequestTrait.php
                        ├── APIClientRequestType.php
                    └── Screens/
                        ├── APIClientsArea.php
                        ├── Mode/
                        │   └── ClientsListMode.php
                        │   └── CreateClientMode.php
                        │   └── View/
                        │       ├── APIKeys/
                        │       │   ├── APIKeySettingsAction.php
                        │       │   ├── APIKeyStatusAction.php
                        │       │   ├── APIKeysListAction.php
                        │       │   ├── CreateAPIKeyAction.php
                        │       ├── APIKeysSubmode.php
                        │       ├── ClientSettingsSubmode.php
                        │       ├── ClientStatusSubmode.php
                        │   └── ViewClientMode.php
                    └── Traits/
                        └── APIKeyActionInterface.php
                        └── APIKeyActionRecordInterface.php
                        └── APIKeyActionRecordTrait.php
                        └── APIKeyActionTrait.php
                        └── ClientModeInterface.php
                        └── ClientModeTrait.php
                        └── ClientSubmodeInterface.php
                        └── ClientSubmodeTrait.php

```
###  Path: `/src/classes/Application/API/Admin/APIClientRecordURLs.php`

```php
namespace Application\API\Admin;

use Application\API\Admin\Screens\APIClientsArea as APIClientsArea;
use Application\API\Admin\Screens\Mode\View\APIKeysSubmode as APIKeysSubmode;
use Application\API\Admin\Screens\Mode\View\ClientSettingsSubmode as ClientSettingsSubmode;
use Application\API\Admin\Screens\Mode\ViewClientMode as ViewClientMode;
use Application\API\Clients\APIClientRecord as APIClientRecord;
use DBHelper\Admin\Traits\RecordStatusScreenInterface as RecordStatusScreenInterface;
use TestDriver\ClassFactory as ClassFactory;
use UI\AdminURLs\AdminURL as AdminURL;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

class APIClientRecordURLs
{
	public function status(): AdminURLInterface
	{
		/* ... */
	}


	public function base(): AdminURLInterface
	{
		/* ... */
	}


	public function settings(): AdminURLInterface
	{
		/* ... */
	}


	public function apiKeys(): AdminURLInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Admin/APICollectionURLs.php`

```php
namespace Application\API\Admin;

use Application\API\Admin\Screens\APIClientsArea as APIClientsArea;
use Application\API\Admin\Screens\Mode\ClientsListMode as ClientsListMode;
use Application\API\Admin\Screens\Mode\CreateClientMode as CreateClientMode;
use UI\AdminURLs\AdminURL as AdminURL;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

class APICollectionURLs
{
	public function list(): AdminURLInterface
	{
		/* ... */
	}


	public function base(): AdminURLInterface
	{
		/* ... */
	}


	public function create(): AdminURLInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Admin/APIKeyCollectionURLs.php`

```php
namespace Application\API\Admin;

use Application\API\Admin\Screens\Mode\View\APIKeys\APIKeysListAction as APIKeysListAction;
use Application\API\Admin\Screens\Mode\View\APIKeys\CreateAPIKeyAction as CreateAPIKeyAction;
use Application\API\Clients\Keys\APIKeysCollection as APIKeysCollection;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

class APIKeyCollectionURLs
{
	public function list(): AdminURLInterface
	{
		/* ... */
	}


	public function base(): AdminURLInterface
	{
		/* ... */
	}


	public function create(): AdminURLInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Admin/APIKeyURLs.php`

```php
namespace Application\API\Admin;

use Application\API\Admin\Screens\Mode\View\APIKeys\APIKeySettingsAction as APIKeySettingsAction;
use Application\API\Admin\Screens\Mode\View\APIKeys\APIKeyStatusAction as APIKeyStatusAction;
use Application\API\Clients\Keys\APIKeyRecord as APIKeyRecord;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

class APIKeyURLs
{
	public function status(): AdminURLInterface
	{
		/* ... */
	}


	public function base(): AdminURLInterface
	{
		/* ... */
	}


	public function settings(): AdminURLInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Admin/APIScreenRights.php`

```php
namespace Application\API\Admin;

use Application\API\User\APIRightsInterface as APIRightsInterface;

class APIScreenRights
{
	public const SCREEN_CLIENTS_AREA = APIRightsInterface::RIGHT_VIEW_API_CLIENTS;
	public const SCREEN_CLIENTS_LIST = APIRightsInterface::RIGHT_VIEW_API_CLIENTS;
	public const SCREEN_CLIENTS_LIST_MULTI_DELETE = APIRightsInterface::RIGHT_DELETE_API_CLIENTS;
	public const SCREEN_CLIENTS_CREATE = APIRightsInterface::RIGHT_CREATE_API_CLIENTS;
	public const SCREEN_CLIENTS_VIEW = APIRightsInterface::RIGHT_VIEW_API_CLIENTS;
	public const SCREEN_CLIENTS_VIEW_STATUS = APIRightsInterface::RIGHT_VIEW_API_CLIENTS;
	public const SCREEN_CLIENTS_SETTINGS = APIRightsInterface::RIGHT_VIEW_API_CLIENTS;
	public const SCREEN_CLIENTS_SETTINGS_EDIT = APIRightsInterface::RIGHT_EDIT_API_CLIENTS;
	public const SCREEN_API_KEYS = APIRightsInterface::RIGHT_VIEW_API_CLIENTS;
	public const SCREEN_API_KEYS_CREATE = APIRightsInterface::RIGHT_CREATE_API_CLIENTS;
	public const SCREEN_API_KEYS_STATUS = APIRightsInterface::RIGHT_VIEW_API_CLIENTS;
	public const SCREEN_API_KEYS_SETTINGS = APIRightsInterface::RIGHT_VIEW_API_CLIENTS;
	public const SCREEN_API_KEYS_SETTINGS_EDIT = APIRightsInterface::RIGHT_EDIT_API_CLIENTS;
	public const SCREEN_API_KEYS_LIST = APIRightsInterface::RIGHT_VIEW_API_CLIENTS;
}


```
###  Path: `/src/classes/Application/API/Admin/RequestTypes/APIClientRequestInterface.php`

```php
namespace Application\API\Admin\RequestTypes;

use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;

interface APIClientRequestInterface extends AdminScreenInterface
{
	public function getAPIClientRequest(): APIClientRequestType;
}


```
###  Path: `/src/classes/Application/API/Admin/RequestTypes/APIClientRequestTrait.php`

```php
namespace Application\API\Admin\RequestTypes;

trait APIClientRequestTrait
{
	public function getAPIClientRequest(): APIClientRequestType
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Admin/RequestTypes/APIClientRequestType.php`

```php
namespace Application\API\Admin\RequestTypes;

use Application\API\Clients\APIClientRecord as APIClientRecord;
use Application\API\Clients\APIClientsCollection as APIClientsCollection;
use Application\AppFactory as AppFactory;
use DBHelper\Admin\Requests\BaseDBRecordRequestType as BaseDBRecordRequestType;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

/**
 * Request type for API Client records, used to fetch an
 * API record based on the request.
 *
 * @package API
 * @subpackage Admin
 *
 * @method APIClientRecord|NULL getRecord()
 * @method APIClientRecord getRecordOrRedirect()
 * @method APIClientRecord requireRecord()
 */
class APIClientRequestType extends BaseDBRecordRequestType
{
	public function getCollection(): APIClientsCollection
	{
		/* ... */
	}


	public function getRecordMissingURL(): AdminURLInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Admin/Screens/APIClientsArea.php`

```php
namespace Application\API\Admin\Screens;

use Application\API\APIManager as APIManager;
use Application\API\Admin\APIScreenRights as APIScreenRights;
use Application\API\Admin\Screens\Mode\ClientsListMode as ClientsListMode;
use Application\Admin\BaseArea as BaseArea;
use UI as UI;
use UI_Icon as UI_Icon;

/**
 * Abstract base class for the API Clients area.
 *
 * @package API
 * @subpackage Admin
 */
class APIClientsArea extends BaseArea
{
	public const URL_NAME = 'api-clients';

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


	public function getNavigationIcon(): ?UI_Icon
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


	public function getRequiredRight(): string
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
}


```
###  Path: `/src/classes/Application/API/Admin/Screens/Mode/ClientsListMode.php`

```php
namespace Application\API\Admin\Screens\Mode;

use Application\API\Admin\APIScreenRights as APIScreenRights;
use Application\API\Admin\Traits\ClientModeInterface as ClientModeInterface;
use Application\API\Admin\Traits\ClientModeTrait as ClientModeTrait;
use Application\API\Clients\APIClientRecord as APIClientRecord;
use Application\API\Clients\APIClientsCollection as APIClientsCollection;
use DBHelper\Admin\Screens\Mode\BaseRecordListMode as BaseRecordListMode;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use DBHelper_BaseFilterCriteria_Record as DBHelper_BaseFilterCriteria_Record;
use UI as UI;
use UI_DataGrid_Action as UI_DataGrid_Action;
use UI_DataGrid_Entry as UI_DataGrid_Entry;

/**
 * Abstract base class for the API Clients overview screen.
 *
 * @package API
 * @subpackage Admin
 */
class ClientsListMode extends BaseRecordListMode implements ClientModeInterface
{
	use ClientModeTrait;

	public const URL_NAME = 'list';
	public const COL_LABEL = 'label';
	public const COL_FOREIGN_ID = 'foreign_id';
	public const COL_DATE_CREATED = 'date_created';
	public const COL_IS_ACTIVE = 'is_active';
	public const COL_ID = 'id';

	public function getURLName(): string
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


	public function getRequiredRight(): string
	{
		/* ... */
	}


	/**
	 * @return array<string, string>
	 */
	public function getFeatureRights(): array
	{
		/* ... */
	}


	public function getBackOrCancelURL(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Admin/Screens/Mode/CreateClientMode.php`

```php
namespace Application\API\Admin\Screens\Mode;

use Application\API\Admin\APIScreenRights as APIScreenRights;
use Application\API\Admin\Traits\ClientModeInterface as ClientModeInterface;
use Application\API\Admin\Traits\ClientModeTrait as ClientModeTrait;
use Application\API\Clients\APIClientRecord as APIClientRecord;
use Application\API\Clients\APIClientRecordSettings as APIClientRecordSettings;
use Application\API\Clients\APIClientsCollection as APIClientsCollection;
use Application\AppFactory as AppFactory;
use DBHelper\Admin\Screens\Mode\BaseRecordCreateMode as BaseRecordCreateMode;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

/**
 * Abstract base class for the API Client creation screen.
 *
 * @package API
 * @subpackage Admin
 */
class CreateClientMode extends BaseRecordCreateMode implements ClientModeInterface
{
	use ClientModeTrait;

	public const URL_NAME = 'create';

	public function getURLName(): string
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


	public function getRequiredRight(): string
	{
		/* ... */
	}


	public function getSettingsManager(): APIClientRecordSettings
	{
		/* ... */
	}


	public function getSuccessMessage(DBHelperRecordInterface $record): string
	{
		/* ... */
	}


	public function getSuccessURL(DBHelperRecordInterface $record): AdminURLInterface
	{
		/* ... */
	}


	public function getBackOrCancelURL(): string
	{
		/* ... */
	}


	public function getAbstract(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Admin/Screens/Mode/View/APIKeys/APIKeySettingsAction.php`

```php
namespace Application\API\Admin\Screens\Mode\View\APIKeys;

use Application\API\Admin\APIScreenRights as APIScreenRights;
use Application\API\Admin\RequestTypes\APIClientRequestTrait as APIClientRequestTrait;
use Application\API\Admin\Traits\APIKeyActionRecordInterface as APIKeyActionRecordInterface;
use Application\API\Admin\Traits\APIKeyActionRecordTrait as APIKeyActionRecordTrait;
use Application\API\Admin\Traits\APIKeyActionTrait as APIKeyActionTrait;
use Application\API\Clients\Keys\APIKeyRecordSettings as APIKeyRecordSettings;
use DBHelper\Admin\Screens\Action\BaseRecordSettingsAction as BaseRecordSettingsAction;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

class APIKeySettingsAction extends BaseRecordSettingsAction implements APIKeyActionRecordInterface
{
	use APIClientRequestTrait;
	use APIKeyActionTrait;
	use APIKeyActionRecordTrait;

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


	public function getFeatureRights(): array
	{
		/* ... */
	}


	public function getSettingsManager(): APIKeyRecordSettings
	{
		/* ... */
	}


	public function isEditable(): bool
	{
		/* ... */
	}


	public function getBackOrCancelURL(): AdminURLInterface
	{
		/* ... */
	}


	public function getSuccessMessage(DBHelperRecordInterface $record): string
	{
		/* ... */
	}


	public function isUserAllowedEditing(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Admin/Screens/Mode/View/APIKeys/APIKeyStatusAction.php`

```php
namespace Application\API\Admin\Screens\Mode\View\APIKeys;

use AppUtils\ClassHelper as ClassHelper;
use Application\API\Admin\APIScreenRights as APIScreenRights;
use Application\API\Admin\RequestTypes\APIClientRequestTrait as APIClientRequestTrait;
use Application\API\Admin\Traits\APIKeyActionRecordInterface as APIKeyActionRecordInterface;
use Application\API\Admin\Traits\APIKeyActionRecordTrait as APIKeyActionRecordTrait;
use Application\API\Admin\Traits\APIKeyActionTrait as APIKeyActionTrait;
use Application\API\Clients\Keys\APIKeyRecord as APIKeyRecord;
use DBHelper\Admin\Screens\Action\BaseRecordStatusAction as BaseRecordStatusAction;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;
use UI_PropertiesGrid as UI_PropertiesGrid;

class APIKeyStatusAction extends BaseRecordStatusAction implements APIKeyActionRecordInterface
{
	use APIClientRequestTrait;
	use APIKeyActionTrait;
	use APIKeyActionRecordTrait;

	public const URL_NAME = 'status';

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
}


```
###  Path: `/src/classes/Application/API/Admin/Screens/Mode/View/APIKeys/APIKeysListAction.php`

```php
namespace Application\API\Admin\Screens\Mode\View\APIKeys;

use AppUtils\ClassHelper as ClassHelper;
use AppUtils\ConvertHelper as ConvertHelper;
use AppUtils\Microtime as Microtime;
use Application\API\Admin\APIScreenRights as APIScreenRights;
use Application\API\Admin\RequestTypes\APIClientRequestTrait as APIClientRequestTrait;
use Application\API\Admin\Traits\APIKeyActionInterface as APIKeyActionInterface;
use Application\API\Admin\Traits\APIKeyActionTrait as APIKeyActionTrait;
use Application\API\Clients\Keys\APIKeyRecord as APIKeyRecord;
use DBHelper\Admin\Screens\Action\BaseRecordListAction as BaseRecordListAction;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use DBHelper_BaseFilterCriteria_Record as DBHelper_BaseFilterCriteria_Record;
use UI as UI;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

class APIKeysListAction extends BaseRecordListAction implements APIKeyActionInterface
{
	use APIClientRequestTrait;
	use APIKeyActionTrait;

	public const URL_NAME = 'list';
	public const COL_LABEL = 'label';
	public const COL_METHOD_COUNT = 'method_count';
	public const COL_LAST_ACCESSED = 'last_accessed';
	public const COL_USER = 'user';
	public const COL_CREATED = 'created';
	public const COL_EXPIRES = 'expires';

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


	public function getBackOrCancelURL(): AdminURLInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Admin/Screens/Mode/View/APIKeys/CreateAPIKeyAction.php`

```php
namespace Application\API\Admin\Screens\Mode\View\APIKeys;

use Application\API\Admin\RequestTypes\APIClientRequestTrait as APIClientRequestTrait;
use Application\API\Admin\Traits\APIKeyActionInterface as APIKeyActionInterface;
use Application\API\Admin\Traits\APIKeyActionTrait as APIKeyActionTrait;
use Application\API\Clients\Keys\APIKeyRecordSettings as APIKeyRecordSettings;
use DBHelper\Admin\Screens\Action\BaseRecordCreateAction as BaseRecordCreateAction;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use UI as UI;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

class CreateAPIKeyAction extends BaseRecordCreateAction implements APIKeyActionInterface
{
	use APIClientRequestTrait;
	use APIKeyActionTrait;

	public const URL_NAME = 'create';

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


	public function getSettingsManager(): APIKeyRecordSettings
	{
		/* ... */
	}


	public function getSuccessMessage(DBHelperRecordInterface $record): string
	{
		/* ... */
	}


	public function getBackOrCancelURL(): AdminURLInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Admin/Screens/Mode/View/APIKeysSubmode.php`

```php
namespace Application\API\Admin\Screens\Mode\View;

use Application\API\Admin\APIScreenRights as APIScreenRights;
use Application\API\Admin\RequestTypes\APIClientRequestTrait as APIClientRequestTrait;
use Application\API\Admin\Screens\Mode\View\APIKeys\APIKeysListAction as APIKeysListAction;
use Application\API\Admin\Traits\ClientSubmodeInterface as ClientSubmodeInterface;
use Application\API\Admin\Traits\ClientSubmodeTrait as ClientSubmodeTrait;
use DBHelper\Admin\Screens\Submode\BaseRecordSubmode as BaseRecordSubmode;

class APIKeysSubmode extends BaseRecordSubmode implements ClientSubmodeInterface
{
	use ClientSubmodeTrait;
	use APIClientRequestTrait;

	public const URL_NAME = 'api_keys';

	public function getURLName(): string
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


	public function getRequiredRight(): string
	{
		/* ... */
	}


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
###  Path: `/src/classes/Application/API/Admin/Screens/Mode/View/ClientSettingsSubmode.php`

```php
namespace Application\API\Admin\Screens\Mode\View;

use Application\API\Admin\APIScreenRights as APIScreenRights;
use Application\API\Admin\RequestTypes\APIClientRequestTrait as APIClientRequestTrait;
use Application\API\Admin\Traits\ClientSubmodeInterface as ClientSubmodeInterface;
use Application\API\Admin\Traits\ClientSubmodeTrait as ClientSubmodeTrait;
use Application\API\Clients\APIClientRecordSettings as APIClientRecordSettings;
use DBHelper\Admin\Screens\Submode\BaseRecordSettingsSubmode as BaseRecordSettingsSubmode;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;

class ClientSettingsSubmode extends BaseRecordSettingsSubmode implements ClientSubmodeInterface
{
	use ClientSubmodeTrait;
	use APIClientRequestTrait;

	public const URL_NAME = 'settings';

	public function getURLName(): string
	{
		/* ... */
	}


	public function getTitle(): string
	{
		/* ... */
	}


	public function getSettingsManager(): APIClientRecordSettings
	{
		/* ... */
	}


	public function getRequiredRight(): string
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
###  Path: `/src/classes/Application/API/Admin/Screens/Mode/View/ClientStatusSubmode.php`

```php
namespace Application\API\Admin\Screens\Mode\View;

use AppUtils\ClassHelper as ClassHelper;
use Application\API\Admin\APIScreenRights as APIScreenRights;
use Application\API\Admin\RequestTypes\APIClientRequestTrait as APIClientRequestTrait;
use Application\API\Admin\Traits\ClientSubmodeInterface as ClientSubmodeInterface;
use Application\API\Admin\Traits\ClientSubmodeTrait as ClientSubmodeTrait;
use Application\API\Clients\APIClientRecord as APIClientRecord;
use DBHelper\Admin\Screens\Submode\BaseRecordStatusSubmode as BaseRecordStatusSubmode;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;
use UI_PropertiesGrid as UI_PropertiesGrid;

class ClientStatusSubmode extends BaseRecordStatusSubmode implements ClientSubmodeInterface
{
	use ClientSubmodeTrait;
	use APIClientRequestTrait;

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
###  Path: `/src/classes/Application/API/Admin/Screens/Mode/ViewClientMode.php`

```php
namespace Application\API\Admin\Screens\Mode;

use AppUtils\ClassHelper as ClassHelper;
use Application\API\Admin\APIScreenRights as APIScreenRights;
use Application\API\Admin\Screens\Mode\View\ClientStatusSubmode as ClientStatusSubmode;
use Application\API\Admin\Traits\ClientModeInterface as ClientModeInterface;
use Application\API\Admin\Traits\ClientModeTrait as ClientModeTrait;
use Application\API\Clients\APIClientRecord as APIClientRecord;
use DBHelper\Admin\Screens\Mode\BaseRecordMode as BaseRecordMode;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use UI as UI;
use UI\AdminURLs\AdminURL as AdminURL;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

class ViewClientMode extends BaseRecordMode implements ClientModeInterface
{
	use ClientModeTrait;

	public const URL_NAME = 'view';

	public function getURLName(): string
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


	public function getRecord(): APIClientRecord
	{
		/* ... */
	}


	public function getRequiredRight(): string
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
###  Path: `/src/classes/Application/API/Admin/Traits/APIKeyActionInterface.php`

```php
namespace Application\API\Admin\Traits;

use Application\API\Admin\RequestTypes\APIClientRequestInterface as APIClientRequestInterface;
use Application\Admin\ClassLoaderScreenInterface as ClassLoaderScreenInterface;
use Application\Interfaces\Admin\AdminActionInterface as AdminActionInterface;
use Application\Interfaces\Admin\MissingRecordInterface as MissingRecordInterface;

interface APIKeyActionInterface extends APIClientRequestInterface, AdminActionInterface, MissingRecordInterface, ClassLoaderScreenInterface
{
}


```
###  Path: `/src/classes/Application/API/Admin/Traits/APIKeyActionRecordInterface.php`

```php
namespace Application\API\Admin\Traits;

use Application\API\Admin\RequestTypes\APIClientRequestInterface as APIClientRequestInterface;
use Application\API\Clients\Keys\APIKeyRecord as APIKeyRecord;
use Application\API\Clients\Keys\APIKeysCollection as APIKeysCollection;
use Application\Interfaces\Admin\AdminActionInterface as AdminActionInterface;
use Application\Interfaces\Admin\MissingRecordInterface as MissingRecordInterface;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

interface APIKeyActionRecordInterface extends APIKeyActionInterface
{
}


```
###  Path: `/src/classes/Application/API/Admin/Traits/APIKeyActionRecordTrait.php`

```php
namespace Application\API\Admin\Traits;

use AppUtils\ClassHelper as ClassHelper;
use Application\API\Admin\Screens\Mode\View\APIKeys\APIKeySettingsAction as APIKeySettingsAction;
use Application\API\Admin\Screens\Mode\View\APIKeys\APIKeyStatusAction as APIKeyStatusAction;
use Application\API\Clients\Keys\APIKeyRecord as APIKeyRecord;
use Application\API\Clients\Keys\APIKeysCollection as APIKeysCollection;
use UI as UI;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

trait APIKeyActionRecordTrait
{
	public function getCollection(): APIKeysCollection
	{
		/* ... */
	}


	public function getRecord(): APIKeyRecord
	{
		/* ... */
	}


	public function getRecordStatusURL(): string|AdminURLInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Admin/Traits/APIKeyActionTrait.php`

```php
namespace Application\API\Admin\Traits;

use Application\API\Admin\Screens\Mode\View\APIKeysSubmode as APIKeysSubmode;
use Application\API\Clients\Keys\APIKeysCollection as APIKeysCollection;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

trait APIKeyActionTrait
{
	public function getParentScreenClass(): string
	{
		/* ... */
	}


	public function createCollection(): APIKeysCollection
	{
		/* ... */
	}


	public function getRecordMissingURL(): AdminURLInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Admin/Traits/ClientModeInterface.php`

```php
namespace Application\API\Admin\Traits;

use Application\Admin\ClassLoaderScreenInterface as ClassLoaderScreenInterface;
use Application\Interfaces\Admin\AdminModeInterface as AdminModeInterface;

interface ClientModeInterface extends AdminModeInterface, ClassLoaderScreenInterface
{
}


```
###  Path: `/src/classes/Application/API/Admin/Traits/ClientModeTrait.php`

```php
namespace Application\API\Admin\Traits;

use Application\API\Admin\Screens\APIClientsArea as APIClientsArea;
use Application\API\Clients\APIClientsCollection as APIClientsCollection;
use Application\AppFactory as AppFactory;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

trait ClientModeTrait
{
	public function createCollection(): APIClientsCollection
	{
		/* ... */
	}


	public function getParentScreenClass(): string
	{
		/* ... */
	}


	public function getRecordMissingURL(): AdminURLInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Admin/Traits/ClientSubmodeInterface.php`

```php
namespace Application\API\Admin\Traits;

use Application\API\Admin\RequestTypes\APIClientRequestInterface as APIClientRequestInterface;
use Application\Admin\ClassLoaderScreenInterface as ClassLoaderScreenInterface;
use Application\Interfaces\Admin\AdminSubmodeInterface as AdminSubmodeInterface;

interface ClientSubmodeInterface extends AdminSubmodeInterface, APIClientRequestInterface, ClassLoaderScreenInterface
{
}


```
###  Path: `/src/classes/Application/API/Admin/Traits/ClientSubmodeTrait.php`

```php
namespace Application\API\Admin\Traits;

use Application\API\Admin\Screens\Mode\ViewClientMode as ViewClientMode;
use Application\API\Clients\APIClientsCollection as APIClientsCollection;
use Application\AppFactory as AppFactory;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

trait ClientSubmodeTrait
{
	public function createCollection(): APIClientsCollection
	{
		/* ... */
	}


	public function getParentScreenClass(): ?string
	{
		/* ... */
	}


	public function getRecordMissingURL(): AdminURLInterface
	{
		/* ... */
	}
}


```
---
**File Statistics**
- **Size**: 28.71 KB
- **Lines**: 1203
File: `modules/api/clients/architecture-ui.md`
