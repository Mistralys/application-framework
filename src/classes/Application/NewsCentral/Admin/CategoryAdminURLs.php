<?php

declare(strict_types=1);

namespace Application\NewsCentral\Admin;

use Application\NewsCentral\Admin\Screens\ManageNewsArea;
use Application\NewsCentral\Admin\Screens\Mode\ViewCategory\SettingsSubmode;
use Application\NewsCentral\Admin\Screens\Mode\ViewCategoryMode;
use Application\NewsCentral\Categories\CategoriesCollection;
use Application\NewsCentral\Categories\Category;
use UI\AdminURLs\AdminURL;
use UI\AdminURLs\AdminURLInterface;

class CategoryAdminURLs
{
    private int $categoryID;

    public function __construct(Category $category)
    {
        $this->categoryID = $category->getID();
    }

    public function settings() : AdminURLInterface
    {
        return $this
            ->base()
            ->submode(SettingsSubmode::URL_NAME);
    }

    public function base() : AdminURLInterface
    {
        return AdminURL::create()
            ->area(ManageNewsArea::URL_NAME)
            ->mode(ViewCategoryMode::URL_NAME)
            ->int(CategoriesCollection::REQUEST_PRIMARY_NAME, $this->categoryID);
    }
}
