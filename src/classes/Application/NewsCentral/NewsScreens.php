<?php

declare(strict_types=1);

namespace Application\NewsCentral;

use Application\Admin\Area\News\BaseNewsListScreen;
use Application\Admin\Area\News\BaseReadNewsScreen;
use Application\Admin\Area\News\BaseViewArticleScreen;
use Application\Admin\Area\News\ReadNews\BaseReadArticleScreen;
use Application\Admin\Area\News\ReadNews\BaseReadArticlesScreen;
use Application\Admin\BaseScreenRights;

class NewsScreens extends BaseScreenRights
{
    public const SCREEN_LIST = BaseNewsListScreen::class;
    public const SCREEN_READ_ARTICLES = BaseReadArticlesScreen::class;
    public const SCREEN_READ_ARTICLE = BaseReadArticleScreen::class;
    public const SCREEN_READ_NEWS = BaseReadNewsScreen::class;
    public const SCREEN_VIEW_ARTICLE = BaseViewArticleScreen::class;

    public const SCREEN_RIGHTS = array(
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
