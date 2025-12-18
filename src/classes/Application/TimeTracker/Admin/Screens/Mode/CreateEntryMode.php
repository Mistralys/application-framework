<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin\Screens\Mode;

use Application\TimeTracker\Admin\TimeTrackerScreenRights;
use Application\TimeTracker\Admin\TimeUIManager;
use Application\TimeTracker\Admin\Traits\ModeInterface;
use Application\TimeTracker\Admin\Traits\ModeTrait;
use Application\TimeTracker\TimeSettingsManager;
use DBHelper\Admin\Screens\Mode\BaseRecordCreateMode;
use DBHelper\Interfaces\DBHelperRecordInterface;
use UI\AdminURLs\AdminURLInterface;

class CreateEntryMode extends BaseRecordCreateMode implements ModeInterface
{
    use ModeTrait;

    public const string URL_NAME = 'create';

    public function getTitle(): string
    {
        return t('Create a time entry');
    }

    public function getSettingsManager() : TimeSettingsManager
    {
        return new TimeSettingsManager($this);
    }

    public function getSuccessMessage(DBHelperRecordInterface $record): string
    {
        return t(
            'The time entry has been added successfully at %1$s.',
            sb()->time()
        );
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendArea($this->area);

        $this->breadcrumb->appendItem($this->getNavigationTitle())
            ->makeLinked($this->createCollection()->adminURL()->list());
    }

    public function getSuccessURL(DBHelperRecordInterface $record): string|AdminURLInterface
    {
        return $this->getBackOrCancelURL();
    }

    public function getBackOrCancelURL(): string
    {
        return (string)TimeUIManager::getBackToListURL();
    }

    public function getRequiredRight(): string
    {
        return TimeTrackerScreenRights::SCREEN_CREATE_ENTRY;
    }
}
