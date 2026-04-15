<?php

declare(strict_types=1);

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
    public function getCacheKeyParameters() : array
    {
        $identifier = $this->getUserCacheIdentifier();

        if($identifier === '')
        {
            throw new APICacheException(
                'User-scoped cache method returned an empty user identifier.',
                sprintf('Class [%s] returned an empty identifier from getUserCacheIdentifier().', get_class($this)),
                APICacheException::ERROR_EMPTY_USER_CACHE_IDENTIFIER
            );
        }

        return array('_userScope' => $identifier) + $this->getUserScopedCacheKeyParameters();
    }
}
