<?php

declare(strict_types=1);

namespace Application\TimeTracker\User;

interface TimeTrackerRightsInterface
{
    public const RIGHTS_TIME_TRACKING = 'TimeTracking';

    public const RIGHT_VIEW_TIME_ENTRIES = 'ViewTimeEntries';
    public const RIGHT_EDIT_TIME_ENTRIES = 'EditTimeEntries';
    public const RIGHT_DELETE_TIME_ENTRIES = 'DeleteTimeEntries';
    public const RIGHT_CREATE_TIME_ENTRIES = 'ViewTimeFilters';

}
