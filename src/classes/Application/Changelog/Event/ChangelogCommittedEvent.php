<?php

declare(strict_types=1);

namespace Application\Changelog\Event;

use Application_EventHandler_EventableEvent;

class ChangelogCommittedEvent extends Application_EventHandler_EventableEvent
{
    public const string EVENT_NAME = 'ChangelogCommitted';
}
