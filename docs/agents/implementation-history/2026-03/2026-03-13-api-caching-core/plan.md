# Plan: API Response Caching — Core Infrastructure

**Series:** API Caching System (Plan 1 of 3)  
**Project:** Application Framework  
**Depends on:** Nothing  
**Blocked by:** Nothing  
**Reference:** [/docs/agents/projects/api-caching-system.md](/docs/agents/projects/api-caching-system.md)

---

## Summary

Add file-based response caching to the framework's API method layer. API methods opt in via an Interface + Trait pair (following the existing `DryRunAPIInterface`/`DryRunAPITrait` pattern). Two cache strategies are provided: fixed-duration TTL and manual-only. A static `APICacheManager` handles folder management, per-method invalidation, and global clearing. The `BaseAPIMethod::_process()` method is modified to check and write cache. The CacheControl system receives a new cache location so the API response cache appears in the admin cache management UI.

## Architectural Context

- **API method processing:** `BaseAPIMethod::_process()` in `src/classes/Application/API/BaseMethods/BaseAPIMethod.php` is the central pipeline: `validate() → collectRequestData() → collectResponseData() → sendSuccessResponse()`. The `sendSuccessResponse()` method is typed `never` and halts execution.
- **Interface + Trait composition pattern:** Used throughout the API layer — e.g., `DryRunAPIInterface`/`DryRunAPITrait` in `src/classes/Application/API/Traits/`. Methods implement the interface and `use` the trait; the base class checks `$this instanceof SomeInterface` to enable behavior.
- **AI cache strategies:** An existing file-based TTL cache exists at `src/classes/Application/AI/Cache/Strategies/FixedDurationStrategy.php`, serving as a design reference. The API cache strategies are independent but modeled after this pattern.
- **CacheControl system:** `CacheLocationInterface` / `BaseCacheLocation` in `src/classes/Application/CacheControl/`. Locations are registered via event listeners extending `BaseRegisterCacheLocationsListener` (e.g., `RegisterAPIIndexCacheListener` in `src/classes/Application/API/Events/`).
- **Storage layout:** `Application::getStorageSubfolderPath()` provides application storage paths. Cache files go under `{APP_STORAGE}/api/cache/{MethodName}/{hash}.json`.
- **File I/O:** `FolderInfo::factory()` for folder operations, `JSONFile::factory()` for JSON file read/write. Never use raw `file_get_contents`.
- **Autoloading:** Classmap-based. Run `composer dump-autoload` after creating new files.

## Approach / Architecture

Create a new `Application\API\Cache` namespace under `src/classes/Application/API/Cache/` containing:

1. **Strategy interface** (`APICacheStrategyInterface`) — defines `getID()` and `isCacheFileValid(JSONFile)`.
2. **Two strategy implementations** — `FixedDurationStrategy` (time-based TTL) and `ManualOnlyStrategy` (always valid).
3. **Cacheable interface + trait** (`CacheableAPIMethodInterface`/`CacheableAPIMethodTrait`) — methods implement the interface and `use` the trait; they only need to define `getCacheStrategy()` and `getCacheKeyParameters()`.
4. **Cache manager** (`APICacheManager`) — static utility for folder management, per-method invalidation, and global clearing.
5. **`BaseAPIMethod::_process()` modification** — insert cache check after `validate()`/`getActiveVersion()` (short-circuits on hit via `sendSuccessResponse()`) and cache write before the final `sendSuccessResponse()`.
6. **CacheControl integration** — `APIResponseCacheLocation` + `RegisterAPIResponseCacheListener` so the cache appears in admin cache management.

## Rationale

- **Interface + Trait pattern** is the established API extensibility mechanism — no new patterns introduced.
- **File-based JSON caching** aligns with the existing AI cache and requires no additional infrastructure (no Redis/Memcached dependency).
- **Per-method subdirectories** allow `invalidateMethod()` to delete one method's entire cache with a single folder delete, without touching other methods.
- **Cache check after `validate()`** is required because parameter values (needed for the cache key) are only available after validation runs.
- **Two-phase approach** (core now, DBHelper invalidation later) avoids coupling the base caching mechanism to DBHelper events, keeping Phase 1 self-contained.

## Detailed Steps

### Step 1: Create cache strategy interface

Create `src/classes/Application/API/Cache/APICacheStrategyInterface.php` with:
- `getID(): string`
- `isCacheFileValid(JSONFile $cacheFile): bool`

See the project document for the full implementation.

### Step 2: Create `FixedDurationStrategy`

Create `src/classes/Application/API/Cache/Strategies/FixedDurationStrategy.php` with:
- Duration constants: 1min, 5min, 15min, 1h, 6h, 12h, 24h
- Constructor accepting `int $durationInSeconds`
- `isCacheFileValid()` using `filemtime()` comparison

### Step 3: Create `ManualOnlyStrategy`

Create `src/classes/Application/API/Cache/Strategies/ManualOnlyStrategy.php`:
- `isCacheFileValid()` always returns `true`
- Cache only invalidated by explicit `invalidateCache()` calls

### Step 4: Create `CacheableAPIMethodInterface`

Create `src/classes/Application/API/Cache/CacheableAPIMethodInterface.php` extending `APIMethodInterface`:
- `getCacheStrategy(): APICacheStrategyInterface`
- `getCacheKeyParameters(): array`
- `getCacheKey(string $version): string`
- `readFromCache(string $version): ?array`
- `writeToCache(string $version, array $data): void`
- `invalidateCache(): void`

### Step 5: Create `APICacheManager`

Create `src/classes/Application/API/Cache/APICacheManager.php`:
- `getCacheFolder(): FolderInfo` — returns `{APP_STORAGE}/api/cache/`
- `invalidateMethod(string $methodName): void` — deletes the method's subfolder
- `clearAll(): void` — deletes and recreates the entire cache folder
- `getCacheSize(): int` — returns total byte size

### Step 6: Create `CacheableAPIMethodTrait`

Create `src/classes/Application/API/Cache/CacheableAPIMethodTrait.php`:
- `getCacheKey()` — builds hash from method name + version + sorted parameter values
- `getCacheFile()` — returns `JSONFile` at `{cache}/{MethodName}/{hash}.json`
- `readFromCache()` — checks existence, validates via strategy, returns parsed data or null
- `writeToCache()` — writes data via `JSONFile::putData()`
- `invalidateCache()` — delegates to `APICacheManager::invalidateMethod()`

**Note:** The trait depends on `APICacheManager` (Step 5), so it must be created after the manager.

### Step 7: Modify `BaseAPIMethod::_process()`

In `src/classes/Application/API/BaseMethods/BaseAPIMethod.php`:
1. Add `use Application\API\Cache\CacheableAPIMethodInterface;` import.
2. After `$version = $this->getActiveVersion();`, insert cache check:
   ```php
   if ($this instanceof CacheableAPIMethodInterface) {
       $cached = $this->readFromCache($version);
       if ($cached !== null) {
           $this->sendSuccessResponse(ArrayDataCollection::create($cached));
       }
   }
   ```
3. Before `$this->sendSuccessResponse($response);`, insert cache write:
   ```php
   if ($this instanceof CacheableAPIMethodInterface) {
       $this->writeToCache($version, $response->getData());
   }
   ```

### Step 8: Create `APIResponseCacheLocation`

Create `src/classes/Application/API/Cache/APIResponseCacheLocation.php` extending `BaseCacheLocation`:
- `getID()` → `'APIResponseCache'`
- `getLabel()` → `t('API Response Cache')`
- `getByteSize()` → delegates to `APICacheManager::getCacheSize()`
- `clear()` → delegates to `APICacheManager::clearAll()`

### Step 9: Create `RegisterAPIResponseCacheListener`

Create `src/classes/Application/API/Events/RegisterAPIResponseCacheListener.php` extending `BaseRegisterCacheLocationsListener`:
- Returns `array(new APIResponseCacheLocation())` from `getCacheLocations()`

Follows the same pattern as the existing `RegisterAPIIndexCacheListener` in the same directory.

### Step 10: Run `composer dump-autoload`

Required because the project uses classmap autoloading. All 8 new files must be indexed.

### Step 11: Write unit tests

Create test file(s) under `tests/AppFrameworkTests/API/Cache/`:

**Strategy tests:**
- `FixedDurationStrategy::isCacheFileValid()` returns `true` for fresh file
- `FixedDurationStrategy::isCacheFileValid()` returns `false` for expired file
- `ManualOnlyStrategy::isCacheFileValid()` always returns `true`

**Cache key tests:**
- `getCacheKey()` is deterministic (same inputs → same hash)
- `getCacheKey()` varies by version
- `getCacheKey()` varies by parameter values

**Cache manager tests:**
- `invalidateMethod()` deletes only the target method folder
- `clearAll()` deletes all method folders

### Step 12: Write integration tests

Create integration test(s) using a test API method stub that implements `CacheableAPIMethodInterface`:

- Call `processReturn()` twice — second call returns same data from cache file
- Call `processReturn()`, then `invalidateCache()`, then `processReturn()` — second call computes fresh
- Verify cache file written to expected path `{APP_STORAGE}/api/cache/{MethodName}/`
- `APIResponseCacheLocation::getByteSize()` returns > 0 after caching
- `APIResponseCacheLocation::clear()` removes all cached responses

Use `composer test-file` or `composer test-filter` to run tests. **Never run the full suite.**

### Step 13: Run static analysis

Run `composer analyze` to verify PHPStan passes with the new code.

## Dependencies

- `AppUtils\FileHelper\JSONFile` (existing dependency)
- `AppUtils\FileHelper\FolderInfo` (existing dependency)
- `Application\CacheControl\BaseCacheLocation` (existing class)
- `Application\CacheControl\Events\BaseRegisterCacheLocationsListener` (existing class)
- `Application\API\BaseMethods\BaseAPIMethod` (existing class to modify)
- `Application\API\APIMethodInterface` (existing interface to extend)

## Required Components

**New files (8):**

| # | File | Type |
|---|---|---|
| 1 | `src/classes/Application/API/Cache/APICacheStrategyInterface.php` | Interface |
| 2 | `src/classes/Application/API/Cache/Strategies/FixedDurationStrategy.php` | Class |
| 3 | `src/classes/Application/API/Cache/Strategies/ManualOnlyStrategy.php` | Class |
| 4 | `src/classes/Application/API/Cache/CacheableAPIMethodInterface.php` | Interface |
| 5 | `src/classes/Application/API/Cache/CacheableAPIMethodTrait.php` | Trait |
| 6 | `src/classes/Application/API/Cache/APICacheManager.php` | Class (static) |
| 7 | `src/classes/Application/API/Cache/APIResponseCacheLocation.php` | Class |
| 8 | `src/classes/Application/API/Events/RegisterAPIResponseCacheListener.php` | Listener |

**Modified files (1):**

| File | Change |
|---|---|
| `src/classes/Application/API/BaseMethods/BaseAPIMethod.php` | Add cache check + write in `_process()`, add import |

**New test files (~2):**

| File | Type |
|---|---|
| `tests/AppFrameworkTests/API/Cache/APICacheStrategyTest.php` | Unit tests |
| `tests/AppFrameworkTests/API/Cache/APICacheIntegrationTest.php` | Integration tests |

## Assumptions

- `ArrayDataCollection::create()` accepts an associative array to pre-populate the collection (used when restoring cached data).
- `sendSuccessResponse()` is typed `never` and halts execution, so the cache hit path short-circuits correctly without a `return` statement.
- `processReturn()` (test mode) works with the cache because `sendSuccessResponse()` throws `APIResponseDataException` in return mode, which `processReturn()` catches — no change needed.
- The existing event listener discovery mechanism will automatically pick up `RegisterAPIResponseCacheListener` without manual registration.

## Constraints

- All new files must use `declare(strict_types=1)`.
- All array creation must use `array()` syntax, never `[]`.
- No PHP enums, no `readonly` properties.
- Run `composer dump-autoload` after creating files (classmap autoloading).
- Follow the exact class/method/constant naming shown in the project document.

## Out of Scope

- **DBHelper-aware automatic invalidation** — covered in Plan 2.
- **HCP Editor API method conversion** — covered in Plan 3.
- **Admin UI for viewing/managing the API cache** — beyond the three-plan scope.
- **Redis/Memcached backends** — file-based only.

## Acceptance Criteria

- [ ] All 8 new files created with correct namespaces and conventions.
- [ ] `BaseAPIMethod::_process()` contains cache check and write blocks.
- [ ] A test API method implementing `CacheableAPIMethodInterface` with `FixedDurationStrategy` returns cached responses on the second `processReturn()` call.
- [ ] `invalidateCache()` forces fresh computation on the next call.
- [ ] `APICacheManager::clearAll()` removes all cached data.
- [ ] `APIResponseCacheLocation` appears in the CacheControl system and reports correct size.
- [ ] All unit and integration tests pass via `composer test-file`.
- [ ] `composer analyze` passes with no new errors.

## Testing Strategy

- **Unit tests** for strategies (TTL validity, manual always-valid) and cache key generation (determinism, version/parameter variation).
- **Unit tests** for `APICacheManager` (per-method invalidation, global clear).
- **Integration tests** using a test stub API method and `processReturn()` to verify end-to-end cache hit/miss/invalidation behavior.
- **CacheControl integration tests** to verify byte size reporting and clearing.
- Run with `composer test-file` or `composer test-filter`. Never run the full test suite.

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **`ArrayDataCollection::create($cached)` doesn't accept array argument** | Verify the method signature before implementing the cache hit path; adapt if needed (e.g., `create()->setKeys($cached)`). |
| **Cache files accumulate without bound** | `clearAll()` available via CacheControl admin; TTL strategies provide automatic expiry. Plan 2 adds event-based invalidation. |
| **Race condition: two concurrent requests write the same cache file** | JSON write is atomic at the filesystem level (write-then-rename in `JSONFile::putData()`). Last writer wins, which is acceptable since both write the same data. |
| **`validate()` side effects on cache hit path** | Validate runs on every request (needed for cache key). Confirm it has no expensive side effects beyond parameter parsing. |
