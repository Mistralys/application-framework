<?php

declare(strict_types=1);

namespace TestDriver\OfflineEvents\Test;

use Application\EventHandler\Event\EventInterface;
use Application\EventHandler\Event\StandardEvent;
use Application\EventHandler\OfflineEvents\BaseOfflineListener;
use TestDriver\OfflineEvents\TestEvent;

class ListenerA extends BaseOfflineListener
{
    public const string CONSTANT_NAME = 'OFFLINE_EVENTS_LISTENER_A_TRIGGERED';

    public function getEventName(): string
    {
        return TestEvent::EVENT_NAME;
    }

    /**
     * @param TestEvent $event
     * @param mixed ...$args
     * @return void
     */
    protected function handleEvent(EventInterface $event, ...$args): void
    {
        boot_define(self::CONSTANT_NAME, true);
    }
}
