# UI Themes - Architecture
_SOURCE: Public class signatures for the theme manager and theme_
# Public class signatures for the theme manager and theme
```
// Structure of documents
└── src/
    └── classes/
        └── UI/
            └── Themes.php
            └── Themes/
                └── BaseTemplates/
                    ├── NavigationTemplate.php
                └── Exception.php
                └── Exception/
                    ├── VariableMissingException.php
                └── Theme.php
                └── Theme/
                    └── ContentRenderer.php

```
###  Path: `/src/classes/UI/Themes.php`

```php
namespace ;

/**
 * Theme manager: manages the available themes and provides a way
 * to access the selected theme details.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Themes
{
	public const ERROR_THEME_CLASS_FILE_NOT_FOUND = 28401;
	public const ERROR_THEME_CLASS_NOT_FOUND = 28402;

	/**
	 * @return UI
	 */
	public function getUI(): UI
	{
		/* ... */
	}


	/**
	 * Retrieves the ID of the currently selected theme.
	 * @return string
	 */
	public function getThemeID(): string
	{
		/* ... */
	}


	/**
	 * Retrieves the current theme.
	 *
	 * @return UI_Themes_Theme
	 * @throws Application_Exception
	 *
	 * @see UI_Themes::ERROR_THEME_CLASS_FILE_NOT_FOUND
	 * @see UI_Themes::ERROR_THEME_CLASS_NOT_FOUND
	 */
	public function getTheme(): UI_Themes_Theme
	{
		/* ... */
	}


	/**
	 * Checks whether the theme exists in the file system.
	 *
	 * @param string $id
	 * @return boolean
	 */
	public function themeIDExists(string $id): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Themes/BaseTemplates/NavigationTemplate.php`

```php
namespace UI\Themes\BaseTemplates;

use DateTime as DateTime;
use JSHelper as JSHelper;
use UI_Page_Navigation as UI_Page_Navigation;
use UI_Page_Navigation_Item as UI_Page_Navigation_Item;
use UI_Page_Navigation_Item_DropdownMenu as UI_Page_Navigation_Item_DropdownMenu;
use UI_Page_Template_Custom as UI_Page_Template_Custom;

abstract class NavigationTemplate extends UI_Page_Template_Custom
{
	abstract public function getElementID(): string;
}


```
###  Path: `/src/classes/UI/Themes/Exception.php`

```php
namespace ;

class UI_Themes_Exception extends Application_Exception
{
}


```
###  Path: `/src/classes/UI/Themes/Exception/VariableMissingException.php`

```php
namespace UI\Themes\Exception;

use UI\Interfaces\PageTemplateInterface as PageTemplateInterface;
use UI_Themes_Exception as UI_Themes_Exception;

class VariableMissingException extends UI_Themes_Exception
{
	public const ERROR_CODE = 104301;
}


```
###  Path: `/src/classes/UI/Themes/Theme.php`

```php
namespace ;

abstract class UI_Themes_Theme
{
	public const ERROR_RESOURCE_FILE_NOT_FOUND = 27401;
	const LOCATION_DRIVER = 'driver';
	const LOCATION_DEFAULT = 'default';
	const FILE_TYPE_STYLESHEET = 'css';
	const FILE_TYPE_JAVASCRIPT = 'js';
	const FILE_TYPE_TEMPLATE = 'templates';
	const FILE_TYPE_GRAPHIC = 'img';
	public const IMAGE_FILE_EMPTY_IMAGE = 'empty-image.png';

	public function getDefaultRootPath(): string
	{
		/* ... */
	}


	public function getDriverRootPath(): string
	{
		/* ... */
	}


	public function getID()
	{
		/* ... */
	}


	/**
	 * Retrieves the base paths to all resource
	 * repositories.
	 *
	 * @return array
	 */
	public function getResourcePaths(): array
	{
		/* ... */
	}


	public function getDefaultPath()
	{
		/* ... */
	}


	public function getDefaultURL()
	{
		/* ... */
	}


	public function getDriverPath()
	{
		/* ... */
	}


	public function getDriverURL()
	{
		/* ... */
	}


	public function getDefaultTemplatesPath()
	{
		/* ... */
	}


	public function getDefaultTemplatesURL()
	{
		/* ... */
	}


	public function getDefaultImagesPath()
	{
		/* ... */
	}


	public function getDefaultImagesURL()
	{
		/* ... */
	}


	public function getDefaultStylesheetsURL()
	{
		/* ... */
	}


	public function getDefaultStylesheetsPath()
	{
		/* ... */
	}


	public function getDefaultJavascriptsURL()
	{
		/* ... */
	}


	public function getDefaultJavascriptsPath()
	{
		/* ... */
	}


	public function getDriverTemplatesPath()
	{
		/* ... */
	}


	public function getDriverTemplatesURL()
	{
		/* ... */
	}


	public function getDriverImagesPath()
	{
		/* ... */
	}


	public function getDriverImagesURL()
	{
		/* ... */
	}


	public function getDriverStylesheetsURL()
	{
		/* ... */
	}


	public function getDriverStylesheetsPath()
	{
		/* ... */
	}


	public function getDriverJavascriptsURL()
	{
		/* ... */
	}


	public function getDriverJavascriptsPath()
	{
		/* ... */
	}


	public function getImageURL($fileName)
	{
		/* ... */
	}


	public function getEmptyImageURL(): string
	{
		/* ... */
	}


	public function getEmptyImagePath(): string
	{
		/* ... */
	}


	public function getImagePath($fileName)
	{
		/* ... */
	}


	public function getStylesheetURL($fileName)
	{
		/* ... */
	}


	public function getStylesheetPath($fileName)
	{
		/* ... */
	}


	public function getJavascriptURL($fileName)
	{
		/* ... */
	}


	public function getJavascriptPath($fileName)
	{
		/* ... */
	}


	public function getTemplateURL($fileName)
	{
		/* ... */
	}


	public function getTemplatePath($fileName)
	{
		/* ... */
	}


	public function getResourceURL($type, $fileName = null, $location = self::LOCATION_DEFAULT)
	{
		/* ... */
	}


	public function getResourcePath($type, $fileName = null, $location = self::LOCATION_DEFAULT)
	{
		/* ... */
	}


	public function injectJS()
	{
		/* ... */
	}


	public function findResource($fileName, $resourceType)
	{
		/* ... */
	}


	public function injectDependencies()
	{
		/* ... */
	}


	/**
	 * Creates a new content renderer instance.
	 *
	 * @param UI $ui
	 * @return UI_Themes_Theme_ContentRenderer
	 */
	public function createContentRenderer(UI $ui): UI_Themes_Theme_ContentRenderer
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Themes/Theme/ContentRenderer.php`

```php
namespace ;

use AppUtils\ClassHelper\BaseClassHelperException as BaseClassHelperException;
use AppUtils\ConvertHelper as ConvertHelper;
use AppUtils\Interfaces\OptionableInterface as OptionableInterface;
use AppUtils\Interfaces\StringableInterface as StringableInterface;
use AppUtils\Traits\OptionableTrait as OptionableTrait;

/**
 * A content renderer is automatically given to each UI_Page instance,
 * and is used to customize the upper scaffold of the pages, like the
 * page title, abstract, etc., as well as to hold the content that is
 * shown in the page.
 *
 * NOTE: implements the Renderable interface, but does not extend the
 * Renderable class because of timing issues.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Themes_Theme_ContentRenderer implements OptionableInterface, UI_Renderable_Interface
{
	use OptionableTrait;
	use UI_Traits_RenderableGeneric;

	public function getDefaultOptions(): array
	{
		/* ... */
	}


	public function getUI(): UI
	{
		/* ... */
	}


	public function getRenderer(): UI_Themes_Theme_ContentRenderer
	{
		/* ... */
	}


	/**
	 * Enables the sidebar (off by default).
	 * @return UI_Themes_Theme_ContentRenderer
	 */
	public function makeWithSidebar(): UI_Themes_Theme_ContentRenderer
	{
		/* ... */
	}


	/**
	 * Disable the sidebar (off by default).
	 * @return UI_Themes_Theme_ContentRenderer
	 */
	public function makeWithoutSidebar(): UI_Themes_Theme_ContentRenderer
	{
		/* ... */
	}


	public function setWithSidebar(bool $with = true): UI_Themes_Theme_ContentRenderer
	{
		/* ... */
	}


	/**
	 * Sets the page title. This is used as the browser title
	 * as well if the page has not been given a specific title.
	 *
	 * @param string|number|UI_Renderable_Interface $title
	 * @return UI_Themes_Theme_ContentRenderer
	 */
	public function setTitle($title): UI_Themes_Theme_ContentRenderer
	{
		/* ... */
	}


	/**
	 * Sets the subline text to show directly beneath the title.
	 *
	 * @param string|number|UI_Renderable_Interface $subline
	 * @return UI_Themes_Theme_ContentRenderer
	 */
	public function setTitleSubline($subline): UI_Themes_Theme_ContentRenderer
	{
		/* ... */
	}


	/**
	 * Sets the page's abstract text, shown below the subnavigation.
	 *
	 * @param string|number|UI_Renderable_Interface $abstract
	 * @return UI_Themes_Theme_ContentRenderer
	 */
	public function setAbstract($abstract): UI_Themes_Theme_ContentRenderer
	{
		/* ... */
	}


	/**
	 * Sets a subtitle for the page, shown above the abstract and the subnavigation.
	 *
	 * @param string|number|UI_Renderable_Interface $subtitle
	 * @return UI_Themes_Theme_ContentRenderer
	 */
	public function setSubtitle($subtitle): UI_Themes_Theme_ContentRenderer
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface $content
	 * @return $this
	 */
	public function setContent($content): self
	{
		/* ... */
	}


	/**
	 * @param string|number|StringableInterface $content
	 * @return $this
	 */
	public function appendContent($content): self
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @param mixed $value
	 * @return $this
	 */
	public function setTemplateVar(string $name, $value): self
	{
		/* ... */
	}


	/**
	 * @param Application_Interfaces_Formable $formable
	 * @return $this
	 */
	public function appendFormable(Application_Interfaces_Formable $formable): self
	{
		/* ... */
	}


	/**
	 * @param UI_Form $form
	 * @return $this
	 * @throws UI_Themes_Exception
	 * @throws BaseClassHelperException
	 */
	public function appendForm(UI_Form $form): self
	{
		/* ... */
	}


	/**
	 * @param UI_DataGrid $grid
	 * @param array<int,array<string,mixed>|UI_DataGrid_Entry> $entries
	 * @return $this
	 *
	 * @throws Application_Exception
	 * @throws UI_Themes_Exception
	 * @throws BaseClassHelperException
	 */
	public function appendDataGrid(UI_DataGrid $grid, array $entries): self
	{
		/* ... */
	}


	/**
	 * @param string $templateIDOrClass
	 * @param array<string,mixed> $vars
	 * @return $this
	 *
	 * @throws UI_Themes_Exception
	 * @throws BaseClassHelperException
	 */
	public function appendTemplateClass(string $templateIDOrClass, array $vars = []): self
	{
		/* ... */
	}


	/**
	 * @param UI_Page_Template $template
	 * @return $this
	 */
	public function appendTemplate(UI_Page_Template $template): self
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}


	public function getAbstract(): string
	{
		/* ... */
	}


	public function getTitle(): UI_Page_Title
	{
		/* ... */
	}


	public function isWithSidebar(): bool
	{
		/* ... */
	}


	public function getSubtitle(): UI_Page_Subtitle
	{
		/* ... */
	}


	public function getContent(): string
	{
		/* ... */
	}


	public function hasTitle(): bool
	{
		/* ... */
	}


	public function hasSubtitle(): bool
	{
		/* ... */
	}


	public function hasAbstract(): bool
	{
		/* ... */
	}
}


```
---
**File Statistics**
- **Size**: 10.84 KB
- **Lines**: 677
File: `modules/ui/themes/architecture.md`
