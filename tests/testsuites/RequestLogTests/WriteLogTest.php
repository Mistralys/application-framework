<?php

declare(strict_types=1);

namespace testsuites\RequestLogTests;

use Application;
use AppUtils\FileHelper;
use RequestLogTestCase;

class WriteLogTest extends RequestLogTestCase
{
    public function test_write() : void
    {
        $logger = Application::getLogger();
        $logMessage = 'Log message to write';

        $logger->clearLog();
        $logger->log($logMessage);

        $path = $logger->write();

        $this->assertFileExists($path);
        $this->assertStringContainsString($logMessage, FileHelper::readContents($path));
    }
}
