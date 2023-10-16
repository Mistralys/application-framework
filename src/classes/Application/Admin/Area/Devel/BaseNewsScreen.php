<?php

declare(strict_types=1);

namespace Application\Admin\Area\Devel;

use Application\Admin\Area\Devel\News\BaseNewsListScreen;
use Application\AppFactory;
use Application_Admin_Area_Mode;
use UI;

abstract class BaseNewsScreen extends Application_Admin_Area_Mode
{
    public const URL_NAME = 'news';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getDefaultSubmode(): string
    {
        return BaseNewsListScreen::URL_NAME;
    }

    public function isUserAllowed(): bool
    {
        return $this->user->canViewNews();
    }

    public function getNavigationTitle(): string
    {
        return t('News');
    }

    public function getTitle(): string
    {
        return t('Application news');
    }

    protected function _handleHelp(): void
    {
        $this->renderer->getTitle()
            ->setIcon(UI::icon()->news());
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendArea($this->area);
        $this->breadcrumb->appendItem($this->getNavigationTitle())
            ->makeLinked(AppFactory::createNews()->getAdminURL());
    }
}
