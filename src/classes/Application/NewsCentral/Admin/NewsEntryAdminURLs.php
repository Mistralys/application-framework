<?php

declare(strict_types=1);

namespace Application\NewsCentral\Admin;

use Application\AppFactory;
use Application\NewsCentral\Admin\Screens\ManageNewsArea;
use Application\NewsCentral\Admin\Screens\Mode\ViewArticle\ArticleSettingsSubmode;
use Application\NewsCentral\Admin\Screens\Mode\ViewArticle\ArticleStatusSubmode;
use Application\NewsCentral\Admin\Screens\Mode\ViewArticleMode;
use Application\NewsCentral\Admin\Screens\ReadNews\ReadArticleScreen;
use Application\NewsCentral\NewsCollection;
use NewsCentral\Entries\NewsEntry;
use UI\AdminURLs\AdminURL;
use UI\AdminURLs\AdminURLInterface;

class NewsEntryAdminURLs
{
    private int $entryID;

    public function __construct(NewsEntry $entry)
    {
        $this->entryID = $entry->getID();
    }

    public function status() : AdminURLInterface
    {
        return $this
            ->base()
            ->submode(ArticleStatusSubmode::URL_NAME);
    }

    public function publish() : AdminURLInterface
    {
        return $this
            ->status()
            ->bool(ArticleStatusSubmode::REQUEST_PARAM_PUBLISH, true);
    }

    public function settings() : AdminURLInterface
    {
        return $this
            ->base()
            ->submode(ArticleSettingsSubmode::URL_NAME);
    }

    public function base() : AdminURLInterface
    {
        return AdminURL::create()
            ->area(ManageNewsArea::URL_NAME)
            ->mode(ViewArticleMode::URL_NAME)
            ->int(NewsCollection::REQUEST_PRIMARY_NAME, $this->entryID);
    }

    public function read() : AdminURLInterface
    {
        return AppFactory::createNews()
            ->adminURL()
            ->read()
            ->base()
            ->mode(ReadArticleScreen::URL_NAME)
            ->int(ReadArticleScreen::REQUEST_PARAM_ARTICLE, $this->entryID);
    }
}
