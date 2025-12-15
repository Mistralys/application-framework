<?php

declare(strict_types=1);

namespace Application\OfflineEvents\RegisterAdminScreenFolders;

use Application\CacheControl\BaseRegisterScreenFoldersListener;
use Application\Media\Collection\MediaCollection;

class RegisterMediaScreensListener extends BaseRegisterScreenFoldersListener
{
    protected function getAdminScreenFolders(): array
    {
        return array(
            MediaCollection::getAdminScreensFolder()
        );
    }
}
