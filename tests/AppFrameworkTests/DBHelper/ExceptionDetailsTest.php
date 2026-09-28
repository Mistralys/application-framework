<?php

declare(strict_types=1);

namespace AppFrameworkTests\DBHelper;

use AppFrameworkTestClasses\ApplicationTestCase;
use DBHelper;
use DBHelper_Exception;

/**
 * Tests for the developer detail rendered onto a {@see DBHelper_Exception}
 * raised by a genuine query failure, via {@see \DBHelper\Exception\BaseErrorRenderer}.
 *
 * Confirms both halves of the diagnosable-failure contract this plan
 * depends on (see `docs/agents/project-manifest/constraints.md`):
 * the SQL error message (pre-existing) and the MySQL/PDO error code
 * (added by this WP), both present in `getDetails()`.
 */
final class ExceptionDetailsTest extends ApplicationTestCase
{
    /**
     * The SQL error message half of the contract already existed before
     * this WP — {@see \DBHelper\Exception\BaseErrorRenderer::__construct()}
     * unconditionally adds a "PDO message: ..." line. This assertion must
     * pass unmodified regardless of the error-code addition.
     */
    public function test_detailsContainSQLErrorMessage(): void
    {
        $exception = $this->triggerInvalidQueryException();

        $this->assertStringContainsString('PDO message:', $exception->getDetails());
        $this->assertStringContainsString(
            "doesnotexist_".self::MISSING_TABLE_NAME,
            $exception->getDetails()
        );
    }

    /**
     * The MySQL/PDO error code (the SQLSTATE string from {@see DBHelper::getErrorCode()})
     * must be present in the rendered developer details, labelled and
     * distinct from the free-text PDO message.
     */
    public function test_detailsContainMySQLErrorCode(): void
    {
        $exception = $this->triggerInvalidQueryException();

        $this->assertMatchesRegularExpression(
            '/MySQL error code:\s*\S+/i',
            $exception->getDetails()
        );
    }

    // region: Support methods

    private const MISSING_TABLE_NAME = 'exception_details_test_table';

    private function triggerInvalidQueryException(): DBHelper_Exception
    {
        try {
            DBHelper::fetchAll('SELECT * FROM doesnotexist_'.self::MISSING_TABLE_NAME);
        } catch (DBHelper_Exception $e) {
            return $e;
        }

        $this->fail('Querying a non-existent table must raise a DBHelper_Exception.');
    }

    // endregion
}
