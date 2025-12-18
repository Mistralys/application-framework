<?php

declare(strict_types=1);

namespace NewsCentral\Entries;

use Application\AppFactory;
use Application\Interfaces\Admin\AdminScreenInterface;
use Application\MarkdownRenderer;
use Application\NewsCentral\Admin\Screens\ManageNewsArea;
use Application\NewsCentral\Admin\Screens\ReadNews\ReadArticleScreen;
use Application\NewsCentral\Admin\Screens\ReadNewsArea;
use Application\NewsCentral\NewsCollection;

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
        $params[AdminScreenInterface::REQUEST_PARAM_PAGE] = ManageNewsArea::URL_NAME;
        $params[AdminScreenInterface::REQUEST_PARAM_MODE] = ReadNewsArea::URL_NAME;
        $params[AdminScreenInterface::REQUEST_PARAM_SUBMODE] = ReadArticleScreen::URL_NAME;
        $params[ReadArticleScreen::REQUEST_PARAM_ARTICLE] = $this->getID();

        return AppFactory::createRequest()
            ->buildURL($params);
    }
}
