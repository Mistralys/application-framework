<?php

declare(strict_types=1);

use AppUtils\FileHelper;

class Application_EventHandler_OfflineEvents_OfflineEvent
{
    public const ERROR_CANNOT_FIND_CLASS_LOCATION = 97201;

    /**
     * @var string
     */
    private $eventName;

    /**
     * @var string
     */
    private $eventClass;

    /**
     * @var array
     */
    private $args;

    /**
     * @var string|null
     */
    private $listenersFolder = null;

    /**
     * @var Application_EventHandler_OfflineEvents_OfflineListener[]
     */
    private $listeners = array();

    /**
     * @var bool
     */
    private $listenersLoaded = false;

    /**
     * @var string
     */
    private $triggerName;

    /**
     * @var int
     */
    private static $eventCounter = 0;

    /**
     * @var Application_EventHandler_Event|NULL
     */
    private $triggeredEvent = null;

    public function __construct(string $eventName, string $eventClass, array $args=array())
    {
        self::$eventCounter++;

        $this->eventName = $eventName;
        $this->eventClass = $eventClass;
        $this->triggerName = 'offline-event-'.self::$eventCounter;
        $this->args = $args;
    }

    /**
     * @return string
     */
    public function getEventName() : string
    {
        return $this->eventName;
    }

    /**
     * @return string
     */
    public function getEventClass() : string
    {
        return $this->eventClass;
    }

    /**
     * @return array
     */
    public function getArgs() : array
    {
        return $this->args;
    }

    /**
     * @return string
     *
     * @throws Application_EventHandler_Exception
     * @see Application_EventHandler_OfflineEvents_OfflineEvent::ERROR_CANNOT_FIND_CLASS_LOCATION
     */
    public function getListenersFolder() : string
    {
        if(isset($this->listenersFolder))
        {
            return $this->listenersFolder;
        }

        $file = Application_Bootstrap::getAutoLoader()->findFile($this->eventClass);

        if($file === false)
        {
            throw new Application_EventHandler_Exception(
                'Cannot determine event class location.',
                sprintf(
                    'The composer autoloader could not find the location on disk of the class [%s].',
                    $this->eventClass
                ),
                self::ERROR_CANNOT_FIND_CLASS_LOCATION
            );
        }

        $this->listenersFolder = dirname($file).'/'.getClassTypeName($this->eventClass);

        return $this->listenersFolder;
    }

    /**
     * @return Application_EventHandler_OfflineEvents_OfflineListener[]
     */
    public function getListeners() : array
    {
        $this->loadListeners();

        return $this->listeners;
    }

    private function loadListeners() : void
    {
        if($this->listenersLoaded)
        {
            return;
        }

        $this->listenersLoaded = true;

        $names = FileHelper::createFileFinder($this->getListenersFolder())
            ->getPHPClassNames();

        foreach($names as $name)
        {
            $this->listeners[] = $this->createListener($name);
        }
    }

    public function hasListeners() : bool
    {
        $this->loadListeners();

        return !empty($this->listeners);
    }

    public function getTriggeredEvent() : ?Application_EventHandler_Event
    {
        return $this->trigger();
    }

    public function trigger() : ?Application_EventHandler_Event
    {
        if(isset($this->triggeredEvent))
        {
            return $this->triggeredEvent;
        }

        if(!$this->hasListeners())
        {
            return null;
        }

        $listeners = $this->getListeners();

        foreach($listeners as $listener)
        {
            Application_EventHandler::addListener(
                $this->triggerName,
                $listener->getCallable(),
                'offline-event'
            );
        }

        $this->triggeredEvent = Application_EventHandler::trigger(
            $this->triggerName,
            $this->args,
            $this->eventClass
        );

        return $this->triggeredEvent;
    }

    /**
     * @param string $name
     * @return Application_EventHandler_OfflineEvents_OfflineListener
     * @throws Application_Exception_UnexpectedInstanceType
     */
    private function createListener(string $name) : Application_EventHandler_OfflineEvents_OfflineListener
    {
        $className = $this->eventClass.'_'.$name;

        $listener = new $className($this);

        if($listener instanceof Application_EventHandler_OfflineEvents_OfflineListener)
        {
            return $listener;
        }

        throw new Application_Exception_UnexpectedInstanceType(Application_EventHandler_OfflineEvents_OfflineListener::class, $listener);
    }
}
