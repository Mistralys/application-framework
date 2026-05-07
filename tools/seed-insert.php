<?php
/**
 * CLI script: insert seed data into the test database.
 *
 * This is the second step of the two-step seeding process. It runs in its own
 * process so that ORM collection caches from the truncation step do not carry
 * over, eliminating the need for `resetCollection()` calls.
 *
 * Usage:
 *   php tools/seed-insert.php
 *   composer seed-tests   (runs truncate + insert in sequence)
 *
 * @package Application
 * @subpackage Tools
 * @see \Application\Bootstrap\Screen\TestSuiteBootstrap::seedSystemUsers()
 * @see \Application\Bootstrap\Screen\TestSuiteBootstrap::seedLocales()
 * @see \Application\Bootstrap\Screen\TestSuiteBootstrap::seedCountries()
 */

declare(strict_types=1);

define('APP_SEED_MODE', true);

// Requires the test bootstrap (not tools/include/cli-utilities.php) because this
// script needs APP_SEED_MODE + TestSuiteBootstrap, not the interactive CLI helpers.
require_once __DIR__ . '/../tests/bootstrap.php';

use Application\Bootstrap\Screen\TestSuiteBootstrap;

echo '- Seeding test database...' . PHP_EOL;

TestSuiteBootstrap::seedSystemUsers();
TestSuiteBootstrap::seedLocales();
TestSuiteBootstrap::seedCountries();

echo '  DONE.' . PHP_EOL;
