<?php

declare(strict_types=1);

namespace NewsCentral\Entries;

use Application\NewsCentral\NewsCollection;
use Application\NewsCentral\NewsEntry;
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
        $parser = new CommonMarkConverter(array(
            'html_input' => 'strip',
            'allow_unsafe_links' => false
        ));

        return (string)$parser->convert($this->getArticle());
    }
}
