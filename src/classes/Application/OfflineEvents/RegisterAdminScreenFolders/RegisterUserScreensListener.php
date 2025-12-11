<?php

declare(strict_types=1);

namespace Application\OfflineEvents\RegisterAdminScreenFolders;

use Application\CacheControl\BaseRegisterScreenFoldersListener;
use Application_Users;

class RegisterUserScreensListener extends BaseRegisterScreenFoldersListener
{
    protected function getAdminScreenFolders(): array
    {
        return array(
            Application_Users::getAdminScreensFolder()
        );
    }
}
