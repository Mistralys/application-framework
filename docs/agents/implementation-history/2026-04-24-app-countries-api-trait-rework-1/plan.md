# Plan: Post-Synthesis Rework — AppCountriesAPITrait Technical Debt & Recommendations

## Summary

Address all actionable items from the `2026-04-24-app-countries-api-trait` synthesis: extract a `ListParameterTrait` to eliminate code duplication between `IDListParameter` and `StringListParameter`, fix the `IDListParameter::selectValue()` return type, resolve constant duplication in the plural country parameter classes, fix the `AppCountriesRuleHandler` null-return contract violation, standardise the null-return contract at the base handler level, fix namespace inconsistencies in test driver classes, and add a missing docblock to `AppCountryParamsContainer`.

## Architectural Context

All changes target the API parameter infrastructure at `src/classes/Application/API/Parameters/` and the Countries API module at `src/classes/Application/Countries/API/`.

### Key files and patterns:

- **Parameter types** (`src/classes/Application/API/Parameters/Type/`): `IDListParameter` and `StringListParameter` both contain a byte-for-byte identical private `requireValidType()` method that handles `null → []`, array passthrough, `string → explodeTrim`, and type-error throwing.
- **Handler contract** (`src/classes/Application/API/Parameters/Handlers/BaseAPIHandler.php`): `resolveValueFromSubject()` is `abstract protected` with return type `mixed`. No PHPDoc documents the critical null-return contract used by `BaseParamsHandlerContainer::resolveValue()` which iterates handlers and uses `if($value !== null)` as the "did this handler contribute a value?" check.
- **Constant ownership**: The singular parameter classes (`AppCountryIDParam`, `AppCountryISOParam`) reference `AppCountryAPIInterface` constants directly. The plural parameter classes (`AppCountryIDsParam`, `AppCountryISOsParam`) declare their own duplicate constants.
- **Test drivers**: All test driver API classes under `tests/application/assets/classes/TestDriver/API/` use namespace `TestDriver\API` except `TestGetCountryBySetAPI.php` and `TestGetCountriesBySetAPI.php` which incorrectly use `application\assets\classes\TestDriver\API`.
- **`BaseAPIParameter::selectValue()`** returns `self`; `StringListParameter` correctly returns `self` but `IDListParameter` downcasts to `BaseAPIParameter`.
- **`AppCountryParamsContainer`** is missing class-level PHPDoc description and `@package`/`@subpackage` tags, unlike its plural counterpart and other handler containers.

## Approach / Architecture

Seven targeted corrections grouped into three tiers:

1. **Framework-level improvements** (Steps 1–2): `ListParameterTrait` extraction and `selectValue()` covariance fix — both in `src/classes/Application/API/Parameters/Type/`.
2. **Countries API corrections** (Steps 3–5): Constant deduplication, null-return fix, and docblock addition — all in `src/classes/Application/Countries/API/`.
3. **Null-return contract standardisation** (Step 6): PHPDoc update to `BaseAPIHandler::resolveValueFromSubject()` — codifies the "return null when no value" convention at the base level.
4. **Test hygiene** (Step 7): Namespace fix for two test driver files.

No new classes or interfaces are introduced. All changes are backward-compatible.

## Rationale

- **ListParameterTrait** eliminates a maintenance hazard: if the parsing logic changes, both classes must be updated in lockstep. A trait makes this impossible to forget and prepares the framework for future list-type parameters (e.g., `FloatListParameter`).
- **Constant deduplication** aligns the plural parameter classes with the singular pattern, which already references the interface constants directly. The param classes should not be the canonical owner because the interface is the contract consumed by API method implementors.
- **Null-return contract** was the root cause of a real bug discovered during WP-006 integration testing. Codifying it in the base class PHPDoc prevents future implementors from making the same mistake.
- **selectValue() return type** is a straightforward covariance fix that restores fluent interface typing for `IDListParameter` consumers.

## Detailed Steps

### Step 1: Extract `ListParameterTrait`

**Location:** `src/classes/Application/API/Parameters/Type/ListParameterTrait.php` (NEW)

Create a trait containing the shared `requireValidType()` method extracted from `IDListParameter` and `StringListParameter`:

```php
namespace Application\API\Parameters\Type;

use Application\API\Parameters\APIParameterException;
use AppUtils\ConvertHelper;

trait ListParameterTrait
{
    /**
     * Normalises a raw API input value into an array.
     *
     * - `null` → empty array
     * - `array` → passthrough
     * - `string` → comma-separated explode with trim
     * - anything else → throws
     *
     * @param mixed $value
     * @return array<int,string>
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
}
```

Then update both consumer classes:

**`IDListParameter.php`** (lines 82–102): Remove the private `requireValidType()` method and add `use ListParameterTrait;`.

**`StringListParameter.php`** (lines 123–143): Remove the private `requireValidType()` method and add `use ListParameterTrait;`.

Run `composer dump-autoload` after creating the new file (classmap autoloading).

### Step 2: Fix `IDListParameter::selectValue()` return type

**File:** `src/classes/Application/API/Parameters/Type/IDListParameter.php` (lines 49–55)

**Current:**
```php
public function selectValue(float|int|bool|array|string|null $value): BaseAPIParameter
```

**Change to:**
```php
public function selectValue(float|int|bool|array|string|null $value): self
```

Update the PHPDoc `@return` tag from `BaseAPIParameter` to `$this` to match `StringListParameter` and the base class convention.

### Step 3: Remove duplicate constants from plural parameter classes

**File:** `src/classes/Application/Countries/API/Params/AppCountryIDsParam.php` (lines 22–27)

Remove the local `PARAM_COUNTRY_IDS` constant declaration (and its PHPDoc). Update the constructor call to reference `AppCountriesAPIInterface::PARAM_COUNTRY_IDS` instead, matching the pattern used by the singular `AppCountryIDParam`.

**File:** `src/classes/Application/Countries/API/Params/AppCountryISOsParam.php` (lines 27–32)

Remove the local `PARAM_COUNTRY_ISOS` constant declaration (and its PHPDoc). Update the constructor call to reference `AppCountriesAPIInterface::PARAM_COUNTRY_ISOS` instead, matching the pattern used by the singular `AppCountryISOParam`.

Verify that no other file references `AppCountryIDsParam::PARAM_COUNTRY_IDS` or `AppCountryISOsParam::PARAM_COUNTRY_ISOS` — if any do, update them to use the interface constant.

### Step 4: Fix `AppCountriesRuleHandler::resolveValueFromSubject()` null-return

**File:** `src/classes/Application/Countries/API/ParamSets/AppCountriesRuleHandler.php` (lines 49–56)

**Current:**
```php
protected function resolveValueFromSubject(): array
{
    return $this->getRule()?->getCountries() ?? array();
}
```

**Change to:**
```php
/**
 * Returns `null` when no rule has been registered or the rule
 * resolves no countries, so that the
 * {@see BaseParamsHandlerContainer} "first non-null wins"
 * iteration can fall through to the next handler.
 *
 * @return Application_Countries_Country[]|null
 */
protected function resolveValueFromSubject(): ?array
{
    $rule = $this->getRule();

    if($rule === null) {
        return null;
    }

    $countries = $rule->getCountries();

    return empty($countries) ? null : $countries;
}
```

This aligns with the singular `AppCountryRuleHandler::resolveValueFromSubject()` which correctly returns `?Application_Countries_Country` (nullable).

Note: The `getCountries()` method on the rule's param set returns `Application_Countries_Country[]`. An empty array must map to `null` to honour the "first non-null wins" contract — a handler that found nothing should not block subsequent handlers.

### Step 5: Add class-level PHPDoc to `AppCountryParamsContainer`

**File:** `src/classes/Application/Countries/API/AppCountryParamsContainer.php`

**Current** (line 14):
```php
/**
 * @method AppCountryAPIInterface getMethod()
 */
```

**Change to:**
```php
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
```

### Step 6: Standardise the null-return contract in `BaseAPIHandler`

**File:** `src/classes/Application/API/Parameters/Handlers/BaseAPIHandler.php` (lines 85–90)

**Current PHPDoc:**
```php
/**
 * This is called when no value has been selected directly.
 * The value must be resolved from the parameter itself.
 *
 * @return mixed
 */
abstract protected function resolveValueFromSubject() : mixed;
```

**Change to:**
```php
/**
 * This is called when no value has been selected directly.
 * The value must be resolved from the parameter itself.
 *
 * **Null-return contract:** Implementations MUST return `null`
 * when the handler has no value to contribute (parameter absent,
 * value empty, or rule not registered). {@see BaseParamsHandlerContainer::resolveValue()}
 * iterates all registered handlers and uses "first non-null wins"
 * semantics — returning a non-null value (including an empty array)
 * will be treated as a successful resolution and prevent subsequent
 * handlers from being consulted.
 *
 * @return mixed The resolved value, or `null` if this handler has no value.
 */
abstract protected function resolveValueFromSubject() : mixed;
```

### Step 7: Fix test driver namespace inconsistencies

**File:** `tests/application/assets/classes/TestDriver/API/TestGetCountryBySetAPI.php` (line 9)

**Current:**
```php
namespace application\assets\classes\TestDriver\API;
```

**Change to:**
```php
namespace TestDriver\API;
```

Remove any now-unnecessary `use` imports that were only needed because of the wrong namespace.

**File:** `tests/application/assets/classes/TestDriver/API/TestGetCountriesBySetAPI.php` (line 9)

**Current:**
```php
namespace application\assets\classes\TestDriver\API;
```

**Change to:**
```php
namespace TestDriver\API;
```

Remove any now-unnecessary `use` imports that were only needed because of the wrong namespace.

Run `composer dump-autoload` after these changes (classmap autoloading).

### Step 8: Validate

1. Run `composer dump-autoload` to regenerate the classmap.
2. Run `composer test-filter -- StringListParameterTest` — all 18 tests must pass.
3. Run `composer test-filter -- AppCountriesAPITraitTest` — all 13 tests must pass.
4. Run `composer test-filter -- IDListParameter` — verify no regressions from trait extraction and return type change.
5. Run `composer test-filter -- CountryBySet` — verify no regressions from namespace fix.
6. Run `composer analyze` — verify PHPStan is clean or baseline-equivalent. Save results with `composer analyze-save` if the error count changes.

## Dependencies

- All steps are independent of each other and can be implemented in any order.
- Steps 1 and 2 both modify `IDListParameter.php` — apply them together or sequentially to the same file.
- Step 8 (validation) depends on all other steps being complete.

## Required Components

- `src/classes/Application/API/Parameters/Type/ListParameterTrait.php` — **NEW FILE**
- `src/classes/Application/API/Parameters/Type/IDListParameter.php` — modified (Steps 1, 2)
- `src/classes/Application/API/Parameters/Type/StringListParameter.php` — modified (Step 1)
- `src/classes/Application/Countries/API/Params/AppCountryIDsParam.php` — modified (Step 3)
- `src/classes/Application/Countries/API/Params/AppCountryISOsParam.php` — modified (Step 3)
- `src/classes/Application/Countries/API/ParamSets/AppCountriesRuleHandler.php` — modified (Step 4)
- `src/classes/Application/Countries/API/AppCountryParamsContainer.php` — modified (Step 5)
- `src/classes/Application/API/Parameters/Handlers/BaseAPIHandler.php` — modified (Step 6)
- `tests/application/assets/classes/TestDriver/API/TestGetCountryBySetAPI.php` — modified (Step 7)
- `tests/application/assets/classes/TestDriver/API/TestGetCountriesBySetAPI.php` — modified (Step 7)

## Assumptions

- The `ConvertHelper::explodeTrim()` method is already imported in both `IDListParameter` and `StringListParameter` — the trait will need its own `use` import statement.
- The `APIParameterException` class is already imported in both files — same applies to the trait.
- No external code references `AppCountryIDsParam::PARAM_COUNTRY_IDS` or `AppCountryISOsParam::PARAM_COUNTRY_ISOS` directly (all consumers use the interface constant).
- The test database is configured and available for running the integration tests.

## Constraints

- Always use `array()` syntax, never `[]` — project-wide rule.
- Run `composer dump-autoload` after creating new files or changing namespaces — classmap autoloading.
- Do not refactor beyond the scope of the listed items.

## Out of Scope

- Wiring `AppCountriesAPITrait` into a production consumer (e.g., `GetMailingsAPI`) — separate plan.
- Broader `@phpstan-return never` audit beyond the API parameters area — the two relevant base methods (`BaseAPIHandler::requireValue()` and `BaseParamsHandlerContainer::requireValue()`) already have the annotation. No additional missing annotations were found in the handlers area.
- Singular country parameter classes — already follow the correct patterns.
- New test cases — existing tests cover all modified code paths. Validation is regression-only.

## Acceptance Criteria

- `ListParameterTrait.php` exists and is used by both `IDListParameter` and `StringListParameter`; neither class contains a local `requireValidType()` method.
- `IDListParameter::selectValue()` returns `self`, not `BaseAPIParameter`.
- `AppCountryIDsParam` and `AppCountryISOsParam` have no local `PARAM_COUNTRY_*` constants; they reference `AppCountriesAPIInterface` constants.
- `AppCountriesRuleHandler::resolveValueFromSubject()` returns `null` (not `array()`) when no value is available.
- `BaseAPIHandler::resolveValueFromSubject()` PHPDoc documents the null-return contract.
- `AppCountryParamsContainer` has a class-level PHPDoc with description, `@package`, and `@subpackage`.
- `TestGetCountryBySetAPI` and `TestGetCountriesBySetAPI` use namespace `TestDriver\API`.
- All existing tests pass without modification.
- PHPStan analysis is clean or baseline-equivalent.

## Testing Strategy

Regression-only. No new tests are required — the existing test suites (`StringListParameterTest`, `AppCountriesAPITraitTest`, and any tests covering `IDListParameter` and the "BySet" test drivers) already exercise the modified code paths. The validation step (Step 8) runs targeted test filters to confirm no regressions.

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **Trait `use` visibility**: `requireValidType()` is `private` in both classes; a trait method with `private` visibility is only accessible to the class that uses the trait — this is correct PHP behaviour, but verify the trait compiles correctly. | Trait method stays `private`. PHP traits support private methods. Run tests to confirm. |
| **Constant removal breaks external consumers**: If any code references `AppCountryIDsParam::PARAM_COUNTRY_IDS` directly, removing the constant is a breaking change. | Grep the entire workspace (including HCP Editor) for references before removing. If references exist, add a deprecation `@deprecated` tag pointing to the interface constant instead of removing outright. |
| **Namespace change breaks test autoloading**: Changing the namespace of test driver classes could break test discovery if any test file references the old fully-qualified class name. | Grep for `application\assets\classes\TestDriver\API\TestGet` to find references. Run `composer dump-autoload` and targeted tests after the change. |
| **`AppCountriesRuleHandler` null-return changes OrRule behaviour**: Returning `null` instead of `array()` might change the resolution outcome when the OrRule is the sole handler and the rule resolves to an empty set. | Review the integration test `test_setIDsWinWhenBothAreProvided` — it validates that the rule handler correctly resolves values. An empty countries array from a valid rule evaluation should still return the array (non-null); only the "no rule registered" / "rule not triggered" case should return null. The implementation in Step 4 handles this distinction explicitly. |
