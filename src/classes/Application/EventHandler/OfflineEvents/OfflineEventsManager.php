<?php
/**
 * @package Application
 * @subpackage EventHandler
 */

declare(strict_types=1);

namespace Application\EventHandler\OfflineEvents;

use AppUtils\ClassHelper;
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
 * ## Event classes folder structure
 *
 * By default, offline events are stored in the driver's
 * `OfflineEvents` subfolder under `assets/classes/{DriverName}/OfflineEvents`.
 * Each event has its own class, and listeners must be added
 * to a subfolder named after the event class.
 *
 * ### Example structure
 *
 * - `OfflineEvents/CriticalEvent.php`
 * - `OfflineEvents/CriticalEvent/LogHandler.php`
 * - `OfflineEvents/CriticalEvent/NotifyHandler.php`
 *
 * `CriticalEvent.php` contains the event class, `LogHandler.php`
 * and `NotifyHandler.php` are listeners of the event.
 *
 * ## Class inheritance
 *
 * - Offline events must extend the regular event class, {@see Application_EventHandler_Event}.
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

        return $event;
    }
}
