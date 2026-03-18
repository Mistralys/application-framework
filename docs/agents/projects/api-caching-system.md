# Project: API Response Caching System

## Goal

Add file-based response caching to the framework's API method layer. Methods opt in via an interface + trait pair. Cached responses bypass the expensive `collectRequestData()` / `collectResponseData()` pipeline. Three invalidation strategies are supported: fixed-duration TTL, manual-only, and DBHelper-collection-aware automatic invalidation.

## Research

See [/docs/agents/research/2026-03-13-api-caching-system.md](/docs/agents/research/2026-03-13-api-caching-system.md) for the full research report.

---

## Architecture Overview

```
BaseAPIMethod::_process()
  │
  ├─ validate()                          ← runs always (needed for cache key)
  ├─ getActiveVersion()                  ← runs always (needed for cache key)
  │
  ├─ ★ CACHE CHECK (new)                ← if CacheableAPIMethodInterface: read cache
  │     └─ HIT → sendSuccessResponse()  ← short-circuits, skips everything below
  │
  ├─ collectRequestData()               ← only on MISS
  ├─ collectResponseData()              ← only on MISS
  │
  ├─ ★ CACHE WRITE (new)               ← if CacheableAPIMethodInterface: write cache
  │
  └─ sendSuccessResponse()
```

### Design Pattern

Follows the framework's existing **Interface + Trait composition** model, identical to `DryRunAPIInterface`/`DryRunAPITrait` and `JSONResponseInterface`/`JSONResponseTrait`.

---

## Implementation Status

| Step | File | WP | Status |
|---|---|---|---|
| 1 | `APICacheStrategyInterface` | WP-001 | ✅ Done |
| 2 | `FixedDurationStrategy` | WP-001 | ✅ Done |
| 3 | `ManualOnlyStrategy` | WP-001 | ✅ Done |
| 4 | `APICacheManager` | WP-002 | ✅ Done |
| 5 | `CacheableAPIMethodInterface` | WP-003 | ✅ Done |
| 6 | `CacheableAPIMethodTrait` | WP-003 | ✅ Done |
| 7 | `DBHelperAwareStrategy` | Phase 2 | ⬜ Pending |
| 8 | Modify `BaseAPIMethod::_process()` | WP-004 | ✅ Done |
| 9 | `APIResponseCacheLocation` | WP-005 | ✅ Done |
| 10 | `RegisterAPIResponseCacheListener` | WP-005 | ✅ Done |

---

## New Files to Create

All new files belong to the framework project.

### 1. Cache Strategy Interface

**File:** `src/classes/Application/API/Cache/APICacheStrategyInterface.php`  
**Namespace:** `Application\API\Cache`

```php
<?php

declare(strict_types=1);

namespace Application\API\Cache;

use AppUtils\FileHelper\JSONFile;

interface APICacheStrategyInterface
{
    /**
     * Returns a unique identifier for this strategy (e.g. 'FixedDuration', 'ManualOnly').
     *
     * @return string
     */
    public function getID() : string;

    /**
     * Given a cache file, returns whether it is still considered valid.
     *
     * @param JSONFile $cacheFile
     * @return bool
     */
    public function isCacheFileValid(JSONFile $cacheFile) : bool;
}
```

### 2. Fixed Duration Strategy

**File:** `src/classes/Application/API/Cache/Strategies/FixedDurationStrategy.php`  
**Namespace:** `Application\API\Cache\Strategies`

Modeled after the existing `Application\AI\Cache\Strategies\FixedDurationStrategy`.

```php
<?php

declare(strict_types=1);

namespace Application\API\Cache\Strategies;

use Application\API\Cache\APICacheStrategyInterface;
use AppUtils\FileHelper\JSONFile;

class FixedDurationStrategy implements APICacheStrategyInterface
{
    public const int DURATION_1MIN = 60;
    public const int DURATION_5MIN = 300;
    public const int DURATION_15MIN = 900;
    public const int DURATION_1HOUR = 3600;
    public const int DURATION_6HOURS = 21600;
    public const int DURATION_12HOURS = 43200;
    public const int DURATION_24HOURS = 86400;

    private int $durationInSeconds;

    public function __construct(int $durationInSeconds = self::DURATION_1HOUR)
    {
        $this->durationInSeconds = $durationInSeconds;
    }

    public function getID() : string
    {
        return 'FixedDuration';
    }

    public function isCacheFileValid(JSONFile $cacheFile) : bool
    {
        return (time() - filemtime($cacheFile->getPath())) < $this->durationInSeconds;
    }
}
```

### 3. Manual-Only Strategy

**File:** `src/classes/Application/API/Cache/Strategies/ManualOnlyStrategy.php`  
**Namespace:** `Application\API\Cache\Strategies`

```php
<?php

declare(strict_types=1);

namespace Application\API\Cache\Strategies;

use Application\API\Cache\APICacheStrategyInterface;
use AppUtils\FileHelper\JSONFile;

class ManualOnlyStrategy implements APICacheStrategyInterface
{
    public function getID() : string
    {
        return 'ManualOnly';
    }

    public function isCacheFileValid(JSONFile $cacheFile) : bool
    {
        return true;
    }
}
```

### 4. DBHelper-Aware Strategy

**File:** `src/classes/Application/API/Cache/Strategies/DBHelperAwareStrategy.php`  
**Namespace:** `Application\API\Cache\Strategies`

Extends `FixedDurationStrategy` and additionally declares which DBHelper collection classes the method depends on. When those collections fire create/delete events, the cache for the method is invalidated.

The TTL from the parent class acts as a **safety net** for record updates, since `BaseRecord::save()` does not fire a collection-level event.

```php
<?php

declare(strict_types=1);

namespace Application\API\Cache\Strategies;

class DBHelperAwareStrategy extends FixedDurationStrategy
{
    public const string STRATEGY_ID = 'dbhelper_aware';

    /**
     * @var class-string<\DBHelper_BaseCollection>[]
     */
    private array $collectionClasses;

    /**
     * @param class-string<\DBHelper_BaseCollection>[] $collectionClasses
     * @param int $durationInSeconds TTL safety net for record updates.
     */
    public function __construct(array $collectionClasses, int $durationInSeconds = self::DURATION_24HOURS)
    {
        parent::__construct($durationInSeconds);
        $this->collectionClasses = $collectionClasses;
    }

    public function getID(): string
    {
        return self::STRATEGY_ID;
    }

    /**
     * @return class-string<\DBHelper_BaseCollection>[]
     */
    public function getCollectionClasses(): array
    {
        return $this->collectionClasses;
    }
}
```

### 5. Cacheable API Method Interface

**File:** `src/classes/Application/API/Cache/CacheableAPIMethodInterface.php`  
**Namespace:** `Application\API\Cache`

```php
<?php

declare(strict_types=1);

namespace Application\API\Cache;

use Application\API\APIMethodInterface;

/**
 * Interface for API methods that support response caching.
 *
 * Use the trait {@see CacheableAPIMethodTrait} to implement this interface.
 *
 * @package API
 * @subpackage Cache
 * @see CacheableAPIMethodTrait
 */
interface CacheableAPIMethodInterface extends APIMethodInterface
{
    /**
     * Returns the cache strategy for this method.
     */
    public function getCacheStrategy(): APICacheStrategyInterface;

    /**
     * Returns the parameter names whose values contribute to the cache key.
     *
     * The cache key is built from: method name + version + sorted parameter values.
     * Return an empty array if the method has no parameters or if parameters
     * don't affect the response.
     *
     * @return string[]
     */
    public function getCacheKeyParameters(): array;

    /**
     * Generates the cache key hash for the given API version.
     */
    public function getCacheKey(string $version): string;

    /**
     * Reads cached response data if still valid.
     *
     * @return array<int|string,mixed>|null Cached data or null on miss.
     */
    public function readFromCache(string $version): ?array;

    /**
     * Writes response data to the cache.
     *
     * @param string $version
     * @param array<int|string,mixed> $data
     */
    public function writeToCache(string $version, array $data): void;

    /**
     * Deletes all cached entries for this method (all parameter combinations).
     */
    public function invalidateCache(): void;
}
```

### 6. Cacheable API Method Trait

**File:** `src/classes/Application/API/Cache/CacheableAPIMethodTrait.php`  
**Namespace:** `Application\API\Cache`

Provides the full default implementation. Methods using this trait only define `getCacheStrategy()` and `getCacheKeyParameters()`.

```php
<?php

declare(strict_types=1);

namespace Application\API\Cache;

use Application\AppFactory;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\FileHelper\JSONFile;

/**
 * Default implementation for {@see CacheableAPIMethodInterface}.
 *
 * Requires the implementing class to also extend {@see BaseAPIMethod}
 * (provides {@see self::getMethodName()} and {@see self::getParam()}).
 *
 * @package API
 * @subpackage Cache
 * @see CacheableAPIMethodInterface
 */
trait CacheableAPIMethodTrait
{
    public function getCacheKey(string $version): string
    {
        $parts = array($this->getMethodName(), $version);

        foreach ($this->getCacheKeyParameters() as $paramName) {
            $value = $this->getParam($paramName, '');
            if (is_array($value)) {
                $parts[] = $paramName . '=' . md5(serialize($value));
            } else {
                $parts[] = $paramName . '=' . (string)$value;
            }
        }

        return md5(implode('|', $parts));
    }

    public function getCacheFile(string $version): JSONFile
    {
        return JSONFile::factory(
            APICacheManager::getCacheFolder()
            . '/' . $this->getMethodName()
            . '/' . $this->getCacheKey($version) . '.json'
        );
    }

    public function readFromCache(string $version): ?array
    {
        $cacheFile = $this->getCacheFile($version);

        if (!$cacheFile->exists()) {
            return null;
        }

        if (!$this->getCacheStrategy()->isCacheFileValid($cacheFile)) {
            return null;
        }

        try
        {
            return $cacheFile->parse();
        }
        catch(\Throwable $e)
        {
            // Cache file is corrupt — log the event for operator observability, then
            // delete the file best-effort and signal a cache miss.
            AppFactory::createLogger()->logError(
                sprintf(
                    'Corrupt API cache file detected and deleted (error code %d). Path: %s | Error: %s',
                    APICacheException::ERROR_CACHE_FILE_CORRUPT,
                    $cacheFile->getPath(),
                    $e->getMessage()
                )
            );
            try { $cacheFile->delete(); } catch(\Throwable $ignored) {}
            return null;
        }
    }

    public function writeToCache(string $version, array $data): void
    {
        $this->getCacheFile($version)->putData($data);
    }

    public function invalidateCache(): void
    {
        APICacheManager::invalidateMethod($this->getMethodName());
    }
}
```

### 7. API Cache Manager

**File:** `src/classes/Application/API/Cache/APICacheManager.php`  
**Namespace:** `Application\API\Cache`

Static utility class for cache folder management, size calculation, per-method invalidation, and global clearing.

```php
<?php

declare(strict_types=1);

namespace Application\API\Cache;

use Application\Application;
use AppUtils\FileHelper;
use AppUtils\FileHelper\FolderInfo;

class APICacheManager
{
    private const CACHE_SUBFOLDER = 'api/cache';

    /**
     * Returns the base cache folder, creating it if it does not exist.
     *
     * @return FolderInfo
     */
    public static function getCacheFolder() : FolderInfo
    {
        return FolderInfo::factory(Application::getStorageSubfolderPath(self::CACHE_SUBFOLDER));
    }

    /**
     * Returns the cache subfolder for a specific API method.
     * The folder is not created automatically.
     *
     * @param string $methodName
     * @return FolderInfo
     */
    public static function getMethodCacheFolder(string $methodName) : FolderInfo
    {
        return FolderInfo::factory(
            Application::getStorageSubfolderPath(self::CACHE_SUBFOLDER) . '/' . $methodName
        );
    }

    /**
     * Deletes all cached responses for a specific API method.
     * No-op if the method's cache folder does not exist.
     *
     * @param string $methodName
     * @return void
     * @throws APICacheException {@see APICacheException::ERROR_INVALID_METHOD_NAME}
     */
    public static function invalidateMethod(string $methodName) : void
    {
        $folder = self::getMethodCacheFolder($methodName);

        if($folder->exists())
        {
            FileHelper::deleteTree($folder);
        }
    }

    /**
     * Deletes all cached API response data.
     *
     * @return void
     */
    public static function clearAll() : void
    {
        $folder = self::getCacheFolder();

        if($folder->exists())
        {
            FileHelper::deleteTree($folder);
        }
    }

    /**
     * Returns the total byte size of all files in the cache folder.
     * Returns 0 if the folder does not exist or is empty.
     *
     * @return int
     */
    public static function getCacheSize() : int
    {
        $folder = self::getCacheFolder();

        if($folder->exists())
        {
            return $folder->getSize();
        }

        return 0;
    }
}
```

### 8. Cache Location (CacheControl Integration)

**File:** `src/classes/Application/API/Cache/APIResponseCacheLocation.php`  
**Namespace:** `Application\API\Cache`

```php
<?php

declare(strict_types=1);

namespace Application\API\Cache;

use Application\CacheControl\BaseCacheLocation;

class APIResponseCacheLocation extends BaseCacheLocation
{
    public const string CACHE_ID = 'APIResponseCache';

    public function getID(): string
    {
        return self::CACHE_ID;
    }

    public function getLabel(): string
    {
        return t('API Response Cache');
    }

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

### 9. Cache Location Registration Listener

**File:** `src/classes/Application/API/Events/RegisterAPIResponseCacheListener.php`  
**Namespace:** `Application\API\Events`

Follows the same pattern as the existing `RegisterAPIIndexCacheListener` in the same folder.

```php
<?php

declare(strict_types=1);

namespace Application\API\Events;

use Application\API\Cache\APIResponseCacheLocation;
use Application\CacheControl\Events\BaseRegisterCacheLocationsListener;

class RegisterAPIResponseCacheListener extends BaseRegisterCacheLocationsListener
{
    protected function getCacheLocations(): array
    {
        return array(new APIResponseCacheLocation());
    }
}
```

---

## Existing File to Modify

### `BaseAPIMethod::_process()` — Add Cache Check and Cache Write

**File:** `src/classes/Application/API/BaseMethods/BaseAPIMethod.php`

The `_process()` method currently looks like this (lines 142–179):

```php
private function _process(): void
{
    $this->time = Microtime::createNow();

    $this->validate();

    $version = $this->getActiveVersion();

    try {
        $this->collectRequestData($version);
    } catch (Throwable $e) {
        if($e instanceof APIResponseDataException) {
            throw $e;
        }

        $this->errorResponse(APIMethodInterface::ERROR_REQUEST_DATA_EXCEPTION)
            ->makeInternalServerError()
            ->setErrorMessage('Failed collecting request data: %s', $e->getMessage())
            ->send();
    }

    $response = ArrayDataCollection::create();

    try {
        $this->collectResponseData($response, $version);
    } catch (Throwable $e) {
        if($e instanceof APIResponseDataException) {
            throw $e;
        }

        $this->errorResponse(APIMethodInterface::ERROR_RESPONSE_DATA_EXCEPTION)
            ->makeInternalServerError()
            ->setErrorMessage('Failed collecting response data: %s', $e->getMessage())
            ->send();
    }

    $this->sendSuccessResponse($response);
}
```

**Target state** after modification:

```php
private function _process(): void
{
    $this->time = Microtime::createNow();

    $this->validate();

    $version = $this->getActiveVersion();

    // Serve from cache if available.
    if ($this instanceof CacheableAPIMethodInterface) {
        $cached = $this->readFromCache($version);
        if ($cached !== null) {
            $this->sendSuccessResponse(ArrayDataCollection::create($cached));
        }
    }

    try {
        $this->collectRequestData($version);
    } catch (Throwable $e) {
        if($e instanceof APIResponseDataException) {
            throw $e;
        }

        $this->errorResponse(APIMethodInterface::ERROR_REQUEST_DATA_EXCEPTION)
            ->makeInternalServerError()
            ->setErrorMessage('Failed collecting request data: %s', $e->getMessage())
            ->send();
    }

    $response = ArrayDataCollection::create();

    try {
        $this->collectResponseData($response, $version);
    } catch (Throwable $e) {
        if($e instanceof APIResponseDataException) {
            throw $e;
        }

        $this->errorResponse(APIMethodInterface::ERROR_RESPONSE_DATA_EXCEPTION)
            ->makeInternalServerError()
            ->setErrorMessage('Failed collecting response data: %s', $e->getMessage())
            ->send();
    }

    // Write response to cache on miss.
    if ($this instanceof CacheableAPIMethodInterface) {
        $this->writeToCache($version, $response->getData());
    }

    $this->sendSuccessResponse($response);
}
```

**Changes:**
1. Add `use Application\API\Cache\CacheableAPIMethodInterface;` to the imports.
2. Insert the cache check block (5 lines) after `getActiveVersion()`.
3. Insert the cache write block (3 lines) before the final `sendSuccessResponse()`.

**Why this position works:**
- `validate()` runs before the cache check because parameter values are needed for the cache key (via `getParam()` in the trait's `getCacheKey()`).
- `sendSuccessResponse()` is typed `never` — on a cache hit, execution stops there. On cache miss, it falls through to the existing pipeline.
- `processReturn()` (test mode) works identically: `sendSuccessResponse()` throws `APIResponseDataException` in return mode, which `processReturn()` catches — no change needed.

---

## DBHelper Automatic Invalidation (Phase 2)

This adds a manager that wires collection events to cache invalidation automatically.

### Invalidation Manager

**File:** `src/classes/Application/API/Cache/APICacheInvalidationManager.php`  
**Namespace:** `Application\API\Cache`

```php
<?php

declare(strict_types=1);

namespace Application\API\Cache;

use Application\API\APIManager;
use Application\API\Cache\Strategies\DBHelperAwareStrategy;

class APICacheInvalidationManager
{
    /**
     * Registers AfterCreateRecord and AfterDeleteRecord listeners on all
     * DBHelper collections referenced by cacheable API methods using the
     * DBHelperAwareStrategy.
     *
     * Call once during application boot (e.g., from a startup listener).
     */
    public static function registerListeners(): void
    {
        $bindings = self::collectBindings();

        foreach ($bindings as $collectionClass => $methodNames) {
            $collection = new $collectionClass();

            $invalidator = static function () use ($methodNames): void {
                foreach ($methodNames as $methodName) {
                    APICacheManager::invalidateMethod($methodName);
                }
            };

            $collection->onAfterCreateRecord($invalidator);
            $collection->onAfterDeleteRecord($invalidator);
        }
    }

    /**
     * Scans all API methods for CacheableAPIMethodInterface implementations
     * using DBHelperAwareStrategy and builds a map:
     *   collection class → [method names that depend on it]
     *
     * @return array<class-string<\DBHelper_BaseCollection>, string[]>
     */
    private static function collectBindings(): array
    {
        $bindings = array();
        $api = APIManager::getInstance();

        foreach ($api->getMethodCollection()->getAll() as $method) {
            if (!$method instanceof CacheableAPIMethodInterface) {
                continue;
            }

            $strategy = $method->getCacheStrategy();

            if (!$strategy instanceof DBHelperAwareStrategy) {
                continue;
            }

            foreach ($strategy->getCollectionClasses() as $collectionClass) {
                if (!isset($bindings[$collectionClass])) {
                    $bindings[$collectionClass] = array();
                }
                $bindings[$collectionClass][] = $method->getMethodName();
            }
        }

        return $bindings;
    }
}
```

### How to Wire the Invalidation Manager

The `registerListeners()` call should be placed in a framework startup hook or called from the application's bootstrap. Options:

- **Option A:** Create an offline event listener for a `BootCompleted` or similar event.
- **Option B:** Call `APICacheInvalidationManager::registerListeners()` from `APIManager::process()` on first call (lazy).
- **Option C:** Let each application call it explicitly from its bootstrap.

**Recommendation:** Option B (lazy in `APIManager::process()`) keeps it automatic and requires no application-level changes.

---

## Storage Layout

```
{APP_STORAGE}/api/cache/             ← APICacheManager::getCacheFolder()
  ├── GetTenantsAPI/                 ← one folder per method name
  │   └── a1b2c3d4e5f6...json       ← hash of method+version+params
  ├── GetComtypesAPI/
  │   ├── f6g7h8i9j0k1...json       ← tenant_id=1
  │   └── l2m3n4o5p6q7...json       ← tenant_id=2
  └── GetMailingLayoutAPI/
      ├── r8s9t0u1v2w3...json       ← template_id=5, locale=de_DE
      └── x4y5z6a7b8c9...json       ← template_id=5, locale=en_US
```

Per-method subdirectories allow `invalidateMethod()` to delete one method's entire cache (all parameter combinations) with a single folder delete, without affecting other methods.

---

## New File Summary

| # | File | Namespace | Type |
|---|---|---|---|
| 1 | `src/classes/Application/API/Cache/APICacheStrategyInterface.php` | `Application\API\Cache` | Interface |
| 2 | `src/classes/Application/API/Cache/Strategies/FixedDurationStrategy.php` | `Application\API\Cache\Strategies` | Class |
| 3 | `src/classes/Application/API/Cache/Strategies/ManualOnlyStrategy.php` | `Application\API\Cache\Strategies` | Class |
| 4 | `src/classes/Application/API/Cache/Strategies/DBHelperAwareStrategy.php` | `Application\API\Cache\Strategies` | Class |
| 5 | `src/classes/Application/API/Cache/CacheableAPIMethodInterface.php` | `Application\API\Cache` | Interface |
| 6 | `src/classes/Application/API/Cache/CacheableAPIMethodTrait.php` | `Application\API\Cache` | Trait |
| 7 | `src/classes/Application/API/Cache/APICacheManager.php` | `Application\API\Cache` | Class (static) |
| 8 | `src/classes/Application/API/Cache/APIResponseCacheLocation.php` | `Application\API\Cache` | Class |
| 9 | `src/classes/Application/API/Events/RegisterAPIResponseCacheListener.php` | `Application\API\Events` | Listener |
| 10 | `src/classes/Application/API/Cache/APICacheInvalidationManager.php` | `Application\API\Cache` | Class (Phase 2) |

**Modified file:**

| File | Change |
|---|---|
| `src/classes/Application/API/BaseMethods/BaseAPIMethod.php` | Add cache check + cache write in `_process()`, add import |

---

## Coding Conventions Checklist

Per the framework's `constraints.md`:

- [ ] `declare(strict_types=1)` in every new file.
- [ ] `array()` syntax for all array creation — never `[]`.
- [ ] No PHP enums, no `readonly` properties.
- [ ] camelCase for methods/properties, PascalCase for classes, UPPER_SNAKE_CASE for constants.
- [ ] `ClassHelper::requireObjectInstanceOf()` for type assertions.
- [ ] `FolderInfo::factory()` / `JSONFile::factory()` for file I/O — never raw `file_get_contents`.
- [ ] `t()` for user-facing strings (labels).
- [ ] Run `composer dump-autoload` after creating new files (classmap autoloading).

---

## Testing Plan

### Unit Tests for Cache Infrastructure

| Test | Validates |
|---|---|
| `FixedDurationStrategy::isCacheFileValid()` returns `true` for fresh file | TTL logic |
| `FixedDurationStrategy::isCacheFileValid()` returns `false` for expired file | TTL expiry |
| `ManualOnlyStrategy::isCacheFileValid()` always returns `true` | Manual-only behavior |
| `CacheableAPIMethodTrait::getCacheKey()` is deterministic | Same inputs → same hash |
| `CacheableAPIMethodTrait::getCacheKey()` varies by version | Key includes version |
| `CacheableAPIMethodTrait::getCacheKey()` varies by parameters | Key includes param values |
| `APICacheManager::invalidateMethod()` deletes only the target method folder | Per-method isolation |
| `APICacheManager::clearAll()` deletes all method folders | Global clear |

### Integration Tests Using `processReturn()`

| Test | Validates |
|---|---|
| Call `processReturn()` twice — second returns same data and is served from cache file | Cache write + read |
| Call `processReturn()`, then `invalidateCache()`, then `processReturn()` — second computes fresh | Invalidation works |
| Cache file is written to expected path under `{APP_STORAGE}/api/cache/{MethodName}/` | Storage layout |
| `APIResponseCacheLocation::getByteSize()` returns >0 after caching | CacheControl integration |
| `APIResponseCacheLocation::clear()` removes all cached responses | CacheControl clear |

### Test Scope

Use `composer test-file` for individual test files and `composer test-filter` for pattern matching. **Never run the full test suite.**

---

## Implementation Phases

### Phase 1: Core Caching (Minimum Viable)

1. Create the `APICacheStrategyInterface`.
2. Create `FixedDurationStrategy` and `ManualOnlyStrategy`.
3. Create `CacheableAPIMethodInterface` and `CacheableAPIMethodTrait`.
4. Create `APICacheManager`.
5. Modify `BaseAPIMethod::_process()` (add cache check + write).
6. Create `APIResponseCacheLocation` and `RegisterAPIResponseCacheListener`.
7. Run `composer dump-autoload`.
8. Write unit tests for strategies and cache key generation.
9. Write integration test using a test API method that implements `CacheableAPIMethodInterface`.

### Phase 2: DBHelper Invalidation

1. Create `DBHelperAwareStrategy`.
2. Create `APICacheInvalidationManager`.
3. Wire `registerListeners()` into the application boot sequence.
4. Write integration tests that verify create/delete on a collection triggers cache invalidation.

### Phase 3: Adopt in HCP Editor (Separate Project)

After the framework work is complete, convert HCP Editor API methods to use caching. Good candidates in priority order:

| Method | Strategy | Key Parameters | Rationale |
|---|---|---|---|
| `GetCountriesAPI` | `ManualOnlyStrategy` | none | Static data, never changes at runtime |
| `GetMailingOutputFormatsAPI` | `ManualOnlyStrategy` | none | Static enumeration |
| `GetMailingLayoutAreaRolesAPI` | `ManualOnlyStrategy` | none | Static enumeration |
| `GetGlobalContentStatesAPI` | `ManualOnlyStrategy` | none | Static enumeration |
| `GetMailServersAPI` | `ManualOnlyStrategy` | none | Static configuration |
| `GetTenantsAPI` | `FixedDuration(24h)` | none | Rarely changes |
| `GetComtypesAPI` | `DBHelperAware([ComtypesCollection], 6h)` | `tenant_id`, `tenant_name` | Changes on comtype CRUD |
| `GetBusinessAreasAPI` | `DBHelperAware([BusinessAreasCollection], 6h)` | `mail_server` | Changes on area CRUD |
| `GetMailingLayoutsAPI` | `FixedDuration(1h)` | none | Template list changes occasionally |
| `GetMailingLayoutAPI` | `FixedDuration(15min)` | `template_id`, `locale` | Template details change on editing |
| `GetComgroupsAPI` | `DBHelperAware([ComGroupsCollection], 6h)` | none | Changes on group CRUD |
| `GetVariableSourcesAPI` | `FixedDuration(1h)` | none | Variable sources change infrequently |

**Not cacheable** (mutations): `CreateMailAPI`, `CreateMailAudienceAPI`, and any POST/PUT/DELETE methods.  
**Not recommended** (unbounded key space): `GetMailExportBatchAPI` (accepts up to 250 mail IDs).

---

## Reference: Existing Patterns Used as Models

| Pattern | Example in Codebase | Location |
|---|---|---|
| Interface + Trait for API methods | `DryRunAPIInterface` / `DryRunAPITrait` | `src/classes/Application/API/Traits/` |
| `instanceof` check in `_process()` | _New_ (but follows the same structural pattern as checking for `APIKeyMethodInterface` in `initReservedParams()`) | `BaseAPIMethod.php` |
| Cache strategy with `filemtime()` | `Application\AI\Cache\Strategies\FixedDurationStrategy` | `src/classes/Application/AI/Cache/Strategies/` |
| Cache location for CacheControl | `APICacheLocation` (API method index) | `src/classes/Application/AppFactory/APICacheLocation.php` |
| Cache location listener | `RegisterAPIIndexCacheListener` | `src/classes/Application/API/Events/` |
| JSONFile for cache storage | `APIMethodIndex::getDataFile()` | `src/classes/Application/API/Collection/APIMethodIndex.php` |
| FolderInfo for folder ops | `BaseAICacheStrategy::getCacheFolder()` | `src/classes/Application/AI/Cache/BaseAICacheStrategy.php` |
| Collection events | `AfterCreateRecordEvent` / `AfterDeleteRecordEvent` | `src/classes/DBHelper/BaseCollection/Event/` |
