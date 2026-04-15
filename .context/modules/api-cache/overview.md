# API Cache - Overview
_SOURCE: API Cache Overview_
# API Cache Overview
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── API/
                └── Cache/
                    └── README.md

```
###  Path: `/src/classes/Application/API/Cache/README.md`

```md
# Submodule: API / Cache

## Purpose

Provides file-based response caching for API methods. API method classes opt in by
implementing `CacheableAPIMethodInterface` and using `CacheableAPIMethodTrait`. The
submodule covers the complete caching pipeline: strategy abstraction (`APICacheStrategyInterface`,
built-in strategy classes), file system management (`APICacheManager`), read/write/invalidate
operations (`CacheableAPIMethodTrait`), and admin UI integration via the CacheControl system
(`APIResponseCacheLocation`).

---

## Classes

| Class | Role |
|---|---|
| `APICacheException` | Exception class for programming errors in cache infrastructure (empty user identifier, invalid method name, corrupt cache file). |
| `APICacheStrategyInterface` | Contract that determines whether a cached file is still valid. |
| `APICacheManager` | Static utility: resolves storage paths, deletes cached files by method, reports cache size. |
| `CacheableAPIMethodInterface` | Interface extending `APIMethodInterface` that API methods implement to opt into caching. |
| `CacheableAPIMethodTrait` | Trait providing default implementations of all caching operations. Consuming classes supply the strategy and key parameters. |
| `UserScopedCacheInterface` | Sub-interface of `CacheableAPIMethodInterface` for API methods that return user-specific data and require per-user cache isolation. Declares `getUserCacheIdentifier()` and `getUserScopedCacheKeyParameters()`. |
| `UserScopedCacheTrait` | Pair trait for `UserScopedCacheInterface`: overrides `getCacheKeyParameters()` to automatically inject the `_userScope` key and hard-fail on an empty user identifier. |
| `APIResponseCacheLocation` | CacheControl integration: exposes the API response cache to the admin cache management UI. |
| `Strategies\FixedDurationStrategy` | Built-in strategy: cache file is valid for a configurable duration in seconds. |
| `Strategies\ManualOnlyStrategy` | Built-in strategy: cached file never expires automatically; invalidation is only triggered manually. |

---

## Storage Layout

Cache files are stored under the application storage directory:

```
{APP_STORAGE}/
  api/
    cache/
      {MethodName}/
        {hash}.json
```

The hash component (`{hash}`) is an MD5 of the concatenated method name, API version, and
sorted cache key parameters.  `APICacheManager` computes the `{MethodName}/` path;
`CacheableAPIMethodTrait::getCacheKey()` computes the filename hash.

---

## Usage: Adding Caching to an API Method

### 1. Implement the interface and use the trait

```php
use Application\API\Cache\APICacheStrategyInterface;
use Application\API\Cache\CacheableAPIMethodInterface;
use Application\API\Cache\CacheableAPIMethodTrait;
use Application\API\Cache\Strategies\FixedDurationStrategy;
use Application\API\BaseMethods\BaseAPIMethod;

class GetProductsAPI extends BaseAPIMethod implements CacheableAPIMethodInterface
{
    use CacheableAPIMethodTrait;

    public function getCacheStrategy() : APICacheStrategyInterface
    {
        return new FixedDurationStrategy(FixedDurationStrategy::DURATION_1_HOUR);
    }

    public function getCacheKeyParameters() : array
    {
        return array(
            'locale' => $this->getLocale(),
            'filter' => $this->getFilter(),
        );
    }

    // ... rest of method implementation
}
```

Caching is then handled **transparently** by `BaseAPIMethod::_process()`. On each request:

1. After `validate()` and `getActiveVersion()`, the base class checks the cache via
   `readFromCache($version)`. On a **cache hit**, `sendSuccessResponse()` is called
   immediately (typed `never`) — `collectRequestData()` and `collectResponseData()` are
   skipped entirely.
2. On a **cache miss**, the normal pipeline runs. After `collectResponseData()` completes,
   the base class writes the assembled response to cache via `writeToCache($version, $response->getData())`
   before sending the final response.

No manual `readFromCache()` or `writeToCache()` calls are required in the implementing class.
`collectResponseData()` is written exactly as it would be for a non-cached method.

### 2. User-scoped caching (per-user cache isolation)

For API methods that return user-specific data, use `UserScopedCacheInterface` and
`UserScopedCacheTrait` instead of the base interface and trait. This two-tier design
makes it structurally impossible to omit the user identity from cache keys — every
cache file is automatically namespaced to the requesting user.

```php
use Application\API\Cache\APICacheStrategyInterface;
use Application\API\Cache\UserScopedCacheInterface;
use Application\API\Cache\UserScopedCacheTrait;
use Application\API\Cache\Strategies\FixedDurationStrategy;
use Application\API\BaseMethods\BaseAPIMethod;

class GetUserOrdersAPI extends BaseAPIMethod implements UserScopedCacheInterface
{
    use UserScopedCacheTrait;

    public function getCacheStrategy() : APICacheStrategyInterface
    {
        return new FixedDurationStrategy(FixedDurationStrategy::DURATION_15_MIN);
    }

    public function getUserCacheIdentifier() : string
    {
        // Return a stable, non-empty string that uniquely identifies
        // the current user context (e.g. pseudo user ID from the API key).
        return $this->getAPIKey()->getUserID();
    }

    public function getUserScopedCacheKeyParameters() : array
    {
        // Return method-specific parameters only — do NOT add a user
        // identifier here; the trait injects _userScope automatically.
        return array(
            'status' => $this->getStatusFilter(),
        );
    }

    // ... rest of method implementation
}
```

The `_userScope` key is injected automatically by `UserScopedCacheTrait::getCacheKeyParameters()`
and always takes precedence over any key returned by `getUserScopedCacheKeyParameters()` (the
array union operator ensures `_userScope` can never be overwritten by the implementing class).

An empty return value from `getUserCacheIdentifier()` is a **hard failure**: `APICacheException`
is thrown with `ERROR_EMPTY_USER_CACHE_IDENTIFIER` immediately — silent fallback is not
acceptable because falling back would collapse all users into a single cache scope.

**Important:** The key name `_userScope` is **reserved**. Do not return it from
`getUserScopedCacheKeyParameters()` — it will have no effect and the entry will be silently
discarded in favour of the trait-injected value.

**`BaseAPIMethod::_process()` requires no changes.** The existing
`instanceof CacheableAPIMethodInterface` check covers `UserScopedCacheInterface` automatically
because the sub-interface extends the base interface.

### 3. Invalidate on data changes (optional)

Call `$method->invalidateCache()` (or `APICacheManager::invalidateMethod($name)` statically)
whenever the underlying data changes and cached responses must be discarded.

---

## Two-Tier Design: Stateless vs. User-Scoped Methods

The caching system provides two composition tiers to cleanly separate stateless and user-specific caching:

| Interface | Trait | Use when |
|---|---|---|
| `CacheableAPIMethodInterface` | `CacheableAPIMethodTrait` | Response is identical for all users (e.g. locale lists, country lists). |
| `UserScopedCacheInterface` | `UserScopedCacheTrait` | Response depends on the identity of the requesting user. |

`UserScopedCacheInterface` extends `CacheableAPIMethodInterface`, so user-scoped methods are
transparent to `BaseAPIMethod::_process()` — no special handling is required beyond the existing
`instanceof CacheableAPIMethodInterface` check.

---

## Cache Key Generation

`CacheableAPIMethodTrait::getCacheKey()` produces a deterministic hash:

1. Fetch `getCacheKeyParameters()` and sort keys with `ksort()`.
2. JSON-encode the sorted array with `json_encode($params, JSON_THROW_ON_ERROR)`.
3. Concatenate: `{methodName}|{version}|{json_params}`.
4. Hash with `md5()`.

**Important:** Parameter values must be scalar (or JSON-serializable) types. Non-encodable
values cause `json_encode()` to throw `JsonException` immediately (due to `JSON_THROW_ON_ERROR`),
rather than silently producing `false`.

---

## Cache Strategies

| Strategy class | ID constant | Validity rule |
|---|---|---|
| `FixedDurationStrategy` | `STRATEGY_ID = 'FixedDuration'` | Valid if `filemtime` is within `$durationInSeconds` of `time()`. If `filemtime()` returns `false` (e.g. race condition during parallel deletion), the file is treated as expired. |
| `ManualOnlyStrategy` | `STRATEGY_ID = 'ManualOnly'` | Always valid; never expires automatically. |

`FixedDurationStrategy` ships with named duration constants for common intervals:
`DURATION_1_MIN`, `DURATION_5_MIN`, `DURATION_15_MIN`, `DURATION_1_HOUR`, `DURATION_6_HOURS`,
`DURATION_12_HOURS`, `DURATION_24_HOURS`.

Each strategy exposes a `STRATEGY_ID` string constant (PascalCase) that can be used
for comparison or logging instead of hard-coding the string value.

Implement `APICacheStrategyInterface` to define custom validity rules.

---

## Error Handling

`readFromCache()` returns `null` on a cache miss, when the strategy deems the file
expired, or when the cache file contains malformed (corrupt) JSON. In the corrupt-file
case, the event is **logged** at error level via the application logger (including the
file path, exception message, and `APICacheException::ERROR_CACHE_FILE_CORRUPT` as the
error code reference), and the file is then **deleted best-effort** before returning
`null`, so the next write will create a fresh entry without operator intervention.

No exception propagates from `readFromCache()` for corrupt cache content — the resilience
behavior is transparent to the calling code. Systematic corruption is visible in the
application logs.

---

## APICacheException

`APICacheException` extends `APIException` and is thrown by cache infrastructure classes
for conditions that indicate a programming error or unexpected state.

| Constant | Value | When thrown |
|---|---|---|
| `ERROR_EMPTY_USER_CACHE_IDENTIFIER` | `59213009` | A user-scoped method returned an empty identifier. |
| `ERROR_INVALID_METHOD_NAME` | `59213010` | `APICacheManager::getMethodCacheFolder()` received an empty or path-traversal-containing method name. Propagates from any method that calls `getMethodCacheFolder()` without a catch — including `invalidateMethod()`. |
| `ERROR_CACHE_FILE_CORRUPT` | `59213011` | Not thrown directly. Used as the error code reference in the log entry emitted by `readFromCache()` when a corrupt cache file is detected; the file is then deleted best-effort. |

`APICacheManager::getMethodCacheFolder()` throws `APICacheException::ERROR_INVALID_METHOD_NAME`
if `$methodName` is empty or contains `/`, `..`, or `DIRECTORY_SEPARATOR`. This guard
prevents path-traversal attacks and ensures a stray empty string cannot inadvertently
clear the entire cache folder. In normal framework usage the method name is always
supplied by `APIMethodInterface::getMethodName()` and this exception should never fire.

---

## Admin UI Integration

`APIResponseCacheLocation` registers the entire API response cache with the CacheControl
system via the `RegisterAPIResponseCacheListener` event. This allows administrators to view
the cache size and clear all cached API responses from the admin interface without writing
custom PHP.

No manual registration is required — the listener is discovered automatically by the
event handler registry.

```
---
**File Statistics**
- **Size**: 11.61 KB
- **Lines**: 274
File: `modules/api-cache/overview.md`
