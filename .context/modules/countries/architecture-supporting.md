# Countries - AI Tools, Events, Rights (Public API)
_SOURCE: CountryAITools, ListCountriesTool, GetCountryConfigTool, IgnoredCountriesUpdatedEvent, CountryRightsInterface, CountryRightsTrait, CountryScreenRights_
# CountryAITools, ListCountriesTool, GetCountryConfigTool, IgnoredCountriesUpdatedEvent, CountryRightsInterface, CountryRightsTrait, CountryScreenRights
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── Countries/
                └── AI/
                    ├── CountryAIException.php
                    ├── CountryAITools.php
                    ├── Tools/
                    │   └── GetCountryConfigTool.php
                    │   └── ListCountriesTool.php
                └── Event/
                    ├── IgnoredCountriesUpdatedEvent.php
                └── Rights/
                    └── CountryRightsInterface.php
                    └── CountryRightsTrait.php
                    └── CountryScreenRights.php

```
###  Path: `/src/classes/Application/Countries/AI/CountryAIException.php`

```php
namespace Application\Countries\AITools;

use Application\AI\AIToolException as AIToolException;

class CountryAIException extends AIToolException
{
	public const ERROR_INVALID_COUNTRY = 189101;
}


```
###  Path: `/src/classes/Application/Countries/AI/CountryAITools.php`

```php
namespace Application\Countries\AITools;

use Application\AI\BaseAIToolContainer as BaseAIToolContainer;
use Application\Countries\AI\Tools\GetCountryConfigTool as GetCountryConfigTool;
use Application\Countries\AI\Tools\ListCountriesTool as ListCountriesTool;
use PhpMcp\Server\Attributes\McpTool as McpTool;

/**
 * @package Countries
 * @subpackage AI Tools
 */
class CountryAITools extends BaseAIToolContainer
{
	#[McpTool(
		name: ListCountriesTool::TOOL_NAME,
		description: ListCountriesTool::TOOL_DESCRIPTION,
	)]
	public function listCountries(): array
	{
		/* ... */
	}


	/**
	 * @param string $isoCode Two-letter country code, e.g. `at`, `gb`. Case-insensitive.
	 *         The code `uk` is accepted as alias for `gb`, but will exclusively be
	 *         referred to as `gb` in the returned data.
	 */
	#[McpTool(
		name: GetCountryConfigTool::TOOL_NAME,
		description: GetCountryConfigTool::TOOL_DESCRIPTION,
	)]
	public function getCountryConfig(string $isoCode): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/AI/Tools/GetCountryConfigTool.php`

```php
namespace Application\Countries\AI\Tools;

use Application\AI\Cache\AICacheStrategyInterface as AICacheStrategyInterface;
use Application\AI\Cache\Strategies\FixedDurationStrategy as FixedDurationStrategy;
use Application\AI\Tools\BaseAITool as BaseAITool;
use Application\AppFactory as AppFactory;
use Application\Countries\AITools\CountryAIException as CountryAIException;
use Application\Countries\API\Methods\GetAppCountriesAPI as GetAppCountriesAPI;

/**
 * @package Countries
 * @subpackage AI Tools
 */
class GetCountryConfigTool extends BaseAITool
{
	public const TOOL_NAME = 'get_country_configuration';
	public const TOOL_DESCRIPTION = 'Get detailed currency and locale config for a specific country.';

	public function getID(): string
	{
		/* ... */
	}


	public function execute(): array
	{
		/* ... */
	}


	public function getCacheStrategy(): AICacheStrategyInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/AI/Tools/ListCountriesTool.php`

```php
namespace Application\Countries\AI\Tools;

use Application\AI\Cache\AICacheStrategyInterface as AICacheStrategyInterface;
use Application\AI\Cache\Strategies\FixedDurationStrategy as FixedDurationStrategy;
use Application\AI\Tools\BaseAITool as BaseAITool;
use Application\AppFactory as AppFactory;

/**
 * @package Countries
 * @subpackage AI Tools
 */
class ListCountriesTool extends BaseAITool
{
	public const TOOL_NAME = 'list_supported_countries';
	public const TOOL_DESCRIPTION = 'Returns a complete list of all supported/available countries with their ISO codes. Use this when the user asks: what/which countries are available, show all countries, list supported countries, or wants to see country options.';

	public function getID(): string
	{
		/* ... */
	}


	public function getCacheStrategy(): AICacheStrategyInterface
	{
		/* ... */
	}


	public function execute(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/Event/IgnoredCountriesUpdatedEvent.php`

```php
namespace Application\Countries\Event;

use Application\EventHandler\Eventables\BaseEventableEvent as BaseEventableEvent;

class IgnoredCountriesUpdatedEvent extends BaseEventableEvent
{
	public const EVENT_NAME = 'IgnoredCountriesUpdated';

	public function getName(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/Rights/CountryRightsInterface.php`

```php
namespace Application\Countries\Rights;

use Application_User_Interface as Application_User_Interface;

/**
 * Interface defining all user rights used by the country management.
 * The user configuration is done in the trait {@see CountryRightsTrait}.
 *
 * @package Countries
 * @subpackage Rights
 * @see CountryRightsTrait
 */
interface CountryRightsInterface extends Application_User_Interface
{
	public const RIGHT_VIEW_COUNTRIES = 'ViewCountries';
	public const RIGHT_CREATE_COUNTRIES = 'CreateCountries';
	public const RIGHT_EDIT_COUNTRIES = 'EditCountries';
	public const RIGHT_DELETE_COUNTRIES = 'DeleteCountries';

	public function canViewCountries(): bool;


	public function canEditCountries(): bool;


	public function canDeleteCountries(): bool;


	public function canCreateCountries(): bool;
}


```
###  Path: `/src/classes/Application/Countries/Rights/CountryRightsTrait.php`

```php
namespace Application\Countries\Rights;

use Application_User_Rights_Group as Application_User_Rights_Group;

/**
 * Trait used to configure the country rights setup
 * in the user class.
 *
 * @package Countries
 * @subpackage Rights
 * @see CountryRightsInterface
 */
trait CountryRightsTrait
{
	public function canEditCountries(): bool
	{
		/* ... */
	}


	public function canDeleteCountries(): bool
	{
		/* ... */
	}


	public function canCreateCountries(): bool
	{
		/* ... */
	}


	public function canViewCountries(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/Rights/CountryScreenRights.php`

```php
namespace Application\Countries\Rights;

/**
 * User rights used by the country management screens.
 *
 * @package Countries
 * @subpackage Rights
 */
class CountryScreenRights
{
	public const SCREEN_AREA = CountryRightsInterface::RIGHT_VIEW_COUNTRIES;
	public const SCREEN_LIST = CountryRightsInterface::RIGHT_VIEW_COUNTRIES;
	public const SCREEN_LIST_MULTI_DELETE = CountryRightsInterface::RIGHT_DELETE_COUNTRIES;
	public const SCREEN_VIEW = CountryRightsInterface::RIGHT_VIEW_COUNTRIES;
	public const SCREEN_STATUS = CountryRightsInterface::RIGHT_VIEW_COUNTRIES;
	public const SCREEN_CREATE = CountryRightsInterface::RIGHT_CREATE_COUNTRIES;
	public const SCREEN_SETTINGS = CountryRightsInterface::RIGHT_EDIT_COUNTRIES;
}


```
---
**File Statistics**
- **Size**: 7.16 KB
- **Lines**: 288
File: `modules/countries/architecture-supporting.md`
