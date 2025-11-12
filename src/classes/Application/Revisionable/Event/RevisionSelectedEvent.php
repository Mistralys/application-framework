<?php

declare(strict_types=1);

namespace Application\Revisionable\Event;

use Application\Revisionable\RevisionableInterface;
use Application_EventHandler_EventableEvent;

class RevisionSelectedEvent extends Application_EventHandler_EventableEvent
{
    public const string EVENT_NAME = 'RevisionSelected';

    public function getRevisionable(): RevisionableInterface
    {
        return $this->getArgumentObject(0, RevisionableInterface::class);
    }

    public function getRevision(): int
    {
        return $this->getArgumentInt(1);
    }
}