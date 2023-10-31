<?php

declare(strict_types=1);

use Application\NewsCentral\NewsEntry;
use AppUtils\ConvertHelper;

class template_default_news_entry_article extends UI_Page_Template_Custom
{
    protected function generateOutput(): void
    {
        ?>
            <article class="news-article">
                <h4 class="news-article-title"><?php echo $this->article->getLabel(); ?></h4>
                <div class="news-article-meta">
                    <ul>
                        <li><?php pts('By %1$s', $this->article->getAuthor()->getName()); ?></li>
                        <li><?php pts('Last modified:'); echo ConvertHelper::date2listLabel($this->article->getDateModified(), true, true) ?></li>
                    </ul>
                </div>
                <div class="news-article-body">
                <?php echo $this->article->renderArticle(); ?>
                </div>
            </article>
        <?php
    }

    private NewsEntry $article;

    protected function preRender(): void
    {
        $this->article = $this->getObjectVar('article', NewsEntry::class);

        $this->ui->addStylesheet('ui-news.css');
    }
}
