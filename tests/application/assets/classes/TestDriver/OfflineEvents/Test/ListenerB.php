<?php

declare(strict_types=1);

namespace TestDriver\OfflineEvents\Test;

use Application\EventHandler\OfflineEvents\BaseOfflineListener;
use TestDriver\OfflineEvents\TestEvent;

class ListenerB extends BaseOfflineListener
{
    public const string CONSTANT_NAME = 'OFFLINE_EVENTS_LISTENER_B_ARGUMENT';

    public function getEventName(): string
    {
        return TestEvent::EVENT_NAME;
    }

    /**
     * @param TestEvent $event
     * @param mixed ...$args
     * @return void
     */
    protected function handleEvent($event, ...$args): void
    {
        $value = $args[0] ?? 'unknown';

        boot_define(self::CONSTANT_NAME, $value);
    }
}
