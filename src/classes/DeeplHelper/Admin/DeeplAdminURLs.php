<?php

declare(strict_types=1);

namespace DeeplHelper\Admin;

use Application\Development\Admin\Screens\DevelArea;
use DeeplHelper\Admin\Screens\DeepLTestScreen;
use UI\AdminURLs\AdminURL;
use UI\AdminURLs\AdminURLInterface;

class DeeplAdminURLs
{
    public function testing() : AdminURLInterface
    {
        return AdminURL::create()
            ->area(DevelArea::URL_NAME)
            ->mode(DeepLTestScreen::URL_NAME);
    }
}
