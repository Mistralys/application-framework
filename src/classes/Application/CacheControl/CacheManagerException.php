<?php
/**
 * @package Application
 * @subpackage CacheControl
 */

declare(strict_types=1);

namespace Application\CacheControl;

use Application_Exception;

/**
 * @package Application
 * @subpackage CacheControl
 */
class CacheManagerException extends Application_Exception
{
    /**
     * Thrown by {@see CacheManager::clearLocation()} and
     * {@see CacheManager::clearLocations()} when one or more
     * {@see RegeneratingCacheLocationInterface} locations failed
     * to regenerate. The first failure encountered is chained as
     * the previous exception.
     */
    public const int ERROR_REGENERATION_FAILED = 183801;
}
