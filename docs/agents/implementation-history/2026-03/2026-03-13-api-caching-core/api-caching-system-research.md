# Research Report: API Method Response Caching System

## Problem Statement

The framework's API method layer has no built-in response caching. Every API call — even for rarely-changing data like tenant lists, comtype catalogs, or layout metadata — executes the full pipeline (parameter validation → request data collection → response data collection → serialization) on every request. This is wasteful for the many read-only GET methods that return stable data.

The goal is to design a flexible, file-based caching system for API method responses that supports multiple invalidation strategies: fixed-duration TTL, manual invalidation, and automatic invalidation when underlying DBHelper collections change.

## Problem Decomposition

1. **Cache interception point**: Where in the `BaseAPIMethod::_process()` pipeline to intercept and serve cached responses.
2. **Opt-in mechanism**: How individual API methods declare themselves as cacheable and configure their caching behavior.
3. **Cache key generation**: How to produce deterministic, collision-free keys from method name + version + parameters.
4. **Cache storage**: File-based storage layout under `{APP_STORAGE}`.
5. **Invalidation strategies**: Fixed-duration, manual-only, and DBHelper-collection-aware automatic invalidation.
6. **DBHelper integration**: Mapping collections to API methods and listening for mutation events to trigger invalidation.
7. **Cache management UI**: Integration with the existing CacheControl system (CacheManager + admin screen).
8. **Test mode compatibility**: Ensuring `processReturn()` works correctly with caching.

## Context & Constraints

- **PHP 8.4+**, `declare(strict_types=1)` required.
- **`array()` syntax** — not `[]` — for all array creation.
- **No enums, no `readonly` properties.**
- **Classmap autoloading** — `composer dump-autoload` after new files.
- **File-based storage only** — no Redis, Memcached, or APCu. All caching must use the filesystem under `Application::getCacheFolder()`.
- **Existing patterns to follow**: AI cache strategies (`BaseAICacheStrategy`, `FixedDurationStrategy`) use `JSONFile` + `filemtime()` validation. CacheControl system (`CacheLocationInterface`, `BaseCacheLocation`) provides admin UI integration.
- **50+ API methods** exist in the HCP Editor project across modules (tenants, comtypes, templates, global contents, variables, connectors). Approximately 70% are read-only GET methods — prime caching candidates.
- **DBHelper events** available for invalidation: `AfterCreateRecordEvent`, `AfterDeleteRecordEvent` on collections; `KeyModifiedEvent` on records. No `AfterSave` event — only the `_postSave()` hook exists on records.
- **The `_process()` pipeline is `private`** — caching logic must be added inside `BaseAPIMethod` itself or via a check within `_process()`.

## Prior Art & Known Patterns

### Pattern 1: Interface + Trait Opt-In (Recommended)

- **Description:** API methods declare cacheability by implementing a `CacheableAPIMethodInterface` and using a `CacheableAPIMethodTrait`. The `_process()` method in `BaseAPIMethod` checks for the interface and shortcuts the pipeline when a valid cache entry exists. This mirrors the existing `JSONResponseInterface` / `JSONResponseTrait` and `DryRunAPIInterface` / `DryRunAPITrait` composition patterns already used in the framework.
- **Where used:** This is the established pattern throughout the framework's API layer for optional capabilities (JSON responses, API key auth, dry-run mode).
- **Strengths:** Fully opt-in, zero impact on non-cacheable methods. Each method controls its own cache key, strategy, and TTL. Follows the project's existing architecture exactly.
- **Weaknesses:** Requires a small change to `BaseAPIMethod::_process()` (adding the cache check). Each cacheable method must explicitly implement the interface.
- **Fit:** **Excellent.** Aligns perfectly with the interface+trait composition pattern already governing the API method layer.

### Pattern 2: Decorator / Middleware Wrapper

- **Description:** A `CachingAPIMethodDecorator` wraps any `APIMethodInterface` instance. `APIManager::loadMethod()` would wrap cacheable methods in the decorator before calling `process()`. The decorator checks the cache before delegating to the wrapped method.
- **Where used:** Common in PSR-15 middleware stacks (Slim, Laravel, Symfony).
- **Strengths:** Clean separation of concerns — caching logic is completely isolated from method logic.
- **Weaknesses:** Breaks the framework's established composition model. `BaseAPIMethod::process()` and `sendSuccessResponse()` are `final` / `private` / call `Application::exit()` — decorating around them requires fighting the architecture. The `processReturn()` test mode relies on exception-based flow control that a decorator would need to replicate exactly. `APIMethodInterface` has 20+ methods, making delegation boilerplate heavy.
- **Fit:** **Poor.** Fights the existing architecture rather than working with it.

### Pattern 3: Subclass-Level Manual Caching

- **Description:** Each API method individually implements caching in its `collectResponseData()` — checking a cache file, returning early if valid, or writing the cache after computing the response.
- **Where used:** `GetMailForgeAppInfo` in the HCP Editor uses a static property for single-request caching. This is the only existing example.
- **Strengths:** No framework changes needed.
- **Weaknesses:** Massive code duplication across 30+ methods. No centralized invalidation. No admin UI integration. No standard cache key generation. Every developer reinvents the wheel.
- **Fit:** **Poor** as a systematic solution. Acceptable only for one-off cases.

### Pattern 4: APIManager-Level Response Cache

- **Description:** `APIManager::process()` checks a cache before instantiating the method class at all. If a cached response exists for the method name + parameters, it sends the cached JSON directly without constructing or executing the method object.
- **Where used:** Reverse proxy caches (Varnish, Nginx), CDN caching.
- **Strengths:** Maximum performance — skips object construction entirely. Centralized.
- **Weaknesses:** Cannot access the method instance to determine cache configuration (TTL, key components), because the method hasn't been instantiated yet. Would require a separate configuration registry mapping method names to cache policies. Cannot generate proper response envelopes (the `api` metadata block with `requestTime`, `selectedVersion`, etc.) without the method instance. Doesn't support `processReturn()` test mode.
- **Fit:** **Moderate.** Fast, but requires a parallel configuration system and loses response metadata accuracy.

## Alternative & Creative Approaches

### Hybrid: Interface Opt-In + Lazy Manager Cache Check

Combine Pattern 1 and Pattern 4: `APIManager` instantiates the method (to access its cache configuration), then checks the cache *before* calling `process()`. If the cache hits, the manager sends the cached response directly. If it misses, it calls `process()` normally, and `BaseAPIMethod::_process()` writes the response to cache after `collectResponseData()` completes.

- **Rationale:** Gets the configuration from the method instance (Pattern 1 benefit) while keeping the cache-hit path extremely fast (Pattern 4 benefit). The method is instantiated (cheap — just constructor + `init()` for parameter registration) but the expensive `collectRequestData()` + `collectResponseData()` are skipped on cache hit.
- **Risk:** Parameter validation must still run before the cache check, because the cache key depends on validated parameter values. This means the full validation step executes even on cache hits. However, validation is cheap compared to database queries.

This hybrid is essentially Pattern 1 with the cache check positioned optimally inside `_process()`.

### DBHelper Invalidation via Offline Event Listeners

Rather than requiring API methods to manually register their collection dependencies at runtime, use the same **offline event listener** pattern the framework already uses for `RegisterCacheLocationsEvent`. An offline event like `RegisterAPICacheBindingsEvent` would let each module declare which collections invalidate which API methods — discovered at build time, cached in a JSON index.

- **Rationale:** Keeps invalidation configuration decoupled from runtime. The index can be rebuilt with `composer build`. Follows the established offline event pattern.
- **Risk:** Adds one more build step. The binding declarations must be kept in sync with actual method dependencies.

## Comparative Evaluation

| Criterion | Pattern 1: Interface+Trait | Pattern 2: Decorator | Pattern 3: Manual | Pattern 4: Manager-Level | Hybrid |
|---|---|---|---|---|---|
| **Complexity** | Low | High | None (per method: high) | Moderate | Low-Moderate |
| **Performance (cache hit)** | Good (skips collect*) | Good | Good | Best (skips instantiation) | Good |
| **Performance (cache miss)** | Negligible overhead | Moderate overhead | None | Low overhead | Negligible overhead |
| **Maintainability** | Excellent (follows existing patterns) | Poor (fights architecture) | Poor (duplication) | Moderate (separate config) | Excellent |
| **Risk** | Low | High | Low | Moderate | Low |
| **Time to implement** | Moderate | High | Per-method cost | Moderate | Moderate |
| **Framework alignment** | Perfect | Poor | N/A | Partial | Perfect |
| **Test mode support** | Full | Complex | Full | Complex | Full |
| **Admin UI integration** | Via CacheControl | Via CacheControl | None | Via CacheControl | Via CacheControl |

## Recommendation

**Pattern 1 (Interface + Trait Opt-In)** is the clear winner. It follows the framework's established composition model exactly, requires minimal changes to `BaseAPIMethod`, and gives each API method full control over its caching behavior.

The DBHelper invalidation binding should use a **method-declared approach** (the method itself declares which collections it depends on) rather than an external registry, since this keeps the knowledge close to the code that uses it.

### Recommended Architecture

#### 1. New Interface: `CacheableAPIMethodInterface`

```php
namespace Application\API\Cache;

interface CacheableAPIMethodInterface
{
    /**
     * Returns the cache strategy for this method.
     */
    public function getCacheStrategy(): APICacheStrategyInterface;

    /**
     * Returns the parameter names whose values contribute to the cache key.
     * The cache key is built from: method name + version + sorted parameter values.
     * Return an empty array if the method takes no parameters (or parameters
     * don't affect the response).
     *
     * @return string[]
     */
    public function getCacheKeyParameters(): array;
}
```

#### 2. New Trait: `CacheableAPIMethodTrait`

Provides default implementations for cache key generation, cache file resolution, cache read/write, and invalidation. Methods using the trait only need to implement `getCacheStrategy()` and `getCacheKeyParameters()`.

```php
namespace Application\API\Cache;

trait CacheableAPIMethodTrait
{
    public function getCacheKey(string $version): string
    {
        $parts = array($this->getMethodName(), $version);

        foreach ($this->getCacheKeyParameters() as $paramName) {
            $value = $this->getParam($paramName, '');
            $parts[] = $paramName . '=' . (is_array($value) ? md5(serialize($value)) : (string)$value);
        }

        return md5(implode('|', $parts));
    }

    public function getCacheFile(string $version): JSONFile
    {
        return JSONFile::factory(
            APICacheManager::getCacheFolder() . '/'
            . $this->getMethodName() . '/'
            . $this->getCacheKey($version) . '.json'
        );
    }

    public function readFromCache(string $version): ?array
    {
        $file = $this->getCacheFile($version);

        if (!$file->exists()) {
            return null;
        }

        if (!$this->getCacheStrategy()->isCacheFileValid($file)) {
            return null;
        }

        return $file->parse();
    }

    public function writeToCache(string $version, array $data): void
    {
        $this->getCacheFile($version)->putData($data);
    }

    public function invalidateCache(): void
    {
        $folder = FolderInfo::factory(
            APICacheManager::getCacheFolder() . '/' . $this->getMethodName()
        );

        if ($folder->exists()) {
            $folder->delete();
        }
    }
}
```

#### 3. Cache Strategies

Reuse the existing pattern from the AI cache system:

| Strategy | Class | Behavior |
|---|---|---|
| **FixedDuration** | `FixedDurationCacheStrategy` | TTL-based via `filemtime()` check. Configurable duration (1h, 6h, 12h, 24h, custom). |
| **ManualOnly** | `ManualOnlyCacheStrategy` | Never expires on its own. Only cleared via admin UI or programmatic `invalidateCache()` call. |
| **DBHelperAware** | `DBHelperAwareCacheStrategy` | Extends FixedDuration with collection binding. Declares which `DBHelper_BaseCollection` classes it depends on. Automatically invalidated when those collections fire `AfterCreateRecordEvent` or `AfterDeleteRecordEvent`. Falls back to TTL if events are missed (safety net). |

```php
namespace Application\API\Cache\Strategies;

interface APICacheStrategyInterface
{
    public function getID(): string;
    public function isCacheFileValid(JSONFile $cacheFile): bool;
}

class FixedDurationCacheStrategy implements APICacheStrategyInterface
{
    // Same pattern as AI's FixedDurationStrategy.
    // Validates: (time() - filemtime($file)) < $durationInSeconds
}

class ManualOnlyCacheStrategy implements APICacheStrategyInterface
{
    // isCacheFileValid() always returns true (file exists = valid).
}

class DBHelperAwareCacheStrategy extends FixedDurationCacheStrategy
{
    /**
     * @param class-string<DBHelper_BaseCollection>[] $collectionClasses
     */
    public function __construct(array $collectionClasses, int $durationInSeconds = self::DURATION_24_HOURS)
    {
        parent::__construct($durationInSeconds);
        $this->collectionClasses = $collectionClasses;
    }

    /**
     * @return class-string<DBHelper_BaseCollection>[]
     */
    public function getCollectionClasses(): array
    {
        return $this->collectionClasses;
    }
}
```

#### 4. Interception Point in `BaseAPIMethod::_process()`

Add a cache check after validation (because parameter values are needed for the cache key) but before `collectRequestData()` and `collectResponseData()`:

```php
private function _process(): void
{
    $this->time = Microtime::createNow();

    $this->validate();

    $version = $this->getActiveVersion();

    // --- NEW: Cache check ---
    if ($this instanceof CacheableAPIMethodInterface) {
        $cached = $this->readFromCache($version);
        if ($cached !== null) {
            $this->sendSuccessResponse(ArrayDataCollection::create($cached));
            // sendSuccessResponse() is `never` — execution stops here.
        }
    }
    // --- END cache check ---

    try {
        $this->collectRequestData($version);
    } catch (Throwable $e) {
        // ... existing error handling
    }

    $response = ArrayDataCollection::create();

    try {
        $this->collectResponseData($response, $version);
    } catch (Throwable $e) {
        // ... existing error handling
    }

    // --- NEW: Write to cache ---
    if ($this instanceof CacheableAPIMethodInterface) {
        $this->writeToCache($version, $response->getData());
    }
    // --- END write to cache ---

    $this->sendSuccessResponse($response);
}
```

This adds only two small blocks to `_process()`. On cache hit, the method short-circuits before any database access. On cache miss, the response is written to the cache file after being computed.

#### 5. DBHelper Invalidation Listener

A single `APICacheInvalidationListener` wired to collections at boot time:

```php
namespace Application\API\Cache;

class APICacheInvalidationManager
{
    /**
     * Registers event listeners on all collections referenced by
     * DBHelperAwareCacheStrategy instances. Called once during application boot.
     */
    public static function registerListeners(APIMethodCollection $methods): void
    {
        // Build a map: collection class → [method names]
        $bindings = self::collectBindings($methods);

        foreach ($bindings as $collectionClass => $methodNames) {
            $collection = self::instantiateCollection($collectionClass);

            $invalidator = static function () use ($methodNames): void {
                foreach ($methodNames as $methodName) {
                    APICacheManager::invalidateMethod($methodName);
                }
            };

            $collection->onAfterCreateRecord($invalidator);
            $collection->onAfterDeleteRecord($invalidator);
        }
    }
}
```

**Important design decision:** The `_postSave()` hook on records is *not* an event and cannot be listened to externally. For record *updates* to trigger invalidation, the recommended approach is:

- Use `KeyModifiedEvent` on individual records (fine-grained but requires per-record registration — impractical for bulk).
- **Or** rely on the TTL safety net from `FixedDurationCacheStrategy` — updates will be picked up when the TTL expires. This is the pragmatic recommendation for v1.
- **Or** add an `AfterSaveRecordEvent` to collections in a future framework version (separate enhancement).

For v1, the DBHelper-aware strategy provides automatic invalidation on **create** and **delete**, with TTL fallback for **updates**. This covers the overwhelming majority of use cases, since record creation and deletion change the shape of list-style API responses far more than field-level updates do.

#### 6. Cache Storage Layout

```
{APP_STORAGE}/api/cache/
  ├── GetTenantsAPI/
  │   └── a1b2c3d4e5...json        ← cached response (key = hash of method+version+params)
  ├── GetComtypesAPI/
  │   ├── f6g7h8i9j0...json        ← tenant_id=1
  │   └── k1l2m3n4o5...json        ← tenant_id=2
  ├── GetMailingLayoutAPI/
  │   ├── p6q7r8s9t0...json        ← template_id=5, locale=de_DE
  │   └── u1v2w3x4y5...json        ← template_id=5, locale=en_US
  ...
```

Per-method subdirectories allow `invalidateCache()` to delete an entire method's cache with a single `rmdir()` without affecting other methods.

#### 7. CacheControl Integration

Register an `APICacheLocation` (or rename the existing one) that reports the total size of `{APP_STORAGE}/api/cache/` and clears it:

```php
class APIResponseCacheLocation extends BaseCacheLocation
{
    public const string CACHE_ID = 'APIResponseCache';

    public function getID(): string { return self::CACHE_ID; }
    public function getLabel(): string { return t('API Response Cache'); }

    public function getByteSize(): int
    {
        return APICacheManager::getCacheSize();
    }

    public function clear(): void
    {
        APICacheManager::clearAll();
    }
}
```

Register it via a `RegisterCacheLocationsEvent` listener. This makes the API response cache visible and clearable in the admin cache control screen.

#### 8. Usage Example (HCP Editor Method)

```php
namespace Maileditor\Tenants\API\Methods;

use Application\API\Cache\CacheableAPIMethodInterface;
use Application\API\Cache\CacheableAPIMethodTrait;
use Application\API\Cache\Strategies\FixedDurationCacheStrategy;

class GetTenantsAPI extends BaseJSONMethod implements CacheableAPIMethodInterface
{
    use CacheableAPIMethodTrait;

    public function getCacheStrategy(): APICacheStrategyInterface
    {
        return new FixedDurationCacheStrategy(
            FixedDurationCacheStrategy::DURATION_24_HOURS
        );
    }

    public function getCacheKeyParameters(): array
    {
        return array(); // No parameters → single cache entry
    }

    // ... existing collectResponseData() unchanged
}
```

```php
namespace Maileditor\Comtypes\API\Methods;

class GetComtypesAPI extends BaseJSONMethod implements CacheableAPIMethodInterface
{
    use CacheableAPIMethodTrait;

    public function getCacheStrategy(): APICacheStrategyInterface
    {
        return new DBHelperAwareCacheStrategy(
            array(ComtypesCollection::class),
            FixedDurationCacheStrategy::DURATION_6_HOURS
        );
    }

    public function getCacheKeyParameters(): array
    {
        return array('tenant_id', 'tenant_name');
    }

    // ... existing collectResponseData() unchanged
}
```

### Proof-of-Concept Outline

1. Create `Application\API\Cache\` namespace with: `CacheableAPIMethodInterface`, `CacheableAPIMethodTrait`, `APICacheManager` (static utility for folder paths, size calculation, clearing).
2. Create `Application\API\Cache\Strategies\` with: `APICacheStrategyInterface`, `FixedDurationCacheStrategy`, `ManualOnlyCacheStrategy`.
3. Add the two cache check/write blocks to `BaseAPIMethod::_process()`.
4. Create `APIResponseCacheLocation` and its `RegisterCacheLocationsEvent` listener.
5. Convert one simple HCP Editor method (`GetCountriesAPI` — no parameters, fully static data) to implement `CacheableAPIMethodInterface`.
6. Verify: first call computes and caches; second call serves from cache; admin UI shows cache size and allows clearing.
7. Add `DBHelperAwareCacheStrategy` and `APICacheInvalidationManager`.
8. Convert a parameterized method (`GetComtypesAPI`) to use `DBHelperAwareCacheStrategy`.
9. Verify: creating a comtype in the admin UI invalidates the cached response.

## Open Questions

- **Record updates:** The `save()` method on `BaseRecord` does not fire an event. Should we add an `AfterSaveRecordEvent` to `BaseCollection` as a prerequisite, or is the TTL safety net acceptable for v1? The TTL approach means updates are picked up within the TTL window (e.g., 6 hours) rather than immediately.
- **Cache warmup:** Should there be a mechanism to pre-warm caches (e.g., a CLI command or admin action that calls `processReturn()` on all cacheable methods)? This could be useful after a deployment that clears the cache folder.
- **HTTP cache headers:** Should cacheable methods also set `Cache-Control`, `ETag`, or `Last-Modified` HTTP headers? This would enable client-side and CDN caching as a complementary layer. It's orthogonal to server-side response caching but could be added to the trait.
- **Cache key collision safety:** The MD5 hash of method+version+params is extremely unlikely to collide, but should the cache file also store the original key components for verification? This adds a small read overhead but eliminates theoretical collision risk.
- **Connector methods:** The HCP Editor also has 20+ connector methods (Pigeon, Hubspot, etc.) that call external APIs. These already have `Connectors_Request_Cache` for HTTP-level caching. Should they also use the new API response cache, or is one layer sufficient? They serve different purposes: connector cache caches the raw external response; API response cache caches the serialized API output.
- **Maximum cache size:** Should there be a configurable limit on total cache folder size, with LRU eviction? For file-based caching this adds complexity (scanning `filemtime` across all files). Possibly unnecessary if TTLs keep the cache self-pruning.

## References

- `BaseAPIMethod::_process()` — [src/classes/Application/API/BaseMethods/BaseAPIMethod.php](src/classes/Application/API/BaseMethods/BaseAPIMethod.php) (lines 142–179)
- AI cache strategies — [src/classes/Application/AI/Cache/](src/classes/Application/AI/Cache/)
- CacheControl system — [src/classes/Application/CacheControl/](src/classes/Application/CacheControl/)
- DBHelper collection events — [src/classes/DBHelper/BaseCollection/Event/](src/classes/DBHelper/BaseCollection/Event/)
- DBHelper record events — [src/classes/DBHelper/BaseRecord/Event/](src/classes/DBHelper/BaseRecord/Event/)
- HCP Editor API methods — [assets/classes/](assets/classes/) (Maileditor project, 37+ method classes)
- Only existing manual cache: `GetMailForgeAppInfo` — [assets/classes/MailForge/APIConnector/Method/GetMailForgeAppInfo.php](assets/classes/MailForge/APIConnector/Method/GetMailForgeAppInfo.php) (HCP Editor)
- Project brief — [docs/agents/projects/api-caching-system.md](docs/agents/projects/api-caching-system.md)
