<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin\Screens;

use Application\Admin\Area\BaseMode;
use Application\AppFactory;
use Application\TimeTracker\Admin\Screens\ListScreen\BaseGlobalListScreen;
use Application\TimeTracker\Admin\TimeTrackerScreenRights;
use UI;

abstract class BaseListScreen extends BaseMode
{
    public const string URL_NAME = 'list';

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
        $this->subnav->addURL(t('Time Spans'), $urls->timeSpans());
        $this->subnav->addURL(t('Settings'), $urls->globalSettings())->setIcon(UI::icon()->settings());
    }
}
