<?php

declare(strict_types=1);

namespace Mistralys\AppFrameworkTests\TestClasses;

use Application;
use Application_User;

abstract class UserTestCase extends ApplicationTestCase
{
    protected Application_User $user;

    protected function setUp(): void
    {
        parent::setUp();

        //$this->enableLogging();

        Application::log('Tests | Set up');

        $this->startTransaction();

        $this->user = Application::getUser();

        // Ensure we start with a pristine user instance.
        $this->user->clearCache();
    }
}
