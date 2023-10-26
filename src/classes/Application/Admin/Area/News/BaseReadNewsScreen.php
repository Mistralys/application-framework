<?php

declare(strict_types=1);

namespace Application\Admin\Area\News;

use Application_Admin_Area_Mode;
use Application_Driver;
use UI;

abstract class BaseReadNewsScreen extends Application_Admin_Area_Mode
{
    public const URL_NAME = 'read';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getDefaultSubmode(): string
    {
        return '';
    }

    public function isUserAllowed(): bool
    {
        return true;
    }

    public function getNavigationTitle(): string
    {
        return t('News');
    }

    public function getTitle(): string
    {
        return t('%1$s news', $this->driver->getAppNameShort());
    }

    protected function _handleHelp(): void
    {
        $this->renderer
            ->getTitle()
            ->setText($this->getTitle())
            ->setIcon(UI::icon()->news());

        $this->renderer
            ->setAbstract(t('Read the latest %1$s news.', $this->driver->getAppNameShort()));
    }

    protected function _renderContent()
    {
        return $this->renderer
            ->makeWithoutSidebar();
    }
}
