<?php
/**
 * @package Application
 * @subpackage EventHandler
 */

declare(strict_types=1);

namespace Application\EventHandler\OfflineEvents;

use Application\EventHandler\Event\StandardEvent;
use Mistralys\AppFrameworkDocs\DocumentationPages;

/**
 * Class handling the management of offline events: These
 * are events that are stored on the disk instead of living
 * in memory.
 *
 * ## What are offline events used for?
 *
 * They allow for classes to listen to events even if the
 * class instance is not loaded at the time the event is
 * triggered: the event listener includes everything needed
 * to load the matching class instance, and let it process
 * the event.
 *
 * ## How do the offline events work?
 *
 * When an offline event is triggered, it is converted to
 * a regular event. Listeners are equally converted to
 * regular listeners by "waking" the listening classes, and
 * adding them as listeners.
 *
 * ## Event discovery
 *
 * Offline event classes and listener classes can be placed anywhere
 * in the codebase. The event indexer automatically discovers them by
 * scanning for classes that implement `OfflineEventInterface` and
 * `OfflineEventListenerInterface`.
 *
 * Listeners are linked to events by matching the event name returned
 * by the listener's `getEventName()` method with the event's `getName()`
 * method.
 *
 * ## Class inheritance
 *
 * - Offline events must extend the regular event class, {@see StandardEvent}.
 * - Offline listeners must extend the class {@see BaseOfflineListener}.
 *
 * @package Application
 * @subpackage EventHandler
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see DocumentationPages::OFFLINE_EVENTS
 */
class OfflineEventsManager
{
    /**
     * @var array<string,OfflineEventContainer[]> $triggeredEvents
     */
    private array $triggeredEvents = array();

    public function __construct()
    {
    }

    /**
     * @param string $eventName
     * @param array<int,mixed> $args
     * @return OfflineEventContainer
     */
    public function triggerEvent(string $eventName, array $args = array()): OfflineEventContainer
    {
        $event = new OfflineEventContainer($eventName, $args);

        $event->trigger();

        if(!isset($this->triggeredEvents[$eventName])) {
            $this->triggeredEvents[$eventName] = array();
        }

        $this->triggeredEvents[$eventName][] = $event;

        return $event;
    }

    public function wasEventTriggered(string $eventName): bool
    {
        return isset($this->triggeredEvents[$eventName]);
    }

    /**
     * @return array<string,OfflineEventContainer[]>
     */
    public function getTriggeredEvents(): array
    {
        return $this->triggeredEvents;
    }

    /**
     * @param string $eventName
     * @return OfflineEventContainer[]
     */
    public function getEventsByName(string $eventName): array
    {
        return $this->triggeredEvents[$eventName] ?? array();
    }
}
