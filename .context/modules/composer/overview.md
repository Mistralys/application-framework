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
├── ModulesOverview/
│   ├── ModulesOverviewGenerator.php    — Orchestrates modules-overview generation
│   ├── ModuleContextFileFinder.php     — Discovers module-context.yaml files
│   ├── ModuleInfo.php                  — Immutable VO: parsed module metadata
│   └── ModulesOverviewRenderer.php     — Renders the Markdown overview table
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
- **Size**: 7.49 KB
- **Lines**: 213
File: `modules/composer/overview.md`
