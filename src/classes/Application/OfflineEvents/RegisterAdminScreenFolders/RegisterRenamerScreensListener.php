<?php

declare(strict_types=1);

namespace Application\OfflineEvents\RegisterAdminScreenFolders;

use Application\CacheControl\BaseRegisterScreenFoldersListener;
use Application\Renamer\RenamingManager;

class RegisterRenamerScreensListener extends BaseRegisterScreenFoldersListener
{
    protected function getAdminScreenFolders(): array
    {
        return array(
            RenamingManager::getAdminScreensFolder()
        );
    }
}
