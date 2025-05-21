<?php
/**
 * @package Application
 * @subpackage CacheControl
 */

declare(strict_types=1);

namespace Application\OfflineEvents;

use Application\CacheControl\BaseRegisterCacheLocationsListener;
use Application\CacheControl\CacheLocationInterface;
use Application\CacheControl\CacheManager;
use Application_EventHandler_Event;

/**
 * This offline event is triggered when the {@see CacheManager} is initialized,
 * to discover and register all locations where data is cached.
 *
 * ## Usage
 *
 * 1. Add listeners in the folder {@see self::EVENT_NAME} in the offline event folder.
 * 2. Extend the base class {@see BaseRegisterCacheLocationsListener}.
 *
 * @package Application
 * @subpackage CacheControl
 * @see BaseRegisterCacheLocationsListener
 */
class RegisterCacheLocationsEvent extends Application_EventHandler_Event
{
    public const EVENT_NAME = 'RegisterCacheLocations';

    /**
     * @var CacheLocationInterface[]
     */
    private array $locations = array();

    public function registerLocation(CacheLocationInterface $location): void
    {
        $this->locations[] = $location;
    }

    public function getLocations(): array
    {
        return $this->locations;
    }
}
