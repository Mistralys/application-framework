<?php

declare(strict_types=1);

namespace Application\AppSettings;

use Application\OfflineEvents\RegisterAppSettingsEvent;
use Application_EventHandler_Event;
use Application\EventHandler\OfflineEvents\BaseOfflineListener;
use AppUtils\ClassHelper;

abstract class BaseRegisterAppSettingsListener extends BaseOfflineListener
{
    public function getEventName(): string
    {
        return RegisterAppSettingsEvent::EVENT_NAME;
    }

    protected function handleEvent(Application_EventHandler_Event $event, ...$args): void
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