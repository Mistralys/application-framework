<?php
/**
 * Interactive developer menu for the Application Framework.
 *
 * Presents a numbered list of common developer tasks and dispatches
 * to the appropriate Composer command or tool script via passthru().
 * The menu loops until the developer selects option 0 (Exit).
 *
 * If the `vendor/` directory is absent when the script starts, a
 * `composer install` is run automatically so the environment is always
 * bootstrapped before further actions are attempted.
 *
 * Usage:
 *   ./menu.sh        (Unix — see menu.sh wrapper)
 *   menu.cmd         (Windows — see menu.cmd wrapper)
 *   php tools/menu.php
 *
 * @package Application
 * @subpackage Tools
 */

declare(strict_types=1);

// ============================================================================
// Pre-flight: ensure Composer dependencies are installed
// ============================================================================

/**
 * Checks whether the `vendor/` directory exists relative to the project root.
 * If it is missing, runs `composer install` automatically and aborts on failure.
 *
 * @return void
 */
function ensureVendorInstalled() : void
{
    $vendorDir = __DIR__ . '/../vendor';

    if (is_dir($vendorDir))
    {
        return;
    }

    // Note: cli-utilities.php is not yet loaded at this point (it is require_once'd
    // below, after this pre-flight check completes). Use bare echo/exit here rather
    // than the shared writeln/color utilities to avoid a dependency on a file that
    // may itself depend on vendor/ being present.
    echo 'vendor/ directory not found. Running composer install...' . PHP_EOL;
    passthru('composer install', $exitCode);

    if ($exitCode !== 0)
    {
        echo 'composer install failed (exit code ' . $exitCode . '). Aborting.' . PHP_EOL;
        exit(1);
    }

    echo PHP_EOL;
}

ensureVendorInstalled();

// ============================================================================
// Bootstrap — shared CLI utilities (writeln, color, prompt)
// ============================================================================

require_once __DIR__ . '/include/cli-utilities.php';

// ============================================================================
// Menu display
// ============================================================================

/**
 * Renders the numbered developer menu to STDOUT.
 *
 * @return void
 */
function showMenu() : void
{
    writeln();
    writeln(color('=== Application Framework — Developer Menu ===', 'bold'));
    writeln();
    writeln('  ' . color('1', 'cyan') . '  Setup local environment');
    writeln('  ' . color('2', 'cyan') . '  Build');
    writeln('  ' . color('3', 'cyan') . '  Run tests');
    writeln('  ' . color('4', 'cyan') . '  Clear caches');
    writeln('  ' . color('5', 'cyan') . '  Seed test database');
    writeln('  ' . color('6', 'cyan') . '  PHPStan analysis');
    writeln();
    writeln('  ' . color('0', 'cyan') . '  Exit');
    writeln();
}

// ============================================================================
// Action dispatch
// ============================================================================

/**
 * Dispatches the user's menu choice to the appropriate command.
 *
 * Returns false when the caller should exit, true to continue looping.
 *
 * @param string $choice The raw input string entered by the user.
 * @return bool False to exit the menu loop, true to continue.
 */
function dispatchChoice(string $choice) : bool
{
    switch ($choice)
    {
        case '1':
            writeln(color('--- Setup local environment ---', 'yellow'));
            passthru('php ' . escapeshellarg(__DIR__ . '/setup-local.php'));
            break;

        case '2':
            writeln(color('--- Build ---', 'yellow'));
            passthru('composer build');
            break;

        case '3':
            $filter = prompt('Filter pattern (empty = full suite)');

            if ($filter !== '')
            {
                writeln(color('--- Tests (filter: ' . $filter . ') ---', 'yellow'));
                passthru('composer test-filter -- ' . escapeshellarg($filter));
            }
            else
            {
                writeln(color('--- Tests (full suite) ---', 'yellow'));
                passthru('composer test');
            }
            break;

        case '4':
            writeln(color('--- Clear caches ---', 'yellow'));
            passthru('composer clear-caches');
            break;

        case '5':
            writeln(color('--- Seed test database ---', 'yellow'));
            passthru('composer seed-tests');
            break;

        case '6':
            writeln(color('--- PHPStan analysis ---', 'yellow'));
            passthru('composer analyze');
            break;

        case '0':
            writeln(color('Goodbye!', 'green'));
            return false;

        default:
            writeln(color('Unknown option: ' . $choice, 'red'));
            break;
    }

    return true;
}

// ============================================================================
// Main loop
// ============================================================================

while (true)
{
    showMenu();
    $choice = prompt('Select option');

    if (!dispatchChoice($choice))
    {
        break;
    }
}
