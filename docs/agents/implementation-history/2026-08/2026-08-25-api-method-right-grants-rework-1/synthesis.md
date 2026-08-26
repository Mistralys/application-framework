
## Synthesis

### Completion Status
- Date: 2026-08-25
- Status: COMPLETE
- Completed by: Standalone Developer Agent
- Archived in Ledger: 2026-08-25

### Outcome Summary

Closed the Medium-priority security gap in the API Key Methods Selection screen by adding a server-side edit-right guard (both conditional action registration and an early-return handler check), migrated all HCP Editor test call sites from the deprecated `createTestAPIKeyWithRights()` to `createTestAPIKeyForMethod()`, updated the test scaffold generator and documentation accordingly, and rebuilt the HCP Editor method index under the v2 schema. Verification surfaced two genuine pre-existing regressions unrelated to this migration (confirmed via `git stash` isolation), which are documented below and in the insight sink rather than fixed, since they fall outside this plan's scope.

### Implementation Summary
- `APIKeyMethodsAction::createDataGrid()` now only registers the `addConfirmAction()` save action when the current user holds `SCREEN_API_KEYS_METHODS_EDIT`, mirroring the existing UI-level sidebar guard.
- `APIKeyMethodsAction::handle_saveMethodSelection()` now performs an early-return server-side check for the same right, redirecting with an error message via `redirectWithErrorMessage()` before any grant mutation logic runs.
- All HCP Editor test call sites (single-line and multi-line, including the two files with duplicate call sites) were migrated from `createTestAPIKeyWithRights(Method::METHOD_NAME, array(...))` to `createTestAPIKeyForMethod(Method::METHOD_NAME)`.
- `tools/generate-api-test.php`'s scaffold template now emits `createTestAPIKeyForMethod({ClassName}::METHOD_NAME)` directly, with the stale TODO comment removed.
- The HCP Editor method index (`storage/api/method-index.json`) was rebuilt under the v2 schema via `composer rebuild-api-method-index`, validating all declared rights across every registered method.

### Documentation Updates
- `docs/agents/project-manifest/testing.md` (HCP Editor) — Rewrote the "Rights-Gated API Method Testing" subsection to describe `createTestAPIKeyForMethod()` as the primary helper, mark `createTestAPIKeyWithRights()` as deprecated, and update Pattern A/B examples.
- `docs/agents/guides/guide-adding-api-methods.md` (HCP Editor) — Updated the `assertMethodCallIsSuccessful()` warning, the write-method test scaffold's `createMethod()` template, and the customisation checklist to reference `createTestAPIKeyForMethod()`.
- `changelog.md` (framework) — Added a bullet under the `v7.5.1` WIP entry documenting the server-side edit-right guard.
- `dev-changelog.md` (HCP Editor) — Added a new `v22.5.0 - WIP UNRELEASED` section documenting the test helper migration, scaffold update, and documentation changes.

### Verification Summary
- Tests run: `composer test-file-unit` against the migrated Comtypes API test files (single invocation), plus `Mail/API/APITest.php`, `Mails/API/DeleteMailingAPITest.php`, `Mails/API/UpdateMailingAPITest.php`, and `Mails/API/SetMailingStateAPITest.php` (isolated diagnostic run).
- Static analysis run: `composer analyze` (PHPStan) in both `application-framework` and `hcp-editor`.
- Infrastructure: `composer rebuild-api-method-index` in HCP Editor.
- Result: PASS for all files in scope of this plan. The migrated Comtypes test files and the Mail/Mails files (excluding `SetMailingStateAPITest.php`) pass cleanly. `SetMailingStateAPITest.php` has pre-existing failures confirmed (via `git stash`) to exist independently of this migration — see Code Insights. PHPStan reports zero issues in files this plan touched; the pre-existing errors it reports elsewhere (`TestImageStagePictogramNoImageContent.php`, `VariationAPITraitStub.php`) are untouched by this plan. The `composer test-suite-unit -- comtypes` suite-level run could not complete due to a pre-existing fatal error in `VariationAPITraitStub.php` (also documented below); the migrated files were instead verified individually.

### Code Insights
- [high] (debt) `hcp-editor/assets/classes/Maileditor/Mails/API/Methods/SetMailingStateAPI.php`: `handleFinalize()` has its own explicit second-tier authorization check (`$user->hasRight(MailRightsInterface::RIGHT_FINALIZE_MAILS)`) on the API key's pseudo-user, separate from the generic `getRequiredRight()`/`APIKeyRights::satisfies()` method-grant check introduced by the parent plan. Confirmed via `git stash` that several tests in `SetMailingStateAPITest.php` already fail with error `#183006` on unmodified code — this predates this session's migration and is not caused by it. Root cause: `createTestAPIKeyWithRights()`/`createTestAPIKeyForMethod()` no longer set any pseudo-user rights (intentionally dropped by the parent plan), but this method still gates its finalize sub-action on pseudo-user rights directly. Needs either a test helper that also grants pseudo-user rights, or migrating this check to the method-grant model. Recommend a dedicated follow-up plan.
- [high] (debt) `hcp-editor/tests/MailEditorTestClasses/Stubs/API/VariationAPITraitStub.php`: Does not implement `APIKeyMethodInterface::getRequiredRight()`, now a mandatory abstract method. This is a pre-existing fatal error (confirmed unmodified by this session) that crashes the entire PHPUnit process for `composer test-suite-unit -- comtypes`, since `VariationAPITraitTest.php` lives under that suite's directory. Blocked a suite-level verification run for this plan; worked around by testing the 15 migrated files individually. Needs a `getRequiredRight()` implementation added to the stub as a follow-up fix.
- [low] (improvement) `application-framework/src/classes/Application/API/Admin/Screens/Mode/View/APIKeys/APIKeyMethodsAction.php`: The new server-side guard in `handle_saveMethodSelection()` duplicates the sidebar's identical right check in `_handleSidebar()`. Both are needed (UI vs. server-side defense-in-depth), but if a third call site appears, consider a small private helper (e.g. `canEditMethods()`) to keep the right constant reference in one place.
- [low] (convention) `hcp-editor/tests/MailEditorTests/Mails/API/SetMailingStateAPITest.php`: One migrated call site had a comment noting a rights-restriction test scenario (`RIGHT_FINALIZE_MAILS intentionally omitted`) whose premise was already stale before this migration, since the deprecated helper already ignored the `$rights` array. No test behavior changed as a result of the mechanical replacement.
- [low] (debt) `hcp-editor` (repo root): The working tree had pre-existing uncommitted modifications unrelated to this session (`.context/framework/**`, `composer.json`, `composer.lock`, `api/openapi.json`, `docs/agents/project-manifest/module-glossary.md`, `docs/agents/project-manifest/modules-overview.md`, `personas/docs/persona-design-guide.md`, `storage/admin/sitemap.php`), discovered while isolating this session's own changes via `git stash`. Not touched or caused by this plan — flagging so the user can review and commit or discard them separately.

### Additional Comments
- The plan's Risk Mitigation table assumed "no test depends on the pseudo-user rights path" for the deprecated helper migration. This held true for every migrated call site except `SetMailingStateAPITest.php`'s finalize-right assertions, which depend on a method-specific pseudo-user right check unrelated to the generic method-grant authorization model. This was pre-existing breakage from the parent plan, not introduced here, and is now explicitly documented for follow-up.
- AC-05 (method index rebuild) succeeded cleanly; AC-06 (no regressions) holds for every file this plan's Test Plan lists, with the one documented pre-existing exception.
