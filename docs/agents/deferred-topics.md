# Deferred Topics

Topics where the current implementation is a known compromise and a cleaner solution
should be pursued when the right approach has been identified. Each entry describes
the limitation, the current workaround, and the conditions under which the deferred
work should be revisited.

---

## DT-001 — Replace `clearRecordCache()` with a proper test-isolation event

**Filed:** 2026-07-16  
**Status:** Deferred — awaiting clean solution  
**Area:** `DBHelper_BaseCollection`, `Application_Countries`, test infrastructure

### Background

`DBHelper_BaseCollection::clearRecordCache()` was introduced as a test-isolation helper
for the countries singleton. The intended approach (using `resetCollection()`) disposes
all cached record objects, which breaks any external code that legitimately holds
references to those objects across `setUp()` boundaries — for example, tenant country
collections (`TenantCountriesCollection`), notification locale managers
(`NotificationLocalesManager`), and API method instances.

`clearRecordCache()` works around this by only flushing the `getAll()` result cache
(`$allRecords`) and the ISO-to-ID lookup (`$idLookup`) without disposing records. This
is sufficient to prevent stale IDs from rolled-back test transactions from leaking into
subsequent tests. However, it has two known gaps:

1. **The per-ID record cache (`$records`) is not cleared.** A record created inside a
   rolled-back transaction remains reachable via `getByID()` for the remainder of the
   process lifetime. In practice this does not cause failures (no subsequent test has
   the stale ID), but it is a correctness gap.

2. **Co-resets must be managed manually.** Each singleton that lazily caches records
   from the countries collection (currently `Locales` via `clearLocaleCache()`) must be
   individually identified and co-reset in `setUp()`. This is fragile: adding a new
   singleton that caches country records elsewhere in the codebase will silently
   re-introduce the stale-reference problem unless the developer also adds a co-reset.

### Desired Long-Term Solution

The clean solution is an `onAfterClearCache` (or `onAfterResetCollection`) event on
`BaseCollection` that interested parties can subscribe to. The countries collection would
fire this event in both `clearRecordCache()` and `resetCollection()`. `Locales` and any
other singleton that caches country records would subscribe and self-reset automatically.

This would eliminate the manual co-reset calls in `MailTestCase::setUp()` and make the
cache-invalidation contract part of the collection's API rather than test infrastructure
knowledge.

### What Must Happen Before This Can Be Resolved

- Audit all singletons that hold references to `Application_Countries_Country` records.
  The countries collection is not the only one affected; other collections may have the
  same pattern.
- Design the event API so that `resetCollection()` and `clearRecordCache()` can fire
  distinct events (full dispose vs. cache-only flush), allowing subscribers to react
  differently if needed.
- Migrate all existing co-resets in `MailTestCase::setUp()` to event subscriptions.
- Verify the full test suite passes (countries filter, mail suite, notifications suite)
  after the migration.

### Current State

- `clearRecordCache()` is documented with its limitation in its docblock in
  `src/classes/DBHelper/BaseCollection.php`.
- `Locales::clearLocaleCache()` is the only registered co-reset; it is called explicitly
  in `MailTestCase::setUp()` immediately after `clearRecordCache()`.
- The research paper that concluded `Locales` was the only affected singleton was
  incorrect. `TenantCountriesCollection` and `NotificationLocalesManager` also hold
  country references, which is why `resetCollection()` cannot safely replace
  `clearRecordCache()` today.

### References

- `src/classes/DBHelper/BaseCollection.php` — `clearRecordCache()` and `resetCollection()` docblocks
- `src/classes/Application/Locales/Locales.php` — `clearLocaleCache()`
- `hcp-editor/tests/MailEditorTestClasses/MailTestCase.php` — current co-reset calls in `setUp()`
- `hcp-editor/docs/agents/projects/test-fixes.md` — full rationale for the current approach
- `hcp-editor/docs/agents/plans/2026-07-16-countries-reset-collection/synthesis.md` — implementation notes
