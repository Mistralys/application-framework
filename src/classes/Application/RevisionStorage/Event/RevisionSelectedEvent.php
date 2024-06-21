<?php

declare(strict_types=1);

namespace Application\RevisionStorage\Event;

use Application_EventHandler_EventableEvent;
use Application_RevisionStorage;

class RevisionSelectedEvent extends Application_EventHandler_EventableEvent
{
    public const EVENT_NAME = 'RevisionSelected';

    public function getStorage() : Application_RevisionStorage
    {
        return $this->getArgumentObject(0, Application_RevisionStorage::class);
    }

    public function getRevision() : int
    {
        return $this->getArgumentInt(1);
    }
}
