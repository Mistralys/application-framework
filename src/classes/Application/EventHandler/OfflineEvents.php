<?php
/**
 * @package Application
 * @subpackage EventHandler
 */

declare(strict_types=1);

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
 * to load the according class instance, and let it process
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
 * - Offline listeners must extend the class {@see Application_EventHandler_OfflineEvents_OfflineListener}.
 *
 * @package Application
 * @subpackage EventHandler
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see DocumentationPages::OFFLINE_EVENTS
 */
class Application_EventHandler_OfflineEvents
{
    /**
     * @var string[]
     */
    private array $eventBaseNames = array();

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
        if(!in_array($baseName, $this->eventBaseNames, true))
        {
            $this->eventBaseNames[] = $baseName;
        }

        return $this;
    }

    /**
     * @param string $eventName
     * @param array<int,mixed> $args
     * @param class-string|null $eventClassName
     * @return Application_EventHandler_OfflineEvents_OfflineEvent
     */
    public function triggerEvent(string $eventName, array $args=array(), ?string $eventClassName=null) : Application_EventHandler_OfflineEvents_OfflineEvent
    {
        $event = $this->createEvent($eventName, $args, $eventClassName);

        $event->trigger();

        return $event;
    }

    /**
     * @param string $eventName
     * @param array<int,mixed> $args
     * @param class-string|null $eventClassName
     * @return Application_EventHandler_OfflineEvents_OfflineEvent
     */
    public function createEvent(string $eventName, array $args, ?string $eventClassName=null) : Application_EventHandler_OfflineEvents_OfflineEvent
    {
        if($eventClassName !== null)
        {
            $eventClass = $eventClassName;
        }
        else
        {
            $eventClass = $this->resolveEventClass($eventName);
        }

        return new Application_EventHandler_OfflineEvents_OfflineEvent(
            $eventName,
            $eventClass,
            $args
        );
    }

    public function resolveEventClass(string $eventName) : ?string
    {
        foreach($this->eventBaseNames as $baseName)
        {
            $className = ClassHelper::resolveClassName(sprintf(
                '%s_%sEvent',
                $baseName,
                $eventName
            ));

            if($className !== null)
            {
                return $className;
            }
        }

        return null;
    }
}
