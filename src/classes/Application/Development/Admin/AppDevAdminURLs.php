<?php

declare(strict_types=1);

namespace Application\Development\Admin;

use Application\Development\Admin\Screens\DevelArea;
use Application\ErrorLog\Admin\Screens\ErrorLogMode;
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
            ->mode(ErrorLogMode::URL_NAME);
    }

    public function base() : AdminURLInterface
    {
        return AdminURL::create()
            ->area(DevelArea::URL_NAME);
    }
}
