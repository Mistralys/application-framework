<?php

declare(strict_types=1);

namespace Application\Revisionable\Storage\Event;

use Application\EventHandler\Eventables\BaseEventableEvent;
use Application\Revisionable\Storage\BaseRevisionStorage;

class RevisionSelectedEvent extends BaseEventableEvent
{
    public const string EVENT_NAME = 'RevisionSelected';

    public function getName() : string
    {
        return self::EVENT_NAME;
    }

    public function getStorage() : BaseRevisionStorage
    {
        return $this->getArgumentObject(0, BaseRevisionStorage::class);
    }

    public function getRevision() : int
    {
        return $this->getArgumentInt(1);
    }
}
