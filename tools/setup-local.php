<?php
/**
 * Interactive local development environment setup script.
 *
 * Prompts the user for database and UI settings, generates
 * `test-db-config.php` and `test-ui-config.php` from their `.dist.php`
 * templates, creates the database if it does not exist, imports the
 * base schema, and then runs `composer seed-tests` to seed system users.
 *
 * Usage:
 *   php tools/setup-local.php
 *   composer setup
 *
 * The script is idempotent: re-running it reads existing config values as
 * defaults so the user only needs to change what has actually changed.
 *
 * @package Application
 * @subpackage Tools
 */

declare(strict_types=1);

// ============================================================================
// Bootstrap — shared CLI utilities
// ============================================================================

require_once __DIR__ . '/include/cli-utilities.php';

// ============================================================================
// Path constants
// ============================================================================

const SETUP_ROOT       = __DIR__ . '/..';
const SETUP_CONFIG_DIR = SETUP_ROOT . '/tests/application/config';

const SETUP_TPL_DB  = SETUP_CONFIG_DIR . '/test-db-config.dist.php';
const SETUP_TPL_UI  = SETUP_CONFIG_DIR . '/test-ui-config.dist.php';

const SETUP_DB_CONFIG  = SETUP_CONFIG_DIR . '/test-db-config.php';
const SETUP_UI_CONFIG  = SETUP_CONFIG_DIR . '/test-ui-config.php';

const SETUP_SQL_SCHEMA = SETUP_ROOT . '/tests/sql/testsuite.sql';

// ============================================================================
// SIGINT handler — restore terminal echo if interrupted during password input
// ============================================================================

if (function_exists('pcntl_signal'))
{
    pcntl_signal(SIGINT, static function () : void {
        shell_exec('stty echo 2>/dev/null');
        writeln();
        writeln(color('Setup interrupted.', 'yellow'));
        exit(130);
    });
    pcntl_async_signals(true);
}

// ============================================================================
// Helpers — existing config parsing (idempotency)
// ============================================================================

/**
 * Extracts a PHP constant value from a config file's raw content.
 *
 * Handles three value shapes written by this script:
 *   const NAME = 'some string';
 *   const NAME = 123;           (bare integer)
 *   const NAME = null;          (null literal)
 *
 * @param string $constant The constant name to search for.
 * @param string $content  The raw PHP source of the config file.
 * @return string The extracted value as a string, or '' when not found.
 */
function extractConstantValue(string $constant, string $content) : string
{
    // String value: const NAME = 'value';
    if (preg_match('/const\s+' . preg_quote($constant, '/') . '\s*=\s*\'(.*?)\'\s*;/s', $content, $m))
    {
        return $m[1];
    }

    // Integer value: const NAME = 123;
    if (preg_match('/const\s+' . preg_quote($constant, '/') . '\s*=\s*(\d+)\s*;/', $content, $m))
    {
        return $m[1];
    }

    // Null literal: const NAME = null;
    if (preg_match('/const\s+' . preg_quote($constant, '/') . '\s*=\s*null\s*;/i', $content, $m))
    {
        return 'null';
    }

    return '';
}

/**
 * Parses `test-db-config.php` (if it exists) and returns existing values
 * keyed by setting name, for use as interactive defaults.
 *
 * @return array<string, string>
 */
function parseExistingDbConfig() : array
{
    $defaults = array(
        'host'     => '127.0.0.1',
        'name'     => 'app_framework_testsuite',
        'user'     => 'root',
        'password' => '',
        'port'     => 'null',
    );

    if (!file_exists(SETUP_DB_CONFIG))
    {
        return $defaults;
    }

    $content = (string)file_get_contents(SETUP_DB_CONFIG);

    $host = extractConstantValue('TESTSUITE_DB_HOST', $content);
    $name = extractConstantValue('TESTSUITE_DB_NAME', $content);
    $user = extractConstantValue('TESTSUITE_DB_USER', $content);
    $pass = extractConstantValue('TESTSUITE_DB_PASSWORD', $content);
    $port = extractConstantValue('TESTSUITE_DB_PORT', $content);

    if ($host !== '') { $defaults['host'] = $host; }
    if ($name !== '') { $defaults['name'] = $name; }
    if ($user !== '') { $defaults['user'] = $user; }
    if ($pass !== '') { $defaults['password'] = $pass; }
    if ($port !== '') { $defaults['port'] = $port; }

    return $defaults;
}

/**
 * Parses `test-ui-config.php` (if it exists) and returns existing values
 * keyed by setting name, for use as interactive defaults.
 *
 * @return array<string, string>
 */
function parseExistingUiConfig() : array
{
    $defaults = array(
        'base_url' => 'http://127.0.0.1/application-framework',
        'email'    => 'name@host.domain',
    );

    if (!file_exists(SETUP_UI_CONFIG))
    {
        return $defaults;
    }

    $content = (string)file_get_contents(SETUP_UI_CONFIG);

    $url   = extractConstantValue('TESTS_BASE_URL', $content);
    $email = extractConstantValue('TESTS_SYSTEM_EMAIL_RECIPIENTS', $content);

    if ($url !== '')   { $defaults['base_url'] = $url; }
    if ($email !== '') { $defaults['email']    = $email; }

    return $defaults;
}

// ============================================================================
// Helpers — input collection
// ============================================================================

/**
 * Interactively collects database connection settings from the user.
 *
 * @param array<string, string> $defaults Previously stored or dist-file defaults.
 * @return array<string, string> Keys: host, name, user, password, port.
 */
function collectDatabaseSettings(array $defaults) : array
{
    writeln(color('--- Database settings ---', 'cyan'));
    writeln();

    $host     = prompt('Database host', $defaults['host']);

    // Re-prompt until a valid identifier is entered (letters, digits, underscores only).
    do
    {
        $name = prompt('Database name', $defaults['name']);
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $name))
        {
            writeln(color('  Invalid database name. Only letters, digits, and underscores are allowed.', 'red'));
            $name = '';
        }
    }
    while ($name === '');

    $user     = prompt('Database user', $defaults['user']);
    $password = promptPassword('Database password', $defaults['password']);

    // Re-prompt until the port is empty/null or a valid positive integer string.
    do
    {
        $portRaw = prompt('Database port (leave empty for null)', $defaults['port'] === 'null' ? '' : $defaults['port']);

        if ($portRaw === '' || strtolower($portRaw) === 'null')
        {
            $port = 'null';
            break;
        }

        if (!ctype_digit($portRaw))
        {
            writeln(color('  Invalid port. Enter a numeric value or leave empty for null.', 'red'));
            $portRaw = '';
            continue;
        }

        $port = $portRaw;
    }
    while ($portRaw === '');

    return array(
        'host'     => $host,
        'name'     => $name,
        'user'     => $user,
        'password' => $password,
        'port'     => $port,
    );
}

/**
 * Interactively collects UI / application settings from the user.
 *
 * Note: `TESTS_SESSION_TYPE` is deliberately not prompted — it always
 * defaults to `'NoAuth'`. To enable CAS authentication, manually edit
 * `tests/application/config/test-ui-config.php` and set:
 *
 *   const TESTS_SESSION_TYPE = 'CAS';
 *
 * Then copy `tests/application/config/test-cas-config.dist.php` to
 * `test-cas-config.php` and fill in your CAS / LDAP server details.
 * See the README's "CAS authentication mode" section for the full list
 * of constants that need to be configured.
 *
 * @param array<string, string> $defaults Previously stored or dist-file defaults.
 * @return array<string, string> Keys: base_url, email.
 */
function collectUiSettings(array $defaults) : array
{
    writeln(color('--- UI / application settings ---', 'cyan'));
    writeln();

    $baseUrl = prompt('Base URL (tests/application on local webserver)', $defaults['base_url']);
    $email   = prompt('System email recipients', $defaults['email']);

    return array(
        'base_url' => $baseUrl,
        'email'    => $email,
    );
}

// ============================================================================
// Helpers — database connection test
// ============================================================================

/**
 * Attempts a PDO connection to the MySQL server WITHOUT selecting a database.
 *
 * Returns the PDO object on success, or `false` on failure (printing the
 * error message to STDERR).
 *
 * @param array<string, string> $settings DB settings (host, user, password, port).
 * @return PDO|false
 */
function testDatabaseConnection(array $settings) : PDO|false
{
    $dsn = 'mysql:host=' . $settings['host'];

    if ($settings['port'] !== 'null')
    {
        $dsn .= ';port=' . $settings['port'];
    }

    try
    {
        $pdo = new PDO(
            $dsn,
            $settings['user'],
            $settings['password'],
            array(
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT            => 5,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            )
        );

        return $pdo;
    }
    catch (\PDOException $e)
    {
        writeln(color('Connection failed: ' . $e->getMessage(), 'red'));

        return false;
    }
}

// ============================================================================
// Helpers — config file generation
// ============================================================================

/**
 * Replaces the value of a PHP constant declaration inside raw file content.
 *
 * Handles three target value shapes:
 *   - String:  const NAME = 'new_value';
 *   - Integer: const NAME = 42;
 *   - Null:    const NAME = null;
 *
 * @param string $content   The raw PHP source to process.
 * @param string $constant  The constant name to replace.
 * @param string $value     The new value as a string (e.g. 'root', '3306', 'null').
 * @param bool   $isInt     When true, the value is written as a bare integer.
 * @param bool   $isNull    When true, the value is written as `null`.
 * @return string The modified source.
 */
function replaceConfigConstant(
    string $content,
    string $constant,
    string $value,
    bool $isInt  = false,
    bool $isNull = false
) : string {
    if ($isNull)
    {
        $replacement = 'const ' . $constant . ' = null;';
    }
    elseif ($isInt)
    {
        $replacement = 'const ' . $constant . ' = ' . $value . ';';
    }
    else
    {
        // addslashes() escapes \, ', ", and NUL — sufficient for the concrete
        // value types this script handles (hostnames, URLs, emails, passwords).
        $replacement = 'const ' . $constant . " = '" . addslashes($value) . "';";
    }

    // Replace any existing `const NAME = ...;` declaration on a single line.
    $pattern = '/^const\s+' . preg_quote($constant, '/') . '\s*=\s*[^;]+;/m';

    return (string)preg_replace($pattern, $replacement, $content, 1);
}

/**
 * Reads `test-db-config.dist.php`, replaces all constant values with the
 * provided settings, and writes the result to `test-db-config.php`.
 *
 * @param array<string, string> $settings DB settings (host, name, user, password, port).
 * @return void
 */
function generateDbConfig(array $settings) : void
{
    $rawContent = file_get_contents(SETUP_TPL_DB);

    if ($rawContent === false)
    {
        writeln(color('ERROR: Could not read template: ' . SETUP_TPL_DB, 'red'));
        exit(1);
    }

    $content = $rawContent;

    $content = replaceConfigConstant($content, 'TESTSUITE_DB_HOST',     $settings['host']);
    $content = replaceConfigConstant($content, 'TESTSUITE_DB_NAME',     $settings['name']);
    $content = replaceConfigConstant($content, 'TESTSUITE_DB_USER',     $settings['user']);
    $content = replaceConfigConstant($content, 'TESTSUITE_DB_PASSWORD', $settings['password']);

    if ($settings['port'] === 'null')
    {
        $content = replaceConfigConstant($content, 'TESTSUITE_DB_PORT', 'null', false, true);
    }
    else
    {
        $content = replaceConfigConstant($content, 'TESTSUITE_DB_PORT', $settings['port'], true, false);
    }

    if (file_put_contents(SETUP_DB_CONFIG, $content) === false)
    {
        writeln(color('ERROR: Could not write ' . SETUP_DB_CONFIG, 'red'));
        writeln('Check that the config directory is writable.');
        exit(1);
    }
}

/**
 * Reads `test-ui-config.dist.php`, replaces all constant values with the
 * provided settings, and writes the result to `test-ui-config.php`.
 *
 * `TESTS_SESSION_TYPE` is always set to `'NoAuth'`; CAS must be configured
 * manually when needed.
 *
 * @param array<string, string> $settings UI settings (base_url, email).
 * @return void
 */
function generateUiConfig(array $settings) : void
{
    $rawContent = file_get_contents(SETUP_TPL_UI);

    if ($rawContent === false)
    {
        writeln(color('ERROR: Could not read template: ' . SETUP_TPL_UI, 'red'));
        exit(1);
    }

    $content = $rawContent;

    $content = replaceConfigConstant($content, 'TESTS_BASE_URL',                 $settings['base_url']);
    $content = replaceConfigConstant($content, 'TESTS_SESSION_TYPE',             'NoAuth');
    $content = replaceConfigConstant($content, 'TESTS_SYSTEM_EMAIL_RECIPIENTS',  $settings['email']);

    if (file_put_contents(SETUP_UI_CONFIG, $content) === false)
    {
        writeln(color('ERROR: Could not write ' . SETUP_UI_CONFIG, 'red'));
        writeln('Check that the config directory is writable.');
        exit(1);
    }
}

// ============================================================================
// Helpers — database creation & schema import
// ============================================================================

/**
 * Ensures the target database exists and its schema is up to date.
 *
 * Steps:
 * 1. Try to `USE <dbName>` — if it succeeds the database already exists.
 * 2. On failure, `CREATE DATABASE \`<dbName>\`` and select it.
 * 3. Read the schema file via `file_get_contents()`.
 * 4. Execute the SQL via PDO `exec()`.
 *
 * Aborts with a non-zero exit code if `CREATE DATABASE` fails (e.g. the
 * DB user lacks the required privilege).
 *
 * @param PDO    $pdo        An open PDO connection (no database selected).
 * @param string $dbName     The target database name.
 * @param string $schemaFile Absolute path to the SQL schema file.
 * @return void
 */
function ensureDatabase(PDO $pdo, string $dbName, string $schemaFile) : void
{
    // --- Check if the database already exists ---
    $dbExists = false;

    try
    {
        $pdo->exec('USE `' . $dbName . '`');
        $dbExists = true;
    }
    catch (\PDOException $e)
    {
        // Database does not exist — proceed to create it.
    }

    if (!$dbExists)
    {
        writeln('  Creating database `' . $dbName . '`…');

        try
        {
            $pdo->exec('CREATE DATABASE `' . $dbName . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            $pdo->exec('USE `' . $dbName . '`');
            writeln('  ' . color('Database created.', 'green'));
        }
        catch (\PDOException $e)
        {
            writeln(color('ERROR: Could not create database `' . $dbName . '`.', 'red'));
            writeln(color('       ' . $e->getMessage(), 'red'));
            writeln();
            writeln('Please create the database manually and re-run setup, or grant');
            writeln('CREATE DATABASE privileges to the configured user.');
            exit(1);
        }
    }
    else
    {
        writeln('  Database `' . $dbName . '` already exists — skipping creation.');
    }

    // --- Import schema ---
    writeln('  Importing schema from ' . basename($schemaFile) . '…');

    $sql = file_get_contents($schemaFile);

    if ($sql === false || trim($sql) === '')
    {
        writeln(color('WARNING: Schema file is empty or could not be read: ' . $schemaFile, 'yellow'));
        return;
    }

    try
    {
        $pdo->exec($sql);
        writeln('  ' . color('Schema imported successfully.', 'green'));
    }
    catch (\PDOException $e)
    {
        writeln(color('WARNING: Schema import reported an error (some statements may already exist):', 'yellow'));
        writeln('  ' . $e->getMessage());
    }
}

// ============================================================================
// Main entry point
// ============================================================================

// Banner
writeln();
writeln(color('=== Application Framework — Local Environment Setup ===', 'bold'));
writeln();

// --- Parse existing configs for defaults ---
$dbDefaults = parseExistingDbConfig();
$uiDefaults = parseExistingUiConfig();

// --- Collect settings ---
$dbSettings = collectDatabaseSettings($dbDefaults);
writeln();
$uiSettings = collectUiSettings($uiDefaults);
writeln();

// --- Test DB connection (loop until success) ---
writeln(color('Testing database connection…', 'bold'));

$pdo = false;

while ($pdo === false)
{
    $pdo = testDatabaseConnection($dbSettings);

    if ($pdo === false)
    {
        writeln();
        writeln(color('Please check your credentials and try again.', 'yellow'));
        writeln();
        $dbSettings = collectDatabaseSettings($dbSettings);
        writeln();
    }
}

writeln(color('Connection successful.', 'green'));
writeln();

// --- Generate config files ---
writeln(color('Writing config files…', 'bold'));

generateDbConfig($dbSettings);
writeln('  ' . color('Written:', 'green') . ' tests/application/config/test-db-config.php');

generateUiConfig($uiSettings);
writeln('  ' . color('Written:', 'green') . ' tests/application/config/test-ui-config.php');

writeln();

// --- Ensure database and import schema ---
writeln(color('Setting up database…', 'bold'));

ensureDatabase($pdo, $dbSettings['name'], SETUP_SQL_SCHEMA);

writeln();

// --- Seed test data ---
writeln(color('Seeding test data (composer seed-tests)…', 'bold'));
writeln();

$seedExitCode = 0;
passthru('composer seed-tests', $seedExitCode);

writeln();

if ($seedExitCode !== 0)
{
    writeln(color('WARNING: composer seed-tests exited with code ' . $seedExitCode . '.', 'yellow'));
    writeln('The config files and schema have been set up. You may need to re-run');
    writeln('`composer seed-tests` manually once the issue is resolved.');
}
else
{
    writeln(color('Setup complete!', 'green'));
    writeln();
    writeln('You can now run the test suite:');
    writeln('  composer test');
}

writeln();
