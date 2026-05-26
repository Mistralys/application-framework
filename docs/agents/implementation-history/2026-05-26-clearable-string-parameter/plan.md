# Plan

## Plan Audit Cycles
- Audits: 2 — Plan Auditor v1.3.1
- Architectural Reviews: 1 — Plan Architect Reviewer v1.4.0

## Summary

Add a new `ClearableStringParameter` type to the Application Framework's API parameter system. The `ClearableStringParameter` distinguishes between "parameter absent from request" (meaning: don't change the field) and "parameter present but empty/whitespace-only" (meaning: clear the field). It also trims all incoming values. This solves the problem where Update-style API methods cannot clear optional metadata fields once set. The new type leverages the existing `setMaxLength()` method (already on `StringParameter`) through inheritance.

## Architectural Context

The framework's API parameter system lives in `src/classes/Application/API/Parameters/`:

- **`BaseAPIParameter`** (`BaseAPIParameter.php`) — Abstract base class. Defines the lifecycle: `resolveValue()` → validation pipeline → `getValue()`. The `hasValue()` method returns `getValue() !== null`. Selected values (`selectValue()`) bypass resolution entirely.
- **`ParamTypeSelector`** (`ParamTypeSelector.php`) — Fluent factory registered via `APIParamManager`. Each type has a factory method (e.g., `string()`, `integer()`, `boolean()`). Returns the typed parameter instance for further configuration.
- **`Type/StringParameter`** (`Type/StringParameter.php`) — Current string type. Its `resolveValue()` delegates to `$this->getRequestParam()->get()`, which discards empty strings (returns the default value instead). This is the documented, intentional behaviour: *"Empty strings will be treated as null values."*
- **`Type/` directory** — All concrete parameter types live here: `BooleanParameter`, `IntegerParameter`, `JSONParameter`, `StringListParameter`, `IDListParameter`.

The key constraint is that `RequestParam::get()` (from `application-utils`) also discards empty strings before `StringParameter` ever sees them. The new type must bypass this layer by reading `$_REQUEST` directly — which is safe because:
1. `resolveValue()` is explicitly designed to be overridden by subclasses.
2. The existing test infrastructure already sets `$_REQUEST` directly (see `StringParamTest`).
3. Other tests in `APITestCase` reset `$_REQUEST` in `setUp()`.

Relevant files:
- `src/classes/Application/API/Parameters/BaseAPIParameter.php` — abstract base, `hasValue()` at line 213, `getValue()` at line 220, abstract `resolveValue()` at line 302.
- `src/classes/Application/API/Parameters/ParamTypeSelector.php` — factory; last method `nameOrTitle()` at line ~170.
- `src/classes/Application/API/Parameters/Type/StringParameter.php` — parent class for the new type.
- `tests/AppFrameworkTests/API/Parameters/StringParamTest.php` — reference for test patterns.
- `tests/AppFrameworkTestClasses/API/APITestCase.php` — base class that resets `$_REQUEST` in `setUp()`.

## Approach / Architecture

Create a new `ClearableStringParameter` class extending `StringParameter` that overrides `resolveValue()` with a three-state semantic:

| `$_REQUEST` state | `resolveValue()` returns | Meaning |
|---|---|---|
| Key absent | `null` | Not provided — don't touch the field |
| Key present, value is empty or whitespace-only after trim | `''` (empty string) | Clear the field |
| Key present, value is non-empty after trim | Trimmed string | Set the field to this value |

Register it in `ParamTypeSelector` via a new `clearableString()` factory method.

Since `ClearableStringParameter` extends `StringParameter`, it inherits the existing `setMaxLength()` method automatically. The existing `MaxLengthValidation` skips null and empty-string values (only validates non-empty strings that represent actual content).

This makes the consumer code trivial:
```php
$this->ownerParam = $this->addParam('owner', 'Owner')
    ->clearableString()
    ->setMaxLength(200)
    ->setDescription('...');

// In the update method:
if ($param->hasValue()) {
    $record->setField($param->getValue()); // '' clears, 'x' sets
}
// else: not provided, no change
```

Note: `hasValue()` in `BaseAPIParameter` returns `getValue() !== null`. When the parameter is absent, `resolveValue()` returns `null`, so `hasValue()` returns `false`. When the parameter is present (even as empty string), `resolveValue()` returns a string (`''` or trimmed value), so `hasValue()` returns `true`. This is the correct behaviour without modifying `BaseAPIParameter`.

**Validation pipeline short-circuit via `getValue()` override:** The `validate()` method in `BaseAPIParameter` is private and cannot be overridden. Instead, `ClearableStringParameter` overrides `getValue()` to short-circuit before the parent's validation pipeline runs. The logic:
1. If `isInvalidated()` returns `true` (invalidated by a prior call), return `null`.
2. Check `$_REQUEST` for the parameter key: if it is present and the value resolves to `''` after trimming (the clear signal), return `''` directly — bypassing `parent::getValue()` entirely.
3. Otherwise, delegate to `parent::getValue()` for all other cases (including `selectValue()` paths, non-empty values that need validation, and absent parameters).

This is safe because: when `selectValue()` was called, `parent::getValue()` returns the selected value immediately without resolving or validating; the clear-signal check only matches when `$_REQUEST` has the key present with an empty/whitespace-only value; and `$this->result` (protected, accessed by `getValidationResults()`) stays in its initial valid state for the short-circuit path. This ensures that format validations (e.g., `validateByRegex('/^[a-z]+$/')`) never reject the clear signal. The three-state contract is unconditional — consumers do not need to be aware of validation interactions when clearing a field.

## Rationale

1. **Solves both problems simultaneously** — trimming and clearing in one coherent design.
2. **Idiomatic framework location** — the `Type/` directory is designed for specialized parameter types.
3. **Clean semantics** — null = not sent, `''` = clear, non-empty = set. Maps directly to HTTP form semantics (absent vs. present-but-empty).
4. **Fluent registration** — `clearableString()` on `ParamTypeSelector` makes usage identical to other types.
5. **Reusable** — any API method needing clearable fields gets this for free.
6. **Zero risk to existing code** — new class, no modification to `StringParameter` or `BaseAPIParameter`.
7. **Max-length already available** — the existing `setMaxLength()` on `StringParameter` is available to `ClearableStringParameter` through inheritance, so consumers can enforce length limits without additional work.

## Considered Alternatives

| Decision | Chosen Shape | Alternatives Considered | Trade-Off Summary |
|----------|--------------|-------------------------|-------------------|
| Where to handle clearing | New parameter type (`ClearableStringParameter`) | Sentinel magic strings; `clearFields` array parameter; modifying `StringParameter` with opt-in flag | A new type is self-contained, doesn't touch existing behaviour, and is the framework's designed extension point for parameter semantics. |
| How to bypass empty-string interception | Read `$_REQUEST` directly via `array_key_exists()` | Modify `RequestParam::get()` to support empty strings; add a bypass flag to `StringParameter` | Direct `$_REQUEST` access is already the pattern in tests; modifying `RequestParam` in application-utils is a cross-package change with wider blast radius. |
| Where to place trim logic | Inside `resolveValue()` of the new type | Separate trim step in API method; validation callback | Trim in `resolveValue()` ensures all consumers get trimmed values by default; no per-method boilerplate. |
| Where to place max-length logic | Dedicated `MaxLengthValidation` class + `setMaxLength()` on `StringParameter` | Regex-based length check; inline callback per method; method on `BaseAPIParameter` | A dedicated validation class follows the established pattern (`RegexValidation`, `EnumValidation`). Placing `setMaxLength()` on `StringParameter` means all string subtypes inherit it. A regex approach would be less readable and wouldn't produce a distinct error code. |

## Pattern Alignment

- **Parameter type per `Type/` directory** (`src/classes/Application/API/Parameters/Type/`) — follows the existing pattern of one class per parameter type. No departure.
- **`ParamTypeSelector` factory method** (`ParamTypeSelector.php`) — follows the existing pattern of one factory method per type (e.g., `string()`, `integer()`, `boolean()`). No departure.
- **Validation class per `Validation/Type/` directory** (`src/classes/Application/API/Parameters/Validation/Type/`) — follows the existing pattern of `RegexValidation`, `RequiredValidation`, `EnumValidation`, etc. No departure.
- **Convenience method on parameter class** — `setMaxLength()` on `StringParameter` follows the pattern of `validateByRegex()` which wraps a validation class in a fluent method. No departure.
- **Validation code constants on `ParamValidationInterface`** — all validation error codes are defined there as class constants. No departure.
- **Test file per parameter type** (`tests/AppFrameworkTests/API/Parameters/`) — follows the pattern of `StringParamTest.php`, `IntegerParameterTest.php`, etc. No departure.
- **`$_REQUEST` superglobal in tests** — the existing `APITestCase` sets and resets `$_REQUEST` directly. Our tests follow this pattern exactly.
- **Extending `StringParameter`** — inherits validation pipeline, `validateByRegex()`, `validateAs()`, `setMaxLength()`, format validators, and documentation annotations. Both `resolveValue()` and `getValue()` are overridden: `resolveValue()` for three-state resolution semantics, `getValue()` for validation pipeline short-circuit on the clear signal.

## Detailed Steps

1. **Create `ClearableStringParameter.php`** in `src/classes/Application/API/Parameters/Type/`:
   - Namespace: `Application\API\Parameters\Type`
   - Extends `StringParameter`
   - Overrides `resolveValue()` to:
     - Check `array_key_exists($this->getName(), $_REQUEST)` — if absent, return `null`.
     - Get the raw value from `$_REQUEST[$this->getName()]`.
     - If not a string/numeric, return `null` (same guard as parent).
     - Trim the value and return it (empty string means "clear").
   - Overrides `getValue()` to short-circuit the validation pipeline on the clear signal:
     - If `$this->isInvalidated()` returns `true`, return `null`.
     - Check `$_REQUEST` for the parameter key: if present and the value resolves to `''` after trimming, return `''` directly — bypassing `parent::getValue()` entirely (skips the private `validate()` call).
     - Otherwise, delegate to `parent::getValue()` for all other cases.
   - Override `getTypeLabel()` to return `t('Clearable string')`.

2. **Add `clearableString()` method to `ParamTypeSelector`**:
   - Add `use Application\API\Parameters\Type\ClearableStringParameter;` import.
   - Add factory method following the exact pattern of `string()`.
   - Returns `ClearableStringParameter`.

3. **Create unit test `ClearableStringParameterTest.php`** in `tests/AppFrameworkTests/API/Parameters/`:
   - Extends `APITestCase`.
   - Test cases covering all three states plus edge cases.

4. **Run `composer dump-autoload`** (classmap autoloading requires it after adding new class files).

5. **Run the test files** to verify all assertions pass.

## Dependencies

- No external dependencies. This is a self-contained addition to the existing parameter type system.
- The `StringParameter` parent class and `ParamTypeSelector` are both stable with no pending changes.

## Required Components

- **New:** `src/classes/Application/API/Parameters/Type/ClearableStringParameter.php`
- **Modified:** `src/classes/Application/API/Parameters/ParamTypeSelector.php` (add factory method + import)
- **New:** `tests/AppFrameworkTests/API/Parameters/ClearableStringParameterTest.php`

## Assumptions

- `$_REQUEST` superglobal is the source of truth for form-encoded API parameters (confirmed by existing `StringParamTest` which sets `$_REQUEST` directly).
- `array_key_exists()` on `$_REQUEST` reliably distinguishes absent from present-but-empty (standard PHP behaviour for form-encoded requests).
- The framework's validation pipeline (regex, enum, required, callback validations) operates on the value returned by `resolveValue()`. The `getValue()` override short-circuits before this pipeline runs when the resolved value is `''` (the clear signal), so validations never see the empty string.
- `BaseAPIParameter::hasValue()` checks `getValue() !== null`, which naturally maps to "parameter was provided" when `resolveValue()` returns `''` for clear.

## Constraints

- Must use `array()` syntax exclusively (project rule).
- Must include `declare(strict_types=1)` in all new files.
- No constructor promotion, no readonly properties.
- Must run `composer dump-autoload` after adding new class files.

## Out of Scope

- Modifying the HCP Editor's `UpdateComtypeAPI` to use the new type (that will be a separate follow-up plan in the `hcp-editor` project).
- Modifying the existing behaviour of `StringParameter` or `BaseAPIParameter`.
- Modifying `RequestParam` in the `application-utils` package.
- OpenAPI spec annotation changes (can be addressed later if needed).
- Adding `makeRequired()` + clearable interaction tests (edge case, can be added later if a real use case emerges).

## Acceptance Criteria

- A `ClearableStringParameter` class exists in `src/classes/Application/API/Parameters/Type/`.
- `ParamTypeSelector::clearableString()` returns an instance of `ClearableStringParameter`.
- When `$_REQUEST` does not contain the parameter key, `getValue()` returns `null` and `hasValue()` returns `false`.
- When `$_REQUEST` contains the parameter key with an empty string, `getValue()` returns `''` and `hasValue()` returns `true`.
- When `$_REQUEST` contains the parameter key with a whitespace-only string, `getValue()` returns `''` (trimmed) and `hasValue()` returns `true`.
- When `$_REQUEST` contains the parameter key with a non-empty string, `getValue()` returns the trimmed string and `hasValue()` returns `true`.
- When `$_REQUEST` contains the parameter key with a numeric value, `getValue()` returns the string representation (trimmed).
- Existing `StringParameter` tests continue to pass (no regression).
- The new parameter type integrates with `selectValue()` (selected value bypasses resolution, as inherited from `BaseAPIParameter`).
- The new parameter type integrates with validation (e.g., `validateByRegex()` applies to non-empty resolved values).
- Format validations (regex, max-length, etc.) are skipped entirely when the resolved value is `''` (the clear signal). The clear signal is never rejected by the validation pipeline.
- `ClearableStringParameter` inherits `setMaxLength()` from `StringParameter` (which already exists) and it works correctly through inheritance.

## Testing Strategy

Unit tests exercising the `ClearableStringParameter` in isolation by manipulating `$_REQUEST` directly — the same approach used by `StringParamTest.php`. Tests cover:
- All three semantic states (absent, empty, non-empty).
- Whitespace trimming edge cases.
- Numeric value coercion.
- Invalid type handling (array value in request).
- `selectValue()` override behaviour (inherited).
- Default value behaviour (should only apply when key is absent).
- Integration with regex validation on non-empty values.

## Test Plan

### ClearableStringParameter Tests

- `tests/AppFrameworkTests/API/Parameters/ClearableStringParameterTest.php::test_absentParameter_returnsNull` — When `$_REQUEST` has no entry for the param, `getValue()` returns `null` and `hasValue()` returns `false` — Covers AC: absent parameter.
- `tests/AppFrameworkTests/API/Parameters/ClearableStringParameterTest.php::test_emptyStringInRequest_returnsEmptyString` — When `$_REQUEST['param'] = ''`, `getValue()` returns `''` and `hasValue()` returns `true` — Covers AC: empty string clears.
- `tests/AppFrameworkTests/API/Parameters/ClearableStringParameterTest.php::test_whitespaceOnlyInRequest_returnsEmptyString` — When `$_REQUEST['param'] = '   '`, `getValue()` returns `''` — Covers AC: whitespace trimmed to clear.
- `tests/AppFrameworkTests/API/Parameters/ClearableStringParameterTest.php::test_nonEmptyStringInRequest_returnsTrimmedValue` — When `$_REQUEST['param'] = ' hello '`, `getValue()` returns `'hello'` — Covers AC: trimmed non-empty value.
- `tests/AppFrameworkTests/API/Parameters/ClearableStringParameterTest.php::test_numericValueInRequest_returnsStringRepresentation` — When `$_REQUEST['param'] = 42`, `getValue()` returns `'42'` — Covers AC: numeric coercion.
- `tests/AppFrameworkTests/API/Parameters/ClearableStringParameterTest.php::test_invalidTypeInRequest_returnsNull` — When `$_REQUEST['param'] = array()`, `getValue()` returns `null` — Covers robustness against invalid types.
- `tests/AppFrameworkTests/API/Parameters/ClearableStringParameterTest.php::test_selectValue_overridesResolution` — `selectValue('override')` causes `getValue()` to return `'override'` regardless of `$_REQUEST` — Covers AC: selectValue integration.
- `tests/AppFrameworkTests/API/Parameters/ClearableStringParameterTest.php::test_defaultValue_appliesWhenAbsent` — `setDefaultValue('default')` is returned when key is absent from `$_REQUEST` — Covers AC: default value when absent.
- `tests/AppFrameworkTests/API/Parameters/ClearableStringParameterTest.php::test_defaultValue_notAppliedWhenPresent` — `setDefaultValue('default')` is NOT returned when key is present as empty string — Covers AC: empty overrides default.
- `tests/AppFrameworkTests/API/Parameters/ClearableStringParameterTest.php::test_regexValidation_appliedToNonEmptyValue` — `validateByRegex('/^[a-z]+$/')` with `$_REQUEST['param'] = '123'` invalidates — Covers AC: validation integration.
- `tests/AppFrameworkTests/API/Parameters/ClearableStringParameterTest.php::test_regexValidation_skippedOnClearSignal` — `validateByRegex('/^[a-z]+$/')` with `$_REQUEST['param'] = ''` does NOT invalidate; parameter is valid with `getValue()` returning `''` — Covers AC: validation pipeline short-circuit on clear.
- `tests/AppFrameworkTests/API/Parameters/ClearableStringParameterTest.php::test_clearableStringWithMaxLength` — `ClearableStringParameter` with `setMaxLength()` validates non-empty values exceeding the limit and skips empty (clear signal) — Covers AC: `setMaxLength()` inheritance works.

## Documentation Updates

- `src/classes/Application/API/Parameters/Type/ClearableStringParameter.php` — Class-level docblock documents the three-state semantics and trim behaviour.
- No manifest or `.context/` updates required at this stage — the CTX generator will pick up the new file automatically on the next `composer build` run.

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **`$_REQUEST` bypass breaks in non-standard request contexts (CLI, tests with custom request objects)** | The test infrastructure already uses `$_REQUEST` directly. CLI API calls typically use `selectValue()` which bypasses resolution entirely. Document that `ClearableStringParameter` requires `$_REQUEST` to be populated. |
| **`hasValue()` returning `true` for empty string confuses consumers** | The semantic is clearly documented in the class docblock. Consumers opt in by choosing `clearableString()` — they must handle the empty-string case. |
| **`makeRequired()` rejects the empty-string clear signal** | `RequiredValidation::validate()` uses `empty($value)`, and `empty('')` is `true` in PHP, so combining `makeRequired()` with `clearableString()` would reject the clear signal. This is consistent with how `makeRequired()` works across all parameter types — it asserts that a meaningful value is present. Consumers needing "required but clearable" semantics would need a different approach (outside this plan's scope). The combination is listed in Out of Scope. |
| **Future change to `BaseAPIParameter::hasValue()` logic** | The current implementation is stable and simple (`!== null`). If it ever changes, the `ClearableStringParameter` test suite will catch the regression immediately. |
