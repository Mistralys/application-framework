# Plan

## Summary

Fix all non-environment test failures in the framework test suite discovered during the PR readiness check for the `fix-stale-collection-objects` branch. The failures fall into three actionable categories: a test authoring bug using a normalized ISO country code, a stale Locales singleton caused by cross-test contamination from `resetCollection()` event listeners, and a test asserting against a class that unexpectedly declares the constant it was supposed to lack.

## Architectural Context

The framework test suite (1186 tests) runs against a seeded test database managed by `TestSuiteBootstrap`. Collections use the singleton pattern via `AppFactory` and register cross-collection event listeners (e.g. `Locales` listens to `Countries::onAfterDeleteRecord`). Test isolation relies on `ApplicationTestCase::setUp()`/`tearDown()` with transaction rollbacks, but singleton in-memory caches persist across tests.

Key files:
- `tests/AppFrameworkTests/DBHelper/DisposingTest.php` — tests collection disposing/reset behavior
- `tests/AppFrameworkTests/Locales/CollectionTest.php` — tests Locales collection
- `tests/AppFrameworkTests/Locales/LanguageTest.php` — tests language enumeration
- `tests/AppFrameworkTests/UI/WizardPreselectionTest.php` — tests wizard step preselection
- `tests/application/assets/classes/TestDriver/` — test driver classes
- `src/classes/Application/Locales.php` — Locales singleton with `clearLocaleCache()`
- `tests/ApplicationTestCase.php` — base test case with setUp/tearDown

## Approach / Architecture

Three independent fixes, each targeting a different failure category:

1. **ISO code fix in DisposingTest** — Replace test country codes that conflict with seed data.
2. **Locales singleton co-reset in tearDown** — Wire the existing `clearLocaleCache()` and `clearRecordCache()` APIs into `ApplicationTestCase::tearDown()` to prevent stale singleton state from leaking across tests.
3. **WizardPreselectionTest fix** — Create a purpose-built test step class that omits the `STEP_NAME` constant, replacing the test's use of a class that unexpectedly declares it.

## Rationale

All three fixes are minimal, targeted corrections to test infrastructure. No production code changes are required. The Locales co-reset uses the `clearLocaleCache()` API already added on this branch rather than introducing new abstractions.

## Considered Alternatives

| Decision | Chosen Shape | Alternatives Considered | Trade-Off Summary |
|----------|--------------|-------------------------|-------------------|
| DisposingTest country codes | Replace `'uk'`/`'de'` with `'at'`/`'pl'` (non-seeded, non-normalized codes) | Use fictional codes like `'xx'`/`'yy'` | Real ISO codes are more realistic; fictional codes might trigger unexpected validation paths |
| Locales tearDown co-reset | Add `clearRecordCache()` + `clearLocaleCache()` in `ApplicationTestCase::tearDown()` | Replace `resetCollection()` with `clearRecordCache()` in DisposingTest; Central `CacheResettable` interface | tearDown co-reset is the simplest fix; the generalized interface is deferred to DT-001 |
| WizardPreselectionTest | New `NoStepName` test driver class without the constant | Remove constant from existing `Summary` class | Removing the constant from Summary could break other tests that depend on it |

## Pattern Alignment

- Follows the existing test driver pattern: new test fixture classes go under `tests/application/assets/classes/TestDriver/` — consistent with `Summary.php`, `Countries.php`, `Ticket.php` in the same directory.
- Follows the existing tearDown cleanup pattern in `ApplicationTestCase` — adding cache resets alongside the existing transaction rollback.

## Detailed Steps

### Step 1: Fix DisposingTest ISO codes

In `tests/AppFrameworkTests/DBHelper/DisposingTest.php`, method `test_resetCollection`:
- Replace `'uk'` / `'United Kingdom'` with `'at'` / `'Austria'` (or another code not in `SEED_COUNTRIES` and not subject to normalization).
- Replace `'de'` / `'Germany'` with `'pl'` / `'Poland'` (same criteria).

### Step 2: Create NoStepName test driver class

Create `tests/application/assets/classes/TestDriver/Area/WizardTest/Wizard/Step/NoStepName.php`:
- Extend the wizard step base class.
- Intentionally omit the `STEP_NAME` constant.
- Implement required abstract methods (`render()`, `_process()`).

Run `composer dump-autoload` after creating the file (classmap autoloading).

### Step 3: Fix WizardPreselectionTest

In `tests/AppFrameworkTests/UI/WizardPreselectionTest.php`, method `test_setStepValueByClass_throwsWithoutConstant`:
- Replace the reference to `TestDriver_Area_WizardTest_Wizard_Step_Summary` with `TestDriver_Area_WizardTest_Wizard_Step_NoStepName`.

### Step 4: Add Locales co-reset to tearDown

In `ApplicationTestCase::tearDown()`, after the transaction rollback:
- Add `AppFactory::createCountries()->clearRecordCache();`
- Add `AppFactory::createLocales()->clearLocaleCache();`

### Step 5: Verify fixes

1. Run `DisposingTest` in isolation: `composer test-file -- tests/AppFrameworkTests/DBHelper/DisposingTest.php`
2. Run `WizardPreselectionTest` in isolation: `composer test-file -- tests/AppFrameworkTests/UI/WizardPreselectionTest.php`
3. Run `Locales\CollectionTest` and `Locales\LanguageTest` in isolation.
4. Run the full suite excluding CAS: confirm all non-CAS tests pass.

### Step 6: Update deferred topics

In `docs/agents/deferred-topics.md`:
- Add the generalized collection cache reset concept (central `CacheResettable` interface + `CollectionRegistry`) to DT-001.
- Update the Current State section to reflect the new framework-level tearDown co-reset.

### Step 7: Discard generated file noise

Run `git restore docs/agents/project-manifest/modules-overview.md` to discard the timestamp-only diff from a prior `build-docs` run.

## Dependencies

- The `clearLocaleCache()` method on the `Locales` class must already exist (added earlier on the `fix-stale-collection-objects` branch).
- The `clearRecordCache()` method on collection classes must already exist (added earlier on the same branch).

## Required Components

- `tests/AppFrameworkTests/DBHelper/DisposingTest.php` — edit
- `tests/AppFrameworkTests/UI/WizardPreselectionTest.php` — edit
- `tests/application/assets/classes/TestDriver/Area/WizardTest/Wizard/Step/NoStepName.php` — **new**
- `tests/ApplicationTestCase.php` — edit
- `docs/agents/deferred-topics.md` — edit

## Assumptions

- The `clearLocaleCache()` and `clearRecordCache()` APIs are already implemented and functional on this branch.
- The test database is seeded (`composer seed-tests` has been run).
- LDAP/CAS test failures are environment-specific and out of scope.

## Constraints

- No production code changes — all fixes are in test files and documentation.
- Must use `array()` syntax (not `[]`) per project conventions.
- New class files require `composer dump-autoload` for classmap autoloading.

## Out of Scope

- LDAP/CAS test failures (Category 4) — these require local environment configuration.
- Generalized `CacheResettable` interface / `CollectionRegistry` — deferred to DT-001.
- Refactoring `DisposingTest` to use `TestDBCollection` instead of live singletons.

## Acceptance Criteria

- `DisposingTest::test_resetCollection` passes without ISO duplicate key violations.
- `WizardPreselectionTest::test_setStepValueByClass_throwsWithoutConstant` passes (exception is thrown as expected).
- `Locales\CollectionTest::test_createCollection` passes in full-suite context (not just isolation).
- `Locales\LanguageTest::test_collectionIsNotEmpty` passes in full-suite context.
- `DeeplHelperTest` (all 4 tests) pass without separate changes (cascading fix from Step 1).
- Full suite excluding CAS: all non-CAS tests pass (1188 tests, 3687 assertions).

## Testing Strategy

Run each fix in isolation first, then run the full suite (excluding CAS) to confirm no regressions and that the ordering-dependent failures are resolved.

## Test Plan

- `DisposingTest::test_resetCollection` — asserts country creation and collection reset work without duplicate key errors — covers AC 1
- `WizardPreselectionTest::test_setStepValueByClass_throwsWithoutConstant` — asserts exception is thrown for a step class without `STEP_NAME` — covers AC 2
- `Locales\CollectionTest::test_createCollection` — asserts `getAll()` returns non-empty after full-suite execution — covers AC 3
- `Locales\LanguageTest::test_collectionIsNotEmpty` — asserts `getAll()` returns non-empty after full-suite execution — covers AC 4
- `DeeplHelperTest` (4 tests) — asserts DeepL language mapping works — covers AC 5
- Full suite excluding CAS — regression check — covers AC 6

## Documentation Updates

- `docs/agents/deferred-topics.md` — add DT-001 entry for generalized collection reset design, update Current State section.

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **New country codes (`'at'`, `'pl'`) might also conflict** | Verified against `SEED_COUNTRIES` — neither is in the seed list |
| **tearDown co-reset is fragile** | Documented as known limitation in DT-001; generalized event-based solution planned |
| **NoStepName class might need updating if wizard base class changes** | Minimal class with only required abstract methods; low maintenance burden |
