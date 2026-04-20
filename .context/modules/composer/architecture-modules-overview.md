# Application Composer - ModulesOverview Subpackage
_SOURCE: ModulesOverview generator classes_
# ModulesOverview generator classes
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── Composer/
                └── ModulesOverview/
                    └── ModuleContextFileFinder.php
                    └── ModuleInfo.php
                    └── ModuleInfoParser.php
                    └── ModuleJsonExportGenerator.php
                    └── ModulesOverviewGenerator.php
                    └── ModulesOverviewRenderer.php
                    └── ReadmeOverviewParser.php

```
###  Path: `/src/classes/Application/Composer/ModulesOverview/ModuleContextFileFinder.php`

```php
namespace Application\Composer\ModulesOverview;

use AppUtils\FileHelper as FileHelper;
use AppUtils\FileHelper\FileInfo as FileInfo;
use AppUtils\FileHelper\FolderInfo as FolderInfo;
use Symfony\Component\Yaml\Yaml as Yaml;

/**
 * Discovers all `module-context.yaml` files in the project by
 * following the import chain defined in the project's `context.yaml`.
 *
 * Handles two import styles:
 * - Glob patterns ending in `module-context.yaml` (e.g. `assets/classes/ ** /module-context.yaml`)
 * - References to other `context.yaml` files (recursed into)
 *
 * @package Application
 * @subpackage Composer
 */
final class ModuleContextFileFinder
{
	/**
	 * Parses the project's `context.yaml` and recursively follows
	 * all `import` entries to collect every `module-context.yaml` file.
	 *
	 * @return FileInfo[]
	 */
	public function findAll(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Composer/ModulesOverview/ModuleInfo.php`

```php
namespace Application\Composer\ModulesOverview;

/**
 * Immutable value object holding parsed metadata for a single
 * module discovered from a `module-context.yaml` file.
 *
 * @package Application
 * @subpackage Composer
 */
final class ModuleInfo
{
	public function getId(): string
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	public function getDescription(): string
	{
		/* ... */
	}


	/**
	 * @return string[]
	 */
	public function getRelatedModules(): array
	{
		/* ... */
	}


	public function getSourcePath(): string
	{
		/* ... */
	}


	public function getContextOutputFolder(): string
	{
		/* ... */
	}


	public function getComposerPackage(): string
	{
		/* ... */
	}


	/**
	 * Returns the list of domain-specific keywords declared for this module
	 * in its `module-context.yaml` `moduleMetaData.keywords` field.
	 *
	 * @return string[]
	 */
	public function getKeywords(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Composer/ModulesOverview/ModuleInfoParser.php`

```php
namespace Application\Composer\ModulesOverview;

use AppUtils\FileHelper\FileInfo as FileInfo;
use AppUtils\FileHelper\FolderInfo as FolderInfo;
use AppUtils\FileHelper\JSONFile as JSONFile;
use Application\Composer\BuildMessages as BuildMessages;
use Symfony\Component\Yaml\Exception\ParseException as ParseException;
use Symfony\Component\Yaml\Yaml as Yaml;

/**
 * Parses individual `module-context.yaml` files into {@see ModuleInfo}
 * value objects.
 *
 * Encapsulates YAML parsing, source-path resolution, Composer-package
 * resolution, and CTX output-folder resolution so that every generator
 * that consumes module metadata ({@see ModulesOverviewGenerator},
 * application-level `ModuleJsonExportGenerator`, etc.) shares a single,
 * authoritative implementation.
 *
 * @package Application
 * @subpackage Composer
 */
final class ModuleInfoParser
{
	/**
	 * Parses a single `module-context.yaml` file and returns the corresponding
	 * {@see ModuleInfo}. Returns `null` if the file cannot be parsed or lacks a
	 * valid `moduleMetaData` section; diagnostics are registered via {@see BuildMessages}.
	 *
	 * @param FileInfo $file
	 * @return ModuleInfo|null
	 */
	public function parseFile(FileInfo $file): ?ModuleInfo
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Composer/ModulesOverview/ModuleJsonExportGenerator.php`

```php
namespace Application\Composer\ModulesOverview;

use AppUtils\FileHelper\FileInfo as FileInfo;
use AppUtils\FileHelper\FolderInfo as FolderInfo;
use Application\Composer\KeywordGlossary\Events\DecorateGlossaryEvent as DecorateGlossaryEvent;
use Application\Composer\KeywordGlossary\KeywordGlossaryBuilder as KeywordGlossaryBuilder;
use Application\EventHandler\OfflineEvents\OfflineEventsManager as OfflineEventsManager;

/**
 * Generic, subclassable generator that encapsulates the
 * application-agnostic module JSON export workflow.
 *
 * Discovers and parses all `module-context.yaml` files via
 * {@see ModuleContextFileFinder} and {@see ModuleInfoParser}, resolves
 * README overviews via {@see ReadmeOverviewParser} and module briefs via
 * {@see resolveModuleBrief()}, builds the keyword glossary via
 * {@see KeywordGlossaryBuilder}, fires {@see DecorateGlossaryEvent} to
 * collect custom glossary sections, and writes a JSON document with
 * `generatedAt`, `modules`, `glossary`, and `glossarySections` keys.
 *
 * Applications can subclass this generator and override the hook methods
 * {@see resolveModuleSource()} and {@see resolveModuleBrief()} to customise
 * module source classification and brief resolution without duplicating the
 * core data-collection workflow.
 *
 * Progress output is routed through the optional `$onProgress` callable.
 * When `null`, no output is produced, which is suitable for automated or
 * test contexts.
 *
 * @package Application
 * @subpackage Composer
 */
class ModuleJsonExportGenerator
{
	/**
	 * Orchestrates the full workflow: discovers modules, resolves descriptions
	 * and briefs, builds the glossary, collects glossary sections, and writes
	 * the JSON output file.
	 *
	 * By default only modules that have a brief are included in the output.
	 * Pass `true` for `$includeAll` to include modules without a brief.
	 *
	 * @param string $outputPath Absolute path to the JSON output file.
	 * @param bool   $includeAll When true, modules without a brief are also included.
	 * @return void
	 */
	public function generate(string $outputPath, bool $includeAll = false): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Composer/ModulesOverview/ModulesOverviewGenerator.php`

```php
namespace Application\Composer\ModulesOverview;

use AppUtils\FileHelper\FileInfo as FileInfo;
use AppUtils\FileHelper\FolderInfo as FolderInfo;
use Application\Composer\BuildMessages as BuildMessages;

/**
 * Orchestrates the module overview generation workflow.
 *
 * Discovers all `module-context.yaml` files via {@see ModuleContextFileFinder},
 * parses each into a {@see ModuleInfo} value object, renders the resulting
 * Markdown document via {@see ModulesOverviewRenderer}, and writes it to
 * `docs/agents/project-manifest/modules-overview.md`.
 *
 * @package Application
 * @subpackage Composer
 */
final class ModulesOverviewGenerator
{
	/**
	 * Runs the full generation workflow and writes the output file.
	 *
	 * @return void
	 */
	public function generate(): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Composer/ModulesOverview/ModulesOverviewRenderer.php`

```php
namespace Application\Composer\ModulesOverview;

/**
 * Renders a Markdown overview document from a collection of {@see ModuleInfo}
 * objects. Modules are grouped by Composer package, sorted alphabetically
 * within each group. Packages themselves are sorted alphabetically.
 *
 * @package Application
 * @subpackage Composer
 */
final class ModulesOverviewRenderer
{
	/**
	 * Builds and returns the complete Markdown document string.
	 *
	 * @return string
	 */
	public function render(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Composer/ModulesOverview/ReadmeOverviewParser.php`

```php
namespace Application\Composer\ModulesOverview;

/**
 * Utility class for extracting the `## Overview` section text
 * from a module's README.md file.
 *
 * @package Application
 * @subpackage Composer
 */
final class ReadmeOverviewParser
{
	/**
	 * Extracts the text content of the `## Overview` section from a README.md file.
	 *
	 * Returns the trimmed text between the `## Overview` heading and the next
	 * `##` heading (or the end of the file). Returns `null` if the file does
	 * not exist or contains no `## Overview` section.
	 *
	 * @param string $readmePath Absolute path to the README.md file.
	 * @return string|null The trimmed overview text, or null if not found.
	 */
	public static function extractOverview(string $readmePath): ?string
	{
		/* ... */
	}
}


```
---
**File Statistics**
- **Size**: 8.64 KB
- **Lines**: 330
File: `modules/composer/architecture-modules-overview.md`
