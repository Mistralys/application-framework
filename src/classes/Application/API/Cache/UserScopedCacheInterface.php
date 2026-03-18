<?php

declare(strict_types=1);

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
    public function getUserCacheIdentifier() : string;

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
    public function getUserScopedCacheKeyParameters() : array;
}
