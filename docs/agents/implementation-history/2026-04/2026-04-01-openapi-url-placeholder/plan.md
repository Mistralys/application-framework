# Plan: Replace Application Base URL with Placeholder in OpenAPI Spec

## Summary

When the OpenAPI specification is generated via `composer build`, example response data and schema descriptions contain the local installation's base URL (e.g., `http://127.0.0.1/projects/maileditor-DEV-Fenrir`). This creates noisy VCS diffs and embeds environment-specific data into a committed artifact. The solution is to replace all occurrences of the `APP_URL` constant's value with a `{APPLICATION_URL}` placeholder in the final JSON output, keeping the examples semantically clear without leaking local configuration.

## Architectural Context

### OpenAPI Generation Pipeline

The generation flow is:

1. **Entry point:** `APIManager::generateOpenAPISpec()` — [src/classes/Application/API/APIManager.php](src/classes/Application/API/APIManager.php)
2. **Orchestrator:** `OpenAPIGenerator` — [src/classes/Application/API/OpenAPI/OpenAPIGenerator.php](src/classes/Application/API/OpenAPI/OpenAPIGenerator.php)
3. **Response embedding:** `ResponseConverter::buildSuccessResponse()` — [src/classes/Application/API/OpenAPI/ResponseConverter.php](src/classes/Application/API/OpenAPI/ResponseConverter.php)
4. **Example data:** `JSONResponseWithExampleTrait::getExampleJSONResponse()` — [src/classes/Application/API/Traits/JSONResponseWithExampleTrait.php](src/classes/Application/API/Traits/JSONResponseWithExampleTrait.php)
5. **Schema descriptions:** `SchemaInferrer` merges key descriptions from `getReponseKeyDescriptions()` — [src/classes/Application/API/OpenAPI/SchemaInferrer.php](src/classes/Application/API/OpenAPI/SchemaInferrer.php)

### Where URLs Appear

URLs referencing the application base appear in two places in the generated `openapi.json`:

1. **Example data** — `deepLinks` objects in example responses (e.g., `GetGlobalContent`, `GetComtype`, etc.) contain absolute admin URLs built via `AdminURLInterface`.
2. **Schema descriptions** — `getReponseKeyDescriptions()` implementations may embed absolute references to the API documentation page (e.g., `See the [GetGlobalContentStates API](http://127.0.0.1/.../documentation.php?method=...)`).

Both originate from the same base URL: the `APP_URL` constant (e.g., `http://127.0.0.1/projects/maileditor-DEV-Fenrir`).

### Current JSON Serialization

`OpenAPIGenerator::generate()` serializes the spec array to JSON with `JSON_UNESCAPED_SLASHES`, meaning the URL appears literally as the `APP_URL` constant value — no JSON escaping of slashes to complicate the replacement.

### `APP_URL` Availability During Build

During `composer build`, the Maileditor's `ComposerScripts::initAutoloader()` calls `Application_Bootstrap::bootClass()`, which loads `config/_framework-manager-config.php` where `$APP_URL` is defined. The constant is therefore available during the generation step.

## Approach / Architecture

Add a generic output string replacement mechanism to `OpenAPIGenerator`, then wire `APIManager` to register the `APP_URL → {APPLICATION_URL}` replacement when the constant is defined.

The replacement operates on the serialized JSON string in `generate()`, **after** `json_encode` and **before** writing to disk. This is the simplest approach because:

- It catches **all** URL occurrences regardless of source (examples, descriptions, any future field).
- It requires no changes to individual API methods or their example/description generation logic.
- The replacement is a trivial `str_replace` on the final string.

## Rationale

- **Post-serialization string replacement** was chosen over modifying individual API methods or the `ResponseConverter` because URLs can appear anywhere in the spec (examples, descriptions, extension fields). A single-pass replacement on the final output is exhaustive and requires minimal code.
- **Generic replacement API** (`addOutputReplacement`) is preferred over a dedicated `setBaseUrlPlaceholder()` method to keep the generator reusable for other normalizations in the future without added complexity.
- **Framework-level implementation** is correct because `OpenAPIGenerator` and `APIManager` own the generation pipeline. Application-level post-processing (e.g., in Maileditor's `ComposerScripts`) would work but is less clean — it would mean reading, modifying, and rewriting the file after it's already been written.
- **No changes to the Maileditor codebase** are required; the framework handles this generically for all applications.

## Detailed Steps

### Step 1: Add output replacement support to `OpenAPIGenerator`

**File:** `src/classes/Application/API/OpenAPI/OpenAPIGenerator.php`

1. Add a private property:
   ```php
   /**
    * @var array<string, string> Search → replace pairs applied to the serialized JSON before writing.
    */
   private array $outputReplacements = array();
   ```

2. Add a fluent setter in the "Configuration (fluent setters)" section:
   ```php
   /**
    * Registers a string replacement to apply to the serialized JSON output
    * before writing to disk. Useful for normalizing environment-specific
    * values (e.g., replacing the application base URL with a placeholder).
    *
    * @param string $search  The literal string to find.
    * @param string $replace The replacement string.
    * @return $this
    */
   public function addOutputReplacement(string $search, string $replace) : self
   {
       $this->outputReplacements[$search] = $replace;
       return $this;
   }
   ```

3. In `generate()`, apply replacements between JSON encoding and file writing:
   ```php
   public function generate() : string
   {
       $json = JSONConverter::var2json(
           $this->toArray(),
           JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
       );

       $json = $this->applyOutputReplacements($json);

       FileInfo::factory($this->outputPath)->putContents($json);

       return $this->outputPath;
   }
   ```

4. Add the private helper method:
   ```php
   /**
    * Applies all registered string replacements to the serialized JSON output.
    *
    * @param string $json
    * @return string
    */
   private function applyOutputReplacements(string $json) : string
   {
       foreach($this->outputReplacements as $search => $replace)
       {
           $json = str_replace($search, $replace, $json);
       }

       return $json;
   }
   ```

### Step 2: Wire the `APP_URL` replacement in `APIManager`

**File:** `src/classes/Application/API/APIManager.php`

In `generateOpenAPISpec()`, after constructing the generator, conditionally register the URL replacement:

```php
public function generateOpenAPISpec(string $outputPath = '') : string
{
    $generator = new OpenAPIGenerator(
        $this->getMethodCollection(),
        $this->driver->getAppName(),
        $this->driver->getVersion(),
        '',
        '',
        $outputPath
    );

    if(defined('APP_URL'))
    {
        $generator->addOutputReplacement(APP_URL, '{APPLICATION_URL}');
    }

    return $generator->generate();
}
```

### Step 3: Regenerate `openapi.json`

Run `composer build` (or `composer build-dev`) in the Maileditor project to regenerate `api/openapi.json` with the placeholder applied.

### Step 4: Verify the output

Inspect the regenerated `api/openapi.json` to confirm:
- All `deepLinks` URLs use `{APPLICATION_URL}` instead of the local base URL.
- Description URLs (e.g., links to `documentation.php`) also use the placeholder.
- No other content is inadvertently modified.
- The JSON remains valid.

## Dependencies

- Step 2 depends on Step 1 (the method must exist before it can be called).
- Step 3 depends on Steps 1 and 2 (both framework changes must be in place).

## Required Components

### Modified Files (Framework)

- `src/classes/Application/API/OpenAPI/OpenAPIGenerator.php` — Add `$outputReplacements` property, `addOutputReplacement()` setter, `applyOutputReplacements()` helper, and modify `generate()`.
- `src/classes/Application/API/APIManager.php` — Add `APP_URL` replacement registration in `generateOpenAPISpec()`.

### Regenerated Files (Maileditor)

- `api/openapi.json` — Regenerated by `composer build-dev` with placeholders applied.

## Assumptions

- `APP_URL` is defined during `composer build` / `composer build-dev` runs (confirmed: `ComposerScripts::initAutoloader()` boots the application with config loaded).
- `APP_URL` does not contain a trailing slash (confirmed: `http://127.0.0.1/projects/maileditor-DEV-Fenrir`).
- The `JSON_UNESCAPED_SLASHES` flag ensures URLs in JSON match the `APP_URL` constant literally (no escaped forward slashes).

## Constraints

- Must use `array()` syntax (project convention).
- The replacement must be conditional on `defined('APP_URL')` to avoid errors in environments where the constant is not set.

## Out of Scope

- Replacing URLs generated by the live `GetOpenAPISpec` API method (which serves the spec over HTTP with the current server's URL — this is correct behavior for live use).
- Changing how `AdminURLInterface` or `deepLinks` generate URLs in API method responses.
- Modifying the `servers` section of the OpenAPI spec (currently empty; populating it is a separate concern).

## Acceptance Criteria

- After `composer build-dev`, `api/openapi.json` contains no occurrences of the local `APP_URL` value.
- All former `APP_URL` occurrences are replaced with `{APPLICATION_URL}`.
- The generated JSON is syntactically valid.
- No other values in the JSON are affected by the replacement.
- When `APP_URL` is not defined, generation proceeds without error and without replacements.

## Testing Strategy

- **Manual verification:** Run `composer build-dev` in the Maileditor and `grep` the output file for the old URL and the new placeholder.
- **Unit test (optional):** The `OpenAPIGenerator::toArray()` + `generate()` flow could be tested by constructing a generator with a mocked collection, adding an output replacement, and verifying the written file content. However, given the simplicity of the change (a `str_replace` wrapper), manual verification is sufficient.

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **`APP_URL` value appears in non-URL contexts** (e.g., a description mentioning the URL as text) | This is actually desirable — any reference to the local URL should be replaced. The placeholder is self-documenting. |
| **`APP_URL` not defined during build** | The `defined('APP_URL')` guard ensures no error; generation proceeds unchanged. |
| **Future URL formats that don't start with `APP_URL`** | The replacement is purely additive; unrecognized URLs pass through unchanged. This can be addressed later if needed. |
| **Replacement corrupts JSON syntax** | `APP_URL` values only appear inside JSON string values. Replacing one string literal with another preserves JSON validity. The `{` and `}` characters in `{APPLICATION_URL}` do not conflict with JSON syntax when inside a quoted string. |
