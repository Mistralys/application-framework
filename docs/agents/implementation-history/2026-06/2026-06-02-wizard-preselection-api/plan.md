# Plan
## Plan Audit Cycles
- Audits: 4 — Plan Auditor v1.4.0
- Architectural Reviews: 1 — Plan Architect Reviewer v1.5.0

## Summary

Implement a Wizard Preselection API that allows consumers to pre-select step property values before redirecting a user to a wizard. The API consists of two new classes — `WizardPreselection` (value storage) and `WizardConfigurator` (session orchestration + URL generation) — plus a targeted change to the Step constructor to properly merge defaults with incoming data. The wizard trait and interface receive no modifications.

## Architectural Context

The wizard system is built on:

- **Trait**: `src/classes/Application/Traits/Admin/Wizard.php` — core wizard logic (session management, step initialization, navigation, settings).
- **Step base**: `src/classes/Application/Admin/Wizard/Step.php` — abstract step class instantiated with data from session.
- **BaseWizardMode**: `src/classes/Application/Admin/Wizard/BaseWizardMode.php` — modern namespaced wizard base class.
- **Session mechanism**: Wizard sessions use `Application_Session` with CRC32-based session IDs (format: `WZ` + hash). Step data stored under keys `settingPrefix + '-step_' + StepName` (e.g., `-step_Countries` when prefix is empty).
- **`generateNewSessionID()`**: Public static method that pre-creates a session entry in `Application_Session`.
- **`getStepData()`**: Private method that reads `settingPrefix + '-step_' + name` from session data via `getWizardSetting()`; returns empty `array()` when no data exists.
- **`setWizardSetting()` / `getWizardSetting()`**: Session data is stored under keys `settingPrefix + '-' + name`. The `saveSettings()` method iterates all steps and writes `'step_' + stepID` → `$step->getData()`.
- **`settingPrefix`**: Defaults to empty string `''`. `setSettingPrefix()` exists but has no call sites in the entire codebase — all current wizards use the default.
- **Step constructor**: Receives step data array; if data is populated, the step uses it directly. On first visit, steps receive an empty array.
- **`getDefaultData()`**: Abstract method on steps returning initial values. Currently unreachable via constructor flow (the `!isset()` check is dead code since `$data` is always a non-null array).
- **AdminURL system**: `src/classes/UI/AdminURLs/AdminURL.php` — fluent URL builder for admin screens.
- **Prior art**: `Application_Formable_RecordSettings_ValueSet` (`src/classes/Application/Formable/RecordSettings/ValueSet.php`) is a similar typed key/value container.

Key integration points:
- `src/classes/Application/Admin/Wizard/Step.php` line 55: Step constructor (needs modification to merge defaults with incoming data).
- `src/classes/Application/Admin/Wizard/` directory: destination for new classes.

## Approach / Architecture

Introduce two new namespaced classes in the existing `Application\Admin\Wizard` namespace, plus one targeted change to the Step base class constructor:

```
src/classes/Application/Admin/Wizard/
├── BaseWizardMode.php          (existing — no changes)
├── InvalidationHandler.php     (existing — no changes)
├── Step.php                    (existing — constructor merge logic)
├── WizardPreselection.php      (NEW — value storage)
└── WizardConfigurator.php      (NEW — session orchestrator + URL builder)
```

**Data flow:**
1. Consumer creates `WizardConfigurator` with the wizard's base URL and setting prefix.
2. Consumer sets preselected values on the `WizardPreselection` object via `setStepValue()`.
3. Consumer calls `getRedirectURL()` which internally creates a session via `generateNewSessionID()` and writes preselection values **directly into the session step data slots** using the same key format the wizard already reads (`settingPrefix + '-step_' + stepName`).
4. User is redirected to the wizard URL (containing `?wizard=<sessionID>`).
5. Wizard initializes, calls `getStepData()` for each step — finds existing data in the session slots and returns it unmodified.
6. Step constructor merges `getDefaultData()` with the incoming data (preselection wins over defaults for any overlapping keys; defaults fill gaps for unset keys).
7. After a step is processed and saved, normal session step data takes over — the preselection data is naturally overwritten by `saveSettings()`.

**Key insight:** By writing preselection directly into step data slots at session creation time, the wizard trait requires zero modifications. The trait already reads these keys in `getStepData()`. Once a step is saved, `saveSettings()` overwrites the slots with the full step data, so preselection is implicitly consumed on first save.

## Rationale

- **Session-based storage** (not URL params): Secure, no URL length limits, consistent with wizard's existing state mechanism.
- **Direct write to step slots** (not a dedicated preselection key): Eliminates all modifications to the wizard trait. The trait already reads step data from these keys — zero new coupling. No consume-once mutation logic needed in a private getter.
- **Separate value store from configurator**: Single Responsibility — storage is reusable and testable independently; configurator handles orchestration.
- **Merge with step defaults in constructor** (not replace): Consumer only sets values they care about; step defaults fill gaps for unset keys.
- **Configurator accepts base URL** (not wizard class name): The consumer is always in an admin context that already knows the wizard's URL. No static URL method exists on the wizard interface, and adding one would force all wizard implementations to change.
- **Explicit `settingPrefix` parameter**: The configurator must write to the same key format the wizard reads. Making the prefix an explicit constructor parameter (defaulting to `''`) documents the coupling and future-proofs against wizards that might set a non-empty prefix.

## Considered Alternatives

| Decision | Chosen Shape | Alternatives Considered | Trade-Off Summary |
|----------|--------------|-------------------------|-------------------|
| Value transport mechanism | Session pre-seeding | URL query parameters, Event/hook injection | Session avoids URL length limits and security exposure; event hooks create chicken-and-egg coupling issues. |
| API shape | Dedicated Configurator + ValueStore pair | Raw session manipulation, Single monolithic class | Separation gives SRP, testability, and aligns with existing builder patterns (AdminURL). |
| Session storage strategy | Write directly into step data slots (`-step_StepName`) | Dedicated `preselection` key read + cleared by `getStepData()` | Direct write eliminates all trait changes, removes consume-once mutation from a private getter, and reduces the coupling surface. No observable behavioural difference when combined with the Step constructor merge. |
| How configurator obtains the wizard URL | Accept base URL string directly from consumer | Derive from wizard class name (static method or instantiation) | The interface has no static URL method; instantiation requires dependencies. The consumer always knows the URL already. Accepting it directly is simpler and keeps the configurator fully decoupled from the wizard class. |
| Step data merging location | Step constructor (merge defaults + incoming data) | `getStepData()` only, Step `_process()` method | Constructor merge ensures form fields are populated on first render regardless of step implementation. |
| Preselection lifecycle | Implicitly consumed when `saveSettings()` overwrites step slots | Explicit consume-once clearing in trait, Re-applied each init | Natural overwrite by `saveSettings()` requires no clearing logic and is already the standard wizard data lifecycle. |
| Bundling `getDefaultData()` fix with this feature | Fixed as part of the Step constructor change | Fix separately in a standalone commit | Bundling is pragmatic since the preselection merge needs the defaults path to work. A covering test case isolates the behavioral change for bisect. |

## Pattern Alignment

- **Builder/Fluent pattern** (`src/classes/UI/AdminURLs/AdminURL.php`): `WizardConfigurator` follows the same fluent builder ergonomics. No departure.
- **Namespaced classes in module directory** (`src/classes/Application/Admin/Wizard/`): New classes go in the existing wizard namespace/directory. No departure.
- **`array()` syntax** (`docs/agents/project-manifest/constraints.md`): All array creation uses verbose syntax. No departure.
- **`public static` factory for session** (wizard trait line 153): `WizardConfigurator` calls the existing `generateNewSessionID()`. No departure.
- **Settings prefix pattern** (wizard trait `getWizardSetting`/`setWizardSetting`): Configurator writes session keys using the same `settingPrefix + '-step_' + stepName` format. No departure.
- **Session data direct manipulation** (wizard trait `saveSettings()` at line 674): `saveSettings()` writes directly to `$this->session->setValue()`. Configurator follows the same pattern — writing data into the session value array under the session ID. No departure.
- **Step constructor departure**: The `getDefaultData()` call is currently unreachable dead code. The plan revives it by changing the constructor to always merge defaults with the incoming data via `array_merge(getDefaultData(), $data)`. This fixes a latent bug where step defaults are never applied. Steps that return an empty array from `getDefaultData()` (the majority) are completely unaffected.

## Detailed Steps

1. **Create `WizardPreselection` class** (`src/classes/Application/Admin/Wizard/WizardPreselection.php`)
   - Namespace: `Application\Admin\Wizard`
   - Properties: `private array $values = array()` — nested map `array<stepName, array<key, value>>`.
   - Methods:
     - `setStepValue(string $stepName, string $key, mixed $value): self` — fluent setter.
     - `getStepValues(string $stepName): array` — returns values for a specific step (empty array if none).
     - `hasStepValues(string $stepName): bool` — checks if any values exist for a step.
     - `toArray(): array` — serialize to storable format (returns the nested values map).
     - `isEmpty(): bool` — true when no values have been set.
     - `getStepNames(): array` — returns list of step names that have preselection values.

2. **Create `WizardConfigurator` class** (`src/classes/Application/Admin/Wizard/WizardConfigurator.php`)
   - Namespace: `Application\Admin\Wizard`
   - Constructor: `__construct(string $wizardBaseURL, string $settingPrefix = '')`
     - `$wizardBaseURL`: The base URL of the wizard screen (e.g., from `AdminURL::create()->...->getURL()`).
     - `$settingPrefix`: Must match the wizard's `settingPrefix` property. Defaults to `''` (matches all current wizards). Documents the coupling explicitly.
   - Properties:
     - `private WizardPreselection $preselection`
     - `private string $wizardBaseURL`
     - `private string $settingPrefix`
     - `private ?string $sessionID = null`
   - Methods:
     - `getPreselection(): WizardPreselection` — accessor to set preselected values.
     - `getRedirectURL(): string` — creates session (if not yet created), writes preselection values into session step data slots, appends `?wizard=<sessionID>` to the base URL and returns it.
     - `private createSession(): string` — calls `BaseWizardMode::generateNewSessionID()`, initialises the `invalidationHandler` session key, then writes each step's preselection values into the session under keys `settingPrefix + '-step_' + stepName`, returns session ID.
   - Session write logic in `createSession()`:
     ```
     $sessionID = BaseWizardMode::generateNewSessionID();
     $session = AppFactory::createSession();
     $data = $session->getValue($sessionID);
     $invalidationHandler = new InvalidationHandler();
     $invalidationHandler->setIsInvalidated(false);
     $data[$this->settingPrefix . '-invalidationHandler'] = $invalidationHandler;
     foreach ($this->preselection->getStepNames() as $stepName) {
         $key = $this->settingPrefix . '-step_' . $stepName;
         $data[$key] = $this->preselection->getStepValues($stepName);
     }
     $session->setValue($sessionID, $data);
     ```
     > **Note:** The `invalidationHandler` key must be initialised because `initWizard()` unconditionally reads it via `getWizardSetting('invalidationHandler')` and assigns it to a non-nullable typed property. Omitting it causes a `TypeError` at runtime. Both `InvalidationHandler` and `BaseWizardMode` are in namespace `Application\Admin\Wizard` — no additional imports required.

3. **Modify Step constructor** (`src/classes/Application/Admin/Wizard/Step.php` line 55)
   - Replace the dead-code `if(!isset($this->data))` check with a proper merge:
     - Always call `$this->data = array_merge($this->getDefaultData(), $data)`.
     - The `$this->setComplete(false)` call in the replaced dead-code branch is intentionally dropped — it was unreachable and has no behavioral effect.
     - This means:
       - On first visit without preselection (`$data` is empty array): step gets only its defaults.
       - On first visit with preselection (`$data` has preselected keys): defaults fill gaps, preselection wins for overlapping keys.
       - On subsequent visits (`$data` is full saved step data): defaults are merged under but saved data wins for all keys (effectively no change since saved data already contains all keys).
   - This fixes the latent issue where step defaults were never applied and ensures preselection merges cleanly with defaults.

4. **Create a test application preselection screen** (`tests/application/assets/classes/TestDriver/Area/WizardTest/Preselection.php`)
   - A new mode screen in the existing `WizardTest` area (URL name: `preselection`).
   - Legacy-style class name: `TestDriver_Area_WizardTest_Preselection` extending `Application_Admin_Area_Mode`.
   - Purpose: demonstrates and enables manual UI testing of the preselection API.
   - In `_handleActions()`: uses `WizardConfigurator` to preselect the `country_id` value on the `Countries` step, then redirects to the wizard.
   - The country to preselect can be chosen via a request parameter (e.g., `?country=GB`) or a simple form.
   - The mode is auto-discovered by the framework's `AdminScreenIndex` via filesystem/classmap convention — no explicit registration in the area class is needed. Running `composer dump-autoload` is sufficient for discovery.
   - Add `VALUE_COUNTRY_ID` constant to `TestDriver_Area_WizardTest_Wizard_Step_Countries` for use in the preselection call.

5. **Write unit tests** for the new API (in `tests/AppFrameworkTests/Application/Admin/Wizard/`).

6. **Run `composer dump-autoload`** to register new classmap entries.

## Dependencies

- `BaseWizardMode::generateNewSessionID()` — existing public static method (defined in the trait, dispatched via the modern non-deprecated class). `Application_Admin_Wizard` is annotated `@deprecated Use BaseWizardMode instead`; `BaseWizardMode` inherits the same static method and is the correct dispatch target for new code.
- `Application_Session` / `AppFactory::createSession()` — existing session storage mechanism.
- `Application\Admin\Wizard\BaseWizardMode` — existing base class (no changes, but confirms namespace).
- The test wizard (`tests/application/assets/classes/TestDriver/Area/WizardTest/`) — used for integration testing.

## Required Components

- **New**: `src/classes/Application/Admin/Wizard/WizardPreselection.php` — value storage class.
- **New**: `src/classes/Application/Admin/Wizard/WizardConfigurator.php` — orchestrator/builder class.
- **Modified**: `src/classes/Application/Admin/Wizard/Step.php` — constructor merge logic.
- **New**: `tests/AppFrameworkTests/Application/Admin/Wizard/WizardPreselectionTest.php` — unit tests for value store.
- **New**: `tests/AppFrameworkTests/Application/Admin/Wizard/WizardConfiguratorTest.php` — unit tests for configurator.
- **Modified**: `tests/AppFrameworkTests/Application/Admin/Wizard/WizardTest.php` — additional integration test cases.
- **New**: `tests/application/assets/classes/TestDriver/Area/WizardTest/Preselection.php` — UI test screen that preselects wizard values and redirects.
- **Modified**: `tests/application/assets/classes/TestDriver/Area/WizardTest/Wizard/Step/Countries.php` — add `VALUE_COUNTRY_ID` constant for testing.
- ~~**Modified**: `tests/application/assets/classes/TestDriver/Area/WizardTest.php`~~ — No modification needed. Modes are auto-discovered by `AdminScreenIndex` via filesystem/classmap convention; `composer dump-autoload` registers the new `Preselection` class.

## Assumptions

- The `settingPrefix` for all current wizards is empty string (confirmed: `setSettingPrefix()` has no call sites). The configurator accepts it as an explicit parameter defaulting to `''` to document the coupling and future-proof.
- `generateNewSessionID()` is safe to call from outside a wizard instance (confirmed — it's `public static`).
- The consumer always knows the wizard's base URL from their current admin context (e.g., via `AdminURL`).
- Step names used in `setStepValue()` match the names passed to `addStep()` in the wizard's `_initSteps()` method (string identifiers like `'Countries'`, `'Ticket'`).
- Steps that return an empty array from `getDefaultData()` are completely unaffected by the constructor merge change (`array_merge(array(), $data) === $data`).

## Constraints

- All code must use `array()` syntax for array creation.
- New files must use `declare(strict_types=1)` and proper namespaces.
- No PHP enums, no `readonly` properties.
- Must run `composer dump-autoload` after adding new files.
- Preselection must not break existing wizard behavior — steps that don't use preselection must work identically.
- The wizard trait (`Application_Traits_Admin_Wizard`) must not be modified.
- The wizard interface (`Application_Interfaces_Admin_Wizardable`) must not be modified.

## Out of Scope

- Auto-advancing steps based on preselection (left to individual step `_process()` implementations, as the test wizard already demonstrates).
- Validation of step names/keys at set-time in the configurator (deferred to session creation per the research paper's decision).
- UI indicators showing which values were preselected vs. user-entered.

## Acceptance Criteria

1. A consumer can create a `WizardConfigurator` with a wizard base URL without needing a wizard instance or modifying any interface.
2. A consumer can set preselected values using step name + key constants via `getPreselection()->setStepValue()`.
3. Calling `getRedirectURL()` produces a valid wizard URL with a pre-created session containing the preselection data written to step data slots.
4. When the wizard initializes with a preselected session, steps receive the preselected values merged with their defaults on first render.
5. After a step is processed and saved, preselection data is naturally overwritten — normal session step data takes over.
6. Existing wizard functionality (without preselection) is unaffected — all existing wizard tests pass.
7. The API is fluent and chainable.
8. Step defaults (from `getDefaultData()`) are now properly applied on first visit for all steps — positively verified by test (latent bug fix).
9. A dedicated test screen in the test application uses `WizardConfigurator` to preselect values and redirect to the wizard, enabling manual UI verification.

## Testing Strategy

Unit tests verify the new classes in isolation and integration tests verify the end-to-end flow using the existing test wizard infrastructure. The Step constructor change is explicitly verified in both directions: defaults-only path and preselection+defaults path.

## Test Plan

- `tests/AppFrameworkTests/Application/Admin/Wizard/WizardPreselectionTest.php` — **New file**
  - Namespace: `testsuites\Application\Admin\Wizard` (consistent with existing `WizardTest.php` in the same directory).
  - `test_setAndGetStepValue` — Asserts `setStepValue()` stores and `getStepValues()` retrieves correctly. Covers AC #2.
  - `test_hasStepValues` — Asserts `hasStepValues()` returns false for unset steps, true for set steps. Covers AC #2.
  - `test_toArray` — Asserts serialization to the expected nested array format. Covers internal correctness.
  - `test_isEmpty` — Asserts `isEmpty()` returns true initially, false after setting a value. Covers internal correctness.
  - `test_fluentInterface` — Asserts `setStepValue()` returns `$this` for chaining. Covers AC #7.
  - `test_getStepNames` — Asserts `getStepNames()` returns correct list of step names with values. Covers internal correctness.

- `tests/AppFrameworkTests/Application/Admin/Wizard/WizardConfiguratorTest.php` — **New file**
  - Namespace: `testsuites\Application\Admin\Wizard` (consistent with existing `WizardTest.php` in the same directory).
  - `test_createSessionWithPreselection` — Asserts `getRedirectURL()` creates a session and the URL contains the session ID parameter. Covers AC #1, #3.
  - `test_preselectionWrittenToStepSlots` — Asserts preselection data is written to the session under the correct step data keys (format: `settingPrefix + '-step_' + stepName`). Covers AC #3.
  - `test_getPreselectionReturnsInstance` — Asserts `getPreselection()` returns a `WizardPreselection` instance. Covers AC #1.
  - `test_customSettingPrefix` — Asserts configurator with a non-empty prefix writes to correctly prefixed keys. Covers future-proofing (Concern 2 mitigation).

- `tests/AppFrameworkTests/Application/Admin/Wizard/WizardTest.php` — **Modified (add test cases)**
  - `test_preselectedValuesAppliedToStep` — Creates a preselected session externally via `WizardConfigurator`, sets `$_REQUEST['wizard'] = $sessionID` to inject the session ID (matching `initWizard()`'s `$this->request->getParam('wizard')` lookup), initializes the wizard with that session, asserts the Countries step receives the preselected `country_id` merged with defaults. Unsets `$_REQUEST['wizard']` in `tearDown()` to prevent cross-test leakage. Covers AC #4.
  - `test_preselectionOverwrittenAfterStepSave` — Sets `$_REQUEST['wizard'] = $sessionID` before invoking the wizard, processes a step, re-initializes, asserts that the step now uses its saved data (preselection slot overwritten by `saveSettings()`). Unsets `$_REQUEST['wizard']` in `tearDown()`. Covers AC #5.
  - `test_wizardWithoutPreselectionUnchanged` — Standard wizard flow without preselection, asserts behavior is identical to before. Covers AC #6.
  - `test_stepDefaultsAppliedOnFirstVisit` — Explicitly asserts that step defaults from `getDefaultData()` are now returned on first visit (no preselection, no prior session data). Positively verifies the constructor merge fix. Covers AC #8.

- `tests/application/assets/classes/TestDriver/Area/WizardTest/Preselection.php` — **New file (UI test screen)**
  - Manual UI test: navigate to `?page=wizardtest&mode=preselection&country=GB` → screen uses `WizardConfigurator` to preselect the country, then redirects to the wizard with the Countries step pre-filled. Covers AC #9.

## Documentation Updates

- `docs/agents/project-manifest/modules-overview.md` — Run `composer build` to refresh if wizard module entry needs updating.
- `.context/` — Run `composer build` to regenerate context documentation reflecting the new classes.
- `changelog.md` — Add entry for the new Wizard Preselection API feature.

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **Step constructor change affects all wizard steps on first visit** | The merge `array_merge(getDefaultData(), $data)` is a no-op for steps returning empty defaults (the majority). A dedicated test (`test_stepDefaultsAppliedOnFirstVisit`) positively verifies the changed behavior. Full test suite run validates no regressions. |
| **`settingPrefix` mismatch between configurator and wizard** | Configurator accepts `string $settingPrefix = ''` as an explicit constructor parameter, documenting the coupling. A test case (`test_customSettingPrefix`) verifies correct key formatting with non-empty prefix. Currently all wizards use the default empty prefix. |
| **Race condition if user opens wizard before redirect completes** | `generateNewSessionID()` is atomic (single session write). The session ID in the URL guarantees the correct session is loaded. |
| **Preselection values could be overwritten if wizard calls `saveSettings()` before step is visited** | `saveSettings()` only writes data for steps that have been instantiated (iterates `$this->steps`). Steps are lazily instantiated on visit. Pre-seeded slot data persists until the step's first visit and save. |
| **Future wizard with non-empty `settingPrefix` breaks configurator** | The `settingPrefix` is a required-knowledge parameter. If a wizard uses `setSettingPrefix()`, the consumer must pass the same value to the configurator. Documented in the constructor's PHPDoc. |
