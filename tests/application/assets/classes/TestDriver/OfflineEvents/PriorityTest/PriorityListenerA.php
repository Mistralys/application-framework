<?php

declare(strict_types=1);

namespace TestDriver\OfflineEvents\PriorityTest;

use Application_EventHandler_Event;
use Application_EventHandler_OfflineEvents_OfflineListener;
use TestDriver\OfflineEvents\TestEvent;

class PriorityListenerA extends Application_EventHandler_OfflineEvents_OfflineListener
{
    public const PRIORITY = 10;

    public function getPriority(): int
    {
        return self::PRIORITY;
    }

    /**
     * @param TestEvent $event
     * @param mixed ...$args
     * @return void
     */
    protected function handleEvent(Application_EventHandler_Event $event, ...$args): void
    {
    }
}
