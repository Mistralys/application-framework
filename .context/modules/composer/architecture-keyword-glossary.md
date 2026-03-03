# Application Composer - KeywordGlossary Subpackage
_SOURCE: KeywordGlossary generator classes and offline event_
# KeywordGlossary generator classes and offline event
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── Composer/
                └── KeywordGlossary/
                    └── Events/
                        ├── BaseDecorateGlossaryListener.php
                        ├── DecorateGlossaryEvent.php
                    └── GlossarySection.php
                    └── GlossarySectionEntry.php
                    └── KeywordEntry.php
                    └── KeywordGlossaryGenerator.php
                    └── KeywordGlossaryRenderer.php
                    └── KeywordParser.php

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
---
**File Statistics**
- **Size**: 8.65 KB
- **Lines**: 385
File: `modules/composer/architecture-keyword-glossary.md`
