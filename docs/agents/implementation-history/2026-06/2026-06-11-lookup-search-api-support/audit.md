# Plan Audit Report

## Plan Under Review
- **Plan:** `docs/agents/plans/2026-06-11-lookup-search-api-support/plan.md`
- **Date:** 2026-06-12
- **Auditor:** Plan Auditor Agent
- **Companion report:** `design-review.md` (Plan Architect Reviewer, advisory) — produced in parallel; not consulted here.

## Verdict: PASS WITH FINDINGS

### Summary
The plan is well-grounded and well-structured. All file paths, method names, visibility claims, and class hierarchy references are verified against the codebase. The approach is minimal and additive. Two Major findings need attention before implementation: (1) a mismatch between where LIMIT is applied (per-SQL-query) and the acceptance criteria's global guarantee, and (2) the test plan lacks a fixture strategy for testing an abstract class whose search path depends on the database.

### Finding Counts
- **Critical:** 0
- **Major:** 2
- **Minor:** 1

---

## Findings

### Critical

_None._

### Major

| # | Category | Finding | Plan Location | Codebase Evidence `{file_path, line_range, claim}` | Recommendation |
|---|----------|---------|---------------|----------------------------------------------------|----------------|
| 1 | Consistency | **LIMIT semantics mismatch.** Step 2 says to apply LIMIT inside `findMatchesBySearch()` at the SQL level. The acceptance criteria state: "When `setLimit()` is called with a positive integer, `findMatchingIDs()` returns at most that many IDs." These are contradictory for multi-term searches. `findMatchingIDs()` (Step 3) iterates `$terms` and calls `findMatchesBySearch()` once per term. Each SQL query would get its own LIMIT, but after `array_unique()` deduplication, the total ID count can exceed the limit (up to `N × limit` minus overlaps). The numeric-term path (`idExists()`) bypasses SQL entirely and adds IDs uncapped. | Step 2 (setLimit), Step 3 (findMatchingIDs), Acceptance Criteria item 6 | `{src/classes/Application/LookupItems/BaseLookupItem.php, L109–L134, "findMatches iterates terms and calls findMatchesBySearch per term; IDs are merged and deduped after all queries complete"}` | Add a post-dedup guard in `findMatchingIDs()`: after `array_unique()`, apply `array_slice($ids, 0, $this->limit)` when `$this->limit > 0`. This enforces the global cap regardless of how many terms or query paths contribute IDs. Keep the SQL-level LIMIT as a performance optimization (avoids over-fetching per query), but the method-level contract must be enforced in PHP. Update Step 2 and Step 3 to reflect both levels. |
| 2 | Test Coverage | **Test plan lacks fixture strategy for abstract class with DB dependency.** `BaseLookupItem` is abstract with 9 abstract methods. Its `findMatchesBySearch()` calls `DBHelper::fetchAllKeyInt()` which requires a live database connection. The plan lists 4 test cases in `tests/AppFrameworkTests/LookupItems/BaseLookupItemTest.php` but does not specify how the test fixture is constructed: (a) a concrete test double with stubbed DB responses, or (b) integration tests using the framework test application's database with real collection data. The existing lookup item tests in the HCP Editor are integration tests. An implementer would stall choosing the approach. | Test Plan section | `{src/classes/Application/LookupItems/BaseLookupItem.php, L101–L107, "9 abstract methods including getQuerySQL, getSearchColumns, idExists, getByID — all require concrete implementation"}` | Specify the fixture strategy. Recommended: create a concrete `TestLookupItem` in `tests/AppFrameworkTestClasses/LookupItems/` that extends `BaseDBCollectionLookupItem` (or `BaseLookupItem` directly) and backs onto a test-database table. The framework test application already has a seeded DB. Alternatively, if pure unit tests are preferred, document that `findMatchesBySearch()` must be mockable (which currently requires the test double to override the `private` method — not possible without refactoring). |

### Minor

| # | Category | Finding | Plan Location | Codebase Evidence `{file_path, line_range, claim}` | Recommendation |
|---|----------|---------|---------------|----------------------------------------------------|----------------|
| 1 | Consistency | **`setLimit()` signature diverges from the established pattern.** The existing `FilterCriteria::setLimit()` signature is `setLimit(int $limit = 0, int $offset = 0): self`. The plan proposes `setLimit(int $limit): self` on `BaseLookupItem` — no default value, no offset parameter. The plan explicitly puts offset out of scope, which is reasonable, but the divergence is not acknowledged. | Step 2 (setLimit), Considered Alternatives | `{src/classes/Application/FilterCriteria/FilterCriteria.php, L124, "public function setLimit(int $limit = 0, int $offset = 0) : self"}` | Add a sentence to the Rationale or Constraints section noting that the signature intentionally omits offset (out of scope) and the default value (a limit of 0 means "no limit" — the semantic is clear from context). This prevents a future reviewer from filing a consistency bug. |

---

## Overlooked Codebase Patterns

_No existing utilities or patterns were found that the plan duplicates or ignores. The plan correctly identifies `BaseLookupItem` as the sole owner of the lookup search logic and proposes extending it rather than duplicating it._

---

## Completeness Assessment

| Plan Section | Status | Notes |
|--------------|--------|-------|
| Summary | OK | Clear goal, well-scoped. |
| Architectural Context | OK | Hierarchy, file paths, method visibility table, and data flow all verified against the codebase. |
| Approach / Architecture | OK | Minimal additive change, clearly described. |
| Rationale | OK | Three alternatives considered with trade-off reasoning. |
| Considered Alternatives | OK | Decision table is thorough. |
| Pattern Alignment | OK | Claims verified — naming follows existing conventions. |
| Detailed Steps | OK | Steps are actionable. Step 2 has the LIMIT ambiguity noted in Finding #1. |
| Dependencies | OK | Self-contained within the framework. |
| Required Components | OK | Single file modification. |
| Assumptions | OK | All three assumptions verified against the codebase. |
| Constraints | OK | Correctly preserves internal method visibility and array syntax rule. |
| Out of Scope | OK | Count query, subclass changes, tenant scoping, and offset are all reasonable exclusions. |
| Acceptance Criteria | Gap | Criterion 6 (setLimit caps IDs) is not achievable with the SQL-only approach described in Step 2 — see Finding #1. |
| Testing Strategy | OK | Regression testing plus new coverage is appropriate. |
| Test Plan | Gap | Four test cases listed, but no fixture strategy — see Finding #2. |
| Documentation Updates | OK | `composer build` for CTX regeneration is sufficient. No new conventions warrant a `constraints.md` update. |
| Risks & Mitigations | OK | Three risks identified with concrete mitigations. The LIMIT semantic mismatch could be added as a fourth risk. |
