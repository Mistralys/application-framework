<?php

declare(strict_types=1);

namespace Application\NewsCentral\Admin;

use Application\NewsCentral\Admin\Screens\ManageNewsArea;
use Application\NewsCentral\Admin\Screens\Mode\CategoriesListMode;
use Application\NewsCentral\Admin\Screens\Mode\CreateCategoryMode;
use UI\AdminURLs\AdminURL;
use UI\AdminURLs\AdminURLInterface;

class CategoriesAdminURLs
{
    public function list() : AdminURLInterface
    {
        return $this
            ->base()
            ->mode(CategoriesListMode::URL_NAME);
    }

    public function create() : AdminURLInterface
    {
        return $this
            ->base()
            ->mode(CreateCategoryMode::URL_NAME);
    }

    public function base() : AdminURLInterface
    {
        return AdminURL::create()
            ->area(ManageNewsArea::URL_NAME);
    }
}
