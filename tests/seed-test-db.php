<?php
/**
 * Seeds system users into the test database.
 * Invoked via: composer seed-tests
 *
 * ## Constant ordering constraint
 *
 * This script intentionally uses `define()` (rather than `const`) for
 * TESTS_ROOT and APP_TESTS_RUNNING so that both constants are defined
 * *before* `tests/bootstrap.php` is required.  The bootstrap file
 * contains `const TESTS_ROOT = __DIR__` at file scope; because `const`
 * declarations are evaluated at compile time and cannot be conditionally
 * guarded, PHP would emit an E_WARNING ("Constant TESTS_ROOT already
 * defined") if this script were to use `const` instead.  Using `define()`
 * here keeps the first definition (from this script) in place and avoids
 * the compile-time conflict.
 *
 * Note: the E_WARNING from bootstrap.php's unconditional `const TESTS_ROOT`
 * is still emitted during a normal `composer seed-tests` run.  This is a
 * pre-existing issue in bootstrap.php and is tracked as technical debt
 * (see WP-003 pipeline comments).
 *
 * @package Application
 * @subpackage Tests
 */

declare(strict_types=1);

use Application\Bootstrap\Screen\TestSuiteBootstrap;

$testsRoot = __DIR__;

define('TESTS_ROOT', $testsRoot);
define('APP_TESTS_RUNNING', true);

require_once __DIR__ . '/bootstrap.php';

try
{
    TestSuiteBootstrap::seedSystemUsers();
}
catch(\Throwable $e)
{
    echo 'Seeding failed: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}

echo "Test database seeded successfully.\n";
