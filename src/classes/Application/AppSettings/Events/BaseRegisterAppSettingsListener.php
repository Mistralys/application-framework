<?php

declare(strict_types=1);

namespace Application\AppSettings\Events;

use Application\EventHandler\Event\EventInterface;
use Application\EventHandler\Event\StandardEvent;
use Application\EventHandler\OfflineEvents\BaseOfflineListener;
use AppUtils\ClassHelper;

abstract class BaseRegisterAppSettingsListener extends BaseOfflineListener
{
    public function getEventName(): string
    {
        return RegisterAppSettingsEvent::EVENT_NAME;
    }

    protected function handleEvent(EventInterface $event, ...$args): void
    {
        $this->registerSettings(
            ClassHelper::requireObjectInstanceOf(
                RegisterAppSettingsEvent::class,
                $event
            )
        );
    }

    abstract protected function registerSettings(RegisterAppSettingsEvent $event): void;
}