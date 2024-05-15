<?php

declare(strict_types=1);

use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;
use AppUtils\FileHelper;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\FileHelper_Exception;

class Application_EventHandler_OfflineEvents_OfflineEvent
{
    public const ERROR_CANNOT_FIND_CLASS_LOCATION = 97201;
    public const ERROR_COULD_NOT_DETECT_LISTENER_CLASS = 97202;

    private string $eventName;

    /**
     * @var class-string|NULL
     */
    private ?string $eventClass;
    /**
     * @var FolderInfo[]|null
     */
    private ?array $listenerFolders = null;
    private bool $listenersLoaded = false;
    private string $triggerName;
    private static int $eventCounter = 0;
    private ?Application_EventHandler_Event $triggeredEvent = null;

    /**
     * @var Application_EventHandler_OfflineEvents_OfflineListener[]
     */
    private array $listeners = array();

    /**
     * @var array<int,mixed>
     */
    private array $args;

    /**
     * @param string $eventName
     * @param class-string|NULL $eventClass
     * @param array<int,mixed> $args
     */
    public function __construct(string $eventName, ?string $eventClass, array $args=array())
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
     * @return class-string|NULL Can be null if there is no event class (the event is invalid).
     */
    public function getEventClass() : ?string
    {
        return $this->eventClass;
    }

    /**
     * @return array<int,mixed>
     */
    public function getArgs() : array
    {
        return $this->args;
    }

    /**
     * @return FolderInfo[]
     * @throws FileHelper_Exception
     *
     * @see Application_EventHandler_OfflineEvents_OfflineEvent::ERROR_CANNOT_FIND_CLASS_LOCATION
     */
    public function getListenerFolders() : array
    {
        if(isset($this->listenerFolders))
        {
            return $this->listenerFolders;
        }

        $this->listenerFolders = array();

        if($this->eventClass !== null)
        {
            $folderName = getClassTypeName($this->eventClass);

            $this->listenerFolders[] = FolderInfo::factory(sprintf(
                '%s/assets/classes/%s/OfflineEvents/%s',
                APP_ROOT,
                APP_CLASS_NAME,
                $folderName
            ));

            $this->listenerFolders[] = FolderInfo::factory(__DIR__.'/../../OfflineEvents/'.$folderName);
        }

        return $this->listenerFolders;
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
        if($this->listenersLoaded) {
            return;
        }

        $this->listenersLoaded = true;

        foreach($this->getListenerNames() as $name)
        {
            $this->listeners[] = $this->createListener($name);
        }
    }

    /**
     * @return string[]
     * @throws FileHelper_Exception
     */
    public function getListenerNames() : array
    {
        $names = array();

        foreach($this->getListenerFolders() as $folder)
        {
            if(!$folder->exists()) {
                continue;
            }

            array_push($names, ...FileHelper::createFileFinder($folder)->getPHPClassNames());
        }

        return $names;
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

        if(!isset($this->eventClass) || !$this->hasListeners())
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
     *
     * @throws BaseClassHelperException
     * @throws Throwable
     */
    private function createListener(string $name) : Application_EventHandler_OfflineEvents_OfflineListener
    {
        $classes = array(
            sprintf(
                '%s_OfflineEvents_%sEvent_%s',
               APP_CLASS_NAME,
                $this->eventName,
                $name
            ),
            'Application_OfflineEvents_'.$this->eventName.'Event_'.$name,
        );

        $className = null;
        foreach($classes as $class) {
            $resolved = ClassHelper::resolveClassName($class);
            if($resolved !== null) {
                $className = $resolved;
                break;
            }
        }

        if($className !== null) {
            return ClassHelper::requireObjectInstanceOf(
                Application_EventHandler_OfflineEvents_OfflineListener::class,
                new $className($this)
            );
        }

        throw new Application_Exception(
            'Could not detect offline event listener class.',
            sprintf(
                'Offline event: %s'.PHP_EOL.
                'Listener files have been found, but no matching classes could be found.'.PHP_EOL.
                'Listener names:'.PHP_EOL.
                '- %s'.PHP_EOL.
                'Tried to find the following classes:'.PHP_EOL.
                '- %s',
                $this->eventName,
                implode(PHP_EOL.'- ', $this->getListenerNames()),
                implode(PHP_EOL.'- ', $classes)
            ),
            self::ERROR_COULD_NOT_DETECT_LISTENER_CLASS
        );
    }
}
