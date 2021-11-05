<?php
/**
 * File containing the class {@see Application_EventHandler_OfflineEvents}.
 *
 * @package Application
 * @subpackage EventHandler
 * @see Application_EventHandler_OfflineEvents
 */

declare(strict_types=1);

/**
 * Class handling the management of offline events: These
 * are events that are stored on the disk instead of living
 * in memory.
 *
 * **What are offline events used for?**
 *
 * They allow for classes to listen to events even if the
 * class instance is not loaded at the time the event is
 * triggered: the event listener includes everything needed
 * to load the according class instance, and let it process
 * the event.
 *
 * **How do the offline events work?**
 *
 * When an offline event is triggered, it is converted to
 * a regular event. Listeners are equally converted to
 * regular listeners by "waking" the listening classes, and
 * adding them as listeners.

 * **Event classes folder structure**
 *
 * By default, offline events are stored in the driver's
 * `OfflineEvents` sub-folder under `assets/classes/{DriverName}/OfflineEvents`.
 * Each event has its own class, and listeners must be added
 * to a sub-folder named after the event class.
 *
 * Example structure:
 *
 * - `OfflineEvents/CriticalEvent.php`
 * - `OfflineEvents/CriticalEvent/Logger.php`
 * - `OfflineEvents/CriticalEvent/Notifier.php`
 *
 * `CriticalEvent.php` contains the event class, `Logger.php`
 * and `Notifier.php` are listeners of the event.
 *
 * **Class inheritance**
 *
 * - Offline events must extend the regular event class, {@see Application_EventHandler_Event}.
 * - Offline listeners must extend the class {@see Application_EventHandler_OfflineEvents_OfflineListener}.
 *
 * @package Application
 * @subpackage EventHandler
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_EventHandler_OfflineEvents
{
    /**
     * @var string[]
     */
    private $eventBaseNames = array();

    public function __construct()
    {
        $this->addEventsClassBase('Application_OfflineEvents');

        $this->addEventsClassBase(sprintf(
            '%s_OfflineEvents',
            APP_CLASS_NAME
        ));
    }

    /**
     * Adds a class prefix from which to load offline event handling classes.
     *
     * @param string $baseName
     * @return $this
     */
    public function addEventsClassBase(string $baseName) : Application_EventHandler_OfflineEvents
    {
        if(!in_array($baseName, $this->eventBaseNames))
        {
            $this->eventBaseNames[] = $baseName;
        }

        return $this;
    }

    /**
     * @param string $eventName
     * @param array $args
     * @return Application_EventHandler_OfflineEvents_OfflineEvent
     */
    public function triggerEvent(string $eventName, array $args=array()) : Application_EventHandler_OfflineEvents_OfflineEvent
    {
        $event = $this->createEvent($eventName, $args);

        if($event !== null)
        {
            $event->trigger();
        }

        return $event;
    }

    /**
     * @param string $eventName
     * @param array $args
     * @return Application_EventHandler_OfflineEvents_OfflineEvent|null
     */
    public function createEvent(string $eventName, array $args) : ?Application_EventHandler_OfflineEvents_OfflineEvent
    {
        $eventClass = $this->resolveEventClass($eventName);

        if($eventClass === null)
        {
            return null;
        }

        return new Application_EventHandler_OfflineEvents_OfflineEvent(
            $eventName,
            $eventClass,
            $args
        );
    }

    private function resolveEventClass(string $eventName) : ?string
    {
        foreach($this->eventBaseNames as $baseName)
        {
            $className = sprintf(
                '%s_%s',
                $baseName,
                $eventName
            );

            if(class_exists($className))
            {
                return $className;
            }
        }

        return null;
    }
}
