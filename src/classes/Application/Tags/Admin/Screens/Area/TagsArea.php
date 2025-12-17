<?php

declare(strict_types=1);

namespace Application\Tags\Admin\Screens\Area;

use Application\Admin\BaseArea;
use Application\Tags\Admin\Screens\Mode\ListMode;
use Application\Tags\Admin\TagScreenRights;
use UI;
use UI_Icon;

class TagsArea extends BaseArea
{
    public const string URL_NAME = 'tags';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return TagScreenRights::SCREEN_MAIN;
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
        return '';
    }

    public function getDependencies(): array
    {
        return array();
    }

    public function isCore(): bool
    {
        return true;
    }

    protected function _handleHelp(): void
    {
        $this->renderer
            ->getTitle()
            ->setIcon(UI::icon()->tags());
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
