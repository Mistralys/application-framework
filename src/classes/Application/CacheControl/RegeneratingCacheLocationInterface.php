<?php
/**
 * @package Application
 * @subpackage CacheControl
 */

declare(strict_types=1);

namespace Application\CacheControl;

/**
 * Interface for cache locations whose artefact is required at
 * runtime and must therefore never be left absent by a cache
 * clear operation: instead of being deleted, it is regenerated
 * in place.
 *
 * USAGE: Implement this interface in addition to {@see CacheLocationInterface}
 * (or extend {@see BaseCacheLocation}) to opt a location into the
 * "regenerate in place" behavior in {@see CacheManager::clearLocation()},
 * {@see CacheManager::clearLocations()} and {@see CacheManager::clearAll()}.
 * Implementing locations are never cleared via {@see CacheLocationInterface::clear()}
 * by the cache manager; {@see self::regenerate()} is called instead.
 *
 * @package Application
 * @subpackage CacheControl
 */
interface RegeneratingCacheLocationInterface extends CacheLocationInterface
{
    /**
     * Rebuilds the cache artefact in place.
     *
     * Implementations must never leave the artefact absent:
     * on failure, the previously existing artefact must be
     * left untouched, and an exception must be thrown rather
     * than deleting the artefact without replacing it.
     *
     * @throws \Throwable On any failure to regenerate the artefact.
     */
    public function regenerate() : void;
}
