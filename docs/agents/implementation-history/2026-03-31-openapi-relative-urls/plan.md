# Plan: Make OpenAPI externalDocs URLs Relative

## Summary

Replace the absolute `externalDocs` URLs in the generated OpenAPI 3.1 specification with relative paths so the committed `api/openapi.json` file is environment-agnostic. Currently, URLs like `http://127.0.0.1/projects/maileditor-git/api/documentation.php?method=CreateMail` are baked in at build time, making the file incorrect when built locally and committed to git. After this change, URLs will be relative paths like `documentation.php?method=CreateMail`, which are valid in OpenAPI 3.1 and resolve correctly regardless of the host.

## Architectural Context

The OpenAPI spec generation lives entirely in the framework:

- **Generator orchestrator:** `src/classes/Application/API/OpenAPI/OpenAPIGenerator.php` — assembles the full spec, delegates per-method conversion to `MethodConverter`.
- **Per-method converter:** `src/classes/Application/API/OpenAPI/MethodConverter.php` — builds each operation, including `externalDocs`. The URL is obtained via `(string)$method->getDocumentationURL()` (line ~127), which returns an `AdminURLInterface` that always renders as an absolute URL (scheme + host + path + query).
- **URL construction chain:** `BaseAPIMethod::getDocumentationURL()` → `APIUrls::methodDocumentation()` → `AdminURL::create()->string(...)->dispatcher('api/documentation.php')` → `URLBuilder::get()` which prepends `APP_URL`.
- **Spec serving endpoint:** `src/classes/Application/API/OpenAPI/GetOpenAPISpec.php` — reads `api/openapi.json` from disk and echoes it as-is.
- **Existing tests:** `tests/AppFrameworkTests/API/OpenAPI/OpenAPIGeneratorTest.php` (mocks `AdminURLInterface` with empty string `__toString()`).

The only URLs embedded in the spec are the per-method `externalDocs.url` values. The `servers` array is already empty (empty string passed to constructor).

## Approach / Architecture

**Change the `MethodConverter` to build relative `externalDocs` URLs itself**, rather than relying on the `AdminURL` string cast which always produces absolute URLs.

The `MethodConverter` already receives the `APIMethodInterface` and has access to the method name. The relative documentation URL follows a fixed pattern:

```
documentation.php?method={MethodName}
```

This is a purely mechanical construction — dispatcher constant (`api/documentation.php`) + method name query param. The `MethodConverter` will construct this relative URL directly using the `APIDocumentationBootstrap::DISPATCHER` constant and the method name, producing a path relative to the `/api/` directory where the spec itself lives.

**Why modify `MethodConverter` and not the URL construction chain:**
- `AdminURL` / `URLBuilder` is a shared utility that many parts of the app depend on for absolute URLs — changing its behaviour would be a breaking change.
- `getDocumentationURL()` on `APIMethodInterface` is a public contract that other consumers may rely on for absolute URLs.
- `MethodConverter` is the only consumer that needs relative URLs, and it already knows the context (OpenAPI spec generation).

## Rationale

- **Relative URLs are valid in OpenAPI 3.1** — the spec resolves them against the `servers` base URL or the URL from which the spec was fetched.
- **No runtime transformation needed** — `GetOpenAPISpec` continues to serve the file as-is, no string replacement on an 18 MB file.
- **The generated file becomes environment-agnostic** — correct in git, on dev, on staging, and in production.  
- **Minimal change footprint** — only `MethodConverter` is modified; no public API changes.
- **The fix lives in the framework** — where the spec generation code is owned and maintained.

## Detailed Steps

### Step 1: Modify `MethodConverter::buildOperation()` to emit relative `externalDocs` URLs

**File:** `src/classes/Application/API/OpenAPI/MethodConverter.php`

Replace the current `externalDocs` block (~lines 125–131):

```php
// Optional: external documentation.
$docUrl = (string)$method->getDocumentationURL();
if($docUrl !== '')
{
    $operation['externalDocs'] = array(
        'url' => $docUrl,
    );
}
```

With a new block that builds a relative URL:

```php
// Optional: external documentation (relative URL for environment portability).
$docUrl = $this->buildDocumentationUrl($method);
if($docUrl !== '')
{
    $operation['externalDocs'] = array(
        'url' => $docUrl,
    );
}
```

### Step 2: Add a private helper method `buildDocumentationUrl()` to `MethodConverter`

This method constructs the relative URL using the dispatcher constant and method name:

```php
/**
 * Builds a relative documentation URL for the method.
 *
 * Uses a path relative to the API directory so the generated
 * OpenAPI spec is independent of the application's base URL.
 *
 * @param APIMethodInterface $method
 * @return string Relative URL, e.g. `documentation.php?method=GetComtypes`. Empty if no documentation URL is available.
 */
private function buildDocumentationUrl(APIMethodInterface $method) : string
{
    $adminUrl = $method->getDocumentationURL();
    
    if((string)$adminUrl === '')
    {
        return '';
    }
    
    return sprintf(
        'documentation.php?%s=%s',
        APIMethodInterface::REQUEST_PARAM_METHOD,
        $method->getMethodName()
    );
}
```

**Imports to add:** `use Application\API\APIMethodInterface;` is already imported.

**Note:** We still call `(string)$adminUrl` as a guard to respect methods that return an empty documentation URL. The relative path `documentation.php?method=X` is relative to `/api/` — which is where the spec lives (at `/api/openapi.json`) and where it's served from (at `/api/GetOpenAPISpec`). OpenAPI 3.1 resolves relative URLs in `externalDocs` against the document's location, so these will resolve correctly.

### Step 3: Add a unit test for relative `externalDocs` URL generation

**File:** `tests/AppFrameworkTests/API/OpenAPI/OpenAPIGeneratorTest.php`

Add a test that verifies the `externalDocs` URL is relative when a documentation URL is available. This requires adjusting the mock setup to return a non-empty `AdminURLInterface` so `MethodConverter` doesn't skip it.

The test should:
1. Create a method mock where `getDocumentationURL()` returns a non-empty `AdminURLInterface`.
2. Generate the spec via `OpenAPIGenerator::toArray()`.
3. Assert the `externalDocs.url` in the resulting path item is a relative URL matching `documentation.php?method={MethodName}`.

### Step 4: Rebuild the maileditor's `openapi.json`

After the framework change, run `composer build-dev` in the maileditor project to regenerate `api/openapi.json` with relative URLs.

Verify by inspecting the output: all `externalDocs` URLs should look like `documentation.php?method=CreateMail` instead of `http://127.0.0.1/.../api/documentation.php?method=CreateMail`.

## Dependencies

- Framework change (Steps 1–3) must be completed before the maileditor rebuild (Step 4).

## Required Components

- `src/classes/Application/API/OpenAPI/MethodConverter.php` (modified)
- `tests/AppFrameworkTests/API/OpenAPI/OpenAPIGeneratorTest.php` (modified)
- Maileditor: `api/openapi.json` (regenerated build artifact)

## Assumptions

- Relative `externalDocs` URLs resolve correctly in the tools that consume the spec (Swagger UI, Redoc, etc.). This is per the OpenAPI 3.1 specification which allows relative URLs in `externalDocs`.
- The `REQUEST_PARAM_METHOD` constant value is `method` and will remain stable.
- All API methods that have a documentation URL follow the same `documentation.php?method={Name}` pattern, which is enforced by `APIUrls::methodDocumentation()`.

## Constraints

- Use `array()` syntax, not `[]` — hard project rule.
- Use `declare(strict_types=1);` in all modified files.
- Run `composer dump-autoload` if new files are added (not needed here — only modifications).
- Do not modify `AdminURL`, `URLBuilder`, or `APIMethodInterface` — they are shared contracts.

## Out of Scope

- Adding a `servers` entry to the OpenAPI spec (currently empty by design).
- Modifying `GetOpenAPISpec` serving behavior (no runtime URL replacement needed).
- Changes to the `AdminURL` / `URLBuilder` public API.
- Adding a `.gitignore` entry for `openapi.json` (it must remain tracked since `composer build` is not run on production).

## Acceptance Criteria

- `MethodConverter` emits relative `externalDocs` URLs (e.g. `documentation.php?method=GetComtypes`) instead of absolute URLs.
- Methods that return an empty documentation URL produce no `externalDocs` entry (existing behavior preserved).
- All existing OpenAPI tests pass.
- A new test verifies that `externalDocs` URLs are relative.
- Regenerated `api/openapi.json` in the maileditor contains only relative documentation URLs.

## Testing Strategy

1. **Unit tests (framework):** Run `composer test-file -- tests/AppFrameworkTests/API/OpenAPI/OpenAPIGeneratorTest.php` to verify existing tests still pass and the new relative URL test passes.
2. **Visual verification:** After `composer build-dev` in the maileditor, inspect `api/openapi.json` to confirm all `externalDocs` URLs are relative.
3. **Functional verification:** Access the API documentation UI (`/api/documentation.php`) and Swagger UI to confirm the relative links resolve correctly.

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **Some OpenAPI UI tools may not resolve relative `externalDocs` URLs** | OpenAPI 3.1 explicitly supports relative URLs in all URL fields. Swagger UI and Redoc both handle them correctly. Verify with the project's documentation UI. |
| **Methods with custom/overridden `getDocumentationURL()` may diverge from the pattern** | The guard check `(string)$adminUrl === ''` preserves the opt-out behavior. The relative URL construction uses the method name directly, matching the standard pattern. |
| **`REQUEST_PARAM_METHOD` value could change** | Uses the constant reference rather than hardcoding `'method'`, so any rename is automatically picked up. |
