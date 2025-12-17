<?php
/**
 * @package Application
 * @subpackage CacheControl
 */

declare(strict_types=1);

namespace Application\CacheControl\Events;

use Application\CacheControl\CacheLocationInterface;
use Application\EventHandler\OfflineEvents\BaseOfflineListener;
use Application_EventHandler_Event;
use AppUtils\ClassHelper;

/**
 * Base class for offline event listeners that register cache locations.
 *
 * @package Application
 * @subpackage CacheControl
 */
abstract class BaseRegisterCacheLocationsListener extends BaseOfflineListener
{
    public function getEventName(): string
    {
        return RegisterCacheLocationsEvent::EVENT_NAME;
    }

    protected function handleEvent(Application_EventHandler_Event $event, ...$args): void
    {
        $this->handleTagRegistration(
            ClassHelper::requireObjectInstanceOf(
                RegisterCacheLocationsEvent::class,
                $event
            )
        );
    }

    protected function handleTagRegistration(RegisterCacheLocationsEvent $event): void
    {
        foreach ($this->getCacheLocations() as $location) {
            $event->registerLocation($location);
        }
    }

    /**
     * @return CacheLocationInterface[]
     */
    abstract protected function getCacheLocations() : array;
}
