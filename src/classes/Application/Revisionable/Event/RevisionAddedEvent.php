<?php

declare(strict_types=1);

namespace Application\Revisionable\Event;

use Application\Revisionable\RevisionableStatelessInterface;
use Application\Revisionable\Storage\Event\Application_RevisionStorage_Event_RevisionAdded;
use Application_EventHandler_EventableEvent;

class RevisionAddedEvent extends Application_EventHandler_EventableEvent
{
    public function getRevisionable(): RevisionableStatelessInterface
    {
        return $this->getArgumentObject(0, RevisionableStatelessInterface::class);
    }

    public function getNumber(): int
    {
        return $this->getOriginalEvent()->getNumber();
    }

    public function getTimestamp(): int
    {
        return $this->getOriginalEvent()->getTimestamp();
    }

    public function getOwnerID(): int
    {
        return $this->getOriginalEvent()->getOwnerID();
    }

    public function getComments(): string
    {
        return $this->getOriginalEvent()->getComments();
    }

    public function getOwnerName(): string
    {
        return $this->getOriginalEvent()->getOwnerName();
    }

    public function getOriginalEvent(): Application_RevisionStorage_Event_RevisionAdded
    {
        return $this->getArgumentObject(1, Application_RevisionStorage_Event_RevisionAdded::class);
    }

    public function isCancellable(): bool
    {
        return false;
    }
}
