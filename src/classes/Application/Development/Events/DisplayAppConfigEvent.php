<?php
/**
 * @package Application
 * @subpackage Environments
 */

declare(strict_types=1);

namespace Application\Development\Events;

use Application\AppSettings\Events\BaseRegisterAppSettingsListener;
use Application\Environments\Admin\Screens\AppConfigMode;
use Application\Environments\Events\BaseDisplayAppConfigListener;
use Application\EventHandler\OfflineEvents\BaseOfflineEvent;

/**
 * This offline event is triggered when the application's configuration
 * settings are displayed in the developer screens. It can be used to
 * add custom configuration values to the grid.
 *
 * ## Usage
 *
 * 1. Add listeners in the folder {@see self::EVENT_NAME} in the offline event folder.
 * 2. Extend the base class {@see BaseDisplayAppConfigListener}.
 *
 * @package Application
 * @subpackage Environments
 *
 * @see BaseRegisterAppSettingsListener
 */
class DisplayAppConfigEvent extends BaseOfflineEvent
{
    public const string EVENT_NAME = 'DisplayAppConfig';

    public function getName(): string
    {
        return self::EVENT_NAME;
    }

    public function getScreen() : AppConfigMode
    {
        return $this->getArgumentObject(0, AppConfigMode::class);
    }
}
