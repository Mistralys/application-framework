# Plan

## Summary

Follow-up cleanup plan addressing the four actionable items identified in the `2026-05-05-test-db-seed-data-rework-1` synthesis. The items are: (1) replace or pin the floating `shark/simple_html_dom` `dev-master` dependency, (2) remove the now-dead `ComposerScripts::doSeedTests()` method, (3) add bidirectional `@see` cross-references to `seedSystemUsers()` and `seedCountries()`, and (4) add a PHPStan stub for `tools/include/cli-utilities.php` so that `tools/` scripts can optionally be analyzed without 91 false-positive `function.notFound` errors.

## Architectural Context

### Package: `shark/simple_html_dom`

- **Declared in:** `composer.json` line 96 as `"shark/simple_html_dom": "dev-master"`.
- **Installed version:** 1.5 (from GitHub `samacs/simple_html_dom`, commit `d0a7686`).
- **Framework usage:** NONE — the framework source (`src/`) does not reference this package at all.
- **Downstream usage:** The HCP Editor uses `str_get_html()` and the `simple_html_dom_node` class in `assets/classes/Maileditor/Mails/Mail/FrozenTextParser.php` (lines 210, 216).
- **PHP 8.4 deprecation:** `$http_response_header` usage on lines 99, 102, 113 of `simple_html_dom.php` emits deprecation notices at runtime.
- **Risk:** Floating `dev-master` pins bypass Composer's security advisory checks (OWASP A06).

### Method: `ComposerScripts::doSeedTests()`

- **Location:** `src/classes/Application/Composer/ComposerScripts.php` lines 129–147.
- **Current invocations:** Zero. `composer seed-tests` now invokes `php tools/seed-truncate.php` and `php tools/seed-insert.php`. No HCP Editor code calls `doSeedTests()` (the planned HCP Editor seeding feature in `docs/agents/plans/2026-05-05-test-db-seed-data/plan.md` has not been implemented).
- **Contains:** `truncateAllTables()`, two `resetCollection()` calls, three seed method calls, and progress echoing — duplicating the logic now handled by the two CLI scripts.

### PHPDoc Cross-References

- **`seedLocales()` (line 214):** Has `@see self::SEED_LOCALES` and `@see self::seedCountries()`.
- **`seedSystemUsers()` (line 180):** Has `@see ComposerScripts::doSeedTests()` only — no cross-reference to sibling seed methods.
- **`seedCountries()` (line 284):** Has no `@see` cross-references to sibling seed methods at all.

### PHPStan and `tools/setup-local.php`

- `tools/` is NOT in PHPStan's `paths:` configuration, so the 91 `function.notFound` errors do NOT appear in standard `composer analyze` runs.
- The errors would appear only if `tools/` is explicitly added to analysis scope in the future.
- Root cause: `tools/setup-local.php` includes `tools/include/cli-utilities.php` via `require_once` at runtime; PHPStan cannot resolve these functions statically without a stub or bootstrap entry.

## Approach / Architecture

Four independent, low-risk changes:

1. **Replace `shark/simple_html_dom`** with `mistralys/simple_html_dom` — a drop-in replacement package that is PHP 8.4 compatible and offers stable tagged releases. Since the framework itself has zero usages, the safest approach is to **remove it from the framework's `composer.json`** and let the HCP Editor declare its own dependency on `mistralys/simple_html_dom`. Alternatively, if the framework wants to keep providing it transitively, simply swap the require line.

2. **Remove `doSeedTests()`** — since it has zero callers and its logic is fully superseded by the process-isolated CLI scripts. Add a `@deprecated` notice first if a grace period is desired, or remove outright since no callers exist anywhere in the workspace.

3. **Add `@see` cross-references** to `seedSystemUsers()` and `seedCountries()` docblocks, following the pattern established in `seedLocales()`.

4. **Create a PHPStan stub file** at `tests/phpstan/cli-utilities-stubs.php` that declares the function signatures from `tools/include/cli-utilities.php`, and add it to `phpstan.neon`'s `bootstrapFiles` list. This allows `tools/` to be optionally analyzed without false positives.

## Rationale

- **Replace vs. remove `simple_html_dom`:** Although the framework itself doesn't use the package, it provides it transitively to the HCP Editor. Replacing with `mistralys/simple_html_dom` (a maintained, PHP 8.4-compatible, drop-in fork) eliminates the security/deprecation issues while keeping the transitive dependency chain intact — no downstream changes needed.
- **Remove vs. deprecate `doSeedTests()`:** There are zero callers across the entire workspace (framework + HCP Editor). The method's contract (single-process with `resetCollection()`) is incompatible with the new architecture. Deprecating it adds noise without serving anyone; removal is cleaner.
- **PHPDoc cross-references:** Navigability of the seeding surface — a developer reading any one seed method should discover the others immediately.
- **PHPStan stub:** Defensive improvement — prevents a flood of false positives if `tools/` is ever added to analysis scope (as recommended by the CTX documentation's completeness goals).

## Detailed Steps

### Step 1: Replace `shark/simple_html_dom` with `mistralys/simple_html_dom`

1. Replace the line `"shark/simple_html_dom": "dev-master"` with `"mistralys/simple_html_dom": "^2.0"` in `composer.json` `require` section.
2. Run `composer update mistralys/simple_html_dom --with-all-dependencies` to install the new package and remove the old one (the `replaces` metadata in `mistralys/simple_html_dom` handles the removal of `shark/simple_html_dom` automatically).
3. Run `composer install` to verify no framework code breaks.
4. Run `composer analyze` to verify no PHPStan regressions.

### Step 2: Remove `ComposerScripts::doSeedTests()`

1. Delete the `doSeedTests()` method (lines 129–147) from `src/classes/Application/Composer/ComposerScripts.php`.
2. Remove the `@see ComposerScripts::doSeedTests()` tag from `seedSystemUsers()` docblock in `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php` (line 172).
3. Run `composer dump-autoload`.
4. Run `composer test-filter -- Seed` to verify existing seed tests still pass.

### Step 3: Add PHPDoc `@see` cross-references

1. In `TestSuiteBootstrap::seedSystemUsers()` docblock, add:
   - `@see self::seedLocales()`
   - `@see self::seedCountries()`
2. In `TestSuiteBootstrap::seedCountries()` docblock, add:
   - `@see self::SEED_COUNTRIES`
   - `@see self::seedSystemUsers()`
   - `@see self::seedLocales()`
3. In `TestSuiteBootstrap::seedLocales()` docblock, add:
   - `@see self::seedSystemUsers()`

### Step 4: Create PHPStan stub for CLI utilities

1. Create `tests/phpstan/cli-utilities-stubs.php` containing function signatures (no bodies) for all functions defined in `tools/include/cli-utilities.php`.
2. Add `- ./tests/phpstan/cli-utilities-stubs.php` to the `bootstrapFiles` array in `phpstan.neon`.
3. Optionally add `- ./tools` to the PHPStan `paths:` array and verify zero errors appear.
4. Run `composer analyze` to confirm no regressions.
5. Update the AGENTS.md description of `tests/phpstan/` from "contains only bootstrap constants" to "contains PHPStan bootstrap files (constants and function stubs)".

### Step 5: Regenerate documentation

1. Run `composer build` to regenerate all CTX context. The removal of `doSeedTests()` from source will be reflected automatically in `.context/modules/composer/architecture-core.md` (generated from the PHP source via `php-content-filter`).

> **Note:** Historical plan documents in `docs/agents/plans/` that reference `doSeedTests()` will continue to appear in the generated `framework-core-system-overview.md` — this is acceptable as historical context. Do NOT manually edit `.context/` generated files; they will be overwritten by `composer build`.

## Dependencies

- Steps 1–4 are independent and can be executed in parallel.
- Step 5 depends on Steps 2 and 1 being complete.

## Required Components

- `composer.json` (replace `shark/simple_html_dom` with `mistralys/simple_html_dom`)
- `src/classes/Application/Composer/ComposerScripts.php` (remove `doSeedTests()`)
- `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php` (PHPDoc updates)
- `tests/phpstan/cli-utilities-stubs.php` (new file)
- `phpstan.neon` (add stub to bootstrapFiles)
- `.context/modules/composer/architecture-core.md` (documentation update)
- `.context/framework-core-system-overview.md` (documentation update)

## Assumptions

- The HCP Editor's planned `seedTests()` feature (from `docs/agents/plans/2026-05-05-test-db-seed-data/plan.md`) has NOT been implemented yet and will not call `doSeedTests()`. If it is implemented in the future, it should use the CLI script pattern instead.
- `mistralys/simple_html_dom` is a drop-in replacement for `shark/simple_html_dom` and provides the same `str_get_html()` / `simple_html_dom` / `simple_html_dom_node` API. The HCP Editor's `FrozenTextParser.php` will continue to work without changes.
- The function signatures in `tools/include/cli-utilities.php` are stable and can be stubbed without frequent maintenance.

## Constraints

- Array syntax: `array()` only — never `[]`.
- No constructor promotion.
- `declare(strict_types=1)` in every new PHP file.
- Run `composer dump-autoload` after any class file modification.

## Out of Scope

- Replacing `str_get_html()` usage in the HCP Editor's `FrozenTextParser.php` — that belongs to an HCP Editor plan.
- The HCP Editor's own test database seeding feature (synthesis item #5 from the prior plan).
- Refactoring the CTX-generated documentation sections that reference older plan content (the plan references in `.context/framework-core-system-overview.md` are auto-generated and will be refreshed by `composer build`).
- Revising the HCP Editor plan `docs/agents/plans/2026-05-05-test-db-seed-data/plan.md` (which delegates to `doSeedTests()`) — that plan must be updated to use the CLI script pattern before implementation, but that revision belongs to an HCP Editor task.

## Acceptance Criteria

1. `composer.json` requires `mistralys/simple_html_dom` (stable `^2.0` constraint) instead of `shark/simple_html_dom`.
2. `composer install` completes without errors in the framework project.
3. `ComposerScripts::doSeedTests()` no longer exists in the source.
4. `composer seed-tests` still works correctly (uses CLI scripts, unaffected by removal).
5. `seedSystemUsers()`, `seedLocales()`, and `seedCountries()` all have bidirectional `@see` cross-references to each other.
6. `tests/phpstan/cli-utilities-stubs.php` exists and is referenced in `phpstan.neon`.
7. `composer analyze` passes with no new errors.
8. All modified documentation is consistent with the code changes.

## Testing Strategy

- **Step 1:** Run `composer install` and `composer analyze` after swapping the dependency. Run `composer test-filter -- Html` to verify no framework tests regress with the new package.
- **Step 2:** Run `composer test-filter -- Seed` and `composer seed-tests` to verify seeding still works.
- **Step 3:** PHPDoc-only change — verify with `composer analyze`.
- **Step 4:** Run `composer analyze` to verify no new errors from the stub.

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **HCP Editor breaks after `simple_html_dom` swap** | `mistralys/simple_html_dom` is a drop-in replacement providing the same `str_get_html()` API. The HCP Editor inherits the framework's dependency transitively — no changes needed there. |
| **Undiscovered caller of `doSeedTests()` in a deployment script** | Full workspace search confirmed zero callers. The method echoes to stdout, making it easy to detect if something was using it. |
| **PHPStan stub drifts from actual CLI utilities** | The utilities file is small (233 lines) and rarely changes. Add a comment in the stub pointing to the source file. |
| **`composer build` overwrites documentation changes** | Run `composer build` AFTER making documentation edits to `.context/` source configs, not the generated files themselves. Manual edits to `.context/` generated files will be overwritten. |
