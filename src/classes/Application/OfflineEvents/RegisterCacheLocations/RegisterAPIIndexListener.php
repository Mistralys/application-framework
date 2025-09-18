<?php
/**
 * @package Application
 * @subpackage CacheControl
 */

declare(strict_types=1);

namespace Application\OfflineEvents\RegisterCacheLocationsEvent;

use Application\API\Collection\APIMethodIndex;
use Application\CacheControl\BaseRegisterCacheLocationsListener;
use Application_API;

/**
 * Registers the API method index cache location.
 *
 * @package Application
 * @subpackage CacheControl
 *
 * @see APIMethodIndex::getCacheLocation()
 */
class RegisterAPIIndexListener extends BaseRegisterCacheLocationsListener
{
    protected function getCacheLocations(): array
    {
        return array(Application_API::getInstance()->getMethodIndex()->getCacheLocation());
    }
}
