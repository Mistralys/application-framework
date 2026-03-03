# Application Composer - Core Architecture (Public API)
_SOURCE: BuildMessages, ComposerScripts, CSSClassesGenerator_
# BuildMessages, ComposerScripts, CSSClassesGenerator
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── Composer/
                └── BuildMessages.php
                └── CSSClassesGenerator.php
                └── ComposerScripts.php
                └── KeywordGlossary/
                    ├── Events/
                    │   ├── BaseDecorateGlossaryListener.php
                    │   ├── DecorateGlossaryEvent.php
                    ├── GlossarySection.php
                    ├── GlossarySectionEntry.php
                    ├── KeywordEntry.php
                    ├── KeywordGlossaryGenerator.php
                    ├── KeywordGlossaryRenderer.php
                    ├── KeywordParser.php
                └── ModulesOverview/
                    └── ModuleContextFileFinder.php
                    └── ModuleInfo.php
                    └── ModulesOverviewGenerator.php
                    └── ModulesOverviewRenderer.php

```
###  Path: `/src/classes/Application/Composer/BuildMessages.php`

```php
namespace Application\Composer;

/**
 * Static registry that collects notices, warnings, and errors emitted
 * during a Composer build run ({@see ComposerScripts::build()} /
 * {@see ComposerScripts::buildDEV()}).
 *
 * Any build step can add a message via {@see self::addMessage()}. At the
 * end of the build {@see self::printSummary()} is called to render all
 * collected messages in a highlighted block so they are not lost in the
 * regular console log output.
 *
 * Usage example:
 *
 * ```php
 * BuildMessages::addMessage('MyGenerator', BuildMessages::LEVEL_WARNING, 'Something looks off.');
 * ```
 *
 * @package Application
 * @subpackage Composer
 */
class BuildMessages
{
	public const LEVEL_NOTICE = 'NOTICE';
	public const LEVEL_WARNING = 'WARNING';
	public const LEVEL_ERROR = 'ERROR';

	/**
	 * Adds a message to the build-time collection.
	 *
	 * @param string $source  Short label identifying the build step (e.g. `ModulesOverviewGenerator`).
	 * @param string $level   Severity — use one of the `LEVEL_*` constants.
	 * @param string $message The human-readable message text.
	 * @return void
	 */
	public static function addMessage(string $source, string $level, string $message): void
	{
		/* ... */
	}


	/**
	 * Convenience shorthand for {@see self::addMessage()} with level {@see self::LEVEL_WARNING}.
	 *
	 * @param string $source
	 * @param string $message
	 * @return void
	 */
	public static function addWarning(string $source, string $message): void
	{
		/* ... */
	}


	/**
	 * Convenience shorthand for {@see self::addMessage()} with level {@see self::LEVEL_ERROR}.
	 *
	 * @param string $source
	 * @param string $message
	 * @return void
	 */
	public static function addError(string $source, string $message): void
	{
		/* ... */
	}


	/**
	 * Returns `true` when at least one message has been collected.
	 *
	 * @return bool
	 */
	public static function hasMessages(): bool
	{
		/* ... */
	}


	/**
	 * Returns `true` when at least one message with level {@see self::LEVEL_ERROR}
	 * has been collected.
	 *
	 * @return bool
	 */
	public static function hasErrors(): bool
	{
		/* ... */
	}


	/**
	 * Prints the collected messages to stdout as a prominent summary block,
	 * grouped by source. Does nothing when no messages were collected.
	 *
	 * @return void
	 */
	public static function printSummary(): void
	{
		/* ... */
	}


	/**
	 * Clears all collected messages. Useful in tests.
	 *
	 * @return void
	 */
	public static function reset(): void
	{
		/* ... */
	}


	/**
	 * Serialises all collected messages to a JSON file so they survive across
	 * separate PHP process invocations (i.e. between Composer script entries).
	 *
	 * @param string $filePath Absolute path to the target file.
	 * @return void
	 */
	public static function saveToFile(string $filePath): void
	{
		/* ... */
	}


	/**
	 * Loads messages previously saved by {@see self::saveToFile()} into the
	 * in-memory registry. Each entry is passed through {@see self::addMessage()}
	 * so duplicate detection is always enforced. Silently does nothing when the
	 * file does not exist.
	 *
	 * @param string $filePath Absolute path to the source file.
	 * @return void
	 */
	public static function loadFromFile(string $filePath): void
	{
		/* ... */
	}


	/**
	 * Deletes the messages file if it exists. Call this at the start of a build
	 * run to ensure no stale data from a previous (possibly failed) run leaks in.
	 *
	 * @param string $filePath Absolute path to the file to remove.
	 * @return void
	 */
	public static function clearFile(string $filePath): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Composer/CSSClassesGenerator.php`

```php
namespace Application\Composer;

use AppUtils\FileHelper\FileInfo as FileInfo;
use Application\AppFactory as AppFactory;
use ReflectionClass as ReflectionClass;
use UI\CSSClasses as CSSClasses;

/**
 * Generates the clientside JavaScript reference file for the
 * {@see CSSClasses} PHP class constants, so that clientside UI
 * handling can use the same class name definitions.
 *
 * The generated file is written to the theme's JS folder and
 * is automatically included in all page requests as part of
 * the core scripts.
 *
 * @package Application
 * @subpackage Composer
 * @see CSSClasses
 * @see ComposerScripts::generateCSSClassesJS()
 */
class CSSClassesGenerator
{
	public const TARGET_JS_FILE = 'ui/css-classes.js';

	public function generate(): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Composer/ComposerScripts.php`

```php
namespace Application\Composer;

use AppUtils\FileHelper\FileInfo as FileInfo;
use AppUtils\FileHelper\FolderInfo as FolderInfo;
use AppUtils\Microtime as Microtime;
use Application\API\APIManager as APIManager;
use Application\Admin\Index\AdminScreenIndexer as AdminScreenIndexer;
use Application\AppFactory as AppFactory;
use Application\AppFactory\ClassCacheHandler as ClassCacheHandler;
use Application\CacheControl\CacheManager as CacheManager;
use Application\Composer\KeywordGlossary\KeywordGlossaryGenerator as KeywordGlossaryGenerator;
use Application\Composer\ModulesOverview\ModulesOverviewGenerator as ModulesOverviewGenerator;
use Application\EventHandler\OfflineEvents\Index\EventIndexer as EventIndexer;
use Application\Exception\ApplicationException as ApplicationException;

/**
 * Class with static methods that are used as Composer scripts.
 *
 * See the {@see /composer.json} file for the scripts that are defined.
 *
 * @package Application
 * @subpackage Composer
 */
class ComposerScripts
{
	public const ERROR_BOOTSTRAP_NOT_FOUND = 169801;

	public static function build(): void
	{
		/* ... */
	}


	public static function doUpdateModuleDocumentation(): void
	{
		/* ... */
	}


	public static function clearCaches(): void
	{
		/* ... */
	}


	public static function doClearCaches(): void
	{
		/* ... */
	}


	public static function apiMethodIndex(): void
	{
		/* ... */
	}


	public static function indexAdminScreens(): void
	{
		/* ... */
	}


	public static function doIndexAdminScreens(): void
	{
		/* ... */
	}


	public static function indexOfflineEvents(): void
	{
		/* ... */
	}


	public static function doIndexOfflineEvents(): void
	{
		/* ... */
	}


	public static function doApiMethodIndex(): void
	{
		/* ... */
	}


	public static function generateCSSClassesJS(): void
	{
		/* ... */
	}


	public static function doGenerateCSSClassesJS(): void
	{
		/* ... */
	}


	/**
	 * Loads the bootstrap file for the application.
	 *
	 * When running within the framework GIT package,
	 * the test application's bootstrap file is loaded.
	 * Otherwise, the application's bootstrap file is
	 * used.
	 *
	 * This way, the scripts can be used interchangeably
	 * when developing the framework and the application.
	 *
	 * @return void
	 */
	public static function init(): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Composer/KeywordGlossary/Events/BaseDecorateGlossaryListener.php`

```php
namespace Application\Composer\KeywordGlossary\Events;

use AppUtils\ClassHelper as ClassHelper;
use Application\EventHandler\Event\EventInterface as EventInterface;
use Application\EventHandler\OfflineEvents\BaseOfflineListener as BaseOfflineListener;

/**
 * Base class for offline listeners that contribute sections to the
 * keyword glossary via {@see DecorateGlossaryEvent}.
 *
 * ## Usage
 *
 * Extend this class and implement {@see handleGlossaryDecoration()} to
 * add one or more {@see \Application\Composer\KeywordGlossary\GlossarySection}
 * instances to the event.
 *
 * @package Application
 * @subpackage Composer
 * @see DecorateGlossaryEvent
 */
abstract class BaseDecorateGlossaryListener extends BaseOfflineListener
{
	public function getEventName(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Composer/KeywordGlossary/Events/DecorateGlossaryEvent.php`

```php
namespace Application\Composer\KeywordGlossary\Events;

use Application\Composer\KeywordGlossary\GlossarySection as GlossarySection;
use Application\EventHandler\OfflineEvents\BaseOfflineEvent as BaseOfflineEvent;

/**
 * Offline event fired when the keyword glossary is being decorated.
 * Listeners may contribute custom {@see GlossarySection} instances
 * which the generator will include in the rendered glossary document.
 *
 * ## Usage
 *
 * 1. Add listeners in the offline event folder named `DecorateGlossary`.
 * 2. Extend the base class {@see BaseDecorateGlossaryListener}.
 *
 * @package Application
 * @subpackage Composer
 */
class DecorateGlossaryEvent extends BaseOfflineEvent
{
	public const EVENT_NAME = 'DecorateGlossary';

	public function getName(): string
	{
		/* ... */
	}


	/**
	 * Appends a glossary section contributed by a listener.
	 *
	 * @param GlossarySection $section
	 * @return void
	 */
	public function addSection(GlossarySection $section): void
	{
		/* ... */
	}


	/**
	 * Returns all glossary sections collected from listeners.
	 *
	 * @return GlossarySection[]
	 */
	public function getSections(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Composer/KeywordGlossary/GlossarySection.php`

```php
namespace Application\Composer\KeywordGlossary;

/**
 * Immutable value object representing a single section in the keyword
 * glossary, with a heading, column headers, and a list of entries.
 *
 * @package Application
 * @subpackage Composer
 */
final class GlossarySection
{
	public function getHeading(): string
	{
		/* ... */
	}


	/**
	 * @return string[]
	 */
	public function getColumnHeaders(): array
	{
		/* ... */
	}


	/**
	 * @return GlossarySectionEntry[]
	 */
	public function getEntries(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Composer/KeywordGlossary/GlossarySectionEntry.php`

```php
namespace Application\Composer\KeywordGlossary;

/**
 * Immutable value object representing a single row in a {@see GlossarySection},
 * holding the cell values for that row.
 *
 * @package Application
 * @subpackage Composer
 */
final class GlossarySectionEntry
{
	/**
	 * @return string[]
	 */
	public function getValues(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Composer/KeywordGlossary/KeywordEntry.php`

```php
namespace Application\Composer\KeywordGlossary;

/**
 * Immutable value object representing a single keyword entry with its
 * optional context and the list of module IDs that declare it.
 *
 * @package Application
 * @subpackage Composer
 */
final class KeywordEntry
{
	public function getKeyword(): string
	{
		/* ... */
	}


	public function getContext(): string
	{
		/* ... */
	}


	/**
	 * @return string[]
	 */
	public function getModuleIds(): array
	{
		/* ... */
	}


	/**
	 * Returns a new instance with the given module ID appended (immutable update).
	 *
	 * @param string $moduleId
	 * @return self
	 */
	public function addModuleId(string $moduleId): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Composer/KeywordGlossary/KeywordGlossaryGenerator.php`

```php
namespace Application\Composer\KeywordGlossary;

use AppUtils\FileHelper\FileInfo as FileInfo;
use AppUtils\FileHelper\FolderInfo as FolderInfo;
use Application\Composer\BuildMessages as BuildMessages;
use Application\Composer\KeywordGlossary\Events\DecorateGlossaryEvent as DecorateGlossaryEvent;
use Application\Composer\ModulesOverview\ModuleContextFileFinder as ModuleContextFileFinder;
use Application\EventHandler\OfflineEvents\OfflineEventsManager as OfflineEventsManager;
use Symfony\Component\Yaml\Exception\ParseException as ParseException;
use Symfony\Component\Yaml\Yaml as Yaml;

/**
 * Orchestrates the keyword-glossary generation workflow.
 *
 * Discovers all `module-context.yaml` files via {@see ModuleContextFileFinder},
 * extracts `moduleMetaData.id` and `moduleMetaData.keywords` from each,
 * builds a de-duplicated {@see KeywordEntry} map, fires
 * {@see DecorateGlossaryEvent} via the offline events manager to collect
 * custom {@see GlossarySection} instances, renders the Markdown document
 * via {@see KeywordGlossaryRenderer}, and writes it to the specified output path.
 *
 * @package Application
 * @subpackage Composer
 */
final class KeywordGlossaryGenerator
{
	/**
	 * Returns the root folder.
	 *
	 * @return FolderInfo
	 */
	public function getRootFolder(): FolderInfo
	{
		/* ... */
	}


	/**
	 * Runs the full generation workflow and writes the output file.
	 *
	 * @param string $outputPath Absolute path to the Markdown output file.
	 * @return void
	 */
	public function generate(string $outputPath): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Composer/KeywordGlossary/KeywordGlossaryRenderer.php`

```php
namespace Application\Composer\KeywordGlossary;

/**
 * Renders a Markdown keyword-glossary document from pre-sorted
 * {@see KeywordEntry} and {@see GlossarySection} collections.
 *
 * The caller is responsible for sorting both collections before construction.
 * Uses LF-only ("\n") line endings for OS-independent output.
 *
 * @package Application
 * @subpackage Composer
 */
final class KeywordGlossaryRenderer
{
	/**
	 * Returns the keyword entries.
	 *
	 * @return KeywordEntry[]
	 */
	public function getKeywords(): array
	{
		/* ... */
	}


	/**
	 * Returns the custom glossary sections.
	 *
	 * @return GlossarySection[]
	 */
	public function getSections(): array
	{
		/* ... */
	}


	/**
	 * Builds and returns the complete Markdown document string.
	 *
	 * @return string
	 */
	public function render(): string
	{
		/* ... */
	}


	/**
	 * Renders a single glossary section as an array of Markdown lines.
	 *
	 * Produces the section heading (`## {heading}`), a Markdown table
	 * header with the section's column headers, and one table row per
	 * {@see GlossarySectionEntry}.
	 *
	 * @param GlossarySection $section
	 * @return string[]
	 */
	public function renderSection(GlossarySection $section): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Composer/KeywordGlossary/KeywordParser.php`

```php
namespace Application\Composer\KeywordGlossary;

/**
 * Parses a raw keyword string of the form "TERM (context description)"
 * into its constituent parts.
 *
 * Edge cases handled:
 * - No parenthesis present → context is empty string.
 * - Nested parentheses → context is trimmed to the last `)`.
 * - Empty string → returns empty keyword and context.
 *
 * @package Application
 * @subpackage Composer
 */
final class KeywordParser
{
	/**
	 * Parses a raw keyword string into its keyword and context parts.
	 *
	 * @param  string $rawKeyword Raw keyword string, e.g. "SOCCER (default enrichment system)".
	 * @return array{keyword: string, context: string}
	 */
	public static function parse(string $rawKeyword): array
	{
		/* ... */
	}
}


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
- **Size**: 19.45 KB
- **Lines**: 887
File: `modules/composer/architecture-core.md`
