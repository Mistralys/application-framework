## Synthesis

### Completion Status
- Date: 2026-07-22
- Status: COMPLETE
- Completed by: Standalone Developer Agent
- Archived in Ledger: 2026-07-22

### Outcome Summary

All six targeted changes across the Application Framework and HCP Editor were implemented successfully. The security gap where `SetMailingStateAPI::handleFinalize()` lacked a Tier 2 rights check is closed, the test infrastructure for API key methods is improved with a convenience factory, and several smaller code quality improvements (log enrichment, dead cache fix, UI template completeness, test fixture cleanup) are in place.

### Implementation Summary

- **Step 1 (HCP Editor) — Tier 2 `RIGHT_FINALIZE_MAILS` check:** Added an early-exit guard at the entry of `SetMailingStateAPI::handleFinalize()` that rejects finalization with HTTP 403 / `ERROR_INSUFFICIENT_RIGHTS` (183006) when the API key pseudo-user lacks `RIGHT_FINALIZE_MAILS`.
- **Step 1a (HCP Editor) — `callSetMailingState()` prerequisite fix:** Updated the test helper to call `addMethod(SetMailingStateAPI::METHOD_NAME)` and grant both `RIGHT_EDIT_MAILS` and `RIGHT_FINALIZE_MAILS` to satisfy the Tier 1 and Tier 2 authorization gates before `processReturn()` is called.
- **Step 2 (HCP Editor) — Tier 2 test cases:** Added `test_handleFinalize_deniedWithoutFinalizeRight()` (AC-01) and `test_handleFinalize_allowedWithFinalizeRight()` (AC-02) to `SetMailingStateAPITest`.
- **Step 3 (Framework) — `createTestAPIKeyForMethod()` helper:** Added the single-call factory to `APIClientTestTrait`, eliminating the `createTestAPIKey()` + `addMethod()` boilerplate pattern.
- **Step 4 (Framework) — Refactored existing tests:** Updated all positive-path tests in `KeyAuthorizationTest` and `APIKeyParameterTest` to use `createTestAPIKeyForMethod()`. `test_methodAccessDenied()` was deliberately left unchanged (it must not grant the method).
- **Step 5 (Framework) — Log enrichment:** Both denial paths in `BaseAPIMethod::authorize()` now emit structured log messages. The method-not-granted path logs key ID and method name. The insufficient-rights path additionally logs the pseudo-user ID and required right name.
- **Step 6 (Framework) — HTTP 403 in documentation UI:** Extended `APIMethodDetailTmpl::resolveHTTPStatusCodes()` to include HTTP 403 conditionally for methods implementing `APIKeyMethodInterface`. Added the missing `use Application\API\Clients\API\APIKeyMethodInterface;` import.
- **Step 7 (Framework) — `createUser()` cache fix:** Added `self::$knownUsers[$userID] = $user;` in the `if ($user instanceof Application_User)` branch of `Application::createUser()`. Added `clearUserCache()` static method for test isolation.
- **Step 7.2 (Framework) — Cache test:** Created `CreateUserCacheTest` asserting that `createUser()` returns the same instance on a second call with the same ID.
- **Step 8 (Framework) — Test fixture cleanup:** Removed the `manageParamAPIKey()->register()` call from `TestAPIKeyMethod::init()` and `TestAPIKeyMethodWithRight::init()`, leaving empty-body overrides to satisfy the abstract method contract.

### Documentation Updates

- `docs/agents/project-manifest/constraints.md` (Framework) — Rewrote the `createUser()` gotcha section to reflect the working cache and document `clearUserCache()` for test isolation.
- `docs/agents/project-manifest/testing.md` (Framework) — Updated the API Key Method Tests section to reference `createTestAPIKeyForMethod()` as the recommended pattern.
- `src/classes/Application/API/README.md` (Framework) — Extended the Authorization bullet to document enriched log messages and Tier 2 rights pattern with a pointer to the HCP Editor example.
- `assets/classes/Maileditor/API/README.md` (HCP Editor) — Replaced the "Known gap" warning with the documented Tier 2 pattern and code example. Added Verification subsection referencing the new test methods. Restored the GetRequiredRightTest reference that was removed during editing.

### Verification Summary

- Tests run: `KeyAuthorizationTest`, `APIKeyParameterTest`, `CreateUserCacheTest` (framework); `SetMailingStateAPITest` (HCP Editor)
- Static analysis run: `composer analyze` — framework and HCP Editor
- Result: All tests pass. PHPStan reports zero errors in both projects.

### Code Insights

- [low] (debt) `tests/application/assets/classes/TestDriver/API/TestAPIKeyMethod.php`: ~~The `init()` method is abstract in `BaseAPIMethod` but its purpose (registering reserved params, then allowing method-specific setup) makes it easy to misuse. A future improvement could rename it `configure()` or add a docblock stating that reserved params are already registered in `initReservedParams()` before `init()` is called, so overrides should not re-register them.~~ **DONE** — Docblock updated in `BaseAPIMethod::init()` to document that reserved params are already registered and must not be re-registered in overrides.
- [low] (improvement) `src/classes/Application/Application.php`: ~~The `clearUserCache()` method was added for test isolation but has no direct test of the cache-miss path after clearing. Consider adding a second assertion to `CreateUserCacheTest` that clears the cache and verifies that `createUser()` returns a *different* instance on the next call if test isolation guarantees demand it.~~ **DONE** — Added `test_clearUserCacheEvictsInstances()` to `CreateUserCacheTest`, asserting that after `clearUserCache()` a subsequent `createUser()` call returns a distinct instance.
- [low] (improvement) `tests/AppFrameworkTestClasses/API/APIClientTestTrait.php`: ~~The trait lacks a `createTestAPIKeyWithRights(string $methodName, array $rights)` convenience factory. Tests that grant both method access and rights (as in `KeyAuthorizationTest::test_userRightsGranted()`) still require two lines. This can be added in a future pass when more tests are onboarded.~~ **DONE** — Added `createTestAPIKeyWithRights()` to the trait. `test_methodAccessGrantedIndividual()` and `test_userRightsGranted()` in `KeyAuthorizationTest` updated to use it.
- [low] (debt) `SetMailingStateAPITest` — ~~The `test_handleFinalize_deniedWithoutFinalizeRight()` test duplicates the API key setup boilerplate instead of using `createTestAPIKeyForMethod()`, because it needs to omit `RIGHT_FINALIZE_MAILS`. A dedicated `createTestAPIKeyForMethodWithRights(string $methodName, array $rights)` helper would clean this up.~~ **DONE** — Both `callSetMailingState()` and `test_handleFinalize_deniedWithoutFinalizeRight()` updated to use `createTestAPIKeyWithRights()`. Also renamed the `$key` variable in `callSetMailingState()` to `$apiKey` to eliminate the pre-existing collision with the `foreach` loop variable.

### Additional Comments

- The `#[APIMethodTest]` attribute import (`use MailEditorTestClasses\Attributes\APIMethodTest;`) was accidentally removed during an early edit and immediately restored. No net change.
- The `init()` body removal from test fixtures was revised: since `BaseAPIMethod::init()` is declared `abstract`, the override cannot be removed entirely — only its `register()` body was cleared.
- PHPUnit Notice in the APIKey test run ("no expectations were configured") is a pre-existing issue unrelated to this plan.
