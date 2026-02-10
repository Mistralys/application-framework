<?php
/**
 * @package Application
 * @subpackage CacheControl
 */

declare(strict_types=1);

namespace Application\OfflineEvents\RegisterCacheLocationsEvent;

use Application\AppFactory\ClassCacheHandler;
use Application\CacheControl\Events\BaseRegisterCacheLocationsListener;

/**
 * @package Application
 * @subpackage CacheControl
 *
 * @see ClassCacheHandler::getCacheLocation()
 */
class RegisterClassCacheListener extends BaseRegisterCacheLocationsListener
{
    protected function getCacheLocations(): array
    {
        return array(ClassCacheHandler::getCacheLocation());
    }
}
