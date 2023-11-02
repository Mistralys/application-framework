<?php

declare(strict_types=1);

namespace Application\Admin\Area;

use Application\Admin\Area\Media\BaseMediaListScreen;
use Application_Admin_Area;
use UI;
use UI_Icon;

class BaseMediaLibraryScreen extends Application_Admin_Area
{
    public const URL_NAME = 'media';

    public function getDefaultMode(): string
    {
        return BaseMediaListScreen::URL_NAME;
    }

    public function getNavigationGroup(): string
    {
        return t('Manage');
    }

    public function getNavigationIcon(): UI_Icon
    {
        return UI::icon()->media();
    }

    public function isUserAllowed(): bool
    {
        return $this->user->canViewMedia();
    }

    public function getDependencies(): array
    {
        return array();
    }

    public function isCore(): bool
    {
        return true;
    }

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getNavigationTitle(): string
    {
        return t('Media');
    }

    public function getTitle(): string
    {
        return t('Media library');
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendArea($this);
    }
}
