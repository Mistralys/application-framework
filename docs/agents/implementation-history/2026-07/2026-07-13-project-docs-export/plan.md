# Plan

## Plan Audit Cycles
- Audits: 3 — Plan Auditor v1.5.0
- Architectural Reviews: 2 — Plan Architect Reviewer v2.0.0

## Prior Project Context
The `exportDocs` feature was added to `ModuleJsonExportGenerator` in the HCP Editor's `2026-07-03-export-docs-module-json` project (with a rework in `2026-07-03-export-docs-module-json-rework-1`). That project established the pattern: module authors declare `.md` file paths in `moduleMetaData.exportDocs`, the framework generator reads them at build time with a containment guard, and includes their content in the JSON output under `additionalDocs` per module. The feature spans the framework (`ModuleInfo`, `ModuleInfoParser`, `ModuleJsonExportGenerator`) and the HCP Editor (its subclass and CLI script).

This plan extends the same generator to support **project-level docs** — markdown documents that describe cross-cutting platform concerns with no natural module owner (e.g., system architecture maps, platform history). This is part of a larger migration initiative in `nexus-personas` (`2026-07-13-reference-doc-migration-to-hcp-editor`) that moves hand-authored reference documentation into the HCP Editor to be automatically distributed via the JSON export pipeline.

## Summary

Add support for a `projectMetaData.exportDocs` section in the root `context.yaml`, mirroring the existing `moduleMetaData.exportDocs` pattern used in `module-context.yaml` files. The generator reads this configuration at the start of `generate()`, resolves the declared files using the same containment-guard and `.md`-validation logic as module-level `resolveAdditionalDocs()`, and emits a new `projectDocs` top-level key in the JSON output. The key is always present (empty array when no project docs are declared), following the established always-present-array convention used by `additionalDocs`, `glossary`, and `glossarySections`.

The HCP Editor's `context.yaml` would declare:

```yaml
projectMetaData:
  exportDocs:
    - docs/platform/module-map.md
    - docs/platform/system-map.md
    - docs/platform/hcp-history.md
```

## Architectural Context

### ModuleJsonExportGenerator

The generator at `src/classes/Application/Composer/ModulesOverview/ModuleJsonExportGenerator.php` is the framework's generic, subclassable JSON export orchestrator. It:

1. Discovers `module-context.yaml` files via `ModuleContextFileFinder`.
2. Parses them via `ModuleInfoParser` into `ModuleInfo` value objects.
3. Resolves briefs (`resolveModuleBrief()`), sources (`resolveModuleSource()`), and additional docs (`resolveAdditionalDocs()`).
4. Builds the keyword glossary via `KeywordGlossaryBuilder`.
5. Collects glossary sections via the offline `DecorateGlossaryEvent`.
6. Writes the JSON output with keys: `generatedAt`, `modules`, `glossary`, `glossarySections`.

The HCP Editor subclass at `Maileditor\Composer\ModuleJsonExport\ModuleJsonExportGenerator` overrides only `resolveModuleSource()`. The CLI script `tools/generate-module-json.php` instantiates the HCP Editor subclass and calls `generate()`.

### Relevant Classes

| Class | Path | Role |
|---|---|---|
| `ModuleJsonExportGenerator` | `src/classes/Application/Composer/ModulesOverview/ModuleJsonExportGenerator.php` | Base generator — orchestrates the full export workflow |
| `ModuleInfo` | `src/classes/Application/Composer/ModulesOverview/ModuleInfo.php` | Immutable value object for parsed module metadata |
| `ModuleInfoParser` | `src/classes/Application/Composer/ModulesOverview/ModuleInfoParser.php` | Parses `module-context.yaml` files into `ModuleInfo` |
| `ModuleContextFileFinder` | `src/classes/Application/Composer/ModulesOverview/ModuleContextFileFinder.php` | Discovers `module-context.yaml` files by following `context.yaml` imports |
| `ModuleJsonExportGeneratorTest` | `tests/AppFrameworkTests/Composer/ModulesOverview/ModuleJsonExportGeneratorTest.php` | Unit tests using temp fixture directories |

### Current JSON Output Structure

```json
{
  "generatedAt": "2026-07-13T16:48:04+02:00",
  "modules": [
    {
      "id": "pigeon",
      "label": "Pigeon OMS Adapter",
      "summary": "...",
      "source": "hcp-editor",
      "description": "...",
      "relatedModules": ["..."],
      "brief": "...",
      "additionalDocs": [
        { "fileName": "pigeon-service-reference.md", "content": "..." }
      ]
    }
  ],
  "glossary": [...],
  "glossarySections": [...]
}
```

## Approach / Architecture

Add project-level document support to `ModuleJsonExportGenerator` by reading a `projectMetaData.exportDocs` section from the root `context.yaml` — the same file that `ModuleContextFileFinder` already parses for module imports.

**Design choice — `projectMetaData` in `context.yaml`, not a setter method:**

Using `projectMetaData.exportDocs` in `context.yaml` mirrors the `moduleMetaData.exportDocs` pattern that module authors already know. Configuration stays declarative and co-located with the existing module discovery config. No API changes are needed on the generator — the `generate()` method reads the YAML itself.

The generator already depends on `context.yaml` existing (via `ModuleContextFileFinder`). Reading one additional top-level key from the same file is a natural extension, not a new coupling.

**Containment guard:** Project docs use the project root as their containment boundary (not a module source directory). The `resolveAdditionalDocs()` guard logic is extracted into a reusable private method that both module docs and project docs call, each with their own base path.

## Rationale

- **Declarative consistency.** `projectMetaData.exportDocs` mirrors `moduleMetaData.exportDocs` — same key name, same semantics, same file format. Module authors and project maintainers use the same mental model.
- **Minimal change.** One new private method to parse the YAML key, one to resolve the files, and a single new key in the JSON output. No changes to `ModuleInfo`, `ModuleInfoParser`, or `ModuleContextFileFinder`. No public API changes.
- **Non-breaking.** When `projectMetaData` is absent from `context.yaml`, the `projectDocs` key defaults to an empty array. Existing consumers ignore unknown keys.
- **Security parity.** Project docs reuse the same containment guard as module docs, preventing path traversal outside the project root.
- **No new coupling.** The generator already depends on `context.yaml` existing in the root folder (via `ModuleContextFileFinder`). Reading one more key from the same file introduces no new file dependencies.

## Considered Alternatives

| Decision | Chosen Shape | Alternatives Considered | Trade-Off Summary |
|----------|--------------|-------------------------|-------------------|
| Configuration mechanism | `projectMetaData.exportDocs` in `context.yaml` | (a) `setProjectDocs()` setter method on generator; (b) Constructor parameter; (c) Separate config file | `context.yaml` is already the project's configuration hub for module discovery. Using the same file with a parallel `projectMetaData` section maintains declarative consistency with `moduleMetaData`. A setter would work but introduces an imperative API when everything else is YAML-driven. Constructor param breaks existing subclasses. A separate config file would fragment configuration. |
| YAML key naming | `projectMetaData.exportDocs` | (a) `projectDocs` at root level; (b) `project.exportDocs` | `projectMetaData` mirrors `moduleMetaData` — same naming convention, same nesting depth. A flat `projectDocs` key would be inconsistent with how modules declare theirs. `project` is already used by CTX Generator for a different purpose (`project.path`, `project.alias`). |
| Containment guard reuse | Extract `protected resolveDocFile()` from `resolveAdditionalDocs()` | (a) Duplicate the guard logic; (b) Make `resolveAdditionalDocs()` accept a generic base path | Extraction avoids duplication. `protected` visibility ensures subclasses overriding `resolveAdditionalDocs()` can call the shared containment guard without re-implementing it. Making `resolveAdditionalDocs()` generic would change a `protected` method signature, potentially breaking subclass overrides. |
| JSON key name | `projectDocs` | (a) `globalDocs`; (b) `platformDocs`; (c) `additionalProjectDocs` | `projectDocs` is the most accurate — these are project-scoped, not module-scoped. Short, clear, and parallel to `additionalDocs`. |

## Pattern Alignment

- **`exportDocs` key naming**: Follows the identical key name used in `moduleMetaData.exportDocs` within `module-context.yaml`. No departure.
- **`projectMetaData` section in `context.yaml`**: **New addition.** The root `context.yaml` currently only has `mcp`, `project`, `import`, and `documents` sections. Adding `projectMetaData` is a new section. Justified by the parallel with `moduleMetaData` in `module-context.yaml` — it's the project-level equivalent of the per-module configuration.
- **Always-present array in JSON output**: Follows the pattern established by `additionalDocs` (per module) and `glossarySections` (top-level). `projectDocs` is always present, defaulting to an empty array. No departure.
- **Containment guard with trailing-slash semantics**: Follows the exact pattern documented in `resolveAdditionalDocs()` PHPDoc. No departure.
- **`.md`-only validation**: Follows the pattern from `ModuleInfoParser.extractExportDocs()`. The generator applies the same filter when parsing `projectMetaData.exportDocs`. No departure.
- **`array()` syntax**: All new code uses `array()` per project constraints. No departure.

## Detailed Steps

### Step 1: Extract `resolveDocFile()` from `resolveAdditionalDocs()`

Extract the per-file resolution logic (path joining, `realpath()`, containment guard, `file_get_contents()`, progress warnings) into a new `protected` method:

```php
/**
 * Resolves a single documentation file path against a base directory.
 *
 * Applies the containment guard, reads the file content, and returns
 * a {fileName, content} array on success, or null on failure (with a
 * progress warning).
 *
 * Declared `protected` so that subclasses overriding
 * {@see resolveAdditionalDocs()} can call this shared helper and
 * benefit from the containment guard without re-implementing it.
 *
 * @param string $relPath     Relative path to the doc file.
 * @param string $basePath    Absolute path to the containing directory.
 * @param string $sourceLabel Label for progress messages (e.g., module ID or "project").
 * @return array{fileName: string, content: string}|null
 */
protected function resolveDocFile(string $relPath, string $basePath, string $sourceLabel) : ?array
```

Refactor `resolveAdditionalDocs()` to call `resolveDocFile()` in its loop, passing `$sourcePath` as `$basePath` and `$module->getId()` as `$sourceLabel`.

### Step 2: Add `parseProjectExportDocs()` private method

Add a private method that reads the root `context.yaml` and extracts the `projectMetaData.exportDocs` list:

```php
/**
 * Parses the root `context.yaml` for a `projectMetaData.exportDocs`
 * section and returns the list of valid `.md` file paths.
 *
 * Returns an empty array when `context.yaml` does not exist, has no
 * `projectMetaData` section, or declares no `exportDocs`. Non-`.md`
 * entries are filtered with a progress warning, matching the behavior
 * of {@see ModuleInfoParser::extractExportDocs()}.
 *
 * @return list<string>
 */
private function parseProjectExportDocs() : array
```

The method:
1. Reads `{rootFolder}/context.yaml` using `Yaml::parseFile()`.
2. Checks for `$data['projectMetaData']['exportDocs']` (must be an array).
3. Filters each entry: only `.md` extensions pass; non-`.md` entries emit a progress warning.
4. Returns the filtered list of relative paths.

### Step 3: Add `resolveProjectDocs()` private method

```php
/**
 * Resolves the project-level documentation files declared in
 * `projectMetaData.exportDocs` in the root `context.yaml`.
 *
 * Uses the project root as the containment boundary. Each file is
 * resolved via {@see resolveDocFile()}.
 *
 * @return array<int, array{fileName: string, content: string}>
 */
private function resolveProjectDocs() : array
```

Calls `parseProjectExportDocs()` to get the path list, then iterates each path, calling `resolveDocFile()` with the root folder path as `$basePath` and `"project"` as `$sourceLabel`. Collects results into an array.

### Step 4: Extend `generate()` to emit `projectDocs`

In the `generate()` method, after building the glossary sections and before writing JSON:

1. Call `$this->resolveProjectDocs()`.
2. If any project docs were loaded or skipped, emit a progress message.
3. Add `'projectDocs' => $projectDocs` to the `$output` array.

The output array becomes:

```php
$output = array(
    'generatedAt'      => ...,
    'modules'          => $moduleData,
    'glossary'         => $glossary,
    'glossarySections' => $glossarySections,
    'projectDocs'      => $projectDocs,
);
```

### Step 5: Write tests

Extend the `writeRootContextYaml()` fixture helper to accept an optional `$projectExportDocs` array. When provided, the helper appends a `projectMetaData.exportDocs` section to the generated `context.yaml`.

Add the following test methods to `ModuleJsonExportGeneratorTest`:

1. **`test_generate_projectDocs_keyAlwaysPresent()`** — No `projectMetaData` in `context.yaml`; verify `projectDocs` is `[]` in output. Uses the existing `buildFixtureRoot()` which writes no `projectMetaData`.

2. **`test_generate_projectDocs_includesConfiguredDocs()`** — Write `context.yaml` with `projectMetaData.exportDocs: [docs/platform/test-doc.md]`. Create the file in the temp root. Run the generator and verify `projectDocs[0]` has correct `fileName` and `content`.

3. **`test_generate_projectDocs_missingFile_skippedGracefully()`** — Declare a project doc path in `context.yaml` pointing to a non-existent file. Verify `projectDocs` is `[]`.

4. **`test_generate_projectDocs_pathTraversal_blocked()`** — Declare `../../outside.md` in `projectMetaData.exportDocs`. Create the file outside the temp root. Verify the containment guard blocks it (`projectDocs` is `[]`).

5. **`test_generate_projectDocs_nonMarkdown_filtered()`** — Declare `docs/guide.txt` in `projectMetaData.exportDocs`. Verify `projectDocs` is `[]` (filtered during YAML parsing).

6. **Existing `test_generate_exportDocs_includesAdditionalDocsInOutput()`** — Must still pass after the `resolveDocFile()` extraction. No new test needed — just confirm the existing test is green.

### Step 6: Update PHPDoc on `generate()` and class

Add documentation for the `projectDocs` key in the class-level PHPDoc, and document the `projectMetaData.exportDocs` configuration in the `generate()` method's PHPDoc.

## Dependencies

- `symfony/yaml` — already a project dependency, used by `ModuleContextFileFinder` and `ModuleInfoParser`.
- The `Maileditor\Composer\ModuleJsonExport\ModuleJsonExportGenerator` subclass in the HCP Editor does not override `generate()` or `resolveAdditionalDocs()`, so this change is non-breaking for the HCP Editor.

## Required Components

- `src/classes/Application/Composer/ModulesOverview/ModuleJsonExportGenerator.php` — Add `resolveDocFile()` protected method, `parseProjectExportDocs()` private method, `resolveProjectDocs()` private method; refactor `resolveAdditionalDocs()` to use `resolveDocFile()`; extend `generate()` output array
- `tests/AppFrameworkTests/Composer/ModulesOverview/ModuleJsonExportGeneratorTest.php` — Extend `writeRootContextYaml()` fixture helper; add 5 new test methods for `projectDocs`

## Assumptions

- The root `context.yaml` already exists in the project root (it is required by `ModuleContextFileFinder` for module discovery). The generator reads it directly.
- The HCP Editor's `context.yaml` will be updated separately (in the HCP Editor plan) to add a `projectMetaData.exportDocs` section listing the cross-cutting platform docs.
- The `nexus-personas` `build-references.js` will be updated separately (in the nexus-personas plan) to consume the new `projectDocs` key from the JSON output.
- The `projectMetaData` section is a new YAML key that does not conflict with the CTX Generator's existing `project`, `mcp`, `import`, and `documents` keys. The CTX Generator ignores unknown top-level keys.

## Constraints

- Only `.md` files are accepted (consistent with `moduleMetaData.exportDocs`).
- The containment guard uses the project root as the boundary — project doc files must reside within the project directory tree.
- The `resolveAdditionalDocs()` method is `protected` and may be overridden by subclasses. The refactoring must not change its signature or observable behavior.
- All new code must use `array()` syntax (project rule).
- All new code must use `declare(strict_types=1)` (project rule).

## Out of Scope

- Changes to `ModuleInfo`, `ModuleInfoParser`, or `ModuleContextFileFinder` — project docs are read directly from `context.yaml` by the generator, not through the module discovery pipeline.
- Changes to the HCP Editor's subclass, CLI script, or `context.yaml` — handled in the HCP Editor plan.
- Changes to `build-references.js` in nexus-personas — handled in the nexus-personas plan.
- The CTX Generator — `projectMetaData` is consumed only by the module JSON export generator; the CTX Generator ignores unknown keys.

## Acceptance Criteria

- AC-01: `ModuleJsonExportGenerator` reads `projectMetaData.exportDocs` from the root `context.yaml` during `generate()`.
- AC-02: Non-`.md` paths in `projectMetaData.exportDocs` are silently filtered with a progress warning, matching `moduleMetaData.exportDocs` behavior.
- AC-03: The JSON output from `generate()` always contains a `projectDocs` top-level key (empty array when `projectMetaData` is absent or declares no `exportDocs`).
- AC-04: When project docs are declared and the files exist, `projectDocs` contains `{fileName, content}` entries with correct data.
- AC-05: The containment guard prevents project doc paths from resolving outside the project root directory.
- AC-06: Missing or unreadable project doc files are skipped gracefully with a progress warning.
- AC-07: The `resolveAdditionalDocs()` refactoring does not change its observable behavior — all existing `exportDocs` tests pass without modification.
- AC-08: Five new test methods cover the `projectDocs` feature (always-present key, happy path, missing file, path traversal, non-markdown filtering).

## Testing Strategy

All tests use the existing temp-directory fixture pattern established in `ModuleJsonExportGeneratorTest`. Each test creates a temporary project root, configures the generator, runs `generate()`, and asserts against the decoded JSON output. No database, no HTTP, no external dependencies.

## Test Plan

- `test_generate_projectDocs_keyAlwaysPresent()` — No `projectMetaData` in `context.yaml`; verify `projectDocs` is `[]` in output. — AC-03
- `test_generate_projectDocs_includesConfiguredDocs()` — Write `context.yaml` with `projectMetaData.exportDocs: [docs/platform/test-doc.md]`; create the file; verify `projectDocs[0]` has correct `fileName` and `content`. — AC-01, AC-04
- `test_generate_projectDocs_missingFile_skippedGracefully()` — Declare non-existent path in `projectMetaData.exportDocs`; verify `projectDocs` is `[]`. — AC-06
- `test_generate_projectDocs_pathTraversal_blocked()` — Declare `../../outside.md` in `projectMetaData.exportDocs`; create file outside temp root; verify `projectDocs` is `[]`. — AC-05
- `test_generate_projectDocs_nonMarkdown_filtered()` — Declare `docs/guide.txt` in `projectMetaData.exportDocs`; verify `projectDocs` is `[]`. — AC-02
- Existing `test_generate_exportDocs_includesAdditionalDocsInOutput()` — Must still pass after refactoring. — AC-07

## Documentation Updates

- `ModuleJsonExportGenerator.php` class-level PHPDoc — Document `projectDocs` capability and the `projectMetaData.exportDocs` configuration
- `ModuleJsonExportGenerator.php` `generate()` PHPDoc — Document the `projectDocs` key in output and reference the YAML configuration
- `src/classes/Application/Composer/README.md` — Document the new `projectMetaData.exportDocs` configuration key and the `projectDocs` output key; run `composer build` afterwards to regenerate `.context/modules/composer/overview.md`

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **`resolveAdditionalDocs()` refactoring breaks subclass overrides** | The refactoring extracts a `protected` helper (`resolveDocFile()`) and does not change `resolveAdditionalDocs()`'s signature, return type, or observable behavior. Using `protected` preserves the extension contract: subclasses overriding `resolveAdditionalDocs()` can call `resolveDocFile()` directly rather than re-implementing the security-critical containment guard. All existing tests must pass. |
| **CTX Generator rejects unknown `projectMetaData` key** | Verified: CTX Generator's JSON schema for `context.yaml` uses `additionalProperties` and ignores unknown top-level keys. The `projectMetaData` section is invisible to CTX. |
| **`context.yaml` does not exist** | The `parseProjectExportDocs()` method returns an empty array when `context.yaml` is missing, matching `ModuleContextFileFinder`'s behavior. This produces an empty `projectDocs` array — correct and graceful. |
| **Performance concern with many project docs** | The expected use case is 3–5 small markdown files. File reading is sequential and fast. No concern at this scale. |
