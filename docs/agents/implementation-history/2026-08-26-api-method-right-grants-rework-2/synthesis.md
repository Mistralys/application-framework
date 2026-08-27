# Synthesis Report — API Method Right Grants Rework (Phase 2)

**Project:** `2026-08-26-api-method-right-grants-rework-2`  
**Date:** 2026-08-26  
**Status:** COMPLETE

---

## Executive Summary

Replaced the multiplexed `SetMailingStateAPI` with three dedicated API methods — `FinalizeMailingAPI`, `RevertMailingToDraftAPI`, and `DeactivateMailingAPI` — each declaring its own authorization right to fit the one-right-per-method model. The original method was deleted after all cross-references were migrated. Two additional improvements were delivered: a missing `getRequiredRight()` method was added to `VariationAPITraitStub` (unblocking the comtypes test suite), and a `canEditMethods()` helper was extracted in `APIKeyMethodsAction` to eliminate DRY violations flagged in Phase 1. All generated artifacts (method index, OpenAPI spec, nexus-plugins build) were rebuilt and verified clean of stale references.

---

## Metrics

| Metric | Value |
|---|---|
| Work Packages | 9/9 COMPLETE |
| Pipeline Stages Passed | 34/34 (100%) |
| Tests Executed (across all QA pipelines) | 608+ |
| Tests Failed | 0 |
| Security Audits | 3 (WP-003, WP-004, WP-005) |
| Security Issues (Critical/High/Medium) | 0 |
| Security Issues (Low) | 1 (defence-in-depth suggestion) |
| PHPStan New Errors | 0 |
| Revisions / Rework | 0 |
| Files Created | 6 (3 API methods + 3 test files) |
| Files Modified | 12 |
| Files Deleted | 2 (SetMailingStateAPI + test) |
| Generated Artifacts Rebuilt | 2 (method-index.json, openapi.json) |

---

## Strategic Recommendations

1. **Authorization Test Pattern (Gold Nugget) — ✅ Adopted 2026-08-26:** `FinalizeMailingAPITest::test_keyWithoutFinalizeMethodGrantIsRejected` proves one-right-per-method isolation end-to-end. This pattern — testing that a key granted one right is rejected by a method requiring a different right — has been documented as a standing convention in both the HCP Editor (`docs/agents/project-manifest/testing.md`, "Rights-Gated API Method Testing") and Application Framework (`docs/agents/project-manifest/testing.md`, "API Key Method Tests") project manifests, for adoption by any future API method using a distinct authorization right.

2. **Consistent API Method Family Pattern — ✅ Adopted 2026-08-26:** The three sibling state-transition methods share identical structural patterns (trait composition, transaction handling, `buildResponse`, `compileRequestVars`, documentation sections). Guard strategies appropriately diverge based on available framework helpers. This family can serve as a template for future state-transition API methods — documented as a standing convention in the HCP Editor (`docs/agents/project-manifest/constraints.md`, "State-Transition API Method Family Pattern") and generalized as a principle in the Application Framework (`docs/agents/project-manifest/constraints.md`, "Structural Consistency Across Sibling Methods") project manifests.

3. **Dual Guard Pattern for Missing Convenience Methods:** `RevertMailingToDraftAPI` uses a dual guard (`!isDeleted() + hasDependency()`) because no `canBeDraft()` convenience method exists on `StandardStateSetupTrait`. If more API methods need draft-transition guards, consider adding `canBeDraft()` to the trait.

---

## Code Insights

All pipeline observations across all agents reported clean, consistent code with no code smells, refactoring needs, or convention violations. No actionable observations were recorded by any agent during any pipeline stage.

---

## Deferred & Follow-Up Items

| # | Source | Agent | Description | Type | Priority |
|---|---|---|---|---|---|
| 1 | WP-005 | Security Auditor | `MailForgeException::getMessage()` included verbatim in error 185907 API response. Consider generalizing to "Template generation failed" and logging the full exception server-side. Mitigated by API key authentication. | Deferred | Low |
| 2 | WP-002 | Documentation | CTX-generated docs (`.context/`) are stale after framework changes. Deferred to pre-release per AGENTS.md policy — run `composer build` before tagging. | Deferred | Low |
| 3 | WP-009 | QA | `nexus-plugins/tools/api-methods.json` has an empty methods array after build. Pre-existing config issue unrelated to this plan — the glossary plugin indexes modules/terms but api-methods.json source may not be populated. | Out-of-scope | Low |
| 4 | WP-009 | QA | 4 pre-existing PHPStan errors in `TestImageStagePictogramNoImageContent.php` (MailForge test generator, `classConstant.notFound` for `ImageStageTestSerializer` constants). Unrelated to this plan. | Out-of-scope | Low |

---

## Next Steps

1. **Pre-release builds:** Run `composer build` in both HCP Editor and Application Framework to regenerate CTX documentation before tagging releases.
2. **Changelog finalization:** The `dev-changelog.md` (HCP Editor) and `changelog.md` (Application Framework) entries are in place. Run the changelog skill before release to ensure formatting consistency.
3. **Consider `canBeDraft()` helper:** If future API methods need draft-transition guards, adding `canBeDraft()` to `StandardStateSetupTrait` would eliminate the dual-guard pattern.
4. **MailForgeException message hardening:** Low-priority defence-in-depth improvement for `FinalizeMailingAPI` error 185907 — generalize the exception message in the API response.
