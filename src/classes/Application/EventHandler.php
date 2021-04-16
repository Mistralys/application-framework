<?php
/**
 * File containing the {@link Application_EventHandler} class.
 * 
 * @package Application
 * @subpackeage EventHandler
 * @see Application_EventHandler
 */

/**
 * Event management class: handles registering and triggering events
 * and any listeners. This is used for all events, so event names
 * should be prefixed to ensure that the naming is unique.
 * 
 * @package Application
 * @subpackage EventHandler
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_EventHandler
{
    const ERROR_INVALID_EVENT_CLASS = 13801;
    const ERROR_MISSING_EVENT_CLASS = 13802;
    const ERROR_INVALID_CALLBACK = 13803;
    const ERROR_UNKNOWN_LISTENER = 13804;

   /**
    * @var array<int,Application_EventHandler_Listener>
    */
    protected static $listeners = array();

   /**
    * @var array<string,array<int,int>>
    */
    protected static $events = array();

   /**
    * @var integer
    */
    protected static $listenerIDCounter = 0;

   /**
    * Adds a callback to the specified event.
    * 
    * @param string $eventName
    * @param mixed $callback
    * @param string $source A human readable label for the listener.
    * @return Application_EventHandler_Listener
    * @throws Application_EventHandler_Exception|Application_Exception
    */
    public static function addListener(string $eventName, $callback, string $source='') : Application_EventHandler_Listener
    {
        Application::requireCallableValid($callback, self::ERROR_INVALID_CALLBACK);
        
        self::$listenerIDCounter++;
        $listenerID = self::$listenerIDCounter;

        if (!isset(self::$events[$eventName])) 
        {
            self::$events[$eventName] = array();
        }

        $listener = new Application_EventHandler_Listener(
            $listenerID,
            $eventName,
            $callback,
            $source
        );
        
        self::$events[$eventName][] = $listenerID;
        self::$listeners[$listenerID] = $listener;

        Application::log(sprintf('Event [%s] | Added the listener [%s]. | Source: [%s]', $eventName, $listenerID, $source));

        return $listener;
    }
    
   /**
    * Checks whether any listeners have been added for the specified event.
    * @param string $eventName
    * @return boolean
    */
    public static function hasListener(string $eventName) : bool
    {
        return isset(self::$events[$eventName]) && !empty(self::$events[$eventName]);
    }

    /**
     * Triggers the specified event, calling all registered listeners.
     *
     * @param string $eventName
     * @param array $args
     * @param string $class The name of the event class to use. Allows specifying a custom class for this event, which must extend the base event class.
     * @return Application_EventHandler_Event
     * @throws Application_EventHandler_Exception
     * @throws Application_Exception_UnexpectedInstanceType
     *
     * @see Application_EventHandler::ERROR_MISSING_EVENT_CLASS
     * @see Application_EventHandler::ERROR_INVALID_EVENT_CLASS
     */
    public static function trigger(string $eventName, array $args=array(), string $class=Application_EventHandler_Event::class)
    {
        if(!is_array($args)) {
            $args = array($args);
        }
        
        if(!class_exists($class)) {
            throw new Application_EventHandler_Exception(
                'Missing event class',
                sprintf(
                    'Event [%s]: The [%s] class could not be found. Custom event classes must be loaded prior to triggering the event.',
                    $eventName,
                    $class
                ),
                self::ERROR_MISSING_EVENT_CLASS
            );
        }
        
        /* @var $event Application_EventHandler_Event */
        $event = ensureType(
            Application_EventHandler_Event::class, 
            new $class($eventName, $args),
            self::ERROR_INVALID_EVENT_CLASS
        );
        
        if (!isset(self::$events[$eventName])) 
        {
            return $event;
        }

        $event->startTrigger();
        
        array_unshift($args, $event);
        
        foreach (self::$events[$eventName] as $listenerID) 
        {
            $listener = self::getListenerByID($listenerID);
            
            $event->selectListener($listener);
            
            call_user_func_array($listener->getCallback(), $args);
            
            if($event->isCancelled()) {
                Application::log(sprintf('Event [%s] | Event has been cancelled by listener [%s].', $eventName, $listenerID));
                break;
            }
        }
        
        $event->stopTrigger();

        return $event;
    }
    
    public static function removeListener(int $listenerID) : void
    {
        if(!self::listenerExists($listenerID))
        {
            return;
        }
        
        $listener = self::getListenerByID($listenerID);
        $eventName = $listener->getEventName();
        
        unset(self::$listeners[$listenerID]);
        
        Application::log(sprintf('Event [%s] | Removed the listener [%s].', $eventName, $listenerID));
        
        $key = array_search($listenerID, self::$events[$eventName]);
        if($key !== false) 
        {
            unset(self::$events[$eventName][$key]);
        }
    }
    
    public static function listenerExists(int $listenerID) : bool
    {
        return isset(self::$listeners[$listenerID]);
    }

    /**
     * @param int $listenerID
     * @return Application_EventHandler_Listener
     * @throws Application_EventHandler_Exception
     *
     * @see Application_EventHandler::ERROR_UNKNOWN_LISTENER
     */
    public static function getListenerByID(int $listenerID) : Application_EventHandler_Listener
    {
        if(isset(self::$listeners[$listenerID])) 
        {
            return self::$listeners[$listenerID];
        }
        
        throw new Application_EventHandler_Exception(
            'Unknown event listener',
            sprintf(
                'Could not get listener with ID [%s].',
                $listenerID
            ),
            self::ERROR_UNKNOWN_LISTENER
        );
    }
}
