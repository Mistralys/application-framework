<?php
/**
 * @package Application
 * @subpackage CacheControl
 */

declare(strict_types=1);

namespace Application\OfflineEvents;

use Application\CacheControl\CacheLocationInterface;
use Application\CacheControl\CacheManager;
use Application_EventHandler_Event;

/**
 * This offline event is triggered when the {@see CacheManager} is initialized,
 * to discover and register all locations where data is cached.
 *
 * @package Application
 * @subpackage CacheControl
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
