<?php
/**
 * @package Time Tracker
 * @subpackage User
 */

declare(strict_types=1);

namespace Application\TimeTracker\User;

use Application_User_Interface;

/**
 * Interface for the rights of the Time Tracker module.
 *
 * A trait is available to implement it in the application's
 * user class: {@see TimeTrackerRightsTrait}.
 *
 * @package Time Tracker
 * @subpackage User
 *
 * @see TimeTrackerRightsTrait
 */
interface TimeTrackerRightsInterface extends Application_User_Interface
{
    public const GROUP_TIME_TRACKING = 'TimeTracking';

    public const RIGHT_VIEW_TIME_ENTRIES = 'ViewTimeEntries';
    public const RIGHT_EDIT_TIME_ENTRIES = 'EditTimeEntries';
    public const RIGHT_DELETE_TIME_ENTRIES = 'DeleteTimeEntries';
    public const RIGHT_CREATE_TIME_ENTRIES = 'ViewTimeFilters';
}
