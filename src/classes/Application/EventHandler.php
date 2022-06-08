<?php
/**
 * File containing the {@link Application_EventHandler} class.
 * 
 * @package Application
 * @subpackeage EventHandler
 * @see Application_EventHandler
 */

use Application\ClassFinder;

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
    public const ERROR_INVALID_EVENT_CLASS = 13801;
    public const ERROR_MISSING_EVENT_CLASS = 13802;
    public const ERROR_INVALID_CALLBACK = 13803;
    public const ERROR_UNKNOWN_LISTENER = 13804;

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
     * @var Application_EventHandler_OfflineEvents|NULL
     */
    private static $offlineEvents;

    /**
    * Adds a callback to the specified event.
    * 
    * @param string $eventName
    * @param callable $callback
    * @param string $source A human-readable label for the listener.
    * @return Application_EventHandler_Listener
    */
    public static function addListener(string $eventName, callable $callback, string $source='') : Application_EventHandler_Listener
    {
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
     * @param array<int,mixed> $args Indexed array of arguments.
     * @param string $class The name of the event class to use. Allows specifying a custom class for this event, which must extend the base event class.
     * @return Application_EventHandler_Event
     * @throws Application_EventHandler_Exception
     *
     * @see Application_EventHandler::ERROR_MISSING_EVENT_CLASS
     * @see Application_EventHandler::ERROR_INVALID_EVENT_CLASS
     */
    public static function trigger(string $eventName, array $args=array(), string $class=Application_EventHandler_Event::class)
    {
        if(!is_array($args)) {
            $args = array($args);
        }

        // PHP8 fix for call_user_func_array using associative array
        // keys as named parameters: we remove all keys to avoid the
        // interpreter using named parameters.
        //
        // https://php.watch/versions/8.0/named-parameters#named-params-call_user_func_array
        //
        $args = array_values($args);

        $event = self::createEvent($eventName, $class, $args);

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

    public static function createOfflineEvents() : Application_EventHandler_OfflineEvents
    {
        if(!isset(self::$offlineEvents))
        {
            self::$offlineEvents = new Application_EventHandler_OfflineEvents();
        }

        return self::$offlineEvents;
    }

    private static function createEvent(string $eventName, string $class, array $args) : Application_EventHandler_Event
    {
        $actualClass = ClassFinder::requireResolvedClass($class);

        return ClassFinder::requireInstanceOf(
            Application_EventHandler_Event::class,
            new $actualClass($eventName, $args),
            self::ERROR_INVALID_EVENT_CLASS
        );
    }
}
