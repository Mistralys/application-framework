<?php

declare(strict_types=1);

namespace Application\Revisionable\Event;

use Application\Revisionable\RevisionableStatelessInterface;
use Application_EventHandler_EventableEvent;

class RevisionSelectedEvent extends Application_EventHandler_EventableEvent
{
    public const EVENT_NAME = 'RevisionSelected';

    public function getRevisionable(): RevisionableStatelessInterface
    {
        return $this->getArgumentObject(0, RevisionableStatelessInterface::class);
    }

    public function getRevision(): int
    {
        return $this->getArgumentInt(1);
    }
}