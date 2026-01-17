<?php
/**
 * @package Application
 * @subpackage CacheControl
 */

declare(strict_types=1);

namespace Application\AI\Cache\Events;

use Application\AI\Cache\AICacheLocation;
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
class RegisterAIIndexCacheListener extends BaseRegisterCacheLocationsListener
{
    protected function getCacheLocations(): array
    {
        return array(AICacheLocation::getInstance());
    }
}
