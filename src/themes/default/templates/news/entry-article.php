<?php

declare(strict_types=1);

use Application\NewsCentral\NewsEntry;
use AppUtils\ConvertHelper;
use NewsCentral\Entries\NewsArticle;

class template_default_news_entry_article extends UI_Page_Template_Custom
{
    protected function generateOutput(): void
    {
        $manager = $this->article->getCategoriesManager();

        ?>
            <article class="news-article">
                <?php
                if($this->showTitle)
                {
                    ?>
                    <h4 class="news-article-title">
                        <a href="<?php echo $this->article->getLiveURLRead() ?>">
                            <?php echo $this->article->getLabel(); ?>
                        </a>
                    </h4>
                    <?php
                }
                ?>
                <div class="news-article-meta">
                    <ul>
                        <li><?php pts('By %1$s', $this->article->getAuthor()->getName()); ?></li>
                        <li><?php pts('Last modified:'); echo ConvertHelper::date2listLabel($this->article->getDateModified(), true, true) ?></li>
                        <?php
                        if($manager->hasCategories()) {
                            ?>
                            <li>
                                <?php pts('Filed under:'); echo $manager->renderCommaSeparated(); ?>
                            </li>
                            <?php
                        }
                        ?>
                    </ul>
                </div>
                <div class="news-article-body <?php echo implode(' ', $this->bodyClasses) ?>">
                    <?php echo $this->renderBody(); ?>
                </div>
            </article>
        <?php
    }

    protected function renderBody() : string
    {
        return $this->article->renderSynopsis();
    }

    protected NewsArticle $article;
    protected bool $showTitle = true;
    protected array $bodyClasses = array();

    protected function preRender(): void
    {
        $this->article = $this->getObjectVar('article', NewsArticle::class);

        $this->ui->addStylesheet('ui-news.css');
    }
}
