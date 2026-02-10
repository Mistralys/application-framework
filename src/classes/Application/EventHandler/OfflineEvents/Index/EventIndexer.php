<?php

declare(strict_types=1);

namespace Application\EventHandler\OfflineEvents\Index;

use Application_Interfaces_Loggable;
use Application_Traits_Loggable;

class EventIndexer implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    private bool $indexed = false;
    private string $logIdentifier;

    private static ?EventIndexer $instance = null;
    private int $countEvents = 0;
    private int $countListeners = 0;

    public static function getInstance() : EventIndexer
    {
        if(!isset(self::$instance)) {
            self::$instance = new EventIndexer();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->logIdentifier = 'OfflineEvents | EventIndexer';
    }

    public function getLogIdentifier(): string
    {
        return $this->logIdentifier;
    }

    public function index() : void
    {
        if($this->indexed) {
            return;
        }

        $this->indexed = true;

        $this->logHeader('Indexing offline events');

        EventIndex::getIndexFile()->putStatements('return '.var_export($this->serialize(), true).';');
    }

    public function countEvents() : int
    {
        return $this->countEvents;
    }

    public function countListeners() : int
    {
        return $this->countListeners;
    }

    /**
     * @return array<string|int, mixed>
     */
    public function serialize() : array
    {
        $classFinder = new EventClassFinder();
        $listenerFinder = new ListenerClassFinder();

        $result = array(
            EventIndex::KEY_EVENTS => $classFinder->serialize(),
            EventIndex::KEY_LISTENERS => $listenerFinder->serialize(),
        );

        $this->countEvents = $classFinder->countRecords();
        $this->countListeners = $listenerFinder->countRecords();

        return $result;
    }
}
