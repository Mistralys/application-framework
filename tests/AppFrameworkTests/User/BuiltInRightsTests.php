<?php

declare(strict_types=1);

namespace AppFrameworkTests\User;

use Application;
use Application\AppFactory;
use Application_Session_AuthTypes_NoneInterface;
use Application_User;
use Mistralys\AppFrameworkTests\TestClasses\UserTestCase;
use TestDriver_Session;

/**
 * @see TestDriver_Session
 * @see \TestDriver_User
 */
final class BuiltInRightsTests extends UserTestCase
{
    public function test_fixedRightsAreAvailableInTheSession() : void
    {
        $session = AppFactory::createSession();

        $this->assertInstanceOf(Application_Session_AuthTypes_NoneInterface::class, $session);
        $this->assertInstanceOf(TestDriver_Session::class, $session);
        $this->assertNotEmpty($session->fetchSimulatedRights());
        $this->assertStringContainsString(Application_User::RIGHT_LOGIN, $session->getRightsString());
    }

    public function test_builtInRights() : void
    {
        $user = Application::getUser();

        $this->assertContains(Application_User::RIGHT_LOGIN, $user->getRights());
    }
}
