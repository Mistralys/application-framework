<?php

declare(strict_types=1);

namespace Application\API\Cache;

use Application\API\APIMethodInterface;

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
    public function getCacheStrategy() : APICacheStrategyInterface;

    /**
     * Returns the parameter values that affect the response and
     * should be included in the cache key hash. Use an associative
     * array of parameter name => value for deterministic ordering.
     *
     * @return array
     */
    public function getCacheKeyParameters() : array;

    /**
     * Builds a deterministic cache key hash from the method name,
     * version, and the values returned by {@see getCacheKeyParameters()}.
     *
     * @param string $version
     * @return string
     */
    public function getCacheKey(string $version) : string;

    /**
     * Reads response data from the cache, if available and valid.
     * Returns null on cache miss or if the cache entry has expired.
     *
     * @param string $version
     * @return array|null
     */
    public function readFromCache(string $version) : ?array;

    /**
     * Writes response data to the cache file for the given version.
     *
     * @param string $version
     * @param array $data
     * @return void
     */
    public function writeToCache(string $version, array $data) : void;

    /**
     * Invalidates all cached entries for this method.
     *
     * @return void
     */
    public function invalidateCache() : void;
}
