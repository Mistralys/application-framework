<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin\Screens;

use Application\Admin\Area\TimeTracker\ViewScreen\BaseStatusScreen;
use Application\AppFactory;
use Application\TimeTracker\Admin\TimeTrackerScreenRights;
use Application\TimeTracker\Admin\TimeUIManager;
use Application\TimeTracker\TimeEntry;
use Application\TimeTracker\TimeTrackerCollection;
use Application\Traits\AllowableMigrationTrait;
use DBHelper\Admin\Screens\Mode\BaseRecordMode;
use UI;
use UI\AdminURLs\AdminURLInterface;

/**
 * @property TimeEntry $record
 */
abstract class BaseViewScreen extends BaseRecordMode
{
    use AllowableMigrationTrait;

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

    protected function createCollection() : TimeTrackerCollection
    {
        return AppFactory::createTimeTracker();
    }

    public function getRecordMissingURL(): AdminURLInterface
    {
        return AppFactory::createTimeTracker()->adminURL()->list();
    }

    public function getDefaultSubmode(): string
    {
        return BaseStatusScreen::URL_NAME;
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
