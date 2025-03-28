<?php
/**
 * @package TestDriver
 * @subpackage User
 */

declare(strict_types=1);

use Application\TimeTracker\User\TimeTrackerRightsInterface;
use Application\TimeTracker\User\TimeTrackerRightsTrait;

/**
 * @package TestDriver
 * @subpackage User
 */
class TestDriver_User extends Application_User implements TimeTrackerRightsInterface
{
    use TimeTrackerRightsTrait;

    protected function registerRoles(Application_User_Rights $manager): void
    {
    }

    protected function registerRightGroups(Application_User_Rights $manager): void
    {
        $this->registerTimeTrackerGroup($manager);
    }
}
