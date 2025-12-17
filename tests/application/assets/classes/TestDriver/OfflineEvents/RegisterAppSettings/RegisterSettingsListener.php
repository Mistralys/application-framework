<?php

declare(strict_types=1);

namespace TestDriver\OfflineEvents\RegisterAppSettings;

use Application\AppSettings\Events\BaseRegisterAppSettingsListener;
use Application\AppSettings\Events\RegisterAppSettingsEvent;

class RegisterSettingsListener extends BaseRegisterAppSettingsListener
{
    protected function registerSettings(RegisterAppSettingsEvent $event): void
    {
        $event->addSetting(
            'test_boolean_setting',
            'boolean',
            t('Test setting: Boolean')
        );
    }
}
