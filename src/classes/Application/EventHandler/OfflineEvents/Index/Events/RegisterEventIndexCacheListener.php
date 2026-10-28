<?php
/**
 * @package Application
 * @subpackage EventHandler
 */

declare(strict_types=1);

namespace Application\EventHandler\OfflineEvents\Index\Events;

use Application\CacheControl\Events\BaseRegisterCacheLocationsListener;
use Application\EventHandler\OfflineEvents\Index\EventIndexCacheLocation;

/**
 * Registers the offline event index cache location.
 *
 * @package Application
 * @subpackage EventHandler
 *
 * @see EventIndexCacheLocation
 */
class RegisterEventIndexCacheListener extends BaseRegisterCacheLocationsListener
{
    protected function getCacheLocations(): array
    {
        return array(new EventIndexCacheLocation());
    }
}
