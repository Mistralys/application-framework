<?php

declare(strict_types=1);

namespace Application\Admin\Area\News;

use Application\Admin\Area\BaseMode;
use Application\Admin\Area\News\ReadNews\BaseReadArticlesScreen;
use Application\AppFactory;
use Application\NewsCentral\NewsScreenRights;

abstract class BaseReadNewsScreen extends BaseMode
{
    public const string URL_NAME = 'read';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getDefaultSubmode(): string
    {
        return BaseReadArticlesScreen::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return NewsScreenRights::SCREEN_READ_NEWS;
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
            ->makeLinked(AppFactory::createNews()->getLiveReadURL());
    }

    protected function _handleSubnavigation(): void
    {
        $this->subnav->clearItems();
    }
}
