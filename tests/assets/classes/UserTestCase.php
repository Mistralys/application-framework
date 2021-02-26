<?php

declare(strict_types=1);

abstract class UserTestCase extends ApplicationTestCase
{
    /**
     * @var Application_User
     */
    protected $user;

    protected function setUp(): void
    {
        //$this->enableLogging();

        Application::log('Tests | Set up');

        $this->startTransaction();

        $this->user = Application::getUser();

        // Ensure we start with a pristine user instance.
        $this->user->clearCache();
    }
}
