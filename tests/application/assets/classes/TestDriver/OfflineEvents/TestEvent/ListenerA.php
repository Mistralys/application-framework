<?php

declare(strict_types=1);

namespace TestDriver\OfflineEvents\TestEvent;

use Application_EventHandler_OfflineEvents_OfflineListener;
use AppUtils\NamedClosure;
use Closure;

class ListenerA extends Application_EventHandler_OfflineEvents_OfflineListener
{
    public const CONSTANT_NAME = 'OFFLINE_EVENTS_LISTENER_A_TRIGGERED';

    protected function wakeUp(): NamedClosure
    {
        return NamedClosure::fromClosure(
            Closure::fromCallable(array($this, 'callback')),
            array($this, 'callback')
        );
    }

    private function callback(): void
    {
        define(self::CONSTANT_NAME, true);
    }
}
