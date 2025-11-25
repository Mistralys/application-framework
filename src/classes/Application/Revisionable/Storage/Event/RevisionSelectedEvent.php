<?php

declare(strict_types=1);

namespace Application\Revisionable\Storage\Event;

use Application\Revisionable\Storage\BaseRevisionStorage;
use Application_EventHandler_EventableEvent;

class RevisionSelectedEvent extends Application_EventHandler_EventableEvent
{
    public const EVENT_NAME = 'RevisionSelected';

    public function getStorage() : BaseRevisionStorage
    {
        return $this->getArgumentObject(0, BaseRevisionStorage::class);
    }

    public function getRevision() : int
    {
        return $this->getArgumentInt(1);
    }
}
