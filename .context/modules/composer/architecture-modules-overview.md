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
                    └── ModulesOverviewGenerator.php
                    └── ModulesOverviewRenderer.php

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
###  Path: `/src/classes/Application/Composer/ModulesOverview/ModulesOverviewGenerator.php`

```php
namespace Application\Composer\ModulesOverview;

use AppUtils\FileHelper\FileInfo as FileInfo;
use AppUtils\FileHelper\FolderInfo as FolderInfo;
use AppUtils\FileHelper\JSONFile as JSONFile;
use Application\Composer\BuildMessages as BuildMessages;
use Symfony\Component\Yaml\Exception\ParseException as ParseException;
use Symfony\Component\Yaml\Yaml as Yaml;

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
---
**File Statistics**
- **Size**: 4.25 KB
- **Lines**: 198
File: `modules/composer/architecture-modules-overview.md`
