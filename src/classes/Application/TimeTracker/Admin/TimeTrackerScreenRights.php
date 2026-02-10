<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin;

use Application\Admin\BaseScreenRights;
use Application\Interfaces\Admin\AdminScreenInterface;
use Application\TimeTracker\Admin\Screens\Mode\CreateEntryMode;
use Application\TimeTracker\Admin\Screens\Mode\CreateTimeSpanMode;
use Application\TimeTracker\Admin\Screens\Mode\ExportMode;
use Application\TimeTracker\Admin\Screens\Mode\ImportMode;
use Application\TimeTracker\Admin\Screens\Mode\ListMode;
use Application\TimeTracker\Admin\Screens\Mode\ListScreen\GlobalSettingsSubmode;
use Application\TimeTracker\Admin\Screens\Mode\ListScreen\TimeSpanListSubmode;
use Application\TimeTracker\Admin\Screens\Mode\ViewMode;
use Application\TimeTracker\Admin\Screens\Mode\ViewScreen\SettingsSubmode;
use Application\TimeTracker\Admin\Screens\TimeTrackerArea;
use Application\TimeTracker\User\TimeTrackerRightsInterface;

class TimeTrackerScreenRights extends BaseScreenRights
{
    public const string SCREEN_TIME_TRACKER_AREA = TimeTrackerRightsInterface::RIGHT_VIEW_TIME_ENTRIES;
    public const string SCREEN_CREATE_ENTRY = TimeTrackerRightsInterface::RIGHT_CREATE_TIME_ENTRIES;
    public const string SCREEN_ENTRIES_LIST = TimeTrackerRightsInterface::RIGHT_VIEW_TIME_ENTRIES;
    public const string SCREEN_VIEW = TimeTrackerRightsInterface::RIGHT_VIEW_TIME_ENTRIES;
    public const string SCREEN_EXPORT = TimeTrackerRightsInterface::RIGHT_VIEW_TIME_ENTRIES;
    public const string SCREEN_IMPORT = TimeTrackerRightsInterface::RIGHT_EDIT_TIME_ENTRIES;
    public const string SCREEN_TIME_SPANS_EDIT = TimeTrackerRightsInterface::RIGHT_EDIT_TIME_ENTRIES;
    public const string SCREEN_TIME_SPANS_LIST = TimeTrackerRightsInterface::RIGHT_VIEW_TIME_ENTRIES;
    public const string SCREEN_TIME_SPANS_CREATE = TimeTrackerRightsInterface::RIGHT_CREATE_TIME_ENTRIES;
    public const string SCREEN_GLOBAL_SETTINGS = TimeTrackerRightsInterface::RIGHT_EDIT_TIME_ENTRIES;

    /**
     * @var array<class-string<AdminScreenInterface>, string>
     */
    public const array SCREEN_RIGHTS = array(
        TimeTrackerArea::class => self::SCREEN_TIME_TRACKER_AREA,
        CreateEntryMode::class => self::SCREEN_CREATE_ENTRY,
        ListMode::class => self::SCREEN_ENTRIES_LIST,
        ViewMode::class => self::SCREEN_VIEW,
        ExportMode::class => self::SCREEN_EXPORT,
        ImportMode::class => self::SCREEN_IMPORT,
        SettingsSubmode::class => self::SCREEN_GLOBAL_SETTINGS,
        TimeSpanListSubmode::class =>  self::SCREEN_TIME_SPANS_LIST,
        CreateTimeSpanMode::class => self::SCREEN_TIME_SPANS_CREATE,
        GlobalSettingsSubmode::class => self::SCREEN_GLOBAL_SETTINGS,
    );
    public const string SCREEN_VIEW_SETTINGS = TimeTrackerRightsInterface::RIGHT_VIEW_TIME_ENTRIES;
    public const string SCREEN_VIEW_SETTINGS_EDIT = TimeTrackerRightsInterface::RIGHT_EDIT_TIME_ENTRIES;
    public const string SCREEN_VIEW_STATUS = TimeTrackerRightsInterface::RIGHT_VIEW_TIME_ENTRIES;
    public const string SCREEN_LIST_DAY = TimeTrackerRightsInterface::RIGHT_VIEW_TIME_ENTRIES;

    protected function _registerRights(): void
    {
        foreach (self::SCREEN_RIGHTS as $screen => $right) {
            $this->register($screen, $right);
        }
    }
}
