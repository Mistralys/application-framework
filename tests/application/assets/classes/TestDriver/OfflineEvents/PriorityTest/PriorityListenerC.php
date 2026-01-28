<?php

declare(strict_types=1);

namespace application\assets\classes\TestDriver\OfflineEvents\PriorityTest;

use Application\EventHandler\Event\EventInterface;
use Application\EventHandler\Event\StandardEvent;
use Application\EventHandler\OfflineEvents\BaseOfflineListener;
use TestDriver\OfflineEvents\PriorityTestEvent;
use TestDriver\OfflineEvents\TestEvent;

class PriorityListenerC extends BaseOfflineListener
{
    public const int PRIORITY = 20;

    public function getEventName(): string
    {
        return PriorityTestEvent::EVENT_NAME;
    }

    public function getPriority(): int
    {
        return self::PRIORITY;
    }

    /**
     * @param TestEvent $event
     * @param mixed ...$args
     * @return void
     */
    protected function handleEvent(EventInterface $event, ...$args): void
    {
    }
}
