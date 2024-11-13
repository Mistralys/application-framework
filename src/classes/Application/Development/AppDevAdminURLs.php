<?php

declare(strict_types=1);

namespace Application\Development;

use Application_Admin_Area_Devel;
use Application_Admin_Area_Devel_Errorlog;
use UI\AdminURLs\AdminURL;
use UI\AdminURLs\AdminURLInterface;

class AppDevAdminURLs
{
    private static ?self $instance = null;

    public static function getInstance() : self
    {
        if(self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function errorLog() : AdminURLInterface
    {
        return $this->base()
            ->mode(Application_Admin_Area_Devel_Errorlog::URL_NAME);
    }

    public function base() : AdminURLInterface
    {
        return AdminURL::create()
            ->area(Application_Admin_Area_Devel::URL_NAME);
    }
}
