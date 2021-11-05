<?php

declare(strict_types=1);

use AppUtils\NamedClosure;

class TestDriver_OfflineEvents_TestEvent_ListenerA extends Application_EventHandler_OfflineEvents_OfflineListener
{
    protected function wakeUp() : NamedClosure
    {
        return NamedClosure::fromClosure(
            Closure::fromCallable(array($this, 'callback')),
            array($this, 'callback')
        );
    }

    private function callback(TestDriver_OfflineEvents_TestEvent $event) : void
    {
        define('OFFLINE_EVENTS_LISTENER_A_TRIGGERED', true);
    }
}
