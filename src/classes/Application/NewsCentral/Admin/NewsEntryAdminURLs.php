<?php

declare(strict_types=1);

namespace Application\NewsCentral\Admin;

use Application\NewsCentral\Admin\Screens\ManageNewsArea;
use Application\NewsCentral\Admin\Screens\Mode\ViewArticle\BaseArticleStatusScreen;
use Application\NewsCentral\Admin\Screens\Mode\ViewArticleMode;
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
            ->submode(BaseArticleStatusScreen::URL_NAME);
    }

    public function publish() : AdminURLInterface
    {
        return $this
            ->status()
            ->bool(BaseArticleStatusScreen::REQUEST_PARAM_PUBLISH, true);
    }

    public function settings() : AdminURLInterface
    {
        return $this
            ->base()
            ->submode(BaseArticleStatusScreen::URL_NAME);
    }

    public function base() : AdminURLInterface
    {
        return AdminURL::create()
            ->area(ManageNewsArea::URL_NAME)
            ->mode(ViewArticleMode::URL_NAME)
            ->int(NewsCollection::REQUEST_PRIMARY_NAME, $this->entryID);
    }
}
