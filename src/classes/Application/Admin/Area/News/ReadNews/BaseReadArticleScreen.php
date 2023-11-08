<?php

declare(strict_types=1);

namespace Application\Admin\Area\News\ReadNews;

use Application\AppFactory;
use Application_Admin_Area_Mode_Submode;
use NewsCentral\Entries\NewsArticle;
use UI;

class BaseReadArticleScreen extends Application_Admin_Area_Mode_Submode
{
    public const URL_NAME = 'article';
    public const REQUEST_PARAM_ARTICLE = 'id';
    private NewsArticle $article;

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getNavigationTitle(): string
    {
        return t('Article');
    }

    public function getTitle(): string
    {
        return $this->article->getLabel();
    }

    public function getDefaultAction(): string
    {
        return '';
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendItem($this->article->getLabel())
            ->makeLinked($this->article->getLiveURLRead());
    }

    protected function _handleHelp(): void
    {
        $this->renderer
            ->getTitle()
            ->setText($this->getTitle())
            ->setIcon(UI::icon()->news());
    }

    protected function _handleActions(): bool
    {
        $id = (int)$this->request->getParam(self::REQUEST_PARAM_ARTICLE);
        $collection = AppFactory::createNews();

        if(!$collection->idExists($id)) {
            $this->redirectWithErrorMessage(
                t('No such news article found.'),
                $collection->getLiveReadURL()
            );
        }

        $this->article = $collection->getByID($id);

        return true;
    }

    protected function _renderContent()
    {
        return $this->renderer
            ->appendContent($this->ui->createTemplate('news/entry-article-detail')
                ->setVar('article', $this->article)
            );
    }
}
