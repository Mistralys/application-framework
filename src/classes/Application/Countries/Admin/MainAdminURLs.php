<?php

declare(strict_types=1);

namespace Application\Countries\Admin;

use Application\Countries\Admin\Screens\BaseAreaScreen;
use Application\Countries\Admin\Screens\BaseCreateScreen;
use Application\Countries\Admin\Screens\BaseListScreen;
use UI\AdminURLs\AdminURL;
use UI\AdminURLs\AdminURLInterface;

class MainAdminURLs
{
    public function list() : AdminURLInterface
    {
        return $this
            ->area()
            ->mode(BaseListScreen::URL_NAME);
    }

    public function create() : AdminURLInterface
    {
        return $this
            ->area()
            ->mode(BaseCreateScreen::URL_NAME);
    }

    public function area() : AdminURLInterface
    {
        return AdminURL::create()
            ->area(BaseAreaScreen::URL_NAME);
    }
}
