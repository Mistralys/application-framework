<?php

declare(strict_types=1);

namespace Application\NewsCentral\Admin\Screens;

use Application\Admin\BaseArea;
use Application\AppFactory;
use Application\NewsCentral\Admin\NewsScreenRights;
use Application\NewsCentral\Admin\Screens\ReadNews\ArticlesListMode;
use UI;
use UI_Icon;

class ReadNewsArea extends BaseArea
{
    public const string URL_NAME = 'read-news';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getNavigationGroup(): string
    {
        return t('News');
    }

    public function getNavigationIcon(): UI_Icon
    {
        return UI::icon()->news();
    }

    public function getDefaultMode(): string
    {
        return ArticlesListMode::URL_NAME;
    }

    public function getDefaultSubscreenClass(): string
    {
        return ArticlesListMode::class;
    }

    public function getRequiredRight(): string
    {
        return NewsScreenRights::SCREEN_READ_NEWS;
    }

    public function isCore(): bool
    {
        return true;
    }

    public function getDependencies(): array
    {
        return array();
    }

    public function getNavigationTitle(): string
    {
        return t('News');
    }

    public function getTitle(): string
    {
        return t('%1$s news', $this->driver->getAppNameShort());
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->clearItems();

        $this->breadcrumb->appendItem($this->getNavigationTitle())
            ->makeLinked(AppFactory::createNews()->adminURL()->read()->list());
    }

    protected function _handleSubnavigation(): void
    {
        $this->subnav->clearItems();
    }
}
