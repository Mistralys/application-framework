<?php

declare(strict_types=1);

namespace Application\Admin\Area\News\ReadNews;

use Application\AppFactory;
use Application\Interfaces\FilterCriteriaInterface;
use Application\NewsCentral\NewsCollection;
use Application\NewsCentral\NewsEntry;
use Application\NewsCentral\NewsFilterCriteria;
use Application\NewsCentral\NewsScreenRights;
use Application\Traits\AllowableMigrationTrait;
use Application_Admin_Area_Mode_Submode;
use Application_FilterCriteria;
use AppUtils\PaginationHelper;
use UI;
use UI\PaginationRenderer;

abstract class BaseReadArticlesScreen extends Application_Admin_Area_Mode_Submode
{
    use AllowableMigrationTrait;

    public const URL_NAME = 'articles';
    public const REQUEST_PARAM_PAGE_NUMBER = 'news-page';
    private NewsFilterCriteria $filters;
    private PaginationRenderer $paginator;

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getTitle(): string
    {
        return t('%1$s news', $this->driver->getAppNameShort());
    }

    public function getRequiredRight(): string
    {
        return NewsScreenRights::SCREEN_READ_ARTICLES;
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

    protected function _handleActions(): bool
    {
        $this->filters = AppFactory::createNews()->getFilterCriteria();
        $this->paginator = $this->ui->createPagination(
            new PaginationHelper($this->filters->countUnfiltered(), $this->itemsPerPage),
            self::REQUEST_PARAM_PAGE_NUMBER,
            $this->getURL()
        )
            ->setCurrentPageFromRequest()
            ->setAdjacentPages(5);
        
        return true;
    }

    protected function _renderContent()
    {
        $pagination = null;

        if($this->paginator->getTotalPages() > 1) {
            $pagination = $this->paginator->render();
        }

        if($pagination !== null) {
            $this->renderer->appendContent($pagination);
            $this->renderer->appendContent('<hr>');
        }

        $items = $this->filters
            ->selectArticles()
            ->selectPublished()
            ->setLimitByPagination($this->paginator)
            ->setOrderBy(NewsCollection::COL_DATE_CREATED, FilterCriteriaInterface::ORDER_DIR_DESCENDING)
            ->getItemsObjects();

        foreach($items as $item) {
            $this->renderer->appendContent($this->renderItem($item));
        }

        // Be able to specify an icon or poster image?
        // Add categories for posts?

        if($pagination !== null) {
            $this->renderer->appendContent('<hr>');
            $this->renderer->appendContent($pagination);
        }

        return $this->renderer
            ->makeWithoutSidebar();
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
