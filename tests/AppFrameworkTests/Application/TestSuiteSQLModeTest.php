<?php

declare(strict_types=1);

namespace AppFrameworkTests\Application;

use AppFrameworkTestClasses\ApplicationTestCase;
use DBHelper;

/**
 * Verifies that {@see \Application\Bootstrap\Screen\TestSuiteBootstrap::configureDatabase()}
 * brings the PHPUnit `tests` connection's SQL mode into parity with the
 * devel environment, specifically that it includes `ONLY_FULL_GROUP_BY`.
 *
 * Without this parity, a query that selects a column which is neither
 * aggregated nor part of the `GROUP BY` clause can silently pass any
 * consumer of this test harness while failing with MySQL error 1055
 * ("... isn't in GROUP BY") on every dev/live environment.
 *
 * @see \Application\Bootstrap\Screen\Screen The equivalent devel-environment statement.
 */
final class TestSuiteSQLModeTest extends ApplicationTestCase
{
    public function test_sessionSQLModeIncludesOnlyFullGroupBy(): void
    {
        $sqlMode = (string)DBHelper::fetchKey(
            'sql_mode',
            'SELECT @@SESSION.sql_mode AS sql_mode'
        );

        $this->assertStringContainsString(
            'ONLY_FULL_GROUP_BY',
            $sqlMode,
            'The PHPUnit tests connection session sql_mode must include ONLY_FULL_GROUP_BY to match the dev/live environments.'
        );
    }
}
