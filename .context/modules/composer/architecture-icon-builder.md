# Application Composer - IconBuilder Subpackage
_SOURCE: IconBuilder code-generation pipeline_
# IconBuilder code-generation pipeline
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── Composer/
                └── IconBuilder/
                    └── AbstractLanguageRenderer.php
                    └── IconBuilder.php
                    └── IconDefinition.php
                    └── IconsReader.php
                    └── JSRenderer.php
                    └── PHPRenderer.php

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
---
**File Statistics**
- **Size**: 8.2 KB
- **Lines**: 340
File: `modules/composer/architecture-icon-builder.md`
