# Plan

## Summary

Follow-up plan implementing the remaining actionable strategic recommendations from the `2026-03-13-api-caching-core-rework-1-rework-1` synthesis. Three items from the synthesis have been resolved since it was produced ([C] AI Cache CTX docs, [D] phpunit.xml alignment, [H] CountryRequestTrait guard). This plan addresses the five remaining items, prioritized by operational impact.

## Architectural Context

### PHPUnit Test Infrastructure

The framework's test suite lives under `tests/AppFrameworkTests/` and is configured in `phpunit.xml` with a single suite (`Framework Tests`) that discovers all `.php` files in that directory tree. Modern test files use the `AppFrameworkTests\{Subdirectory}` namespace pattern with class names ending in `Test` and file names matching the class name (e.g., `EventTest.php` → `AppFrameworkTests\SessionTests\EventTest`). However, 19 legacy test files use non-namespaced underscore-prefixed class names (e.g., class `Application_MessagelogsTest` in `Messagelogs.php`), causing PHPUnit "Class X cannot be found" warnings because PHPUnit expects the class name to match the file name.

### API & AI Cache Strategies

Both the API Cache module (`src/classes/Application/API/Cache/Strategies/`) and the AI Cache module (`src/classes/Application/AI/Cache/Strategies/`) provide a `FixedDurationStrategy` class with duration constants. The two classes are independent (separate namespaces, separate interfaces) but serve the same conceptual purpose. Their constant naming conventions diverge:

- **API module:** `DURATION_1HOUR`, `DURATION_6HOURS` (no underscore before unit), plus short-duration constants (`DURATION_1MIN`, `DURATION_5MIN`, `DURATION_15MIN`)
- **AI module:** `DURATION_1_HOUR`, `DURATION_6_HOURS` (underscore before unit), no short-duration constants

Consumers of the API constants: 3 test files. Consumers of the AI constants: 2 production files (`GetCountryConfigTool`, `ListCountriesTool`).

### CacheableAPIMethodTrait

`src/classes/Application/API/Cache/CacheableAPIMethodTrait.php` contains `readFromCache()`, which handles corrupt cache files in a catch block. The `$cacheFile->delete()` call is wrapped in a defensive inner try/catch, but the preceding `logError()` call is not — meaning a logger failure would propagate the exception instead of falling through to `return null`.

### Design-Archive Documents

`docs/agents/projects/api-caching-system.md` is a historical design specification with embedded code snippets that can drift from the current implementation. It has no header indicating its archival status.

## Approach / Architecture

Five independent changes, each self-contained and testable in isolation:

1. **Namespace 19 legacy test files** — add `namespace AppFrameworkTests\{Dir};`, rename classes from `Category_NameTest` to `NameTest`, rename files to `NameTest.php`. This eliminates PHPUnit class-discovery warnings and restores clean exit codes.

2. **Harmonize FixedDurationStrategy constants** — adopt the AI module's underscore convention (`DURATION_1_HOUR`) as the standard (more readable); update the API module's constants to match and add the missing short-duration constants to the AI module.

3. **Wrap logError() in defensive try/catch** — enclose the `logError()` call in `readFromCache()`'s corrupt-cache catch block in its own inner try/catch, matching the pattern already used for `$cacheFile->delete()`.

4. **Update readFromCache() PHPDoc** — add a line documenting the corrupt-cache recovery behavior (log + delete + return null).

5. **Add design-archive header** — prepend a callout block to `docs/agents/projects/api-caching-system.md` marking it as a historical design document.

## Rationale

- **Underscore convention for constants** was chosen because `DURATION_1_HOUR` is more readable than `DURATION_1HOUR`, aligning with PHP constant naming best practices where word boundaries are underscored. The AI module already uses this convention. Since both modules are relatively new with limited consumers, now is the cheapest time to standardize.
- **Test file namespacing** is prioritized highest because the exit-code issue silently undermines CI reliability for every test run, not just cache-related ones.
- **Items [C], [D], and [H]** are excluded because they are already resolved: the AI/API modules are fully documented in CTX, phpunit.xml has a single suite by design with documentation matching, and the `CountryRequestTrait` guard is a non-blocking stylistic preference.

## Detailed Steps

### Step 1: Namespace legacy test files (resolves synthesis item [B])

For each of the 19 affected files:

| # | Current file path | Current class name | New file path | New class name | Namespace |
|---|---|---|---|---|---|
| 1 | `tests/AppFrameworkTests/Application/Messagelogs.php` | `Application_MessagelogsTest` | `tests/AppFrameworkTests/Application/MessagelogsTest.php` | `MessagelogsTest` | `AppFrameworkTests\Application` |
| 2 | `tests/AppFrameworkTests/Application/Settings.php` | `Application_SettingsTest` | `tests/AppFrameworkTests/Application/SettingsTest.php` | `SettingsTest` | `AppFrameworkTests\Application` |
| 3 | `tests/AppFrameworkTests/Collection/IntegerRecord.php` | `Collection_IntegerRecordTest` | `tests/AppFrameworkTests/Collection/IntegerRecordTest.php` | `IntegerRecordTest` | `AppFrameworkTests\Collection` |
| 4 | `tests/AppFrameworkTests/Disposables/Core.php` | `Disposables_CoreTest` | `tests/AppFrameworkTests/Disposables/CoreTest.php` | `CoreTest` | `AppFrameworkTests\Disposables` |
| 5 | `tests/AppFrameworkTests/Forms/DefaultValues.php` | `Forms_DefaultValuesTest` | `tests/AppFrameworkTests/Forms/DefaultValuesTest.php` | `DefaultValuesTest` | `AppFrameworkTests\Forms` |
| 6 | `tests/AppFrameworkTests/Functions/subsetsum.php` | `Functions_SubsetSumTest` | `tests/AppFrameworkTests/Functions/SubsetSumTest.php` | `SubsetSumTest` | `AppFrameworkTests\Functions` |
| 7 | `tests/AppFrameworkTests/Global/Enums.php` | `Global_EnumsTest` | `tests/AppFrameworkTests/Global/EnumsTest.php` | `EnumsTest` | `AppFrameworkTests\Global` |
| 8 | `tests/AppFrameworkTests/Installer/Core.php` | `Installer_CoreTest` | `tests/AppFrameworkTests/Installer/CoreTest.php` | `CoreTest` | `AppFrameworkTests\Installer` |
| 9 | `tests/AppFrameworkTests/OAuth/Strategies.php` | `OAuth_StrategiesTest` | `tests/AppFrameworkTests/OAuth/StrategiesTest.php` | `StrategiesTest` | `AppFrameworkTests\OAuth` |
| 10 | `tests/AppFrameworkTests/Ratings/General.php` | `Ratings_GeneralTest` | `tests/AppFrameworkTests/Ratings/GeneralTest.php` | `GeneralTest` | `AppFrameworkTests\Ratings` |
| 11 | `tests/AppFrameworkTests/Ratings/URL.php` | `Ratings_URLTest` | `tests/AppFrameworkTests/Ratings/URLTest.php` | `URLTest` | `AppFrameworkTests\Ratings` |
| 12 | `tests/AppFrameworkTests/UI/Icons.php` | `UI_IconsTest` | `tests/AppFrameworkTests/UI/IconsTest.php` | `IconsTest` | `AppFrameworkTests\UI` |
| 13 | `tests/AppFrameworkTests/UI/Statuses.php` | `UI_StatusesTest` | `tests/AppFrameworkTests/UI/StatusesTest.php` | `StatusesTest` | `AppFrameworkTests\UI` |
| 14 | `tests/AppFrameworkTests/User/Notepad.php` | `User_NotepadTest` | `tests/AppFrameworkTests/User/NotepadTest.php` | `NotepadTest` | `AppFrameworkTests\User` |
| 15 | `tests/AppFrameworkTests/User/Recent.php` | `User_RecentTest` | `tests/AppFrameworkTests/User/RecentTest.php` | `RecentTest` | `AppFrameworkTests\User` |
| 16 | `tests/AppFrameworkTests/User/Rights.php` | `User_RightsTest` | `tests/AppFrameworkTests/User/RightsTest.php` | `RightsTest` | `AppFrameworkTests\User` |
| 17 | `tests/AppFrameworkTests/User/Settings.php` | `User_SettingsTest` | `tests/AppFrameworkTests/User/SettingsTest.php` | `SettingsTest` | `AppFrameworkTests\User` |
| 18 | `tests/AppFrameworkTests/User/Statistics.php` | `User_StatisticsTest` | `tests/AppFrameworkTests/User/StatisticsTest.php` | `StatisticsTest` | `AppFrameworkTests\User` |
| 19 | `tests/AppFrameworkTests/User/SystemUsers.php` | `User_SystemUsersTest` | `tests/AppFrameworkTests/User/SystemUsersTest.php` | `SystemUsersTest` | `AppFrameworkTests\User` |

For each file:
1. Add `declare(strict_types=1);` if missing.
2. Add `namespace AppFrameworkTests\{Directory};` declaration.
3. Rename the class from `Category_NameTest` to `NameTest`.
4. Rename the file to `NameTest.php` (matching the class name).
5. Delete the old file.

**Important notes:**
- Verify that each file's actual class name matches the expected pattern before modifying (some may deviate).
- `Global` is a reserved word in PHP — verify whether `namespace AppFrameworkTests\Global;` causes issues. If it does, use `AppFrameworkTests\GlobalTests` instead (or another non-reserved name).
- After all renames: run `composer dump-autoload` to update the classmap.
- Validate with `composer test-filter -- Test` and confirm zero PHPUnit warnings in stderr.

### Step 2: Harmonize FixedDurationStrategy constant naming (resolves synthesis item [E])

**Target convention:** Underscore-separated (`DURATION_1_HOUR`, `DURATION_5_MIN`).

**API module** (`src/classes/Application/API/Cache/Strategies/FixedDurationStrategy.php`):
- Rename `DURATION_1MIN` → `DURATION_1_MIN`
- Rename `DURATION_5MIN` → `DURATION_5_MIN`
- Rename `DURATION_15MIN` → `DURATION_15_MIN`
- Rename `DURATION_1HOUR` → `DURATION_1_HOUR`
- Rename `DURATION_6HOURS` → `DURATION_6_HOURS`
- Rename `DURATION_12HOURS` → `DURATION_12_HOURS`
- Rename `DURATION_24HOURS` → `DURATION_24_HOURS`
- Update internal `__construct()` default parameter reference.

**Update all consumers of the API constants** (3 test files):
- `tests/application/assets/classes/TestDriver/API/TestCacheableMethod.php` — update `DURATION_1HOUR` → `DURATION_1_HOUR`
- `tests/application/assets/classes/TestDriver/API/TestUserScopedMethod.php` — update `DURATION_1HOUR` → `DURATION_1_HOUR`
- `tests/AppFrameworkTests/API/Cache/APICacheIntegrationTest.php` — update `DURATION_1HOUR` → `DURATION_1_HOUR`
- `tests/AppFrameworkTests/API/Cache/APICacheStrategyTest.php` — update `DURATION_1HOUR` → `DURATION_1_HOUR`

**AI module** (`src/classes/Application/AI/Cache/Strategies/FixedDurationStrategy.php`):
- Add the missing short-duration constants:
  ```php
  public const int DURATION_1_MIN = 60;
  public const int DURATION_5_MIN = 300;
  public const int DURATION_15_MIN = 900;
  ```
- No existing constants need renaming (AI module already uses the underscore convention).

**No consumer updates needed for the AI module** — `GetCountryConfigTool` and `ListCountriesTool` use `DURATION_24_HOURS`, which is unchanged.

After all changes: run API Cache and AI tests to validate.

### Step 3: Defensive logger wrapping (resolves synthesis item [A])

In `src/classes/Application/API/Cache/CacheableAPIMethodTrait.php`, in the `readFromCache()` method's `catch(\Throwable $e)` block, wrap the `logError()` call in its own inner try/catch:

```php
catch(\Throwable $e)
{
    // Cache file is corrupt — log the event for operator observability, then
    // delete the file best-effort and signal a cache miss.
    try
    {
        AppFactory::createLogger()->logError(
            sprintf(
                'Corrupt API cache file detected and deleted (error code %d). Path: %s | Error: %s',
                APICacheException::ERROR_CACHE_FILE_CORRUPT,
                $cacheFile->getPath(),
                $e->getMessage()
            )
        );
    }
    catch(\Throwable $ignored) {}

    try { $cacheFile->delete(); } catch(\Throwable $ignored) {}
    return null;
}
```

This ensures that a logger failure during bootstrap does not prevent the cache-miss fallthrough.

### Step 4: Update readFromCache() PHPDoc (resolves synthesis item [G])

In the same file, update the PHPDoc of `readFromCache()` to:

```php
/**
 * Reads response data from the cache file for the given version.
 * Returns null if the cache file does not exist or is no longer valid
 * according to the configured strategy. If the cache file is corrupt
 * (parse failure), logs an error, deletes the file, and returns null.
 *
 * @param string $version
 * @return array|null
 */
```

### Step 5: Add design-archive header (resolves synthesis item [F])

Prepend the following callout block at the top of `docs/agents/projects/api-caching-system.md`, before the `# Project: API Response Caching System` heading:

```markdown
> **Design Archive** — This document is a historical design specification.
> Code snippets may not reflect the current implementation.
> For authoritative documentation, see the module's `README.md`
> and generated `.context/` files.

```

## Dependencies

- Steps 1–5 are independent of each other and can be executed in any order or in parallel.
- Step 1 requires `composer dump-autoload` after file renames.
- Step 2 requires checking the Maileditor workspace for downstream consumers of the renamed API constants (verified: none found — the Maileditor does not reference `FixedDurationStrategy` in its own code).

## Required Components

**Modified files:**

| File | Step |
|---|---|
| 19 test files under `tests/AppFrameworkTests/` (renamed + modified) | 1 |
| `src/classes/Application/API/Cache/Strategies/FixedDurationStrategy.php` | 2 |
| `src/classes/Application/AI/Cache/Strategies/FixedDurationStrategy.php` | 2 |
| `tests/application/assets/classes/TestDriver/API/TestCacheableMethod.php` | 2 |
| `tests/application/assets/classes/TestDriver/API/TestUserScopedMethod.php` | 2 |
| `tests/AppFrameworkTests/API/Cache/APICacheIntegrationTest.php` | 2 |
| `tests/AppFrameworkTests/API/Cache/APICacheStrategyTest.php` | 2 |
| `src/classes/Application/API/Cache/CacheableAPIMethodTrait.php` | 3, 4 |
| `docs/agents/projects/api-caching-system.md` | 5 |

**No new files created** (only renames and modifications).

## Assumptions

- The 19 affected test files all follow the `Category_NameTest` class naming pattern. Each file's actual class name should be verified before modifying.
- `Global` as a PHP namespace segment may be a reserved word conflict — the engineer must verify and use `GlobalTests` if needed.
- No downstream consumers of the API `FixedDurationStrategy` constants exist outside the framework (verified for the Maileditor workspace; other consuming applications are not checked).

## Constraints

- Use `array()` syntax, not `[]` — hard project rule.
- All new/modified files must use `declare(strict_types=1);`.
- Run `composer dump-autoload` after file renames (classmap autoloading).
- Do not run the full test suite; use targeted `composer test-filter` commands.

## Out of Scope

- [C] AI Cache module CTX documentation — already complete.
- [D] Named test suites in phpunit.xml — single suite is by design; documentation is aligned.
- [H] `CountryRequestTrait` `isset()` vs `=== null` guard — stylistic, non-blocking, consistent with project patterns.
- Adding `DURATION_*` constants for other time units (e.g., days, weeks) — not requested.
- Upstream CI pipeline configuration changes — this plan focuses on framework code only.

## Acceptance Criteria

1. Running `composer test-filter -- Test` produces **zero** PHPUnit "Class X cannot be found" warnings.
2. The test command exits with code 0 when all tests pass (no spurious exit-code 1 from warnings).
3. Both `FixedDurationStrategy` classes use the `DURATION_X_UNIT` underscore convention for all duration constants.
4. The AI `FixedDurationStrategy` has `DURATION_1_MIN`, `DURATION_5_MIN`, `DURATION_15_MIN` constants.
5. `readFromCache()`'s `logError()` call is wrapped in a defensive try/catch.
6. `readFromCache()`'s PHPDoc documents the corrupt-cache recovery behavior.
7. `docs/agents/projects/api-caching-system.md` begins with a design-archive callout.
8. All existing API Cache and AI Cache tests pass after changes.
9. PHPStan introduces no new errors (`composer analyze`).
10. `composer build` completes successfully.

## Testing Strategy

| Scope | Command | Validates |
|---|---|---|
| API Cache tests | `composer test-filter -- APICacheManager` | Steps 2, 3, 4 (constant renames, defensive wrapping) |
| API Cache strategy tests | `composer test-filter -- APICacheStrategy` | Step 2 (constant renames) |
| All renamed test files | `composer test-filter -- Test` (broader) | Step 1 (no warning regressions) |
| PHPStan | `composer analyze` | No new static analysis errors |
| Build | `composer build` | CTX regeneration, autoload integrity |

Focus on verifying that **zero PHPUnit warnings** appear in any test run after Step 1 is complete.

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **`Global` namespace is a PHP reserved word** | Verify during implementation; use `AppFrameworkTests\GlobalTests` as fallback. |
| **Downstream applications reference renamed API constants** | Verified: Maileditor does not reference `FixedDurationStrategy`. Other downstream apps should be checked before releasing a framework version bump. Consider documenting the constant rename in the changelog as a breaking change. |
| **Test file renames break cross-references** | Test files are leaf nodes — they are not imported or referenced by other code. Verified by the non-namespaced nature of the current classes. |
| **Renamed test class names collide** | Two files will become `CoreTest` (Disposables and Installer) — but they are in different namespaces (`AppFrameworkTests\Disposables\CoreTest` vs `AppFrameworkTests\Installer\CoreTest`), so no collision occurs. Similarly for `SettingsTest` (Application vs User). |
