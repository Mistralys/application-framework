# API Cache - Core Architecture (Public API)
_SOURCE: APICacheException, APICacheStrategyInterface, APICacheManager, CacheableAPIMethodInterface, CacheableAPIMethodTrait, UserScopedCacheInterface, UserScopedCacheTrait, APIResponseCacheLocation_
# APICacheException, APICacheStrategyInterface, APICacheManager, CacheableAPIMethodInterface, CacheableAPIMethodTrait, UserScopedCacheInterface, UserScopedCacheTrait, APIResponseCacheLocation
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── API/
                └── Cache/
                    └── APICacheException.php
                    └── APICacheManager.php
                    └── APICacheStrategyInterface.php
                    └── APIResponseCacheLocation.php
                    └── CacheableAPIMethodInterface.php
                    └── CacheableAPIMethodTrait.php
                    └── Strategies/
                        ├── FixedDurationStrategy.php
                        ├── ManualOnlyStrategy.php
                    └── UserScopedCacheInterface.php
                    └── UserScopedCacheTrait.php

```
###  Path: `/src/classes/Application/API/Cache/APICacheException.php`

```php
namespace Application\API\Cache;

use Application\API\APIException as APIException;

/**
 * Exception class for API cache-related errors.
 *
 * @package API
 * @subpackage Cache
 */
class APICacheException extends APIException
{
	/** Thrown when a user-scoped API method returns an empty cache identifier. */
	public const ERROR_EMPTY_USER_CACHE_IDENTIFIER = 59213009;

	/**
	 * Thrown when an empty or path-traversal-containing method name is passed
	 * to {@see APICacheManager::getMethodCacheFolder()}.
	 */
	public const ERROR_INVALID_METHOD_NAME = 59213010;

	/**
	 * Reserved for logging context when a corrupt cache file is encountered.
	 * Not thrown directly — the resilience path deletes the file and returns null.
	 */
	public const ERROR_CACHE_FILE_CORRUPT = 59213011;
}


```
###  Path: `/src/classes/Application/API/Cache/APICacheManager.php`

```php
namespace Application\API\Cache;

use AppUtils\FileHelper as FileHelper;
use AppUtils\FileHelper\FolderInfo as FolderInfo;
use Application\Application as Application;

/**
 * Static utility class for managing the file system layout of
 * cached API method responses.
 *
 * Storage layout:
 * <pre>
 * {APP_STORAGE}/
 *   api/
 *     cache/
 *       {MethodName}/
 *         {hash}.json
 * </pre>
 */
class APICacheManager
{
	/**
	 * Returns the base cache folder, creating it if it does not exist.
	 *
	 * @return FolderInfo
	 */
	public static function getCacheFolder(): FolderInfo
	{
		/* ... */
	}


	/**
	 * Returns the cache subfolder for a specific API method.
	 * The folder is not created automatically; it is created on the
	 * first write via {@see CacheableAPIMethodTrait::writeToCache()}.
	 *
	 * @param string $methodName Must be a trusted, framework-internal method
	 *                           name (i.e. a value returned by
	 *                           {@see APIMethodInterface::getMethodName()},
	 *                           never user-supplied input). The value is
	 *                           concatenated directly into a filesystem path.
	 * @return FolderInfo
	 * @throws APICacheException {@see APICacheException::ERROR_INVALID_METHOD_NAME}
	 */
	public static function getMethodCacheFolder(string $methodName): FolderInfo
	{
		/* ... */
	}


	/**
	 * Deletes all cached responses for a specific API method.
	 * No-op if the method's cache folder does not exist.
	 *
	 * @param string $methodName
	 * @return void
	 * @throws APICacheException {@see APICacheException::ERROR_INVALID_METHOD_NAME}
	 */
	public static function invalidateMethod(string $methodName): void
	{
		/* ... */
	}


	/**
	 * Deletes all cached API response data.
	 *
	 * @return void
	 */
	public static function clearAll(): void
	{
		/* ... */
	}


	/**
	 * Returns the total byte size of all files in the cache folder.
	 * Returns 0 if the folder does not exist or is empty.
	 *
	 * @return int
	 */
	public static function getCacheSize(): int
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Cache/APICacheStrategyInterface.php`

```php
namespace Application\API\Cache;

use AppUtils\FileHelper\JSONFile as JSONFile;

interface APICacheStrategyInterface
{
	/**
	 * Returns a unique identifier for this strategy (e.g. 'FixedDuration', 'ManualOnly').
	 *
	 * @return string
	 */
	public function getID(): string;


	/**
	 * Given a cache file, returns whether it is still considered valid.
	 *
	 * @param JSONFile $cacheFile
	 * @return bool
	 */
	public function isCacheFileValid(JSONFile $cacheFile): bool;
}


```
###  Path: `/src/classes/Application/API/Cache/APIResponseCacheLocation.php`

```php
namespace Application\API\Cache;

use Application\CacheControl\BaseCacheLocation as BaseCacheLocation;

/**
 * Cache location for API response cache files.
 * Integrates with the CacheControl admin UI to display and clear
 * all cached API responses.
 *
 * @package API
 * @subpackage Cache
 * @see APICacheManager
 */
class APIResponseCacheLocation extends BaseCacheLocation
{
	public const LOCATION_ID = 'APIResponseCache';

	public function getID(): string
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	public function getByteSize(): int
	{
		/* ... */
	}


	public function clear(): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Cache/CacheableAPIMethodInterface.php`

```php
namespace Application\API\Cache;

use Application\API\APIMethodInterface as APIMethodInterface;

/**
 * Interface for API methods that support response caching.
 *
 * Implement this interface and use {@see CacheableAPIMethodTrait} to opt into
 * file-based response caching. The implementing class must define
 * {@see getCacheStrategy()} and {@see getCacheKeyParameters()}.
 *
 * **Security note:** On a cache hit, `BaseAPIMethod::_process()` calls
 * `sendSuccessResponse()` immediately after the cache check, bypassing
 * `collectRequestData()` entirely. Any per-user authorization or
 * row-level access checks placed inside `collectRequestData()` will be
 * skipped for cached responses. Methods that return user-scoped data
 * **must** include user-identifying values (e.g. user ID, role) in
 * {@see getCacheKeyParameters()} to ensure each user receives only their
 * own cached data.
 *
 * @package API
 * @subpackage Cache
 * @see CacheableAPIMethodTrait
 */
interface CacheableAPIMethodInterface extends APIMethodInterface
{
	/**
	 * Returns the caching strategy for this method.
	 *
	 * @return APICacheStrategyInterface
	 */
	public function getCacheStrategy(): APICacheStrategyInterface;


	/**
	 * Returns the parameter values that affect the response and
	 * should be included in the cache key hash. Use an associative
	 * array of parameter name => value for deterministic ordering.
	 *
	 * @return array
	 */
	public function getCacheKeyParameters(): array;


	/**
	 * Builds a deterministic cache key hash from the method name,
	 * version, and the values returned by {@see getCacheKeyParameters()}.
	 *
	 * @param string $version
	 * @return string
	 */
	public function getCacheKey(string $version): string;


	/**
	 * Reads response data from the cache, if available and valid.
	 * Returns null on cache miss or if the cache entry has expired.
	 *
	 * @param string $version
	 * @return array|null
	 */
	public function readFromCache(string $version): ?array;


	/**
	 * Writes response data to the cache file for the given version.
	 *
	 * @param string $version
	 * @param array $data
	 * @return void
	 */
	public function writeToCache(string $version, array $data): void;


	/**
	 * Invalidates all cached entries for this method.
	 *
	 * @return void
	 */
	public function invalidateCache(): void;
}


```
###  Path: `/src/classes/Application/API/Cache/CacheableAPIMethodTrait.php`

```php
namespace Application\API\Cache;

use AppUtils\FileHelper\JSONFile as JSONFile;
use Application\AppFactory as AppFactory;

/**
 * Provides default implementations for {@see CacheableAPIMethodInterface}.
 *
 * Use this trait inside an API method class that also implements
 * {@see CacheableAPIMethodInterface}. The consuming class must define:
 * - {@see CacheableAPIMethodInterface::getCacheStrategy()}
 * - {@see CacheableAPIMethodInterface::getCacheKeyParameters()}
 *
 * @package API
 * @subpackage Cache
 * @see CacheableAPIMethodInterface
 * @phpstan-require-implements CacheableAPIMethodInterface
 */
trait CacheableAPIMethodTrait
{
	/**
	 * Builds a deterministic MD5 hash from the method name, API version,
	 * and the sorted cache key parameters.
	 *
	 * @param string $version
	 * @return string
	 */
	public function getCacheKey(string $version): string
	{
		/* ... */
	}


	/**
	 * Resolves the JSON cache file for the given version.
	 *
	 * @param string $version
	 * @return JSONFile
	 */
	protected function getCacheFile(string $version): JSONFile
	{
		/* ... */
	}


	/**
	 * Reads response data from the cache file for the given version.
	 * Returns null if the cache file does not exist or is no longer valid
	 * according to the configured strategy.
	 *
	 * @param string $version
	 * @return array|null
	 */
	public function readFromCache(string $version): ?array
	{
		/* ... */
	}


	/**
	 * Writes response data to the cache file for the given version.
	 * The parent directory is created automatically if it does not exist.
	 *
	 * @param string $version
	 * @param array $data
	 * @return void
	 */
	public function writeToCache(string $version, array $data): void
	{
		/* ... */
	}


	/**
	 * Invalidates all cached entries for this API method by delegating
	 * to {@see APICacheManager::invalidateMethod()}.
	 *
	 * @return void
	 */
	public function invalidateCache(): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Cache/Strategies/FixedDurationStrategy.php`

```php
namespace Application\API\Cache\Strategies;

use AppUtils\FileHelper\JSONFile as JSONFile;
use Application\API\Cache\APICacheStrategyInterface as APICacheStrategyInterface;

/**
 * Cache strategy that considers a cache file valid as long as its modification
 * time is within the configured duration from the current time.
 *
 * @package API
 * @subpackage Cache
 */
class FixedDurationStrategy implements APICacheStrategyInterface
{
	public const STRATEGY_ID = 'FixedDuration';
	public const DURATION_1MIN = 60;
	public const DURATION_5MIN = 300;
	public const DURATION_15MIN = 900;
	public const DURATION_1HOUR = 3600;
	public const DURATION_6HOURS = 21600;
	public const DURATION_12HOURS = 43200;
	public const DURATION_24HOURS = 86400;

	public function getID(): string
	{
		/* ... */
	}


	/**
	 * Returns true if the cache file's modification time is within
	 * the configured duration from the current time.
	 * Returns false if `filemtime()` returns false (e.g. race condition
	 * during parallel deletion — treated as expired).
	 *
	 * @param JSONFile $cacheFile
	 * @return bool
	 */
	public function isCacheFileValid(JSONFile $cacheFile): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Cache/Strategies/ManualOnlyStrategy.php`

```php
namespace Application\API\Cache\Strategies;

use AppUtils\FileHelper\JSONFile as JSONFile;
use Application\API\Cache\APICacheStrategyInterface as APICacheStrategyInterface;

/**
 * Cache strategy that never expires a cache file automatically.
 * Cache entries are only invalidated through explicit calls to
 * {@see CacheableAPIMethodTrait::invalidateCache()}.
 *
 * @package API
 * @subpackage Cache
 */
class ManualOnlyStrategy implements APICacheStrategyInterface
{
	public const STRATEGY_ID = 'ManualOnly';

	public function getID(): string
	{
		/* ... */
	}


	public function isCacheFileValid(JSONFile $cacheFile): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Cache/UserScopedCacheInterface.php`

```php
namespace Application\API\Cache;

/**
 * Interface for API methods that return user-specific data and require
 * per-user cache isolation.
 *
 * Implement this interface and use {@see UserScopedCacheTrait} to enforce
 * that every cache key contains a user-identifying component. The trait
 * overrides {@see CacheableAPIMethodInterface::getCacheKeyParameters()} and
 * automatically injects the `_userScope` key, preventing one user's cached
 * response from being served to another.
 *
 * **Design note:** Not all cacheable methods are user-scoped. Stateless
 * methods (e.g. `GetAppLocalesAPI`) implement {@see CacheableAPIMethodInterface}
 * directly. This subinterface exists only for methods whose responses differ
 * per user.
 *
 * @package API
 * @subpackage Cache
 * @see UserScopedCacheTrait
 * @see CacheableAPIMethodInterface
 */
interface UserScopedCacheInterface extends CacheableAPIMethodInterface
{
	/**
	 * Returns a unique, non-empty identifier for the current user context
	 * (e.g. a pseudo user ID derived from the API key).
	 *
	 * Must never return an empty string — {@see UserScopedCacheTrait} will
	 * throw {@see APICacheException} with
	 * {@see APICacheException::ERROR_EMPTY_USER_CACHE_IDENTIFIER} if it does.
	 *
	 * @return string
	 */
	public function getUserCacheIdentifier(): string;


	/**
	 * Returns the method-specific cache key parameters, excluding user
	 * identification. The {@see UserScopedCacheTrait} automatically merges
	 * the `_userScope` key (populated from {@see getUserCacheIdentifier()})
	 * into the final parameters returned by
	 * {@see CacheableAPIMethodInterface::getCacheKeyParameters()}.
	 *
	 * Note: the key `_userScope` is reserved by the trait. Returning a
	 * `_userScope` entry here has no effect — the trait's injected value
	 * always takes precedence.
	 *
	 * @return array
	 */
	public function getUserScopedCacheKeyParameters(): array;
}


```
###  Path: `/src/classes/Application/API/Cache/UserScopedCacheTrait.php`

```php
namespace Application\API\Cache;

/**
 * Provides user-scoped cache key enforcement for {@see UserScopedCacheInterface}.
 *
 * Use this trait inside an API method class that also implements
 * {@see UserScopedCacheInterface}. It overrides
 * {@see CacheableAPIMethodInterface::getCacheKeyParameters()} to automatically
 * inject a `_userScope` key containing the value returned by
 * {@see UserScopedCacheInterface::getUserCacheIdentifier()}, merged with the
 * method-specific parameters from
 * {@see UserScopedCacheInterface::getUserScopedCacheKeyParameters()}.
 *
 * An empty user identifier is treated as a hard failure: an
 * {@see APICacheException} is thrown with
 * {@see APICacheException::ERROR_EMPTY_USER_CACHE_IDENTIFIER}. Silent
 * fallback is not acceptable — a user-scoped method must always supply a
 * non-empty identifier.
 *
 * @package API
 * @subpackage Cache
 * @see UserScopedCacheInterface
 * @see CacheableAPIMethodTrait
 * @phpstan-require-implements UserScopedCacheInterface
 */
trait UserScopedCacheTrait
{
	use CacheableAPIMethodTrait;

	/**
	 * Builds the cache key parameters for this user-scoped method.
	 *
	 * Calls {@see UserScopedCacheInterface::getUserCacheIdentifier()} and
	 * validates that the returned value is non-empty. Merges the
	 * `_userScope` key with the parameters returned by
	 * {@see UserScopedCacheInterface::getUserScopedCacheKeyParameters()}.
	 *
	 * @return array
	 * @throws APICacheException If the user identifier is empty.
	 */
	public function getCacheKeyParameters(): array
	{
		/* ... */
	}
}


```