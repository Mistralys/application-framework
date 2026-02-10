<?php

declare(strict_types=1);

namespace Application\NewsCentral\Admin;

use Application\Admin\BaseScreenRights;
use Application\NewsCentral\Admin\Screens\Mode\NewsListMode;
use Application\NewsCentral\Admin\Screens\Mode\ViewArticleMode;
use Application\NewsCentral\Admin\Screens\ReadNews\ArticlesListMode;
use Application\NewsCentral\Admin\Screens\ReadNews\ReadArticleScreen;
use Application\NewsCentral\Admin\Screens\ReadNewsArea;

class NewsScreens extends BaseScreenRights
{
    public const string SCREEN_LIST = NewsListMode::class;
    public const string SCREEN_READ_ARTICLES = ArticlesListMode::class;
    public const string SCREEN_READ_ARTICLE = ReadArticleScreen::class;
    public const string SCREEN_READ_NEWS = ReadNewsArea::class;
    public const string SCREEN_VIEW_ARTICLE = ViewArticleMode::class;

    public const array SCREEN_RIGHTS = array(
        self::SCREEN_LIST => NewsScreenRights::SCREEN_NEWS,
        self::SCREEN_READ_ARTICLES => NewsScreenRights::SCREEN_READ_ARTICLES,
        self::SCREEN_READ_ARTICLE => NewsScreenRights::SCREEN_READ_ARTICLE,
        self::SCREEN_READ_NEWS => NewsScreenRights::SCREEN_READ_NEWS,
        self::SCREEN_VIEW_ARTICLE => NewsScreenRights::SCREEN_VIEW_ARTICLE
    );

    protected function _registerRights(): void
    {
        foreach (self::SCREEN_RIGHTS as $screen => $right) {
            $this->register($screen, $right);
        }
    }
}
