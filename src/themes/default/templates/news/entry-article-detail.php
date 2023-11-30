<?php

declare(strict_types=1);

class template_default_news_entry_article_detail extends template_default_news_entry_article
{
    protected bool $showTitle = false;
    protected array $bodyClasses = array('news-detail');

    protected function renderBody(): string
    {
        return $this->article->renderArticle();
    }
}
