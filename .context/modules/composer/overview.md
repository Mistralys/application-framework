# Application Composer - Overview
_SOURCE: Application\Composer Overview_
# Application\Composer Overview
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── Composer/
                └── README.md

```
###  Path: `/src/classes/Application/Composer/README.md`

```md
# Module: Application\Composer — Build-Time Documentation Generators

## Purpose

The `Application\Composer` namespace contains build-time utilities that run during
`composer build` / `composer build-dev`. They generate two Markdown documentation
artefacts from `module-context.yaml` files distributed throughout the codebase:

- **Modules Overview** (`docs/agents/project-manifest/modules-overview.md`) — a
  Markdown table listing every module, its source path, context docs location, and
  inter-module dependencies.
- **Keyword Glossary** (`docs/agents/project-manifest/module-glossary.md`) — a
  Markdown glossary mapping opaque domain terms to the modules that define them,
  plus any custom sections contributed by application-level offline-event listeners.

A shared **`BuildMessages`** registry collects build-time notices, warnings, and
errors emitted during generation and prints them in a consolidated summary.

## Subpackages

```
src/classes/Application/Composer/
├── BuildMessages.php                   — Shared build-time message registry
├── IconBuilder/
│   ├── IconBuilder.php                 — Orchestrator: reads JSON, renders PHP+JS, writes target files
│   ├── IconsReader.php                 — Parses custom-icons.json into IconDefinition value objects
│   ├── IconDefinition.php              — Immutable VO: a single icon (ID, FA name, FA type prefix)
│   ├── AbstractLanguageRenderer.php    — Base class for PHP/JS method renderers
│   ├── PHPRenderer.php                 — Renders PHP icon accessor methods (camelCase)
│   └── JSRenderer.php                  — Renders JS icon accessor methods (PascalCase)
├── ModulesOverview/
│   ├── ModulesOverviewGenerator.php    — Orchestrates modules-overview generation
│   ├── ModuleContextFileFinder.php     — Discovers module-context.yaml files
│   ├── ModuleInfo.php                  — Immutable VO: parsed module metadata
│   ├── ModuleInfoParser.php            — Parses module-context.yaml files into ModuleInfo VOs
│   ├── ReadmeOverviewParser.php        — Extracts the Overview section from a module README.md
│   ├── ModulesOverviewRenderer.php     — Renders the Markdown overview table
│   └── ModuleJsonExportGenerator.php  — Subclassable base: exports module data as JSON
└── KeywordGlossary/
    ├── KeywordGlossaryGenerator.php    — Orchestrates keyword-glossary generation
    ├── KeywordParser.php               — Parses "TERM (context)" keyword strings
    ├── KeywordEntry.php                — Immutable VO: a single parsed keyword
    ├── KeywordGlossaryRenderer.php     — Renders the Markdown glossary document
    ├── GlossarySection.php             — VO: a custom section (heading + rows)
    ├── GlossarySectionEntry.php        — VO: a single row in a GlossarySection
    └── Events/
        ├── DecorateGlossaryEvent.php          — Offline event: collect custom sections
        └── BaseDecorateGlossaryListener.php   — Base class for listener implementations
```

## IconBuilder subpackage

A build-time code generator that reads icon definitions from a JSON source file
and writes typed accessor methods into a PHP class and a JS object. Applications
use this to define custom FontAwesome icons that are available as fluent method
calls at both server and client level.

### Adding a new custom icon

1. **Edit `themes/custom-icons.json`** in the application root. Add a key–value
   entry where the key is the icon ID and the value specifies the FontAwesome
   icon name and type prefix:

   ```json
   {
     "my-feature": {
       "icon": "wand-magic-sparkles",
       "type": "fas"
     }
   }
   ```

   | Field    | Description | Examples |
   |----------|-------------|----------|
   | **key**  | Icon ID. Hyphens and spaces are normalised to underscores. | `my-feature`, `mail_builder` |
   | `icon`   | FontAwesome icon name (without prefix). | `wand-magic-sparkles`, `university` |
   | `type`   | FontAwesome style prefix. | `fas` (solid), `far` (regular), `fab` (brands), `fa` (default) |

2. **Run `composer rebuild-icons`** (or `composer build` / `composer build-dev`,
   which include this step). The builder replaces the code between the
   `/* START METHODS */` and `/* END METHODS */` markers in both target files.

3. **Use the generated methods.** The icon ID `my-feature` produces:
   - PHP: `MyApp::icon()->myFeature()` (camelCase)
   - JS: `application.Icon().MyFeature()` (PascalCase)

### Reserved IDs

The ID `spinner` is excluded from generation because it has special runtime
behaviour and must not be overwritten.

### Target files

The application's `ComposerScripts::rebuildIcons()` method wires the builder
to the correct paths. Typical targets:

| File | Role |
|------|------|
| `assets/classes/<AppNamespace>/CustomIcon.php` | PHP icon class extending `UI_Icon` |
| `themes/default/js/ui/custom-icon.js` | JS icon object |

## `BuildMessages`

A static registry for build-time output. Emitting a message:

```php
use Application\Composer\BuildMessages;

BuildMessages::addMessage('Module overview generated.');
BuildMessages::addError('Missing module ID in context.yaml: src/classes/Foo/');
```

At the end of the build step, call `printSummary()` to display all collected
messages. Messages can also be persisted to a file for CI pipelines.

## ModulesOverview subpackage

### Entry point

```php
use Application\Composer\ModulesOverview\ModulesOverviewGenerator;
use AppUtils\FileHelper\FolderInfo;

(new ModulesOverviewGenerator(FolderInfo::factory('/path/to/repo/root')))->generate();
```

`generate()` writes the output to
`docs/agents/project-manifest/modules-overview.md` relative to the provided
root folder.

### Discovery mechanism

`ModuleContextFileFinder` reads the root `context.yaml`, follows its `import`
chain, and collects every `module-context.yaml` file referenced. The framework
and any application built on it can therefore auto-discover all modules without
manual registration.

### Parsing and error handling

`ModuleInfoParser` handles all YAML loading and `moduleMetaData` validation.
It never throws — all parse failures are routed through `BuildMessages` and
`null` is returned:

- **YAML parse error** → `BuildMessages::addError()` + `return null`
- **Missing or non-array `moduleMetaData` section** → `BuildMessages::addWarning()` + `return null`
- **Incomplete `moduleMetaData`** (missing `id`, `label`, or `description`) → `BuildMessages::addWarning()` + `return null`

Callers such as `ModulesOverviewGenerator` simply skip `null` results;
no try/catch is required and no output is written to stdout.

### README overview extraction

`ReadmeOverviewParser` is a utility that extracts the `## Overview` section text
from any module's `README.md` file. It is used by application-level generators
(e.g. `ModuleJsonExportGenerator`) to embed human-readable module summaries into
generated documents.

```php
use Application\Composer\ModulesOverview\ReadmeOverviewParser;

$overview = ReadmeOverviewParser::extractOverview('/path/to/module/README.md');
// Returns the trimmed text between "## Overview" and the next "##" heading,
// or null if the file does not exist or has no Overview section.
```

### JSON module export — `ModuleJsonExportGenerator`

`ModuleJsonExportGenerator` is a generic, subclassable generator that encapsulates
the full module-data export workflow. It discovers all `module-context.yaml` files,
parses them, resolves README overviews and module briefs, builds the keyword
glossary, fires `DecorateGlossaryEvent`, and writes a single JSON document.

**Basic usage (framework default behaviour):**

```php
use Application\Composer\ModulesOverview\ModuleJsonExportGenerator;
use AppUtils\FileHelper\FolderInfo;

$generator = new ModuleJsonExportGenerator(FolderInfo::factory('/path/to/repo/root'));
$generator->generate('/path/to/output/modules.json');

// Pass true to include modules that have no README-Brief.md:
$generator->generate('/path/to/output/modules.json', includeAll: true);
```

**JSON output structure:**

```json
{
  "generatedAt": "2026-04-20T10:00:00+00:00",
  "modules": [
    {
      "id": "my-module",
      "label": "My Module",
      "summary": "Short description from module-context.yaml",
      "source": "vendor/my-package",
      "description": "Overview section from README.md (or null)",
      "relatedModules": ["other-module"],
      "brief": "Full content of README-Brief.md (or null)"
    }
  ],
  "glossary": [
    { "term": "Widget", "context": "the core UI component", "relatedModules": ["my-module"] }
  ],
  "glossarySections": []
}
```

**Subclassing contract — extension points:**

Applications built on the framework typically subclass `ModuleJsonExportGenerator`
and override one or both of the two protected hook methods. Neither hook needs to
call `parent::`.

| Hook method | Default behaviour | Override reason |
|---|---|---|
| `resolveModuleSource(ModuleInfo $module): string` | Returns `$module->getComposerPackage()` (e.g. `vendor/my-package`) | Override to return a human-readable source label (e.g. `'framework'` or `'hcp-editor'`) |
| `resolveModuleBrief(ModuleInfo $module, string $sourcePath): ?string` | Looks for `README-Brief.md` in the module's source directory | Override to use a different file name, location, or resolution strategy |

The `$sourcePath` parameter passed to `resolveModuleBrief()` is the **absolute** path
to the module's source directory (trailing slash stripped). Use `$module` for
identifier-based lookups or `$sourcePath` for filesystem-based ones.

**Minimal subclass example:**

```php
use Application\Composer\ModulesOverview\ModuleJsonExportGenerator;
use Application\Composer\ModulesOverview\ModuleInfo;

class AppModuleJsonExportGenerator extends ModuleJsonExportGenerator
{
    protected function resolveModuleSource(ModuleInfo $module) : string
    {
        // Map composer package to a display label
        return match($module->getComposerPackage()) {
            'mistralys/application_framework' => 'framework',
            default                           => 'app',
        };
    }
}
```

> **Note:** No unit test exists for `ModuleJsonExportGenerator` yet. A follow-up
> `ModuleJsonExportGeneratorTest` is recommended to cover the `generate()` workflow
> end-to-end, the `$includeAll` flag behaviour, and hook method override scenarios.

## KeywordGlossary subpackage

### Entry point

```php
use Application\Composer\KeywordGlossary\KeywordGlossaryGenerator;
use AppUtils\FileHelper\FolderInfo;

(new KeywordGlossaryGenerator(FolderInfo::factory('/path/to/repo/root')))->generate(
    '/path/to/repo/root/docs/agents/project-manifest/module-glossary.md'
);
```

### Keyword format in `module-context.yaml`

Keywords are listed under the `moduleMetaData.keywords` key:

```yaml
moduleMetaData:
  id: "my-module"
  label: "My Module"
  keywords:
    - "Widget (the core UI component)"
    - "Sprocket"
```

`KeywordParser` parses the optional `(context)` suffix from each keyword string.

### Custom sections via `DecorateGlossaryEvent`

After building the keyword table, `KeywordGlossaryGenerator` fires
`DecorateGlossaryEvent` through the framework's offline event system. Any
application module can register a listener to contribute additional sections
(e.g. a tenant list, a brand registry, a colour palette).

**Step 1 — Create a `GlossarySection`:**

```php
use Application\Composer\KeywordGlossary\GlossarySection;
use Application\Composer\KeywordGlossary\GlossarySectionEntry;

$section = new GlossarySection(
    'Tenants',
    array('Tenant', 'Country / Region'),
    array(
        new GlossarySectionEntry(array('ACME Corp', 'US')),
        new GlossarySectionEntry(array('Globex', 'EU')),
    )
);
```

**Step 2 — Implement the listener:**

Create a file in the application's offline events folder at
`OfflineEvents/DecorateGlossary/YourListenerName.php`:

```php
use Application\Composer\KeywordGlossary\Events\BaseDecorateGlossaryListener;
use Application\Composer\KeywordGlossary\Events\DecorateGlossaryEvent;

class AddTenantSectionListener extends BaseDecorateGlossaryListener
{
    public function handle(DecorateGlossaryEvent $event) : void
    {
        // Build $section as shown above, then:
        $event->addSection($section);
    }
}
```

The framework's offline event indexer picks up the listener automatically during
the next `composer build`.

## Integration with `ComposerScripts::build()`

`Application\Composer\ComposerScripts::build()` already calls
`updateModuleDocumentation()`, which in turn invokes both generators. No
additional wiring is needed if an application delegates its build step to the
framework's `ComposerScripts`.

## Tests

The test suite lives under `tests/AppFrameworkTests/Composer/`:

| Test file | Class under test | Type |
|---|---|---|
| `BuildMessagesTest.php` | `BuildMessages` | Unit |
| `ModulesOverview/ModulesOverviewRendererTest.php` | `ModulesOverviewRenderer` | Unit |
| `ModulesOverview/ModuleContextFileFinderTest.php` | `ModuleContextFileFinder` | Integration |
| `ModulesOverview/ModulesOverviewGeneratorTest.php` | `ModulesOverviewGenerator` | Integration |
| `ModulesOverview/ModuleJsonExportGeneratorTest.php` | `ModuleJsonExportGenerator` | _(planned — not yet implemented)_ |
| `KeywordGlossary/KeywordGlossaryRendererTest.php` | `KeywordGlossaryRenderer` | Unit |
| `KeywordGlossary/KeywordParserTest.php` | `KeywordParser` | Unit |
| `KeywordGlossary/GlossarySectionTest.php` | `GlossarySection` + `GlossarySectionEntry` | Unit |
| `KeywordGlossary/DecorateGlossaryEventTest.php` | `DecorateGlossaryEvent` | Unit |
| `KeywordGlossary/KeywordGlossaryGeneratorTest.php` | `KeywordGlossaryGenerator` | Integration (fixture) |

Run the suite in the framework root:

```bash
composer test-filter -- Composer
```

> **Note:** `ModulesOverviewGeneratorTest` runs `ModulesOverviewGenerator` against
> the framework's own module structure as a side-effect and **updates the tracked
> file** `docs/agents/project-manifest/modules-overview.md`. This is intentional
> for an integration test of a generator; be aware when running the test suite
> that this file will be modified on disk.

## Context

- Interfaces: NO
- Public API: YES (entry-point generators and value objects)
- Offline event: `DecorateGlossaryEvent` (event name: `DecorateGlossary`)
- Build dependency: `symfony/yaml` (`require-dev`)

```
---
**File Statistics**
- **Size**: 14.7 KB
- **Lines**: 384
File: `modules/composer/overview.md`
