# API Clients - API Keys Architecture (Public API)
_SOURCE: APIKeysCollection, APIKeyRecord, APIKeyFilterCriteria, APIKeyFilterSettings, APIKeyRecordSettings, APIKeyException, APIKeyMethods, APIKeyMethodInterface, APIKeyMethodTrait, APIKeyParam, APIKeyHandler_
# APIKeysCollection, APIKeyRecord, APIKeyFilterCriteria, APIKeyFilterSettings, APIKeyRecordSettings, APIKeyException, APIKeyMethods, APIKeyMethodInterface, APIKeyMethodTrait, APIKeyParam, APIKeyHandler
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── API/
                └── Clients/
                    └── API/
                        ├── APIKeyMethodInterface.php
                        ├── APIKeyMethodTrait.php
                        ├── Params/
                        │   └── APIKeyHandler.php
                        │   └── APIKeyParam.php
                    └── Keys/
                        └── APIKeyException.php
                        └── APIKeyFilterCriteria.php
                        └── APIKeyFilterSettings.php
                        └── APIKeyMethods.php
                        └── APIKeyRecord.php
                        └── APIKeyRecordSettings.php
                        └── APIKeysCollection.php

```
###  Path: `/src/classes/Application/API/Clients/API/APIKeyMethodInterface.php`

```php
namespace Application\API\Clients\API;

use Application\API\APIMethodInterface as APIMethodInterface;
use Application\API\BaseMethods\BaseAPIMethod as BaseAPIMethod;
use Application\API\Clients\API\Params\APIKeyHandler as APIKeyHandler;

/**
 * Interface for API methods that require an API Key.
 *
 * > NOTE: The API Key parameter is always required and cannot be made optional.
 * > Additionally, it is automatically registered as soon as an API method
 * > implements this interface (see {@see BaseAPIMethod::initReservedParams()}).
 *
 * @package API Clients
 * @subpackage API Methods
 *
 * @see APIKeyMethodTrait
 */
interface APIKeyMethodInterface extends APIMethodInterface
{
	public const API_KEY_PARAM_NAME = 'apiKey';

	public function manageParamAPIKey(): APIKeyHandler;
}


```
###  Path: `/src/classes/Application/API/Clients/API/APIKeyMethodTrait.php`

```php
namespace Application\API\Clients\API;

use Application\API\Clients\API\Params\APIKeyHandler as APIKeyHandler;

/**
 * @see APIKeyMethodInterface
 */
trait APIKeyMethodTrait
{
	final public function manageParamAPIKey(): APIKeyHandler
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Clients/API/Params/APIKeyHandler.php`

```php
namespace Application\API\Clients\API\Params;

use Application\API\Clients\Keys\APIKeyRecord as APIKeyRecord;
use Application\API\Parameters\Handlers\BaseParamHandler as BaseParamHandler;

/**
 * Handler for the API Key parameter: Utility class that
 * handles registration, selection, and resolution of the
 * API Key parameter {@see APIKeyParam}.
 *
 * @package API Clients
 * @subpackage API Parameters
 *
 * @method APIKeyParam register()
 * @method APIKeyParam|null getParam()
 * @method APIKeyRecord requireValue()
 */
class APIKeyHandler extends BaseParamHandler
{
	public function selectValue(mixed $value): self
	{
		/* ... */
	}


	public function selectKey(APIKeyRecord $key): self
	{
		/* ... */
	}


	public function getKey(): ?APIKeyRecord
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Clients/API/Params/APIKeyParam.php`

```php
namespace Application\API\Clients\API\Params;

use AppUtils\RequestHelper as RequestHelper;
use Application\API\Clients\API\APIKeyMethodInterface as APIKeyMethodInterface;
use Application\API\Clients\APIClientException as APIClientException;
use Application\API\Clients\Keys\APIKeyRecord as APIKeyRecord;
use Application\API\Parameters\Flavors\APIHeaderParameterInterface as APIHeaderParameterInterface;
use Application\API\Parameters\Flavors\APIHeaderParameterTrait as APIHeaderParameterTrait;
use Application\API\Parameters\Flavors\RequiredOnlyParamInterface as RequiredOnlyParamInterface;
use Application\API\Parameters\Flavors\RequiredOnlyParamTrait as RequiredOnlyParamTrait;
use Application\API\Parameters\Type\StringParameter as StringParameter;
use Application\AppFactory as AppFactory;
use Connectors\Headers\HTTPHeadersBasket as HTTPHeadersBasket;

/**
 * API parameter used to specify the API Key for authentication.
 *
 * @package API
 * @subpackage Clients
 */
class APIKeyParam extends StringParameter implements APIHeaderParameterInterface, RequiredOnlyParamInterface
{
	use APIHeaderParameterTrait;
	use RequiredOnlyParamTrait;

	public function getHeaderExample(): string
	{
		/* ... */
	}


	public function getHeaderValue(): ?string
	{
		/* ... */
	}


	public function getKey(): ?APIKeyRecord
	{
		/* ... */
	}


	/**
	 * Like {@see self::getKey}, but always returns a value and
	 * throws an exception if no valid key is found.
	 *
	 * @return APIKeyRecord
	 * @throws APIClientException
	 */
	public function requireKey(): APIKeyRecord
	{
		/* ... */
	}


	public function injectHeaderForValue(HTTPHeadersBasket $headers, string $value): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Clients/Keys/APIKeyException.php`

```php
namespace Application\API\Clients\Keys;

use Application\API\Clients\APIClientException as APIClientException;

class APIKeyException extends APIClientException
{
	public const API_KEY_PARAM_CANNOT_BE_OPTIONAL = 187401;
}


```
###  Path: `/src/classes/Application/API/Clients/Keys/APIKeyFilterCriteria.php`

```php
namespace Application\API\Clients\Keys;

use DBHelper_BaseFilterCriteria as DBHelper_BaseFilterCriteria;
use DBHelper_StatementBuilder_ValuesContainer as DBHelper_StatementBuilder_ValuesContainer;

/**
 * @package API
 * @subpackage API Keys
 */
class APIKeyFilterCriteria extends DBHelper_BaseFilterCriteria
{
}


```
###  Path: `/src/classes/Application/API/Clients/Keys/APIKeyFilterSettings.php`

```php
namespace Application\API\Clients\Keys;

use DBHelper_BaseFilterSettings as DBHelper_BaseFilterSettings;

/**
 * @package API
 * @subpackage API Keys
 */
class APIKeyFilterSettings extends DBHelper_BaseFilterSettings
{
	public const SETTING_SEARCH = 'search';
}


```
###  Path: `/src/classes/Application/API/Clients/Keys/APIKeyMethods.php`

```php
namespace Application\API\Clients\Keys;

use Application\API\APIManager as APIManager;
use DBHelper as DBHelper;

/**
 * Handles the names of API methods granted to an API key.
 * This can be accessed via {@see APIKeyRecord::getMethods()}.
 *
 * @package API
 * @subpackage API Keys
 */
class APIKeyMethods
{
	public const TABLE_NAME = 'api_key_methods';
	public const COL_API_KEY_ID = 'api_key_id';
	public const COL_API_CLIENT_ID = 'api_client_id';
	public const COL_METHOD_NAME = 'method_name';

	/**
	 * Grants all available methods to the API key.
	 *
	 * > NOTE: This will clear any individually granted methods
	 * > from the database.
	 *
	 * @return $this
	 */
	public function grantAll(): self
	{
		/* ... */
	}


	/**
	 * Whether all methods are granted to the API key.
	 * This differs from manually setting all methods
	 * in that it will automatically include any new methods
	 * added to the API in the future.
	 *
	 * @return bool
	 */
	public function areAllGranted(): bool
	{
		/* ... */
	}


	public function getMethodNames(): array
	{
		/* ... */
	}


	/**
	 * Gets a list of all method names available in the system.
	 * @return string[]
	 */
	public function getAvailableMethods(): array
	{
		/* ... */
	}


	/**
	 * @param string[] $methodNames
	 * @return $this
	 */
	public function addMethods(array $methodNames): self
	{
		/* ... */
	}


	public function addMethod(string $methodName): self
	{
		/* ... */
	}


	public function setMethods(array $methodNames): self
	{
		/* ... */
	}


	public function removeMethods(array $methodNames): self
	{
		/* ... */
	}


	public function removeMethod(string $methodName): self
	{
		/* ... */
	}


	public function hasMethod(string $methodName): bool
	{
		/* ... */
	}


	public function countMethods(): int
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Clients/Keys/APIKeyRecord.php`

```php
namespace Application\API\Clients\Keys;

use AppUtils\ClassHelper as ClassHelper;
use AppUtils\DateTimeHelper\DateIntervalExtended as DateIntervalExtended;
use AppUtils\Interfaces\StringableInterface as StringableInterface;
use AppUtils\Microtime as Microtime;
use Application\API\Admin\APIKeyURLs as APIKeyURLs;
use Application\API\Admin\APIScreenRights as APIScreenRights;
use Application\API\Clients\APIClientRecord as APIClientRecord;
use Application\AppFactory as AppFactory;
use Application\Application as Application;
use Application_User as Application_User;
use Application_Users_User as Application_Users_User;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use DBHelper_BaseRecord as DBHelper_BaseRecord;

/**
 * @package API
 * @subpackage API Keys
 *
 * @method APIKeysCollection getCollection()
 */
class APIKeyRecord extends DBHelper_BaseRecord
{
	public function getClientID(): int
	{
		/* ... */
	}


	public function getPseudoUserID(): int
	{
		/* ... */
	}


	public function getPseudoUser(): Application_User
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	public function getLabelLinked(): string
	{
		/* ... */
	}


	public function setLabel(string|StringableInterface $label): self
	{
		/* ... */
	}


	public function getDateCreated(): Microtime
	{
		/* ... */
	}


	public function getCreatedByID(): int
	{
		/* ... */
	}


	public function getCreatedBy(): Application_Users_User
	{
		/* ... */
	}


	public function isExpired(): bool
	{
		/* ... */
	}


	public function areAllMethodsGranted(): bool
	{
		/* ... */
	}


	public function setGrantAll(bool $grant): self
	{
		/* ... */
	}


	/**
	 * Gets the API method manager for this API key,
	 * which can be used to manage which methods are
	 * granted to this key.
	 *
	 * @return APIKeyMethods
	 */
	public function getMethods(): APIKeyMethods
	{
		/* ... */
	}


	public function updateLastUsed(): self
	{
		/* ... */
	}


	public function getLastUsed(): ?Microtime
	{
		/* ... */
	}


	public function getUsageCount(): int
	{
		/* ... */
	}


	public function getLastUsedDate(): ?Microtime
	{
		/* ... */
	}


	public function getExpiryDate(): ?Microtime
	{
		/* ... */
	}


	public function resolveExpiryDate(): ?Microtime
	{
		/* ... */
	}


	public function getExpiryDelay(): ?DateIntervalExtended
	{
		/* ... */
	}


	public function getAPIKey(): string
	{
		/* ... */
	}


	public function getClient(): APIClientRecord
	{
		/* ... */
	}


	/**
	 * @return APIClientRecord
	 */
	public function getParentRecord(): DBHelperRecordInterface
	{
		/* ... */
	}


	public function adminURL(): APIKeyURLs
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Clients/Keys/APIKeyRecordSettings.php`

```php
namespace Application\API\Clients\Keys;

use AppUtils\DateTimeHelper\DurationStringInfo as DurationStringInfo;
use Application\API\Admin\APIScreenRights as APIScreenRights;
use Application\API\Clients\APIClientRecord as APIClientRecord;
use Application\AppFactory as AppFactory;
use Application_Formable_RecordSettings_Extended as Application_Formable_RecordSettings_Extended;
use Application_Formable_RecordSettings_Setting as Application_Formable_RecordSettings_Setting;
use Application_Formable_RecordSettings_ValueSet as Application_Formable_RecordSettings_ValueSet;
use Application_Interfaces_Formable as Application_Interfaces_Formable;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use HTML_QuickForm2_Node as HTML_QuickForm2_Node;
use HTML_QuickForm2_Rule_Callback as HTML_QuickForm2_Rule_Callback;
use UI as UI;
use UI\CSSClasses as CSSClasses;

/**
 * @package API
 * @subpackage API Keys
 */
class APIKeyRecordSettings extends Application_Formable_RecordSettings_Extended
{
	public const SETTING_LABEL = 'label';
	public const SETTING_EXPIRY_DELAY = 'expiry_delay';
	public const SETTING_EXPIRY_DATE = 'expiry_date';
	public const SETTING_PSEUDO_USER = 'pseudo_user';
	public const SETTING_COMMENTS = 'comments';
	public const SETTING_GRANT_ALL = 'grant_all';

	public function getDefaultSettingName(): string
	{
		/* ... */
	}


	public function isUserAllowedEditing(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Clients/Keys/APIKeysCollection.php`

```php
namespace Application\API\Clients\Keys;

use AppUtils\Microtime as Microtime;
use Application\API\Admin\APIKeyCollectionURLs as APIKeyCollectionURLs;
use Application\API\Clients\APIClientRecord as APIClientRecord;
use Application\API\Clients\APIClientsCollection as APIClientsCollection;
use Application\AppFactory as AppFactory;
use Application_User as Application_User;
use DBHelper\BaseCollection\BaseChildCollection as BaseChildCollection;

/**
 * API Keys Collection that handles the available keys for an API client.
 *
 * @package API
 * @subpackage API Keys
 *
 * @method APIClientsCollection getParentCollection()
 * @method APIClientRecord getParentRecord()
 * @method APIKeyFilterCriteria getFilterCriteria()
 * @method APIKeyFilterSettings getFilterSettings()
 * @method APIKeyRecord[] getAll()
 * @method APIKeyRecord getByID(int $record_id)
 * @method APIKeyRecord createNewRecord(array $data = array(), bool $silent = false, array $options = array())
 */
class APIKeysCollection extends BaseChildCollection
{
	public const RECORD_TYPE_NAME = 'api_key';
	public const TABLE_NAME = 'api_keys';
	public const PRIMARY_NAME = 'api_key_id';
	public const COL_API_CLIENT_ID = 'api_client_id';
	public const COL_API_KEY = 'api_key';
	public const COL_PSEUDO_USER_ID = 'pseudo_user';
	public const COL_LABEL = 'label';
	public const COL_COMMENTS = 'comments';
	public const COL_GRANT_ALL_METHODS = 'grant_all_methods';
	public const COL_DATE_CREATED = 'date_created';
	public const COL_CREATED_BY = 'created_by';
	public const COL_EXPIRY_DATE = 'expiry_date';
	public const COL_EXPIRY_DELAY = 'expiry_delay';
	public const COL_EXPIRED = 'expired';
	public const COL_LAST_USED = 'last_used';
	public const COL_USAGE_COUNT = 'usage_count';

	public function getRecordClassName(): string
	{
		/* ... */
	}


	public function getParentCollectionClass(): string
	{
		/* ... */
	}


	public function getRecordFiltersClassName(): string
	{
		/* ... */
	}


	public function getRecordFilterSettingsClassName(): string
	{
		/* ... */
	}


	public function getRecordDefaultSortKey(): string
	{
		/* ... */
	}


	public function getRecordSearchableColumns(): array
	{
		/* ... */
	}


	public function getRecordTableName(): string
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


	public function getCollectionLabel(): string
	{
		/* ... */
	}


	public function getRecordLabel(): string
	{
		/* ... */
	}


	public function getRecordProperties(): array
	{
		/* ... */
	}


	public function generateKey(): string
	{
		/* ... */
	}


	public function createNewAPIKey(string $label, Application_User $pseudoUser): APIKeyRecord
	{
		/* ... */
	}


	public function adminURLs(): APIKeyCollectionURLs
	{
		/* ... */
	}
}


```
---
**File Statistics**
- **Size**: 15.12 KB
- **Lines**: 722
File: `modules/api/clients/architecture-keys.md`
