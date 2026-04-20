# Plan

## Summary

Audit the recently added `ModuleInfoParser` in the Application Framework and consolidate module-parsing responsibilities that currently live in the HCP Editor application but should be framework-level concerns. Five findings were identified: (1) the framework's own `KeywordGlossaryGenerator` does not use the new shared parser, undermining the extraction's purpose; (2) glossary-building logic is duplicated between the framework glossary generator and the application's JSON export generator; (3) the generic `ReadmeOverviewParser` utility lives in the HCP Editor instead of the framework; (4) dead code exists in the application's JSON export generator; (5) the JSON export generator's core data-collection workflow is application-agnostic and could be reusable at the framework level.

## Architectural Context

### Framework: `Application\Composer\ModulesOverview\`

| File | Role |
|---|---|
| `src/classes/Application/Composer/ModulesOverview/ModuleContextFileFinder.php` | Discovers all `module-context.yaml` files by following `context.yaml` import chains |
| `src/classes/Application/Composer/ModulesOverview/ModuleInfoParser.php` | **New.** Parses individual YAML files into `ModuleInfo` VOs. Extracted to be the "single, authoritative implementation" |
| `src/classes/Application/Composer/ModulesOverview/ModuleInfo.php` | Immutable value object (id, label, description, relatedModules, sourcePath, contextOutputFolder, composerPackage, keywords) |
| `src/classes/Application/Composer/ModulesOverview/ModulesOverviewGenerator.php` | Orchestrates Markdown overview generation. **Uses `ModuleInfoParser` ✓** |
| `src/classes/Application/Composer/ModulesOverview/ModulesOverviewRenderer.php` | Renders Markdown table output |

### Framework: `Application\Composer\KeywordGlossary\`

| File | Role |
|---|---|
| `src/classes/Application/Composer/KeywordGlossary/KeywordGlossaryGenerator.php` | Orchestrates Markdown glossary generation. **Does NOT use `ModuleInfoParser` ✗** — still does its own `Yaml::parseFile()` + manual `moduleMetaData` extraction |
| `src/classes/Application/Composer/KeywordGlossary/KeywordParser.php` | Parses `"TERM (context)"` keyword strings |
| `src/classes/Application/Composer/KeywordGlossary/KeywordEntry.php` | Immutable VO for single keyword entry |
| `src/classes/Application/Composer/KeywordGlossary/KeywordGlossaryRenderer.php` | Renders Markdown glossary document |
| `src/classes/Application/Composer/KeywordGlossary/GlossarySection.php` | VO for custom glossary sections |
| `src/classes/Application/Composer/KeywordGlossary/GlossarySectionEntry.php` | VO for custom section entries |

### HCP Editor: `Maileditor\Composer\`

| File | Role |
|---|---|
| `assets/classes/Maileditor/Composer/ModuleJsonExport/ModuleJsonExportGenerator.php` | Generates JSON module metadata export. Uses `ModuleInfoParser` ✓. Duplicates glossary building logic ✗ |
| `assets/classes/Maileditor/Composer/ModuleJsonExport/ReadmeOverviewParser.php` | Extracts `## Overview` from README.md — generic utility with no application-specific dependencies |
| `assets/classes/Maileditor/Composer/KeywordGlossary/TenantListProvider.php` | Queries DB for tenant data — application-specific (should stay in HCP Editor) |
| `assets/classes/Maileditor/Composer/KeywordGlossary/TenantEntry.php` | VO for tenant + countries — application-specific (should stay in HCP Editor) |
| `assets/classes/Maileditor/Composer/ComposerScripts.php` | Build orchestration — calls framework generators. Application-specific (should stay in HCP Editor) |

### Integration point

`Application\Composer\ComposerScripts::doUpdateModuleDocumentation()` calls both `ModulesOverviewGenerator` and `KeywordGlossaryGenerator`. The HCP Editor's `ComposerScripts::updateContext()` calls the same two generators directly.

## Approach / Architecture

Five changes, ordered by dependency:

### Change A: Refactor `KeywordGlossaryGenerator` to use `ModuleInfoParser`

Replace the generator's inline YAML parsing with `ModuleInfoParser`. The generator currently calls `Yaml::parseFile()` directly and manually inspects `moduleMetaData.id` and `moduleMetaData.keywords` — exactly the logic `ModuleInfoParser::parseFile()` already encapsulates.

After this change, `KeywordGlossaryGenerator` will:
1. Create a `ModuleInfoParser` instance
2. Call `$this->parser->parseFile($file)` for each discovered YAML file
3. Use `ModuleInfo::getId()` and `ModuleInfo::getKeywords()` instead of raw array access

This eliminates the YAML parsing duplication and fulfils the stated purpose of the `ModuleInfoParser` extraction.

### Change B: Extract glossary building into `KeywordGlossaryBuilder`

Create a new class `Application\Composer\KeywordGlossary\KeywordGlossaryBuilder` that encapsulates the keyword collection, deduplication, and sorting logic currently duplicated in:
- `KeywordGlossaryGenerator::generate()` (lines 80–130)
- `ModuleJsonExportGenerator::buildGlossary()` (lines 139–190)

The builder will:
- Accept `ModuleInfo[]` as input
- Internally use `KeywordParser` for parsing
- Return sorted `KeywordEntry[]`
- Optionally accept a progress callback for conflict warnings

Both `KeywordGlossaryGenerator` and `ModuleJsonExportGenerator` will delegate to this builder.

### Change C: Move `ReadmeOverviewParser` to framework

Move `Maileditor\Composer\ModuleJsonExport\ReadmeOverviewParser` → `Application\Composer\ModulesOverview\ReadmeOverviewParser`.

This class has zero application-specific dependencies. It is a pure utility that extracts a `## Overview` section from a Markdown file. Moving it to the framework makes it available to any application for module documentation enrichment.

The HCP Editor's `ModuleJsonExportGenerator` will update its `use` statement to the new namespace.

### Change D: Remove dead code `collectGlossarySections()` from `ModuleJsonExportGenerator`

The private method `collectGlossarySections()` (lines 203–238) is defined but never called anywhere in the codebase. The `generate()` method only calls `buildGlossary()` — glossary sections are not included in the JSON output. Remove the dead method.

### Change E: Move JSON module export generator to framework

Move the core data-collection workflow from `Maileditor\Composer\ModuleJsonExport\ModuleJsonExportGenerator` to a new framework-level class `Application\Composer\ModulesOverview\ModuleJsonExportGenerator`.

The framework version will:
- Discover and parse modules via `ModuleContextFileFinder` + `ModuleInfoParser` (already framework classes)
- Resolve README overviews via `ReadmeOverviewParser` (moved in Change C)
- Build the glossary via `KeywordGlossaryBuilder` (created in Change B)
- Collect glossary sections via `DecorateGlossaryEvent`
- Provide a **hook method** `resolveModuleSource(ModuleInfo $module): string` that applications override to classify modules (e.g., "framework" vs "hcp-editor")
- Provide a **hook method** `resolveModuleBrief(ModuleInfo $module, string $sourcePath): ?string` that applications can override to resolve brief files (default: look for `README-Brief.md` in the source path)
- Accept an `$includeAll` parameter for filtering
- Output the JSON structure with `generatedAt`, `modules`, `glossary`, and `glossarySections`

The HCP Editor's `ModuleJsonExportGenerator` becomes a thin subclass that:
1. Overrides `resolveModuleSource()` to check `str_starts_with($module->getComposerPackage(), 'mistralys/')`
2. Inherits everything else

## Rationale

| Decision | Why |
|---|---|
| Refactor `KeywordGlossaryGenerator` before extracting the builder | The generator's internal parsing must be replaced first so it can consume `ModuleInfo[]`, which is the input the builder will require |
| Extract `KeywordGlossaryBuilder` as a separate class | Two consumers already exist (Markdown glossary + JSON export) and the logic is identical. A shared builder eliminates duplication and ensures consistency |
| Move `ReadmeOverviewParser` before the JSON export | The JSON export generator depends on it, so it must be available in the framework namespace first |
| Make the JSON export an open class (not `final`) | Applications need to override source-classification logic, which varies per project. Hook methods are simpler than injecting a strategy object for this use case |
| Remove dead code immediately | Dead code increases maintenance burden and confuses agents/developers reading the class |
| Keep tenant-related classes in HCP Editor | `TenantListProvider` and `TenantEntry` query application-specific database tables. They are correctly integrated via the `DecorateGlossaryEvent` extensibility mechanism |

## Detailed Steps

### Step 1 — Refactor `ModuleInfoParser` to use `BuildMessages` + refactor `KeywordGlossaryGenerator` (Change A)

**Step 1a — Adopt `BuildMessages` in `ModuleInfoParser`:**

1. Add `use Application\Composer\BuildMessages;` import to `ModuleInfoParser`.
2. Replace the three `echo` calls in `parseFile()` with `BuildMessages` calls:
   - YAML parse error: replace `echo sprintf('ERROR: ...')` + `throw $e` with `BuildMessages::addError('ModuleInfoParser', ...)` and `return null` instead of re-throwing (non-fatal, consistent with the glossary generator's current `continue` behaviour).
   - Missing `moduleMetaData`: replace `echo sprintf('WARNING: ...')` with `BuildMessages::addWarning('ModuleInfoParser', ...)`.
   - Incomplete `moduleMetaData`: replace `echo sprintf('WARNING: ...')` with `BuildMessages::addWarning('ModuleInfoParser', ...)`.
3. Keep the `use Symfony\Component\Yaml\Exception\ParseException;` import — it is still required by the `catch(ParseException $e)` clause even though the exception is no longer re-thrown.
4. Verify `ModulesOverviewGenerator` still works correctly (it already consumes `null` returns from `parseFile()`).

> **Design note — behavioral change:** This step changes `parseFile()` from **throwing** on YAML parse errors (fatal — aborts the build) to **returning `null`** (non-fatal — module is silently skipped with a `BuildMessages::addError()`). This is an intentional relaxation: a single malformed YAML file should not prevent the rest of the build from completing. Both `ModulesOverviewGenerator` and `KeywordGlossaryGenerator` already handle `null` returns gracefully via `continue`.

**Step 1b — Refactor `KeywordGlossaryGenerator` to use `ModuleInfoParser`:**

1. Add `use Application\Composer\ModulesOverview\ModuleInfoParser;` and `use Application\Composer\ModulesOverview\ModuleInfo;` imports to `KeywordGlossaryGenerator`.
2. Add a `private ModuleInfoParser $parser;` property, initialized in the constructor.
3. Replace the inline `Yaml::parseFile()` + manual `moduleMetaData` extraction loop (lines ~80–100) with:
   ```php
   $moduleInfo = $this->parser->parseFile($file);
   if ($moduleInfo === null) { continue; }
   $moduleId = $moduleInfo->getId();
   $keywords = $moduleInfo->getKeywords();
   ```
4. Remove the now-unused `use Symfony\Component\Yaml\Exception\ParseException;`, `use Symfony\Component\Yaml\Yaml;`, and `use Application\Composer\BuildMessages;` imports (all three are no longer referenced once the inline YAML parsing is replaced by `ModuleInfoParser`).
5. Run `composer test-filter -- KeywordGlossary` in the framework to verify no regressions.
6. Run `composer test-filter -- KeywordGlossary` in the HCP Editor to verify the application-level integration tests also pass.

### Step 2 — Extract `KeywordGlossaryBuilder` (Change B)

1. Create `src/classes/Application/Composer/KeywordGlossary/KeywordGlossaryBuilder.php`.
2. The class accepts `ModuleInfo[]` and an optional progress callback in its constructor.
3. Provide a `build(): array` method returning sorted `KeywordEntry[]`.
4. Move the keyword collection/deduplication/sorting logic from `KeywordGlossaryGenerator::generate()` into this builder.
5. Update `KeywordGlossaryGenerator::generate()` to delegate:
   ```php
   $builder = new KeywordGlossaryBuilder($modules, $this->onProgress);
   $entries = $builder->build();
   ```
6. Run `composer test-filter -- KeywordGlossary` in the framework to verify.
7. Run `composer dump-autoload` in the framework (classmap autoloading).

### Step 3 — Move `ReadmeOverviewParser` to framework (Change C)

1. Move `assets/classes/Maileditor/Composer/ModuleJsonExport/ReadmeOverviewParser.php` to `src/classes/Application/Composer/ModulesOverview/ReadmeOverviewParser.php` in the framework.
2. Update the namespace from `Maileditor\Composer\ModuleJsonExport` to `Application\Composer\ModulesOverview`.
3. Update the `use` statement in `ModuleJsonExportGenerator.php` in the HCP Editor to reference the new framework namespace.
4. Move the existing test class `tests/MailEditorTests/Composer/ModuleJsonExport/ReadmeOverviewParserTest.php` to `tests/AppFrameworkTests/Composer/ModulesOverview/ReadmeOverviewParserTest.php` in the framework. Update its namespace from `MailEditorTests\Composer\ModuleJsonExport` to `AppFrameworkTests\Composer\ModulesOverview`, change the base class from `MailTestCase` to `ApplicationTestCase`, and update the `use` import to `Application\Composer\ModulesOverview\ReadmeOverviewParser`.
5. Run `composer dump-autoload` in both the framework and the HCP Editor.
6. Run `composer test-filter -- ReadmeOverviewParser` in the framework to verify the moved tests pass.

### Step 4 — Remove dead code (Change D)

1. Delete the `collectGlossarySections()` method from `ModuleJsonExportGenerator.php`.
2. Remove associated `use` imports that become unused (`OfflineEventsManager`, `DecorateGlossaryEvent` — if not used elsewhere in the file).

### Step 5 — Move JSON export generator to framework (Change E)

1. Create `src/classes/Application/Composer/ModulesOverview/ModuleJsonExportGenerator.php` in the framework.
2. Implement the generic workflow:
   - Constructor accepts `FolderInfo $rootFolder`.
   - `generate(string $outputPath, bool $includeAll = false): void` — main workflow.
   - `protected function resolveModuleSource(ModuleInfo $module): string` — default returns `$module->getComposerPackage()`.
   - `protected function resolveModuleBrief(ModuleInfo $module, string $sourcePath): ?string` — default looks for `README-Brief.md`.
3. Use `KeywordGlossaryBuilder` for glossary building.
4. Use `ReadmeOverviewParser` for README overview extraction.
5. Fire `DecorateGlossaryEvent` for custom glossary sections and include them in the output as a `glossarySections` key. **Note:** This is new functionality — the current HCP Editor generator defines `collectGlossarySections()` but never calls it, so `glossarySections` has never appeared in the JSON output. The framework base class will properly integrate this, making it the first time glossary sections are included.
6. Reduce the HCP Editor's `ModuleJsonExportGenerator` to a thin subclass:
   ```php
   namespace Maileditor\Composer\ModuleJsonExport;

   use Application\Composer\ModulesOverview\ModuleJsonExportGenerator as BaseGenerator;
   use Application\Composer\ModulesOverview\ModuleInfo;

   class ModuleJsonExportGenerator extends BaseGenerator
   {
       protected function resolveModuleSource(ModuleInfo $module): string
       {
           return str_starts_with($module->getComposerPackage(), 'mistralys/')
               ? 'framework'
               : 'hcp-editor';
       }
   }
   ```
7. Run `composer dump-autoload` in both projects.
8. Run `composer test-filter -- ModuleJsonExport` in the HCP Editor (if tests exist).

### Step 6 — Verify integration

1. Run `composer build` in the framework. Verify `modules-overview.md` and `module-glossary.md` are generated correctly.
2. Run `composer build-dev` in the HCP Editor. Verify all three outputs (`modules-overview.md`, `module-glossary.md`, `modules.json`) are generated correctly.
3. Diff the generated files against their previous versions to confirm no content regressions.

## Dependencies

- Change B depends on Change A (the builder needs `ModuleInfo[]` input, which requires the generator to use `ModuleInfoParser` first).
- Change E depends on Changes B, C, and D (the framework JSON generator needs the builder, the moved parser, and the dead code removed).
- Changes A, C, and D are independent of each other and could be parallelized.

### Sequencing diagram

```
  A (Refactor KeywordGlossaryGenerator)
  │
  ├──► B (Extract KeywordGlossaryBuilder)
  │         │
  C (Move ReadmeOverviewParser)  ──────┐
  │                                     │
  D (Remove dead code)  ──────────────► E (Move JSON export to framework)
```

## Required Components

### New files (framework)

| File | Description |
|---|---|
| `src/classes/Application/Composer/KeywordGlossary/KeywordGlossaryBuilder.php` | Reusable glossary building logic |
| `src/classes/Application/Composer/ModulesOverview/ReadmeOverviewParser.php` | Moved from HCP Editor — extracts `## Overview` from README.md |
| `tests/AppFrameworkTests/Composer/ModulesOverview/ReadmeOverviewParserTest.php` | Moved from HCP Editor — tests for `ReadmeOverviewParser` |
| `src/classes/Application/Composer/ModulesOverview/ModuleJsonExportGenerator.php` | Generic JSON module metadata export |

### Modified files (framework)

| File | Change |
|---|---|
| `src/classes/Application/Composer/ModulesOverview/ModuleInfoParser.php` | Replace `echo` + exception re-throw with `BuildMessages` (non-fatal error handling) |
| `src/classes/Application/Composer/KeywordGlossary/KeywordGlossaryGenerator.php` | Use `ModuleInfoParser`, delegate to `KeywordGlossaryBuilder` |

### Modified files (HCP Editor)

| File | Change |
|---|---|
| `assets/classes/Maileditor/Composer/ModuleJsonExport/ModuleJsonExportGenerator.php` | Reduce to thin subclass of framework base |

### Deleted files (HCP Editor)

| File | Reason |
|---|---|
| `assets/classes/Maileditor/Composer/ModuleJsonExport/ReadmeOverviewParser.php` | Moved to framework |
| `tests/MailEditorTests/Composer/ModuleJsonExport/ReadmeOverviewParserTest.php` | Moved to framework (tests follow the class) |

### Files that stay unchanged (correctly placed)

| File | Reason |
|---|---|
| `assets/classes/Maileditor/Composer/KeywordGlossary/TenantListProvider.php` | Application-specific (database query) |
| `assets/classes/Maileditor/Composer/KeywordGlossary/TenantEntry.php` | Application-specific VO |
| `assets/classes/Maileditor/Composer/ComposerScripts.php` | Application-specific build orchestration |
| `tools/generate-module-json.php` | Application-specific CLI entry point |

## Assumptions

- The `KeywordGlossaryGenerator` has no other callers that depend on its internal YAML parsing behavior (verified: only called from `ComposerScripts` in both projects).
- The `ReadmeOverviewParser` has no application-specific behavior beyond what was observed (verified: it is a single static method with no external dependencies).
- The `ModuleJsonExportGenerator` in the HCP Editor has no subclasses or other callers beyond `tools/generate-module-json.php`.
- The `DecorateGlossaryEvent` mechanism is sufficient for all application-specific glossary extensions (the tenant brands integration confirms this pattern works).
- The Application Framework is currently symlinked into the HCP Editor's `vendor/mistralys/application_framework/` directory (DEV variant via `composer switch-dev`). This means files added or moved in the framework workspace are immediately visible to the HCP Editor without a separate install step — `composer dump-autoload` in the HCP Editor is sufficient to pick up new framework classes.

## Constraints

- All code must use `array()` syntax — never `[]`.
- All new files must include `declare(strict_types=1);`.
- No constructor promotion. No `readonly` properties. No PHP enums.
- Run `composer dump-autoload` after adding/moving class files (classmap autoloading).
- The framework's `ModuleJsonExportGenerator` must NOT be `final` — applications need to subclass it.

## Out of Scope

- Refactoring the `ModulesOverviewGenerator` or `ModulesOverviewRenderer` — these are already well-structured.
- Adding new metadata fields to `ModuleInfo` (e.g., README overview content) — the parser should remain focused on YAML metadata; README content is resolved at generation time.
- Changing the `module-context.yaml` schema.
- Adding new unit tests for `KeywordGlossaryBuilder` beyond what is already covered by the existing `KeywordGlossaryGeneratorTest` tests.
- Moving `TenantListProvider` or `TenantEntry` to the framework (these are correctly application-specific).

## Acceptance Criteria

- `KeywordGlossaryGenerator` uses `ModuleInfoParser` exclusively — no direct `Yaml::parseFile()` calls remain.
- A single `KeywordGlossaryBuilder` class exists in the framework, consumed by both the Markdown glossary generator and the JSON export generator.
- `ReadmeOverviewParser` lives in the framework namespace `Application\Composer\ModulesOverview`.
- No dead code (`collectGlossarySections()`) exists in the HCP Editor's `ModuleJsonExportGenerator`.
- The framework provides a generic `ModuleJsonExportGenerator` that any application can subclass.
- The HCP Editor's `ModuleJsonExportGenerator` is a thin subclass overriding only `resolveModuleSource()`.
- Running `composer build` in the framework produces identical `modules-overview.md` and `module-glossary.md` output.
- Running `composer build-dev` in the HCP Editor produces identical `modules-overview.md`, `module-glossary.md`, and `modules.json` output.
- All existing tests pass (with the moved `ReadmeOverviewParserTest` adapted to the framework's test infrastructure).
- The JSON output includes a `glossarySections` key (new feature introduced by Change E).

## Testing Strategy

1. **Framework unit tests:** Run `composer test-filter -- KeywordGlossary` after Changes A and B to verify the refactored glossary generator and new builder produce identical output.
2. **Framework unit tests:** Run `composer test-filter -- ModulesOverview` after Changes C and E to verify the overview generator and new JSON export generator work correctly.
3. **HCP Editor integration:** Run `composer build-dev` and diff all generated output files against the previous versions to confirm byte-identical results (or expected formatting-only differences from the glossary sections addition).
4. **Regression check:** Verify `tools/generate-module-json.php` still produces valid JSON output in the HCP Editor.

**Recommended follow-up (out of scope for this plan):** Add dedicated unit tests for `KeywordGlossaryBuilder` in the framework's test suite.

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **`KeywordGlossaryGenerator` refactoring changes output** | The generator's keyword extraction logic and `ModuleInfoParser` parse the same YAML fields. Diff generated `module-glossary.md` before and after to confirm identical output. Warning messages are now consistent because the parser adopts `BuildMessages` (Step 1a). |
| **Glossary sections missing from JSON after dead code removal** | The `collectGlossarySections()` method was never called, so its removal has zero runtime effect. The framework's JSON generator in Change E will properly integrate glossary sections. |
| **HCP Editor subclass breaks after framework base class changes** | The subclass is minimal (one method override). Integration testing via `composer build-dev` will catch any issues immediately. |
| **Classmap autoloading stale after file moves** | Run `composer dump-autoload` after every file add/move/rename step. Verify with a clean build. |
| **`ModuleInfoParser` error handling change (fatal → non-fatal)** | The parser currently throws `ParseException` on YAML errors, which aborts the entire build. Step 1a changes it to log via `BuildMessages::addError()` and return `null`, making the error non-fatal (the malformed module is skipped). This is an intentional behavioral change: a single broken YAML file should not prevent the rest of the build. Both `ModulesOverviewGenerator` and `KeywordGlossaryGenerator` already handle `null` returns gracefully via `continue`. |
| **`glossarySections` is a new JSON output key** | Change E introduces `glossarySections` in the JSON output for the first time. The current HCP Editor generator defines `collectGlossarySections()` but never calls it. Verify downstream consumers (e.g., MCP knowledgebase) tolerate the new key. |
