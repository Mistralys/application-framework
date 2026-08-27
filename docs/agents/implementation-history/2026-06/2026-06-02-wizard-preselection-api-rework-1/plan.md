# Plan

## Plan Audit Cycles
- Audits: none — Plan Auditor v1.5.0
- Architectural Reviews: none — Plan Architect Reviewer v1.5.0

## Summary
Add a class-based step identification method to `WizardPreselection` that accepts a step class-string and resolves the step name via the class's `STEP_NAME` constant, eliminating the brittle string-name coupling identified in the original synthesis (Strategic Recommendation #2). This rework addresses that single actionable item.

## Architectural Context

The Wizard Preselection API consists of two classes in `src/classes/Application/Admin/Wizard/`:
- **`WizardPreselection.php`** — value-object that stores step values keyed by step name strings
- **`WizardConfigurator.php`** — session orchestrator that reads `WizardPreselection` and writes values into wizard session slots using the key format `settingPrefix + '-step_' + stepName`

The wizard trait (`src/classes/Application/Traits/Admin/Wizard.php`) registers steps via `addStep(string $name, ?string $className = null)`. The `$name` argument becomes the session data key suffix. Steps are stored in `$this->steps[$name]`.

Newer namespaced step classes already declare a `STEP_NAME` constant and use it with `addStep()`:
- `Application\Countries\Admin\Screens\Mode\Create\SourceCountrySelectionStep::STEP_NAME = 'SourceCountrySelection'`
- `Application\Countries\Admin\Screens\Mode\Create\CountrySettingsStep::STEP_NAME = 'CountrySettings'`

Framework-provided step interfaces also declare `STEP_NAME`:
- `Application_Interfaces_Admin_Wizard_SelectCountryStep::STEP_NAME = 'Country'`
- `Application_Interfaces_Admin_Wizard_Step_Confirmation::STEP_NAME = 'Confirm'`

The base `Application_Admin_Wizard_Step` class does NOT currently declare a `STEP_NAME` constant. Its `$id` property is set via `ClassHelper::getClassTypeName($this)` in the constructor.

## Approach / Architecture

Add a `setStepValueByClass(class-string<Application_Admin_Wizard_Step>, string, mixed)` method to `WizardPreselection` that:
1. Accepts the fully-qualified step class name
2. Reads the `STEP_NAME` constant from that class
3. Throws a descriptive exception immediately if the constant is missing (fail-fast, not fail-silent)
4. Delegates to the existing `setStepValue()` with the resolved name

This approach:
- Requires **zero changes** to `WizardConfigurator` (it continues to read step names from `getStepNames()`)
- Requires **zero changes** to the wizard trait or base `Step` class
- Leverages the existing `STEP_NAME` constant pattern already established in namespaced step classes
- Provides compile-time safety via `::class` references (IDE auto-completion, refactoring support, dead-code detection)
- Fails loudly at preselection-build time if a step class lacks `STEP_NAME`

## Rationale

Using `StepClass::class` with a `STEP_NAME` constant is superior to string names because:
- The class reference is checked by the autoloader/IDE — typos are caught immediately
- When wizards migrate to namespaces, `STEP_NAME` remains the single source of truth regardless of class naming conventions
- `getClassTypeName()` is fragile: it extracts the last underscore/namespace segment, which breaks if a class is renamed or if the step name doesn't match the class suffix (e.g., `addStep('Country', SelectCountryStep::class)`)

## Considered Alternatives

| Decision | Chosen Shape | Alternatives Considered | Trade-Off Summary |
|----------|--------------|-------------------------|-------------------|
| Resolution mechanism | `STEP_NAME` constant on step class | (A) `getClassTypeName()` fallback; (B) Abstract static `getStepName()` method on base class; (C) Wizard instance provides lookup | Constants are already the established pattern in namespaced steps; no fallback avoids masking misconfiguration; no base-class changes needed |
| API shape | New `setStepValueByClass()` method | (A) Overload `setStepValue()` first param to accept class-string | Separate method has clearer type signatures, no ambiguity between a step name and a class name, and preserves backward compatibility cleanly |
| Scope of `STEP_NAME` enforcement | Required only for steps used with `setStepValueByClass()` | (A) Add `STEP_NAME` to base `Step` class as mandatory | Minimally invasive; doesn't force all existing step classes to change |

## Pattern Alignment

- **`STEP_NAME` constant pattern** — follows `src/classes/Application/Countries/Admin/Screens/Mode/Create/SourceCountrySelectionStep.php` and `CountrySettingsStep.php` which already declare `public const string STEP_NAME`.
- **Fluent interface** — `setStepValueByClass()` returns `$this` consistent with `setStepValue()`.
- **Exception usage** — uses project's `Application_Exception` pattern with human-readable title + developer message + error code constant, per `docs/agents/exception-usage.md`.
- **`array()` syntax** — all array literals use `array()` per project convention.

## Detailed Steps

1. **Add `STEP_NAME` constant to the TestDriver step class** (`tests/application/assets/classes/TestDriver/Area/WizardTest/Wizard/Step/Countries.php`). Value: `'Countries'` — matching the existing `addStep('Countries')` registration.

2. **Add `setStepValueByClass()` method to `WizardPreselection`** (`src/classes/Application/Admin/Wizard/WizardPreselection.php`):
   - Signature: `public function setStepValueByClass(string $stepClass, string $key, mixed $value): self`
   - PHPDoc `@param class-string<Application_Admin_Wizard_Step> $stepClass`
   - Calls private `resolveStepNameByClass(string $stepClass): string`
   - Delegates to `$this->setStepValue($resolvedName, $key, $value)`

3. **Add `resolveStepNameByClass()` private method to `WizardPreselection`**:
   - Checks `defined($stepClass . '::STEP_NAME')`
   - If defined: returns `constant($stepClass . '::STEP_NAME')`
   - If not defined: throws `Application_Exception` with error code constant and descriptive message indicating the class must declare a `STEP_NAME` constant

4. **Add error code constant to `WizardPreselection`**: `public const int ERROR_STEP_CLASS_MISSING_STEP_NAME = <code>;` (use a code in the existing wizard error range).

5. **Add unit tests** to `tests/AppFrameworkTests/Application/Admin/Wizard/WizardPreselectionTest.php`:
   - Test: `setStepValueByClass` resolves correctly and stores under the step name
   - Test: `setStepValueByClass` throws when class lacks `STEP_NAME`
   - Test: fluent interface works with `setStepValueByClass`

6. **Update the TestDriver Preselection screen** (`tests/application/assets/classes/TestDriver/Area/WizardTest/Preselection.php`) to use `setStepValueByClass()` instead of `setStepValue()` — demonstrating the preferred consumer pattern.

7. **Update changelog** (`changelog.md`) — add entry for the new method under current version.

## Dependencies
- Existing `WizardPreselection` class (delivered in original plan)
- `STEP_NAME` constant pattern (already established in the codebase)

## Required Components
- `src/classes/Application/Admin/Wizard/WizardPreselection.php` — modified (new method + error constant)
- `tests/application/assets/classes/TestDriver/Area/WizardTest/Wizard/Step/Countries.php` — modified (add `STEP_NAME`)
- `tests/AppFrameworkTests/Application/Admin/Wizard/WizardPreselectionTest.php` — modified (new tests)
- `tests/application/assets/classes/TestDriver/Area/WizardTest/Preselection.php` — modified (use new method)
- `changelog.md` — modified (new entry)

## Assumptions
- The `STEP_NAME` constant value in each step class exactly matches the string passed to `addStep()` in the wizard's `_initSteps()`. This is already the established convention in all namespaced step classes.
- Older non-namespaced step classes that want to use `setStepValueByClass()` must add the `STEP_NAME` constant. This is intentional — the exception provides clear guidance.

## Constraints
- `array()` syntax must be used for all array literals.
- No changes to the wizard trait or base `Step` class (minimal scope).
- The existing `setStepValue(string $stepName, ...)` API remains fully functional and unchanged for backward compatibility.

## Out of Scope
- Adding `STEP_NAME` to all existing step classes across the codebase (only the test fixture gets it).
- Modifying `addStep()` in the wizard trait to read `STEP_NAME` from classes automatically.
- The `settingPrefix` validation issue (Strategic Recommendation #1 — already addressed by documentation).
- The `WizardConfigurator` factory method idea (Strategic Recommendation #6 — separate planning cycle).
- The DRY gap for `'country_id'` literals (Strategic Recommendation #6 — trivial follow-up).

## Acceptance Criteria
- AC-1: `WizardPreselection::setStepValueByClass(StepClass::class, $key, $value)` resolves the step name from `StepClass::STEP_NAME` and stores the value under that name.
- AC-2: Calling `setStepValueByClass()` with a class that does not declare `STEP_NAME` throws an `Application_Exception` with a descriptive message and the defined error code.
- AC-3: `setStepValueByClass()` returns `$this` for fluent chaining.
- AC-4: Values set via `setStepValueByClass()` are retrievable via `getStepValues($stepName)` using the resolved name.
- AC-5: The TestDriver Preselection screen uses `setStepValueByClass()` and the end-to-end flow continues to work.
- AC-6: All existing `WizardPreselection` tests continue to pass unchanged.

## Testing Strategy

Unit tests verify the new method's resolution logic, error handling, and integration with the existing value store. The existing integration tests (in `WizardTest.php`) exercise the full preselection → wizard flow and confirm no regression. The TestDriver Preselection screen update serves as a manual verification point.

## Test Plan

- `WizardPreselectionTest::test_setStepValueByClass_resolvesFromConstant` — Asserts that passing a step class with `STEP_NAME = 'Countries'` stores the value under key `'Countries'`, retrievable via `getStepValues('Countries')`. Covers AC-1, AC-4.
- `WizardPreselectionTest::test_setStepValueByClass_throwsWithoutConstant` — Asserts that passing a class without `STEP_NAME` throws `Application_Exception` with the expected error code. Covers AC-2.
- `WizardPreselectionTest::test_setStepValueByClass_fluentInterface` — Asserts the method returns `$this`. Covers AC-3.
- Existing `WizardPreselectionTest` tests — must continue passing unchanged. Covers AC-6.
- Existing `WizardTest` integration tests — must continue passing (no changes to `WizardConfigurator::createSession()` logic). Covers AC-5.

## Documentation Updates

- `changelog.md` — Add entry: "Wizard Preselection: Added `setStepValueByClass()` method accepting step class names for type-safe preselection."
- `src/classes/Application/Admin/Wizard/WizardPreselection.php` — PHPDoc on new method documents the `STEP_NAME` requirement.
- `.context/modules/admin/architecture-wizard.md` — Will be refreshed by `composer build` if the module context config captures this file.

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **Step class lacks `STEP_NAME` and consumer gets an exception** | Exception message clearly states what's needed: "The step class X must declare a public STEP_NAME constant." This is by design — fail-fast over fail-silent. |
| **`STEP_NAME` value doesn't match `addStep()` registration** | Same risk as the original string-based API; documented in PHPDoc. The constant at least centralizes the name in one place per class. |
| **Future namespace migration changes class names** | Using `::class` references means the autoloader validates existence; `STEP_NAME` is independent of the class name itself. |
