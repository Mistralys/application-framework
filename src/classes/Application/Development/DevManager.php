<?php

declare(strict_types=1);

namespace Application\Development;

use Application\Development\Admin\AppDevAdminURLs;
use AppUtils\FileHelper\FolderInfo;

class DevManager
{
    private static ?DevManager $instance = null;

    public static function getInstance() : static
    {
        if(self::$instance === null) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function adminURL() : AppDevAdminURLs
    {
        return AppDevAdminURLs::getInstance();
    }

    public static function getAdminScreensFolder() : FolderInfo
    {
        return FolderInfo::factory(__DIR__.'/Admin/Screens')->requireExists();
    }
}
