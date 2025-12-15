<?php

declare(strict_types=1);

namespace Application\Admin\Area;

use Application\Admin\Area\Media\BaseMediaListScreen;
use Application\AppFactory;
use Application\Media\Collection\MediaCollection;
use Application\Media\Collection\MediaFilterCriteria;
use Application\Media\Collection\MediaFilterSettings;
use Application_Admin_Area;
use Application_User;
use UI;
use UI_Icon;

abstract class BaseMediaLibraryScreen extends Application_Admin_Area
{
    public const string URL_NAME = 'media';
    private MediaCollection $media;

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

    protected function _handleActions(): bool
    {
        $this->media = AppFactory::createMediaCollection();

        return true;
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendArea($this);
    }

    protected function _handleSubnavigation(): void
    {
        $this->subnav->addURL(
            t('Media library'),
            $this->media->adminURL()->list()
        )
            ->setIcon(UI::icon()->media());

        $this->subnav->addURL(
            t('Image gallery'),
            $this->media->adminURL()->gallery()
        )
            ->setIcon(UI::icon()->image());

        $this->subnav->addURL(
            t('Settings'),
            $this->media->adminURL()->settings()
        )
            ->setIcon(UI::icon()->settings())
            ->requireRight(Application_User::RIGHT_ADMIN_MEDIA);
    }
}
