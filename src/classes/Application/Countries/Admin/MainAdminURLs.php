<?php

declare(strict_types=1);

namespace Application\Countries\Admin;

use Application\Countries\Admin\Screens\CountriesArea;
use Application\Countries\Admin\Screens\Mode\CreateScreen;
use Application\Countries\Admin\Screens\Mode\ListScreen;
use UI\AdminURLs\AdminURL;
use UI\AdminURLs\AdminURLInterface;

class MainAdminURLs
{
    public function list() : AdminURLInterface
    {
        return $this
            ->area()
            ->mode(ListScreen::URL_NAME);
    }

    public function create() : AdminURLInterface
    {
        return $this
            ->area()
            ->mode(CreateScreen::URL_NAME);
    }

    public function area() : AdminURLInterface
    {
        return AdminURL::create()
            ->area(CountriesArea::URL_NAME);
    }
}
