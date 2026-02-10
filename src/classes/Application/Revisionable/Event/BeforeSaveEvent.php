<?php

declare(strict_types=1);

namespace Application\Revisionable\Event;

use Application\EventHandler\Eventables\BaseEventableEvent;
use Application\Revisionable\RevisionableInterface;

class BeforeSaveEvent extends BaseEventableEvent
{
    public const string EVENT_NAME = 'BeforeSave';

    public function getName() : string
    {
        return self::EVENT_NAME;
    }

    public function getRevisionable() : RevisionableInterface
    {
        return $this->getArgumentObject(0, RevisionableInterface::class);
    }

    public function isCancellable(): bool
    {
        return false;
    }
}
