# Plan

## Summary

Follow-up rework implementing all strategic recommendations from the `2026-03-13-api-caching-core-rework-1` synthesis report. The work covers five actionable items: wiring corrupt-cache logging with the reserved `ERROR_CACHE_FILE_CORRUPT` constant, applying the explicit `filemtime()` guard to the AI cache strategy, documenting the YAML keyword colon+space constraint in the module-context reference, adding the missing `@throws` annotation to `APICacheManager::invalidateMethod()`, and implementing a test-application consumer for `CountryRequestTrait` (the only currently unused trait). Recommendations 1 and 6 from the synthesis (trait.unused suppression policy) are resolved by restating the existing project policy: traits are never suppressed — the test application must implement concrete consumers.

## Architectural Context

### API Cache Module
- **Location:** `src/classes/Application/API/Cache/`
- **Key files:**
  - [CacheableAPIMethodTrait.php](src/classes/Application/API/Cache/CacheableAPIMethodTrait.php) — provides `readFromCache()` with silent corrupt-file recovery (lines 72–78)
  - [APICacheException.php](src/classes/Application/API/Cache/APICacheException.php) — defines `ERROR_CACHE_FILE_CORRUPT` (59213011), currently unreferenced in code
  - [APICacheManager.php](src/classes/Application/API/Cache/APICacheManager.php) — `invalidateMethod()` propagates `APICacheException` but lacks `@throws`
  - [README.md](src/classes/Application/API/Cache/README.md) — module documentation (recently rewritten in Phase 1)

### AI Cache Module
- **Location:** `src/classes/Application/AI/Cache/`
- **Key file:**
  - [Strategies/FixedDurationStrategy.php](src/classes/Application/AI/Cache/Strategies/FixedDurationStrategy.php) — `isCacheFileValid()` uses implicit `filemtime()` coercion (no explicit `=== false` guard)
- **Hardened counterpart for reference:** [API/Cache/Strategies/FixedDurationStrategy.php](src/classes/Application/API/Cache/Strategies/FixedDurationStrategy.php) — contains the explicit guard pattern to match

### Framework Logging
- **Logger access:** `AppFactory::createLogger()` returns an `Application_Logger` instance
- **Method:** `logError(string $message, ...$args)` for error/warning-level messages
- **Pattern:** Used throughout the framework for diagnostic logging

### Countries / CountryRequestTrait
- **Trait:** [src/classes/Application/Countries/Admin/Traits/CountryRequestTrait.php](src/classes/Application/Countries/Admin/Traits/CountryRequestTrait.php)
- **Interface:** [src/classes/Application/Countries/Admin/Traits/CountryRequestInterface.php](src/classes/Application/Countries/Admin/Traits/CountryRequestInterface.php) — extends `AdminScreenInterface`
- **Request type:** [src/classes/Application/Countries/Admin/CountryRequestType.php](src/classes/Application/Countries/Admin/CountryRequestType.php) — extends `BaseDBRecordRequestType`
- **Test application:** `tests/application/assets/classes/TestDriver/` — no Countries consumer exists yet
- **Existing patterns:** Other request type consumers exist in the test app (e.g., collection record screens)

### Module Context Reference
- **Location:** [docs/agents/references/module-context-reference.md](docs/agents/references/module-context-reference.md) — the single reference file for `module-context.yaml` authoring
- **Current state:** Documents `moduleMetaData` and `documents` sections but does not mention YAML syntax pitfalls for keyword values

### PHPStan Configuration
- **Location:** [phpstan.neon](phpstan.neon) — currently clean of `trait.unused` suppressions (removed in WP-004 of the original project)

## Approach / Architecture

The rework is six discrete, low-risk changes. No new architectural patterns are introduced — each change applies an existing pattern or extends existing documentation.

1. **Corrupt-cache logging (Rec 2):** Add a `logError()` call in the catch block of `CacheableAPIMethodTrait::readFromCache()`, referencing `ERROR_CACHE_FILE_CORRUPT` for context. The method already silently recovers; this adds operator observability without changing behaviour.

2. **AI `filemtime()` guard (Rec 3):** Mirror the API `FixedDurationStrategy` pattern in the AI counterpart — extract `filemtime()` into a local variable and add an explicit `=== false` check before the arithmetic comparison.

3. **YAML keyword constraint documentation (Rec 4):** Add a "Keyword Value Syntax" section to `docs/agents/references/module-context-reference.md` documenting the Symfony YAML colon+space parsing behaviour and the quoting requirement.

4. **`@throws` annotation (Rec 5):** Add `@throws APICacheException` to `APICacheManager::invalidateMethod()`.

5. **CountryRequestTrait test-app consumer (Rec 1 & 6 policy implementation):** Create a minimal admin screen in the test application that implements `CountryRequestInterface` and uses `CountryRequestTrait`. This provides PHPStan a consumer to analyze through and establishes the pattern for future trait consumers.

6. **Policy documentation (Rec 1 & 6):** Add a "Trait Consumer Policy" section to the project constraints document stating that `trait.unused` suppressions must never be added and that the test application must implement concrete consumers for all library traits.

## Rationale

- **Rec 1 & 6 consolidated:** The synthesis proposed a suppression-management policy. The user clarified the actual policy is stricter: never suppress, always implement a test-app consumer. This is the better approach because it provides both PHPStan coverage and regression testing simultaneously.
- **Logging over exceptions for corrupt cache:** The resilience design (silent delete + cache miss) is correct. Adding a log call preserves this behaviour while providing operator observability — systematic corruption will now be visible in application logs.
- **Mirroring the API guard pattern:** Consistency between AI and API cache strategies reduces cognitive load and prevents the same class of implicit-coercion bugs.
- **YAML documentation:** This constraint has caused multiple real issues already. Documenting it in the module-context reference is the most discoverable location for authors of `module-context.yaml` files.

## Detailed Steps

### Step 1: Add corrupt-cache logging to `CacheableAPIMethodTrait::readFromCache()`
- **File:** `src/classes/Application/API/Cache/CacheableAPIMethodTrait.php`
- In the catch block (currently lines 72–78), add a `logError()` call before the delete-and-return-null sequence.
- Use `AppFactory::createLogger()->logError()` with the cache file path and exception message for diagnostic context.
- Reference `APICacheException::ERROR_CACHE_FILE_CORRUPT` in the log message or as contextual info.
- Add `use Application\AppFactory;` import if not already present.

### Step 2: Apply explicit `filemtime()` guard to AI `FixedDurationStrategy`
- **File:** `src/classes/Application/AI/Cache/Strategies/FixedDurationStrategy.php`
- Replace the single-line `isCacheFileValid()` body with the two-step pattern from the API counterpart:
  1. `$mtime = filemtime($cacheFile->getPath());`
  2. `if($mtime === false) { return false; }`
  3. `return (time() - $mtime) < $this->durationInSeconds;`
- Add a PHPDoc block matching the API version's documentation style.

### Step 3: Add `@throws` annotation to `APICacheManager::invalidateMethod()`
- **File:** `src/classes/Application/API/Cache/APICacheManager.php`
- Add a docblock above `invalidateMethod()` with `@throws APICacheException` (error code: `ERROR_INVALID_METHOD_NAME`, propagated from `getMethodCacheFolder()`).

### Step 4: Document YAML keyword colon+space constraint
- **File:** `docs/agents/references/module-context-reference.md`
- Add a new section (e.g., "Keyword Value Syntax Constraints") documenting:
  - Symfony YAML parses bare `word: text` as a mapping key, not a string scalar.
  - Keyword values containing `: ` (colon followed by space) must be quoted.
  - Example: `"CacheableAPIMethodTrait: provides caching"` (quoted) vs `CacheableAPIMethodTrait: provides caching` (broken — parsed as mapping).
  - Consequence: `ModulesOverviewGenerator::buildModuleInfo()` receives arrays instead of strings, causing `Array to string conversion` errors in `composer build`.

### Step 5: Implement `CountryRequestTrait` consumer in test application
- **New file:** `tests/application/assets/classes/TestDriver/Admin/Area/Countries/` (or appropriate test-app admin area)
- Create a minimal admin screen class that:
  - Extends the appropriate test-app admin area base class
  - Implements `CountryRequestInterface`
  - Uses `CountryRequestTrait`
- The class needs only minimal method stubs to satisfy the interface contracts — it exists to give PHPStan a consumer and to demonstrate the trait's integration pattern.
- The Countries collection is available in the test application by default — no special setup needed.
- Run `composer dump-autoload` after creating the file.

### Step 6: Document trait consumer policy in constraints
- **File:** `docs/agents/project-manifest/constraints.md`
- Add a section titled "Trait Consumer Policy" or similar, documenting:
  - `trait.unused` PHPStan suppressions must never be added to `phpstan.neon`.
  - The test application (`tests/application/`) must implement concrete consumers for all library traits.
  - If a trait is unused, the correct action is to create a test-app consumer class, not to suppress the PHPStan notice.
  - Reason: `trait.unused` suppression disables static analysis of the trait's entire method body, creating a blind spot for type errors and logic bugs.

### Step 7: Run verification
- Run `composer dump-autoload` (for the new test-app class).
- Run `composer analyze` to verify the `CountryRequestTrait` consumer resolves PHPStan notices and introduces no new errors.
- Run `composer test-filter -- CountryRequest` if any related tests exist, or run `composer test-suite -- api-cache` to verify no regressions in cache tests.
- Run `composer build` to regenerate `.context/` documentation.

## Dependencies

- Steps 1–4 are fully independent and can be implemented in any order.
- Step 5 (CountryRequestTrait consumer) may require investigation of the test-application's admin area structure to determine the correct base class and registration pattern.
- Step 6 depends on the policy being confirmed (confirmed by user).
- Step 7 depends on all prior steps.

## Required Components

### Modified Files
- `src/classes/Application/API/Cache/CacheableAPIMethodTrait.php` — add logging
- `src/classes/Application/AI/Cache/Strategies/FixedDurationStrategy.php` — add `filemtime()` guard
- `src/classes/Application/API/Cache/APICacheManager.php` — add `@throws` annotation
- `docs/agents/references/module-context-reference.md` — add YAML constraint section
- `docs/agents/project-manifest/constraints.md` — add trait consumer policy section

### New Files
- `tests/application/assets/classes/TestDriver/Admin/Area/Countries/...` — CountryRequestTrait consumer (exact path TBD based on test-app conventions)

### No External Services or Infrastructure Changes

## Assumptions

- The framework logger (`AppFactory::createLogger()`) is available in the context where `CacheableAPIMethodTrait::readFromCache()` executes at runtime (API method processing context).
- The test application's admin area supports registering new screen classes that implement `AdminScreenInterface` without additional wiring beyond class creation and autoload.
- The `Application_Countries` collection referenced by `CountryRequestType` is available in the test application by default.

## Constraints

- All PHP code must use `array()` syntax (not `[]`).
- All new files must use `declare(strict_types=1);`.
- Classmap autoloading requires `composer dump-autoload` after adding new files.
- The full PHPUnit test suite must not be run — only targeted test commands.

## Out of Scope

- Whitespace-only identifier guard for `UserScopedCacheTrait` (synthesis marks as "Future" — deferred until user identifiers originate from untrusted input).
- `@package/@subpackage` annotation alignment on AI cache strategy classes (cosmetic, minimal impact).
- API Cache README updates beyond what logging changes require (README was fully rewritten in Phase 1).
- Full audit of every trait in the codebase — this rework addresses the one known unused trait (`CountryRequestTrait`) and documents the policy for future cases.

## Acceptance Criteria

1. `CacheableAPIMethodTrait::readFromCache()` logs a warning-level message (including file path and exception message) when a corrupt cache file is encountered and deleted.
2. `APICacheException::ERROR_CACHE_FILE_CORRUPT` is no longer dead code — it is referenced in the logging call.
3. `Application\AI\Cache\Strategies\FixedDurationStrategy::isCacheFileValid()` uses an explicit `$mtime === false` guard, matching the API counterpart's pattern.
4. `APICacheManager::invalidateMethod()` has a `@throws APICacheException` annotation in its docblock.
5. `docs/agents/references/module-context-reference.md` documents the Symfony YAML colon+space parsing constraint for keyword values.
6. A test-application admin screen class exists that implements `CountryRequestInterface` and uses `CountryRequestTrait`.
7. `docs/agents/project-manifest/constraints.md` documents the trait consumer policy (never suppress `trait.unused`, always implement a test-app consumer).
8. `composer analyze` produces no new errors related to the changes.
9. `composer build` completes successfully.

## Testing Strategy

| Scope | Command | Purpose |
|---|---|---|
| API Cache suite | `composer test-suite -- api-cache` | Verify no regressions in cache tests after logging addition |
| CountryRequest | `composer test-filter -- CountryRequest` | Verify any existing tests still pass |
| PHPStan | `composer analyze` | Verify CountryRequestTrait consumer resolves notices, no new errors |
| Build | `composer build` | Verify `.context/` regeneration and module docs |

No new unit tests are required for this rework:
- The logging addition is a side-effect-only change inside an existing catch block — the existing `CacheResilienceTest` already covers the corrupt-file recovery path.
- The `filemtime()` guard is a defensive hardening of an edge case that is impractical to unit-test (requires mocking `filemtime()` at the C level).
- The `@throws` annotation and documentation changes are non-functional.
- The CountryRequestTrait consumer exists to provide PHPStan coverage, not to test business logic.

## Risks & Mitigations

| Risk | Mitigation |
|---|---|
| **Logger unavailable in API method context** | `AppFactory::createLogger()` is a static factory available throughout the framework lifecycle. Verify during implementation that the import resolves and the method exists in the trait's execution context. |
| **CountryRequestType constructor expectations** | The Countries collection is available in the test application by default, so no special stubbing is needed. The consumer class can use the trait directly. |
| **YAML documentation section placement** | Place the new section prominently (near the top of the keywords subsection) to maximize discoverability. Reference it from the project manifest constraints as well. |
