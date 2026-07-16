## Synthesis

### Completion Status
- Date: 2026-07-16
- Status: COMPLETE
- Completed by: Standalone Developer Agent
- Archived in Ledger: 2026-07-16

### Outcome Summary

Fixed all non-CAS test failures in the framework test suite: a test authoring bug using a normalized ISO code, a test asserting against a class that unexpectedly declared the constant it was supposed to lack, and a stale Locales singleton caused by cross-test contamination from `resetCollection()` event listeners.

### Implementation Summary
- **DisposingTest ISO code fix:** Replaced `'uk'`/`'de'` with `'at'`/`'pl'` in `test_resetCollection`. The original codes conflicted with seed data — `'uk'` normalizes to `'gb'` (already seeded) causing a duplicate key violation, and `'de'` is directly seeded.
- **Locales singleton tearDown co-reset:** Added `clearRecordCache()` + `clearLocaleCache()` calls to `ApplicationTestCase::tearDown()` after the transaction rollback. This prevents the Countries `resetCollection()` event listener from leaving the Locales singleton in a stale/empty state for subsequent tests.
- **WizardPreselectionTest fix:** The `test_setStepValueByClass_throwsWithoutConstant` test incorrectly used `TestDriver_Area_WizardTest_Wizard_Step_Summary`, which declares `STEP_NAME = 'Summary'`. Created a new `TestDriver_Area_WizardTest_Wizard_Step_NoStepName` class that intentionally omits the constant, and updated the test to use it.
- **Deferred topics updated:** Added the generalized collection cache reset concept (central `CacheResettable` interface + `CollectionRegistry`) to DT-001 in `deferred-topics.md`, and updated the Current State section to reflect the new framework-level tearDown co-reset.

### Documentation Updates
- Updated `docs/agents/deferred-topics.md` — added generalized collection reset design notes and updated Current State/References sections.

### Verification Summary
- Tests run: `DisposingTest` (3 tests, 22 assertions, 2 incomplete), `WizardPreselectionTest` (9 tests, 18 assertions), `Locales\CollectionTest` (1 test), `Locales\LanguageTest` (1 test), `DeeplHelperTest` (4 tests), full suite excluding CAS (1188 tests, 3687 assertions)
- Static analysis run: not re-run (no production code changed, only test files and documentation)
- Result: All non-CAS tests pass. The 4 errors and 7 failures in the full run are exclusively LDAP/CAS integration tests requiring local config/server — pre-existing and out of scope.

### Code Insights
- [medium] (code-smell) `tests/AppFrameworkTests/DBHelper/DisposingTest.php`: ~~`test_resetCollection` tests the disposing behavior of `resetCollection()` by creating countries and then resetting the entire collection. This triggers event listeners registered by `Locales` (via `onAfterDeleteRecord`), causing side effects outside the test's scope. Consider whether this test should use the `TestDBCollection` fixture instead of the live `Application_Countries` singleton to avoid coupling to the event system.~~ **RESOLVED 2026-07-16:** Refactored `test_resetCollection` to use `TestDBCollection::getInstance()` and `createTestDBRecord()` instead of `AppFactory::createCountries()` and `createTestCountry()`. The `AppFactory` import was removed. All 3 tests pass (2 remain `markTestIncomplete`).
- [low] (debt) `tests/application/assets/classes/TestDriver/Area/WizardTest/Wizard/Step/Summary.php`: ~~The `render()` and `_process()` methods lack visibility keywords consistent with the other step classes (`Countries`, `Ticket`). `render()` uses default visibility, `_process()` is `public` — both should match the parent's contract explicitly.~~ **RESOLVED 2026-07-16:** Added `public` to `render()`. `_process()` already had `public`; no change needed.
- [low] (convention) `ApplicationTestCase::tearDown()`: ~~The co-reset calls are effective but fragile — adding a new singleton that caches country records requires remembering to add another co-reset line here. This is documented in DT-001 as a known limitation pending the event-based solution.~~ **ACKNOWLEDGED**

### Additional Comments
- The `composer dump-autoload` step was required after adding the new `NoStepName` step class (classmap autoloading).
- The DeeplHelper tests (Category 3 in the investigation) were cascading failures caused by the DisposingTest ISO bug. With that fix in place, they pass without any separate changes.
