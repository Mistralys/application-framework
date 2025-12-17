<?php

declare(strict_types=1);

namespace TestDriver\OfflineEvents;

use Application\EventHandler\OfflineEvents\BaseOfflineEvent;

class TestEvent extends BaseOfflineEvent
{
    public const string EVENT_NAME = 'Test';

    protected function _getEventName(): string
    {
        return self::EVENT_NAME;
    }
}
