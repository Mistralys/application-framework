<?php

declare(strict_types=1);

namespace TestDriver\OfflineEvents\Test;

use Application_EventHandler_OfflineEvents_OfflineListener;
use TestDriver\OfflineEvents\TestEvent;

class ListenerB extends Application_EventHandler_OfflineEvents_OfflineListener
{
    public const CONSTANT_NAME = 'OFFLINE_EVENTS_LISTENER_B_ARGUMENT';

    /**
     * @param TestEvent $event
     * @param mixed ...$args
     * @return void
     */
    protected function handleEvent($event, ...$args): void
    {
        $value = $args[0] ?? 'unknown';

        define(self::CONSTANT_NAME, $value);
    }
}
