<?php

declare(strict_types=1);

use AppUtils\NamedClosure;

class TestDriver_OfflineEvents_TestEvent_ListenerB extends Application_EventHandler_OfflineEvents_OfflineListener
{
    protected function wakeUp() : NamedClosure
    {
        return NamedClosure::fromClosure(
            Closure::fromCallable(array($this, 'callback')),
            array($this, 'callback')
        );
    }

    private function callback(TestDriver_OfflineEvents_TestEvent $event, string $arg1) : void
    {
        define('OFFLINE_EVENTS_LISTENER_B_ARGUMENT', $arg1);
    }
}
