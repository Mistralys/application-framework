# Plan: Interactive Developer Menu & `composer setup` Command

## Summary

Add an interactive CLI developer menu to the Application Framework, accessible via `menu.sh` (Unix) and `menu.cmd` (Windows) from the project root. The menu presents numbered options for common development tasks: Setup local environment, Build, Run tests, Clear caches, Seed tests, and PHPStan analysis. The menu shell scripts are thin wrappers that invoke a PHP menu script (`tools/menu.php`), keeping all logic in one language and avoiding duplication across platforms.

The **Setup** option (also available directly via `composer setup`) interactively configures the local development environment: it queries the user for database and UI settings, generates `test-db-config.php` and `test-ui-config.php` from their `.dist.php` templates, and runs `composer seed-tests` to initialize the database. The CAS config is deliberately skipped (it is loaded as optional and must be edited manually if needed). The setup is idempotent: re-running uses existing values as defaults.

## Architectural Context

### Config File Loading Chain

1. `tests/bootstrap.php` → `Application_Bootstrap::init()` → loads `tests/application/config/app-config.php`
2. `app-config.php` → `new EnvironmentsConfig(FolderInfo)->detect()`
3. `EnvironmentsConfig` registers `LocalEnvironment` which queues config file includes
4. `LocalEnvironment::setUpEnvironment()` includes:
   - `test-db-config.php` (required)
   - `test-ui-config.php` (required)
   - `test-cas-config.php` (optional — second parameter `true`)
5. After includes are loaded, `IncludesLoaded` event fires → `configureDefaultSettings()` reads the constants

### Template Files (`.dist.php`)

| Template | Target | Constants |
|---|---|---|
| `tests/application/config/test-db-config.dist.php` | `test-db-config.php` | `TESTSUITE_DB_HOST`, `TESTSUITE_DB_NAME`, `TESTSUITE_DB_USER`, `TESTSUITE_DB_PASSWORD`, `TESTSUITE_DB_PORT` |
| `tests/application/config/test-ui-config.dist.php` | `test-ui-config.php` | `TESTS_BASE_URL`, `TESTS_SESSION_TYPE`, `TESTS_SYSTEM_EMAIL_RECIPIENTS` |
| `tests/application/config/test-cas-config.dist.php` | `test-cas-config.php` | CAS/LDAP constants (skipped by this script) |

### Existing Convention (HCP Editor)

The HCP Editor has `tools/setup-local.php` — a standalone CLI script (no bootstrap dependency) invoked via `composer setup`. This plan follows the same pattern.

### Relevant Files

- `tests/application/config/test-db-config.dist.php` — DB config template
- `tests/application/config/test-ui-config.dist.php` — UI config template
- `tests/application/config/test-cas-config.dist.php` — CAS config template (not touched)
- `composer.json` — Composer scripts section (new `setup` script to add)
- `.gitignore` — currently ignores `/tools` (must be un-ignored)
- `tests/sql/testsuite.sql` — DB schema (imported by the setup script before seeding)

### File Structure (New)

```
(project root)
├── menu.sh                         ← Unix launcher (new)
├── menu.cmd                        ← Windows launcher (new)
└── tools/
    ├── menu.php                    ← Interactive menu (new)
    ├── setup-local.php             ← Setup script (new)
    └── include/
        └── cli-utilities.php       ← Shared CLI utilities (new)
```

## Approach / Architecture

The solution has three layers:

1. **Root launcher scripts** (`menu.sh`, `menu.cmd`) — thin wrappers that invoke `php tools/menu.php`
2. **PHP menu script** (`tools/menu.php`) — presents a numbered menu, dispatches to Composer commands or the setup script
3. **Setup script** (`tools/setup-local.php`) — standalone interactive setup (also callable directly via `composer setup`)

```
menu.sh / menu.cmd
       │
       ▼
tools/menu.php  (interactive numbered menu)
       │
       ├─→ [pre-flight] Check for vendor/ → run `composer install` if missing
       │
       ├─→ [1] Setup  → php tools/setup-local.php
       ├─→ [2] Build  → composer build
       ├─→ [3] Tests  → composer test (with scope prompt)
       ├─→ [4] Clear caches → composer clear-caches
       ├─→ [5] Seed tests → composer seed-tests
       ├─→ [6] PHPStan → composer analyze
       └─→ [0] Exit
```

Before showing the menu, the script checks whether the `vendor/` directory exists. If it does not, it runs `composer install` automatically. This allows a developer to go from a fresh clone to a fully operational local copy using only the menu.

The menu loops after each action completes, allowing the developer to run multiple tasks in one session. Exit with `0` or Ctrl+C.

## Rationale

- **PHP-based menu** avoids duplicating logic in bash and batch. Only the 2-line launcher wrappers differ per OS.
- **Standalone scripts** (no bootstrap) avoid the chicken-and-egg problem — config files don't exist yet on first run.
- **Same pattern as HCP Editor** reduces cognitive load for developers working on both projects.
- **Vendor pre-flight** means a fresh clone only needs `./menu.sh` to become fully operational — no prior manual steps.
- **Looping menu** lets developers run multiple tasks (e.g., setup → build → test) in one session.
- **Idempotent setup** via existing-value parsing: safe to re-run when changing a single setting.
- **Delegating DB seeding to `composer seed-tests`** keeps the seed logic in one place (`ComposerScripts::seedTests`) and avoids duplicating it.
- **CAS skipped** because it's rarely needed and marked optional in the environment config.

## Detailed Steps

### Step 1: Un-ignore `/tools` in `.gitignore`

Remove the line `/tools` from `.gitignore` so the new scripts can be version-controlled.

**File:** `.gitignore` (line 19)

### Step 2: Create `menu.sh` (project root)

A thin wrapper for Unix (macOS/Linux):

```bash
#!/usr/bin/env bash
# Interactive developer menu
cd "$(dirname "$0")" || exit 1
php tools/menu.php
```

Mark executable (`chmod +x`).

### Step 3: Create `menu.cmd` (project root)

A thin wrapper for Windows:

```batch
@echo off
cd /d "%~dp0"
php tools/menu.php
```

### Step 4: Create `tools/menu.php`

The interactive numbered menu script. Structure:

#### 4a. Pre-flight: Vendor Check

As the very first action (before including shared utilities or showing the menu), check for the `vendor/` directory relative to the project root:

```php
$vendorDir = __DIR__ . '/../vendor';

if (!is_dir($vendorDir)) {
    echo 'vendor/ directory not found. Running composer install...' . PHP_EOL;
    passthru('composer install', $exitCode);
    if ($exitCode !== 0) {
        echo 'composer install failed (exit code ' . $exitCode . '). Aborting.' . PHP_EOL;
        exit(1);
    }
    echo PHP_EOL;
}
```

This ensures all dependencies (including autoload) are available before any further logic executes.

#### 4b. Console I/O Utilities (shared)

Extract shared utilities into `tools/include/cli-utilities.php` so both `menu.php` and `setup-local.php` can reuse them:

- `writeln(string $text = ''): void`
- `color(string $text, string $color): string` — ANSI color support (green, red, yellow, cyan, bold)
- `prompt(string $label, string $default = ''): string` — readline with default

#### 4c. Menu Display & Loop

```php
function showMenu(): void
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
```

#### 4d. Menu Dispatch

Each option executes via `passthru()`:

| Choice | Action |
|--------|--------|
| `1` | `php tools/setup-local.php` (direct invocation, stays in same process context) |
| `2` | `composer build` |
| `3` | Sub-prompt: "Filter pattern (empty = full suite):" → `composer test-filter -- <pattern>` or `composer test` |
| `4` | `composer clear-caches` |
| `5` | `composer seed-tests` |
| `6` | `composer analyze` |
| `0` | Exit |

After each action completes, the menu redisplays (loop).

#### 4e. Main Entry

```php
// Pre-flight: ensure vendor/ exists
ensureVendorInstalled();

while (true) {
    showMenu();
    $choice = prompt('Select option');
    // dispatch...
}
```

### Step 5: Create `tools/include/cli-utilities.php`

Shared CLI utility functions used by both `menu.php` and `setup-local.php`:

- `writeln()`
- `color()`
- `prompt()`
- `promptPassword()` — hidden input via `stty -echo` (Unix) with graceful fallback (Windows: echo visible)

Guard against double-inclusion with `function_exists()` checks.

### Step 6: Create `tools/setup-local.php`

Create the standalone CLI setup script with the following sections:

#### 6a. Constants & Paths

```php
const SETUP_ROOT       = __DIR__ . '/..';
const SETUP_CONFIG_DIR = SETUP_ROOT . '/tests/application/config';

// Template files
const SETUP_TPL_DB  = SETUP_CONFIG_DIR . '/test-db-config.dist.php';
const SETUP_TPL_UI  = SETUP_CONFIG_DIR . '/test-ui-config.dist.php';

// Target files
const SETUP_DB_CONFIG = SETUP_CONFIG_DIR . '/test-db-config.php';
const SETUP_UI_CONFIG = SETUP_CONFIG_DIR . '/test-ui-config.php';
```

#### 6b. Include Shared Utilities

```php
require_once __DIR__ . '/include/cli-utilities.php';
```

#### 6c. Existing Config Parsing (for idempotency)

- `parseExistingDbConfig(): array` — parse `test-db-config.php` if it exists, extract constant values via regex
- `parseExistingUiConfig(): array` — parse `test-ui-config.php` if it exists, extract constant values via regex

Both use a shared `extractConstantValue(string $constant, string $content): string` helper.

#### 6d. Input Collection

- `collectDatabaseSettings(array $defaults): array` — prompt for: DB host, DB name, DB user, DB password, DB port
- `collectUiSettings(array $defaults): array` — prompt for: base URL, system email recipients
  - `TESTS_SESSION_TYPE` is not queried (always `'NoAuth'`; CAS mode requires manual config)

#### 6e. Database Connection Test

- `testDatabaseConnection(array $settings): PDO|false` — PDO connection attempt to the specified host/port/user/password (without selecting a database)
- Loop on failure, re-prompting only the DB settings

#### 6f. Config File Generation

- `replaceConfigConstant(string $content, string $constant, string $value, bool $isInt = false): string` — regex replacement of constant value in template content
- `generateDbConfig(array $settings): void` — read `.dist.php`, replace constant values, write `test-db-config.php`
- `generateUiConfig(array $settings): void` — read `.dist.php`, replace constant values, write `test-ui-config.php`

Special handling for `TESTSUITE_DB_PORT`: it is `null` in the template. When the user provides a port, write it as an integer. When the user leaves it empty or enters `null`, write `null`.

#### 6g. Database Creation & Schema Import

After the DB connection test succeeds and config files are written, ensure the database exists and its schema is loaded. This step runs before seeding because `composer seed-tests` requires the tables to already exist.

- `ensureDatabase(PDO $pdo, string $dbName, string $schemaFile): void`

Logic:
1. Attempt `SELECT 1` against the target database (`USE <dbName>`). If successful, the database exists — skip creation.
2. On failure, run `CREATE DATABASE \`<dbName>\``.
   - If the user lacks `CREATE DATABASE` privileges, report the error and abort.
3. Select the new database (`USE <dbName>`).
4. Read `tests/sql/testsuite.sql` via `file_get_contents()`.
5. Execute the SQL against the database via PDO with `PDO::MYSQL_ATTR_MULTI_STATEMENTS` (or `exec()` for single-connection drivers).
6. Report success or failure.

This follows the proven pattern from the HCP Editor's `tools/setup-local.php` (`ensureDatabase()` function, lines 358–420).

#### 6h. Database Seeding

After the database and schema are confirmed to exist, call `composer seed-tests` via `passthru()`:

```php
passthru('composer seed-tests', $exitCode);
```

This delegates to `ComposerScripts::seedTests()` which boots the framework and calls `TestSuiteBootstrap::seedSystemUsers()`. It requires the schema tables to already exist (handled by step 6g).

#### 6i. Main Entry Point

Orchestrate the flow:
1. Print banner
2. Parse existing configs as defaults
3. Collect DB settings
4. Collect UI settings
5. Test DB connection (loop on failure)
6. Generate `test-db-config.php`
7. Generate `test-ui-config.php`
8. Create database and import schema if needed
9. Run `composer seed-tests`
10. Print success / next-steps summary

#### 6j. Signal Handling

Register `SIGINT` handler (when `pcntl` available) to restore terminal echo if interrupted during password prompts.

### Step 7: Add `setup` script to `composer.json`

Add to the `"scripts"` section:

```json
"setup": "php tools/setup-local.php"
```

This allows `composer setup` as a direct alternative to using the menu.

### Step 8: Update `.gitignore` for generated config files

Verify that `tests/application/config/test-db-config.php` and `tests/application/config/test-ui-config.php` remain in `.gitignore` (they already are — lines 34 and 42). No change needed.

### Step 9: Update `AGENTS.md`

Add the developer menu and `composer setup` command to the framework's `AGENTS.md`:
- Document `menu.sh` / `menu.cmd` as the entry point
- Document `composer setup` as the direct setup command

## Dependencies

- PHP 8.4+ (already a project requirement)
- `ext-pdo` + MySQL driver (already a project requirement)
- `pcntl` extension (optional — for SIGINT handler during password prompts)
- `readline` extension (optional — falls back to `fgets(STDIN)`)
- `composer seed-tests` must work correctly after config files are in place

## Required Components

- **New file:** `menu.sh` (project root) — Unix launcher
- **New file:** `menu.cmd` (project root) — Windows launcher
- **New file:** `tools/menu.php` — interactive menu logic
- **New file:** `tools/include/cli-utilities.php` — shared CLI utilities
- **New file:** `tools/setup-local.php` — setup script
- **Modified file:** `composer.json` — add `"setup"` script
- **Modified file:** `.gitignore` — remove `/tools` exclusion

## Assumptions

- The developer has MySQL/MariaDB running locally and can provide valid credentials.
- The developer has Composer installed and can run `composer` commands.
- `composer seed-tests` handles seeding system users correctly once the database schema exists (this is the existing behavior per `ComposerScripts::seedTests()`). The setup script is responsible for creating the database and importing the schema beforehand.
- The DB user has `CREATE DATABASE` privileges (or the database already exists).

## Constraints

- The script must be standalone (no bootstrap dependency) — config files don't exist before it runs.
- Must use `array()` syntax, not `[]` (hard project rule).
- Must include `declare(strict_types=1)`.
- Must be idempotent: re-running with existing configs should use them as defaults and overwrite only with confirmed new values.
- `TESTS_SESSION_TYPE` defaults to `'NoAuth'` and is not queried.
- CAS config is not generated by this script.

## Out of Scope

- CAS/LDAP configuration (manual-only)
- Database schema migrations or upgrades
- Webserver virtual host configuration
- IDE configuration
- Git hook installation (framework doesn't have hooks)

## Acceptance Criteria

- Running `./menu.sh` (or `menu.cmd` on Windows) displays a numbered menu of developer tasks.
- Selecting option 1 (Setup) interactively generates both config files and seeds the database.
- `composer setup` achieves the same result as menu option 1.
- Running setup again reads existing values as defaults; pressing Enter preserves them.
- If the DB connection fails, the user is re-prompted for credentials until success.
- After successful setup, `composer test-file -- tests/AppFrameworkTests/` runs without config-related errors.
- The CAS config file is not generated.
- `TESTSUITE_DB_PORT` supports both integer and `null` values.
- Menu options 2–6 correctly dispatch to their respective Composer commands.
- The menu loops after each action; exit with `0`.

## Testing Strategy

This is a CLI tool, so testing is primarily manual:

1. **Menu display:** Run `./menu.sh`, verify all options are shown and the loop works correctly.
2. **Fresh setup:** Delete all generated config files, select option 1, verify files are generated correctly and tests can run.
3. **Direct composer setup:** Run `composer setup` directly, verify same behavior as menu option 1.
4. **Idempotent re-run:** Run setup again, press Enter through all prompts, verify config files are unchanged.
5. **Bad credentials:** Enter invalid DB credentials, verify re-prompt loop works and does not crash.
6. **Port handling:** Test with explicit port (e.g., `3307`) and with empty/null port.
7. **Other menu options:** Verify options 2–6 dispatch to correct Composer commands.
8. **Windows:** Verify `menu.cmd` launches the PHP menu correctly.

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **`composer seed-tests` fails on fresh DB** | The setup script creates the database and imports `tests/sql/testsuite.sql` before calling `composer seed-tests`, ensuring all required tables exist. `APP_SEED_MODE` only skips the user-existence check during bootstrap — it does not handle missing schema. If the schema import itself fails, the script reports the PDO error and aborts before attempting to seed. |
| **`stty -echo` not available on Windows** | `promptPassword()` detects the OS and degrades gracefully (password visible on Windows). |
| **Regex fails on unusual constant values** | Use the same proven `replaceConfigConstant()` pattern from the HCP Editor that handles single-quoted, double-quoted, integer, and null values. |
| **`/tools` gitignore removal exposes unintended files** | The `/tools` directory didn't exist before and will only contain the menu/setup scripts. If other local-only tooling is added later, those specific files can be gitignored individually. |
| **ANSI colors break on Windows cmd** | Detect `PHP_OS_FAMILY === 'Windows'` and strip ANSI codes when not in a compatible terminal. Modern Windows Terminal supports ANSI, so this is a graceful fallback only for legacy cmd.exe. |
