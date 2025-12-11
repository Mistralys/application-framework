<?php

declare(strict_types=1);

namespace Application\Development;

use AppUtils\FileHelper\FolderInfo;

class DevManager
{
    public static function getAdminScreensFolder() : FolderInfo
    {
        return FolderInfo::factory(__DIR__.'/Admin/Screens')->requireExists();
    }
}
