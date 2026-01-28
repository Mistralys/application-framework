<?php
/**
 * @package Application
 * @subpackage CacheControl
 */

declare(strict_types=1);

namespace Application\CacheControl\Events;

use Application\CacheControl\CacheLocationInterface;
use Application\CacheControl\CacheManager;
use Application\EventHandler\OfflineEvents\BaseOfflineEvent;

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
class RegisterCacheLocationsEvent extends BaseOfflineEvent
{
    public const string EVENT_NAME = 'RegisterCacheLocations';

    /**
     * @var CacheLocationInterface[]
     */
    private array $locations = array();

    public function getName(): string
    {
        return self::EVENT_NAME;
    }

    public function registerLocation(CacheLocationInterface $location): void
    {
        $this->locations[] = $location;
    }

    public function getLocations(): array
    {
        return $this->locations;
    }
}
