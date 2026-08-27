# Plan

## Plan Audit Cycles
- Audits: 2 — Plan Auditor v1.5.0
- Architectural Reviews: none — Plan Architect Reviewer v1.6.0

## Prior Project Context
The application framework has delivered 16 projects to date, including recent work on wizard preselection API, clearable string parameters, and test database seeding. The framework provides the lookup system (`BaseLookupItem` hierarchy) and filter criteria system (`BaseFilterCriteria`) that downstream applications — notably the HCP Editor — rely on. The HCP Editor's strategic vision is to make all major features available through API methods. This plan directly supports that vision by making the framework's fast lookup search system accessible from API method classes, enabling a transparent performance optimization for list endpoints.

## Summary
Extend the framework's lookup system (`BaseLookupItem` and its subclasses) with public API surface for programmatic search from API method classes. Currently, the lookup system is designed exclusively for UI widget use — key methods are `protected`, there is no limit/offset support, and results are UI-oriented (rendered HTML labels + admin URLs). This plan adds the minimum infrastructure needed for API methods to leverage the lookup system's fast, single-table/single-JOIN queries as a performance alternative to the heavier filter criteria path.

## Architectural Context

### Lookup System Hierarchy
```
BaseLookupItem (abstract)
├── BaseDBCollectionLookupItem (abstract) — single-table SELECT
│   └── ComtypesLookupItem, SnippetsLookupItem, etc.
└── BaseRevisionableLookupItem (abstract) — revision-joined SELECT
    └── MailsLookupItem, NotificationsLookupItem, etc.
```

**Key files:**
- `src/classes/Application/LookupItems/BaseLookupItem.php` — base class, `splitSearchTerm()`, `findMatches()`, `addWhere()`
- `src/classes/Application/LookupItems/BaseDBCollectionLookupItem.php` — collection-backed single-table lookup
- `src/classes/Application/LookupItems/BaseRevisionableLookupItem.php` — revision-joined lookup
- `src/classes/Application/AjaxMethods/LookupItems.php` — AJAX handler (current only consumer)
- `src/classes/DBHelper/BaseFilterCriteria.php` — filter criteria with `setSearch()` and `setLimit()`

### Current Visibility and API Gaps

| Method | Class | Current Visibility | Gap |
|---|---|---|---|
| `addWhere(string)` | `BaseLookupItem` | `protected` | Cannot add constraints (tenant filter) from API methods |
| `findMatches($terms)` | `BaseLookupItem` | `public` | Available, but returns `void` — results via `getResults()` only |
| `splitSearchTerm()` | `BaseLookupItem` | `public static` | Already reusable — no change needed |
| `getSearchColumns()` | `BaseLookupItem` | `protected abstract` | Internal — no change needed |
| `getQuerySQL()` | `BaseLookupItem` | `protected abstract` | Internal — no change needed |
| `getByID(int)` | `BaseLookupItem` | `protected abstract` | Returns full record objects — but not accessible externally |
| Limit/Offset | N/A | N/A | Does not exist in the lookup system |

### How Lookup Search Works (Current)
1. `findMatches($terms)` iterates terms
2. For numeric terms: `idExists()` → direct ID match
3. For string terms: `splitSearchTerm()` → LIKE clauses → `getQuerySQL()` with `{WHERE}` → `DBHelper::fetchAllKeyInt()` → ID list
4. Deduplicate IDs → `getByID()` per ID → `renderLabel()` + `getURL()` → `addResult()`
5. Results in `$this->results` (accessible via `getResults()`)

### What API Methods Need
API methods need to use the lookup's fast SQL to find matching record IDs, then build their own structured JSON responses from the full record objects. They do NOT need `renderLabel()` or `getURL()` — those are UI concerns. They DO need:
- The ability to add WHERE constraints (tenant scoping)
- Limit/offset for pagination
- Access to the matched record IDs (to hydrate via their own response builders)

## Approach / Architecture

Add a `findMatchingIDs()` method to `BaseLookupItem` that performs the lookup search and returns matched record IDs without the UI rendering step. Make `addWhere()` public so API methods can add tenant/scope constraints. Add `setLimit()` for result cap support.

This is a **minimal, additive change** — no existing behavior is altered. The existing `findMatches()` method continues to work exactly as before for UI consumers. The new `findMatchingIDs()` method provides a parallel entry point for programmatic consumers.

## Rationale

The alternative approaches were:
1. **Extract a trait** — Higher complexity, introduces a new abstraction. The lookup items already have the search logic; we just need to expose a subset of it.
2. **Create a separate helper class** — Would duplicate the SQL generation logic that's already in the lookup items.
3. **Make API methods subclass lookup items** — Violates single-responsibility; API methods are not lookup items.

The chosen approach (add `findMatchingIDs()` + make `addWhere()` public + add `setLimit()`) is the smallest change that enables the downstream use case.

## Considered Alternatives

| Decision | Chosen Shape | Alternatives Considered | Trade-Off Summary |
|----------|--------------|-------------------------|-------------------|
| How to expose lookup search to API methods | New `findMatchingIDs()` method on `BaseLookupItem` | Extract `LookupSearchTrait`, Create standalone `LookupSearchHelper` class, Make API methods extend lookup items | Adding a method is the smallest change; a trait would be over-engineering for what is essentially one method. The lookup item already has the search logic — extracting it would duplicate code. |
| How to allow scoping (tenant filter) | Make `addWhere()` public | Add a dedicated `addTenantFilter()` method, Add constructor injection for constraints | `addWhere()` is the existing mechanism; making it public is a one-word change. A dedicated method would couple the base class to tenant semantics it shouldn't know about. |
| How to limit results | Add `setLimit(int)` method + apply in SQL | Post-filter with `array_slice()`, Rely on caller to truncate | SQL LIMIT is more efficient — avoids fetching and discarding rows. |

## Pattern Alignment
- Follows the existing `BaseLookupItem` method naming convention: `findMatches()`, `findMatchesBySearch()` → `findMatchingIDs()`.
- Follows the existing visibility pattern of exposing public entry points (`findMatches()`, `getResults()`, `splitSearchTerm()`) while keeping internals protected.
- `setLimit()` follows the fluent setter pattern established by `addWhere()` (returns `$this`).
- No departure from existing codebase patterns.

## Detailed Steps

### Step 1: Make `addWhere()` public on `BaseLookupItem`

Change the visibility of `addWhere(string $where): self` from `protected` to `public` in `src/classes/Application/LookupItems/BaseLookupItem.php`.

This is a backward-compatible change — existing `protected` callers (subclasses) can still call it, and now external callers (API methods) can too.

### Step 2: Add `setLimit()` method to `BaseLookupItem`

Add a `private int $limit = 0` property and a `public function setLimit(int $limit): self` fluent setter.

When `$limit > 0`, the SQL query in `findMatchesBySearch()` should append `LIMIT {$limit}` to the query. This requires modifying `findMatchesBySearch()` to apply the limit.

Implementation detail: The limit is applied to the SQL query string after the `{WHERE}` substitution and before execution. This ensures the database does the truncation, not PHP.

> **Note:** The SQL-level LIMIT is a **per-query performance optimization** — it prevents over-fetching rows from each individual `findMatchesBySearch()` call. It does NOT enforce the global result cap on its own, because `findMatchingIDs()` may call `findMatchesBySearch()` once per search term, and numeric terms bypass SQL entirely via `idExists()`. After deduplication, the total ID count can exceed the limit (up to `N × limit` minus overlaps). The global cap is enforced in `findMatchingIDs()` (Step 3).

> **Note:** The `setLimit()` signature intentionally omits an `$offset` parameter and a default value. Offset is out of scope for this plan. The existing `FilterCriteria::setLimit(int $limit = 0, int $offset = 0): self` follows a different pattern because it serves paginated list endpoints; the lookup system's limit is a simple result cap. A limit of `0` means "no limit" — the semantic is clear from context.

### Step 3: Add `findMatchingIDs()` method to `BaseLookupItem`

Add a new `public function findMatchingIDs(array $terms): int[]` method that:
1. Iterates `$terms` (same logic as `findMatches()`)
2. For numeric terms: checks `idExists()`, collects ID
3. For string terms: calls the existing `findMatchesBySearch()` (private), collects IDs
4. Deduplicates via `array_unique()`
5. **Enforce global limit:** If `$this->limit > 0`, apply `array_slice($ids, 0, $this->limit)` after deduplication. This ensures the method-level contract ("at most N IDs") is honored regardless of how many terms or query paths contributed IDs.
6. Returns the ID array — does NOT call `renderLabel()`, `getURL()`, or `addResult()`

This is a refactoring of the existing `findMatches()` logic. To avoid duplication, refactor `findMatches()` to call `findMatchingIDs()` internally, then iterate the IDs to render labels and build results.

> **Audit fix (Major #1):** The SQL-level LIMIT (Step 2) is a per-query optimization. The global result cap is enforced here via `array_slice()` after deduplication. Both levels are needed: SQL LIMIT avoids over-fetching, PHP `array_slice()` guarantees the contract.

### Step 4: Verify backward compatibility

Ensure `findMatches()` produces identical results after the refactoring. The existing AJAX handler and all UI consumers must continue to work unchanged.

## Dependencies
- None. This plan is self-contained within the application framework.

## Required Components
- `src/classes/Application/LookupItems/BaseLookupItem.php` — modified (Steps 1–4)
- `tests/AppFrameworkTestClasses/LookupItems/TestLookupItem.php` — new (test fixture, concrete lookup item backed by test DB)
- `tests/AppFrameworkTests/LookupItems/BaseLookupItemTest.php` — new (test cases)

## Assumptions
- The `findMatchesBySearch()` private method can be safely called from `findMatchingIDs()` since both are on the same class.
- No existing code outside `BaseLookupItem` subclasses calls `addWhere()` — making it public is safe.
- The `$limit` property does not conflict with any existing property on `BaseLookupItem` or its subclasses.

## Constraints
- `getQuerySQL()` and `getSearchColumns()` remain `protected abstract` — they are internal to the lookup system and should not be exposed.
- `findMatchesBySearch()` remains `private` — the refactoring uses it internally.
- No new dependencies or classes are introduced.
- Array syntax: use `array()` — this is a hard project rule per constraints.md.
- `setLimit()` intentionally diverges from `FilterCriteria::setLimit(int $limit = 0, int $offset = 0): self` — no offset parameter (out of scope) and no default value. This is a deliberate simplification for the lookup system's result-cap use case, not an oversight.

## Out of Scope
- Adding a count query method (total count for pagination) — can be added later if needed.
- Changing `BaseDBCollectionLookupItem` or `BaseRevisionableLookupItem` — no changes needed there; they inherit the new functionality.
- Adding tenant-aware scoping methods — that's a concern for the HCP Editor's concrete lookup items, not the base class.
- Offset support — for the initial use case (capped search results), a simple LIMIT is sufficient. OFFSET can be added later if paginated lookup is needed.

## Acceptance Criteria
- `addWhere()` is `public` on `BaseLookupItem`.
- `setLimit(int): self` exists on `BaseLookupItem` and is `public`.
- `findMatchingIDs(array $terms): int[]` exists on `BaseLookupItem` and is `public`.
- `findMatchingIDs()` returns the same IDs that `findMatches()` would populate in results (minus the rendering step).
- `findMatches()` continues to work identically for all existing consumers (UI lookup widgets, AJAX handler).
- When `setLimit()` is called with a positive integer, `findMatchingIDs()` returns at most that many IDs.
- When `addWhere()` is called before `findMatchingIDs()`, the WHERE constraint is applied to the search query.
- No existing tests break.

## Testing Strategy
Run the framework's existing test suite to ensure no regressions. Add new test coverage for the three new/modified public methods.

## Test Plan

### Test Fixture Strategy

> **Audit fix (Major #2):** `BaseLookupItem` is abstract with 9 abstract methods, and `findMatchesBySearch()` calls `DBHelper::fetchAllKeyInt()` which requires a live database. The tests use an **integration test approach** with a concrete test double backed by the framework test application's seeded database.

Create a concrete `TestLookupItem` in `tests/AppFrameworkTestClasses/LookupItems/TestLookupItem.php` that extends `BaseDBCollectionLookupItem` (or `BaseLookupItem` directly) and implements all abstract methods against a test-database table available in the seeded test DB. The framework test application already provides a seeded database via `composer seed-tests`.

The test class `tests/AppFrameworkTests/LookupItems/BaseLookupItemTest.php` extends the framework's base test case and uses `TestLookupItem` as the fixture for all test cases.

### Test Cases

- `tests/AppFrameworkTests/LookupItems/BaseLookupItemTest.php` (new) — Tests for `findMatchingIDs()` returning correct IDs for numeric and string terms. — AC: findMatchingIDs returns same IDs as findMatches
- `tests/AppFrameworkTests/LookupItems/BaseLookupItemTest.php` (new) — Test that `setLimit()` caps the number of returned IDs, including multi-term searches where the global cap must be enforced after deduplication. — AC: setLimit caps results
- `tests/AppFrameworkTests/LookupItems/BaseLookupItemTest.php` (new) — Test that `addWhere()` constrains search results when called before `findMatchingIDs()`. — AC: addWhere constrains search
- `tests/AppFrameworkTests/LookupItems/BaseLookupItemTest.php` (new) — Test that `findMatches()` still produces identical results after the internal refactoring. — AC: findMatches backward compatibility

## Documentation Updates
- `docs/agents/project-manifest/constraints.md` — No change needed (no new conventions).
- `.context/` — Run `composer build` to regenerate CTX documentation reflecting the new public methods.

## Risks & Mitigations
| Risk | Mitigation |
|------|------------|
| **Refactoring `findMatches()` to use `findMatchingIDs()` introduces a subtle behavioral difference** | Test that `findMatches()` output is identical before and after the refactoring. The logic extraction is mechanical — no new branching or conditions. |
| **`setLimit()` applied to SQL may interact unexpectedly with the `{WHERE}` substitution** | The LIMIT clause is appended after the WHERE substitution, not within it. Test with and without WHERE constraints. |
| **Making `addWhere()` public could be misused** | The method signature and documentation are clear about its purpose. The risk is low — it's already used by subclasses; external callers follow the same contract. |
| **LIMIT semantic mismatch between SQL and method contract** | SQL LIMIT is per-query (performance optimization); the global cap is enforced via `array_slice()` in `findMatchingIDs()` after deduplication. Both levels are tested. |
