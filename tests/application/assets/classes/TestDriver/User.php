<?php
/**
 * @package TestDriver
 * @subpackage User
 */

declare(strict_types=1);

use Application\API\User\APIRightsInterface;
use Application\API\User\APIRightsTrait;
use Application\TimeTracker\User\TimeTrackerRightsInterface;
use Application\TimeTracker\User\TimeTrackerRightsTrait;
use TestDriver\API\TestAPIKeyMethodWithRight;

/**
 * @package TestDriver
 * @subpackage User
 */
class TestDriver_User extends Application_User implements TimeTrackerRightsInterface, APIRightsInterface
{
    use TimeTrackerRightsTrait;
    use APIRightsTrait;

    protected function registerRoles(Application_User_Rights $manager): void
    {
    }

    public const string RIGHT_GRANTS_TEST_API_METHOD = 'TestGrantsAPIMethodRight';
    public const string GROUP_TEST_API_METHODS = 'TestAPIMethodRights';

    protected function registerRightGroups(Application_User_Rights $manager): void
    {
        $this->registerTimeTrackerGroup($manager);
        $this->registerAPIClientsGroup($manager);
        $this->registerTestAPIMethodGroup($manager);
    }

    private function registerTestAPIMethodGroup(Application_User_Rights $manager) : void
    {
        $manager->registerGroup(
            self::GROUP_TEST_API_METHODS,
            'Test API Method Rights',
            $this->registerTestAPIMethodRights(...)
        );
    }

    private function registerTestAPIMethodRights(Application_User_Rights_Group $group) : void
    {
        $group->registerRight(
            TestAPIKeyMethodWithRight::TEST_RIGHT,
            'Test API method right'
        );

        $group->registerRight(
            self::RIGHT_GRANTS_TEST_API_METHOD,
            'Grants test API method right'
        )->grantRight(TestAPIKeyMethodWithRight::TEST_RIGHT);
    }
}
