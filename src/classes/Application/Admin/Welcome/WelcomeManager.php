<?php

declare(strict_types=1);

namespace Application\Admin\Welcome;

use AppUtils\FileHelper\FolderInfo;

class WelcomeManager
{
    public static function getAdminScreensFolder() : FolderInfo
    {
        return FolderInfo::factory(__DIR__ . '/Screens')->requireExists();
    }
}
