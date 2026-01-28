<?php

declare(strict_types=1);

namespace Application\Revisionable\Event;

use Application\EventHandler\Eventables\BaseEventableEvent;
use Application\Revisionable\RevisionableInterface;

class RevisionSelectedEvent extends BaseEventableEvent
{
    public const string EVENT_NAME = 'RevisionSelected';

    public function getName() : string
    {
        return self::EVENT_NAME;
    }

    public function getRevisionable(): RevisionableInterface
    {
        return $this->getArgumentObject(0, RevisionableInterface::class);
    }

    public function getRevision(): int
    {
        return $this->getArgumentInt(1);
    }
}