<?php

declare(strict_types=1);

namespace Application\EventHandler\OfflineEvents\Index;

use Application\Application;
use Application\EventHandler\OfflineEvents\OfflineEventException;
use Application\EventHandler\OfflineEvents\OfflineEventInterface;
use Application\EventHandler\OfflineEvents\OfflineEventListenerInterface;
use Application_Interfaces_Loggable;
use Application_Traits_Loggable;
use AppUtils\FileHelper\PHPFile;

class EventIndex implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    public const string KEY_EVENTS = 'events';
    public const string KEY_LISTENERS = 'listeners';
    private static ?PHPFile $indexFile = null;

    /**
     * @var array<string,class-string<OfflineEventInterface>>
     */
    private array $events = array();

    /**
     * @var array<string,class-string<OfflineEventListenerInterface>[]>
     */
    private array $listeners = array();

    private bool $loaded = false;

    private static ?EventIndex $instance = null;
    private string $logIdentifier;

    public static function getInstance() : EventIndex
    {
        if(!isset(self::$instance)) {
            self::$instance = new EventIndex();
        }

        return self::$instance;
    }

    public static function getIndexFile() : PHPFile
    {
        if(!isset(self::$indexFile)) {
            self::$indexFile = PHPFile::factory(Application::getStorageSubfolderPath('events').'/offline-events-index.php');
        }

        return self::$indexFile;
    }

    public function __construct()
    {
        $this->logIdentifier = 'OfflineEvents | EventIndex';
    }

    public function getLogIdentifier(): string
    {
        return $this->logIdentifier;
    }

    /**
     * @param string $eventName
     * @return class-string<OfflineEventInterface>|NULL
     */
    public function getEventClass(string $eventName) : ?string
    {
        $this->load();

        if(isset($this->events[$eventName])) {
            return $this->events[$eventName];
        }

        return null;
    }

    /**
     * @param string|OfflineEventInterface $event
     * @return class-string<OfflineEventListenerInterface>[]
     */
    public function getListenerClasses(string|OfflineEventInterface $event) : array
    {
        $this->load();

        if($event instanceof OfflineEventInterface) {
            $eventName = $event->getName();
        } else {
            $eventName = $event;
        }

        return $this->listeners[$eventName] ?? array();
    }

    private function load() : void
    {
        if($this->loaded) {
            return;
        }

        $this->loaded = true;

        $file = self::getIndexFile();
        if(!$file->exists()) {
            $this->log(sprintf('IGNORE | The index file does not exist in path [%s].', $file->getPath()));
            return;
        }

        $index = include $file->getPath();

        if(
            !is_array($index)
            ||
            !isset($index[self::KEY_EVENTS], $index[self::KEY_LISTENERS])
            ||
            !is_array($index[self::KEY_EVENTS])
            ||
            !is_array($index[self::KEY_LISTENERS])
        ) {
            throw new OfflineEventException(
                'The offline event index file is invalid.',
                sprintf(
                    'The file [%s] did not return a valid array when included.',
                    self::getIndexFile()->getPath()
                ),
                OfflineEventException::ERROR_INDEX_FILE_INVALID
            );
        }

        foreach($index[self::KEY_EVENTS] as $eventName => $eventClass) {
            if(is_a($eventClass, OfflineEventInterface::class, true)) {
                $this->events[(string)$eventName] = $eventClass;
            }
        }

        foreach($index[self::KEY_LISTENERS] as $eventName => $listenerClasses) {
            $validListeners = array();
            foreach($listenerClasses as $listenerClass) {
                if(is_a($listenerClass, OfflineEventListenerInterface::class, true)) {
                    $validListeners[] = $listenerClass;
                }
            }

            $this->listeners[(string)$eventName] = $validListeners;
        }
    }
}
