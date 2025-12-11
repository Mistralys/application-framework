<?php

declare(strict_types=1);

namespace Application\OfflineEvents\RegisterAdminScreenFolders;

use Application\AppSettings\AppSettingsRegistry;
use Application\CacheControl\BaseRegisterScreenFoldersListener;

class RegisterAppSettingScreensListener extends BaseRegisterScreenFoldersListener
{
    protected function getAdminScreenFolders(): array
    {
        return array(
            AppSettingsRegistry::getAdminScreensFolder()
        );
    }
}
