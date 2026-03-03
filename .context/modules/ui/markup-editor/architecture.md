# UI Markup Editor - Architecture
_SOURCE: Public class signatures for all editor backends_
# Public class signatures for all editor backends
```
// Structure of documents
└── src/
    └── classes/
        └── UI/
            └── MarkupEditor.php
            └── MarkupEditor/
                ├── CKEditor.php
                ├── Redactor.php
            └── MarkupEditorInfo.php

```
###  Path: `/src/classes/UI/MarkupEditor.php`

```php
namespace ;

use AppUtils\Interfaces\OptionableInterface as OptionableInterface;
use AppUtils\Interfaces\StringableInterface as StringableInterface;
use AppUtils\Traits\OptionableTrait as OptionableTrait;

/**
 * Base class for the available markup editors.
 *
 * @package Application
 * @subpackage MarkupEditor
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 */
abstract class UI_MarkupEditor implements OptionableInterface, StringableInterface
{
	use OptionableTrait;

	/**
	 * Starts the redactor: adds the required javascript includes and stylesheets,
	 * as well as any configuration statements as needed. This is called automatically
	 * by the {@link UI::renderHeadIncludes()} method.
	 *
	 * @return UI_MarkupEditor
	 */
	public function start(): UI_MarkupEditor
	{
		/* ... */
	}


	abstract public static function getLabel(): string;


	abstract public function injectControlMarkup(UI_Form_Renderer_Element $element, string $markup): string;


	/**
	 * To make it compatible with form attributes.
	 * @return string
	 */
	public function __toString()
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/MarkupEditor/CKEditor.php`

```php
namespace ;

use AppLocalize\Localization as Localization;

/**
 * Handles integrating the CKEditor WYSIWYG editor.
 *
 * @package Application
 * @subpackage MarkupEditor
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 *
 * @see template:ui/markup-editor/ckeditor/command
 */
class UI_MarkupEditor_CKEditor extends UI_MarkupEditor
{
	public const BUTTON_BOLD = 'bold';
	public const BUTTON_ITALIC = 'italic';
	public const BUTTON_SUPERSCRIPT = 'superscript';
	public const BUTTON_LINK = 'link';
	public const BUTTON_BULLETED_LIST = 'bulletedList';
	public const BUTTON_NUMBERED_LIST = 'numberedList';
	public const BUTTON_REMOVE_FORMAT = 'removeFormat';
	public const BUTTON_UNDO = 'undo';
	public const BUTTON_REDO = 'redo';
	public const BUTTON_PASTE_AS_PLAIN_TEXT = 'pasteAsPlainText';
	public const BUTTON_STRIKETHROUGH = 'strikethrough';
	public const BUTTON_ALIGN_LEFT = 'alignment:left';
	public const BUTTON_ALIGN_CENTER = 'alignment:center';
	public const BUTTON_ALIGN_RIGHT = 'alignment:right';
	public const BUTTON_ALIGN = 'alignment';

	public static function getLabel(): string
	{
		/* ... */
	}


	public function getDefaultOptions(): array
	{
		/* ... */
	}


	public function insertButtonAfter(string $buttonName, string $afterName): UI_MarkupEditor_CKEditor
	{
		/* ... */
	}


	public function insertButtonBefore(string $buttonName, string $beforeName): UI_MarkupEditor_CKEditor
	{
		/* ... */
	}


	/**
	 * Toggles loading the default CKEditor build. If turned off,
	 * it is possible to load a custom build instead (must load the
	 * javascript file manually using the UI methods).
	 *
	 * @param bool $useCustom
	 * @return $this
	 */
	public function setUseCustomJSBuild(bool $useCustom = true): self
	{
		/* ... */
	}


	/**
	 * Adds a plugin to use.
	 *
	 * NOTE: The CKEditor build must already include the target
	 * plugin. The editor's javascript include is one giant file
	 * that contains everything. If a plugin is not included yet,
	 * it has to be added to the build first.
	 *
	 * @param string $name
	 * @return UI_MarkupEditor_CKEditor
	 */
	public function addPlugin(string $name): UI_MarkupEditor_CKEditor
	{
		/* ... */
	}


	public function injectControlMarkup(UI_Form_Renderer_Element $renderer, string $markup): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/MarkupEditor/Redactor.php`

```php
namespace ;

/**
 * UI helper that handles adding the required clientside includes
 * for the redactor WYSIWYG editor.
 *
 * @package Application
 * @subpackage MarkupEditor
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 */
class UI_MarkupEditor_Redactor extends UI_MarkupEditor
{
	public static function getLabel(): string
	{
		/* ... */
	}


	public function getDefaultOptions(): array
	{
		/* ... */
	}


	/**
	 * Adds the name of a redactor plugin to load. Note that
	 * each plugin is only added once.
	 *
	 * @param string $name
	 * @param string $src The relative path to the javascript include file of the plugin, will be added automatically.
	 * @return UI_MarkupEditor_Redactor
	 */
	public function addPlugin($name, $src = null)
	{
		/* ... */
	}


	/**
	 * Sets whether to use XHTML. Default: <code>true</code>.
	 * @param boolean $xhtml
	 * @return UI_MarkupEditor_Redactor
	 */
	public function setXHTML($xhtml = true)
	{
		/* ... */
	}


	/**
	 * Sets the HTML tags allowed in the editor. This is an
	 * array with lowercase tag names.
	 *
	 * @param array $tags
	 * @return UI_MarkupEditor_Redactor
	 */
	public function setAllowedTags($tags)
	{
		/* ... */
	}


	/**
	 * Sets the buttons to use in the editor control.
	 * Note: only used in conjunction with the
	 * {@link configure()} method.
	 *
	 * @param array $buttons
	 * @return UI_MarkupEditor_Redactor
	 */
	public function setButtons($buttons)
	{
		/* ... */
	}


	/**
	 * Adds an onload configuration statement for the redactor, using
	 * the specified jquery element selector, e.g. <code>.redactor</code>.
	 *
	 * @param string $selector
	 * @return UI_MarkupEditor_Redactor
	 */
	public function configure($selector)
	{
		/* ... */
	}


	public function injectControlMarkup(UI_Form_Renderer_Element $renderer, string $markup): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/MarkupEditorInfo.php`

```php
namespace ;

/**
 * Information container for a markup editor type: allows
 * retrieving a human-readable label and its ID.
 *
 * Additionally, it allows selecting a markup editor as
 * the default for the whole application.
 *
 * @package Application
 * @subpackage MarkupEditor
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 *
 * @see UI::getMarkupEditors()
 */
class UI_MarkupEditorInfo
{
	public const SETTING_NAME_MARKUP_EDITOR_ID = 'MarkupEditorID';

	public function getID(): string
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	/**
	 * Selects this markup editor as the default for the
	 * whole application.
	 *
	 * @return UI_MarkupEditorInfo
	 */
	public function selectAsDefault(): UI_MarkupEditorInfo
	{
		/* ... */
	}
}


```
---
**File Statistics**
- **Size**: 6.65 KB
- **Lines**: 317
File: `modules/ui/markup-editor/architecture.md`
