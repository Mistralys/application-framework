<?php

declare(strict_types=1);

namespace NewsCentral\Entries;

use Application\Admin\Area\BaseNewsScreen;
use Application\Admin\Area\News\BaseReadNewsScreen;
use Application\Admin\Area\News\ReadNews\BaseReadArticleScreen;
use Application\AppFactory;
use Application\MarkdownRenderer;
use Application\NewsCentral\NewsCollection;
use Application\NewsCentral\NewsEntry;
use Application_Admin_ScreenInterface;
use League\CommonMark\CommonMarkConverter;

class NewsArticle extends NewsEntry
{
    public function getSynopsis(): string
    {
        return $this->getRecordStringKey(NewsCollection::COL_SYNOPSIS);
    }

    public function getArticle(): string
    {
        return $this->getRecordStringKey(NewsCollection::COL_ARTICLE);
    }

    public function setSynopsis(string $synopsis) : bool
    {
        return $this->setRecordKey(NewsCollection::COL_SYNOPSIS, $synopsis);
    }

    public function setArticle(string $article) : bool
    {
        return $this->setRecordKey(NewsCollection::COL_ARTICLE, $article);
    }

    public function renderArticle() : string
    {
        return MarkdownRenderer::create()->render($this->getArticle());
    }

    public function renderSynopsis() : string
    {
        return MarkdownRenderer::create()->render($this->getSynopsis());
    }

    public function getLiveURLRead(array $params=array()) : string
    {
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_PAGE] = BaseNewsScreen::URL_NAME;
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_MODE] = BaseReadNewsScreen::URL_NAME;
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_SUBMODE] = BaseReadArticleScreen::URL_NAME;
        $params[BaseReadArticleScreen::REQUEST_PARAM_ARTICLE] = $this->getID();

        return AppFactory::createRequest()
            ->buildURL($params);
    }
}
