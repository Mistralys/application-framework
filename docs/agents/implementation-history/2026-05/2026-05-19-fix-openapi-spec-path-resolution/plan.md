# Plan

## Plan Audit Cycles
- Audits: none — Plan Auditor v1.3.0
- Architectural Reviews: none — Plan Architect Reviewer v1.4.0

## Summary

Fix the `GetOpenAPISpec` API method which resolves the path to `openapi.json` using `APP_INSTALL_FOLDER` (framework `src/` directory) instead of `APP_ROOT` (application root where the file actually lives). This causes a runtime 500 error even though `composer build` correctly generates the file. Additionally, wire up the `generate-openapi-spec` Composer script that the error message references but which doesn't exist.

## Architectural Context

The OpenAPI subsystem spans two layers:

- **Framework** (`src/classes/Application/API/OpenAPI/`): Contains `OpenAPIGenerator` (orchestrator), `GetOpenAPISpec` (runtime API method that serves the pre-generated spec), and supporting converters.
- **HCP Editor** (`assets/classes/Maileditor/Composer/ComposerScripts.php`): Drives the build pipeline and writes the spec to the correct location.

Key constants defined in `bootstrap.php`:
- `APP_ROOT = __DIR__` → the application root (e.g. `<HCP_ROOT>`)
- `APP_INSTALL_FOLDER = __DIR__ . '/vendor/mistralys/application_framework/src'` → the framework source directory

The build pipeline writes `openapi.json` to `APP_ROOT.'/api/openapi.json'` via:
- HCP Editor: `self::$rootFolder->getPath().'/api/openapi.json'` (line 167 of `Maileditor\Composer\ComposerScripts`)
- Framework test app: `tests/application/api/openapi.json` (via `getFrameworkAPIOutputDirectory()`)

At runtime, `GetOpenAPISpec::resolveSpecPath()` looks for the file at `APP_INSTALL_FOLDER.'/api/openapi.json'` — a path that **never** matches where the build writes the file.

**Files involved:**
- `src/classes/Application/API/OpenAPI/GetOpenAPISpec.php` (line 209–215) — path resolution
- `src/classes/Application/API/OpenAPI/OpenAPIGenerator.php` (line 93) — constructor default fallback
- `composer.json` — missing `generate-openapi-spec` script

## Approach / Architecture

1. Change `GetOpenAPISpec::resolveSpecPath()` to use `APP_ROOT` instead of `APP_INSTALL_FOLDER`.
2. Change the `OpenAPIGenerator` constructor default to use `APP_ROOT` for consistency.
3. Add `generate-openapi-spec` and `generate-htaccess` Composer script entries to the framework's `composer.json`.

This ensures that both the build-time write path and the runtime read path resolve to the same location (`APP_ROOT/api/openapi.json`), which works correctly in all environments:
- Framework test app: `APP_ROOT` = `tests/application` → `tests/application/api/openapi.json`
- HCP Editor: `APP_ROOT` = project root → `api/openapi.json`

## Rationale

`APP_ROOT` is the correct constant because it represents the application's root directory — the directory that contains the `api/` folder. `APP_INSTALL_FOLDER` is the framework's source tree installation path, which has no `api/` folder by design (the framework is a library, not an application).

The HCP Editor's build script already uses `APP_ROOT` implicitly (via `self::$rootFolder` which is set from the bootstrap directory). The runtime path must match.

## Considered Alternatives

| Decision | Chosen Shape | Alternatives Considered | Trade-Off Summary |
|----------|--------------|-------------------------|-------------------|
| Which constant to use | `APP_ROOT` | Add new constant `APP_API_FOLDER`; use `APP_URL` with path derivation | `APP_ROOT` exists in all environments, requires no new infrastructure, and directly maps to the filesystem path where `api/` lives |
| Where to fix | Both `GetOpenAPISpec` and `OpenAPIGenerator` default | Only fix `GetOpenAPISpec` | Fixing both prevents future callers from hitting the same issue if they rely on the default |

## Pattern Alignment

- `APP_ROOT` usage for application-relative paths: follows `tests/bootstrap.php` (line 19) and `hcp-editor/bootstrap.php` (line 16). Both define `APP_ROOT` for this purpose.
- Composer scripts for build tasks: follows existing pattern in both `composer.json` files (`rebuild-icons`, `clear-caches`, `seed-tests`).

## Detailed Steps

1. **Fix `GetOpenAPISpec::resolveSpecPath()`** — Change from `APP_INSTALL_FOLDER` to `APP_ROOT`.
2. **Fix `OpenAPIGenerator` constructor default** — Change the fallback output path from `APP_INSTALL_FOLDER` to `APP_ROOT`.
3. **Add Composer script `generate-openapi-spec`** — Wire `Application\Composer\ComposerScripts::generateOpenAPISpec` in the framework's `composer.json`.
4. **Add Composer script `generate-htaccess`** — Wire `Application\Composer\ComposerScripts::generateHtaccess` in the framework's `composer.json` for symmetry (same pattern).
5. **Run existing tests** — Verify `GetOpenAPISpecTest` and `BuildPipelineTest` still pass.

## Dependencies

- None. All changes are within the application-framework package.

## Required Components

- `src/classes/Application/API/OpenAPI/GetOpenAPISpec.php` — path resolution fix
- `src/classes/Application/API/OpenAPI/OpenAPIGenerator.php` — constructor default fix
- `composer.json` — new script entries

## Assumptions

- `APP_ROOT` is defined in every runtime and test environment where the OpenAPI spec is served. (Verified: framework tests define it at `tests/bootstrap.php:19`, HCP Editor defines it at `bootstrap.php:16`.)
- The `api/` directory always exists at `APP_ROOT` when `GetOpenAPISpec` is invoked. (Verified: both the HCP Editor and framework test app have `api/` at their root.)

## Constraints

- `array()` syntax must be used (not `[]`) — project rule.
- `declare(strict_types=1)` is required in all files — already present.

## Out of Scope

- Adding `generate-openapi-spec` to the HCP Editor's `composer.json` — the HCP Editor already has its own `ComposerScripts::build()` that calls `generateOpenAPISpec()` directly, and the framework script wouldn't work standalone anyway (it needs the HCP Editor's bootstrap).
- Changing the framework's `ComposerScripts::getFrameworkAPIOutputDirectory()` heuristic for DEV-mode symlink detection — that method is only used during build, and the HCP Editor overrides the entire build pipeline with its own `ComposerScripts`.

## Acceptance Criteria

- Accessing `/api/GetOpenAPISpec` in the HCP Editor returns the OpenAPI spec JSON (HTTP 200) instead of the error response.
- `GetOpenAPISpec::resolveSpecPath()` returns a path under `APP_ROOT` (not `APP_INSTALL_FOLDER`).
- `composer generate-openapi-spec` works from the framework's own project root.
- All existing OpenAPI tests continue to pass.

## Testing Strategy

The existing unit tests in `tests/AppFrameworkTests/API/OpenAPI/GetOpenAPISpecTest.php` and `BuildPipelineTest.php` verify the public contract of `GetOpenAPISpec`. The path resolution logic (`resolveSpecPath()`) is private, so it's tested implicitly through integration. Since `APP_ROOT` is defined in the framework's test bootstrap, the change is compatible.

## Test Plan

- `tests/AppFrameworkTests/API/OpenAPI/GetOpenAPISpecTest.php` — All existing tests pass unchanged (they don't exercise `resolveSpecPath()` directly as it requires file I/O). — Covers: method contract acceptance criteria.
- `tests/AppFrameworkTests/API/OpenAPI/BuildPipelineTest.php` — All existing tests pass unchanged. — Covers: build pipeline wiring acceptance criteria.
- `tests/AppFrameworkTests/API/OpenAPI/OpenAPIGeneratorTest.php` — All existing tests pass unchanged (they pass explicit output paths). — Covers: generator default fallback acceptance criteria.
- Manual verification: Call `/api/GetOpenAPISpec` from the HCP Editor to confirm HTTP 200. — Covers: primary acceptance criterion.

## Documentation Updates

- No manifest changes required — the fix is a bug correction, not a new feature or structural change.
- The error message in `GetOpenAPISpec` already mentions `composer generate-openapi-spec`; adding the Composer script makes the message accurate.

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **`APP_ROOT` not defined in some edge-case runtime** | The check uses `defined('APP_ROOT')` as a guard (same pattern as the existing `defined('APP_INSTALL_FOLDER')` check). Falls back to empty string which triggers the "not generated" error gracefully. |
| **Framework's own tests break** | Framework test bootstrap already defines `APP_ROOT` at the correct value (`tests/application`). The default path `tests/application/api/openapi.json` is where the framework build writes the file. Verified by directory listing. |
