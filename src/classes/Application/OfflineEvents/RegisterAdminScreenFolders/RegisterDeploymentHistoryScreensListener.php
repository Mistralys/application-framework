<?php

declare(strict_types=1);

namespace Application\OfflineEvents\RegisterAdminScreenFolders;

use Application\CacheControl\BaseRegisterScreenFoldersListener;
use Application\DeploymentRegistry\DeploymentRegistry;

class RegisterDeploymentHistoryScreensListener extends BaseRegisterScreenFoldersListener
{
    protected function getAdminScreenFolders(): array
    {
        return array(
            DeploymentRegistry::getAdminScreensFolder()
        );
    }
}
