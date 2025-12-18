<?php

declare(strict_types=1);

namespace Application\NewsCentral\Admin;

use Application\NewsCentral\Admin\Screens\ManageNewsArea;
use Application\NewsCentral\Admin\Screens\Mode\CreateAlertScreen;
use Application\NewsCentral\Admin\Screens\Mode\CreateArticleScreen;
use Application\NewsCentral\Admin\Screens\Mode\NewsListMode;
use UI\AdminURLs\AdminURL;
use UI\AdminURLs\AdminURLInterface;

class ManageNewsAdminURLs
{
    public function list() : AdminURLInterface
    {
        return $this
            ->base()
            ->mode(NewsListMode::URL_NAME);
    }

    public function createArticle() : AdminURLInterface
    {
        return $this
            ->base()
            ->mode(CreateArticleScreen::URL_NAME);
    }

    public function createAlert() : AdminURLInterface
    {
        return $this
            ->base()
            ->mode(CreateAlertScreen::URL_NAME);
    }

    public function base() : AdminURLInterface
    {
        return AdminURL::create()
            ->area(ManageNewsArea::URL_NAME);
    }
}
