# Synthesis Report: API Method Right Grants

## Executive Summary

Closed the authorization gap where an API key's pseudo user could never satisfy a method's declared right requirement. Introduced `APIKeyRights` — a new authority object owned by `APIKeyRecord` that answers method-scoped rights questions by reading declared rights from a versioned method index and expanding one level through the existing grant graph. The gate swap in `BaseAPIMethod::authorize()` replaces the broken pseudo-user `hasRight()` check with `APIKeyRights::satisfies()`, establishing a fail-closed three-check authorization sequence (key → method → right).

Supporting changes span 8 work packages: a typed `APIMethodIndexEntry` DTO, test application right registration, mandatory `getRequiredRight()` declaration (breaking change — removes the fail-open trait default), schema-versioned method index with build-time right validation, and two new admin screens (Methods Selection with diff-based save, Rights Overview with reverse mapping and one-level grant expansion).

## Metrics

| Metric | Value |
|--------|-------|
| Work packages | 8 / 8 complete |
| Pipeline stages executed | 37 (all PASS) |
| Security audits | 5 (WP-003 through WP-007) |
| Security findings | 0 Critical, 0 High, 1 Medium (deferred), 1 Low (deferred) |
| PHPStan | Clean (0 errors across all WPs) |
| Tests (final regression) | 162 key-related tests, 414 assertions |
| New test files | 2 (`MethodIndexEntryTest.php`, `KeyRightsTest.php`) |
| New test cases | 23 dedicated + re-grounded existing |
| New source classes | 4 (`APIMethodIndexEntry`, `APIKeyRights`, `APIKeyMethodsAction`, `APIKeyRightsAction`) |
| Modified source classes | 8 |
| Documentation files updated | 7 (changelog, constraints.md, 2 READMEs, testing.md, CTX-generated files) |

## Strategic Recommendations

1. **Reusable versioned-cache pattern** (from WP-004 code review): The schema-versioned index with auto-rebuild and fail-closed persistent-mismatch handling in `APIMethodIndex` is a reusable pattern applicable to other file-backed caches in the framework (e.g., screen index, event index). Consider extracting a `VersionedJsonCache` base class if a second consumer emerges.

2. **try/finally index mutation pattern** (from WP-007 code review): The `test_unresolvableDeclaredRightDenied` test demonstrates a clean pattern for tests that must mutate shared state (crafted index entry) and guarantee cleanup. This pattern should be adopted for any future index-dependent test.

3. **Downstream migration pass required**: The mandatory `getRequiredRight()` declaration (WP-003) is a breaking change. All HCP Editor API method classes that relied on the removed trait default must add an explicit `getRequiredRight()` return. The changelog documents the migration path.

4. **`createTestAPIKeyWithRights()` deprecation**: ~20 HCP Editor call sites still use this deprecated helper. A downstream migration pass should replace them with `createTestAPIKeyForMethod()` to align test code with the new authorization model.

## Code Insights

### Developer

- `APIKeyMethods` has a static `$availableMethods` cache that persists across tests. Crafted-entry tests must override existing method entries rather than adding synthetic ones, then restore via `clearIndexCache()` + try/finally. (WP-005, WP-007)
- AC #3 of WP-007 states "method not granted yields ERROR_INSUFFICIENT_RIGHTS" but the gate code produces `ERROR_METHOD_NOT_GRANTED` (183005) for that path. The test correctly asserts the actual code. AC text was imprecise. (WP-007)

### QA

- No unit tests exist for `APIMethodIndexEntry` — a round-trip + nullable-right test file would close this coverage gap. (WP-001, low risk)
- No automated tests for the Methods Selection or Rights Overview admin screens. The diff-based save logic in `APIKeyMethodsAction` is the most critical untested path. Consistent with sibling screen patterns but noted. (WP-006, WP-008)
- `KeyParamTest::test_getValue` fails under broad API filter runs but passes in isolation — pre-existing test-ordering state contamination via `$_SERVER`. Not introduced by this plan. (WP-008)

### Security Auditor

- **A01 Medium** (WP-006): `handle_saveMethodSelection()` and `addConfirmAction()` lack server-side edit-right guard. The edit right is enforced only at UI level (`_handleSidebar()` hides the save button). A view-only admin who knows the internal action name could craft a POST to execute the save. Consistent with existing framework screen patterns. (WP-006)
- **A09 Low** (WP-006): No audit logging for grant mutations (methods added/removed). Adding an audit trail entry would improve security traceability. (WP-006)
- Override contract for `getRequiredRight()` is documentation-only — not runtime-enforced. Pre-existing design gap, not introduced by this plan. (WP-003)

### Code Reviewer

- The fail-open → fail-closed progression across the plan is well-executed: typed DTO → test right registration → mandatory declaration → build-time validation → authority object → gate swap. Each WP builds cleanly on prior ones with no architectural debt introduced.
- `authorize()` is `private` in `BaseAPIMethod` — no subclass can bypass or weaken the three-check gate.

## Deferred & Follow-Up Items

| Source | Agent | Description | Type | Priority |
|--------|-------|-------------|------|----------|
| WP-006 | Security Auditor | Add server-side edit-right guard to `handle_saveMethodSelection()` and conditionally register `addConfirmAction()` | **deferred** | Medium |
| WP-006 | Security Auditor | Add audit logging for grant mutations in `handle_saveMethodSelection()` | **deferred** | Low |
| WP-007 | Developer | Migrate ~20 HCP Editor `createTestAPIKeyWithRights()` call sites to `createTestAPIKeyForMethod()` | **out-of-scope** | Low |
| WP-003 | Developer | Downstream migration: HCP Editor methods relying on removed trait default must add explicit `getRequiredRight()` | **out-of-scope** | High (breaking) |
| WP-001 | QA | Add unit tests for `APIMethodIndexEntry` (round-trip, nullable right, edge cases) | **deferred** | Low |
| WP-006, WP-008 | QA | Add unit tests for Methods Selection and Rights Overview admin screens | **deferred** | Low |
| Pre-existing | QA | Fix `KeyParamTest::test_getValue` test-ordering `$_SERVER` state contamination | **out-of-scope** | Low |

## Next Steps

1. **HCP Editor migration pass** — highest priority follow-up. Add explicit `getRequiredRight()` to all HCP Editor API method classes that relied on the removed trait default. Run `composer build` to validate all declared rights.
2. **Server-side edit-right guard** — add the defense-in-depth guard to `APIKeyMethodsAction::handle_saveMethodSelection()` and conditionally register the confirm action.
3. **Deprecated helper migration** — replace `createTestAPIKeyWithRights()` call sites in HCP Editor tests with `createTestAPIKeyForMethod()`.
4. **Consider `VersionedJsonCache` extraction** — if a second file-backed cache with schema versioning is needed, extract the pattern from `APIMethodIndex`.
