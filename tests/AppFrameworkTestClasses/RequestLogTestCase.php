<?php

declare(strict_types=1);

namespace Mistralys\AppFrameworkTests\TestClasses;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\AppFactory;

class RequestLogTestCase extends ApplicationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        AppFactory::createRequestLog()->clearAllLogs();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        AppFactory::createRequestLog()->clearAllLogs();
    }
}
