<?php

declare(strict_types=1);

class RequestLogTestCase extends ApplicationTestCase
{
    protected function setUp() : void
    {
        parent::setUp();

        Application::createRequestLog()->clearAllLogs();
    }

    protected function tearDown() : void
    {
        parent::tearDown();

        Application::createRequestLog()->clearAllLogs();
    }
}
