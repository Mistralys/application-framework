<?php

declare(strict_types=1);

namespace Application\OfflineEvents\RegisterAdminScreenFolders;

use Application\CacheControl\BaseRegisterScreenFoldersListener;
use Application\Development\DevManager;

class RegisterDevelScreensListener extends BaseRegisterScreenFoldersListener
{
    protected function getAdminScreenFolders(): array
    {
        return array(
            DevManager::getAdminScreensFolder()
        );
    }
}
