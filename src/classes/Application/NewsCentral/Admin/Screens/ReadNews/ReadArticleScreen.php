<?php

declare(strict_types=1);

namespace Application\NewsCentral\Admin\Screens\ReadNews;

use Application\Admin\Area\BaseMode;
use Application\AppFactory;
use Application\NewsCentral\Admin\NewsScreenRights;
use Application\NewsCentral\Admin\Traits\ReadNewsModeInterface;
use Application\NewsCentral\Admin\Traits\ReadNewsModeTrait;
use NewsCentral\Entries\NewsArticle;
use UI;
use UI_Themes_Theme_ContentRenderer;

class ReadArticleScreen extends BaseMode implements ReadNewsModeInterface
{
    use ReadNewsModeTrait;

    public const string URL_NAME = 'article';
    public const string REQUEST_PARAM_ARTICLE = 'id';
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
        return t('News Article');
    }

    public function getRequiredRight(): string
    {
        return NewsScreenRights::SCREEN_READ_ARTICLE;
    }

    public function getDefaultSubmode(): string
    {
        return '';
    }

    public function getDefaultSubscreenClass(): null
    {
        return null;
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendItem($this->article->getLabel())
            ->makeLinked($this->article->adminURL()->read());
    }

    protected function _handleHelp(): void
    {
        $this->renderer
            ->getTitle()
            ->setText($this->getTitle())
            ->setIcon(UI::icon()->news())
            ->setSubline($this->article->getSynopsis())
            ->addClass('news-title');
    }

    protected function _handleActions(): bool
    {
        $id = (int)$this->request->getParam(self::REQUEST_PARAM_ARTICLE);
        $collection = AppFactory::createNews();

        if(!$collection->idExists($id)) {
            $this->redirectWithErrorMessage(
                t('No such news article found.'),
                $collection->adminURL()->read()->list()
            );
        }

        $this->article = $collection->getByID($id);

        return true;
    }

    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        return $this->renderer
            ->appendContent($this->ui->createTemplate('news/entry-article-detail')
                ->setVar('article', $this->article)
            );
    }
}
