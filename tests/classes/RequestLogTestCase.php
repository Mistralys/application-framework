<?php

declare(strict_types=1);

namespace Mistralys\AppFrameworkTests\TestClasses;

use Application;

class RequestLogTestCase extends ApplicationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Application::createRequestLog()->clearAllLogs();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Application::createRequestLog()->clearAllLogs();
    }
}
