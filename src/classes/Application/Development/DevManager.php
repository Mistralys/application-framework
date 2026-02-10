<?php

declare(strict_types=1);

namespace Application\Development;

use Application\Development\Admin\AppDevAdminURLs;

class DevManager
{
    private static ?DevManager $instance = null;

    public static function getInstance() : self
    {
        if(self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function adminURL() : AppDevAdminURLs
    {
        return AppDevAdminURLs::getInstance();
    }
}
