<?php
/**
 * @package Application
 * @subpackage CacheControl
 */

declare(strict_types=1);

namespace Application\API\Events;

use Application\API\Cache\APIResponseCacheLocation;
use Application\CacheControl\Events\BaseRegisterCacheLocationsListener;

/**
 * Registers the API response cache location with the CacheControl system.
 * This listener is discovered automatically — no manual registration required.
 *
 * @package Application
 * @subpackage CacheControl
 * @see APIResponseCacheLocation
 */
class RegisterAPIResponseCacheListener extends BaseRegisterCacheLocationsListener
{
    protected function getCacheLocations() : array
    {
        return array(new APIResponseCacheLocation());
    }
}
