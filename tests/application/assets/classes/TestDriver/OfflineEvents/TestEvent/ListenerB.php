<?php

declare(strict_types=1);

namespace TestDriver\OfflineEvents\TestEvent;

use Application_EventHandler_OfflineEvents_OfflineListener;
use AppUtils\NamedClosure;
use Closure;
use TestDriver\OfflineEvents\TestEvent;

class ListenerB extends Application_EventHandler_OfflineEvents_OfflineListener
{
    public const CONSTANT_NAME = 'OFFLINE_EVENTS_LISTENER_B_ARGUMENT';

    protected function wakeUp(): NamedClosure
    {
        return NamedClosure::fromClosure(
            Closure::fromCallable(array($this, 'callback')),
            array($this, 'callback')
        );
    }

    private function callback(TestEvent $event, string $arg1): void
    {
        define(self::CONSTANT_NAME, $arg1);
    }
}
