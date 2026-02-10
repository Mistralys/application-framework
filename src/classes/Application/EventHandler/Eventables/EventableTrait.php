<?php
/**
 * @package Application
 * @subpackage EventHandler
 */

declare(strict_types=1);

namespace Application\EventHandler\Eventables;

use Application_Exception;
use AppUtils\ConvertHelper;

/**
 * Trait used to enable any class to use event handling.
 *
 * Usage:
 *
 * 1) Use this trait
 * 2) Implement the interface {@see EventableInterface}.
 *
 * Optional:
 *
 * - Override {@see self::getEventNamespace()} to handle event namespaces.
 *
 * @package Application
 * @subpackage EventHandler
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see EventableInterface
 */
trait EventableTrait
{
    /**
     * @var array<string,EventableListener[]>
     */
    protected array $eventListeners = array();
    protected static int $eventListenerCounter = 0;
    protected bool $eventsDisabled = false;

    /**
     * @var array<string,bool>
     */
    protected array $ignoredEvents = array();

    /**
     * @param string $eventName
     * @param callable $callback
     * @return EventableListener
     */
    public function addEventListener(string $eventName, callable $callback): EventableListener
    {
        self::$eventListenerCounter++;

        $eventNameNS = $this->namespaceEventName($eventName);

        $listener = new EventableListener(
            self::$eventListenerCounter,
            $eventName,
            $callback,
            $this,
            $eventNameNS
        );

        $this->eventListeners[$eventNameNS][] = $listener;

        $this->logEvent(
            $eventName,
            'Listener [#%s] | Added the listener | Callback [%s]',
            $listener->getID(),
            $listener->getCallbackAsString()
        );

        return $listener;
    }

    protected function logEventable(string $message, string $eventName = ''): void
    {
        $prefix = 'Eventable | ';
        if (!empty($eventName)) {
            $prefix = sprintf('Event [%s] | ', $eventName);
        }

        $this->log($prefix . $message);
    }

    /**
     * Removes a previously added listener.
     *
     * NOTE: This will fail silently if the listener does not exist.
     *
     * @param EventableListener $listener
     */
    public function removeEventListener(EventableListener $listener): void
    {
        $this->logEvent(
            $listener->getEventName(),
            'Listener [#%s] | Removing listener | Callback [%s].',
            $listener->getID(),
            ConvertHelper::callback2string($listener->getCallback())
        );

        $keep = array();
        $eventNameNS = $listener->getEventNameNS();
        $listenerID = $listener->getID();

        foreach ($this->eventListeners[$eventNameNS] as $check) {
            if ($check->getID() !== $listenerID) {
                $keep[] = $check;
            }
        }

        $this->eventListeners[$eventNameNS] = $keep;
    }

    /**
     * Like {@see self::triggerEvent()}, but returns the
     * correct instance return type for the event class.
     *
     * @template ClassInstanceType
     * @param string $eventName
     * @param class-string<ClassInstanceType> $eventClass
     * @param array<int|string,mixed> $args
     * @return ClassInstanceType|NULL Returns `null` if no listeners have been added, or if events have been disabled.
     *
     * @throws Application_Exception
     */
    protected function triggerEventClass(string $eventName, string $eventClass, array $args = array())
    {
        $event = $this->triggerEvent($eventName, $args, $eventClass);

        if ($event instanceof $eventClass) {
            return $event;
        }

        return null;
    }

    /**
     * Triggers the specified event: creates the event, and executes
     * all listeners that have been added for it.
     *
     * @param string $eventName
     * @param array<int,mixed> $args
     * @param class-string<EventableEventInterface>|NULL $eventClass
     * @return EventableEventInterface|null Returns `null` if no listeners have been added, or if events have been disabled.
     * @throws Application_Exception
     */
    protected function triggerEvent(string $eventName, array $args = array(), ?string $eventClass = null): ?EventableEventInterface
    {
        if ($this->eventsDisabled === true) {
            return null;
        }

        $eventNameNS = $this->namespaceEventName($eventName);

        if ($this->isEventIgnored($eventName)) {
            $this->logEventable('Event is on the ignore list, skipping.', $eventNameNS);
            return null;
        }

        $this->logEventable('Triggering event.', $eventNameNS);

        if (!$this->hasEventListeners($eventName)) {
            $this->logEventable('Ignoring event, no listeners added.', $eventNameNS);
            return null;
        }

        $event = $this->createEvent($eventName, $args, $eventClass);

        $event->startTrigger();

        $this->logEventable(sprintf('Trigger started, processing [%s] listeners.', $this->countEventListeners($eventName)), $eventNameNS);

        foreach ($this->getEventListeners($eventName) as $listener) {
            $event->selectListener($listener);

            $callback = $listener->getCallback();
            $args = $event->getArguments();
            array_unshift($args, $event);

            $this->logEventable(
                sprintf(
                    'Listener [#%s] | Processing | Callback [%s]',
                    $listener->getID(),
                    $listener->getCallbackAsString()
                ),
                $eventNameNS
            );

            call_user_func_array($callback, $args);

            if ($event->isCancelled()) {
                $this->logEvent(
                    $eventNameNS,
                    'Listener [#%s] | CANCEL | Callback [%s].',
                    $listener->getID(),
                    $listener->getCallbackAsString()
                );
                break;
            }
        }

        $event->stopTrigger();

        $this->logEventable('Trigger ended.', $eventNameNS);

        return $event;
    }

    protected function namespaceEventName(string $eventName): string
    {
        $ns = $this->getEventNamespace($eventName);
        if (!empty($ns)) {
            return $eventName . '@' . $ns;
        }

        return $eventName;
    }

    /**
     * @param string $eventName
     * @return string|null
     */
    public function getEventNamespace(string $eventName): ?string
    {
        return null;
    }

    /**
     * @param string $eventName
     * @param array<int,mixed> $args Indexed array with arguments for the event.
     * @param class-string<EventableEventInterface>|NULL $eventClass
     * @return StandardEventableEvent
     * @throws EventableException
     */
    protected function createEvent(string $eventName, array $args, ?string $eventClass = null): EventableEventInterface
    {
        if (empty($eventClass)) {
            $eventClass = StandardEventableEvent::class;
        }

        $event = new $eventClass($eventName, $this, $args);

        if ($event instanceof EventableEventInterface) {
            return $event;
        }

        throw new EventableException(
            'Invalid event object instance',
            sprintf(
                'The class [%s] does not extend the [%s] interface.',
                get_class($event),
                EventableEventInterface::class
            ),
            EventableException::ERROR_INVALID_EVENT_CLASS
        );
    }

    public function getIgnoredEvents(): array
    {
        $result = array();
        foreach ($this->ignoredEvents as $eventName => $ignored) {
            if ($ignored === true) {
                $result[] = $eventName;
            }
        }

        return $result;
    }

    /**
     * @return $this
     */
    public function ignoreEvent(string $eventName): self
    {
        $this->ignoredEvents[$this->namespaceEventName($eventName)] = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function unIgnoreEvent(string $eventName): self
    {
        $this->ignoredEvents[$this->namespaceEventName($eventName)] = false;
        return $this;
    }

    public function isEventIgnored(string $eventName): bool
    {
        $eventName = $this->namespaceEventName($eventName);

        return isset($this->ignoredEvents[$eventName]) && $this->ignoredEvents[$eventName] === true;
    }

    public function hasEventListeners(string $eventName): bool
    {
        return !empty($this->eventListeners[$this->namespaceEventName($eventName)]);
    }

    public function countEventListeners(string $eventName): int
    {
        $eventNameNS = $this->namespaceEventName($eventName);

        if (isset($this->eventListeners[$eventNameNS])) {
            return count($this->eventListeners[$eventNameNS]);
        }

        return 0;
    }

    /**
     * @param string $eventName
     * @return EventableListener[]
     */
    public function getEventListeners(string $eventName): array
    {
        return $this->eventListeners[$this->namespaceEventName($eventName)] ?? array();
    }

    public function clearEventListeners(string $eventName): void
    {
        $eventName = $this->namespaceEventName($eventName);

        if (isset($this->eventListeners[$eventName])) {
            unset($this->eventListeners[$eventName]);
        }
    }

    public function clearAllEventListeners(): void
    {
        $this->log('EventHandling | Clearing all event listeners.');
        $this->eventListeners = array();
    }

    public function areEventsDisabled(): bool
    {
        return $this->eventsDisabled;
    }

    protected function disableEvents(): void
    {
        if ($this->eventsDisabled) {
            return;
        }

        $this->log('EventHandling | Disabling all events.');

        $this->eventsDisabled = true;
    }
}
