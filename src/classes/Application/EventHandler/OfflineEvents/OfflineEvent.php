<?php
/**
 * @package Application
 * @subpackage Events
 */

declare(strict_types=1);

use Application\AppFactory;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\FileHelper_Exception;

/**
 * Specialized offline event class used to handle an offline
 * event that can wake up listeners and trigger them.
 *
 * @package Application
 * @subpackage Events
 */
class Application_EventHandler_OfflineEvents_OfflineEvent
{
    public const ERROR_CANNOT_FIND_CLASS_LOCATION = 97201;

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
            $eventName = $this->getEventName();

            $this->listenerFolders[] = FolderInfo::factory(sprintf(
                '%s/assets/classes/%s/OfflineEvents/%s',
                APP_ROOT,
                APP_CLASS_NAME,
                $eventName
            ));

            $this->listenerFolders[] = FolderInfo::factory(__DIR__.'/../../OfflineEvents/'.$eventName);
        }

        return $this->listenerFolders;
    }

    /**
     * Gets all listeners registered for this event.
     *
     * NOTE: This is sorted by priority and ID.
     *
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

        foreach($this->getListenerClasses() as $class)
        {
            $this->listeners[] = $this->createListener($class);
        }

        // Sort the listeners by priority and ID if no
        // priority is set.
        usort($this->listeners, static function(Application_EventHandler_OfflineEvents_OfflineListener $a, Application_EventHandler_OfflineEvents_OfflineListener $b) : int {
            $prioA = $a->getPriority();
            $prioB = $b->getPriority();

            if($prioA > 0 || $prioB > 0) {
                return ($prioA <=> $prioB) * -1;
            }

            return $a->getID() <=> $b->getID();
        });
    }

    /**
     * @return string[]
     * @throws FileHelper_Exception
     */
    public function getListenerClasses() : array
    {
        $names = array();

        foreach($this->getListenerFolders() as $folder)
        {
            if(!$folder->exists()) {
                continue;
            }

            array_push($names, ...AppFactory::findClassesInFolder($folder, true, Application_EventHandler_OfflineEvents_OfflineListener::class));
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
                $this->getEventName()
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
     * @param class-string $className
     * @return Application_EventHandler_OfflineEvents_OfflineListener
     *
     * @throws BaseClassHelperException
     * @throws Throwable
     */
    private function createListener(string $className) : Application_EventHandler_OfflineEvents_OfflineListener
    {
        return ClassHelper::requireObjectInstanceOf(
            Application_EventHandler_OfflineEvents_OfflineListener::class,
            new $className($this)
        );
    }
}
