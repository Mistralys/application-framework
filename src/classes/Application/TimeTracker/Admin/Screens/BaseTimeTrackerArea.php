<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin\Screens;

use Application\Admin\BaseArea;
use Application\TimeTracker\Admin\TimeTrackerScreenRights;
use UI;
use UI_Icon;

abstract class BaseTimeTrackerArea extends BaseArea
{
    public const string URL_NAME = 'time-tracker';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getNavigationTitle(): string
    {
        return t('Time Tracker');
    }

    public function getTitle(): string
    {
        return t('Time Tracker');
    }

    public function getDefaultMode(): string
    {
        return BaseListScreen::URL_NAME;
    }

    public function getDefaultSubscreenClass(): string
    {
        return BaseListScreen::class;
    }

    public function getNavigationGroup(): string
    {
        return t('Manage');
    }

    public function getNavigationIcon(): ?UI_Icon
    {
        return UI::icon()->timeTracker();
    }

    public function getDependencies(): array
    {
        return array();
    }

    public function isCore(): bool
    {
        return false;
    }

    public function getRequiredRight(): string
    {
        return TimeTrackerScreenRights::SCREEN_TIME_TRACKER_AREA;
    }

    protected function _handleHelp(): void
    {
        $this->renderer->getTitle()->setIcon($this->getNavigationIcon());
    }
}
