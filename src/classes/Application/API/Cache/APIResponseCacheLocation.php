<?php
/**
 * @package API
 * @subpackage Cache
 */

declare(strict_types=1);

namespace Application\API\Cache;

use Application\CacheControl\BaseCacheLocation;

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
    public const string LOCATION_ID = 'APIResponseCache';

    public function getID() : string
    {
        return self::LOCATION_ID;
    }

    public function getLabel() : string
    {
        return t('API Response Cache');
    }

    public function getByteSize() : int
    {
        return APICacheManager::getCacheSize();
    }

    public function clear() : void
    {
        APICacheManager::clearAll();
    }
}
