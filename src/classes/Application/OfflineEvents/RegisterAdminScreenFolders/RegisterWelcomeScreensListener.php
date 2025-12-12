<?php

declare(strict_types=1);

namespace Application\OfflineEvents\RegisterAdminScreenFolders;

use Application\Admin\Welcome\WelcomeManager;
use Application\CacheControl\BaseRegisterScreenFoldersListener;

class RegisterWelcomeScreensListener extends BaseRegisterScreenFoldersListener
{
    protected function getAdminScreenFolders(): array
    {
        return array(
            WelcomeManager::getAdminScreensFolder()
        );
    }
}
