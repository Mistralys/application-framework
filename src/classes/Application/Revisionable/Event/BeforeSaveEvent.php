<?php

declare(strict_types=1);

namespace Application\Revisionable\Event;

use Application\Revisionable\RevisionableStatelessInterface;
use Application_EventHandler_EventableEvent;

class BeforeSaveEvent extends Application_EventHandler_EventableEvent
{
    public function getRevisionable() : RevisionableStatelessInterface
    {
        return $this->getArgumentObject(0, RevisionableStatelessInterface::class);
    }

    public function isCancellable(): bool
    {
        return false;
    }
}
