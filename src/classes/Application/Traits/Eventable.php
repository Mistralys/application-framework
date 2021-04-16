<?php
/**
 * File containing the class {@Application_EventHandler_EventableListener}.
 *
 * @package Application
 * @subpackage EventHandler
 * @see Application_EventHandler_EventableListener
 */

declare(strict_types=1);

/**
 * Trait used to enable any class to use event handling.
 *
 * Usage: use this trait, and implement the interface {@see Application_Interfaces_Eventable}.
 *
 * @package Application
 * @subpackage EventHandler
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Interfaces_Eventable
 */
trait Application_Traits_Eventable
{
    /**
     * @var array<string,Application_EventHandler_EventableListener[]>
     */
    protected $eventListeners = array();

    /**
     * @var int
     */
    protected $eventListenerCounter = 0;

    /**
     * @param string $eventName
     * @param callable $callback
     * @return Application_EventHandler_EventableListener
     */
    public function addEventListener(string $eventName, callable $callback) : Application_EventHandler_EventableListener
    {
        $this->eventListenerCounter++;

        $listener = new Application_EventHandler_EventableListener(
            $this->eventListenerCounter,
            $eventName,
            $callback,
            $this
        );

        $this->eventListeners[$eventName] = $listener;

        return $listener;
    }

    /**
     * Removes a previously added listener.
     *
     * NOTE: This will fail silently if the listener does not exist.
     *
     * @param Application_EventHandler_EventableListener $listener
     */
    public function removeEventListener(Application_EventHandler_EventableListener $listener) : void
    {
        $eventName = $listener->getEventName();

        if(!isset($this->eventListeners[$eventName])) {
            return;
        }

        $listenerID = $listener->getID();
        $keep = array();
        foreach ($this->eventListeners[$eventName] as $check) {
            if($check->getID() !== $listenerID) {
                $keep[] = $listener;
            }
        }

        $this->eventListeners[$eventName] = $keep;
    }

    /**
     * Triggers the specified event: creates the event, and executes
     * all listeners that have been added for it.
     *
     * Returns null if no listeners have been added.
     *
     * @param string $eventName
     * @param array $args
     * @param string $eventClass
     * @return Application_EventHandler_EventableEvent|null
     * @throws Application_Exception
     */
    protected function triggerEvent(string $eventName, array $args, string $eventClass='') : ?Application_EventHandler_EventableEvent
    {
        if(!$this->hasEventListeners($eventName)) {
            return null;
        }

        $event = $this->createEvent($eventName, $args, $eventClass);

        $event->startTrigger();

        foreach($this->eventListeners[$eventName] as $listener)
        {
            $event->selectListener($listener);

            call_user_func_array($listener->getCallback(), $event->getArguments());

            if($event->isCancelled()) {
                Application::log(sprintf(
                    'Event [%s] | Event has been cancelled by listener [%s].',
                    $eventName,
                    $listener->getID()
                ));
                break;
            }
        }

        $event->stopTrigger();

        return $event;
    }

    /**
     * @param string $eventName
     * @param array $args Indexed array with arguments for the event.
     * @param string $eventClass
     * @return Application_EventHandler_EventableEvent
     * @throws Application_Exception
     */
    protected function createEvent(string $eventName, array $args, string $eventClass='') : Application_EventHandler_EventableEvent
    {
        if(empty($eventClass)) {
            $eventClass = Application_EventHandler_EventableEvent::class;
        }

        $event = new $eventClass($eventName, $this, $args);

        if($event instanceof Application_EventHandler_EventableEvent) {
           return $event;
        }

        throw new Application_Exception(
            'Invalid event object instance',
            sprintf(
                'The class [%s] does not extend the [%s] base class.',
                get_class($event),
                Application_EventHandler_EventableEvent::class
            ),
            Application_Interfaces_Eventable::ERROR_INVALID_EVENT_CLASS
        );
    }

    public function hasEventListeners(string $eventName) : bool
    {
        return isset($this->eventListeners[$eventName]) && !empty($this->eventListeners[$eventName]);
    }

    /**
     * @param string $eventName
     * @return Application_EventHandler_EventableListener[]
     */
    public function getEventListeners(string $eventName) : array
    {
        if(isset($this->eventListeners[$eventName])) {
            return $this->eventListeners[$eventName];
        }

        return array();
    }
}
