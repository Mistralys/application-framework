<?php

declare(strict_types=1);

namespace Application\Admin\Area\News\ReadNews;

use Application\AppFactory;
use Application\NewsCentral\NewsCollection;
use Application\NewsCentral\NewsEntry;
use Application_Admin_Area_Mode_Submode;
use Application_FilterCriteria;
use UI;

class BaseReadArticlesScreen extends Application_Admin_Area_Mode_Submode
{
    public const URL_NAME = 'articles';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getTitle(): string
    {
        return t('%1$s news', $this->driver->getAppNameShort());
    }

    protected function _handleHelp(): void
    {
        $this->renderer
            ->getTitle()
            ->setText($this->getTitle())
            ->setIcon(UI::icon()->news());

        $this->renderer
            ->setAbstract(t('Read the latest %1$s news.', $this->driver->getAppNameShort()));
    }

    private int $itemsPerPage = 10;

    protected function _renderContent()
    {
        $offset = ($this->getActivePage() - 1) * $this->itemsPerPage;

        $items = AppFactory::createNews()->getFilterCriteria()
            ->selectArticles()
            ->selectPublished()
            ->setLimit($this->itemsPerPage, $offset)
            ->setOrderBy(NewsCollection::COL_DATE_CREATED, Application_FilterCriteria::ORDER_DIR_DESCENDING)
            ->getItemsObjects();

        foreach($items as $item) {
            $this->renderer->appendContent($this->renderItem($item));
        }

        // Be able to specify an icon or poster image?
        // Use pagination helper
        // Add categories for posts?

        return $this->renderer
            ->makeWithoutSidebar();
    }

    protected function getActivePage(): int
    {
        return 1;
    }

    /**
     * @param NewsEntry $entry
     * @return string
     * @see template_default_news_entry_article
     */
    private function renderItem(NewsEntry $entry) : string
    {
        return $this->ui->createTemplate('news/entry-article')
            ->setVar('article', $entry)
            ->render();
    }

    public function getNavigationTitle(): string
    {
        return t('News');
    }

    public function getDefaultAction(): string
    {
        return '';
    }
}
