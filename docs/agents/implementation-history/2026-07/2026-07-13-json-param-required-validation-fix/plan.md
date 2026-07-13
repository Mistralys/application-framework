# Plan

## Plan Audit Cycles
- Audits: 2 — Plan Auditor v1.5.0
- Architectural Reviews: none — Plan Architect Reviewer v2.0.0

## Summary

Fix `RequiredValidation::validate()` so that an empty PHP array (`array()`) is treated as a provided value rather than a missing one. Currently, `empty($value)` returns `true` for `array()`, which causes `JSONParameter` marked `makeRequired()` to incorrectly fire `VALIDATION_EMPTY_REQUIRED_PARAM` when the caller sends a valid but empty JSON array (`[]`). The fix adds a type-based guard: any array value — including an empty one — passes the required check. Domain-specific "empty not allowed" validation remains the responsibility of the API method itself.

## Architectural Context

The API parameter system lives in `src/classes/Application/API/Parameters/`:

- **`BaseAPIParameter`** (`src/classes/Application/API/Parameters/BaseAPIParameter.php`) — abstract base for all parameter types; provides `makeRequired()`, `isRequired()`, and the validation pipeline.
- **`JSONParameter`** (`src/classes/Application/API/Parameters/Type/JSONParameter.php`) — accepts a JSON string from the request, decodes it to a PHP array via `resolveValue()`. Returns `null` for absent/invalid values.
- **`RequiredValidation`** (`src/classes/Application/API/Parameters/Validation/Type/RequiredValidation.php`) — one of several validation classes that run post-resolution. Checks whether the resolved value is "present." Uses `empty($value)` with exemptions for `0`, `'0'`, and `false`.
- **`ParamValidationInterface`** (`src/classes/Application/API/Parameters/Validation/ParamValidationInterface.php`) — defines the `validate()` contract and error code constants.
- **`BaseParamValidation`** (`src/classes/Application/API/Parameters/Validation/BaseParamValidation.php`) — abstract base implementing `ParamValidationInterface`.

Existing tests:
- `tests/AppFrameworkTests/API/RequiredTest.php` — 3 tests covering `StringParameter` required behaviour.
- `tests/AppFrameworkTests/API/Parameters/JSONParamTest.php` — 7 tests covering JSON decoding, type rejection, empty/null values, and defaults. No required-mode tests exist.

Test base class: `APITestCase` (`tests/AppFrameworkTestClasses/API/APITestCase.php`), which extends `ApplicationTestCase`.

## Approach / Architecture

Add an early-return guard in `RequiredValidation::validate()` that treats any array value (including empty) as "provided." This is a single-method, single-file fix with targeted test additions. No new classes, interfaces, or abstractions are needed.

The guard uses `is_array($value)` rather than `$value instanceof` or type-string checks, because the `$value` parameter's union type already constrains the input to `float|int|bool|array|string|null`.

## Rationale

- **Semantic correctness:** An empty array (`array()`) is a value the caller explicitly sent. `null` is the signal for "not sent" (as returned by `JSONParameter::resolveValue()` when the request key is absent or invalid). The required check should only fire for "not sent."
- **Minimal blast radius for scalar types:** The guard fires only for array-typed values. `StringParameter`, `IntegerParameter`, and `BooleanParameter` produce `string`, `int`, and `bool` resolved values respectively — none produce arrays, so their behaviour is completely unaffected. `IDListParameter` and `StringListParameter` each declare `resolveValue(): array|null`, so the guard also changes their required behaviour in the same direction: an empty array from either type will now pass the required check rather than fire an error. This is intentional and desirable — an empty array return from these types (e.g. an ID list where all tokens were non-numeric) represents a value that was explicitly provided, not an absent parameter.
- **Unblocks downstream consumers:** The HCP Editor's `SetValueVariationValuesAPI` (and any future API method with a required JSON array parameter) can use `makeRequired()` as intended, removing workaround boilerplate.

## Considered Alternatives

| Decision | Chosen Shape | Alternatives Considered | Trade-Off Summary |
|----------|--------------|-------------------------|-------------------|
| Where to place the guard | `RequiredValidation::validate()` (type-aware early return) | Override in `JSONParameter` (e.g. a `skipRequiredForEmpty()` hook) | Centralizing in `RequiredValidation` keeps the fix in one place and avoids an override hook that only one parameter type would use. |
| Guard condition | `is_array($value)` → early return | Expanding the `empty()` exemption list (e.g. `$value !== array()`) | `is_array()` is semantically cleaner: it says "arrays are always considered provided" rather than adding yet another edge-case to the `empty()` chain. It also covers all arrays, not just `array()`. |

## Pattern Alignment

- **Validation class structure** (`src/classes/Application/API/Parameters/Validation/Type/`) — the fix edits an existing validation class, following the established pattern of one validation concern per class file.
- **Test file layout** (`tests/AppFrameworkTests/API/Parameters/JSONParamTest.php`, `tests/AppFrameworkTests/API/RequiredTest.php`) — new test methods are added to existing test files rather than creating new ones, matching the existing pattern.
- **Array syntax** (`docs/agents/project-manifest/constraints.md`) — all array literals in the fix and tests use `array()`, not `[]`.

## Detailed Steps

### Step 1: Add regression baseline — `IDListParameter` + `makeRequired()`

**File:** `tests/AppFrameworkTests/API/Parameters/IDListParameterTest.php`

Add test methods that establish a documented baseline for `IDListParameter` required behaviour **before** the guard is added, so it is clear what the fix changes and that the new behaviour is intentional:

1. `test_requiredWithValidIdListPasses` — sets `$_REQUEST['foo']` to a valid comma-separated ID string, creates an `IDListParameter` with `makeRequired()`, asserts validation passes.
2. `test_requiredWithAbsentValueFails` — does not set `$_REQUEST['foo']`, asserts validation fires `VALIDATION_EMPTY_REQUIRED_PARAM`.
3. `test_requiredWithEmptyArrayPassesAfterFix` — sets `$_REQUEST['foo']` to a string that produces an empty array on resolution (e.g. all non-numeric tokens), asserts validation **passes** after the guard is applied. Document with a comment that this test verifies the intentional behaviour change introduced alongside the `JSONParameter` fix.

> **Note:** These tests are added first so that, once Step 2 changes `RequiredValidation`, their outcomes are already codified and reviewable.

### Step 2: Add array guard to `RequiredValidation::validate()`

**File:** `src/classes/Application/API/Parameters/Validation/Type/RequiredValidation.php`

Add an `is_array($value)` check at the top of `validate()` that returns immediately. An array — even an empty one — is an intentionally provided value; only `null` signals "parameter not sent."

Updated method body:

```php
public function validate(float|int|bool|array|string|null $value, OperationResult $result, APIParameterInterface $param): void
{
    // An array value — even an empty one — is a provided value.
    // Only null means the parameter was not sent.
    if(is_array($value)) {
        return;
    }

    if(empty($value) && $value !== 0 && $value !== '0' && $value !== false)
    {
        $result->makeError(
            sprintf('The API parameter `%s` is required.', $param->getName()),
            ParamValidationInterface::VALIDATION_EMPTY_REQUIRED_PARAM
        );
    }
}
```

### Step 3: Add test — required JSONParameter with empty array passes validation

**File:** `tests/AppFrameworkTests/API/Parameters/JSONParamTest.php`

Add a test method that:
1. Sets `$_REQUEST['foo']` to a JSON-encoded empty array (`'[]'`).
2. Creates a `JSONParameter`, calls `makeRequired()`.
3. Asserts the resolved value is `array()` (empty PHP array).
4. Asserts validation is valid (no error).
5. Asserts the result does **not** contain `VALIDATION_EMPTY_REQUIRED_PARAM`.

### Step 4: Add test — required JSONParameter with null fires validation error

**File:** `tests/AppFrameworkTests/API/Parameters/JSONParamTest.php`

Add a test method that:
1. Does **not** set `$_REQUEST['foo']` (or sets it to `null`).
2. Creates a `JSONParameter`, calls `makeRequired()`.
3. Asserts validation is invalid.
4. Asserts the result contains `VALIDATION_EMPTY_REQUIRED_PARAM`.

### Step 5: Add test — required JSONParameter with populated array passes validation

**File:** `tests/AppFrameworkTests/API/Parameters/JSONParamTest.php`

Add a test method that:
1. Sets `$_REQUEST['foo']` to a JSON-encoded non-empty array (e.g. `'{"key":"value"}'`).
2. Creates a `JSONParameter`, calls `makeRequired()`.
3. Asserts the resolved value is `array('key' => 'value')`.
4. Asserts validation is valid.

### Step 6: Run existing tests to confirm no regressions

Run the existing test files to ensure the fix does not break any current behaviour:

```bash
composer test-file -- tests/AppFrameworkTests/API/RequiredTest.php
composer test-file -- tests/AppFrameworkTests/API/Parameters/JSONParamTest.php
```

### Step 7: Run PHPStan

```bash
composer analyze
```

Confirm no new errors are introduced.

## Dependencies

- None. This is a self-contained fix within the Application Framework.

## Required Components

- `src/classes/Application/API/Parameters/Validation/Type/RequiredValidation.php` — modified (array guard added)
- `tests/AppFrameworkTests/API/Parameters/IDListParameterTest.php` — modified (3 baseline/regression test methods added)
- `tests/AppFrameworkTests/API/Parameters/JSONParamTest.php` — modified (3 test methods added)

## Assumptions

- `JSONParameter::resolveValue()` returns `null` when the request parameter is absent or contains invalid JSON, and returns a PHP array (possibly empty) when valid JSON is provided. Verified by reading the source.
- `IDListParameter` and `StringListParameter` both declare `resolveValue(): array|null` in addition to `JSONParameter`. The `is_array()` guard therefore also changes `RequiredValidation` behaviour for those two types when `makeRequired()` is used and their resolved value is an empty array. This is acknowledged and intentional — an empty array from these types represents an explicitly provided value, not an absent parameter. The regression tests added in Step 1 codify this intent.

## Constraints

- Array syntax: all code must use `array()` not `[]` (project hard rule).
- `declare(strict_types=1)` must be present in all PHP files (already present in both files).
- Run `composer dump-autoload` is not needed — no files are added or renamed.

## Out of Scope

- **HCP Editor follow-up:** Restoring `makeRequired()` on `SetValueVariationValuesAPI`'s `values` parameter and removing the manual null/empty guard. This is a separate task in the HCP Editor project that depends on this fix.
- **Refactoring the `empty()` chain:** The existing `empty($value) && $value !== 0 && $value !== '0' && $value !== false` pattern for scalar types works correctly and is not worth refactoring for this change.
- **`StringListParameter` regression tests:** `StringListParameter` also returns `array|null` from `resolveValue()`, meaning the guard changes its required behaviour in the same direction as `IDListParameter`. Adding dedicated `StringListParameter` + `makeRequired()` tests is left for a follow-up task; the behavioural change is acknowledged as intentional in the Rationale and Assumptions.

## Acceptance Criteria

- AC-01: `RequiredValidation` does **not** fire `VALIDATION_EMPTY_REQUIRED_PARAM` for `array()` (empty array) on a required `JSONParameter`.
- AC-02: `RequiredValidation` **does** fire `VALIDATION_EMPTY_REQUIRED_PARAM` for `null` on a required `JSONParameter` (parameter not sent).
- AC-03: `RequiredValidation` behaviour for scalar-typed parameters (`StringParameter`, `IntegerParameter`, `BooleanParameter`) is **unchanged** — their `resolveValue()` never returns an array, so the guard never fires for them. `IDListParameter` and `StringListParameter` behaviour also changes in the same direction as `JSONParameter` (empty array → required check passes); this is intentional and acceptable. `RequiredTest.php` tests (covering `StringParameter` only) pass without modification.
- AC-04: Existing tests in `RequiredTest.php` and `JSONParamTest.php` continue to pass.
- AC-05: New tests added to `JSONParamTest.php` covering required mode with empty array, null, and populated array.
- AC-06: PHPStan passes at the configured level with no new errors.

## Testing Strategy

Test the fix by adding required-mode tests to the existing `JSONParamTest.php` file. These tests exercise the `RequiredValidation` class through the `JSONParameter` integration path (parameter creation → `makeRequired()` → `getValue()` → validation result inspection), which is the same path production API methods use. Run existing tests in `RequiredTest.php` to confirm no regression for `StringParameter`.

## Test Plan

- `tests/AppFrameworkTests/API/Parameters/IDListParameterTest.php::test_requiredWithValidIdListPasses` — Asserts that a required `IDListParameter` with a valid ID string passes validation. Establishes baseline. Covers AC-03.
- `tests/AppFrameworkTests/API/Parameters/IDListParameterTest.php::test_requiredWithAbsentValueFails` — Asserts that a required `IDListParameter` with no request value fires `VALIDATION_EMPTY_REQUIRED_PARAM`. Covers AC-02 (parallel type).
- `tests/AppFrameworkTests/API/Parameters/IDListParameterTest.php::test_requiredWithEmptyArrayPassesAfterFix` — Asserts that a required `IDListParameter` whose resolution yields an empty array passes validation after the guard is applied. Codifies the intentional behaviour change. Covers AC-03 (acknowledged change).
- `tests/AppFrameworkTests/API/Parameters/JSONParamTest.php::test_requiredWithEmptyArrayPasses` — Asserts that a required `JSONParameter` with `'[]'` in the request resolves to `array()` and passes validation. Covers AC-01.
- `tests/AppFrameworkTests/API/Parameters/JSONParamTest.php::test_requiredWithNullFails` — Asserts that a required `JSONParameter` with no request value fires `VALIDATION_EMPTY_REQUIRED_PARAM`. Covers AC-02.
- `tests/AppFrameworkTests/API/Parameters/JSONParamTest.php::test_requiredWithPopulatedArrayPasses` — Asserts that a required `JSONParameter` with `'{"key":"value"}'` resolves correctly and passes validation. Covers AC-01 (positive case).
- `tests/AppFrameworkTests/API/RequiredTest.php` (existing 3 tests) — Re-run unchanged to confirm AC-03 and AC-04.
- `tests/AppFrameworkTests/API/Parameters/JSONParamTest.php` (existing 7 tests) — Re-run unchanged to confirm AC-04.

## Documentation Updates

- `changelog.md` — Add a bug fix entry documenting that `RequiredValidation` no longer rejects empty arrays on required `JSONParameter` instances.
- No manifest or CTX documentation updates are required. The fix corrects an internal validation behaviour without changing any public API surface, adding files, or altering module structure.

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **A future parameter type returns an array from `resolveValue()` and relies on `RequiredValidation` rejecting empty arrays.** | This is semantically unlikely — an empty array is a provided value, not an absent one. If such a type emerges, it should perform its own domain-specific empty check rather than relying on the generic required check. The `is_array()` guard is the correct semantic default. |
| **Downstream consumers depend on the current (buggy) behaviour.** | The only known consumer is the HCP Editor, which already works around the bug by omitting `makeRequired()`. The fix enables the intended pattern. A search for `JSONParameter`, `IDListParameter`, and `StringListParameter` combined with `makeRequired()` across the workspace can confirm no other consumer relies on the current error-for-empty-array behaviour. |
