<?php

declare(strict_types=1);

namespace Mistralys\AppFrameworkTests\TestClasses;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\Application;
use Application_User;

abstract class UserTestCase extends ApplicationTestCase
{
    protected Application_User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertFalse(Application::isAuthenticationEnabled());

        Application::log('Tests | Set up');

        $this->startTransaction();

        // In CLI mode, this will always be the system user.
        $this->user = Application::getUser();

        $this->user->getRecent()->clearHistories();

        // Ensure we start with a pristine user instance.
        $this->user->clearCache();
    }
}
