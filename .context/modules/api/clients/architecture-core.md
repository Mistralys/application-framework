# API Clients - Core Architecture (Public API)
_SOURCE: APIClientsCollection, APIClientRecord, APIClientFilterCriteria, APIClientFilterSettings, APIClientRecordSettings, APIClientException_
# APIClientsCollection, APIClientRecord, APIClientFilterCriteria, APIClientFilterSettings, APIClientRecordSettings, APIClientException
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── API/
                └── Clients/
                    └── APIClientException.php
                    └── APIClientFilterCriteria.php
                    └── APIClientFilterSettings.php
                    └── APIClientRecord.php
                    └── APIClientRecordSettings.php
                    └── APIClientsCollection.php

```
###  Path: `/src/classes/Application/API/Clients/APIClientException.php`

```php
namespace Application\API\Clients;

use Application\Exception\ApplicationException as ApplicationException;

class APIClientException extends ApplicationException
{
	public const ERROR_API_KEY_MISSING_OR_INVALID = 187501;
}


```
###  Path: `/src/classes/Application/API/Clients/APIClientFilterCriteria.php`

```php
namespace Application\API\Clients;

use DBHelper_BaseFilterCriteria as DBHelper_BaseFilterCriteria;
use DBHelper_StatementBuilder_ValuesContainer as DBHelper_StatementBuilder_ValuesContainer;

class APIClientFilterCriteria extends DBHelper_BaseFilterCriteria
{
}


```
###  Path: `/src/classes/Application/API/Clients/APIClientFilterSettings.php`

```php
namespace Application\API\Clients;

use DBHelper_BaseFilterSettings as DBHelper_BaseFilterSettings;

class APIClientFilterSettings extends DBHelper_BaseFilterSettings
{
	const SETTING_SEARCH = 'search';
}


```
###  Path: `/src/classes/Application/API/Clients/APIClientRecord.php`

```php
namespace Application\API\Clients;

use AppUtils\ClassHelper as ClassHelper;
use AppUtils\Interfaces\StringableInterface as StringableInterface;
use AppUtils\Microtime as Microtime;
use Application\API\Admin\APIClientRecordURLs as APIClientRecordURLs;
use Application\API\Admin\APIScreenRights as APIScreenRights;
use Application\API\Clients\Keys\APIKeyRecord as APIKeyRecord;
use Application\API\Clients\Keys\APIKeysCollection as APIKeysCollection;
use Application\AppFactory as AppFactory;
use Application_User as Application_User;
use Application_Users_User as Application_Users_User;
use DBHelper as DBHelper;
use DBHelper_BaseRecord as DBHelper_BaseRecord;

class APIClientRecord extends DBHelper_BaseRecord
{
	public function createAPIKeys(): APIKeysCollection
	{
		/* ... */
	}


	public function createNewAPIKey(string $label, Application_User $pseudoUser): APIKeyRecord
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	public function setLabel(string|StringableInterface $label): self
	{
		/* ... */
	}


	public function getLabelLinked(): string
	{
		/* ... */
	}


	public function getForeignID(): string
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


	public function isActive(): bool
	{
		/* ... */
	}


	public function getComments(): string
	{
		/* ... */
	}


	public function adminURL(): APIClientRecordURLs
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Clients/APIClientRecordSettings.php`

```php
namespace Application\API\Clients;

use Application\AppFactory as AppFactory;
use Application_Formable_RecordSettings_Extended as Application_Formable_RecordSettings_Extended;
use Application_Formable_RecordSettings_Setting as Application_Formable_RecordSettings_Setting;
use Application_Formable_RecordSettings_ValueSet as Application_Formable_RecordSettings_ValueSet;
use Application_Interfaces_Formable as Application_Interfaces_Formable;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use HTML_QuickForm2_Node as HTML_QuickForm2_Node;
use UI as UI;
use UI\CSSClasses as CSSClasses;

class APIClientRecordSettings extends Application_Formable_RecordSettings_Extended
{
	public const SETTING_LABEL = 'label';
	const SETTING_FOREIGN_ID = 'foreign_id';

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
###  Path: `/src/classes/Application/API/Clients/APIClientsCollection.php`

```php
namespace Application\API\Clients;

use AppUtils\RegexHelper as RegexHelper;
use Application\API\Admin\APICollectionURLs as APICollectionURLs;
use Application\API\Clients\Keys\APIKeyRecord as APIKeyRecord;
use Application\API\Clients\Keys\APIKeysCollection as APIKeysCollection;
use Application\AppFactory as AppFactory;
use Application_User as Application_User;
use Application_Users_User as Application_Users_User;
use DBHelper as DBHelper;
use DBHelper_BaseCollection as DBHelper_BaseCollection;

/**
 * @package API
 * @subpackage Clients
 *
 * @method APIClientFilterCriteria getFilterCriteria()
 * @method APIClientFilterSettings getFilterSettings()
 * @method APIClientRecord[] getAll()
 * @method APIClientRecord getByID(int $record_id)
 * @method APIClientRecord createNewRecord(array $data = array(), bool $silent = false, array $options = array())
 */
class APIClientsCollection extends DBHelper_BaseCollection
{
	public const TABLE_NAME = 'api_clients';
	public const PRIMARY_NAME = 'api_client_id';
	public const RECORD_TYPE_NAME = 'api_client';
	public const COL_LABEL = 'label';
	public const COL_FOREIGN_ID = 'foreign_id';
	public const COL_DATE_CREATED = 'date_created';
	public const COL_CREATED_BY = 'created_by';
	public const COL_IS_ACTIVE = 'is_active';
	public const COL_COMMENTS = 'comments';

	public function getRecordClassName(): string
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


	/**
	 * Finds an API key across all clients, using the API key string.
	 *
	 * @param string $keyValue
	 * @return APIKeyRecord|null
	 */
	public function findAPIKey(string $keyValue): ?APIKeyRecord
	{
		/* ... */
	}


	public function createNewClient(
		string $label,
		string $foreignID,
		?string $comments = null,
		int|Application_User|Application_Users_User|null $createdBy = null,
	): APIClientRecord
	{
		/* ... */
	}


	public function adminURL(): APICollectionURLs
	{
		/* ... */
	}
}


```