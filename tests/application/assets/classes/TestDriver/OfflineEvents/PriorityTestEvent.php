<?php

declare(strict_types=1);

namespace TestDriver\OfflineEvents;

use Application\EventHandler\OfflineEvents\BaseOfflineEvent;

class PriorityTestEvent extends BaseOfflineEvent
{
    public const string EVENT_NAME = 'PriorityTest';

    protected function _getEventName(): string
    {
        return self::EVENT_NAME;
    }
}
