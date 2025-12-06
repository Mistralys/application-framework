<?php

declare(strict_types=1);

namespace Application\Revisionable\Event;

use Application\Revisionable\RevisionableInterface;
use Application_EventHandler_EventableEvent;

class BeforeSaveEvent extends Application_EventHandler_EventableEvent
{
    public function getRevisionable() : RevisionableInterface
    {
        return $this->getArgumentObject(0, RevisionableInterface::class);
    }

    public function isCancellable(): bool
    {
        return false;
    }
}
