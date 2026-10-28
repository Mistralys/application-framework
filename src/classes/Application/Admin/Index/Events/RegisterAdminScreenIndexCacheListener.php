<?php
/**
 * @package Application
 * @subpackage Admin
 */

declare(strict_types=1);

namespace Application\Admin\Index\Events;

use Application\Admin\Index\AdminScreenIndexCacheLocation;
use Application\CacheControl\Events\BaseRegisterCacheLocationsListener;

/**
 * Registers the admin screen index cache location.
 *
 * @package Application
 * @subpackage Admin
 *
 * @see AdminScreenIndexCacheLocation
 */
class RegisterAdminScreenIndexCacheListener extends BaseRegisterCacheLocationsListener
{
    protected function getCacheLocations(): array
    {
        return array(new AdminScreenIndexCacheLocation());
    }
}
