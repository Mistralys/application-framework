# Countries API - Params (Public API)
_SOURCE: AppCountryParamInterface, AppCountriesParamInterface, AppCountryIDParam, AppCountryISOParam, AppCountryIDsParam, AppCountryIDsValidation, AppCountryIDsHandler, AppCountryIDHandler, AppCountryISOHandler_
# AppCountryParamInterface, AppCountriesParamInterface, AppCountryIDParam, AppCountryISOParam, AppCountryIDsParam, AppCountryIDsValidation, AppCountryIDsHandler, AppCountryIDHandler, AppCountryISOHandler
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── Countries/
                └── API/
                    └── Params/
                        └── AppCountriesParamInterface.php
                        └── AppCountryIDHandler.php
                        └── AppCountryIDParam.php
                        └── AppCountryIDsHandler.php
                        └── AppCountryIDsParam.php
                        └── AppCountryIDsValidation.php
                        └── AppCountryISOHandler.php
                        └── AppCountryISOParam.php
                        └── AppCountryISOsHandler.php
                        └── AppCountryISOsParam.php
                        └── AppCountryISOsValidation.php
                        └── AppCountryParamInterface.php

```
###  Path: `/src/classes/Application/Countries/API/Params/AppCountriesParamInterface.php`

```php
namespace Application\Countries\API\Params;

use Application\API\Parameters\APIParameterInterface as APIParameterInterface;
use Application_Countries_Country as Application_Countries_Country;

/**
 * Shared interface for API parameters that resolve to multiple countries.
 *
 * Mirrors {@see AppCountryParamInterface} (singular) for pattern consistency.
 *
 * @package Countries
 * @subpackage API
 * @see AppCountryIDsParam
 * @see AppCountryISOsParam
 */
interface AppCountriesParamInterface extends APIParameterInterface
{
	/**
	 * Returns the resolved country objects for the parameter's current value.
	 *
	 * @return Application_Countries_Country[]
	 */
	public function getCountries(): array;
}


```
###  Path: `/src/classes/Application/Countries/API/Params/AppCountryIDHandler.php`

```php
namespace Application\Countries\API\Params;

use AppUtils\ClassHelper as ClassHelper;
use Application\API\Parameters\Handlers\BaseParamHandler as BaseParamHandler;
use Application_Countries_Country as Application_Countries_Country;

class AppCountryIDHandler extends BaseParamHandler
{
	public function register(): AppCountryIDParam
	{
		/* ... */
	}


	public function getParam(): ?AppCountryIDParam
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/API/Params/AppCountryIDParam.php`

```php
namespace Application\Countries\API\Params;

use Application\API\Parameters\Type\IntegerParameter as IntegerParameter;
use Application\API\Parameters\ValueLookup\SelectableParamValue as SelectableParamValue;
use Application\API\Parameters\ValueLookup\SelectableValueParamInterface as SelectableValueParamInterface;
use Application\API\Parameters\ValueLookup\SelectableValueParamTrait as SelectableValueParamTrait;
use Application\AppFactory as AppFactory;
use Application\Countries\API\AppCountryAPIInterface as AppCountryAPIInterface;
use Application_Countries_Country as Application_Countries_Country;

/**
 * Country ID parameter for the Countries API.
 *
 * > NOTE: This implements {@see AppCountryParamInterface}
 * > to provide access to the resolved country object.
 *
 * @package Countries
 * @subpackage API
 */
class AppCountryIDParam extends IntegerParameter implements SelectableValueParamInterface, AppCountryParamInterface
{
	use SelectableValueParamTrait;

	public function getCountry(): ?Application_Countries_Country
	{
		/* ... */
	}


	public function getDefaultSelectableValue(): ?SelectableParamValue
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/API/Params/AppCountryIDsHandler.php`

```php
namespace Application\Countries\API\Params;

use AppUtils\ClassHelper as ClassHelper;
use Application\API\Parameters\Handlers\BaseParamHandler as BaseParamHandler;
use Application_Countries_Country as Application_Countries_Country;

/**
 * Handler that bridges {@see AppCountryIDsParam} into a
 * {@see \Application\API\Parameters\Handlers\BaseParamsHandlerContainer}.
 *
 * Provides type-narrowed overrides for {@see register()} and {@see getParam()}
 * so consumers receive a typed `AppCountryIDsParam` without casting.
 *
 * @package Countries
 * @subpackage API
 * @see AppCountryIDsParam
 */
class AppCountryIDsHandler extends BaseParamHandler
{
	/**
	 * Registers the parameter and returns it type-narrowed.
	 *
	 * @return AppCountryIDsParam
	 */
	public function register(): AppCountryIDsParam
	{
		/* ... */
	}


	/**
	 * Returns the registered parameter type-narrowed, or `null` if not yet registered.
	 *
	 * @return AppCountryIDsParam|null
	 */
	public function getParam(): ?AppCountryIDsParam
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/API/Params/AppCountryIDsParam.php`

```php
namespace Application\Countries\API\Params;

use Application\API\Parameters\Type\IDListParameter as IDListParameter;
use Application\AppFactory as AppFactory;
use Application\Countries\API\AppCountriesAPIInterface as AppCountriesAPIInterface;
use Application_Countries_Country as Application_Countries_Country;

/**
 * Country IDs parameter for the multi-country Countries API.
 *
 * Accepts one or more application country IDs as a comma-separated
 * string or array. Each ID is validated individually via
 * {@see AppCountryIDsValidation}, which produces a per-ID error message
 * identifying any IDs that do not exist.
 *
 * @package Countries
 * @subpackage API
 * @see AppCountryIDsHandler
 * @see AppCountriesParamInterface
 */
class AppCountryIDsParam extends IDListParameter implements AppCountriesParamInterface
{
	/**
	 * Returns the resolved country objects for each ID in the parameter value.
	 *
	 * Returns an empty array if the parameter has no value.
	 *
	 * @return Application_Countries_Country[]
	 */
	public function getCountries(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/API/Params/AppCountryIDsValidation.php`

```php
namespace Application\Countries\API\Params;

use AppUtils\OperationResult as OperationResult;
use Application\API\Parameters\APIParameterInterface as APIParameterInterface;
use Application\API\Parameters\Validation\BaseParamValidation as BaseParamValidation;
use Application\AppFactory as AppFactory;

/**
 * Validates each country ID in an ID list individually, producing
 * a per-ID error message that identifies which IDs do not exist.
 *
 * This is necessary because {@see \Application\API\Parameters\Validation\Type\ValueExistsCallbackValidation}
 * passes the entire resolved value (the `int[]` array) to the callback as a
 * single argument — it does not iterate per item. Using a custom validation
 * class gives consumers precise feedback on which IDs are invalid.
 *
 * @package Countries
 * @subpackage API
 * @see AppCountryIDsParam
 */
class AppCountryIDsValidation extends BaseParamValidation
{
	public const VALIDATION_COUNTRY_ID_NOT_EXISTS = 184801;

	public function validate(
		float|int|bool|array|string|null $value,
		OperationResult $result,
		APIParameterInterface $param,
	): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/API/Params/AppCountryISOHandler.php`

```php
namespace Application\Countries\API\Params;

use AppUtils\ClassHelper as ClassHelper;
use Application\API\Parameters\Handlers\BaseParamHandler as BaseParamHandler;
use Application_Countries_Country as Application_Countries_Country;

class AppCountryISOHandler extends BaseParamHandler
{
	public function register(): AppCountryISOParam
	{
		/* ... */
	}


	public function getParam(): ?AppCountryISOParam
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/API/Params/AppCountryISOParam.php`

```php
namespace Application\Countries\API\Params;

use Application\API\Parameters\Type\StringParameter as StringParameter;
use Application\API\Parameters\ValueLookup\SelectableParamValue as SelectableParamValue;
use Application\API\Parameters\ValueLookup\SelectableValueParamInterface as SelectableValueParamInterface;
use Application\API\Parameters\ValueLookup\SelectableValueParamTrait as SelectableValueParamTrait;
use Application\AppFactory as AppFactory;
use Application\Countries\API\AppCountryAPIInterface as AppCountryAPIInterface;
use Application_Countries_Country as Application_Countries_Country;

/**
 * Country ISO code parameter for the Countries API.
 *
 * > NOTE: This implements {@see AppCountryParamInterface}
 * > to provide access to the resolved country object.
 *
 * @package Countries
 * @subpackage API
 */
class AppCountryISOParam extends StringParameter implements SelectableValueParamInterface, AppCountryParamInterface
{
	use SelectableValueParamTrait;

	public function getCountry(): ?Application_Countries_Country
	{
		/* ... */
	}


	public function getDefaultSelectableValue(): ?SelectableParamValue
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/API/Params/AppCountryISOsHandler.php`

```php
namespace Application\Countries\API\Params;

use AppUtils\ClassHelper as ClassHelper;
use Application\API\Parameters\Handlers\BaseParamHandler as BaseParamHandler;
use Application_Countries_Country as Application_Countries_Country;

/**
 * Handler that bridges {@see AppCountryISOsParam} into a
 * {@see \Application\API\Parameters\Handlers\BaseParamsHandlerContainer}.
 *
 * Provides type-narrowed overrides for {@see register()} and {@see getParam()}
 * so consumers receive a typed `AppCountryISOsParam` without casting.
 *
 * @package Countries
 * @subpackage API
 * @see AppCountryISOsParam
 */
class AppCountryISOsHandler extends BaseParamHandler
{
	/**
	 * Registers the parameter and returns it type-narrowed.
	 *
	 * @return AppCountryISOsParam
	 */
	public function register(): AppCountryISOsParam
	{
		/* ... */
	}


	/**
	 * Returns the registered parameter type-narrowed, or `null` if not yet registered.
	 *
	 * @return AppCountryISOsParam|null
	 */
	public function getParam(): ?AppCountryISOsParam
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/API/Params/AppCountryISOsParam.php`

```php
namespace Application\Countries\API\Params;

use Application\API\Parameters\Type\StringListParameter as StringListParameter;
use Application\AppFactory as AppFactory;
use Application\Countries\API\AppCountriesAPIInterface as AppCountriesAPIInterface;
use Application_Countries_Country as Application_Countries_Country;

/**
 * Country ISO codes parameter for the multi-country Countries API.
 *
 * Accepts one or more two-letter country ISO codes as a comma-separated
 * string or array. Each ISO code is validated individually via
 * {@see AppCountryISOsValidation}, which produces a per-ISO error message
 * identifying any ISO codes that do not exist.
 *
 * @package Countries
 * @subpackage API
 * @see AppCountryISOsHandler
 * @see AppCountriesParamInterface
 */
class AppCountryISOsParam extends StringListParameter implements AppCountriesParamInterface
{
	/**
	 * Returns the resolved country objects for each ISO code in the parameter value.
	 *
	 * Returns an empty array if the parameter has no value.
	 *
	 * @return Application_Countries_Country[]
	 */
	public function getCountries(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/API/Params/AppCountryISOsValidation.php`

```php
namespace Application\Countries\API\Params;

use AppUtils\OperationResult as OperationResult;
use Application\API\Parameters\APIParameterInterface as APIParameterInterface;
use Application\API\Parameters\Validation\BaseParamValidation as BaseParamValidation;
use Application\AppFactory as AppFactory;

/**
 * Validates each country ISO code in a string list individually, producing
 * a per-ISO error message that identifies which ISO codes do not exist.
 *
 * This is necessary because {@see \Application\API\Parameters\Validation\Type\ValueExistsCallbackValidation}
 * passes the entire resolved value (the `string[]` array) to the callback as a
 * single argument — it does not iterate per item. Using a custom validation
 * class gives consumers precise feedback on which ISO codes are invalid.
 *
 * @package Countries
 * @subpackage API
 * @see AppCountryISOsParam
 */
class AppCountryISOsValidation extends BaseParamValidation
{
	public const VALIDATION_COUNTRY_ISO_NOT_EXISTS = 184802;

	public function validate(
		float|int|bool|array|string|null $value,
		OperationResult $result,
		APIParameterInterface $param,
	): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/API/Params/AppCountryParamInterface.php`

```php
namespace Application\Countries\API\Params;

use Application\API\Parameters\APIParameterInterface as APIParameterInterface;
use Application_Countries_Country as Application_Countries_Country;

/**
 * Interface for API parameters that represent a country.
 *
 * @package Countries
 * @subpackage API
 */
interface AppCountryParamInterface extends APIParameterInterface
{
	public function getCountry(): ?Application_Countries_Country;
}


```
---
**File Statistics**
- **Size**: 13.18 KB
- **Lines**: 462
File: `modules/countries/api/architecture-params.md`
