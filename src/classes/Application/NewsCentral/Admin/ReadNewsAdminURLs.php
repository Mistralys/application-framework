<?php

declare(strict_types=1);

namespace Application\NewsCentral\Admin;

use Application\NewsCentral\Admin\Screens\ManageNewsArea;
use Application\NewsCentral\Admin\Screens\ReadNews\ArticlesListMode;
use Application\NewsCentral\Admin\Screens\ReadNewsArea;
use UI\AdminURLs\AdminURL;
use UI\AdminURLs\AdminURLInterface;

class ReadNewsAdminURLs
{
    public function list() : AdminURLInterface
    {
        return $this
            ->base()
            ->mode(ArticlesListMode::URL_NAME);
    }

    public function base() : AdminURLInterface
    {
        return AdminURL::create()
            ->area(ReadNewsArea::URL_NAME);
    }
}
