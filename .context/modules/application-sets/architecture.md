# Application Sets - Architecture
_SOURCE: Public Class Signatures_
# Public Class Signatures
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── AppSets/
                └── AppSet.php
                └── AppSetSettingsManager.php
                └── AppSetsCollection.php
                └── AppSetsException.php
                └── AppSetsFilterCriteria.php
                └── AppSetsFilterSettings.php
                └── DefaultAppSet.php

```
###  Path: `/src/classes/Application/AppSets/AppSet.php`

```php
namespace Application\AppSets;

use AppUtils\ConvertHelper as ConvertHelper;
use Application\Admin\Index\AdminScreenIndex as AdminScreenIndex;
use Application\Admin\Welcome\Screens\WelcomeArea as WelcomeArea;
use Application\AppFactory as AppFactory;
use Application\AppSets\Admin\AppSetAdminURLs as AppSetAdminURLs;
use Application\Interfaces\Admin\AdminAreaInterface as AdminAreaInterface;
use Application\MarkdownRenderer\MarkdownRenderer as MarkdownRenderer;
use Application\Sets\Admin\AppSetScreenRights as AppSetScreenRights;
use Application_Driver as Application_Driver;
use Application_Formable as Application_Formable;
use DBHelper_BaseRecord as DBHelper_BaseRecord;
use UI as UI;

/**
 * Container for a single application set. Provides an API
 * for accessing set information and manipulating it. Use the
 * sets collection's {@link AppSetsCollection::getByID()} method
 * to retrieve a specific set.
 *
 * @package Application
 * @subpackage Appsets
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class AppSet extends DBHelper_BaseRecord
{
	public const KEY_DEFAULT_URL_NAME = 'defaultArea';
	public const KEY_ID = 'id';
	public const SETTING_ID = 'id';
	public const KEY_ENABLED = 'enabled';

	public function isDefault(): bool
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	public function getAlias(): string
	{
		/* ... */
	}


	public function getDescription(): string
	{
		/* ... */
	}


	/**
	 * The default area that should be opened when this set is active.
	 * @return AdminAreaInterface
	 */
	public function getDefaultArea(): AdminAreaInterface
	{
		/* ... */
	}


	public function getDefaultURLName(): string
	{
		/* ... */
	}


	public static function createSettingsForm(Application_Formable $formable, ?AppSet $set = null): void
	{
		/* ... */
	}


	/**
	 * Enables the specified area for the set.
	 *
	 * @param AdminAreaInterface $area
	 * @return $this
	 */
	public function enableArea(AdminAreaInterface $area): self
	{
		/* ... */
	}


	/**
	 * Gets the stored list of enabled area URL names,
	 * filtering out any that do not exist, or may have
	 * been promoted to core area.
	 *
	 * @return string[] List of non-core valid area URL names.
	 */
	public function getEnabledAreaURLNames(): array
	{
		/* ... */
	}


	/**
	 * Retrieves all areas enabled for this set.
	 * @param bool $includeCore
	 * @return AdminAreaInterface[]
	 */
	public function getEnabledAreas(bool $includeCore = true): array
	{
		/* ... */
	}


	/**
	 * Retrieves the URL names of all areas that
	 * are currently enabled.
	 *
	 * @return string[]
	 */
	public function getEnabledURLNames(): array
	{
		/* ... */
	}


	/**
	 * Retrieves the human-readable names (titles) of all
	 * areas that are currently enabled.
	 *
	 * @return string[]
	 */
	public function getEnabledAreaLabels(): array
	{
		/* ... */
	}


	/**
	 * Checks whether the specified area is enabled for this
	 * application set. Core areas are always enabled.
	 *
	 * @param AdminAreaInterface $area
	 * @return boolean
	 */
	public function isAreaEnabled(AdminAreaInterface $area): bool
	{
		/* ... */
	}


	/**
	 * Enables a collection of areas at once.
	 * @param AdminAreaInterface[] $areas
	 * @return $this
	 */
	public function enableAreas(array $areas): self
	{
		/* ... */
	}


	/**
	 * Whether this is the currently active application set.
	 * @return boolean
	 */
	public function isActive(): bool
	{
		/* ... */
	}


	public function areAllAreasEnabled(): bool
	{
		/* ... */
	}


	public function adminURL(): AppSetAdminURLs
	{
		/* ... */
	}


	public function getLabelLinked(): string
	{
		/* ... */
	}


	public function getActiveBadge(): string
	{
		/* ... */
	}


	public function renderDocumentation(): string
	{
		/* ... */
	}


	public function hasDocumentation(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/AppSets/AppSetSettingsManager.php`

```php
namespace Application\AppSets;

use Application\Admin\Welcome\Screens\WelcomeArea as WelcomeArea;
use Application\AppFactory as AppFactory;
use Application\Interfaces\Admin\AdminAreaInterface as AdminAreaInterface;
use Application_Formable_RecordSettings_Extended as Application_Formable_RecordSettings_Extended;
use Application_Formable_RecordSettings_Setting as Application_Formable_RecordSettings_Setting;
use Application_Formable_RecordSettings_ValueSet as Application_Formable_RecordSettings_ValueSet;
use Application_Interfaces_Formable as Application_Interfaces_Formable;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use HTML_QuickForm2_Node as HTML_QuickForm2_Node;
use UI as UI;
use UI\CSSClasses as CSSClasses;

final class AppSetSettingsManager extends Application_Formable_RecordSettings_Extended
{
	public const SETTING_LABEL = 'label';
	public const SETTING_ALIAS = 'alias';
	public const SETTING_DEFAULT_AREA = 'defaultArea';
	public const SETTING_ENABLED_AREAS = 'enabledAreas';

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
###  Path: `/src/classes/Application/AppSets/AppSetsCollection.php`

```php
namespace Application\AppSets;

use AppUtils\ClassHelper as ClassHelper;
use AppUtils\RegexHelper as RegexHelper;
use Application\AppFactory as AppFactory;
use Application\Interfaces\Admin\AdminAreaInterface as AdminAreaInterface;
use Application\Sets\AppSetsException as AppSetsException;
use Application_Driver as Application_Driver;
use DBHelper as DBHelper;
use DBHelper\Attributes\UncachedQuery as UncachedQuery;
use DBHelper_BaseCollection as DBHelper_BaseCollection;
use DBHelper_StatementBuilder as DBHelper_StatementBuilder;
use UI as UI;
use UI_Page_Sidebar as UI_Page_Sidebar;

/**
 * Helper class used to manage application sets: these
 * can be used to create different application UI
 * environments with only specific administration areas.
 *
 * @package Application
 * @subpackage Sets
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @method AppSet[] getAll()
 */
final class AppSetsCollection extends DBHelper_BaseCollection
{
	public const PRIMARY_NAME = 'app_set_id';
	public const TABLE_NAME = 'app_sets';
	public const COL_ALIAS = 'alias';
	public const COL_IS_ACTIVE = 'is_active';
	public const COL_LABEL = 'label';
	public const COL_DESCRIPTION = 'description';
	public const COL_DEFAULT_URL_NAME = 'default_url_name';
	public const COL_URL_NAMES = 'enabled_url_names';
	public const DEFAULT_ID = -690;
	public const RECORD_TYPE = 'application_set';
	public const REGEX_ALIAS = RegexHelper::REGEX_ALIAS_CAPITALS;
	public const REQUEST_PRIMARY_NAME = 'appSet';
	public const DEFAULT_ALIAS = '__default';

	/**
	 * @return AppSetsCollection
	 */
	public static function getInstance(): self
	{
		/* ... */
	}


	public function getAdminListURL(array $params = []): string
	{
		/* ... */
	}


	public function getAdminCreateURL(array $params = []): string
	{
		/* ... */
	}


	public static function statement(string $template): DBHelper_StatementBuilder
	{
		/* ... */
	}


	#[UncachedQuery]
	public function aliasExists(string $alias): bool
	{
		/* ... */
	}


	#[UncachedQuery]
	public function getIDByAlias(string $alias): ?int
	{
		/* ... */
	}


	public function createNewRecord(array $data = [], bool $silent = false, array $options = []): AppSet
	{
		/* ... */
	}


	/**
	 * Creates a new application set and returns the instance.
	 *
	 * @param string $alias
	 * @param string $label
	 * @param AdminAreaInterface $defaultArea
	 * @param AdminAreaInterface[] $enabledAreas
	 * @return AppSet
	 * @throws AppSetsException
	 */
	public function createNew(
		string $alias,
		string $label,
		AdminAreaInterface $defaultArea,
		array $enabledAreas = [],
	): AppSet
	{
		/* ... */
	}


	public function idExists(int $record_id): bool
	{
		/* ... */
	}


	public function getByID(int $record_id): AppSet|DefaultAppSet
	{
		/* ... */
	}


	/**
	 * Gets the default application set in which all admin
	 * areas are enabled.
	 *
	 * @return DefaultAppSet
	 */
	public function getDefaultSet(): DefaultAppSet
	{
		/* ... */
	}


	/**
	 * Saves all application sets to the configuration file.
	 *
	 * @deprecated
	 */
	public function save(): void
	{
		/* ... */
	}


	/**
	 * Renames the ID of a set. Called by a set when
	 * it is renamed, do not call this manually.
	 *
	 * @param AppSet $set
	 * @param string $newID
	 * @throws AppSetsException
	 */
	public function handle_renameSet(AppSet $set, string $newID): void
	{
		/* ... */
	}


	public function getActiveID(): int
	{
		/* ... */
	}


	public function getActive(): AppSet|DefaultAppSet
	{
		/* ... */
	}


	public function injectCoreAreas(UI_Page_Sidebar $sidebar): void
	{
		/* ... */
	}


	/**
	 * Marks the given application set as active.
	 *
	 * > NOTE: This is intended to be called and
	 * > the request terminated. It does not update
	 * > in-memory references of loaded sets.
	 *
	 * @param AppSet $set
	 * @return void
	 */
	public function makeSetActive(AppSet $set): void
	{
		/* ... */
	}


	public function getRecordClassName(): string
	{
		/* ... */
	}


	public function getRecordRequestPrimaryName(): string
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
}


```
###  Path: `/src/classes/Application/AppSets/AppSetsException.php`

```php
namespace Application\Sets;

use Application\Exception\ApplicationException as ApplicationException;

class AppSetsException extends ApplicationException
{
	public const ERROR_ALIAS_ALREADY_EXISTS = 12701;
	public const ERROR_CANNOT_RENAME_TO_EXISTING_NAME = 12705;
	public const ERROR_CANNOT_RENAME_INEXISTANT_SET = 12704;
	public const ERROR_UNKNOWN_SET = 12702;
	public const ERROR_CANNOT_SAVE_CONFIGURATION = 12703;
	public const ERROR_FORMABLE_NOT_VALID = 12801;
	public const ERROR_INVALID_DEFAULT_AREA = 12802;
}


```
###  Path: `/src/classes/Application/AppSets/AppSetsFilterCriteria.php`

```php
namespace Application\AppSets;

use DBHelper_BaseFilterCriteria as DBHelper_BaseFilterCriteria;
use DBHelper_StatementBuilder_ValuesContainer as DBHelper_StatementBuilder_ValuesContainer;

final class AppSetsFilterCriteria extends DBHelper_BaseFilterCriteria
{
	public static function getValues(): DBHelper_StatementBuilder_ValuesContainer
	{
		/* ... */
	}


	public static function fillValues(
		DBHelper_StatementBuilder_ValuesContainer $container,
	): DBHelper_StatementBuilder_ValuesContainer
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/AppSets/AppSetsFilterSettings.php`

```php
namespace Application\AppSets;

use DBHelper_BaseFilterSettings as DBHelper_BaseFilterSettings;

final class AppSetsFilterSettings extends DBHelper_BaseFilterSettings
{
	public const SETTING_SEARCH = 'search';
}


```
###  Path: `/src/classes/Application/AppSets/DefaultAppSet.php`

```php
namespace Application\AppSets;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use Application\Admin\Index\AdminScreenIndex as AdminScreenIndex;
use Application\Admin\Welcome\Screens\WelcomeArea as WelcomeArea;

final class DefaultAppSet extends AppSet
{
	public function setRecordKey(string $name, mixed $value): bool
	{
		/* ... */
	}


	public function recordKeyExists(string $name): bool
	{
		/* ... */
	}


	public function save(bool $silent = false): bool
	{
		/* ... */
	}
}


```
---
**File Statistics**
- **Size**: 12.34 KB
- **Lines**: 630
File: `modules/application-sets/architecture.md`
