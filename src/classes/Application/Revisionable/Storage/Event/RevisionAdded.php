<?php

declare(strict_types=1);

namespace Application\Revisionable\Storage\Event;
use Application_EventHandler_EventableEvent;

class Application_RevisionStorage_Event_RevisionAdded extends Application_EventHandler_EventableEvent
{
    const ARG_NUMBER = 0;
    const ARG_TIMESTAMP = 1;
    const ARG_OWNER_ID = 2;
    const ARG_OWNER_NAME = 3;
    const ARG_COMMENTS = 4;

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
