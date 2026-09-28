<?php

declare(strict_types=1);

namespace AppFrameworkTests\API;

use Application\AppFactory;
use Application_ErrorLog_Log_Entry_Exception;
use Application_Exception;
use AppFrameworkTestClasses\ApplicationTestCase;
use DBHelper_Exception;
use RuntimeException;

/**
 * Tests for {@see Application_ErrorLog_Log_Entry_Exception::logThrowable()},
 * the adapter that lets the error log accept any {@see \Throwable} — not
 * just the legacy {@see Application_Exception} — and returns an opaque
 * reference safe to expose to an API client.
 *
 * @see \Application\API\BaseMethods\BaseAPIMethod::reportUnexpectedThrowable()
 */
final class ThrowableLoggingTest extends ApplicationTestCase
{
    /**
     * A plain {@see RuntimeException} (not a {@see \AppUtils\BaseException})
     * is logged with its class, code, message, and a readable trace.
     */
    public function test_logThrowable_plainException() : void
    {
        $exception = new RuntimeException('A plain runtime failure.', 40501);

        $reference = Application_ErrorLog_Log_Entry_Exception::logThrowable($exception);

        $this->assertNotEmpty($reference);

        $entry = $this->findLogEntry($reference);

        $this->assertSame(RuntimeException::class, $entry->getClassName());
        $this->assertSame(40501, $entry->getCode());
        $this->assertSame('A plain runtime failure.', $entry->getMessage());
        $this->assertTrue($entry->hasTrace(), 'A trace file must have been written for the throwable.');
        $this->assertSame(RuntimeException::class, $entry->getTrace()->getClass());
    }

    /**
     * A {@see DBHelper_Exception} (a {@see \AppUtils\BaseException}, but not
     * an {@see Application_Exception}) retains its SQL error text — carried
     * in {@see \AppUtils\BaseException::getDetails()} — in the log entry's
     * developer-info token, which is never reflected in the returned reference.
     */
    public function test_logThrowable_retainsBaseExceptionDetails() : void
    {
        $sqlDetail = 'SQL error text: Duplicate entry \'test@example.com\' for key \'email\'';

        $exception = new DBHelper_Exception('Query execution failed', $sqlDetail, 45001);

        $reference = Application_ErrorLog_Log_Entry_Exception::logThrowable($exception);

        // The opaque reference itself must never carry the SQL detail.
        $this->assertStringNotContainsString($sqlDetail, $reference);

        $entry = $this->findLogEntry($reference);

        $this->assertSame(DBHelper_Exception::class, $entry->getClassName());
        $this->assertStringContainsString($sqlDetail, $entry->getDeveloperInfo());
    }

    /**
     * For an {@see Application_Exception}, logThrowable() must delegate to
     * its own idempotent {@see Application_Exception::getLogID()} lifecycle
     * rather than logging again — calling it twice (or once directly, once
     * via logThrowable()) must return the exact same reference and must not
     * create a second log entry.
     */
    public function test_logThrowable_applicationExceptionReusesLogID() : void
    {
        $exception = new Application_Exception('A framework-native failure.', 'Developer detail.', 45002);
        $exception->disableLogging();

        $directLogID = $exception->getLogID();
        $viaAdapter = Application_ErrorLog_Log_Entry_Exception::logThrowable($exception);
        $secondCall = Application_ErrorLog_Log_Entry_Exception::logThrowable($exception);

        $this->assertSame($directLogID, $viaAdapter, 'logThrowable() must reuse the exception\'s own getLogID().');
        $this->assertSame($directLogID, $secondCall, 'Calling logThrowable() again must return the same reference.');
    }

    // region: Support methods

    private function findLogEntry(string $reference) : Application_ErrorLog_Log_Entry_Exception
    {
        $logs = AppFactory::createErrorLog()->getLogs();

        foreach($logs as $log) {
            foreach($log->getEntries() as $entry) {
                if($entry->getLogID() === $reference) {
                    $this->assertInstanceOf(Application_ErrorLog_Log_Entry_Exception::class, $entry);
                    /** @var Application_ErrorLog_Log_Entry_Exception $entry */
                    return $entry;
                }
            }
        }

        $this->fail(sprintf('No error log entry found for reference [%s].', $reference));
    }

    // endregion
}
