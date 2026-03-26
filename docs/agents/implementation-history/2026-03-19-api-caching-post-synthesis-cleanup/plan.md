# Plan: API Caching Post-Synthesis Cleanup

**Date:** 2026-03-19
**Predecessor:** `docs/agents/plans/2026-03-18-api-caching-synthesis-followup/`
**Status at handoff:** 6 WPs complete; `composer test` exits with code 2 due to 7 pre-existing failures.

---

## Plan Overview

This plan addresses the five actionable items surfaced in the 2026-03-18 synthesis report. They are
decomposed into four work packages ordered medium-priority first, low-priority second.

| WP | Title | Priority | Type |
|----|-------|----------|------|
| WP-001 | Fix `HtaccessGeneratorTest` constant/assertion mismatch | Medium | Code |
| WP-002 | Investigate and resolve `RecordTieInTest::test_ancestryHandling` failure | Medium | Code |
| WP-003 | Mark environment-dependent tests with `@group` or skip guards | Medium | Test hygiene |
| WP-004 | Add `phpstan-result.txt` update convention to project manifest | Low | Documentation |
| WP-005 | Update `readFromCache()` PHPDoc in `CacheableAPIMethodInterface` | Low | Documentation |
| WP-006 | Fix `DeepLTestScreen::handleTest()` double-instantiation | Low | Code |
| WP-007 | Rename `.idea/runConfigurations/Application_SettingsTest.xml` | Low | Cosmetic |

> WP-003 covers the two network-dependent test pairs (`AjaxRequestTest`, `Connectors/RequestTest`)
> as a single hygiene work package because both share the same root cause and the same fix pattern.

---

## Summary

The 2026-03-18 synthesis session concluded with `composer test` reporting 7 failures across four test
classes. Additionally, the synthesis surfaced three low-priority housekeeping items: a stale interface
PHPDoc, a misnamed IDE run-configuration file, and a pre-existing object-instantiation code smell in
the DeepL admin screen. This plan resolves every item, bringing the full test suite to a green exit
code and closing the remaining housekeeping debt.

---

## Architectural Context

### Test suite
All unit tests live under `tests/AppFrameworkTests/`. The single suite `Framework Tests` is run with
`composer test` (PHPUnit 13, PHP 8.4+). Two categories of test failure are present:

- **Logic failures** — assertions that do not match the production implementation:
  - `HtaccessGeneratorTest` (12+ tests) — the test constant assertion `test_defaultRewriteBaseConstant`
    asserts `'/api/'` but `HtaccessGenerator::DEFAULT_REWRITE_BASE` is `''` (empty string). Several
    other tests that call `assertStringContainsString('RewriteBase /api/', ...)` on the default content
    will also fail because when `rewriteBase` is empty the `RewriteBase` line is omitted entirely.
    **Root cause:** either the constant was changed without updating the tests, or the tests were
    written against a design spec that was later revised. The implementation comment says "When left
    empty… the directive is omitted", which is coherent. The tests are wrong.
  - `RecordTieInTest::test_ancestryHandling` — the assertion `assertTrue($parentTieIn->isRecordSelected())`
    likely fails because the test sets `$_REQUEST[TestDBCollection::REQUEST_PRIMARY_NAME]` but the
    tie-in's `isRecordSelected()` checks the collection by request, which requires the test database
    record to exist. The test creates a record via `createTestDBRecord()` and then sets the request
    var to its ID, which should work; the more probable explanation is that the `TestDBRecordTieIn`
    (not `MythologicalRecordSelectionTieIn`) reads its record from a different request var and the
    `$_REQUEST` pollution from a prior test in the same run causes the wrong ID to be seen. Diagnosis
    is required before a fix is proposed.

- **Environment-dependent failures** — tests that make live HTTP requests to `APP_URL`:
  - `AjaxRequestTest` — uses `ConnectorTestTrait` which drives `InternalAjaxConnector` over a live
    HTTP request to the test application at `APP_URL`.
  - `Connectors/RequestTest` (`test_adapterSockets`, `test_adapterCURL`) — directly requests
    `APP_URL.'/request-tests/endpoint-json-200.php'` using sockets and cURL respectively.
  - In CI or local environments without a running web server at `APP_URL`, these fail with network
    errors.

### Relevant files
- `src/classes/Application/API/OpenAPI/HtaccessGenerator.php` — constant `DEFAULT_REWRITE_BASE = ''`
- `tests/AppFrameworkTests/API/OpenAPI/HtaccessGeneratorTest.php` — assertions reference `/api/`
- `tests/AppFrameworkTests/Application/Admin/RecordTieInTest.php` — `test_ancestryHandling`
- `tests/AppFrameworkTests/Ajax/AjaxRequestTest.php` — live-HTTP tests
- `tests/AppFrameworkTests/Connectors/RequestTest.php` — live-HTTP tests (`test_adapterSockets`,
  `test_adapterCURL`)
- `src/classes/Application/Collection/Admin/BaseRecordSelectionTieIn.php` — `isEnabled()` / `isRecordSelected()`
- `src/classes/Application/API/Cache/CacheableAPIMethodInterface.php` — `readFromCache()` PHPDoc
- `.idea/runConfigurations/Application_SettingsTest.xml` — misnamed run configuration
- `src/classes/DeeplHelper/Admin/Screens/DeepLTestScreen.php` — `handleTest()` double-instantiation
- `docs/agents/project-manifest/constraints.md` — home for PHPStan conventions
- `phpstan-result.txt` — baseline PHPStan output (currently shows 6 errors)

---

## Approach / Architecture

Each work package is self-contained and can be executed independently (no inter-WP dependencies
except that WP-001, WP-002, and WP-003 should all land before re-running `composer test` to verify
the suite is green).

### WP-001 — HtaccessGenerator test alignment
The implementation is correct: omitting `RewriteBase` when the value is empty is the right default
for portability. The tests were written against an earlier version of the constant. The fix is to
update the tests to match the implementation:
- Replace all `'/api/'` references in assertions that target the *default* behaviour with the correct
  empty-string expectation.
- The tests for the custom `RewriteBase` (e.g., `/myapp/api/`) are unaffected.
- The `test_defaultRewriteBaseConstant` assertion must be changed to assert `''`.
- The `test_getContent_defaultRewriteBase` test must assert that the content does *not* contain a
  `RewriteBase` line when the default is used, not that it contains `RewriteBase /api/`.
- `test_setRewriteBase_affectsContent` currently asserts
  `assertStringNotContainsString('RewriteBase /api/', ...)` — this will need to become a check that
  `RewriteBase /myapp/api/` is present and no other `RewriteBase` line appears.

### WP-002 — RecordTieInTest ancestry failure
The `test_ancestryHandling` failure must be diagnosed first:
1. Run `composer test-filter -- test_ancestryHandling` in isolation and capture the full failure
   message including which assertion fails.
2. Inspect whether `createTestDBRecord()` inserts a record and whether the record's ID is correctly
   placed in `$_REQUEST` before `isRecordSelected()` is invoked.
3. Check for `$_REQUEST` state leaking from a prior test (PHPUnit does not reset superglobals between
   tests by default).
4. Apply the minimal fix: either reset `$_REQUEST` in `setUp()` / `tearDown()`, or update the
   assertion if the expected behaviour was wrong.

### WP-003 — Network-dependent test guard
`AjaxRequestTest` and `Connectors/RequestTest::test_adapterSockets` / `test_adapterCURL` require a
live web server. The correct pattern for the project (per `testing.md`) is to run tests with
`composer test`, so these tests need a guard that makes them self-skipping when the environment is
not suitable, rather than failing with a network error. Options:
- Add `@group live-http` annotation and document that `composer test` skips that group in environments
  where `TESTS_BASE_URL` is not reachable.
- Or use `markTestSkipped()` in `setUp()` when the URL is unreachable (a simple socket-connect probe
  against `TESTS_BASE_URL`).
The preferred approach is the `@group live-http` annotation so CI can choose to include or exclude
them, and add the group exclusion to `phpunit.xml` or document the override in `testing.md`. This
avoids modifying the test logic itself.

### WP-004 — PHPStan baseline convention (documentation only)
Add a new section to `docs/agents/project-manifest/constraints.md` under the existing PHPStan rules
block, stating: "Any commit that changes the PHPStan error count must also update `phpstan-result.txt`
in the same commit." No code change is required.

### WP-005 — CacheableAPIMethodInterface PHPDoc
Add a sentence to the `readFromCache()` docblock noting that the method may also return `null` when
the cache file is corrupt and has been auto-recovered, consistent with the behaviour documented in
`CacheableAPIMethodTrait`.

### WP-006 — DeepLTestScreen double-instantiation
In `handleTest()`, replace `AppFactory::createDeeplHelper()` with `$this->helper` (already assigned
in `_handleActions()`). This eliminates one redundant `DeeplHelper` instantiation per form
submission. The `$this->helper` property is typed `private DeeplHelper $helper` and is assigned
unconditionally before `handleTest()` is called, so the substitution is safe.

### WP-007 — IDE run configuration rename
Rename `.idea/runConfigurations/Application_SettingsTest.xml` to `SettingsTest.xml`. The file
content already references the correct class and path (`SettingsTest`). Only the filename is stale.

---

## Rationale

- Fixing the test-vs-implementation mismatch in WP-001 is the correct direction: the implementation
  design is deliberate and well-documented; the tests simply lagged behind.
- Diagnosing before patching in WP-002 avoids masking a real bug in `BaseRecordSelectionTieIn`
  with a test workaround.
- Using `@group live-http` in WP-003 is preferable to `markTestSkipped` with a network probe because
  it keeps the tests portable across CI configurations without adding runtime network probing overhead
  to every suite run.
- PHPStan convention (WP-004) belongs in `constraints.md` alongside the existing `trait.unused`
  rule — it is a process constraint, not a code pattern.

---

## Detailed Steps

### WP-001: Fix HtaccessGeneratorTest

1. Open `tests/AppFrameworkTests/API/OpenAPI/HtaccessGeneratorTest.php`.
2. `test_defaultRewriteBaseConstant` — change the assertion from `'/api/'` to `''`.
3. `test_getContent_defaultRewriteBase` — replace the `assertStringContainsString('RewriteBase /api/', ...)` assertion with `assertStringNotContainsString('RewriteBase', ...)` to verify the directive is omitted entirely.
4. `test_setRewriteBase_affectsContent` — the `assertStringNotContainsString('RewriteBase /api/', ...)` line is already correct (custom base is `/myapp/api/`, not `/api/`). Verify and leave it.
5. `test_getRewriteBase_returnsDefault` — change the expected value from `'/api/'` to `''`.
6. Run `composer test-file -- tests/AppFrameworkTests/API/OpenAPI/HtaccessGeneratorTest.php` and confirm all tests pass.

### WP-002: Diagnose and fix RecordTieInTest

1. Run `composer test-filter -- test_ancestryHandling` and read the full failure output.
2. Identify which assertion fails and the actual vs expected values.
3. If `$_REQUEST` bleed from a sibling test is the cause, add `tearDown()` to reset the relevant keys or use `$_REQUEST = []` in `setUp()`.
4. If `createTestDBRecord()` is not committing the record before the assertion, check whether the base class wraps test runs in a rolled-back transaction and add an explicit flush if needed.
5. Run `composer test-file -- tests/AppFrameworkTests/Application/Admin/RecordTieInTest.php` and confirm all tests pass.

### WP-003: Mark live-HTTP tests with @group annotation

1. Add `@group live-http` PHPDoc annotation to:
   - `AjaxRequestTest::test_unknownMethodError` and `AjaxRequestTest::testGetJSONDataCall`
     (or to the class-level docblock, which applies to all methods).
   - `Connectors/RequestTest::test_adapterSockets` and `::test_adapterCURL`.
2. Add a `<groups>` exclusion entry to `phpunit.xml` excluding `live-http` from the default suite,
   **or** document in `docs/agents/project-manifest/testing.md` that the group must be explicitly
   included when a web server is available (e.g., `composer test-group -- live-http`).
3. Confirm `composer test` exits 0 with the group excluded, and that running
   `composer test-group -- live-http` correctly executes the network tests when the environment
   supports them.

### WP-004: Add phpstan-result.txt convention to constraints.md

1. Open `docs/agents/project-manifest/constraints.md`.
2. Locate the PHPStan section (currently contains the `trait.unused` rule).
3. Append a new rule block:

   ```
   ### Rule
   **Any commit that changes the PHPStan error count must update `phpstan-result.txt` in the
   same commit.** The file serves as a human-readable baseline snapshot. When the count drifts,
   agents in subsequent sessions compare against a stale baseline and may incorrectly assess the
   static-analysis health of the codebase.

   Regenerate with:
   ```
   composer phpstan 2>&1 | tee phpstan-result.txt
   ```
   or the project's equivalent PHPStan composer script.
   ```

### WP-005: Update readFromCache() PHPDoc in CacheableAPIMethodInterface

1. Open `src/classes/Application/API/Cache/CacheableAPIMethodInterface.php`.
2. Update the `readFromCache()` docblock (currently line 57-61) to add a sentence about corrupt-cache recovery, e.g.:
   > "Also returns null if the cached entry is corrupt and has been auto-removed (see `CacheableAPIMethodTrait` for recovery behaviour)."
3. Run `composer test-file -- tests/AppFrameworkTests/API/Cache/APICacheIntegrationTest.php` to
   confirm no regression.

### WP-006: Fix DeepLTestScreen double-instantiation

1. Open `src/classes/DeeplHelper/Admin/Screens/DeepLTestScreen.php`.
2. In `handleTest()` at line 99, replace:
   ```php
   $translator = AppFactory::createDeeplHelper()->createTranslator($sourceCountry, $targetCountry);
   ```
   with:
   ```php
   $translator = $this->helper->createTranslator($sourceCountry, $targetCountry);
   ```
3. Verify the file compiles cleanly (no stray `AppFactory::createDeeplHelper()` calls remain in
   `handleTest()`). The `AppFactory` import can remain — it is still used in `_handleActions()`.
4. Run `composer phpstan` to confirm no new static-analysis errors.

### WP-007: Rename Application_SettingsTest.xml

1. In `.idea/runConfigurations/`, rename `Application_SettingsTest.xml` to `SettingsTest.xml`.
   (Git: `git mv .idea/runConfigurations/Application_SettingsTest.xml .idea/runConfigurations/SettingsTest.xml`)
2. Verify the file content is unchanged and still references class `SettingsTest`.
3. Confirm PhpStorm (or whatever IDE is in use) no longer lists a stale configuration entry.

---

## Dependencies

- WP-001, WP-002, WP-003 are independent of each other and can be executed in parallel.
- WP-004, WP-005, WP-006, WP-007 are fully independent and can be executed in any order.
- A final green-suite run (`composer test`) should be performed after WP-001, WP-002, and WP-003
  are all complete and before the overall plan is marked done.

---

## Required Components

### Files to modify
- `tests/AppFrameworkTests/API/OpenAPI/HtaccessGeneratorTest.php` (WP-001)
- `tests/AppFrameworkTests/Application/Admin/RecordTieInTest.php` (WP-002, if fix is needed)
- `tests/AppFrameworkTests/Ajax/AjaxRequestTest.php` (WP-003)
- `tests/AppFrameworkTests/Connectors/RequestTest.php` (WP-003)
- `phpunit.xml` (WP-003, if group exclusion is added there)
- `docs/agents/project-manifest/constraints.md` (WP-004)
- `docs/agents/project-manifest/testing.md` (WP-003 documentation, if group is documented there)
- `src/classes/Application/API/Cache/CacheableAPIMethodInterface.php` (WP-005)
- `src/classes/DeeplHelper/Admin/Screens/DeepLTestScreen.php` (WP-006)
- `.idea/runConfigurations/Application_SettingsTest.xml` → `SettingsTest.xml` (WP-007)

### Files to read (context only, not modified)
- `src/classes/Application/API/OpenAPI/HtaccessGenerator.php`
- `src/classes/Application/Collection/Admin/BaseRecordSelectionTieIn.php`
- `src/classes/Application/API/Cache/CacheableAPIMethodTrait.php`
- `phpstan-result.txt`

---

## Assumptions

- The `HtaccessGenerator::DEFAULT_REWRITE_BASE = ''` implementation is intentional and correct; the
  tests are the source of truth to fix.
- `RecordTieInTest::test_ancestryHandling` fails due to test-environment state rather than a bug in
  `BaseRecordSelectionTieIn` — but diagnosis (WP-002, step 1) must confirm this before committing
  to a fix.
- The project uses a PHPStan composer script that can be piped to regenerate `phpstan-result.txt`.
  If it does not, the convention text in WP-004 should be adjusted to match the actual invocation.
- `.idea/` is tracked in version control (confirmed by the git status showing it as modified).

---

## Constraints

- No changes to production logic are permitted for WP-001 or WP-003 — only tests and configuration.
- WP-006 is a pure refactor: no change to observable behaviour (same `DeeplHelper` instance, same
  translator created from the same factory-produced helper).
- `phpstan-result.txt` should not be regenerated as part of this plan unless it drifts as a
  side-effect of WP-006 (unlikely, as the change is pure substitution of equivalent calls).

---

## Out of Scope

- Any new feature work for the API caching system.
- Fixing PHPStan errors recorded in `phpstan-result.txt` (those 6 errors pre-date this session).
- Addressing other pre-existing test failures not listed in the synthesis report.
- Renaming or restructuring the `Connectors/RequestTest.php` class (only the two network methods
  receive group annotations; the rest of the file is healthy and should not be touched).

---

## Acceptance Criteria

- `composer test` exits with code 0 (no failures, no errors) after WP-001, WP-002, and WP-003 are complete.
- `HtaccessGeneratorTest` passes all tests with no skips.
- `RecordTieInTest` passes all tests, including `test_ancestryHandling`.
- `AjaxRequestTest` and `Connectors/RequestTest` do not emit errors when run as part of the default suite (network tests are either skipped or excluded by group).
- `docs/agents/project-manifest/constraints.md` contains a new rule about updating `phpstan-result.txt`.
- `CacheableAPIMethodInterface::readFromCache()` docblock mentions corrupt-cache recovery.
- `DeepLTestScreen::handleTest()` uses `$this->helper` instead of `AppFactory::createDeeplHelper()`.
- `.idea/runConfigurations/Application_SettingsTest.xml` no longer exists; `SettingsTest.xml` exists in its place with identical content.

---

## Testing Strategy

- **WP-001:** `composer test-file -- tests/AppFrameworkTests/API/OpenAPI/HtaccessGeneratorTest.php` — all tests must pass.
- **WP-002:** `composer test-file -- tests/AppFrameworkTests/Application/Admin/RecordTieInTest.php` — all tests must pass, including `test_ancestryHandling`.
- **WP-003:** `composer test` — the network tests must not cause failures in the default run. `composer test-group -- live-http` (or equivalent) must run those tests when a web server is available.
- **WP-004, WP-005, WP-007:** Documentation and cosmetic changes; verified by reading the updated files.
- **WP-006:** `composer phpstan` — no new errors introduced. Visual inspection of `handleTest()`.
- **Full regression:** `composer test` exits 0 after all WPs are complete.

---

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **`test_ancestryHandling` failure is a real bug in `BaseRecordSelectionTieIn`** | Diagnose first (WP-002 step 1-2); if the source class is at fault, fix it there and update the test to match the corrected behaviour. Do not paper over a real bug with a test skip. |
| **`phpunit.xml` group exclusion breaks other suite configurations** | Review `phpunit.xml` before editing; if the file is shared across environments in a fragile way, prefer documenting the group skip in `testing.md` instead of hard-coding it. |
| **Renaming the IDE run configuration file breaks an open PhpStorm project** | The rename is a git mv; PhpStorm will re-index on next open. Low risk for a single-file rename. |
| **WP-006 fix hides a latent bug if `$this->helper` is null in an edge code path** | The property is `private DeeplHelper $helper` (non-nullable, not initialized in declaration), and `_handleActions()` always assigns it before `handleTest()` is ever called. PHP would throw a typed property error if it were unset, making any edge case immediately visible. |
