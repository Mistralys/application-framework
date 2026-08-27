# Synthesis Report — API Caching Core (Phase 1)

**Plan:** `2026-03-13-api-caching-core`
**Date:** 2026-03-13
**Status:** COMPLETE
**Work Packages:** 7 / 7 COMPLETE

---

## Executive Summary

Phase 1 of the API response caching system has been fully implemented in the Application Framework. The feature introduces a transparent, opt-in cache-aside mechanism for API methods, integrated directly into `BaseAPIMethod._process()`. Any API method that implements `CacheableAPIMethodInterface` and uses `CacheableAPIMethodTrait` automatically benefits from caching with no changes to its internal logic.

The implementation follows the established `DryRunAPIInterface` / `DryRunAPITrait` extensibility pattern exactly — zero new architectural patterns were introduced. Cache storage is file-based (JSON), structured under the application storage folder, and surfaced in the CacheControl admin UI via the standard `BaseRegisterCacheLocationsListener` auto-discovery mechanism.

**New files delivered (8 source + 3 test + 2 config + 3 documentation):**

| File | Purpose |
|---|---|
| `src/.../API/Cache/APICacheStrategyInterface.php` | Strategy contract |
| `src/.../API/Cache/Strategies/FixedDurationStrategy.php` | TTL-based strategy (7 constants) |
| `src/.../API/Cache/Strategies/ManualOnlyStrategy.php` | Manual-invalidation-only strategy |
| `src/.../API/Cache/APICacheManager.php` | Static cache folder manager |
| `src/.../API/Cache/CacheableAPIMethodInterface.php` | Opt-in caching contract for API methods |
| `src/.../API/Cache/CacheableAPIMethodTrait.php` | Cache-aside implementation |
| `src/.../API/Cache/APIResponseCacheLocation.php` | CacheControl admin UI integration |
| `src/.../API/Events/RegisterAPIResponseCacheListener.php` | Auto-discovered listener |
| `src/.../API/BaseMethods/BaseAPIMethod.php` | Modified: transparent cache check + write hooks |
| `src/.../API/Cache/README.md` | Module usage documentation |
| `src/.../API/Cache/module-context.yaml` | CTX Generator registration |
| `tests/.../API/Cache/APICacheStrategyTest.php` | 13 unit tests |
| `tests/.../API/Cache/APICacheIntegrationTest.php` | 6 integration tests |
| `tests/.../TestDriver/API/TestCacheableMethod.php` | Integration test stub |
| `changelog.md`, `README.md` | Updated with v7.0.11 entry |
| `docs/agents/project-manifest/testing.md` | Updated: env setup + API stub placement gotcha |

---

## Metrics

| Metric | Value |
|---|---|
| WPs completed | 7 / 7 |
| Pipelines passed | 28 / 28 (7 × implementation + qa + code-review + documentation) |
| Unit tests | 13 PASS / 0 FAIL |
| Integration tests | 6 PASS / 0 FAIL |
| **Total tests** | **19 PASS / 0 FAIL** |
| New PHPStan errors | **0** |
| Pre-existing PHPStan errors | 7 (unrelated files, pre-existing) |
| Pre-existing test failures | 2 HtaccessGeneratorTest (unrelated, feature-openapi-specs branch) |

---

## Blockers & Known Issues

### Resolved During This Session
- **Vendor packages not installed** — PHPUnit and PHPStan were unavailable during WP-001 through WP-005. Resolved in WP-006 by configuring the local test environment (`.dist` config files copied and credentials set). All 19 tests pass after resolution.

### Pre-existing (Not Introduced by This Work)
- **7 PHPStan errors** in `CountryRequestTrait.php`, `BaseDBRecordRequestType.php`, `FetchMany.php`, `PropertiesGrid.php`, `Submode.php`, `frame.footer.php`, `DisposingTest.php` — existed before this feature branch.
- **2 HtaccessGeneratorTest failures** (`test_defaultRewriteBase`, `test_defaultRewriteBaseConstant`) — introduced by the `feature-openapi-specs` branch (commit `727bc18d`), unrelated to caching.

---

## Strategic Recommendations

### Security — High Priority

> **Cache user isolation responsibility is on the implementing method.**

`BaseAPIMethod._process()` bypasses `collectRequestData()` entirely on a cache hit — including any per-user authorization or row-level access checks performed there. `CacheableAPIMethodInterface` now carries a class-level docblock warning. **Implementors must include user-identifying parameters (e.g., user ID, session scope) in `getCacheKeyParameters()` for any method returning user-scoped data.** This is the most important constraint to communicate to future developers adopting this feature.

### Medium Priority — Defensive Hardening (Post-Phase-1 Pass)

1. **`APICacheManager::getMethodCacheFolder()` input validation** — `$methodName` is concatenated directly into a filesystem path with no sanitization. Current callers always pass internal API method class constants (safe), but the lack of a precondition guard is a latent footgun. Add: `if (empty($methodName) || str_contains($methodName, DIRECTORY_SEPARATOR)) { throw new \InvalidArgumentException(...); }`. The `invalidateMethod('') === clearAll()` scenario is particularly dangerous.

2. **`readFromCache()` corrupt-cache resilience** — `JSONFile::parse()` is called without a try-catch. A partially-written or corrupted JSON file will surface as an unhandled exception from `BaseAPIMethod._process()`, failing the entire API request rather than falling back to fresh computation. A catch-and-return-null wrapper would make the cache layer transparent to callers on corruption.

### Low Priority — Code Quality

| Item | Location | Action |
|---|---|---|
| `serialize()` → `json_encode()` for cache key | `CacheableAPIMethodTrait::getCacheKey()` | `json_encode()` is more portable (no `__sleep()` edge cases) and makes the intent (scalar params only) more explicit |
| `filemtime() === false` explicit guard | `FixedDurationStrategy::isCacheFileValid()` | Current behavior is safe (expires-closed fallback) but PHPStan may flag the arithmetic on `false` at strict levels |
| `getID()` is untested and unused at runtime | `APICacheStrategyInterface` | Either add a test or document intended future use (e.g., strategy-aware invalidation, cache statistics) |
| `@phpstan-require-implements` on trait | `CacheableAPIMethodTrait` | Static enforcement that the trait is never used without `CacheableAPIMethodInterface` |
| STRATEGY_ID constant missing | `FixedDurationStrategy`, `ManualOnlyStrategy` | The AI cache equivalent defines `STRATEGY_ID` as a typed constant enabling strategy-switching without magic strings |
| `instanceof` fan-out in `_process()` | `BaseAPIMethod.php` | Two `instanceof` guards are clean now; consider a middleware/pipeline pattern if a third cross-cutting concern is added |
| Class-level PHPDoc missing | `FixedDurationStrategy`, `ManualOnlyStrategy` | All other cache classes have `@package`/`@subpackage` annotations; bring strategy classes to the same standard |
| `@package` annotation inconsistency | `RegisterAPIResponseCacheListener` | Differs from sibling `RegisterAPIIndexCacheListener`; align to framework convention |

### Architectural Insight — Gold Nugget

> **`TestCacheableMethod::getCollectCount()` is an excellent zero-mock observability pattern** for API method tests that need to distinguish cache hits (count unchanged) from fresh executions (count incremented). This technique is worth adopting in all future API method tests that need to verify execution-path branching without mocking `BaseAPIMethod`.

---

## Next Steps

### Immediate (Before Merging)
1. **Run `composer build`** — regenerate `.context/modules/api-cache/` from the new `module-context.yaml`. This is required for the CTX documentation to reflect the new module.
2. **Merge to main** — no blocking issues remain; all 28 pipelines PASS.

### Phase 2 Planning
3. **`DBHelperAwareStrategy`** — cache invalidation tied to database record changes (listed as Phase 2 in `docs/agents/projects/api-caching-system.md`). This is the highest-value next increment.
4. **Defensive hardening pass** — address the `getMethodCacheFolder()` input validation and `readFromCache()` corrupt-cache resilience items above before the first production consumers are onboarded.

### Maintenance
5. **Address 2 HtaccessGeneratorTest failures** on the `feature-openapi-specs` branch — pre-existing but should not land on `main` unresolved.
6. **Address 7 pre-existing PHPStan errors** in a dedicated cleanup pass.
7. **AI cache alignment** — `FixedDurationStrategy` has 7 duration constants; the `Application\AI\Cache\Strategies` equivalent has 4. Decide whether to align them, and document strategy ID naming conventions (`FixedDuration` PascalCase vs `fixed_duration` snake_case) across the two systems.

---

## Deliverables Checklist

- [x] `APICacheStrategyInterface` + strategy classes (WP-001)
- [x] `APICacheManager` static folder manager (WP-002)
- [x] `CacheableAPIMethodInterface` + `CacheableAPIMethodTrait` (WP-003)
- [x] `BaseAPIMethod._process()` transparent cache-aside integration (WP-004)
- [x] `APIResponseCacheLocation` + listener for CacheControl admin UI (WP-005)
- [x] 19 passing tests: 13 unit + 6 integration (WP-006)
- [x] PHPStan: 0 new errors (WP-007)
- [x] `src/classes/Application/API/Cache/README.md` module documentation
- [x] `module-context.yaml` CTX Generator registration
- [x] `changelog.md` v7.0.11 entry
- [x] `docs/agents/project-manifest/testing.md` updated (env setup + API stub placement)
- [ ] `composer build` — pending (generates `.context/modules/api-cache/`)
