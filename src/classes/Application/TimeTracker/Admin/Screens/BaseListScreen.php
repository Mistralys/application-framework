<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin\Screens;

use Application\AppFactory;
use Application\TimeTracker\Admin\Screens\ListScreen\BaseGlobalListScreen;
use Application\Traits\AllowableMigrationTrait;
use Application_Admin_Area_Mode;
use Application\TimeTracker\Admin\TimeTrackerScreenRights;

abstract class BaseListScreen extends Application_Admin_Area_Mode
{
    use AllowableMigrationTrait;

    public const URL_NAME = 'list';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getNavigationTitle(): string
    {
        return t('Overview');
    }

    public function getTitle(): string
    {
        return t('Available time entries');
    }

    public function getDefaultSubmode(): string
    {
        return BaseGlobalListScreen::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return TimeTrackerScreenRights::SCREEN_ENTRIES_LIST;
    }

    protected function _handleHelp(): void
    {
        $this->renderer->setTitle($this->getTitle());
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendArea($this->area);
    }

    protected function _handleSubnavigation(): void
    {
        $urls = AppFactory::createTimeTracker()->adminURL();

        $this->subnav->addURL(t('Global'), $urls->globalList());
        $this->subnav->addURL(t('Day'), $urls->dayList());
    }
}
