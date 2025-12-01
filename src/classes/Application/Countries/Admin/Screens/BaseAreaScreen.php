<?php

declare(strict_types=1);

namespace Application\Countries\Admin\Screens;

use Application\Admin\BaseArea;
use Application\Countries\Rights\CountryScreenRights;
use UI;
use UI_Icon;

abstract class BaseAreaScreen extends BaseArea
{
    public const string URL_NAME = 'countries';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getNavigationTitle(): string
    {
        return t('Countries');
    }

    public function getTitle(): string
    {
        return t('Countries');
    }

    public function getDefaultMode(): string
    {
        return BaseListScreen::URL_NAME;
    }

    public function getNavigationGroup(): string
    {
        return '';
    }

    public function getNavigationIcon(): ?UI_Icon
    {
        return UI::icon()->countries();
    }

    public function getDependencies(): array
    {
        return array();
    }

    public function isCore(): bool
    {
        return true;
    }

    public function getRequiredRight(): string
    {
        return CountryScreenRights::SCREEN_AREA;
    }

    protected function _handleHelp(): void
    {
        $this->renderer->getTitle()->setIcon($this->getNavigationIcon());
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendArea($this);
    }
}
