<?php
/**
 * @package Application
 * @subpackage AppSettings
 */

declare(strict_types=1);

namespace Application\AppSettings\Events;

use Application\AppSettings\AppSettingsRegistry;
use Application\EventHandler\OfflineEvents\BaseOfflineEvent;

/**
 * This offline event is triggered to register any custom settings
 * that the application may want to expose and make editable in
 * the application settings screen.
 *
 * ## Usage
 *
 * 1. Add listeners in the folder {@see self::EVENT_NAME} in the offline event folder.
 * 2. Extend the base class {@see BaseRegisterAppSettingsListener}.
 *
 * @package Application
 * @subpackage AppSettings
 *
 * @see BaseRegisterAppSettingsListener
 */
class RegisterAppSettingsEvent extends BaseOfflineEvent
{
    public const string EVENT_NAME = 'RegisterAppSettings';

    public function getName(): string
    {
        return self::EVENT_NAME;
    }

    public function addSetting(string $name, string $type, string $description): void
    {
        $this->getRegistry()->addSetting($name, $type, $description);
    }

    public function getRegistry() : AppSettingsRegistry
    {
        return $this->getArgumentObject(0, AppSettingsRegistry::class);
    }
}
