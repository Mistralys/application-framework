<?php

declare(strict_types=1);

namespace testsuites\RequestLogTests;

use Application;
use Mistralys\AppFrameworkTests\TestClasses\RequestLogTestCase;

class BrowseLogTest extends RequestLogTestCase
{
    /**
     * Ensure that after writing a log file to disk,
     * it is possible to browse there using the
     * request log's interface.
     */
    public function test_browseToLogFile() : void
    {
        // Add a log message and write it to disk
        $logPath = Application::getLogger()
            ->clearLog()
            ->log('Log message')
            ->write()
            ->getSidecarPath();

        $year = (int)date('Y');
        $month = (int)date('m');
        $day = (int)date('d');
        $hour = (int)date('H');

        $log = Application::createRequestLog();
        $this->assertTrue($log->hasYearNumber($year));

        $yearLog = $log->getYearByNumber($year);
        $this->assertTrue($yearLog->hasMonthNumber($month));

        $monthLog = $yearLog->getMonthByNumber($month);
        $this->assertTrue($monthLog->hasDayNumber($day));

        $dayLog = $monthLog->getDayByNumber($day);
        $this->assertTrue($dayLog->hasHourNumber($hour));

        $hourLog = $dayLog->getHourByNumber($hour);

        $files = $hourLog->getFiles();

        $this->assertCount(1, $files);
        $this->assertTrue($hourLog->hasFiles());
        $this->assertSame($logPath, $files[0]->getFilePath());

        $info = $files[0]->getFileInfo();
        $this->assertSame(Application::isSessionSimulated(), $info->isSimulatedSession());
    }
}
