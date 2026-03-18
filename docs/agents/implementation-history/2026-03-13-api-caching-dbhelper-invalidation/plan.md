# Plan: API Response Caching — DBHelper Automatic Invalidation

**Series:** API Caching System (Plan 2 of 3)  
**Project:** Application Framework  
**Depends on:** Plan 1 (Core Caching Infrastructure)  
**Blocked by:** Plan 1 must be completed first  
**Reference:** [/docs/agents/projects/api-caching-system.md](/docs/agents/projects/api-caching-system.md)

---

## Summary

Extend the API caching system (delivered in Plan 1) with a third cache strategy — `DBHelperAwareStrategy` — that automatically invalidates cached API responses when the underlying DBHelper collections change. An `APICacheInvalidationManager` scans all cacheable API methods at boot time, identifies those using DBHelper-aware caching, and wires `onAfterCreateRecord`/`onAfterDeleteRecord` event listeners to trigger per-method cache invalidation. A fixed-duration TTL acts as a safety net for record updates (since `BaseRecord::save()` does not fire a collection-level event).

## Architectural Context

- **Plan 1 deliverables (prerequisite):** `APICacheStrategyInterface`, `FixedDurationStrategy`, `ManualOnlyStrategy`, `CacheableAPIMethodInterface`/`CacheableAPIMethodTrait`, `APICacheManager`, modified `BaseAPIMethod::_process()`.
- **DBHelper collection events:** `DBHelper_BaseCollection` provides `onAfterCreateRecord(callable)` and `onAfterDeleteRecord(callable)` methods that register listeners. Events fire after `triggerAfterCreateRecord()` / `triggerAfterDeleteRecord()` in the collection. Event classes live in `src/classes/DBHelper/BaseCollection/Event/`.
- **Record save gap:** `BaseRecord::save()` does not fire a collection-level event — only record-level events. This means record updates cannot trigger automatic cache invalidation. The TTL safety net covers this.
- **API method discovery:** `APIManager::getInstance()->getMethodCollection()->getAll()` returns all registered API method instances for scanning.
- **Wiring point:** The invalidation manager's `registerListeners()` must run once during application setup, after API methods are registered but before request processing.

## Approach / Architecture

1. **`DBHelperAwareStrategy`** extends `FixedDurationStrategy` (inherits TTL validity) and adds a list of `DBHelper_BaseCollection` class-strings that the API method depends on.
2. **`APICacheInvalidationManager`** is a static utility that:
   - Scans all API methods for `CacheableAPIMethodInterface` implementations using `DBHelperAwareStrategy`.
   - Builds a reverse map: collection class → method names that depend on it.
   - Registers `onAfterCreateRecord` and `onAfterDeleteRecord` listeners on each collection to call `APICacheManager::invalidateMethod()` for all dependent methods.
3. **Wiring:** Lazy initialization in `APIManager::process()` — call `APICacheInvalidationManager::registerListeners()` once on first API request. This keeps it automatic and requires no application-level changes.

## Rationale

- **Extends `FixedDurationStrategy`** rather than composing it — the TTL safety net is always needed because record updates aren't covered by collection events. Inheritance avoids duplicating the `isCacheFileValid()` logic.
- **Lazy registration in `APIManager::process()`** avoids wiring overhead on non-API requests and requires zero application bootstrap changes.
- **Reverse map (collection → methods)** ensures each collection only gets one set of listeners regardless of how many methods depend on it, and the invalidation callback is efficient.

## Detailed Steps

### Step 1: Create `DBHelperAwareStrategy`

Create `src/classes/Application/API/Cache/Strategies/DBHelperAwareStrategy.php`:

- Extends `FixedDurationStrategy`
- Constructor: `array $collectionClasses, int $durationInSeconds = self::DURATION_24_HOURS`
- Stores `class-string<\DBHelper_BaseCollection>[]`
- `getID()` → `'dbhelper_aware'`
- `getCollectionClasses(): array` — returns the stored collection classes
- Inherits `isCacheFileValid()` from parent (TTL safety net)

See the project document for the full implementation.

### Step 2: Create `APICacheInvalidationManager`

Create `src/classes/Application/API/Cache/APICacheInvalidationManager.php`:

**`registerListeners(): void`** (public static):
1. Call `collectBindings()` to build the reverse map.
2. For each collection class in the map:
   - Instantiate the collection: `new $collectionClass()`
   - Create an invalidator closure that calls `APICacheManager::invalidateMethod()` for each dependent method name.
   - Register the closure on `onAfterCreateRecord()` and `onAfterDeleteRecord()`.

**`collectBindings(): array`** (private static):
1. Get all API methods via `APIManager::getInstance()->getMethodCollection()->getAll()`.
2. Filter to `CacheableAPIMethodInterface` instances.
3. Filter those to instances with `DBHelperAwareStrategy`.
4. Build map: `array<class-string<DBHelper_BaseCollection>, string[]>`.

### Step 3: Wire into `APIManager::process()`

Modify `src/classes/Application/API/APIManager.php`:
1. Add a private static property: `private static bool $invalidationListenersRegistered = false;`
2. At the top of `process()`, before method resolution, add:
   ```php
   if (!self::$invalidationListenersRegistered) {
       self::$invalidationListenersRegistered = true;
       APICacheInvalidationManager::registerListeners();
   }
   ```
3. Add the import: `use Application\API\Cache\APICacheInvalidationManager;`

### Step 4: Run `composer dump-autoload`

Two new files need to be indexed in the classmap.

### Step 5: Write unit tests

Create `tests/AppFrameworkTests/API/Cache/DBHelperAwareStrategyTest.php`:

- `DBHelperAwareStrategy` returns correct strategy ID (`'dbhelper_aware'`)
- `getCollectionClasses()` returns the classes passed to the constructor
- `isCacheFileValid()` behaves like `FixedDurationStrategy` (inherits TTL logic)
- Default TTL is 24 hours

### Step 6: Write integration tests

Create or extend `tests/AppFrameworkTests/API/Cache/APICacheInvalidationTest.php`:

- Create a test API method stub using `DBHelperAwareStrategy` with a test collection class.
- Call `processReturn()` to populate the cache.
- Trigger a create on the test collection.
- Verify the cache was invalidated (next `processReturn()` recomputes).
- Same for delete on the test collection.
- Verify that methods using other strategies are not affected by collection events.

**Note:** Integration tests will need a test collection class. Check if one already exists in `tests/AppFrameworkTestClasses/` or create a minimal stub. The existing test application in `tests/application/` likely has test collections available.

### Step 7: Run static analysis

Run `composer analyze` to verify PHPStan passes.

## Dependencies

- **Plan 1 deliverables:** `APICacheStrategyInterface`, `FixedDurationStrategy`, `CacheableAPIMethodInterface`, `APICacheManager`, modified `BaseAPIMethod::_process()`.
- `Application\API\APIManager` (existing class to modify)
- `DBHelper_BaseCollection::onAfterCreateRecord()` / `onAfterDeleteRecord()` (existing event methods)

## Required Components

**New files (2):**

| # | File | Type |
|---|---|---|
| 1 | `src/classes/Application/API/Cache/Strategies/DBHelperAwareStrategy.php` | Class |
| 2 | `src/classes/Application/API/Cache/APICacheInvalidationManager.php` | Class (static) |

**Modified files (1):**

| File | Change |
|---|---|
| `src/classes/Application/API/APIManager.php` | Add lazy `registerListeners()` call in `process()`, add import, add static flag |

**New test files (~2):**

| File | Type |
|---|---|
| `tests/AppFrameworkTests/API/Cache/DBHelperAwareStrategyTest.php` | Unit tests |
| `tests/AppFrameworkTests/API/Cache/APICacheInvalidationTest.php` | Integration tests |

## Assumptions

- `APIManager::getInstance()->getMethodCollection()->getAll()` returns instances of the API method classes (not metadata), allowing `instanceof` checks and `getCacheStrategy()` calls.
- Test collections exist in the framework test application or can be created as stubs for integration testing.
- Instantiating a collection class via `new $collectionClass()` is valid for all DBHelper collections intended for use with this strategy.

## Constraints

- All new files must use `declare(strict_types=1)`.
- All array creation must use `array()` syntax, never `[]`.
- No PHP enums, no `readonly` properties.
- Run `composer dump-autoload` after creating files.
- The `registerListeners()` call must be idempotent (guarded by a flag) since `process()` may be called multiple times in tests.

## Out of Scope

- **Record update invalidation** — `BaseRecord::save()` doesn't fire a collection event. The TTL safety net covers this. A future enhancement could add record-level event support.
- **Per-record granular invalidation** — only per-method invalidation is supported (all parameter combinations for a method are cleared).
- **HCP Editor API method conversion** — covered in Plan 3.

## Acceptance Criteria

- [ ] `DBHelperAwareStrategy` created with correct inheritance from `FixedDurationStrategy`.
- [ ] `APICacheInvalidationManager::registerListeners()` correctly wires collection events to method cache invalidation.
- [ ] Creating a record in a watched collection invalidates the dependent method's cache.
- [ ] Deleting a record in a watched collection invalidates the dependent method's cache.
- [ ] Methods using other strategies (FixedDuration, ManualOnly) are not affected by collection events.
- [ ] The wiring in `APIManager::process()` runs exactly once (idempotent flag).
- [ ] All unit and integration tests pass via `composer test-file`.
- [ ] `composer analyze` passes with no new errors.

## Testing Strategy

- **Unit tests** for `DBHelperAwareStrategy`: correct ID, collection classes getter, inherited TTL behavior.
- **Integration tests** using test stubs: verify that collection create/delete events trigger cache invalidation for dependent methods, and do not affect non-dependent methods.
- Run with `composer test-file` or `composer test-filter`. Never run the full test suite.

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **`getAll()` returns lazy proxies that don't support `instanceof`** | Verify the return type of `getMethodCollection()->getAll()` before implementing. If proxies are returned, adapt to use metadata-based detection. |
| **Collection instantiation has side effects** | The `new $collectionClass()` call is standard DBHelper usage. If specific collections require constructor arguments, the strategy should document this constraint. |
| **Listener registration overhead on first request** | The scan runs once (guarded by static flag) and only iterates registered API methods. Overhead is negligible. |
| **Record updates not caught by events** | Documented and accepted. The TTL safety net (default 24h) ensures stale data is bounded. Consider adding record-level events as a future enhancement. |
