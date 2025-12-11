<?php

declare(strict_types=1);

namespace Application\OfflineEvents\RegisterAdminScreenFolders;

use Application\Admin\Index\AdminScreenIndex;
use Application\CacheControl\BaseRegisterScreenFoldersListener;

class RegisterAdminIndexScreensListener extends BaseRegisterScreenFoldersListener
{
    protected function getAdminScreenFolders(): array
    {
        return array(
            AdminScreenIndex::getAdminScreensFolder()
        );
    }
}
