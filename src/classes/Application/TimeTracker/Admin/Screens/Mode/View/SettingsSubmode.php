<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin\Screens\Mode\ViewScreen;

use Application\TimeTracker\Admin\TimeTrackerScreenRights;
use Application\TimeTracker\Admin\TimeUIManager;
use Application\TimeTracker\Admin\Traits\ViewSubmodeInterface;
use Application\TimeTracker\Admin\Traits\ViewSubmodeTrait;
use Application\TimeTracker\TimeEntry;
use Application\TimeTracker\TimeSettingsManager;
use Application\TimeTracker\TimeSpans\SidebarSpans;
use DBHelper\Admin\Screens\Submode\BaseRecordSettingsSubmode;
use DBHelper\Interfaces\DBHelperRecordInterface;
use UI\AdminURLs\AdminURLInterface;

/**
 * @property TimeEntry $record
 */
class SettingsSubmode extends BaseRecordSettingsSubmode implements ViewSubmodeInterface
{
    use ViewSubmodeTrait;

    public const string URL_NAME = 'settings';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getTitle(): string
    {
        return t('Settings');
    }

    public function getRequiredRight(): string
    {
        return TimeTrackerScreenRights::SCREEN_VIEW_SETTINGS;
    }

    public function getFeatureRights(): array
    {
        return array(
            t('Edit settings') => TimeTrackerScreenRights::SCREEN_VIEW_SETTINGS_EDIT,
        );
    }

    public function isUserAllowedEditing(): bool
    {
        return $this->user->can(TimeTrackerScreenRights::SCREEN_VIEW_SETTINGS_EDIT);
    }

    public function isEditable(): bool
    {
        return true;
    }

    public function getSettingsManager() : TimeSettingsManager
    {
        return new TimeSettingsManager($this, $this->record);
    }

    public function getSuccessMessage(DBHelperRecordInterface $record): string
    {
        return t(
            'The time entry settings have been saved successfully at %1$s.',
            sb()->time()
        );
    }

    public function getBackOrCancelURL(): AdminURLInterface
    {
        return TimeUIManager::getBackToListURL();
    }

    protected function _handleBeforeSidebar() : void
    {
        new SidebarSpans($this->record->getDate(), $this->sidebar)->addItems();
    }
}
