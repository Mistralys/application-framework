<?php

declare(strict_types=1);

namespace testsuites\RequestLogTests;

use Application;
use Application_RequestLog_LogInfo;
use Application_RequestLog_LogWriter;
use AppUtils\Microtime;
use Mistralys\AppFrameworkTests\TestClasses\RequestLogTestCase;

class LogNameTest extends RequestLogTestCase
{
    /**
     *
     */
    public function test_createName() : void
    {
        $time = new Microtime('2022-02-01 10:40:25.555555');

        $writer = new Application_RequestLog_LogWriter(Application::getLogger());
        $writer->setTime($time);
        $writer->write();

        $sessionID = $writer->getSessionID();
        $requestID = $writer->getRequestID();
        $info = new Application_RequestLog_LogInfo($writer->getSidecarPath());

        $this->assertSame($sessionID, $info->getSessionID());
        $this->assertSame($requestID, $info->getRequestID());
        $this->assertSame(40, $info->getMinutes());
        $this->assertSame(25, $info->getSeconds());
        $this->assertSame(555555, $info->getMicroseconds());
        $this->assertSame($writer->getDuration(), $info->getDuration());
    }
}
