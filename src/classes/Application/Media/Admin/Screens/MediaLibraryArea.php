<?php

declare(strict_types=1);

namespace Application\Media\Admin\Screens;

use Application\Admin\BaseArea;
use Application\Admin\ClassLoaderScreenInterface;
use Application\AppFactory;
use Application\Media\Admin\MediaScreenRights;
use Application\Media\Admin\Screens\Mode\ListMode;
use Application\Media\Collection\MediaCollection;
use Application\Media\MediaRightsInterface;
use UI;
use UI_Icon;

class MediaLibraryArea extends BaseArea implements ClassLoaderScreenInterface
{
    public const string URL_NAME = 'media';
    private MediaCollection $media;

    public function getRequiredRight(): string
    {
        return MediaScreenRights::SCREEN_MAIN;
    }

    public function getDefaultMode(): string
    {
        return ListMode::URL_NAME;
    }

    public function getDefaultSubscreenClass(): string
    {
        return ListMode::class;
    }

    public function getNavigationGroup(): string
    {
        return t('Manage');
    }

    public function getNavigationIcon(): UI_Icon
    {
        return UI::icon()->media();
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
            ->requireRight(MediaRightsInterface::RIGHT_ADMIN_MEDIA);
    }
}
