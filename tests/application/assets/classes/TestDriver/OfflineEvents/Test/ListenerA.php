<?php

declare(strict_types=1);

namespace TestDriver\OfflineEvents\Test;

use Application_EventHandler_Event;
use Application_EventHandler_OfflineEvents_OfflineListener;
use TestDriver\OfflineEvents\TestEvent;

class ListenerA extends Application_EventHandler_OfflineEvents_OfflineListener
{
    public const CONSTANT_NAME = 'OFFLINE_EVENTS_LISTENER_A_TRIGGERED';

    /**
     * @param TestEvent $event
     * @param mixed ...$args
     * @return void
     */
    protected function handleEvent(Application_EventHandler_Event $event, ...$args): void
    {
        define(self::CONSTANT_NAME, true);
    }
}
