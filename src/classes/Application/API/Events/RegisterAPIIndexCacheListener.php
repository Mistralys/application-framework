<?php
/**
 * @package Application
 * @subpackage CacheControl
 */

declare(strict_types=1);

namespace Application\API\Events;

use Application\API\APIManager;
use Application\API\Collection\APIMethodIndex;
use Application\CacheControl\Events\BaseRegisterCacheLocationsListener;

/**
 * Registers the API method index cache location.
 *
 * @package Application
 * @subpackage CacheControl
 *
 * @see APIMethodIndex::getCacheLocation()
 */
class RegisterAPIIndexCacheListener extends BaseRegisterCacheLocationsListener
{
    protected function getCacheLocations(): array
    {
        return array(APIManager::getInstance()->getMethodIndex()->getCacheLocation());
    }
}
