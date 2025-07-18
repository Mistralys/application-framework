<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin;

use Application\TimeTracker\User\TimeTrackerRightsInterface;

class TimeTrackerScreenRights
{
    public const SCREEN_TIME_TRACKER_AREA = TimeTrackerRightsInterface::RIGHT_VIEW_TIME_ENTRIES;
    public const SCREEN_CREATE_ENTRY = TimeTrackerRightsInterface::RIGHT_CREATE_TIME_ENTRIES;
    public const SCREEN_ENTRIES_LIST = TimeTrackerRightsInterface::RIGHT_VIEW_TIME_ENTRIES;
    public const SCREEN_VIEW = TimeTrackerRightsInterface::RIGHT_VIEW_TIME_ENTRIES;
    public const SCREEN_EXPORT = TimeTrackerRightsInterface::RIGHT_VIEW_TIME_ENTRIES;
    public const SCREEN_IMPORT = TimeTrackerRightsInterface::RIGHT_EDIT_TIME_ENTRIES;
    public const SCREEN_TIME_SPANS_EDIT = TimeTrackerRightsInterface::RIGHT_EDIT_TIME_ENTRIES;
    public const SCREEN_TIME_SPANS_LIST = TimeTrackerRightsInterface::RIGHT_VIEW_TIME_ENTRIES;
    public const SCREEN_TIME_SPANS_CREATE = TimeTrackerRightsInterface::RIGHT_CREATE_TIME_ENTRIES;
    public const SCREEN_GLOBAL_SETTINGS = TimeTrackerRightsInterface::RIGHT_EDIT_TIME_ENTRIES;
}
