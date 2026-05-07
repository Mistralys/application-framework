<?php
/**
 * CLI script: truncate all tables in the test database.
 *
 * This is the first step of the two-step seeding process. It clears all base
 * tables so that the subsequent `seed-insert.php` step starts from a clean
 * slate.
 *
 * Usage:
 *   php tools/seed-truncate.php
 *   composer seed-tests   (runs truncate + insert in sequence)
 *
 * @package Application
 * @subpackage Tools
 * @see \Application\Bootstrap\Screen\TestSuiteBootstrap::truncateAllTables()
 */

declare(strict_types=1);

define('APP_SEED_MODE', true);

// Requires the test bootstrap (not tools/include/cli-utilities.php) because this
// script needs APP_SEED_MODE + TestSuiteBootstrap, not the interactive CLI helpers.
require_once __DIR__ . '/../tests/bootstrap.php';

use Application\Bootstrap\Screen\TestSuiteBootstrap;

echo '- Truncating all test database tables...' . PHP_EOL;

TestSuiteBootstrap::truncateAllTables();

echo '  DONE.' . PHP_EOL;
