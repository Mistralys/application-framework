# Plan: Multi-Country API Trait (`AppCountriesAPITrait`)

## Summary

Add a reusable `AppCountriesAPITrait` to the Application Framework that allows API methods to accept multiple countries via either country IDs (`IDListParameter`) or ISO codes (`StringListParameter`), resolving them to an array of `Application_Countries_Country` records. An `OrRule` enforces mutual exclusivity: callers provide `countryIDs` **or** `countryISOs`, not both. This complements the existing `AppCountryAPITrait` (singular), which resolves a single country via `IntegerParameter` or `StringParameter`.

As a prerequisite, a new `StringListParameter` framework type is introduced — the string equivalent of the existing `IDListParameter`.

## Architectural Context

- The existing single-country infrastructure lives at `src/classes/Application/Countries/API/`:
  - `AppCountryAPIInterface` — defines `PARAM_COUNTRY_ID = 'countryID'` and `PARAM_COUNTRY_ISO = 'countryISO'`
  - `AppCountryAPITrait` — lazy-initializes `AppCountryParamsContainer`
  - `AppCountryParamsContainer` — extends `BaseParamsHandlerContainer`, manages three handlers (ID, ISO, OrRule), resolves to a **single** `Application_Countries_Country`
  - `Params/AppCountryIDParam` — extends `IntegerParameter`, validates via `AppFactory::createCountries()->idExists()`, resolves via `getCountryByID()`
  - `Params/AppCountryIDHandler` — extends `BaseParamHandler`, bridges parameter to container
  - `Params/AppCountryISOParam` — extends `StringParameter`, resolves via `getByISO()`
  - `Params/AppCountryISOHandler` — bridges ISO parameter to container
  - `ParamSets/AppCountryParamRule` — `OrRule` combining `CountryIDSet` and `CountryISOSet`
  - `ParamSets/AppCountryRuleHandler` — executes the OrRule
  - `ParamSets/CountryIDSet`, `CountryISOSet` — parameter sets for the OrRule
- The framework provides `IDListParameter` (`src/classes/Application/API/Parameters/Type/IDListParameter.php`) which accepts comma-separated strings or arrays, filters to `int[]`. **No `StringListParameter` equivalent exists yet** — it must be created.
- The `BaseParamsHandlerContainer` / `BaseParamHandler` pattern is the standard for trait-based parameter management.
- `BaseRuleHandler` (`src/classes/Application/API/Parameters/Handlers/BaseRuleHandler.php`) bridges `OrRule`-based mutual exclusivity into the container/handler architecture.
- All API trait infrastructure follows the same pattern: Trait → Container → Handler(s) → Param(s), with optional ParamSets + OrRule for mutual exclusivity.

## Approach / Architecture

Create a parallel set of classes mirroring the existing single-country pattern, but resolving to `Application_Countries_Country[]`:

- **Framework prerequisite:** `StringListParameter` — new reusable parameter type for `string[]` values (mirrors `IDListParameter`).
- `AppCountriesAPIInterface` (plural) — contract with `PARAM_COUNTRY_IDS = 'countryIDs'` and `PARAM_COUNTRY_ISOS = 'countryISOs'`.
- `AppCountriesAPITrait` — lazy container init.
- `AppCountriesParamsContainer` — manages three handlers (IDs, ISOs, OrRule), resolves to array of country records.
- `Params/AppCountryIDsParam` — `IDListParameter` subclass with per-ID existence validation.
- `Params/AppCountryIDsHandler` — bridges ID parameter to container.
- `Params/AppCountryISOsParam` — `StringListParameter` subclass with per-ISO existence validation.
- `Params/AppCountryISOsHandler` — bridges ISO parameter to container.
- `ParamSets/AppCountriesParamRule` — `OrRule` enforcing mutual exclusivity between IDs and ISOs.
- `ParamSets/AppCountriesRuleHandler` — bridges rule to container.
- `ParamSets/CountryIDsSet`, `CountryISOsSet` — parameter sets for the OrRule.
- `ParamSets/AppCountriesParamSetInterface`, `BaseAppCountriesParamSet` — contract and base class for parameter sets.

## Rationale

- **Reusable:** Multiple API methods (current: `GetMailingsAPI` in HCP Editor, future: any method needing multi-country filtering) benefit from a shared trait rather than manual parameter registration.
- **Parallel, not replacement:** The existing `AppCountryAPITrait` (singular) remains for methods that need exactly one country. Both traits can coexist on the same method.
- **Framework-level:** Country resolution is a framework concern (`Application_Countries_Country`), not application-specific. The trait belongs in the framework alongside its single-value counterpart.
- **IDs + ISOs with OrRule:** Mirrors the single-country pattern exactly. API consumers can filter by whichever identifier they have (numeric IDs or ISO codes like `de`, `en`, `fr`), but not both simultaneously.
- **`StringListParameter` as framework type:** Rather than embedding string-list parsing into `AppCountryISOsParam`, a reusable `StringListParameter` is provided at the framework level for any future string-list parameters.

## Detailed Steps

### Step 1: Create `AppCountriesAPIInterface.php`

**Path:** `src/classes/Application/Countries/API/AppCountriesAPIInterface.php`

Defines the contract for API methods that accept multiple country IDs.

```php
namespace Application\Countries\API;

interface AppCountriesAPIInterface extends APIMethodInterface
{
    public const string PARAM_COUNTRY_IDS = 'countryIDs';
    public const string PARAM_COUNTRY_ISOS = 'countryISOs';

    public function manageAppCountriesParams(): AppCountriesParamsContainer;
}
```

> **Parameter names:** `countryIDs` and `countryISOs` (plural) to distinguish from the single-value `countryID` / `countryISO` in `AppCountryAPIInterface`.

### Step 2: Create `AppCountriesAPITrait.php`

**Path:** `src/classes/Application/Countries/API/AppCountriesAPITrait.php`

Lazy-initializes the container. Follows the exact pattern of `AppCountryAPITrait`.

```php
namespace Application\Countries\API;

trait AppCountriesAPITrait
{
    private ?AppCountriesParamsContainer $appCountriesParamsContainer = null;

    public function manageAppCountriesParams(): AppCountriesParamsContainer
    {
        if (!isset($this->appCountriesParamsContainer)) {
            $this->appCountriesParamsContainer = new AppCountriesParamsContainer($this);
        }
        return $this->appCountriesParamsContainer;
    }
}
```

### Step 3: Create `AppCountriesParamsContainer.php`

**Path:** `src/classes/Application/Countries/API/AppCountriesParamsContainer.php`

Manages three handlers (IDs, ISOs, OrRule) and resolves to an array of country records.

```php
namespace Application\Countries\API;

/**
 * @method AppCountriesAPIInterface getMethod()
 */
class AppCountriesParamsContainer extends BaseParamsHandlerContainer
{
    public function __construct(AppCountriesAPIInterface $method)
    {
        parent::__construct($method);
    }

    /**
     * @return Application_Countries_Country[]
     */
    public function resolveValue(): array
    {
        $value = parent::resolveValue();

        if(is_array($value)) {
            return $value;
        }

        return array();
    }

    /**
     * @return Application_Countries_Country[]
     */
    public function requireValue(): array
    {
        $value = parent::requireValue();

        if(is_array($value)) {
            return $value;
        }

        return array();
    }

    /**
     * @param Application_Countries_Country[] $countries
     * @return $this
     */
    public function selectAppCountries(array $countries) : self
    {
        return $this->selectValue($countries);
    }

    public function manageIDs(): AppCountryIDsHandler
    {
        // Lazy-init the IDs handler
    }

    public function manageISOs(): AppCountryISOsHandler
    {
        // Lazy-init the ISOs handler
    }

    public function manageAllParamsRule(): AppCountriesRuleHandler
    {
        // Lazy-init the OrRule handler
    }

    protected function isValidValueType(float|object|array|bool|int|string $value): bool
    {
        return is_array($value);
    }
}
```

### Step 4: Create `StringListParameter.php`

**Path:** `src/classes/Application/API/Parameters/Type/StringListParameter.php`

New reusable framework parameter type for `string[]` values. Mirrors `IDListParameter` but preserves string values instead of casting to `int`. Accepts comma-separated strings or arrays, trims whitespace, and filters out empty strings.

```php
namespace Application\API\Parameters\Type;

class StringListParameter extends BaseAPIParameter
{
    public function getTypeLabel(): string
    {
        return t('String List');
    }

    /**
     * @return string[]
     */
    public function getDefaultValue(): array
    {
        return $this->defaultValue ?? array();
    }

    /**
     * @param array<int|string,string>|string|NULL $default An array of strings or a comma-separated string. Set to `NULL` to reset to an empty array. Other value types are ignored.
     * @return $this
     */
    public function setDefaultValue(int|float|bool|string|array|null $default) : self
    {
        return parent::setDefaultValue($this->filterValues($this->requireValidType($default)));
    }

    /**
     * @param array<int|string,string>|string|null $value
     * @return BaseAPIParameter
     * @throws APIParameterException
     */
    public function selectValue(float|int|bool|array|string|null $value): BaseAPIParameter
    {
        return parent::selectValue($this->filterValues($this->requireValidType($value)));
    }

    /**
     * @param array<int|string,mixed> $values
     * @return string[]
     */
    private function filterValues(array $values) : array
    {
        $result = array();

        foreach($values as $item)
        {
            $item = trim(toString($item));
            if($item !== '') {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * @param mixed $value
     * @return array<int|string,mixed>
     * @throws APIParameterException {@see APIParameterException::ERROR_INVALID_PARAM_VALUE}
     */
    private function requireValidType(mixed $value) : array
    {
        if($value === null) {
            return array();
        }

        if(is_array($value)) {
            return $value;
        }

        if(is_string($value)) {
            return ConvertHelper::explodeTrim(',', $value);
        }

        throw new APIParameterException(
            'Invalid parameter value.',
            sprintf(
                'Expected an array or comma-separated string, given: [%s].',
                gettype($value)
            ),
            APIParameterException::ERROR_INVALID_PARAM_VALUE
        );
    }

    /**
     * @return string[]|null
     */
    protected function resolveValue(): array|null
    {
        $value = $this->getRequestParam()->get();

        if($value === null) {
            return null;
        }

        if(!is_array($value) && !is_string($value)) {
            $this->result->makeWarning(
                'Ignoring non-array, non-string value.',
                ParamValidationInterface::VALIDATION_INVALID_VALUE_TYPE
            );
            return null;
        }

        if(is_string($value)) {
            $value = ConvertHelper::explodeTrim(',', $value);
        }

        $result = array();
        foreach($value as $item) {
            $item = trim(toString($item));
            if($item !== '') {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * @return string[]|null
     */
    public function getValue(): ?array
    {
        $value = parent::getValue();
        if(is_array($value)) {
            return $value;
        }

        return null;
    }
}
```

### Step 5: Create `Params/AppCountryIDsValidation.php`

**Path:** `src/classes/Application/Countries/API/Params/AppCountryIDsValidation.php`

Custom `ParamValidationInterface` implementation that validates each ID individually and produces per-ID error messages. This is necessary because `validateByValueExistsCallback` passes the entire resolved value (the `int[]` array) to the callback as a single argument — it does not iterate per item. A custom validation class gives consumers precise feedback on which IDs are invalid.

```php
namespace Application\Countries\API\Params;

use Application\API\Parameters\Validation\BaseParamValidation;

class AppCountryIDsValidation extends BaseParamValidation
{
    public const int VALIDATION_COUNTRY_ID_NOT_EXISTS = XXXXX; // obtain unique error code

    public function validate(float|int|bool|array|string|null $value, OperationResult $result, APIParameterInterface $param): void
    {
        if(!is_array($value) || empty($value)) {
            return;
        }

        $countries = AppFactory::createCountries();
        $invalid = array();

        foreach($value as $id) {
            if(!$countries->idExists((int)$id)) {
                $invalid[] = $id;
            }
        }

        if(!empty($invalid)) {
            $result->makeError(
                sprintf(
                    'The following country IDs do not exist for parameter `%s`: %s',
                    $param->getName(),
                    implode(', ', $invalid)
                ),
                self::VALIDATION_COUNTRY_ID_NOT_EXISTS
            );
        }
    }
}
```

### Step 6: Create `Params/AppCountriesParamInterface.php`

**Path:** `src/classes/Application/Countries/API/Params/AppCountriesParamInterface.php`

Shared interface for parameter classes that resolve to multiple countries. Mirrors `AppCountryParamInterface` (singular) for pattern consistency.

```php
namespace Application\Countries\API\Params;

use Application\API\Parameters\APIParameterInterface;
use Application_Countries_Country;

interface AppCountriesParamInterface extends APIParameterInterface
{
    /**
     * @return Application_Countries_Country[]
     */
    public function getCountries(): array;
}
```

### Step 7: Create `Params/AppCountryIDsParam.php`

**Path:** `src/classes/Application/Countries/API/Params/AppCountryIDsParam.php`

Extends `IDListParameter`. Implements `AppCountriesParamInterface`. Uses the custom `AppCountryIDsValidation` for per-ID existence checks.

```php
namespace Application\Countries\API\Params;

class AppCountryIDsParam extends IDListParameter implements AppCountriesParamInterface
{
    public function __construct()
    {
        parent::__construct(AppCountriesAPIInterface::PARAM_COUNTRY_IDS, 'Country IDs');
        $this->setDescription('One or more application country IDs, as a comma-separated list or array.');
        $this->validateBy(new AppCountryIDsValidation());
    }

    /**
     * @return Application_Countries_Country[]
     */
    public function getCountries(): array
    {
        $result = array();
        foreach ($this->getValue() as $id) {
            $result[] = AppFactory::createCountries()->getCountryByID($id);
        }
        return $result;
    }
}
```

### Step 8: Create `Params/AppCountryIDsHandler.php`

**Path:** `src/classes/Application/Countries/API/Params/AppCountryIDsHandler.php`

Bridges the parameter to the container. Follows the `AppCountryIDHandler` pattern, including type-narrowing overrides for `register()` and `getParam()`.

```php
namespace Application\Countries\API\Params;

use Application\API\Parameters\Handlers\BaseParamHandler;
use AppUtils\ClassHelper;

class AppCountryIDsHandler extends BaseParamHandler
{
    /**
     * @return Application_Countries_Country[]
     */
    protected function resolveValueFromSubject(): array
    {
        return $this->getParam()?->getCountries() ?? array();
    }

    public function register(): AppCountryIDsParam
    {
        return ClassHelper::requireObjectInstanceOf(
            AppCountryIDsParam::class,
            parent::register()
        );
    }

    public function getParam(): ?AppCountryIDsParam
    {
        $param = parent::getParam();
        if ($param instanceof AppCountryIDsParam) {
            return $param;
        }

        return null;
    }

    protected function createParam(): AppCountryIDsParam
    {
        return new AppCountryIDsParam();
    }
}
```

### Step 9: Create `Params/AppCountryISOsValidation.php`

**Path:** `src/classes/Application/Countries/API/Params/AppCountryISOsValidation.php`

Custom per-ISO validation. Mirrors `AppCountryIDsValidation` but uses `isoExists()` and reports invalid ISO codes individually.

```php
namespace Application\Countries\API\Params;

use Application\API\Parameters\Validation\BaseParamValidation;

class AppCountryISOsValidation extends BaseParamValidation
{
    public const int VALIDATION_COUNTRY_ISO_NOT_EXISTS = XXXXX; // obtain unique error code

    public function validate(float|int|bool|array|string|null $value, OperationResult $result, APIParameterInterface $param): void
    {
        if(!is_array($value) || empty($value)) {
            return;
        }

        $countries = AppFactory::createCountries();
        $invalid = array();

        foreach($value as $iso) {
            if(!$countries->isoExists((string)$iso)) {
                $invalid[] = $iso;
            }
        }

        if(!empty($invalid)) {
            $result->makeError(
                sprintf(
                    'The following country ISO codes do not exist for parameter `%s`: %s',
                    $param->getName(),
                    implode(', ', $invalid)
                ),
                self::VALIDATION_COUNTRY_ISO_NOT_EXISTS
            );
        }
    }
}
```

### Step 10: Create `Params/AppCountryISOsParam.php`

**Path:** `src/classes/Application/Countries/API/Params/AppCountryISOsParam.php`

Extends `StringListParameter`. Uses the custom `AppCountryISOsValidation` for per-ISO existence checks.

```php
namespace Application\Countries\API\Params;

class AppCountryISOsParam extends StringListParameter implements AppCountriesParamInterface
{
    public function __construct()
    {
        parent::__construct(AppCountriesAPIInterface::PARAM_COUNTRY_ISOS, 'Country ISOs');
        $this->setDescription('One or more two-letter country ISO codes (e.g. de, en, fr), as a comma-separated list or array. Case insensitive.');
        $this->validateBy(new AppCountryISOsValidation());
    }

    /**
     * @return Application_Countries_Country[]
     */
    public function getCountries(): array
    {
        $result = array();
        foreach ($this->getValue() as $iso) {
            $result[] = AppFactory::createCountries()->getByISO($iso);
        }
        return $result;
    }
}
```

### Step 11: Create `Params/AppCountryISOsHandler.php`

**Path:** `src/classes/Application/Countries/API/Params/AppCountryISOsHandler.php`

Bridges the ISO parameter to the container. Follows the `AppCountryISOHandler` pattern, including type-narrowing overrides for `register()` and `getParam()`.

```php
namespace Application\Countries\API\Params;

use Application\API\Parameters\Handlers\BaseParamHandler;
use AppUtils\ClassHelper;

class AppCountryISOsHandler extends BaseParamHandler
{
    /**
     * @return Application_Countries_Country[]
     */
    protected function resolveValueFromSubject(): array
    {
        return $this->getParam()?->getCountries() ?? array();
    }

    public function register(): AppCountryISOsParam
    {
        return ClassHelper::requireObjectInstanceOf(
            AppCountryISOsParam::class,
            parent::register()
        );
    }

    public function getParam(): ?AppCountryISOsParam
    {
        $param = parent::getParam();
        if ($param instanceof AppCountryISOsParam) {
            return $param;
        }

        return null;
    }

    protected function createParam(): AppCountryISOsParam
    {
        return new AppCountryISOsParam();
    }
}
```

### Step 12: Create ParamSets infrastructure

Mirror the single-country ParamSets pattern exactly. Create 4 files under `src/classes/Application/Countries/API/ParamSets/`.

#### `AppCountriesParamSetInterface.php`

Contract for parameter sets that resolve to an array of countries. Includes `@method` annotation for `getMethod()` return type narrowing.

```php
namespace Application\Countries\API\ParamSets;

use Application\API\Parameters\Rules\CustomParamSetInterface;
use Application\Countries\API\AppCountriesAPIInterface;

/**
 * @method AppCountriesAPIInterface getMethod()
 */
interface AppCountriesParamSetInterface extends CustomParamSetInterface
{
    /**
     * @return Application_Countries_Country[]
     */
    public function getCountries(): array;
}
```

#### `BaseAppCountriesParamSet.php`

Abstract base class for parameter sets. Includes `@method` annotation for `getMethod()` return type narrowing.

```php
namespace Application\Countries\API\ParamSets;

use Application\API\Parameters\Rules\BaseCustomParamSet;
use Application\Countries\API\AppCountriesAPIInterface;

/**
 * @method AppCountriesAPIInterface getMethod()
 */
abstract class BaseAppCountriesParamSet extends BaseCustomParamSet implements AppCountriesParamSetInterface
{
    public function __construct(AppCountriesAPIInterface $method)
    {
        parent::__construct($method);
    }
}
```

#### `CountryIDsSet.php`

Registers the IDs parameter and resolves countries from IDs.

```php
namespace Application\Countries\API\ParamSets;

class CountryIDsSet extends BaseAppCountriesParamSet
{
    public const string SET_NAME = 'CountryIDs';

    private AppCountryIDsParam $param;

    public function getCountries(): array
    {
        return $this->param->getCountries();
    }

    protected function initParams(): void
    {
        $this->param = $this->getMethod()->manageAppCountriesParams()->manageIDs()->register();
        $this->registerParam($this->param);
    }

    protected function _getID(): string
    {
        return self::SET_NAME;
    }
}
```

#### `CountryISOsSet.php`

Registers the ISOs parameter and resolves countries from ISO codes.

```php
namespace Application\Countries\API\ParamSets;

class CountryISOsSet extends BaseAppCountriesParamSet
{
    public const string SET_NAME = 'CountryISOs';

    private AppCountryISOsParam $param;

    public function getCountries(): array
    {
        return $this->param->getCountries();
    }

    protected function initParams(): void
    {
        $this->param = $this->getMethod()->manageAppCountriesParams()->manageISOs()->register();
        $this->registerParam($this->param);
    }

    protected function _getID(): string
    {
        return self::SET_NAME;
    }
}
```

### Step 13: Create `ParamSets/AppCountriesParamRule.php`

**Path:** `src/classes/Application/Countries/API/ParamSets/AppCountriesParamRule.php`

OrRule combining IDs and ISOs. Mirrors `AppCountryParamRule` (singular) but resolves to `Application_Countries_Country[]`. Includes `addSet()` override enforcing `AppCountriesParamSetInterface` type safety.

```php
namespace Application\Countries\API\ParamSets;

use Application\API\Parameters\ParamSetInterface;
use Application\API\Parameters\Rules\Type\OrRule;
use Application\Countries\API\CountryAPIException;

/**
 * @method AppCountriesParamSetInterface|NULL getValidSet()
 * @method AppCountriesParamSetInterface requireValidSet()
 */
class AppCountriesParamRule extends OrRule
{
    public function __construct(AppCountriesAPIInterface $method)
    {
        parent::__construct('Selecting countries');

        $this
            ->addSet(new CountryIDsSet($method))
            ->addSet(new CountryISOsSet($method));
    }

    /**
     * @param AppCountriesParamSetInterface|ParamSetInterface $set Only {@see AppCountriesParamSetInterface} instances are allowed.
     * @return $this
     */
    public function addSet(AppCountriesParamSetInterface|ParamSetInterface $set): self
    {
        if($set instanceof AppCountriesParamSetInterface) {
            return parent::addSet($set);
        }

        throw new CountryAPIException(
            'Not a countries API parameter set.',
            sprintf(
                'The param set is of type %s, but must implement %s.',
                get_class($set),
                AppCountriesParamSetInterface::class
            ),
            CountryAPIException::INVALID_PARAM_SET
        );
    }

    /**
     * @return Application_Countries_Country[]
     */
    public function getCountries(): array
    {
        $set = $this->getValidSet();
        if($set instanceof AppCountriesParamSetInterface) {
            return $set->getCountries();
        }
        return array();
    }
}
```

### Step 14: Create `ParamSets/AppCountriesRuleHandler.php`

**Path:** `src/classes/Application/Countries/API/ParamSets/AppCountriesRuleHandler.php`

Bridges the OrRule into the container's handler architecture. Mirrors `AppCountryRuleHandler`, including type-narrowing overrides for `resolveValue()` and `getRule()`.

```php
namespace Application\Countries\API\ParamSets;

use Application\API\Parameters\Handlers\BaseRuleHandler;
use Application\Countries\API\AppCountriesAPIInterface;

/**
 * @method AppCountriesAPIInterface getMethod()
 */
class AppCountriesRuleHandler extends BaseRuleHandler
{
    public function __construct(AppCountriesAPIInterface $method)
    {
        parent::__construct($method);
    }

    /**
     * @return Application_Countries_Country[]
     */
    public function resolveValue(): array
    {
        $value = parent::resolveValue();

        if(is_array($value)) {
            return $value;
        }

        return array();
    }

    /**
     * @return Application_Countries_Country[]
     */
    protected function resolveValueFromSubject(): array
    {
        return $this->getRule()?->getCountries() ?? array();
    }

    public function getRule(): ?AppCountriesParamRule
    {
        $rule = parent::getRule();

        if ($rule instanceof AppCountriesParamRule) {
            return $rule;
        }

        return null;
    }

    protected function createRule(): AppCountriesParamRule
    {
        return new AppCountriesParamRule($this->getMethod());
    }
}
```

### Step 15: Register in autoloader

Run `composer dump-autoload` after adding all new class files (classmap autoloading).

### Step 16: Write PHPUnit tests

Two test files plus two test API method fixture stubs:

#### `tests/application/assets/classes/TestDriver/API/TestGetCountriesAPI.php`

Test API method stub that registers IDs and ISOs parameters individually (no OrRule), so each can be tested in isolation. Mirrors `TestGetCountryAPI` (singular).

#### `tests/application/assets/classes/TestDriver/API/TestGetCountriesBySetAPI.php`

Test API method stub that uses the OrRule via `manageAllParamsRule()->register()`. Mirrors `TestGetCountryBySetAPI` (singular).

#### `tests/AppFrameworkTests/API/StringListParameterTest.php`

| Test | Description |
|------|-------------|
| Comma-separated string | `"de,en,fr"` → `['de', 'en', 'fr']` |
| Array input | `['de', 'en']` → `['de', 'en']` |
| Empty/null input | Returns `null` (not provided) |
| Whitespace trimming | `" de , en "` → `['de', 'en']` |
| Empty strings filtered | `"de,,en"` → `['de', 'en']` |
| Non-string/non-array input | Warning, returns `null` |
| setDefaultValue with string | `setDefaultValue("a,b,c")` → default is `['a', 'b', 'c']` |
| setDefaultValue with array | `setDefaultValue(['a', 'b'])` → default is `['a', 'b']` |
| setDefaultValue with null | `setDefaultValue(null)` → default is `[]` |
| setDefaultValue with invalid type | Throws `APIParameterException` |
| selectValue with string | `selectValue("x,y")` → selected is `['x', 'y']` |
| selectValue with array | `selectValue(['x', 'y'])` → selected is `['x', 'y']` |

#### `tests/AppFrameworkTests/Countries/AppCountriesAPITraitTest.php`

| Test | Description |
|------|-------------|
| Single country ID | Resolves to one-element array |
| Multiple country IDs | Comma-separated list resolves to array of country records |
| Single country ISO | Resolves to one-element array |
| Multiple country ISOs | `"de,en,fr"` resolves to array of country records |
| No parameters | Returns empty array |
| Invalid country ID | Per-ID error message naming the invalid ID |
| Invalid country ISO | Per-ISO error message naming the invalid ISO |
| Mixed valid/invalid IDs | Validation rejects the request, lists invalid IDs |
| Mixed valid/invalid ISOs | Validation rejects the request, lists invalid ISOs |
| Both IDs and ISOs provided | OrRule resolves from the first registered set (IDs) |
| ISO case insensitivity | `"DE"` resolves the same as `"de"` |
| Countries can be selected manually | `selectAppCountries()` sets the value without request params |

## Dependencies

- `BaseParamsHandlerContainer`, `BaseParamHandler` — framework API parameter infrastructure (exist)
- `BaseRuleHandler`, `OrRule`, `BaseCustomParamSet`, `CustomParamSetInterface` — framework rule/param-set infrastructure (exist)
- `IDListParameter` — framework parameter type for `int[]` (exists)
- `AppFactory::createCountries()` — country collection access (exists), provides `idExists()`, `isoExists()`, `getCountryByID()`, `getByISO()`
- `Application_Countries_Country` — country record class (exists)
- `CountryAPIException` — existing exception class for country API errors (exists at `src/classes/Application/Countries/API/CountryAPIException.php`)

## Required Components

### New files

| File | Description |
|------|-------------|
| `src/classes/Application/API/Parameters/Type/StringListParameter.php` | **Framework type:** Reusable `string[]` parameter (mirrors `IDListParameter`) |
| `src/classes/Application/Countries/API/AppCountriesAPIInterface.php` | Interface for multi-country API methods |
| `src/classes/Application/Countries/API/AppCountriesAPITrait.php` | Trait providing multi-country parameter management |
| `src/classes/Application/Countries/API/AppCountriesParamsContainer.php` | Container managing IDs, ISOs, and OrRule handlers |
| `src/classes/Application/Countries/API/Params/AppCountriesParamInterface.php` | Shared interface for multi-country parameter classes |
| `src/classes/Application/Countries/API/Params/AppCountryIDsValidation.php` | Custom per-ID existence validation with individual error messages |
| `src/classes/Application/Countries/API/Params/AppCountryIDsParam.php` | `IDListParameter` subclass for country IDs |
| `src/classes/Application/Countries/API/Params/AppCountryIDsHandler.php` | Handler bridging ID parameter to container |
| `src/classes/Application/Countries/API/Params/AppCountryISOsValidation.php` | Custom per-ISO existence validation with individual error messages |
| `src/classes/Application/Countries/API/Params/AppCountryISOsParam.php` | `StringListParameter` subclass for country ISO codes |
| `src/classes/Application/Countries/API/Params/AppCountryISOsHandler.php` | Handler bridging ISO parameter to container |
| `src/classes/Application/Countries/API/ParamSets/AppCountriesParamSetInterface.php` | Contract for multi-country parameter sets |
| `src/classes/Application/Countries/API/ParamSets/BaseAppCountriesParamSet.php` | Abstract base class for parameter sets |
| `src/classes/Application/Countries/API/ParamSets/CountryIDsSet.php` | Parameter set registering country IDs |
| `src/classes/Application/Countries/API/ParamSets/CountryISOsSet.php` | Parameter set registering country ISOs |
| `src/classes/Application/Countries/API/ParamSets/AppCountriesParamRule.php` | OrRule combining IDs and ISOs |
| `src/classes/Application/Countries/API/ParamSets/AppCountriesRuleHandler.php` | Handler bridging OrRule to container |
| `tests/application/assets/classes/TestDriver/API/TestGetCountriesAPI.php` | Test fixture: API method with optional multi-country params |
| `tests/application/assets/classes/TestDriver/API/TestGetCountriesBySetAPI.php` | Test fixture: API method using OrRule param set |
| `tests/AppFrameworkTests/API/StringListParameterTest.php` | Tests for `StringListParameter` |
| `tests/AppFrameworkTests/Countries/AppCountriesAPITraitTest.php` | Tests for the multi-country trait |

### Modified files

None.

## Assumptions

- `BaseParamsHandlerContainer` can resolve to array types (not only single objects). The `isValidValueType()` override returning `is_array($value)` is sufficient. (Verified: `resolveValue()` returns `mixed`, `requireValue()` accepts `array` in its union.)
- `validateByValueExistsCallback` passes the entire resolved value to the callback as one argument. For list parameters this is `int[]` or `string[]`, so custom `ParamValidationInterface` implementations are needed to validate each item individually (see Steps 5 and 9).
- `IDListParameter::getValue()` returns `int[]` after filtering — no additional type conversion needed.
- The new `StringListParameter::getValue()` will return `string[]|null` following the same nullable pattern as `IDListParameter`.
- `Application_Countries::isoExists()` handles case-insensitive matching (verified: it normalizes via `CountryCollection::filterCode()`).

## Constraints

- **PHP 8.4+** (framework requirement).
- **Use `array()` syntax**, not `[]` (hard project rule).
- **Run `composer dump-autoload`** after adding new class files (classmap autoloading).
- Follow the existing single-country trait pattern exactly — do not introduce new abstractions or deviate from the Container → Handler → Param architecture.

## Out of Scope

- **Modifying the existing single-country trait.** `AppCountryAPITrait`, `AppCountryParamsContainer`, etc. are untouched.
- **Application-level API methods.** The consuming `GetMailingsAPI` method is planned separately in the HCP Editor.

## Acceptance Criteria

1. **`StringListParameter` exists** at `src/classes/Application/API/Parameters/Type/StringListParameter.php` and handles comma-separated strings, arrays, whitespace trimming, empty-string filtering, `setDefaultValue()`, and `selectValue()`.
2. **`AppCountriesAPITrait` exists** and follows the same lazy-init pattern as `AppCountryAPITrait`.
3. **`AppCountriesParamsContainer` resolves correctly:** Single ID → one-element array. Comma-separated IDs → multi-element array. Single ISO → one-element array. Comma-separated ISOs → multi-element array. No parameters → empty array.
4. **Container provides type-narrowed `requireValue()` and `selectAppCountries()`.** `requireValue()` returns `array`. `selectAppCountries()` sets the value without request params.
5. **Validation rejects invalid IDs:** Non-existent country IDs produce an error response listing the specific invalid IDs.
6. **Validation rejects invalid ISOs:** Non-existent ISO codes produce an error response listing the specific invalid ISOs.
7. **OrRule enforces mutual exclusivity:** Providing both `countryIDs` and `countryISOs` resolves from the first registered set (IDs).
8. **Parameter names are `countryIDs` and `countryISOs`** (plural), distinct from the existing `countryID` / `countryISO`.
9. **Handler classes provide type-narrowed `register()` and `getParam()` overrides**, returning the concrete param types (`AppCountryIDsParam`, `AppCountryISOsParam`).
10. **`AppCountriesRuleHandler` provides type-narrowed `resolveValue()` and `getRule()` overrides**, returning `array` and `?AppCountriesParamRule` respectively.
11. **`AppCountriesParamRule::addSet()` enforces `AppCountriesParamSetInterface`** — rejects non-conforming param sets with `CountryAPIException`.
12. **`@method` annotations present** on container, rule handler, base param set, and param set interface for `getMethod()` return type narrowing.
13. **All existing framework tests pass** after the changes.
14. **`StringListParameterTest` covers** comma-separated, array, null, whitespace, empty-string, `setDefaultValue()`, and `selectValue()` scenarios.
15. **`AppCountriesAPITraitTest` covers** ID and ISO resolution, validation error messages, OrRule behavior, ISO case insensitivity, and manual country selection.
16. **Autoloader updated** via `composer dump-autoload`.

## Testing Strategy

- `tests/AppFrameworkTests/API/StringListParameterTest.php` — unit tests for the new framework parameter type.
  - Run with: `composer test-filter -- StringListParameter`
- `tests/AppFrameworkTests/Countries/AppCountriesAPITraitTest.php` — integration tests for the multi-country trait.
  - Run with: `composer test-filter -- AppCountriesAPI`
- After implementation, also run: `composer test-filter -- Countries` to catch any regressions in the country module.

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **Custom validation error code collision** | `AppCountryIDsValidation` and `AppCountryISOsValidation` each need a unique error code constant. Obtain them from the error code service before implementation. |
| **`StringListParameter` scope creep** | Keep it minimal — mirror `IDListParameter` behavior but for strings. No encoding, no max-length, no pattern validation. Those can be layered via `validateBy()` by consumers. |

### Resolved Design Notes

The following items were identified during plan audit and resolved:

- **Array resolution in `BaseParamsHandlerContainer`:** Verified safe. The base class's `resolveValue()` returns `mixed` and `requireValue()` accepts `array` in its return union. The `isValidValueType()` override returning `is_array($value)` works natively — no workaround needed.
- **Per-item validation:** `validateByValueExistsCallback` passes the entire resolved value (`int[]` or `string[]`) as one argument — it does not iterate per item. Resolved by introducing custom validation classes (`AppCountryIDsValidation` in Step 5, `AppCountryISOsValidation` in Step 9) that loop over items and produce per-item error messages identifying exactly which IDs/ISOs are invalid.
