<?php

declare(strict_types=1);

namespace Application\Revisionable\Event;

use Application\EventHandler\Eventables\BaseEventableEvent;
use Application\Revisionable\RevisionableInterface;

class RevisionAddedEvent extends BaseEventableEvent
{
    public const string EVENT_NAME = 'RevisionAdded';

    public function getName() : string
    {
        return self::EVENT_NAME;
    }

    public function getRevisionable(): RevisionableInterface
    {
        return $this->getArgumentObject(0, RevisionableInterface::class);
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

    public function getOriginalEvent(): RevisionAddedEvent
    {
        return $this->getArgumentObject(1, RevisionAddedEvent::class);
    }

    public function isCancellable(): bool
    {
        return false;
    }
}
