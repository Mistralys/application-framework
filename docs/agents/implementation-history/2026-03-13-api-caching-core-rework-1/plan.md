# Plan — API Caching Core: Post-Phase-1 Hardening & Alignment

## Summary

This plan addresses the strategic recommendations from the Phase 1 synthesis report (`2026-03-13-api-caching-core/synthesis.md`). It covers three areas: **(A)** enforcing cache user isolation at the type system level so implementors of user-scoped API methods cannot accidentally serve cross-user cached data, **(B)** defensive hardening of `APICacheManager` and `CacheableAPIMethodTrait` against edge cases, and **(C)** code quality alignment between the API cache and AI cache strategy systems (strategy IDs, duration constants, PHPDoc, PHPStan annotations).

## Architectural Context

### API Cache Module

- **Location:** `src/classes/Application/API/Cache/`
- **Key files:**
  - `APICacheStrategyInterface.php` — strategy contract (`getID()`, `isCacheFileValid()`)
  - `Strategies/FixedDurationStrategy.php` — TTL-based strategy with 7 duration constants
  - `Strategies/ManualOnlyStrategy.php` — manual-invalidation-only strategy
  - `APICacheManager.php` — static cache folder manager
  - `CacheableAPIMethodInterface.php` — opt-in caching contract for API methods
  - `CacheableAPIMethodTrait.php` — cache-aside implementation (cache key, read, write, invalidate)
  - `APIResponseCacheLocation.php` — CacheControl admin UI integration
- **Integration point:** `BaseAPIMethod::_process()` (lines ~153–174) performs two `instanceof CacheableAPIMethodInterface` checks for transparent cache read/write.

### Extensibility Pattern

The caching system follows the `APIKeyMethodInterface` / `APIKeyMethodTrait` composition pattern:
- Interface extends `APIMethodInterface` to declare capability
- Trait provides default implementations
- `BaseAPIMethod` detects the interface via `instanceof` and acts accordingly
- `initReservedParams()` auto-registers parameters for detected interfaces

### AI Cache Module (Alignment Target)

- **Location:** `src/classes/Application/AI/Cache/`
- **Strategy interface:** `AICacheStrategyInterface` (extends `StringPrimaryRecordInterface`)
- **Strategies:** `FixedDurationStrategy` (4 duration constants, `STRATEGY_ID = 'fixed_duration'`), `UncachedStrategy` (`STRATEGY_ID = 'uncached'`)
- **Base class:** `BaseAICacheStrategy` (abstract, provides file management)

### User Authentication in API Methods

- API methods run outside normal session context (`APIBootstrap` calls `disableAuthentication()`)
- User identification happens via API keys (`APIKeyMethodInterface`), not sessions
- `collectRequestData()` is **bypassed entirely on cache hits** — this is the security-critical gap

## Approach / Architecture

### A. User Isolation Enforcement (Security — High Priority)

Introduce a dedicated `UserScopedCacheInterface` / `UserScopedCacheTrait` pair that makes it **structurally impossible** for user-scoped methods to omit user-identifying data from cache keys.

**Design:**

1. A new `UserScopedCacheInterface` extends `CacheableAPIMethodInterface` and adds a single method: `getUserCacheIdentifier(): string`. This method must return a user-identifying value (e.g., pseudo user ID from API key, or an application-defined user scope token).

2. A new `UserScopedCacheTrait` (which uses `CacheableAPIMethodTrait`) overrides `getCacheKeyParameters()` to **automatically inject** the user identifier into the cache key parameters before hashing. This means:
   - The implementing method defines domain-specific params via `getUserScopedCacheKeyParameters()` (renamed from `getCacheKeyParameters()` in user-scoped context).
   - The trait's `getCacheKeyParameters()` merges the user identifier with the domain params.
   - There is no way to "forget" the user identifier — the trait enforces it.

3. `BaseAPIMethod::_process()` needs **no changes** — the `instanceof CacheableAPIMethodInterface` check already covers the subinterface.

**Result:** Two-tier system:
- `CacheableAPIMethodInterface` + `CacheableAPIMethodTrait` — for stateless, non-user-scoped methods (e.g., `GetAppLocalesAPI`)
- `UserScopedCacheInterface` + `UserScopedCacheTrait` — for methods returning user-specific data (enforces user isolation by construction)

### B. Defensive Hardening (Medium Priority)

1. **`APICacheManager::getMethodCacheFolder()` input validation** — add a precondition guard rejecting empty strings and strings containing directory separators. The `invalidateMethod('') === clearAll()` scenario is the most dangerous.

2. **`CacheableAPIMethodTrait::readFromCache()` corrupt-cache resilience** — wrap `JSONFile::parse()` in a try-catch. On parse failure, delete the corrupt file and return `null` (transparent fallback to fresh computation).

### C. Code Quality & AI Cache Alignment (Low Priority)

1. **Add `STRATEGY_ID` constants** to `FixedDurationStrategy` and `ManualOnlyStrategy` using **PascalCase** (project norm for strategy IDs), e.g., `'FixedDuration'`, `'ManualOnly'`.
2. **Change `serialize()` to `json_encode()`** in `CacheableAPIMethodTrait::getCacheKey()`.
3. **Add explicit `filemtime() === false` guard** in `FixedDurationStrategy::isCacheFileValid()`.
4. **Add `@phpstan-require-implements CacheableAPIMethodInterface`** to `CacheableAPIMethodTrait`.
5. **Add `@phpstan-require-implements UserScopedCacheInterface`** to the new `UserScopedCacheTrait`.
6. **Add class-level PHPDoc** (`@package`, `@subpackage`) to `FixedDurationStrategy` and `ManualOnlyStrategy`.
7. **Fix `@package` annotation** on `RegisterAPIResponseCacheListener` to align with sibling listeners.
8. **Align duration constants** — add `DURATION_1MIN`, `DURATION_5MIN`, `DURATION_15MIN` to AI cache's `FixedDurationStrategy` for parity, or document the intentional divergence.

## Rationale

- **Interface + trait enforcement vs. docblock warning:** A docblock warning is advisory — developers may not read it, or may read it and still forget. A dedicated interface with a trait that automatically injects user identifiers into the cache key makes the security constraint structural. This follows the `APIKeyMethodInterface` pattern already established in the framework.
- **Subinterface rather than modifying `CacheableAPIMethodInterface`:** Not all cacheable methods are user-scoped. `GetAppLocalesAPI` and `GetAppCountriesAPI` are stateless — forcing them to provide a user identifier would be wrong. The two-tier approach cleanly separates concerns.
- **PascalCase for strategy IDs:** Aligns with the codebase's `PascalCase` convention for API method names and constant identifiers. The AI cache's `snake_case` strategy IDs should also be migrated to PascalCase for consistency.
- **`json_encode()` over `serialize()`:** More portable, avoids `__sleep()` edge cases with complex objects, and makes the intent (scalar-only params) explicit.
- **Corrupt-cache resilience:** A transparent cache layer must never turn a working API method into a broken one because of a stale file. Silent fallback to fresh computation is the only acceptable behavior.

## Detailed Steps

### Step 1 — UserScopedCacheInterface

Create `src/classes/Application/API/Cache/UserScopedCacheInterface.php`:

```php
interface UserScopedCacheInterface extends CacheableAPIMethodInterface
{
    /**
     * Returns a unique identifier for the current user context.
     * This value is automatically injected into the cache key
     * by {@see UserScopedCacheTrait} to ensure per-user cache
     * isolation.
     *
     * @return string A non-empty user-identifying value
     */
    public function getUserCacheIdentifier() : string;

    /**
     * Returns the method-specific cache key parameters,
     * excluding user identification (which is handled
     * automatically by the trait).
     *
     * @return array<string,mixed>
     */
    public function getUserScopedCacheKeyParameters() : array;
}
```

### Step 2 — APICacheException

Create `src/classes/Application/API/Cache/APICacheException.php`:

- Extend `Application\API\APIException` (inherits the framework 4-parameter exception signature).
- Define error code constants for all cache-specific error conditions (e.g., `ERROR_EMPTY_USER_CACHE_IDENTIFIER`, `ERROR_INVALID_METHOD_NAME`, `ERROR_CACHE_FILE_CORRUPT`).

### Step 3 — UserScopedCacheTrait

Create `src/classes/Application/API/Cache/UserScopedCacheTrait.php`:

- Use `CacheableAPIMethodTrait` internally.
- Override `getCacheKeyParameters()` to:
  1. Call `$this->getUserCacheIdentifier()`.
  2. **Hard fail** if the return value is empty (`throw new APICacheException(...)`) — a user-scoped method must always provide a user identifier; silent fallback is not acceptable.
  3. Merge `'_userScope' => $identifier` with the result of `getUserScopedCacheKeyParameters()`.
- Add `@phpstan-require-implements UserScopedCacheInterface`.

### Step 4 — APICacheManager::getMethodCacheFolder() Input Validation

In `APICacheManager::getMethodCacheFolder()`:
- Add precondition: throw `APICacheException` if `$methodName` is empty or contains `DIRECTORY_SEPARATOR` or `/` or `..`.
- This prevents the `invalidateMethod('') === clearAll()` scenario and blocks path traversal.

### Step 5 — readFromCache() Corrupt-Cache Resilience

In `CacheableAPIMethodTrait::readFromCache()`:
- Wrap `$cacheFile->parse()` in a try-catch for `\Throwable`.
- On exception: delete the corrupt file (best-effort, ignore deletion failures) and return `null`.

### Step 6 — serialize() → json_encode() in getCacheKey()

In `CacheableAPIMethodTrait::getCacheKey()`:
- Replace `serialize($params)` with `json_encode($params, JSON_THROW_ON_ERROR)`.
- The `JSON_THROW_ON_ERROR` flag ensures non-encodable values fail fast rather than producing silent `false`.

### Step 7 — Add STRATEGY_ID Constants (PascalCase)

- `FixedDurationStrategy`: add `public const string STRATEGY_ID = 'FixedDuration';` and update `getID()` to return `self::STRATEGY_ID`.
- `ManualOnlyStrategy`: add `public const string STRATEGY_ID = 'ManualOnly';` and update `getID()` to return `self::STRATEGY_ID`.

### Step 8 — filemtime() Explicit Guard in FixedDurationStrategy

In `FixedDurationStrategy::isCacheFileValid()`:
- Explicitly check `filemtime()` return value. If `false`, return `false` (expired). This prevents arithmetic on `false` and satisfies strict PHPStan levels.

### Step 9 — @phpstan-require-implements on CacheableAPIMethodTrait

Add `@phpstan-require-implements CacheableAPIMethodInterface` to the class-level docblock of `CacheableAPIMethodTrait`.

### Step 10 — PHPDoc Alignment

- Add `@package API` and `@subpackage Cache` class-level docblocks to `FixedDurationStrategy` and `ManualOnlyStrategy`.
- Fix `@package` annotation on `RegisterAPIResponseCacheListener` to match sibling `RegisterAPIIndexCacheListener`.

### Step 11 — AI Cache Strategy ID Migration to PascalCase

In `src/classes/Application/AI/Cache/Strategies/`:
- `FixedDurationStrategy`: change `STRATEGY_ID` from `'fixed_duration'` to `'FixedDuration'`.
- `UncachedStrategy`: change `STRATEGY_ID` from `'uncached'` to `'Uncached'`.
- Search for any code referencing the old string values and update.

### Step 12 — Update Module Documentation

- Update `src/classes/Application/API/Cache/README.md` to document:
  - The two-tier caching pattern (stateless vs. user-scoped)
  - `UserScopedCacheInterface` / `UserScopedCacheTrait` usage
  - Strategy ID constants
- Update `src/classes/Application/API/Cache/module-context.yaml` to include the new files.

### Step 13 — Tests

- **Unit tests for UserScopedCacheTrait:** Verify that `getCacheKeyParameters()` always includes the user identifier. Verify different user identifiers produce different cache keys. Verify that an empty user identifier throws `APICacheException` (hard failure — user-scoped methods must always provide a user identifier).
- **Unit test for APICacheManager input validation:** Verify that empty string, strings with `/`, `..`, or `DIRECTORY_SEPARATOR` throw `APICacheException`.
- **Unit test for corrupt-cache resilience:** Create a file with invalid JSON, verify `readFromCache()` returns `null` and removes the file.
- **Unit test for `json_encode` cache key:** Verify deterministic hashing with the new serialization.
- **Regression:** Re-run existing 19 tests to confirm no breakage.

### Step 14 — Run composer dump-autoload and Build

- Run `composer dump-autoload` (new class files added).
- Run `composer build` to regenerate CTX documentation.

## Dependencies

- Phase 1 implementation must be complete and merged (it is — per synthesis).
- No external dependencies.

## Required Components

### New Files
- `src/classes/Application/API/Cache/APICacheException.php`
- `src/classes/Application/API/Cache/UserScopedCacheInterface.php`
- `src/classes/Application/API/Cache/UserScopedCacheTrait.php`

### Modified Files
- `src/classes/Application/API/Cache/APICacheManager.php` (input validation)
- `src/classes/Application/API/Cache/CacheableAPIMethodTrait.php` (corrupt-cache resilience, json_encode, @phpstan-require-implements)
- `src/classes/Application/API/Cache/Strategies/FixedDurationStrategy.php` (STRATEGY_ID, filemtime guard, PHPDoc)
- `src/classes/Application/API/Cache/Strategies/ManualOnlyStrategy.php` (STRATEGY_ID, PHPDoc)
- `src/classes/Application/API/Events/RegisterAPIResponseCacheListener.php` (@package fix)
- `src/classes/Application/AI/Cache/Strategies/FixedDurationStrategy.php` (STRATEGY_ID PascalCase)
- `src/classes/Application/AI/Cache/Strategies/UncachedStrategy.php` (STRATEGY_ID PascalCase)
- `src/classes/Application/API/Cache/README.md` (documentation update)
- `src/classes/Application/API/Cache/module-context.yaml` (add new files)

### New Test Files
- `tests/AppFrameworkTests/API/Cache/UserScopedCacheTest.php`
- `tests/AppFrameworkTests/API/Cache/APICacheManagerValidationTest.php`
- `tests/AppFrameworkTests/API/Cache/CorruptCacheResilienceTest.php`

### Modified Test Files
- `tests/AppFrameworkTests/API/Cache/APICacheStrategyTest.php` (strategy ID constant tests, json_encode cache key test)

## Assumptions

- The Phase 1 code is on a feature branch that has not yet been merged to main. All changes in this plan are additive to that branch.
- API methods that are user-scoped always have access to a user-identifying value (e.g., via `APIKeyMethodInterface` or application-specific context).
- No existing code references the AI cache strategy ID strings `'fixed_duration'` or `'uncached'` as stored/persisted values. If they are persisted in cache files, the migration is a no-op (cache files can be cleared).

## Constraints

- Always use `array()` syntax, never `[]`.
- Follow existing `@package` / `@subpackage` annotation conventions.
- No enums, no readonly properties (PHP 8.4+ project rules).
- New class files require `composer dump-autoload` (classmap autoloading).
- Never run the full test suite — scope tests to the API Cache module.

## Out of Scope

- `DBHelperAwareStrategy` (Phase 2 — separate plan exists at `2026-03-13-api-caching-dbhelper-invalidation/`).
- The 2 pre-existing `HtaccessGeneratorTest` failures (unrelated, `feature-openapi-specs` branch).
- The 7 pre-existing PHPStan errors (unrelated files).
- `getID()` test for strategies — covered implicitly by adding `STRATEGY_ID` constants and testing them.
- Duration constant alignment between API and AI cache (documenting the divergence is sufficient — AI cache may intentionally use fewer granularities).

## Acceptance Criteria

1. A `UserScopedCacheInterface` exists that extends `CacheableAPIMethodInterface` and requires `getUserCacheIdentifier(): string`.
2. A `UserScopedCacheTrait` exists that automatically injects the user identifier into cache key parameters — no way to bypass.
3. `APICacheManager::getMethodCacheFolder()` throws `APICacheException` for empty or path-traversal input.
4. `readFromCache()` returns `null` and deletes corrupt cache files instead of propagating exceptions.
5. Cache keys use `json_encode()` instead of `serialize()`.
6. Both API cache strategy classes have `STRATEGY_ID` constants in PascalCase.
7. Both AI cache strategy classes have `STRATEGY_ID` constants migrated to PascalCase.
8. `FixedDurationStrategy::isCacheFileValid()` explicitly handles `filemtime() === false`.
9. `@phpstan-require-implements` is present on `CacheableAPIMethodTrait` and `UserScopedCacheTrait`.
10. PHPDoc is aligned across all cache strategy and listener classes.
11. All existing 19 tests pass (regression).
12. New tests cover user-scoped cache isolation, input validation, and corrupt-cache resilience.
13. `README.md` documents the two-tier caching pattern.
14. CTX documentation regenerated via `composer build`.

## Testing Strategy

- **Unit tests** for the new `UserScopedCacheTrait`: verify user identifier injection, verify different users → different cache keys, verify the trait throws `APICacheException` on empty user identifier (hard failure).
- **Unit tests** for `APICacheManager` input validation: verify rejection of empty strings, path separators, `..` sequences (all throw `APICacheException`).
- **Unit tests** for corrupt-cache resilience: create invalid JSON files, verify transparent fallback.
- **Unit tests** for strategy ID constants: verify `getID()` returns the constant value.
- **Regression**: run existing `APICacheStrategyTest` and `APICacheIntegrationTest` to verify no breakage.
- **Scope**: `composer test-filter -- APICache` should cover all relevant tests.

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **AI cache strategy ID rename breaks persisted references** | AI cache files are ephemeral filesystem caches — clearing them (`composer clear-caches`) is safe and expected. Search for string references before renaming. |
| **`json_encode()` produces different hash than `serialize()`** | This is intentional — existing cache entries become stale misses and are replaced on next request. No data loss. |
| **`UserScopedCacheTrait` complexity confuses implementors** | Clear README documentation with usage examples showing both tiers. The interface name itself communicates intent. |
| **`filemtime()` guard changes behavior on race condition** | Previous behavior was also "treat as expired" — the guard makes the same behavior explicit and PHPStan-clean. |
