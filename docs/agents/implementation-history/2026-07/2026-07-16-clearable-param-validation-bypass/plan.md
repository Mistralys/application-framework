# Plan

## Plan Audit Cycles
- Audits: 1 — Plan Auditor v1.5.0
- Architectural Reviews: none — Plan Architect Reviewer v2.0.0

## Prior Project Context

The `2026-05-26-clearable-string-parameter` project introduced `ClearableStringParameter` and its design review (Concern #1) explicitly identified this exact gap: format validators inherited from `StringParameter` (via `validateAs()`) reject the empty-string clear signal. The reviewer recommended option (a) — a hook in the validation pipeline to short-circuit on `''`. This plan implements that recommendation. The `2026-07-13-json-param-required-validation-fix` project recently modified `RequiredValidation` to handle array values, demonstrating the pattern of targeted validation pipeline fixes.

## Summary

Add a `protected` hook method `isValueValidatable()` to `BaseAPIParameter` that `ClearableStringParameter` overrides to skip all format validators when the resolved value is `''` (the clear signal). This makes `->clearableString()->validateAs()->url()` (and all other format validators) safe to use without workaround code.

## Architectural Context

The API parameter validation pipeline lives in `src/classes/Application/API/Parameters/`. The flow is:

1. `BaseAPIParameter::getValue()` calls `resolveValue()` (abstract, type-specific)
2. `getValue()` calls `private validate($value)` which iterates all registered `ParamValidationInterface` validators
3. Validators are registered via `validateBy()`, `validateByRegex()`, `validateByCallback()`, `validateByEnum()`, and the `validateAs()` builder

`ClearableStringParameter` extends `StringParameter` (which extends `BaseAPIParameter`) and overrides `resolveValue()` to implement three-state semantics: `null` (absent), `''` (clear), trimmed string (value). The `''` return is the "clear this field" signal.

The problem: validators like `RegexValidation`, `CallbackValidation`, and `EnumValidation` skip `null` but not `''`. When consumers chain `->clearableString()->validateAs()->url()`, the URL regex rejects the empty-string clear signal.

## Approach / Architecture

Add a `protected` template-method hook `isValueValidatable()` to `BaseAPIParameter`, called inside `validate()` before the validator loop. The base implementation returns `true` for all values. `ClearableStringParameter` overrides it to return `false` when the value is `''`, causing the entire validation loop to be skipped for the clear signal.

This is a single-point fix: all validators (current and future) are protected simultaneously without modifying any individual validator class.

## Rationale

- **Single-point protection:** Fixing each validator individually (`RegexValidation`, `CallbackValidation`, `EnumValidation`) would require N changes and leave future validators vulnerable. The hook protects all validators at once.
- **Anticipated by the codebase:** `BaseParamValidation`'s docblock explicitly recommends this pattern: *"If future validators need common utilities (e.g. an `isValidatable()` guard...), they should be added here."* The hook in `BaseAPIParameter` is the parameter-level complement.
- **Recommended by prior review:** The `ClearableStringParameter` design review (Concern #1) explicitly recommended this fix as option (a).
- **Zero regression risk:** The base implementation returns `true`, preserving exact existing behavior for all parameter types. Only `ClearableStringParameter` changes behavior.
- **Consumer transparency:** No consumer code changes are required. `->clearableString()->validateAs()->url()` just works.

## Considered Alternatives

| Decision | Chosen Shape | Alternatives Considered | Trade-Off Summary |
|----------|--------------|-------------------------|-------------------|
| Where to intercept | Hook in `BaseAPIParameter::validate()` before the loop | (A) Fix each validator individually to skip `''`; (B) Override `validate()` by changing it to `protected` | (A) requires modifying 3+ validators and leaves future validators vulnerable. (B) exposes the full validation loop to subclasses which is more than needed — the hook is a minimal, targeted API. |
| Hook location | Before the validator loop, before `RequiredValidation` prepend | (A) After `RequiredValidation` prepend; (B) Inside each validator call | (A) would let `RequiredValidation` still fire on the sentinel value, adding complexity for no gain since clearable params are never required. (B) requires touching every validator individually. Placing the hook before the entire loop is simpler and correctly matches the Step 2 code and the Risks section rationale. |
| Return type of hook | `bool` (validatable or not) | (A) Return a filtered validator list; (B) Return the value to validate (allowing transformation) | A simple boolean is sufficient — the only current need is skip/don't-skip. Over-designing the hook creates unnecessary complexity. |

## Pattern Alignment

- **Template-method hook pattern** — follows the established `resolveValue()` pattern in `BaseAPIParameter` where the base class defines the algorithm and subclasses override specific steps. Verified at `src/classes/Application/API/Parameters/BaseAPIParameter.php`.
- **Validator skip pattern** — mirrors how `MaxLengthValidation` already skips `''` with an explicit `$value === ''` check at `src/classes/Application/API/Parameters/Validation/Type/MaxLengthValidation.php` L43. The new hook centralizes this concern at the parameter level.
- **`array()` syntax** — all new code uses `array()` per project convention.
- **`declare(strict_types=1)`** — all files already have it; no new files are created.

## Detailed Steps

### Step 1: Add `isValueValidatable()` hook to `BaseAPIParameter`

**File:** `src/classes/Application/API/Parameters/BaseAPIParameter.php`

Add a new `protected` method `isValueValidatable()` that returns `true` by default:

```php
/**
 * Determines whether the resolved value should be passed through the
 * validation pipeline. Subclasses may override this to skip format
 * validators for specific sentinel values.
 *
 * The base implementation returns `true` for all values, preserving
 * existing behavior.
 *
 * @param int|float|bool|string|array<int|string,mixed>|null $value The resolved value from {@see resolveValue()}.
 * @return bool `true` to run validators, `false` to skip them.
 */
protected function isValueValidatable(int|float|bool|string|array|null $value) : bool
{
    return true;
}
```

### Step 2: Wire the hook into `validate()`

**File:** `src/classes/Application/API/Parameters/BaseAPIParameter.php`

In the existing `private validate()` method, add an early return after the result validity check and before the `RequiredValidation` prepend:

```php
private function validate(int|float|bool|string|array|null $value) : bool
{
    if(!$this->result->isValid()) {
        return false;
    }

    // Allow subclasses to skip validation for specific sentinel values.
    if(!$this->isValueValidatable($value)) {
        return true;
    }

    // existing RequiredValidation prepend and loop...
}
```

The hook returns `true` (valid) when skipping, because the value is intentionally non-validated — it is a valid sentinel, not a validation failure.

### Step 3: Override `isValueValidatable()` in `ClearableStringParameter`

**File:** `src/classes/Application/API/Parameters/Type/ClearableStringParameter.php`

Add the override:

```php
/**
 * Skips format validation when the resolved value is the empty-string
 * clear signal. A `null` value (absent parameter) already skips
 * individual validators via their own null guards.
 *
 * @param int|float|bool|string|array<int|string,mixed>|null $value
 * @return bool
 */
protected function isValueValidatable(int|float|bool|string|array|null $value) : bool
{
    if($value === '') {
        return false;
    }

    return parent::isValueValidatable($value);
}
```

### Step 4: Add tests for validation bypass on clear signal

**File:** `tests/AppFrameworkTests/API/Parameters/ClearableStringParameterTest.php`

Add a new test region with tests covering:

1. **`test_validateByRegex_emptyStringClearSignal_skipsValidation`** — Register a regex validator (e.g. URL), send `''`, assert value is `''` and result is valid.
2. **`test_validateByRegex_validValue_passesValidation`** — Register a URL regex, send a valid URL, assert value is the URL and result is valid.
3. **`test_validateByRegex_invalidValue_failsValidation`** — Register a URL regex, send an invalid string, assert validation fails with `VALIDATION_INVALID_FORMAT_BY_REGEX`.
4. **`test_validateByRegex_absentKey_skipsValidation`** — Register a regex, don't set `$_REQUEST`, assert value is `null` and result is valid.
5. **`test_validateAs_url_emptyStringClearSignal_skipsValidation`** — Use the `validateAs()->url()` builder, send `''`, assert value is `''` and result is valid.
6. **`test_validateByCallback_emptyStringClearSignal_skipsCallback`** — Register a callback validator, send `''`, assert the callback is never invoked.
7. **`test_validateByEnum_emptyStringClearSignal_skipsValidation`** — Register an enum validator, send `''`, assert value is `''` and result is valid.

### Step 5: Run tests and static analysis

- Run `composer test-file -- tests/AppFrameworkTests/API/Parameters/ClearableStringParameterTest.php` to verify all tests pass.
- Run `composer analyze` to verify no PHPStan regressions.

## Dependencies

- None. This is a self-contained change within the Application Framework.

## Required Components

- `src/classes/Application/API/Parameters/BaseAPIParameter.php` — add hook method and wire it
- `src/classes/Application/API/Parameters/Type/ClearableStringParameter.php` — override hook
- `tests/AppFrameworkTests/API/Parameters/ClearableStringParameterTest.php` — add validation bypass tests

## Assumptions

- `ClearableStringParameter` is never used with `makeRequired()` — the clear signal (`''`) would fail required validation, which is expected and correct (clearing a required field is a contradiction).
- The `''` value is only a special sentinel for `ClearableStringParameter`, not for `StringParameter` or other parameter types. The base implementation returning `true` preserves this.

## Constraints

- Must use `array()` syntax, not `[]`.
- Must not change the behavior of any existing parameter type other than `ClearableStringParameter`.
- Must not modify any individual validator class.
- `validate()` remains `private` — the hook is the public API for subclass customization.

## Out of Scope

- Modifying individual validator classes to handle `''`.
- Making `validate()` protected or public.
- Updating HCP Editor consumer code to use `->clearableString()->validateAs()->url()` — that is a separate follow-up in the HCP Editor codebase.
- Adding `isValueValidatable()` awareness to `BaseParamValidation` — the hook is at the parameter level, not the validator level.

## Acceptance Criteria

- AC-01: `ClearableStringParameter` with `validateAs()->url()` accepts `''` (empty string) as a valid clear signal without triggering format validation errors.
- AC-02: `ClearableStringParameter` with `validateAs()->url()` still validates non-empty values against the URL regex normally.
- AC-03: `ClearableStringParameter` with `validateByRegex()` skips the regex when the value is `''`.
- AC-04: `ClearableStringParameter` with `validateByCallback()` does not invoke the callback when the value is `''`.
- AC-05: `ClearableStringParameter` with `validateByEnum()` skips enum checking when the value is `''`.
- AC-06: Absent parameter (key not in `$_REQUEST`) continues to resolve to `null` and skip validation normally (no regression).
- AC-07: All existing `ClearableStringParameter` tests continue to pass unchanged.
- AC-08: All other parameter types (`StringParameter`, `IntegerParameter`, `BooleanParameter`, etc.) behave identically to before (base `isValueValidatable()` returns `true`).
- AC-09: PHPStan reports no new errors.

## Testing Strategy

All testing is via PHPUnit unit tests. The existing `ClearableStringParameterTest` file is extended with a new test region for validation bypass behavior. Each acceptance criterion maps to at least one test. No integration tests are needed — the validation pipeline is fully testable via standalone parameter instantiation.

## Test Plan

- `test_validateByRegex_emptyStringClearSignal_skipsValidation` — Asserts `''` skips regex validation → AC-01, AC-03
- `test_validateByRegex_validValue_passesValidation` — Asserts valid URL passes regex → AC-02
- `test_validateByRegex_invalidValue_failsValidation` — Asserts invalid string fails regex → AC-02
- `test_validateByRegex_absentKey_skipsValidation` — Asserts null skips regex (regression guard) → AC-06
- `test_validateAs_url_emptyStringClearSignal_skipsValidation` — Asserts `''` skips `validateAs()->url()` → AC-01
- `test_validateByCallback_emptyStringClearSignal_skipsCallback` — Asserts callback not invoked for `''` → AC-04
- `test_validateByEnum_emptyStringClearSignal_skipsValidation` — Asserts `''` skips enum validation → AC-05

Existing 17 tests remain unchanged → AC-07, AC-08.

PHPStan run → AC-09.

## Documentation Updates

- No documentation artefacts require updating for this self-contained change.

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **Future parameter type accidentally overrides `isValueValidatable()` with wrong logic** | The method is well-documented with a clear contract. The base returns `true`, so any override is opt-in. |
| **Consumer attaches `makeRequired()` to a clearable param and expects clearing to work** | This is explicitly out of scope and documented as a contradiction. `RequiredValidation` uses `empty()` which rejects `''` — this is correct behavior for required fields. The hook fires before `RequiredValidation` would be prepended, so `makeRequired()` would never fire on `''` either. If this becomes a real consumer need, it requires a separate design decision. |
| **`''` passed through as stored value in database without validation** | The clear signal `''` is semantically "delete/null this field" — the API method body is responsible for translating `''` to the appropriate storage operation (e.g., setting a column to `NULL`). This is not a validation concern. |
