<?php

declare(strict_types=1);

namespace Application\Revisionable\Storage\Event;
use Application\EventHandler\Eventables\BaseEventableEvent;

class StorageRevisionAddedEvent extends BaseEventableEvent
{
    const int ARG_NUMBER = 0;
    const int ARG_TIMESTAMP = 1;
    const int ARG_OWNER_ID = 2;
    const int ARG_OWNER_NAME = 3;
    const int ARG_COMMENTS = 4;

    public const string EVENT_NAME = 'RevisionAdded';

    public function getName() : string
    {
        return self::EVENT_NAME;
    }

    public function getNumber(): int
    {
        return $this->getArgumentInt(self::ARG_NUMBER);
    }

    public function getTimestamp(): int
    {
        return $this->getArgumentInt(self::ARG_TIMESTAMP);
    }

    public function getOwnerID(): int
    {
        return $this->getArgumentInt(self::ARG_OWNER_ID);
    }

    public function getOwnerName(): string
    {
        return $this->getArgumentString(self::ARG_OWNER_NAME);
    }

    public function getComments(): string
    {
        return $this->getArgumentString(self::ARG_COMMENTS);
    }
}
