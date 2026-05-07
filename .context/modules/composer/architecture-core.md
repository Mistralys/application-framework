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
                └── IconBuilder/
                    ├── AbstractLanguageRenderer.php
                    ├── IconBuilder.php
                    ├── IconDefinition.php
                    ├── IconsReader.php
                    ├── JSRenderer.php
                    ├── PHPRenderer.php
                └── KeywordGlossary/
                    ├── Events/
                    │   ├── BaseDecorateGlossaryListener.php
                    │   ├── DecorateGlossaryEvent.php
                    ├── GlossarySection.php
                    ├── GlossarySectionEntry.php
                    ├── KeywordEntry.php
                    ├── KeywordGlossaryBuilder.php
                    ├── KeywordGlossaryGenerator.php
                    ├── KeywordGlossaryRenderer.php
                    ├── KeywordParser.php
                └── ModulesOverview/
                    └── ModuleContextFileFinder.php
                    └── ModuleInfo.php
                    └── ModuleInfoParser.php
                    └── ModuleJsonExportGenerator.php
                    └── ModulesOverviewGenerator.php
                    └── ModulesOverviewRenderer.php
                    └── ReadmeOverviewParser.php

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
use Application\Bootstrap\Screen\TestSuiteBootstrap as TestSuiteBootstrap;
use Application\CacheControl\CacheManager as CacheManager;
use Application\Composer\IconBuilder\IconBuilder as IconBuilder;
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


	public static function generateOpenAPISpec(): void
	{
		/* ... */
	}


	public static function doGenerateOpenAPISpec(): void
	{
		/* ... */
	}


	public static function generateHtaccess(): void
	{
		/* ... */
	}


	public static function doGenerateHtaccess(): void
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


	public static function rebuildIcons(): void
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
###  Path: `/src/classes/Application/Composer/IconBuilder/AbstractLanguageRenderer.php`

```php
namespace Application\Composer\IconBuilder;

/**
 * Abstract base class for language-specific icon method renderers. Provides
 * the shared region-marker structure and method iteration loop; subclasses
 * implement only the single-method rendering logic.
 *
 * The rendered output of {@see self::render()} is the text that replaces the
 * content between the `START METHODS` and `END METHODS` marker comments
 * inside the target PHP or JS file.
 *
 * @package Application
 * @subpackage Composer
 * @see PHPRenderer
 * @see JSRenderer
 * @see IconsReader
 */
abstract class AbstractLanguageRenderer
{
	/**
	 * Returns the icons reader instance providing the icon definitions
	 * to render.
	 *
	 * @return IconsReader
	 */
	public function getIconsReader(): IconsReader
	{
		/* ... */
	}


	/**
	 * Assembles and returns the complete replacement content that goes
	 * between the `START METHODS` and `END METHODS` marker comments.
	 *
	 * The output includes the region label marker, all generated icon
	 * method lines, and the endregion marker, surrounded by the blank
	 * lines that preserve the original file structure.
	 *
	 * @return string
	 */
	public function render(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Composer/IconBuilder/IconBuilder.php`

```php
namespace Application\Composer\IconBuilder;

use AppUtils\OperationResult as OperationResult;

/**
 * Orchestrator that reads icon definitions from a JSON source file, renders
 * PHP and JS method blocks, and replaces the content between the
 * {@see self::MARKER_START} and {@see self::MARKER_END} marker comments in
 * the respective target files.
 *
 * Designed for build-time use and has no dependency on the full application
 * bootstrap, the `LocalFrameworkClone`, or the `t()` translation function.
 *
 * @package Application
 * @subpackage Composer
 * @see IconsReader
 * @see PHPRenderer
 * @see JSRenderer
 */
class IconBuilder
{
	public const ERROR_PHP_ICON_FILE_NOT_FOUND = 82301;
	public const ERROR_JS_ICON_FILE_NOT_FOUND = 82302;
	public const ERROR_START_MARKER_NOT_FOUND = 82303;
	public const ERROR_WRITE_FAILED = 82304;
	public const ERROR_END_MARKER_NOT_FOUND = 82305;
	public const ERROR_READ_FAILED = 82306;

	/**
	 * Returns the icons reader, creating it on first access.
	 *
	 * @return IconsReader
	 */
	public function getIcons(): IconsReader
	{
		/* ... */
	}


	/**
	 * Rebuilds the icon methods in both target PHP and JS files.
	 *
	 * Reads icon definitions from the JSON source, renders method code for
	 * each language, and replaces the content between the
	 * `START METHODS` / `END METHODS` marker comments in each target file.
	 *
	 * Returns a failed {@see OperationResult} when any of the following occur:
	 * - The PHP target file does not exist ({@see self::ERROR_PHP_ICON_FILE_NOT_FOUND}).
	 * - The JS target file does not exist ({@see self::ERROR_JS_ICON_FILE_NOT_FOUND}).
	 * - A start marker is absent in either target file ({@see self::ERROR_START_MARKER_NOT_FOUND}).
	 * - An end marker is absent in either target file ({@see self::ERROR_END_MARKER_NOT_FOUND}).
	 * - Reading a target file fails (e.g. permission denied) ({@see self::ERROR_READ_FAILED}).
	 * - Writing a target file fails (e.g. read-only file, full disk) ({@see self::ERROR_WRITE_FAILED}).
	 *
	 * @return OperationResult
	 */
	public function build(): OperationResult
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Composer/IconBuilder/IconDefinition.php`

```php
namespace Application\Composer\IconBuilder;

/**
 * Value object for a single icon definition parsed from an icons JSON file.
 * Holds the icon's ID, FA icon name, and FA icon type (prefix), and provides
 * derived name forms used by the language renderers during code generation.
 *
 * @package Application
 * @subpackage Composer
 * @see IconsReader
 */
class IconDefinition
{
	/**
	 * Returns the icon's ID as defined in the JSON source file
	 * (e.g. `attention_required` or `apiClients`).
	 *
	 * @return string
	 */
	public function getID(): string
	{
		/* ... */
	}


	/**
	 * Returns the FontAwesome icon name (e.g. `exclamation-triangle`).
	 *
	 * @return string
	 */
	public function getIconName(): string
	{
		/* ... */
	}


	/**
	 * Returns the FontAwesome icon type / prefix (e.g. `far`, `fas`).
	 * Empty string when the default prefix applies.
	 *
	 * @return string
	 */
	public function getIconType(): string
	{
		/* ... */
	}


	/**
	 * Returns the full icon identifier in the form `type:name` when a type is
	 * present, or just the icon name when the type is empty.
	 *
	 * Examples: `far:sun`, `rocket`.
	 *
	 * @return string
	 */
	public function getFullIconName(): string
	{
		/* ... */
	}


	/**
	 * Returns the icon ID in UPPER_SNAKE_CASE form suitable for use as a
	 * PHP class constant name.
	 *
	 * Examples: `attention_required` → `ATTENTION_REQUIRED`,
	 * `apiClients` → `API_CLIENTS`, `actioncode` → `ACTIONCODE`.
	 *
	 * @return string
	 */
	public function getConstantName(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Composer/IconBuilder/IconsReader.php`

```php
namespace Application\Composer\IconBuilder;

use AppUtils\FileHelper\JSONFile as JSONFile;
use UI\Icons\IconInfo as IconInfo;

/**
 * Parses an icons JSON file and returns a sorted, filtered list of
 * {@see IconDefinition} instances for use by the language renderers
 * during icon code generation.
 *
 * IDs are normalised on load (hyphens and spaces become underscores).
 * The spinner icon is excluded from the result set because it has
 * special runtime behaviour and must not be overwritten by the builder.
 *
 * @package Application
 * @subpackage Composer
 * @see IconDefinition
 * @see IconBuilder
 */
class IconsReader
{
	public const EXCLUDED_ICON_SPINNER = 'spinner';

	/**
	 * Returns the path to the icons JSON file.
	 *
	 * @return string
	 */
	public function getPath(): string
	{
		/* ... */
	}


	/**
	 * Returns all parsed icon definitions, sorted alphabetically by ID
	 * and with the spinner icon excluded.
	 *
	 * @return IconDefinition[]
	 */
	public function getIcons(): array
	{
		/* ... */
	}


	/**
	 * Returns the number of icon definitions in the parsed set.
	 *
	 * @return int
	 */
	public function countIcons(): int
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Composer/IconBuilder/JSRenderer.php`

```php
namespace Application\Composer\IconBuilder;

/**
 * Renders JS icon accessor methods for insertion between the
 * `START METHODS` / `END METHODS` markers in a JS icon object.
 *
 * Each generated method follows the pattern:
 * <pre>
 *     {MethodName}:function() { return this.SetType('{iconName}'); },
 * </pre>
 *
 * The method name is the icon ID converted to PascalCase
 * (underscores are used as word separators).
 *
 * @package Application
 * @subpackage Composer
 * @see AbstractLanguageRenderer
 * @see PHPRenderer
 */
class JSRenderer extends AbstractLanguageRenderer
{
}


```
###  Path: `/src/classes/Application/Composer/IconBuilder/PHPRenderer.php`

```php
namespace Application\Composer\IconBuilder;

/**
 * Renders PHP icon accessor methods for insertion between the
 * `START METHODS` / `END METHODS` markers in a PHP icon class.
 *
 * Each generated method follows the pattern:
 * <pre>
 *     /**
 *      * @return $this
 *      *\/
 *     public function {methodName}() : self { return $this->setType('{iconName}'); }
 * </pre>
 *
 * The method name is the icon ID converted to camelCase
 * (underscores are used as word separators).
 *
 * @package Application
 * @subpackage Composer
 * @see AbstractLanguageRenderer
 * @see JSRenderer
 */
class PHPRenderer extends AbstractLanguageRenderer
{
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
###  Path: `/src/classes/Application/Composer/KeywordGlossary/KeywordGlossaryBuilder.php`

```php
namespace Application\Composer\KeywordGlossary;

use Application\Composer\ModulesOverview\ModuleInfo as ModuleInfo;

/**
 * Builds a deduplicated, sorted list of {@see KeywordEntry} objects
 * from a collection of {@see ModuleInfo} value objects.
 *
 * Encapsulates the keyword collection, conflict detection, deduplication,
 * and alphabetical sorting that was previously duplicated across
 * {@see KeywordGlossaryGenerator} and `ModuleJsonExportGenerator`.
 *
 * @package Application
 * @subpackage Composer
 */
final class KeywordGlossaryBuilder
{
	/**
	 * Builds the deduplicated and alphabetically sorted keyword entry list.
	 *
	 * Keywords are keyed by their lowercase form; only the first-seen casing is
	 * preserved. When the same keyword appears in multiple modules, the module
	 * IDs are merged. A conflict warning is issued via the progress callback when
	 * the same keyword carries different context strings across modules.
	 *
	 * @return KeywordEntry[]
	 */
	public function build(): array
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
use Application\Composer\KeywordGlossary\Events\DecorateGlossaryEvent as DecorateGlossaryEvent;
use Application\Composer\ModulesOverview\ModuleContextFileFinder as ModuleContextFileFinder;
use Application\Composer\ModulesOverview\ModuleInfoParser as ModuleInfoParser;
use Application\EventHandler\OfflineEvents\OfflineEventsManager as OfflineEventsManager;

/**
 * Orchestrates the keyword-glossary generation workflow.
 *
 * Discovers all `module-context.yaml` files via {@see ModuleContextFileFinder},
 * delegates parsing to {@see ModuleInfoParser} to obtain {@see ModuleInfo} value
 * objects from each (files lacking `id`, `label`, or `description` are skipped),
 * delegates keyword deduplication and sorting to {@see KeywordGlossaryBuilder},
 * fires {@see DecorateGlossaryEvent} via the offline events manager to collect
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
- **Size**: 34.17 KB
- **Lines**: 1440
File: `modules/composer/architecture-core.md`
