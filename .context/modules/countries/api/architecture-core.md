# Countries API - Core Architecture (Public API)
_SOURCE: AppCountryAPIInterface, AppCountryAPITrait, AppCountryParamsContainer, AppCountriesAPIInterface, AppCountriesAPITrait, AppCountriesParamsContainer, CountriesAPIGroup, CountryAPIException, GetAppCountriesAPI_
# AppCountryAPIInterface, AppCountryAPITrait, AppCountryParamsContainer, AppCountriesAPIInterface, AppCountriesAPITrait, AppCountriesParamsContainer, CountriesAPIGroup, CountryAPIException, GetAppCountriesAPI
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── Countries/
                └── API/
                    └── AppCountriesAPIInterface.php
                    └── AppCountriesAPITrait.php
                    └── AppCountriesParamsContainer.php
                    └── AppCountryAPIInterface.php
                    └── AppCountryAPITrait.php
                    └── AppCountryParamsContainer.php
                    └── CountriesAPIGroup.php
                    └── CountryAPIException.php
                    └── Methods/
                        └── GetAppCountriesAPI.php

```
###  Path: `/src/classes/Application/Countries/API/AppCountriesAPIInterface.php`

```php
namespace Application\Countries\API;

use Application\API\APIMethodInterface as APIMethodInterface;

/**
 * Interface for API methods that work with multiple countries.
 *
 * Complements the singular {@see AppCountryAPIInterface}: use this when an API
 * method must accept a list of countries (by IDs or ISO codes). The two traits
 * can coexist on the same method if needed.
 *
 * @package Countries
 * @subpackage API
 * @see AppCountriesAPITrait
 */
interface AppCountriesAPIInterface extends APIMethodInterface
{
	public const PARAM_COUNTRY_IDS = 'countryIDs';
	public const PARAM_COUNTRY_ISOS = 'countryISOs';

	public function manageAppCountriesParams(): AppCountriesParamsContainer;
}


```
###  Path: `/src/classes/Application/Countries/API/AppCountriesAPITrait.php`

```php
namespace Application\Countries\API;

/**
 * Trait used to implement API methods that work with multiple countries.
 *
 * ## Usage
 *
 * Use {@see self::manageAppCountriesParams()} to obtain the `AppCountriesParamsContainer`,
 * then register your preferred parameter pattern in `init()`.
 *
 * ### Pattern 1 — Individual registration (no mutual exclusivity)
 *
 * Register the IDs and ISOs handlers separately. When both are registered, the
 * container uses a "first non-null wins" strategy: the IDs handler is tried first;
 * if it returns `null` (no `countryIDs` value in the request), the ISOs handler
 * is tried next. Both parameters are optional and independent — the caller may
 * supply either, both, or neither.
 *
 * ```php
 * protected function init(): void
 * {
 *     $this->manageAppCountriesParams()->manageIDs()->register();
 *     $this->manageAppCountriesParams()->manageISOs()->register();
 * }
 * ```
 *
 * > **Important:** `AppCountryIDsHandler` and `AppCountryISOsHandler` both return
 * > `null` (not `[]`) when the request contains no value for their parameter. This
 * > `null` sentinel is what allows the container to fall through to the next handler.
 *
 * @see TestGetCountriesAPI for a live example of this registration pattern.
 *
 * ### Pattern 2 — OrRule registration (mutual exclusivity)
 *
 * Register the combined `AppCountriesParamRule` via `manageAllParamsRule()`. The
 * OrRule enforces that the caller supplies **either** `countryIDs` **or**
 * `countryISOs`, but not both. Supplying both or neither results in an API error
 * response. Use this pattern when mutual exclusivity is a requirement.
 *
 * ```php
 * protected function init(): void
 * {
 *     $this->manageAppCountriesParams()->manageAllParamsRule()->register();
 * }
 * ```
 *
 * @see TestGetCountriesBySetAPI for a live example of this registration pattern.
 *
 * @package Countries
 * @subpackage API
 * @see AppCountriesAPIInterface
 * @see AppCountriesParamsContainer
 */
trait AppCountriesAPITrait
{
	public function manageAppCountriesParams(): AppCountriesParamsContainer
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/API/AppCountriesParamsContainer.php`

```php
namespace Application\Countries\API;

use Application\API\Parameters\Handlers\BaseParamsHandlerContainer as BaseParamsHandlerContainer;
use Application\Countries\API\ParamSets\AppCountriesRuleHandler as AppCountriesRuleHandler;
use Application\Countries\API\Params\AppCountryIDsHandler as AppCountryIDsHandler;
use Application\Countries\API\Params\AppCountryISOsHandler as AppCountryISOsHandler;
use Application_Countries_Country as Application_Countries_Country;

/**
 * Parameters container for API methods that work with multiple countries.
 *
 * Manages three handlers (IDs, ISOs, OrRule) and resolves to an array of
 * country records. Complements the singular {@see AppCountryParamsContainer}.
 *
 * @method AppCountriesAPIInterface getMethod()
 *
 * @package Countries
 * @subpackage API
 * @see AppCountriesAPITrait
 * @see AppCountriesAPIInterface
 */
class AppCountriesParamsContainer extends BaseParamsHandlerContainer
{
	/**
	 * Resolves the list of countries from the registered handlers.
	 *
	 * Returns an empty array if no handler was able to resolve countries.
	 *
	 * @return Application_Countries_Country[]
	 */
	public function resolveValue(): array
	{
		/* ... */
	}


	/**
	 * Requires that at least one handler resolves a list of countries.
	 * Triggers an API error response if no value can be resolved.
	 *
	 * @return Application_Countries_Country[]
	 */
	public function requireValue(): array
	{
		/* ... */
	}


	/**
	 * Pre-selects the given list of countries in all handlers that support
	 * value selection.
	 *
	 * @param Application_Countries_Country[] $countries
	 * @return $this
	 */
	public function selectAppCountries(array $countries): self
	{
		/* ... */
	}


	public function manageIDs(): AppCountryIDsHandler
	{
		/* ... */
	}


	public function manageISOs(): AppCountryISOsHandler
	{
		/* ... */
	}


	public function manageAllParamsRule(): AppCountriesRuleHandler
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/API/AppCountryAPIInterface.php`

```php
namespace Application\Countries\API;

use Application\API\APIMethodInterface as APIMethodInterface;

/**
 * Interface for API methods that work with countries.
 *
 * @package Countries
 * @subpackage API
 * @see AppCountryAPITrait
 */
interface AppCountryAPIInterface extends APIMethodInterface
{
	public const PARAM_COUNTRY_ID = 'countryID';
	public const PARAM_COUNTRY_ISO = 'countryISO';
	public const KEY_COUNTRY_ID = 'countryID';
	public const KEY_COUNTRY_ISO = 'isoCode';

	public function manageAppCountryParams(): AppCountryParamsContainer;
}


```
###  Path: `/src/classes/Application/Countries/API/AppCountryAPITrait.php`

```php
namespace Application\Countries\API;

use Application\Countries\API\ParamSets\AppCountryParamRule as AppCountryParamRule;
use Application\Countries\API\Params\AppCountryIDParam as AppCountryIDParam;
use Application\Countries\API\Params\AppCountryISOParam as AppCountryISOParam;
use Application_Countries_Country as Application_Countries_Country;

/**
 * Trait used to implement API methods that work with countries.
 *
 * ## Usage
 *
 * Use {@see self::manageAppCountryParams()} to manage the country parameters.
 *
 * @package Countries
 * @subpackage API
 * @see AppCountryAPIInterface
 */
trait AppCountryAPITrait
{
	public function manageAppCountryParams(): AppCountryParamsContainer
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/API/AppCountryParamsContainer.php`

```php
namespace Application\Countries\API;

use AppUtils\ClassHelper as ClassHelper;
use Application\API\Parameters\Handlers\BaseParamsHandlerContainer as BaseParamsHandlerContainer;
use Application\Countries\API\ParamSets\AppCountryRuleHandler as AppCountryRuleHandler;
use Application\Countries\API\Params\AppCountryIDHandler as AppCountryIDHandler;
use Application\Countries\API\Params\AppCountryISOHandler as AppCountryISOHandler;
use Application_Countries_Country as Application_Countries_Country;

/**
 * Container for the single-country API parameter handlers.
 *
 * Manages the country ID, country ISO, and OrRule handlers,
 * resolving to a single {@see Application_Countries_Country} record.
 *
 * @package Application
 * @subpackage Countries
 *
 * @method AppCountryAPIInterface getMethod()
 */
class AppCountryParamsContainer extends BaseParamsHandlerContainer
{
	public function resolveValue(): ?Application_Countries_Country
	{
		/* ... */
	}


	public function requireValue(): Application_Countries_Country
	{
		/* ... */
	}


	public function selectAppCountry(Application_Countries_Country $country): self
	{
		/* ... */
	}


	public function manageID(): AppCountryIDHandler
	{
		/* ... */
	}


	public function manageISO(): AppCountryISOHandler
	{
		/* ... */
	}


	public function manageAllParamsRule(): AppCountryRuleHandler
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/API/CountriesAPIGroup.php`

```php
namespace Application\Countries\API;

use Application\API\Groups\GenericAPIGroup as GenericAPIGroup;

class CountriesAPIGroup extends GenericAPIGroup
{
	public static function create(): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/API/CountryAPIException.php`

```php
namespace Application\Countries\API;

use Application\Countries\CountryException as CountryException;

/**
 * @package Countries
 * @subpackage API
 */
class CountryAPIException extends CountryException
{
	public const INVALID_PARAM_SET = 184701;
}


```
###  Path: `/src/classes/Application/Countries/API/Methods/GetAppCountriesAPI.php`

```php
namespace Application\Countries\API\Methods;

use AppLocalize\Localization\Country\CountryDE as CountryDE;
use AppLocalize\Localization\Country\CountryGB as CountryGB;
use AppUtils\ArrayDataCollection as ArrayDataCollection;
use Application\API\BaseMethods\BaseAPIMethod as BaseAPIMethod;
use Application\API\Groups\APIGroupInterface as APIGroupInterface;
use Application\API\Traits\JSONResponseInterface as JSONResponseInterface;
use Application\API\Traits\JSONResponseTrait as JSONResponseTrait;
use Application\API\Traits\RequestRequestInterface as RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait as RequestRequestTrait;
use Application\API\Utilities\KeyDescription as KeyDescription;
use Application\AppFactory as AppFactory;
use Application\Countries\API\AppCountryAPIInterface as AppCountryAPIInterface;
use Application\Countries\API\CountriesAPIGroup as CountriesAPIGroup;
use Application\Locales\API\Methods\GetAppLocalesAPI as GetAppLocalesAPI;
use Application_Countries as Application_Countries;
use Application_Countries_Country as Application_Countries_Country;

/**
 * API method to retrieve the list of available tenants.
 *
 * @package Countries
 * @subpackage API
 */
class GetAppCountriesAPI extends BaseAPIMethod implements RequestRequestInterface, JSONResponseInterface
{
	use RequestRequestTrait;
	use JSONResponseTrait;

	public const METHOD_NAME = 'GetAppCountries';
	public const VERSION_1 = '1.0';
	public const CURRENT_VERSION = self::VERSION_1;
	public const VERSIONS = [self::VERSION_1];
	public const KEY_COUNTRIES = 'countries';
	public const KEY_LABEL_INVARIANT = 'labelInvariant';
	public const KEY_CODE_ALIASES = 'codeAliases';
	public const KEY_DEFAULT_LOCALE = 'defaultLocale';
	public const KEY_DEFAULT_LOCALE_CODE = 'localeCode';
	public const KEY_DEFAULT_LOCALE_LANGUAGE_CODE = 'languageCode';
	public const KEY_CURRENCY = 'currency';
	public const KEY_CURRENCY_ISO_CODE = 'isoCode';
	public const KEY_CURRENCY_LABEL_SINGULAR = 'labelSingular';
	public const KEY_CURRENCY_LABEL_PLURAL = 'labelPlural';
	public const KEY_CURRENCY_SYMBOL = 'symbol';
	public const KEY_CURRENCY_PREFERRED_SYMBOL = 'preferredSymbol';
	public const KEY_CURRENCY_THOUSANDS_SEP = 'thousandsSeparator';
	public const KEY_CURRENCY_DECIMAL_SEP = 'decimalSeparator';
	public const KEY_CURRENCY_STRUCTURAL_TEMPLATE = 'structuralTemplate';

	public function getMethodName(): string
	{
		/* ... */
	}


	public function getVersions(): array
	{
		/* ... */
	}


	public function getCurrentVersion(): string
	{
		/* ... */
	}


	public function getGroup(): APIGroupInterface
	{
		/* ... */
	}


	/**
	 * @param Application_Countries_Country $country
	 * @return array<string,mixed>
	 */
	public static function collectCountry(Application_Countries_Country $country): array
	{
		/* ... */
	}


	public function getDescription(): string
	{
		/* ... */
	}


	public function getRelatedMethodNames(): array
	{
		/* ... */
	}


	public function getExampleJSONResponse(): array
	{
		/* ... */
	}


	public function getChangelog(): array
	{
		/* ... */
	}


	public function getReponseKeyDescriptions(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/API/Methods/GetAppCountriesAPI.php`

```php
namespace Application\Countries\API\Methods;

use AppLocalize\Localization\Country\CountryDE as CountryDE;
use AppLocalize\Localization\Country\CountryGB as CountryGB;
use AppUtils\ArrayDataCollection as ArrayDataCollection;
use Application\API\BaseMethods\BaseAPIMethod as BaseAPIMethod;
use Application\API\Groups\APIGroupInterface as APIGroupInterface;
use Application\API\Traits\JSONResponseInterface as JSONResponseInterface;
use Application\API\Traits\JSONResponseTrait as JSONResponseTrait;
use Application\API\Traits\RequestRequestInterface as RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait as RequestRequestTrait;
use Application\API\Utilities\KeyDescription as KeyDescription;
use Application\AppFactory as AppFactory;
use Application\Countries\API\AppCountryAPIInterface as AppCountryAPIInterface;
use Application\Countries\API\CountriesAPIGroup as CountriesAPIGroup;
use Application\Locales\API\Methods\GetAppLocalesAPI as GetAppLocalesAPI;
use Application_Countries as Application_Countries;
use Application_Countries_Country as Application_Countries_Country;

/**
 * API method to retrieve the list of available tenants.
 *
 * @package Countries
 * @subpackage API
 */
class GetAppCountriesAPI extends BaseAPIMethod implements RequestRequestInterface, JSONResponseInterface
{
	use RequestRequestTrait;
	use JSONResponseTrait;

	public const METHOD_NAME = 'GetAppCountries';
	public const VERSION_1 = '1.0';
	public const CURRENT_VERSION = self::VERSION_1;
	public const VERSIONS = [self::VERSION_1];
	public const KEY_COUNTRIES = 'countries';
	public const KEY_LABEL_INVARIANT = 'labelInvariant';
	public const KEY_CODE_ALIASES = 'codeAliases';
	public const KEY_DEFAULT_LOCALE = 'defaultLocale';
	public const KEY_DEFAULT_LOCALE_CODE = 'localeCode';
	public const KEY_DEFAULT_LOCALE_LANGUAGE_CODE = 'languageCode';
	public const KEY_CURRENCY = 'currency';
	public const KEY_CURRENCY_ISO_CODE = 'isoCode';
	public const KEY_CURRENCY_LABEL_SINGULAR = 'labelSingular';
	public const KEY_CURRENCY_LABEL_PLURAL = 'labelPlural';
	public const KEY_CURRENCY_SYMBOL = 'symbol';
	public const KEY_CURRENCY_PREFERRED_SYMBOL = 'preferredSymbol';
	public const KEY_CURRENCY_THOUSANDS_SEP = 'thousandsSeparator';
	public const KEY_CURRENCY_DECIMAL_SEP = 'decimalSeparator';
	public const KEY_CURRENCY_STRUCTURAL_TEMPLATE = 'structuralTemplate';

	public function getMethodName(): string
	{
		/* ... */
	}


	public function getVersions(): array
	{
		/* ... */
	}


	public function getCurrentVersion(): string
	{
		/* ... */
	}


	public function getGroup(): APIGroupInterface
	{
		/* ... */
	}


	/**
	 * @param Application_Countries_Country $country
	 * @return array<string,mixed>
	 */
	public static function collectCountry(Application_Countries_Country $country): array
	{
		/* ... */
	}


	public function getDescription(): string
	{
		/* ... */
	}


	public function getRelatedMethodNames(): array
	{
		/* ... */
	}


	public function getExampleJSONResponse(): array
	{
		/* ... */
	}


	public function getChangelog(): array
	{
		/* ... */
	}


	public function getReponseKeyDescriptions(): array
	{
		/* ... */
	}
}


```
---
**File Statistics**
- **Size**: 15.61 KB
- **Lines**: 585
File: `modules/countries/api/architecture-core.md`
