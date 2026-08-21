# Plan

## Plan Audit Cycles
- Audits: none — Plan Auditor v1.7.0
- Architectural Reviews: none — Plan Architect Reviewer v2.2.0

## Prior Project Context
- This tool belongs in the **Application Framework**, where the Developer area lives (`DevelArea`), so every consuming app (HCP Editor, etc.) inherits it. It follows the established developer-tooling precedent: `DatabaseDumpDevMode`, `AppSettingsDevelMode`, `MessageLogDevelMode`.
- Motivating use case (from the companion HCP Editor plan `2026-08-20-country-desync-prevention`): developers have no live DB access and need to run diagnostic `SELECT`s such as the country-desync detection query. A safe read-only console can substitute for one-off report CLIs.

## Summary
Add a developer-only, read-only SQL console screen to the Application Framework's Developer area. Developers can enter a query and view the result set in a data grid, restricted to read operations. Safety is enforced with **defense in depth**: (1) a dedicated read-only MySQL connection (engine-enforced, the hard boundary), and (2) an application-layer query guard that permits only a single `SELECT`/`SHOW`/`EXPLAIN`/`DESCRIBE` statement, blocks stacked statements, strips comments, and forces a row `LIMIT` and execution timeout. The screen auto-registers in the Developer area (no manual wiring) and is gated behind `RIGHT_DEVELOPER`.

## Architectural Context
- **Developer area** (`src/classes/Application/Development/Admin/Screens/DevelArea.php`): auto-registers any subscreen implementing `DevelModeInterface` (using `DevelModeTrait`), building navigation from `getNavigationTitle()` + `getDevCategory()`. `DatabaseDumpDevMode` is the reference template.
- **Rights** (`DevScreenRights.php`): all dev screens gate on `Application_User::RIGHT_DEVELOPER`.
- **DB layer** (`src/classes/DBHelper/DBHelper.php`): `registerDB($id, ...)` supports multiple named connections; `selectDB($id)` switches; `getDB()` builds a cached PDO per id. Only `'main'` (full-privilege) is registered today, at boot via `DBHelper::init()` invoked from `Application/Bootstrap/Screen.php::initDatabase()`.
- **Config** (`BaseConfigRegistry.php`): declares `DB_NAME/HOST/USER/PASSWORD/PORT` constant names; read-only counterparts would be added here.
- **Forms**: dev/admin screens build forms via the Formable API (`createFormableForm()`, `isFormValid()`); results render via `UI_DataGrid`.

## Approach / Architecture
Three parts:

1. **Read-only connection (primary, engine-enforced guard).** Register an optional second DBHelper connection with id `'readonly'` from new config constants (`APP_DB_READONLY_USER`, `APP_DB_READONLY_PASSWORD`, optionally host/name/port defaulting to the main DB). Registration happens in the boot DB init path alongside `DBHelper::init()`, only when the read-only credentials are configured. The console executes queries against this connection, saving and restoring the previously selected DB so `main` is never disturbed. If read-only credentials are **not** configured, the console refuses to run queries and shows a clear "not configured" message (fail-closed) rather than silently falling back to `main`.

2. **Application-layer query guard (secondary, defense in depth).** A new small, typed, testable utility (e.g. `Application\Development\SQLConsole\ReadOnlyQueryGuard`) that validates a submitted query string: strip SQL comments; require the statement to start with one of `SELECT`/`SHOW`/`EXPLAIN`/`DESCRIBE`; reject multiple statements (any `;` outside string literals); reject obvious write keywords defensively; and return a normalized query with an enforced `LIMIT` appended when absent. This runs even though the DB user is read-only, so the UX gives immediate, clear rejection and the app never relies on a single control.

3. **The console screen.** A new `SQLConsoleDevMode extends BaseMode implements DevelModeInterface` (using `DevelModeTrait`) under `src/classes/Application/Development/Admin/Screens/`. It renders a Formable form (query textarea + optional row-limit field + submit), on submit validates via the guard, executes on the `'readonly'` connection wrapped in a transaction that is always rolled back (belt-and-suspenders), and renders rows into a `UI_DataGrid` with column headers derived from the result keys. Errors (guard rejection, SQL error, not-configured) render as UI messages. The screen gates on a new `DevScreenRights::SCREEN_SQL_CONSOLE = RIGHT_DEVELOPER`.

## Rationale
- **A dedicated read-only DB user is the only hard guarantee.** App-layer string parsing cannot be fully trusted (stacked statements, creative payloads), so the engine-level grant is the real boundary; the guard is a fast-fail UX and second barrier — matching defense-in-depth.
- **Multiple named connections already exist** (`registerDB`/`selectDB`), so the read-only connection adds no new mechanism — smallest possible change.
- **Fail-closed when unconfigured** prevents the dangerous default of running ad-hoc SQL on the write-capable `main` account.
- **Auto-registration + `RIGHT_DEVELOPER`** reuses the Developer area's existing access model and navigation — no routing or permission work.
- **Framework placement** means all consumer apps benefit and there is one implementation to maintain.

## Considered Alternatives

| Decision | Chosen Shape | Alternatives Considered | Trade-Off Summary |
|----------|--------------|-------------------------|-------------------|
| Read-only enforcement | Dedicated read-only DB connection **+** app-layer guard | (a) App-layer parsing only on `main`; (b) DB user only, no parsing | (a) is not a hard guarantee — parsing bypasses exist on a write-capable account; (b) gives poor UX and no second barrier. Both layers together are safe and user-friendly. |
| Behavior when read-only creds absent | Fail-closed (refuse to run) | Fall back to `main` connection | Falling back would run arbitrary SQL on a full-privilege account — unacceptable. Fail-closed keeps the safety invariant. |
| Where the feature lives | Application Framework Developer area | HCP Editor only | Framework placement benefits all apps and matches where `DevelArea`/dev screens already live; app-only would duplicate later. |
| Connection registration site | Boot DB init path (next to `DBHelper::init()`), conditional on config | Lazily inside the screen | Registering at boot (only when configured) keeps DB setup centralized and consistent with `main`; lazy registration scatters connection setup. |
| Statement execution | Rolled-back transaction on the read-only connection | Plain execute | The rollback is a cheap extra guard and documents read-only intent; negligible cost for SELECTs. |

## Pattern Alignment
- **Follows** the auto-registering dev-screen pattern (`DatabaseDumpDevMode`, `DevelModeInterface` + `DevelModeTrait`).
- **Follows** the multi-connection DBHelper model (`registerDB`/`selectDB`) for the read-only connection.
- **Follows** the config-constant convention in `BaseConfigRegistry.php` for the new read-only credentials.
- **Follows** the Formable + `UI_DataGrid` UI conventions used by existing dev/admin screens.
- **Departs** from nothing structurally; the read-only connection is a new named instance of an existing mechanism.

## Detailed Steps
1. **Add read-only config constants.** In `src/classes/Application/ConfigSettings/BaseConfigRegistry.php`, declare constant names for the read-only DB credentials (`DB_READONLY_USER`, `DB_READONLY_PASSWORD`, and optional `DB_READONLY_NAME/HOST/PORT` that default to the `main` values). Document them alongside the existing `DB_*` entries.
2. **Register the read-only connection at boot.** In the DB init path (`src/classes/Application/Bootstrap/Screen.php::initDatabase()`, after `DBHelper::init()`), when the read-only credentials are defined, call `DBHelper::registerDB('readonly', ...)` using the read-only constants (falling back to main DB name/host/port). Do not `selectDB()` it — leave `main` active.
3. **Add a helper to run a query on the read-only connection.** Add a small method (e.g. on `DBHelper` or a dedicated `SQLConsole` service) that: asserts the `'readonly'` connection is registered (throws a clear exception otherwise), remembers the currently selected DB, `selectDB('readonly')`, runs the query inside a transaction that is always rolled back, restores the previous selection in a `finally`, and returns the fetched rows.
4. **Implement the query guard.** Create `src/classes/Application/Development/SQLConsole/ReadOnlyQueryGuard.php` (namespaced, typed): strips comments, verifies a single statement starting with an allowed read keyword, rejects stacked statements and write keywords, and returns a normalized query with an enforced `LIMIT` (configurable max). Throw a typed exception with a clear message on rejection.
5. **Add the screen rights constant.** In `DevScreenRights.php`, add `public const string SCREEN_SQL_CONSOLE = Application_User::RIGHT_DEVELOPER;`.
6. **Create the console screen.** Add `src/classes/Application/Development/Admin/Screens/SQLConsoleDevMode.php` (`extends BaseMode implements DevelModeInterface`, `use DevelModeTrait`): define `URL_NAME`, `getRequiredRight()` → `SCREEN_SQL_CONSOLE`, `getTitle()`, `getNavigationTitle()`, `getDevCategory()` (e.g. "Database"). Build a Formable form (query textarea + row-limit field). On valid submit: fail-closed if `'readonly'` is not registered (info message); else validate via `ReadOnlyQueryGuard`, execute via the read-only helper, and render results in a `UI_DataGrid` with headers from result keys. Render guard/SQL errors as UI error messages. Keep the constructor side-effect-free (navigation instantiates it in non-admin mode).
7. **Run `composer dump-autoload`** after adding the new class files (classmap autoloading).
8. **Static analysis.** Run `composer analyze` and resolve findings for the new code.

## Dependencies
- Step 6 depends on Steps 3, 4, 5.
- Step 3 depends on Step 2 (connection must be registered).
- Step 2 depends on Step 1 (constants must exist).

## Required Components
- Modified: `src/classes/Application/ConfigSettings/BaseConfigRegistry.php`
- Modified: `src/classes/Application/Bootstrap/Screen.php`
- Modified: `src/classes/DBHelper/DBHelper.php` (read-only query helper; optional)
- Modified: `src/classes/Application/Development/Admin/DevScreenRights.php`
- New: `src/classes/Application/Development/SQLConsole/ReadOnlyQueryGuard.php`
- New: `src/classes/Application/Development/SQLConsole/ReadOnlyQueryException.php` (typed guard exception; own error-code range)
- New: `src/classes/Application/Development/Admin/Screens/SQLConsoleDevMode.php`
- External (ops): a read-only MySQL user with `SELECT`-only grants on the app database.

## Assumptions
- The deployment can provision a MySQL user with `SELECT`-only privileges and expose its credentials via the read-only config constants.
- `DBHelper`'s multi-connection support (`registerDB`/`selectDB`/cached PDO per id) behaves as read from source (verified) for a second `'readonly'` id.
- Result sets from diagnostic queries are small enough to render in a data grid; the enforced `LIMIT` bounds this.

## Constraints
- Console must **fail closed**: never execute on `main` if the read-only connection is unavailable.
- Must not disturb the globally-selected DB (`main`) — save/restore around execution.
- `array()` syntax only; `declare(strict_types=1)`; PHP 8.4; namespaced/typed new code.
- New error codes in the guard/exception use their own numeric range (avoid collisions with `DBHelper` codes).
- Run `composer dump-autoload` after adding class files; run PHPStan via `composer analyze`.

## Out of Scope
- A general-purpose query builder, saved-query library, or query history persistence (could be a follow-up; note it as deferred).
- Write/DDL support of any kind.
- Cross-database or multi-tenant DB selection in the console UI.
- Exporting large result sets (beyond what the grid shows).
- Changes to HCP Editor; this is a framework feature (HCP Editor gains it automatically on upgrade).

## Acceptance Criteria

- AC-01: A new developer-only screen appears in the Developer area navigation (auto-registered via `DevelModeInterface`) and is gated behind `RIGHT_DEVELOPER`.
- AC-02: When read-only DB credentials are configured, a submitted `SELECT` (or `SHOW`/`EXPLAIN`/`DESCRIBE`) executes on the read-only connection and its rows render in a data grid.
- AC-03: The query guard rejects any non-read statement, stacked/multiple statements, and comment-obfuscated writes, with a clear error message and without touching the database.
- AC-04: A row `LIMIT` is enforced (appended when absent) and the execution runs inside an always-rolled-back transaction.
- AC-05: When read-only credentials are **not** configured, the console refuses to execute and displays a clear "read-only connection not configured" message; it never falls back to the write-capable `main` connection.
- AC-06: Running a query via the console does not change the globally-selected DBHelper connection (`main` remains active afterward).
- AC-07: A malformed query or SQL error surfaces as a UI error message without a fatal/uncaught exception.

## Testing Strategy
Unit-test the `ReadOnlyQueryGuard` in isolation (pure string logic, no DB) for accept/reject cases and LIMIT normalization. Integration-test the read-only execution helper against the test database for connection save/restore and rollback behavior. Use `composer test-file -- <path>` / `composer test-filter -- <pattern>`.

## Test Plan
- `tests/.../Development/SQLConsole/ReadOnlyQueryGuardTest.php` (new) — accepts `SELECT`/`SHOW`/`EXPLAIN`/`DESCRIBE`; rejects `INSERT/UPDATE/DELETE/DROP/…`, stacked statements (`SELECT 1; DROP …`), comment-hidden writes; appends `LIMIT` when absent; preserves an existing `LIMIT` — AC-03, AC-04.
- `tests/.../Development/SQLConsole/ReadOnlyExecutionTest.php` (new) — with a registered `'readonly'` connection: a `SELECT` returns rows; the previously selected DB is restored afterward; the wrapping transaction is rolled back — AC-02, AC-04, AC-06.
- `tests/.../Development/SQLConsole/ReadOnlyNotConfiguredTest.php` (new) — with no `'readonly'` connection registered, the execution helper throws the typed "not configured" exception (fail-closed) — AC-05.
- Screen-level assertion (new or existing dev-screen test harness) — the screen declares `getRequiredRight()` = `SCREEN_SQL_CONSOLE` and is discoverable by `DevelArea` — AC-01.

## Documentation Updates
- `changelog.md` — new version section: "Developer tools: Added a read-only SQL console" (DBHelper: optional read-only connection; Development: SQL console screen + query guard).
- Project manifest / `docs/agents/` — document the new read-only config constants and how to provision the read-only DB user (a short setup note); update the constraints doc if a new convention is introduced.
- Regenerate context docs after code changes: `composer build` (or `composer build-docs`) so `.context/` reflects the new module/classes (AGENTS.md §2).
- Consumer note: HCP Editor's `AGENTS.md`/setup docs may mention the optional read-only DB credentials once the framework version is adopted (out of scope for this repo but flagged).

## Deferred Items
| # | Deferred Item | Origin | Reason Deferred | Notes |
|---|---------------|--------|-----------------|-------|
| 1 | Persisted query history / saved queries | This plan (Out of Scope) | Not required for the core diagnostic use case | Add later as a small collection-backed list if demand appears. |
| 2 | Result-set export (CSV/XLSX) | This plan (Out of Scope) | Grid rendering covers the immediate need | Reuse existing export utilities when added. |

## Risks & Mitigations
| Risk | Mitigation |
|------|------------|
| **App-layer parsing bypassed** (creative SQL) | The read-only MySQL user is the hard boundary; the guard is a secondary UX barrier, not the sole control. |
| **Console runs on the write-capable `main` account** | Fail-closed: refuse to execute unless the `'readonly'` connection is registered; never fall back to `main`. Covered by AC-05 test. |
| **Selected-DB state leaks** (subsequent app queries hit the wrong connection) | Save/restore the selected DB in a `finally` around execution; assert restoration in tests (AC-06). |
| **Read-only user not provisioned in an environment** | Feature degrades gracefully (clear "not configured" message); no crash, no unsafe fallback. |
| **Large result set exhausts memory** | Enforced `LIMIT` and a bounded default; execution timeout on the statement. |
| **Stacked-statement execution via mysqlnd** | Guard rejects any statement separator outside string literals; read-only grant prevents writes even if a separator slips through. |

## Recommended Workflow
- **Workflow:** ledger
- **Rationale:** Security-sensitive feature spanning config, boot, the DB layer, a new guard utility, and a new UI screen — it benefits from formal QA, security audit, and review stages.
