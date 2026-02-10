<?php

declare(strict_types=1);

namespace NewsCentral\Entries;

use Application\MarkdownRenderer\MarkdownRenderer;
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
}
