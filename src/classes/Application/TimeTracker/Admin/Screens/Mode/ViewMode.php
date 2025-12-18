<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin\Screens\Mode;

use Application\TimeTracker\Admin\Screens\Mode\ViewScreen\StatusSubmode;
use Application\TimeTracker\Admin\TimeTrackerScreenRights;
use Application\TimeTracker\Admin\TimeUIManager;
use Application\TimeTracker\Admin\Traits\ModeInterface;
use Application\TimeTracker\Admin\Traits\ModeTrait;
use Application\TimeTracker\TimeEntry;
use DBHelper\Admin\Screens\Mode\BaseRecordMode;
use UI;

/**
 * @property TimeEntry $record
 */
class ViewMode extends BaseRecordMode implements ModeInterface
{
    use ModeTrait;

    public const string URL_NAME = 'view';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getNavigationTitle(): string
    {
        return t('View');
    }

    public function getTitle(): string
    {
        return t('View a time entry');
    }

    public function getDefaultSubmode(): string
    {
        return StatusSubmode::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return TimeTrackerScreenRights::SCREEN_VIEW;
    }

    protected function _handleHelp(): void
    {
        $this->renderer->setTitle($this->record->getLabel());
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendItem($this->area->getNavigationTitle())
            ->makeLinked(TimeUIManager::getBackToListURL());

        $this->breadcrumb->appendItem($this->record->getLabel())
            ->makeLinked($this->record->adminURL()->status());
    }

    protected function _handleSubnavigation() : void
    {
        $this->subnav->addURL(t('Status'), $this->record->adminURL()->status())
            ->setIcon(UI::icon()->status());

        $this->subnav->addURL(t('Settings'), $this->record->adminURL()->settings())
            ->setIcon(UI::icon()->settings());
    }
}
