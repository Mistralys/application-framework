<?php

declare(strict_types=1);

use Mistralys\AppFrameworkTests\TestClasses\UserTestCase;

/**
 * These tests can only be run from the application's
 * own tests suite, because they require the testsuite
 * user class to be used.
 *
 * @see TestDriver_User
 */
final class User_SystemUsersTest extends UserTestCase
{
    public function test_createSystemUser(): void
    {
        $this->startTest('Creating a system user instance');

        $user = Application::createSystemUser();

        $this->assertInstanceOf(TestDriver_User::class, $user);
        $this->assertSame(Application::USER_ID_SYSTEM, $user->getID());
        $this->assertTrue($user->isSystemUser());
    }

    public function test_createDummyUser(): void
    {
        $user = Application::createDummyUser();

        $this->assertInstanceOf(TestDriver_User::class, $user);
        $this->assertSame(Application::USER_ID_DUMMY, $user->getID());
        $this->assertTrue($user->isSystemUser());
    }

    public function test_createUser_system(): void
    {
        $user = Application::createUser(Application::USER_ID_SYSTEM);

        $this->assertSame(Application::USER_ID_SYSTEM, $user->getID());
    }

    public function test_createUser_dummy(): void
    {
        $user = Application::createUser(Application::USER_ID_DUMMY);

        $this->assertSame(Application::USER_ID_DUMMY, $user->getID());
    }
}
