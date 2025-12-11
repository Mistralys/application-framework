<?php

declare(strict_types=1);

namespace Application\AppSettings;

use Application\OfflineEvents\RegisterAppSettingsEvent;
use Application_EventHandler_Event;
use Application_EventHandler_OfflineEvents_OfflineListener;
use AppUtils\ClassHelper;

abstract class BaseRegisterAppSettingsListener extends Application_EventHandler_OfflineEvents_OfflineListener
{
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