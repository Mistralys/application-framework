<?php

declare(strict_types=1);

namespace Application\Area;

use Application\AppFactory;
use Application_Admin_Area;
use Application\Area\Tags\BaseTagListScreen;
use UI;
use UI_Icon;

abstract class BaseTagsScreen extends Application_Admin_Area
{
    public const URL_NAME = 'tags';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getDefaultMode(): string
    {
        return BaseTagListScreen::URL_NAME;
    }

    public function getNavigationGroup(): string
    {
        return '';
    }

    public function isUserAllowed(): bool
    {
        return true;
    }

    public function getDependencies(): array
    {
        return array();
    }

    public function isCore(): bool
    {
        return true;
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendArea($this);
    }

    public function getNavigationIcon(): ?UI_Icon
    {
        return UI::icon()->tags();
    }

    public function getNavigationTitle(): string
    {
        return t('Tags');
    }

    public function getTitle(): string
    {
        return t('Tags');
    }
}
