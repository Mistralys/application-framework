# Plan

## Plan Audit Cycles
- Audits: 2 — Plan Auditor v1.7.0
- Architectural Reviews: none — Plan Architect Reviewer v2.2.0

## Prior Project Context

- `2026-08-25-api-method-right-grants` introduced the `APIKeyRights` authority object, the versioned method index, mandatory `getRequiredRight()` declaration, and two admin screens (Methods Selection, Rights Overview). The synthesis identified a Medium-priority server-side edit-right guard gap in the Methods Selection screen and deprecated the `createTestAPIKeyWithRights()` test helper in favor of `createTestAPIKeyForMethod()`.
- `2026-08-21-fix-api-test-authorization` and its rework migrated all 20 HCP Editor API test files from `createTestAPIKey()` to `createTestAPIKeyWithRights()`. This plan performs the second-wave migration to the renamed replacement `createTestAPIKeyForMethod()`.
- `2026-08-24-fix-api-test-authorization-rework-1` documented the `createTestAPIKeyWithRights()` convention in HCP Editor's testing manifest, API method authoring guide, and test scaffold generator. Those references now need updating to `createTestAPIKeyForMethod()`.

## Summary

Address three actionable items from the `2026-08-25-api-method-right-grants` synthesis: (1) close the Medium-priority security gap where `APIKeyMethodsAction::handle_saveMethodSelection()` and the confirm action registration lack a server-side edit-right guard, (2) migrate all 22 HCP Editor `createTestAPIKeyWithRights()` call sites to `createTestAPIKeyForMethod()` and update the test scaffold generator, and (3) rebuild the HCP Editor method index under the v2 schema to validate all declared rights.

## Architectural Context

**Methods Selection screen.** `APIKeyMethodsAction` (`src/classes/Application/API/Admin/Screens/Mode/View/APIKeys/APIKeyMethodsAction.php`) renders a multi-select grid of available API methods. The save button is gated in `_handleSidebar()` via `$this->user->can(APIScreenRights::SCREEN_API_KEYS_METHODS_EDIT)`, but the `addConfirmAction()` call in `createDataGrid()` and the `handle_saveMethodSelection()` callback have no server-side right check. A view-only admin who knows the action name could craft a POST to execute the save.

**Deprecated test helper.** `APIClientTestTrait::createTestAPIKeyWithRights()` (`tests/AppFrameworkTestClasses/API/APIClientTestTrait.php`) is deprecated — it ignores the `$rights` array and delegates to `createTestAPIKeyForMethod()`. The 22 HCP Editor call sites across 20 test files still use the deprecated signature.

**Test scaffold generator.** `tools/generate-api-test.php` in HCP Editor generates test boilerplate with `$this->createTestAPIKey()` (no method grant) and a TODO comment referencing the deprecated `createTestAPIKeyWithRights()`.

## Approach / Architecture

Three focused changes spanning two repositories:

1. **Defense-in-depth guard (framework).** Move the `addConfirmAction()` registration inside the existing `!areAllMethodsGranted()` block and further gate it behind the edit-right check. Add a server-side early return in `handle_saveMethodSelection()` that checks `$this->user->can(APIScreenRights::SCREEN_API_KEYS_METHODS_EDIT)` and redirects with an error message. This mirrors the sidebar's UI guard as a server-side enforcement.

2. **Deprecated helper migration (HCP Editor).** Mechanically replace all 22 `createTestAPIKeyWithRights(MethodName::METHOD_NAME, array(...))` calls with `createTestAPIKeyForMethod(MethodName::METHOD_NAME)`. Update the `generate-api-test.php` scaffold to use `createTestAPIKeyForMethod()` directly instead of `createTestAPIKey()` with a TODO.

3. **Index validation (HCP Editor).** Run `composer rebuild-api-method-index` to regenerate the method index in v2 schema with declared rights. Verify the build succeeds and all declared rights are registered.

## Rationale

- The server-side guard is defense-in-depth: the UI guard remains, and the server-side check prevents exploitation by users who craft direct POST requests. This is a OWASP A01 (Broken Access Control) remediation.
- The deprecated helper migration is code hygiene — the deprecated method already works correctly, but removing deprecated API usage prevents future confusion and aligns test code with the current authorization model.
- The index rebuild validates that the HCP Editor's declared rights are compatible with the framework's new v2 schema.

## Considered Alternatives

| Decision | Chosen Shape | Alternatives Considered | Trade-Off Summary |
|----------|--------------|-------------------------|-------------------|
| Guard placement | Early return in `handle_saveMethodSelection()` + conditional `addConfirmAction()` | (a) Only guard the handler; (b) only gate the action registration | Both layers are needed: gating registration prevents action discovery; the handler guard catches any remaining POST exploitation. Belt-and-suspenders. |
| Migration scope | Replace all 22 call sites in one pass | (a) Leave deprecated calls and wait for removal; (b) remove the deprecated method | (a) Accumulates technical debt; (b) removing the deprecated method is a breaking change for any other downstream consumers not yet migrated. Replacing call sites first is the safe order. |

## Pattern Alignment

- **Follows** the UI-level `$this->user->can()` guard pattern already in `APIKeyMethodsAction::_handleSidebar()` (L107) and `APIKeySettingsAction` (L89) — the server-side guard mirrors this exact pattern.
- **Follows** the `createTestAPIKeyForMethod()` convention established in the framework's `APIClientTestTrait` and used consistently in all framework-internal tests (`KeyAuthorizationTest`, `KeyRightsTest`, `APIKeyParameterTest`).
- **Follows** the `array()` syntax, `declare(strict_types=1)` conventions.

## Detailed Steps

### Phase 1 — Server-Side Edit-Right Guard (Framework)

1. **Guard the save handler.** In `APIKeyMethodsAction::handle_saveMethodSelection()`, add an early-return guard as the first statement (before the grant-all check): if `!$this->user->can(APIScreenRights::SCREEN_API_KEYS_METHODS_EDIT)`, redirect with an error message to `$this->getCurrentScreenURL()`. Use `redirectWithErrorMessage()`.

2. **Conditionally register the confirm action.** In `APIKeyMethodsAction::createDataGrid()`, move the `addConfirmAction()` block inside a `$this->user->can(APIScreenRights::SCREEN_API_KEYS_METHODS_EDIT)` check, so the action is not registered (and therefore not discoverable or triggerable) for view-only users. The existing `!areAllMethodsGranted()` guard remains as the outer condition.

### Phase 2 — Deprecated Helper Migration (HCP Editor)

3. **Replace all `createTestAPIKeyWithRights()` call sites.** In each of the 20 HCP Editor test files, replace `$this->createTestAPIKeyWithRights(MethodName::METHOD_NAME, array(RightsInterface::RIGHT_*))` with `$this->createTestAPIKeyForMethod(MethodName::METHOD_NAME)`. This is a mechanical find-and-replace within the `createMethod()` helpers. The affected files are:
   - `tests/MailEditorTests/Comtypes/API/AddComtypeCountryAPITest.php`
   - `tests/MailEditorTests/Comtypes/API/CreateComtypeAPITest.php`
   - `tests/MailEditorTests/Comtypes/API/CreateComtypeVariableAPITest.php`
   - `tests/MailEditorTests/Comtypes/API/CreateValueVariationAPITest.php`
   - `tests/MailEditorTests/Comtypes/API/DeleteComtypeAPITest.php`
   - `tests/MailEditorTests/Comtypes/API/DeleteComtypeVariableAPITest.php`
   - `tests/MailEditorTests/Comtypes/API/DeleteValueVariationAPITest.php`
   - `tests/MailEditorTests/Comtypes/API/RemoveComtypeCountryAPITest.php`
   - `tests/MailEditorTests/Comtypes/API/SetValueVariationValuesAPITest.php`
   - `tests/MailEditorTests/Comtypes/API/UpdateComtypeAPITest.php`
   - `tests/MailEditorTests/Comtypes/API/UpdateComtypeCountryAPITest.php`
   - `tests/MailEditorTests/Comtypes/API/UpdateComtypeOptionsAPITest.php`
   - `tests/MailEditorTests/Comtypes/API/UpdateComtypeSendingModesAPITest.php` (2 call sites)
   - `tests/MailEditorTests/Comtypes/API/UpdateComtypeVariableAPITest.php`
   - `tests/MailEditorTests/Comtypes/API/UpdateValueVariationAPITest.php`
   - `tests/MailEditorTests/Mail/API/APITest.php`
   - `tests/MailEditorTests/Mails/API/DeleteMailingAPITest.php`
   - `tests/MailEditorTests/Mails/API/SetMailingStateAPITest.php` (2 call sites)
   - `tests/MailEditorTests/Mails/API/UpdateMailingAPITest.php`

4. **Update the test scaffold generator.** In `tools/generate-api-test.php`, replace the `createMethod()` template body:
   - Change `$this->createTestAPIKey()` to `$this->createTestAPIKeyForMethod({$shortName}::METHOD_NAME)`.
   - Remove the TODO comment referencing `createTestAPIKeyWithRights()`.

5. **Update documentation references.** In HCP Editor docs that reference `createTestAPIKeyWithRights()`, update to reference `createTestAPIKeyForMethod()`:
   - `docs/agents/project-manifest/testing.md` — if it references the deprecated helper
   - `docs/agents/guides/guide-adding-api-methods.md` — if it references the deprecated helper

### Phase 3 — Validation

6. **Rebuild the method index.** Run `composer rebuild-api-method-index` in the HCP Editor to regenerate `storage/api/method-index.json` in the v2 schema. Verify the build succeeds without errors (all declared rights registered). If the command fails because `fault-config.php` is absent (sandboxed/CI environment), fall back to the documented manual edit of `storage/api/method-index.json` per `docs/agents/guides/guide-adding-api-methods.md` (L1384-1390), and note in the synthesis that a full rebuild is still owed once a configured environment is available.

7. **Run affected tests.** Run the modified test files to verify the migration introduces no regressions: `composer test-suite-unit -- comtypes` covers the 15 Comtypes test files; the remaining 4 files under `tests/MailEditorTests/Mail/API/` and `tests/MailEditorTests/Mails/API/` have no dedicated suite (the latter directory is not covered by any `phpunit-unit.xml`/`phpunit.xml` test suite), so run each individually with `composer test-file-unit -- <path>`.

## Dependencies

- Phase 1 is independent of Phases 2–3 (different repositories).
- Phase 2 depends on the framework's `createTestAPIKeyForMethod()` already being available (it is — shipped in the original plan).
- Step 6 depends on the framework's v2 method index schema being installed (it is — the framework vendor is already updated).

## Required Components

**Modified files (framework)**

| File | Change |
|---|---|
| `src/classes/Application/API/Admin/Screens/Mode/View/APIKeys/APIKeyMethodsAction.php` | Server-side edit-right guard in `handle_saveMethodSelection()` + conditional `addConfirmAction()` registration. |

**Modified files (HCP Editor)**

| File | Change |
|---|---|
| 20 test files under `tests/MailEditorTests/` | Replace `createTestAPIKeyWithRights()` → `createTestAPIKeyForMethod()`. |
| `tools/generate-api-test.php` | Update scaffold template to use `createTestAPIKeyForMethod()`. |
| Docs referencing deprecated helper (if any) | Update references. |

**Infrastructure**

- `storage/api/method-index.json` (HCP Editor) — rebuilt via `composer rebuild-api-method-index`.

## Assumptions

- The framework vendor in the HCP Editor workspace already contains the updated `APIClientTestTrait` with both `createTestAPIKeyForMethod()` and the deprecated `createTestAPIKeyWithRights()` wrapper. (Verified: the framework is symlinked via DEV variant.)
- All 20 HCP Editor API methods already declare `getRequiredRight()` with real right constants. (Verified during research.)
- The `redirectWithErrorMessage()` method is available on admin screen actions. (Standard framework method on `BaseAction`.)

## Constraints

- PHP 8.4+, `declare(strict_types=1)`, `array()` syntax only.
- Classmap autoloading — no new class files are added, so `composer dump-autoload` is not required.
- No breaking changes — the deprecated `createTestAPIKeyWithRights()` method is not removed, only its call sites are migrated.

## Out of Scope

- Removing the deprecated `createTestAPIKeyWithRights()` method from `APIClientTestTrait` — premature while any downstream consumer might still reference it.
- Adding unit tests for `APIMethodIndexEntry`, Methods Selection, or Rights Overview screens (deferred — see Deferred Items).
- Audit logging for grant mutations (deferred — see Deferred Items).
- `VersionedJsonCache` extraction (conditional — no second consumer).

## Acceptance Criteria

- AC-01: `handle_saveMethodSelection()` returns a 403-equivalent error redirect when the user lacks `SCREEN_API_KEYS_METHODS_EDIT`.
- AC-02: The `addConfirmAction()` for method save is not registered for users without edit rights.
- AC-03: Zero HCP Editor test files reference `createTestAPIKeyWithRights()`.
- AC-04: The `generate-api-test.php` scaffold uses `createTestAPIKeyForMethod()` directly.
- AC-05: `composer rebuild-api-method-index` succeeds in the HCP Editor with the v2 schema.
- AC-06: All migrated test files pass without regressions.

## Testing Strategy

The server-side guard is tested manually by verifying the conditional action registration and handler guard via code review (no automated admin screen tests exist in the framework — consistent with established practice). The deprecated helper migration is validated by running the affected test suites to confirm no regressions.

## Test Plan

- Run `composer test-suite-unit -- comtypes` in HCP Editor — verifies the 15 Comtypes API test files pass with `createTestAPIKeyForMethod()`. Covers AC-03, AC-06.
- Run `composer test-file-unit -- tests/MailEditorTests/Mail/API/APITest.php` in HCP Editor. Covers AC-03, AC-06.
- Run `composer test-file-unit -- tests/MailEditorTests/Mails/API/DeleteMailingAPITest.php` in HCP Editor. Covers AC-03, AC-06.
- Run `composer test-file-unit -- tests/MailEditorTests/Mails/API/SetMailingStateAPITest.php` in HCP Editor. Covers AC-03, AC-06.
- Run `composer test-file-unit -- tests/MailEditorTests/Mails/API/UpdateMailingAPITest.php` in HCP Editor. Covers AC-03, AC-06.
- Run `composer rebuild-api-method-index` in HCP Editor — verifies build-time right validation succeeds, or confirm the manual `method-index.json` fallback if the command fails in a sandboxed environment. Covers AC-05.

## Documentation Updates

- `docs/agents/project-manifest/testing.md` (HCP Editor) — Update any references from `createTestAPIKeyWithRights()` to `createTestAPIKeyForMethod()`.
- `docs/agents/guides/guide-adding-api-methods.md` (HCP Editor) — Update any references from `createTestAPIKeyWithRights()` to `createTestAPIKeyForMethod()`.
- `changelog.md` (framework) — Note the server-side edit-right guard addition under the current version.
- `dev-changelog.md` (HCP Editor) — Note the test helper migration.

## Deferred Items

| # | Deferred Item | Origin | Reason Deferred | Notes |
|---|---------------|--------|-----------------|-------|
| 1 | Audit logging for grant mutations in `handle_saveMethodSelection()` | WP-006 synthesis (Security Auditor, Low) | No audit logging exists in sibling admin screens; adding it only here would be inconsistent. Wait for a cross-cutting audit logging initiative. | Reconsider when an audit trail feature is designed for the admin UI. |
| 2 | Unit tests for `APIMethodIndexEntry` (round-trip, nullable right) | WP-001 synthesis (QA, Low) | Low risk — the DTO is simple and exercised indirectly through `KeyRightsTest` and `KeyAuthorizationTest`. | Add if the DTO gains complexity or if a bug surfaces. |
| 3 | Unit tests for Methods Selection and Rights Overview admin screens | WP-006/WP-008 synthesis (QA, Low) | Consistent with framework pattern of not unit-testing admin screens. | The diff-based save logic in `APIKeyMethodsAction` is the most critical untested path. |
| 4 | Fix `KeyParamTest::test_getValue` test-ordering state contamination | Pre-existing (QA, Low) | Not introduced by this plan; pre-existing `$_SERVER` state leak. | Address in a dedicated test-stability plan. |
| 5 | `VersionedJsonCache` base class extraction | WP-004 synthesis (Code Reviewer) | No second consumer exists yet. Premature abstraction. | Extract when a second file-backed cache with schema versioning is needed. |
| 6 | Remove deprecated `createTestAPIKeyWithRights()` method | WP-007 synthesis (Developer, Low) | Other downstream consumers may exist outside the workspace. Safe to remove only after confirming zero external call sites. | Reconsider after this migration is committed and deployed. |

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **A test relies on the `$rights` parameter behavior** | The deprecated method already ignores rights. All call sites were verified to pass rights that match the method's declared right — no test depends on the pseudo-user rights path. |
| **Server-side guard breaks a legitimate workflow** | The guard uses the same right constant as the existing UI guard — no user who could previously save will be blocked. |
| **Method index rebuild fails on an unregistered right** | All 20 methods declare rights from `ComtypeRightsInterface` and `MailRightsInterface`, which are registered at boot. The user confirmed the tenant is set up for testing. |
| **`composer rebuild-api-method-index` fails in a sandboxed/CI environment lacking `fault-config.php`** | Fall back to the documented manual edit of `storage/api/method-index.json` (`docs/agents/guides/guide-adding-api-methods.md` L1384-1390) and flag in the synthesis that a full rebuild is still owed once a configured environment is available. |

## Recommended Workflow
- **Workflow:** standalone
- **Rationale:** Small, focused rework with mechanical replacements across well-understood patterns — a single developer session suffices with self-review.
