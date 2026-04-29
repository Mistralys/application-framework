# Countries API - ParamSets (Public API)
_SOURCE: Singular: AppCountryParamRule, AppCountryRuleHandler, CountryIDSet, CountryISOSet, AppCountryParamSetInterface, BaseAppCountryParamSet — Plural: AppCountriesParamRule, AppCountriesRuleHandler, CountryIDsSet, CountryISOsSet, AppCountriesParamSetInterface, BaseAppCountriesParamSet_
# Singular: AppCountryParamRule, AppCountryRuleHandler, CountryIDSet, CountryISOSet, AppCountryParamSetInterface, BaseAppCountryParamSet — Plural: AppCountriesParamRule, AppCountriesRuleHandler, CountryIDsSet, CountryISOsSet, AppCountriesParamSetInterface, BaseAppCountriesParamSet
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── Countries/
                └── API/
                    └── ParamSets/
                        └── AppCountriesParamRule.php
                        └── AppCountriesParamSetInterface.php
                        └── AppCountriesRuleHandler.php
                        └── AppCountryParamRule.php
                        └── AppCountryParamSetInterface.php
                        └── AppCountryRuleHandler.php
                        └── BaseAppCountriesParamSet.php
                        └── BaseAppCountryParamSet.php
                        └── CountryIDSet.php
                        └── CountryIDsSet.php
                        └── CountryISOSet.php
                        └── CountryISOsSet.php

```
###  Path: `/src/classes/Application/Countries/API/ParamSets/AppCountriesParamRule.php`

```php
namespace Application\Countries\API\ParamSets;

use Application\API\Parameters\ParamSetInterface as ParamSetInterface;
use Application\API\Parameters\Rules\Type\OrRule as OrRule;
use Application\Countries\API\AppCountriesAPIInterface as AppCountriesAPIInterface;
use Application\Countries\API\CountryAPIException as CountryAPIException;
use Application_Countries_Country as Application_Countries_Country;

/**
 * Custom rule that combines all parameter sets that can be used to resolve
 * a list of countries.
 *
 * Enforces mutual exclusivity between {@see CountryIDsSet} and
 * {@see CountryISOsSet} via {@see OrRule}: callers must provide
 * `countryIDs` **or** `countryISOs`, not both.
 *
 * The {@see addSet()} override ensures only {@see AppCountriesParamSetInterface}
 * instances are accepted, throwing {@see CountryAPIException} otherwise.
 *
 * Mirrors {@see AppCountryParamRule} (singular) for pattern consistency.
 *
 * @package Countries
 * @subpackage API
 *
 * @method AppCountriesParamSetInterface|NULL getValidSet()
 * @method AppCountriesParamSetInterface requireValidSet()
 */
class AppCountriesParamRule extends OrRule
{
	/**
	 * Adds a parameter set to the rule.
	 *
	 * Only {@see AppCountriesParamSetInterface} instances are accepted.
	 * Passing any other {@see ParamSetInterface} implementation throws
	 * a {@see CountryAPIException}.
	 *
	 * @param AppCountriesParamSetInterface|ParamSetInterface $set Only {@see AppCountriesParamSetInterface} instances are allowed.
	 * @return $this
	 * @throws CountryAPIException When $set does not implement {@see AppCountriesParamSetInterface}.
	 */
	public function addSet(AppCountriesParamSetInterface|ParamSetInterface $set): self
	{
		/* ... */
	}


	/**
	 * Returns the resolved list of countries from the valid parameter set,
	 * or an empty array if no valid set was matched.
	 *
	 * @return Application_Countries_Country[]
	 */
	public function getCountries(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/API/ParamSets/AppCountriesParamSetInterface.php`

```php
namespace Application\Countries\API\ParamSets;

use Application\API\Parameters\Rules\CustomParamSetInterface as CustomParamSetInterface;
use Application\Countries\API\AppCountriesAPIInterface as AppCountriesAPIInterface;
use Application_Countries_Country as Application_Countries_Country;

/**
 * Contract for parameter sets that resolve to an array of countries.
 *
 * Mirrors {@see AppCountryParamSetInterface} (singular) for pattern consistency.
 *
 * @package Countries
 * @subpackage API
 * @see BaseAppCountriesParamSet
 *
 * @method AppCountriesAPIInterface getMethod()
 */
interface AppCountriesParamSetInterface extends CustomParamSetInterface
{
	/**
	 * Returns the resolved country objects for the parameter set's current value.
	 *
	 * @return Application_Countries_Country[]
	 */
	public function getCountries(): array;
}


```
###  Path: `/src/classes/Application/Countries/API/ParamSets/AppCountriesRuleHandler.php`

```php
namespace Application\Countries\API\ParamSets;

use Application\API\Parameters\Handlers\BaseRuleHandler as BaseRuleHandler;
use Application\Countries\API\AppCountriesAPIInterface as AppCountriesAPIInterface;
use Application_Countries_Country as Application_Countries_Country;

/**
 * Bridges the {@see AppCountriesParamRule} into the container's handler
 * architecture.
 *
 * Provides type-narrowed overrides for {@see resolveValue()} (returning
 * `Application_Countries_Country[]`) and {@see getRule()} (returning
 * `?AppCountriesParamRule`) so consumers receive correctly-typed values
 * without casting.
 *
 * Mirrors {@see AppCountryRuleHandler} (singular) for pattern consistency.
 *
 * @package Countries
 * @subpackage API
 *
 * @method AppCountriesAPIInterface getMethod()
 */
class AppCountriesRuleHandler extends BaseRuleHandler
{
	/**
	 * Returns the resolved list of countries, or an empty array if none
	 * could be resolved.
	 *
	 * @return Application_Countries_Country[]
	 */
	public function resolveValue(): array
	{
		/* ... */
	}


	/**
	 * Returns the underlying {@see AppCountriesParamRule}, or `null` if
	 * the rule has not been registered yet.
	 *
	 * @return AppCountriesParamRule|null
	 */
	public function getRule(): ?AppCountriesParamRule
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/API/ParamSets/AppCountryParamRule.php`

```php
namespace Application\Countries\API\ParamSets;

use Application\API\Parameters\ParamSetInterface as ParamSetInterface;
use Application\API\Parameters\Rules\Type\OrRule as OrRule;
use Application\Countries\API\AppCountryAPIInterface as AppCountryAPIInterface;
use Application\Countries\API\CountryAPIException as CountryAPIException;
use Application_Countries_Country as Application_Countries_Country;

/**
 * Custom rule that combines all parameter sets that can be used
 * to resolve a specific country.
 *
 * @package Countries
 * @subpackage API
 *
 * @method AppCountryParamSetInterface|NULL getValidSet()
 * @method AppCountryParamSetInterface requireValidSet()
 */
class AppCountryParamRule extends OrRule
{
	/**
	 * @param AppCountryParamSetInterface|ParamSetInterface $set Only {@see AppCountryParamSetInterface} instances are allowed.
	 * @return $this
	 */
	public function addSet(AppCountryParamSetInterface|ParamSetInterface $set): self
	{
		/* ... */
	}


	public function getCountry(): ?Application_Countries_Country
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/API/ParamSets/AppCountryParamSetInterface.php`

```php
namespace Application\Countries\API\ParamSets;

use Application\API\Parameters\Rules\CustomParamSetInterface as CustomParamSetInterface;
use Application\Countries\API\AppCountryAPIInterface as AppCountryAPIInterface;
use Application_Countries_Country as Application_Countries_Country;

/**
 * @method AppCountryAPIInterface getMethod()
 */
interface AppCountryParamSetInterface extends CustomParamSetInterface
{
	public function getCountry(): ?Application_Countries_Country;
}


```
###  Path: `/src/classes/Application/Countries/API/ParamSets/AppCountryRuleHandler.php`

```php
namespace Application\Countries\API\ParamSets;

use Application\API\Parameters\Handlers\BaseRuleHandler as BaseRuleHandler;
use Application\Countries\API\AppCountryAPIInterface as AppCountryAPIInterface;
use Application_Countries_Country as Application_Countries_Country;

/**
 * Bridges the {@see AppCountryParamRule} into the container's handler
 * architecture.
 *
 * Provides type-narrowed overrides for {@see resolveValue()} (returning
 * `?Application_Countries_Country`) and {@see getRule()} (returning
 * `?AppCountryParamRule`) so consumers receive correctly-typed values
 * without casting.
 *
 * Mirrors {@see AppCountriesRuleHandler} (plural) for pattern consistency.
 *
 * @package Countries
 * @subpackage API
 *
 * @method AppCountryAPIInterface getMethod()
 */
class AppCountryRuleHandler extends BaseRuleHandler
{
	/**
	 * Returns the resolved country, or `null` if none could be resolved.
	 *
	 * @return Application_Countries_Country|null
	 */
	public function resolveValue(): ?Application_Countries_Country
	{
		/* ... */
	}


	/**
	 * Returns the underlying {@see AppCountryParamRule}, or `null` if
	 * the rule has not been registered yet.
	 *
	 * @return AppCountryParamRule|null
	 */
	public function getRule(): ?AppCountryParamRule
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/API/ParamSets/BaseAppCountriesParamSet.php`

```php
namespace Application\Countries\API\ParamSets;

use Application\API\Parameters\Rules\BaseCustomParamSet as BaseCustomParamSet;
use Application\Countries\API\AppCountriesAPIInterface as AppCountriesAPIInterface;

/**
 * Abstract base class for parameter sets that are used to resolve a list of
 * countries. Implements the interface {@see AppCountriesParamSetInterface}.
 *
 * Mirrors {@see BaseAppCountryParamSet} (singular) for pattern consistency.
 *
 * @package Countries
 * @subpackage API
 *
 * @method AppCountriesAPIInterface getMethod()
 */
abstract class BaseAppCountriesParamSet extends BaseCustomParamSet implements AppCountriesParamSetInterface
{
}


```
###  Path: `/src/classes/Application/Countries/API/ParamSets/BaseAppCountryParamSet.php`

```php
namespace Application\Countries\API\ParamSets;

use Application\API\Parameters\Rules\BaseCustomParamSet as BaseCustomParamSet;
use Application\Countries\API\AppCountryAPIInterface as AppCountryAPIInterface;

/**
 * Abstract base class for parameter sets that are used
 * to resolve a specific country. Implements the interface
 * {@see AppCountryParamSetInterface}.
 *
 * @package Countries
 * @subpackage API
 *
 * @method AppCountryAPIInterface getMethod()
 */
abstract class BaseAppCountryParamSet extends BaseCustomParamSet implements AppCountryParamSetInterface
{
}


```
###  Path: `/src/classes/Application/Countries/API/ParamSets/CountryIDSet.php`

```php
namespace Application\Countries\API\ParamSets;

use Application\Countries\API\Params\AppCountryIDParam as AppCountryIDParam;
use Application_Countries_Country as Application_Countries_Country;

class CountryIDSet extends BaseAppCountryParamSet
{
	public const SET_NAME = 'CountryID';

	public function getCountry(): ?Application_Countries_Country
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/API/ParamSets/CountryIDsSet.php`

```php
namespace Application\Countries\API\ParamSets;

use Application\Countries\API\Params\AppCountryIDsParam as AppCountryIDsParam;
use Application_Countries_Country as Application_Countries_Country;

/**
 * Parameter set for the multi-country OrRule that resolves countries by their IDs.
 *
 * Registers the {@see AppCountryIDsParam} via the container's
 * {@see \Application\Countries\API\AppCountriesParamsContainer::manageIDs()} handler
 * and exposes the resolved list of countries via {@see getCountries()}.
 *
 * Mirrors {@see CountryIDSet} (singular) for pattern consistency.
 *
 * @package Countries
 * @subpackage API
 * @see AppCountriesParamRule
 */
class CountryIDsSet extends BaseAppCountriesParamSet
{
	public const SET_NAME = 'CountryIDs';

	/**
	 * @return Application_Countries_Country[]
	 */
	public function getCountries(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/API/ParamSets/CountryISOSet.php`

```php
namespace Application\Countries\API\ParamSets;

use Application\Countries\API\Params\AppCountryISOParam as AppCountryISOParam;
use Application_Countries_Country as Application_Countries_Country;

class CountryISOSet extends BaseAppCountryParamSet
{
	public const SET_NAME = 'CountryISO';

	public function getCountry(): ?Application_Countries_Country
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/API/ParamSets/CountryISOsSet.php`

```php
namespace Application\Countries\API\ParamSets;

use Application\Countries\API\Params\AppCountryISOsParam as AppCountryISOsParam;
use Application_Countries_Country as Application_Countries_Country;

/**
 * Parameter set for the multi-country OrRule that resolves countries by their ISO codes.
 *
 * Registers the {@see AppCountryISOsParam} via the container's
 * {@see \Application\Countries\API\AppCountriesParamsContainer::manageISOs()} handler
 * and exposes the resolved list of countries via {@see getCountries()}.
 *
 * Mirrors {@see CountryISOSet} (singular) for pattern consistency.
 *
 * @package Countries
 * @subpackage API
 * @see AppCountriesParamRule
 */
class CountryISOsSet extends BaseAppCountriesParamSet
{
	public const SET_NAME = 'CountryISOs';

	/**
	 * @return Application_Countries_Country[]
	 */
	public function getCountries(): array
	{
		/* ... */
	}
}


```
---
**File Statistics**
- **Size**: 13.03 KB
- **Lines**: 446
File: `modules/countries/api/architecture-paramsets.md`
