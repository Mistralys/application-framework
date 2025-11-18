<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin;

use Application\Admin\BaseScreenRights;
use Application\Interfaces\Admin\AdminScreenInterface;
use Application\TimeTracker\Admin\Screens\BaseCreateScreen;
use Application\TimeTracker\Admin\Screens\BaseCreateTimeSpanScreen;
use Application\TimeTracker\Admin\Screens\BaseExportScreen;
use Application\TimeTracker\Admin\Screens\BaseImportScreen;
use Application\TimeTracker\Admin\Screens\BaseListScreen;
use Application\TimeTracker\Admin\Screens\BaseTimeTrackerArea;
use Application\TimeTracker\Admin\Screens\BaseViewScreen;
use Application\TimeTracker\Admin\Screens\ListScreen\BaseGlobalSettingsScreen;
use Application\TimeTracker\Admin\Screens\ListScreen\BaseTimeSpansListScreen;
use Application\TimeTracker\Admin\Screens\ViewScreen\BaseSettingsScreen;
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
        BaseTimeTrackerArea::class => self::SCREEN_TIME_TRACKER_AREA,
        BaseCreateScreen::class => self::SCREEN_CREATE_ENTRY,
        BaseListScreen::class => self::SCREEN_ENTRIES_LIST,
        BaseViewScreen::class => self::SCREEN_VIEW,
        BaseExportScreen::class => self::SCREEN_EXPORT,
        BaseImportScreen::class => self::SCREEN_IMPORT,
        BaseSettingsScreen::class => self::SCREEN_GLOBAL_SETTINGS,
        BaseTimeSpansListScreen::class =>  self::SCREEN_TIME_SPANS_LIST,
        BaseCreateTimeSpanScreen::class => self::SCREEN_TIME_SPANS_CREATE,
        BaseGlobalSettingsScreen::class => self::SCREEN_GLOBAL_SETTINGS,
    );

    protected function _registerRights(): void
    {
        foreach (self::SCREEN_RIGHTS as $screen => $right) {
            $this->register($screen, $right);
        }
    }
}
