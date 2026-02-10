<?php

declare(strict_types=1);

namespace Application\Changelog\Event;

use Application\EventHandler\Eventables\BaseEventableEvent;

class ChangelogCommittedEvent extends BaseEventableEvent
{
    public const string EVENT_NAME = 'ChangelogCommitted';

    public function getName() : string
    {
        return self::EVENT_NAME;
    }
}
