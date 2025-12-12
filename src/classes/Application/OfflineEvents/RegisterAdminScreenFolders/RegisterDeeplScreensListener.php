<?php

declare(strict_types=1);

namespace Application\OfflineEvents\RegisterAdminScreenFolders;

use Application\Admin\Index\AdminScreenIndex;
use Application\CacheControl\BaseRegisterScreenFoldersListener;
use DeeplHelper;

class RegisterDeeplScreensListener extends BaseRegisterScreenFoldersListener
{
    protected function getAdminScreenFolders(): array
    {
        return array(
            DeeplHelper::getAdminScreensFolder()
        );
    }
}
