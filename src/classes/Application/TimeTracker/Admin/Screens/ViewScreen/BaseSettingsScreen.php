<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin\Screens\ViewScreen;

use Application\AppFactory;
use Application\TimeTracker\Admin\TimeUIManager;
use Application\TimeTracker\TimeSettingsManager;
use Application\TimeTracker\TimeTrackerCollection;
use Application\TimeTracker\User\TimeTrackerRightsInterface;
use Application_Admin_Area_Mode_Submode_CollectionEdit;
use DBHelper_BaseRecord;

class BaseSettingsScreen extends Application_Admin_Area_Mode_Submode_CollectionEdit
{
    public const URL_NAME = 'settings';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getTitle(): string
    {
        return t('Settings');
    }

    public function isUserAllowedEditing(): bool
    {
        return $this->user instanceof TimeTrackerRightsInterface && $this->user->canEditTimeEntries();
    }

    public function isEditable(): bool
    {
        return true;
    }

    public function getSettingsManager() : TimeSettingsManager
    {
        return new TimeSettingsManager($this, $this->record);
    }

    public function createCollection() : TimeTrackerCollection
    {
        return AppFactory::createTimeTracker();
    }

    public function getSuccessMessage(DBHelper_BaseRecord $record): string
    {
        return t(
            'The time entry settings have been saved successfully at %1$s.',
            sb()->time()
        );
    }

    public function getBackOrCancelURL(): string
    {
        return (string)TimeUIManager::getBackToListURL();
    }
}
