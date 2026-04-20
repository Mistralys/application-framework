# UI Page - Architecture
_SOURCE: Public class signatures for page regions and helpers_
# Public class signatures for page regions and helpers
```
// Structure of documents
└── src/
    └── classes/
        └── UI/
            └── Page.php
            └── Page/
                └── Breadcrumb.php
                └── Breadcrumb/
                    ├── Item.php
                └── Footer.php
                └── Header.php
                └── Help.php
                └── Help/
                    ├── Item.php
                    ├── Item/
                    │   └── Header.php
                    │   └── Para.php
                    │   └── UnorderedListItem.php
                └── Navigation.php
                └── Navigation/
                    ├── Item.php
                    ├── Item/
                    │   ├── ClickableNavItem.php
                    │   ├── DropdownMenu.php
                    │   ├── ExternalLink.php
                    │   ├── HTML.php
                    │   ├── InternalLink.php
                    │   ├── Search.php
                    ├── LinkItemBase.php
                    ├── MetaNavigation.php
                    ├── MetaNavigation/
                    │   ├── DeveloperMenu.php
                    │   ├── UserMenu.php
                    ├── NavConfigurator.php
                    ├── NavConfigurator/
                    │   ├── MenuConfigurator.php
                    ├── QuickNavigation.php
                    ├── QuickNavigation/
                    │   ├── BaseQuickNavItem.php
                    │   ├── Items/
                    │   │   ├── ScreenNavItem.php
                    │   │   ├── URLNavItem.php
                    │   ├── ScreenItemsContainer.php
                    ├── TextLinksNavigation.php
                └── RevisionableTitle.php
                └── Section.php
                └── Section/
                    ├── Content.php
                    ├── Content/
                    │   ├── HTML.php
                    │   ├── Heading.php
                    │   ├── Separator.php
                    │   ├── Template.php
                    ├── GroupControls.php
                    ├── SectionsRegistry.php
                    ├── Type/
                    │   └── Default.php
                    │   └── Developer.php
                └── Sidebar.php
                └── Sidebar/
                    ├── Item.php
                    ├── Item/
                    │   ├── Button.php
                    │   ├── Custom.php
                    │   ├── DeveloperPanel.php
                    │   ├── DropdownButton.php
                    │   ├── FormTOC.php
                    │   ├── Message.php
                    │   ├── Separator.php
                    │   ├── Template.php
                    ├── LockableItem.php
                └── StepsNavigator.php
                └── StepsNavigator/
                    ├── Step.php
                └── Subtitle.php
                └── Template.php
                └── Template/
                    ├── Custom.php
                └── Title.php

```
###  Path: `/src/classes/UI/Page.php`

```php
namespace ;

use AppUtils\ClassHelper as ClassHelper;
use AppUtils\ClassHelper\ClassNotExistsException as ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException as ClassNotImplementsException;
use AppUtils\ConvertHelper as ConvertHelper;
use Application\Interfaces\Admin\AdminAreaInterface as AdminAreaInterface;
use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;
use Application\Revisionable\RevisionableInterface as RevisionableInterface;

/**
 * Page utility class that offers common functionality
 * for pages. Note that only the application driver
 * knows (or rater is supposed to know) which pages
 * exist in the application. A page itself does not know
 * anything beyond its own ID.
 *
 * A page by default comes with helper objects to handle
 * the typical parts of a page, namely a header, footer
 * and sidebar.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 *
 * @see template_default_frame
 */
class UI_Page extends UI_Renderable
{
	public const ERROR_UNKNOWN_NAVIGATION = 45001;
	public const ERROR_PAGE_TITLE_CONTAINS_HTML = 45002;

	/**
	 * @return Application_User
	 */
	public function getUser(): Application_User
	{
		/* ... */
	}


	/**
	 * Sets the document title shown in the browser's toolbar.
	 *
	 * NOTE: May not contain any HTML code.
	 *
	 * @param string|number|UI_Renderable_Interface $title
	 * @throws UI_Exception
	 * @return UI_Page
	 *
	 * @see UI_Page::ERROR_PAGE_TITLE_CONTAINS_HTML
	 */
	public function setTitle($title): UI_Page
	{
		/* ... */
	}


	/**
	 * Retrieves the current page title.
	 *
	 * @return string
	 *
	 * @see UI_Page::resolveTitle()
	 */
	public function getTitle(): string
	{
		/* ... */
	}


	public function getID(): string
	{
		/* ... */
	}


	/**
	 * @return UI_Page_Sidebar
	 */
	public function getSidebar(): UI_Page_Sidebar
	{
		/* ... */
	}


	/**
	 * @return UI_Page_Header
	 */
	public function getHeader(): UI_Page_Header
	{
		/* ... */
	}


	/**
	 * @return UI_Page_Footer
	 */
	public function getFooter(): UI_Page_Footer
	{
		/* ... */
	}


	/**
	 * Sets the HTML markup to use as content of the page.
	 * Note that this is set automatically by the application
	 * driver. Anything you set here manually gets replaced.
	 *
	 * @param string|int|float|UI_Renderable_Interface $content
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setContent($content): self
	{
		/* ... */
	}


	/**
	 * Selects the frame to use to render the page.
	 *
	 * @param string $frameName
	 * @return $this
	 */
	public function selectFrame(string $frameName): self
	{
		/* ... */
	}


	/**
	 * Creates the markup for an error message and returns the generated HTML code.
	 * Use the options array to set any desired options, see the {@link renderMessage()}
	 * method for a list of options.
	 *
	 * @param string $message
	 * @param array<string,mixed> $options
	 * @return string
	 */
	public function renderErrorMessage(string $message, array $options = []): string
	{
		/* ... */
	}


	/**
	 * Creates the markup for an informational message and returns the generated HTML code.
	 * Use the options array to set any desired options, see the {@link renderMessage()}
	 * method for a list of options.
	 *
	 * @param string $message
	 * @param array<string,mixed> $options
	 * @return string
	 */
	public function renderInfoMessage(string $message, array $options = []): string
	{
		/* ... */
	}


	/**
	 * Creates the markup for a success message and returns the generated HTML code.
	 * Use the options array to set any desired options, see the {@link renderMessage()}
	 * method for a list of options.
	 *
	 * @param string $message
	 * @param array<string,mixed> $options
	 * @return string
	 */
	public function renderSuccessMessage(string $message, array $options = []): string
	{
		/* ... */
	}


	/**
	 * Creates the markup for a warning message and returns the generated HTML code.
	 * Use the options array to set any desired options, see the {@link renderMessage()}
	 * method for a list of options.
	 *
	 * @param string $message
	 * @param array<string,mixed> $options
	 * @return string
	 */
	public function renderWarningMessage(string $message, array $options = []): string
	{
		/* ... */
	}


	/**
	 * Creates the markup for a message of the specified type and returns the
	 * generated HTML code. You may use the options array to configure the
	 * error message further.
	 *
	 * @param string $message
	 * @param string $type
	 * @param array<string,mixed> $options
	 * @return string
	 */
	public function renderMessage(string $message, string $type, array $options = []): string
	{
		/* ... */
	}


	/**
	 * Creates a navigation renderer helper class instance
	 * that can be used to create navigation menus. The
	 * navigation ID is used to identify the navigation as
	 * well as load the according template. The template must
	 * be called "navigation.myid.php".
	 *
	 * @param string $navigationID
	 * @return UI_Page_Navigation
	 */
	public function createNavigation(string $navigationID): UI_Page_Navigation
	{
		/* ... */
	}


	/**
	 * Checks whether a navigation with the specified ID
	 * exists.
	 *
	 * @param string $navigationID
	 * @return bool
	 */
	public function hasNavigation(string $navigationID): bool
	{
		/* ... */
	}


	public function hasSubnavigation(): bool
	{
		/* ... */
	}


	/**
	 * @param string $navigationID
	 * @return UI_Page_Navigation
	 * @throws UI_Exception
	 */
	public function getNavigation(string $navigationID): UI_Page_Navigation
	{
		/* ... */
	}


	public function getSubnavigation(): UI_Page_Navigation
	{
		/* ... */
	}


	/**
	 * Creates a breadcrumb navigation helper class instance that
	 * can be used to build a breadcrumb navigation. The id is
	 * used to identify the breadcrumb as well as to load the
	 * according template. The template must be called
	 * "navigation.breadcrumb.myid.php".
	 *
	 * @param string $breadcrumbID
	 * @return UI_Page_Breadcrumb
	 */
	public function createBreadcrumb(string $breadcrumbID): UI_Page_Breadcrumb
	{
		/* ... */
	}


	/**
	 * Retrieve a permalink to the page with additional
	 * optional request parameters. This is the raw
	 * link to the page (without additional page-specific
	 * request parameters). If you need a permalink, use
	 * the {@see UI_Page::getPermalink()} method.
	 *
	 * @param array $params
	 * @return string
	 * @see UI_Page::getPermalink()
	 */
	public function getURL(array $params = []): string
	{
		/* ... */
	}


	/**
	 * Retrieves a list of parameters specific for this page,
	 * dispatches this to the application driver (the driver
	 * handles this kind of information). Returns an associative
	 * array with param name => param value pairs.
	 *
	 * @return array
	 */
	public function getPageParams(): array
	{
		/* ... */
	}


	/**
	 * Retrieves a permalink to the page with all page-specific
	 * request parameters
	 */
	public function getPermalink(): string
	{
		/* ... */
	}


	/**
	 * Adds output to the console output, which is displayed
	 * for developer users.
	 *
	 * @param string $markup
	 * @return $this
	 */
	public function addConsoleOutput(string $markup): self
	{
		/* ... */
	}


	/**
	 * Checks whether there is any console output to display.
	 *
	 * @return boolean
	 */
	public function hasConsoleOutput(): bool
	{
		/* ... */
	}


	/**
	 * Returns the markup to show in the console.
	 *
	 * @return string
	 */
	public function getConsoleOutput(): string
	{
		/* ... */
	}


	/**
	 * @return UI_Page_Breadcrumb
	 */
	public function getBreadcrumb(): UI_Page_Breadcrumb
	{
		/* ... */
	}


	/**
	 * Creates a new page section object that can be used to
	 * configure a section further than the template's renderSection
	 * method allows.
	 *
	 * @param string $type The section type to create. This is one of the types from the UI/Page/Section subfolder (case sensitive).
	 * @return UI_Page_Section
	 */
	public function createSection(string $type = ''): UI_Page_Section
	{
		/* ... */
	}


	/**
	 * Creates a step navigation helper class instance, which can
	 * be used to render a navigation with incremental steps
	 * like in a wizard or order process.
	 *
	 * @return UI_Page_StepsNavigator
	 */
	public function createStepsNavigator(): UI_Page_StepsNavigator
	{
		/* ... */
	}


	public function addQuickSelector(string $selectorID): UI_QuickSelector
	{
		/* ... */
	}


	public function getQuickSelector($selectorID): ?UI_QuickSelector
	{
		/* ... */
	}


	public function hasQuickSelector(string $selectorID): bool
	{
		/* ... */
	}


	/**
	 * Creates the helper class that can be used to render a
	 * page title for a revisionable instance. It gathers information
	 * intelligently, for example adding a state badge if the
	 * revisionable supports states.
	 *
	 * @param RevisionableInterface $revisionable
	 * @return UI_Page_RevisionableTitle
	 */
	public function createRevisionableTitle(RevisionableInterface $revisionable): UI_Page_RevisionableTitle
	{
		/* ... */
	}


	/**
	 * Creates a new page sidebar section object that can be used to
	 * configure a section further than the template's renderSection
	 * method allows.
	 *
	 * @param string $type The section type to create. This is one of the types from the UI/Page/Section subfolder (case sensitive).
	 * @return UI_Page_Section
	 */
	public function createSidebarSection(string $type = ''): UI_Page_Section
	{
		/* ... */
	}


	/**
	 * Creates a new subsection, a section that is meant to be used
	 * within another section. This is not compatible with sidebar
	 * sections.
	 *
	 * @param string $type
	 * @return UI_Page_Section
	 */
	public function createSubsection(string $type = ''): UI_Page_Section
	{
		/* ... */
	}


	/**
	 * Creates a developer panel sidebar section instance.
	 *
	 * @return UI_Page_Section_Type_Developer
	 * @throws ClassNotExistsException
	 * @throws ClassNotImplementsException
	 */
	public function createDeveloperPanel(): UI_Page_Section_Type_Developer
	{
		/* ... */
	}


	/**
	 * Creates a new help instance, which is used to format
	 * and manage help texts.
	 *
	 * @return UI_Page_Help
	 */
	public function createHelp(): UI_Page_Help
	{
		/* ... */
	}


	/**
	 * @return AdminAreaInterface
	 */
	public function getActiveArea(): AdminAreaInterface
	{
		/* ... */
	}


	/**
	 * Retrieves the currently active administration screen.
	 *
	 * @return AdminScreenInterface|NULL
	 * @throws Application_Exception
	 */
	public function getActiveScreen(): ?AdminScreenInterface
	{
		/* ... */
	}


	/**
	 * Retrieves the lock manager instance used in the current
	 * administration screen, if any.
	 *
	 * @return Application_LockManager|NULL
	 * @throws Application_Exception
	 */
	public function getLockManager(): ?Application_LockManager
	{
		/* ... */
	}


	public function renderMessages(): string
	{
		/* ... */
	}


	public function renderMaintenance(): string
	{
		/* ... */
	}


	public function renderConsole(): string
	{
		/* ... */
	}


	/**
	 * Checks whether the page has a context menu. This is
	 * only possible when the page has a subnavigation, as
	 * the context menu is integrated there.
	 *
	 * @return boolean
	 */
	public function hasContextMenu(): bool
	{
		/* ... */
	}


	/**
	 * Retrieves the page's subnavigation context menu.
	 *
	 * NOTE: Check if it is available first.
	 *
	 * @return UI_Bootstrap_DropdownMenu
	 */
	public function getContextMenu(): UI_Bootstrap_DropdownMenu
	{
		/* ... */
	}


	/**
	 * Resolves the actual page title to use in the
	 * document: this is the specified page title with
	 * the application name appended.
	 *
	 * @return string
	 */
	public function resolveTitle(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Breadcrumb.php`

```php
namespace ;

use Application\Interfaces\Admin\AdminAreaInterface as AdminAreaInterface;

class UI_Page_Breadcrumb implements UI_Renderable_Interface
{
	use UI_Traits_RenderableGeneric;

	public function getUI(): UI
	{
		/* ... */
	}


	/**
	 * Appends an item to the breadcrumb navigation. Returns the
	 * item instance, use this to configure where it should be
	 * linked.
	 *
	 * @param string|number|UI_Renderable_Interface|NULL $label
	 * @return UI_Page_Breadcrumb_Item
	 * @throws UI_Exception
	 */
	public function appendItem($label): UI_Page_Breadcrumb_Item
	{
		/* ... */
	}


	/**
	 * Appends a breadcrumb item for the specified administration
	 * area instance. The optional request parameters are added to
	 * the generated URL.
	 *
	 * @param AdminAreaInterface $area
	 * @param array<string,mixed> $params
	 * @return UI_Page_Breadcrumb_Item
	 * @throws UI_Exception
	 */
	public function appendArea(AdminAreaInterface $area, array $params = []): UI_Page_Breadcrumb_Item
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}


	/**
	 * Retrieves an indexed array with all elements in the
	 * breadcrumb, from first to last.
	 *
	 * @return UI_Page_Breadcrumb_Item[]
	 */
	public function getItems(): array
	{
		/* ... */
	}


	public function display(): void
	{
		/* ... */
	}


	public function getPage(): UI_Page
	{
		/* ... */
	}


	/**
	 * Retrieves the last item in the breadcrumb, or null
	 * if there are no items in the breadcrumb.
	 *
	 * @return UI_Page_Breadcrumb_Item|NULL
	 */
	public function getLastItem(): ?UI_Page_Breadcrumb_Item
	{
		/* ... */
	}


	public function clearItems(): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Breadcrumb/Item.php`

```php
namespace ;

use AppUtils\ClassHelper\ClassNotExistsException as ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException as ClassNotImplementsException;
use Application\Interfaces\Admin\AdminAreaInterface as AdminAreaInterface;
use Application\Interfaces\Admin\AdminModeInterface as AdminModeInterface;
use Application\Interfaces\Admin\AdminSubmodeInterface as AdminSubmodeInterface;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

class UI_Page_Breadcrumb_Item implements UI_Renderable_Interface
{
	use UI_Traits_RenderableGeneric;

	public function getUI(): UI
	{
		/* ... */
	}


	/**
	 * Makes the item link to the specified URL.
	 *
	 * @param string|AdminURLInterface|array<string,mixed> $urlOrParams
	 * @return $this
	 *
	 * @throws ClassNotExistsException
	 * @throws ClassNotImplementsException
	 */
	public function makeLinked($urlOrParams): self
	{
		/* ... */
	}


	/**
	 * Turns the item into a javascript click link, which will
	 * execute the specified javascript code when clicked.
	 *
	 * @param string|UI_Renderable_Interface $javascript
	 * @return $this
	 * @throws UI_Exception
	 */
	public function makeClickable($javascript): self
	{
		/* ... */
	}


	/**
	 * Makes the item link to the specified administration area.
	 *
	 * @param AdminAreaInterface $area
	 * @param array<string,mixed> $params
	 * @return $this
	 * @throws ClassNotExistsException
	 * @throws ClassNotImplementsException
	 */
	public function makeLinkedFromArea(AdminAreaInterface $area, array $params = []): self
	{
		/* ... */
	}


	/**
	 * Makes the item link to the specified administration mode.
	 *
	 * @param AdminModeInterface $mode
	 * @param array<string,mixed> $params
	 * @return $this
	 *
	 * @throws ClassNotExistsException
	 * @throws ClassNotImplementsException
	 */
	public function makeLinkedFromMode(AdminModeInterface $mode, array $params = []): self
	{
		/* ... */
	}


	/**
	 * Makes the item link to the specified administration submode.
	 *
	 * @param AdminSubmodeInterface $submode
	 * @param array<string,mixed> $params
	 * @return $this
	 */
	public function makeLinkedFromSubmode(AdminSubmodeInterface $submode, array $params = []): self
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	public function getURL(): string
	{
		/* ... */
	}


	public function getJavascript(): string
	{
		/* ... */
	}


	public function getMode(): string
	{
		/* ... */
	}


	public function isLinked(): bool
	{
		/* ... */
	}


	public function isClickable(): bool
	{
		/* ... */
	}


	public function reset(): void
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function setFirst(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function setLast(): self
	{
		/* ... */
	}


	public function isFirst(): bool
	{
		/* ... */
	}


	public function isLast(): bool
	{
		/* ... */
	}


	/**
	 * @param array<string,mixed> $params
	 * @return $this
	 *
	 * @throws ClassNotExistsException
	 * @throws ClassNotImplementsException
	 */
	public function makeLinkedRefresh(array $params = []): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Breadcrumb/Item.php`

```php
namespace ;

use AppUtils\ClassHelper\ClassNotExistsException as ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException as ClassNotImplementsException;
use Application\Interfaces\Admin\AdminAreaInterface as AdminAreaInterface;
use Application\Interfaces\Admin\AdminModeInterface as AdminModeInterface;
use Application\Interfaces\Admin\AdminSubmodeInterface as AdminSubmodeInterface;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

class UI_Page_Breadcrumb_Item implements UI_Renderable_Interface
{
	use UI_Traits_RenderableGeneric;

	public function getUI(): UI
	{
		/* ... */
	}


	/**
	 * Makes the item link to the specified URL.
	 *
	 * @param string|AdminURLInterface|array<string,mixed> $urlOrParams
	 * @return $this
	 *
	 * @throws ClassNotExistsException
	 * @throws ClassNotImplementsException
	 */
	public function makeLinked($urlOrParams): self
	{
		/* ... */
	}


	/**
	 * Turns the item into a javascript click link, which will
	 * execute the specified javascript code when clicked.
	 *
	 * @param string|UI_Renderable_Interface $javascript
	 * @return $this
	 * @throws UI_Exception
	 */
	public function makeClickable($javascript): self
	{
		/* ... */
	}


	/**
	 * Makes the item link to the specified administration area.
	 *
	 * @param AdminAreaInterface $area
	 * @param array<string,mixed> $params
	 * @return $this
	 * @throws ClassNotExistsException
	 * @throws ClassNotImplementsException
	 */
	public function makeLinkedFromArea(AdminAreaInterface $area, array $params = []): self
	{
		/* ... */
	}


	/**
	 * Makes the item link to the specified administration mode.
	 *
	 * @param AdminModeInterface $mode
	 * @param array<string,mixed> $params
	 * @return $this
	 *
	 * @throws ClassNotExistsException
	 * @throws ClassNotImplementsException
	 */
	public function makeLinkedFromMode(AdminModeInterface $mode, array $params = []): self
	{
		/* ... */
	}


	/**
	 * Makes the item link to the specified administration submode.
	 *
	 * @param AdminSubmodeInterface $submode
	 * @param array<string,mixed> $params
	 * @return $this
	 */
	public function makeLinkedFromSubmode(AdminSubmodeInterface $submode, array $params = []): self
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	public function getURL(): string
	{
		/* ... */
	}


	public function getJavascript(): string
	{
		/* ... */
	}


	public function getMode(): string
	{
		/* ... */
	}


	public function isLinked(): bool
	{
		/* ... */
	}


	public function isClickable(): bool
	{
		/* ... */
	}


	public function reset(): void
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function setFirst(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function setLast(): self
	{
		/* ... */
	}


	public function isFirst(): bool
	{
		/* ... */
	}


	public function isLast(): bool
	{
		/* ... */
	}


	/**
	 * @param array<string,mixed> $params
	 * @return $this
	 *
	 * @throws ClassNotExistsException
	 * @throws ClassNotImplementsException
	 */
	public function makeLinkedRefresh(array $params = []): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Footer.php`

```php
namespace ;

/**
 * UI rendering class used to handle the footer of the layout.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 * @link http://www.mistralys.com
 */
class UI_Page_Footer
{
	public function render()
	{
		/* ... */
	}


	public function display()
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Header.php`

```php
namespace ;

class UI_Page_Header implements UI_Renderable_Interface
{
	use UI_Traits_RenderableGeneric;

	public const ERROR_NAVIGATION_ALREADY_EXISTS = 108901;
	public const NAVIGATION_ID_MAIN = 'main';

	public function getPage(): UI_Page
	{
		/* ... */
	}


	public function addMainNavigation(): UI_Page_Navigation
	{
		/* ... */
	}


	/**
	 * @param string $navigationID
	 * @return UI_Page_Navigation
	 * @throws UI_Exception
	 */
	public function addNavigation(string $navigationID): UI_Page_Navigation
	{
		/* ... */
	}


	public function addNavigationInstance(UI_Page_Navigation $nav): self
	{
		/* ... */
	}


	/**
	 * Retrieves a navigation object by its ID
	 *
	 * @param string $navigationID
	 * @return UI_Page_Navigation|NULL
	 */
	public function getNavigation(string $navigationID): ?UI_Page_Navigation
	{
		/* ... */
	}


	public function renderNavigation(string $navigationID): string
	{
		/* ... */
	}


	/**
	 * Renders the header using the corresponding template and
	 * returns the generated HTML code.
	 *
	 * @return string
	 * @throws UI_Themes_Exception
	 */
	public function render(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Help.php`

```php
namespace ;

use AppUtils\ClassHelper as ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException as BaseClassHelperException;
use AppUtils\Interfaces\StringableInterface as StringableInterface;
use UI\Page\Help\Item\UnorderedListItem as UnorderedListItem;

/**
 * Handles the inline page help interface, which is used to
 * add documentation relevant to the current page. It is accessed
 * in the administration screens via the `_handleHelp()` method.
 *
 * @package Application
 * @subpackage User Interface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Traits_Admin_Screen::_handleHelp()
 * @see template_default_frame_page_help
 */
class UI_Page_Help extends UI_Renderable
{
	public const ERROR_CANNOT_FIND_HELPER_CLASS = 132701;
	public const ERROR_INVALID_HELPER_INSTANCE_CREATED = 132702;

	/**
	 * Adds a paragraph of text.
	 *
	 * @param string|number|UI_Renderable_Interface $text
	 * @return UI_Page_Help_Item_Para
	 * @throws UI_Exception
	 */
	public function addPara($text): UI_Page_Help_Item_Para
	{
		/* ... */
	}


	/**
	 * Adds a subheader in the help screen.
	 *
	 * @param string|number|UI_Renderable_Interface $text
	 * @return UI_Page_Help_Item_Header
	 * @throws UI_Exception
	 */
	public function addHeader($text): UI_Page_Help_Item_Header
	{
		/* ... */
	}


	/**
	 * @param array<int,string|int|float|StringableInterface|NULL>|string|int|float|StringableInterface|NULL ...$items
	 * @return UnorderedListItem
	 * @throws UI_Exception
	 */
	public function addUnorderedList(...$items): UnorderedListItem
	{
		/* ... */
	}


	/**
	 * Sets the summary for this help content, which can
	 * be shown before the whole help is shown.
	 *
	 * @param string|number|UI_Renderable_Interface $text
	 * @return UI_Page_Help
	 */
	public function setSummary($text): UI_Page_Help
	{
		/* ... */
	}


	public function hasItems(): bool
	{
		/* ... */
	}


	/**
	 * @return UI_Page_Help_Item[]
	 */
	public function getItems(): array
	{
		/* ... */
	}


	public function hasSummary(): bool
	{
		/* ... */
	}


	public function getSummary(): string
	{
		/* ... */
	}


	public function setTemplate(string $id): UI_Page_Help
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Help/Item.php`

```php
namespace ;

use AppUtils\Interfaces\ClassableInterface as ClassableInterface;
use AppUtils\Interfaces\OptionableInterface as OptionableInterface;
use AppUtils\Traits\ClassableTrait as ClassableTrait;
use AppUtils\Traits\OptionableTrait as OptionableTrait;

abstract class UI_Page_Help_Item extends UI_Renderable implements OptionableInterface, ClassableInterface
{
	use OptionableTrait;
	use ClassableTrait;
}


```
###  Path: `/src/classes/UI/Page/Help/Item.php`

```php
namespace ;

use AppUtils\Interfaces\ClassableInterface as ClassableInterface;
use AppUtils\Interfaces\OptionableInterface as OptionableInterface;
use AppUtils\Traits\ClassableTrait as ClassableTrait;
use AppUtils\Traits\OptionableTrait as OptionableTrait;

abstract class UI_Page_Help_Item extends UI_Renderable implements OptionableInterface, ClassableInterface
{
	use OptionableTrait;
	use ClassableTrait;
}


```
###  Path: `/src/classes/UI/Page/Help/Item/Header.php`

```php
namespace ;

class UI_Page_Help_Item_Header extends UI_Page_Help_Item
{
	public function getDefaultOptions(): array
	{
		/* ... */
	}


	/**
	 * Sets and replaces the header's text.
	 *
	 * @param string|number|UI_Renderable_Interface $text
	 * @return UI_Page_Help_Item_Header
	 */
	public function setText($text)
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Help/Item/Header.php`

```php
namespace ;

class UI_Page_Help_Item_Header extends UI_Page_Help_Item
{
	public function getDefaultOptions(): array
	{
		/* ... */
	}


	/**
	 * Sets and replaces the header's text.
	 *
	 * @param string|number|UI_Renderable_Interface $text
	 * @return UI_Page_Help_Item_Header
	 */
	public function setText($text)
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Help/Item/Para.php`

```php
namespace ;

class UI_Page_Help_Item_Para extends UI_Page_Help_Item implements Application_Interfaces_Iconizable
{
	use Application_Traits_Iconizable;

	public function getDefaultOptions(): array
	{
		/* ... */
	}


	/**
	 * Sets and replaces the paragraph's text.
	 *
	 * @param string|number|UI_Renderable_Interface $text
	 * @return UI_Page_Help_Item_Para
	 */
	public function setText($text)
	{
		/* ... */
	}


	public function makeHint(): UI_Page_Help_Item_Para
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Help/Item/Para.php`

```php
namespace ;

class UI_Page_Help_Item_Para extends UI_Page_Help_Item implements Application_Interfaces_Iconizable
{
	use Application_Traits_Iconizable;

	public function getDefaultOptions(): array
	{
		/* ... */
	}


	/**
	 * Sets and replaces the paragraph's text.
	 *
	 * @param string|number|UI_Renderable_Interface $text
	 * @return UI_Page_Help_Item_Para
	 */
	public function setText($text)
	{
		/* ... */
	}


	public function makeHint(): UI_Page_Help_Item_Para
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Help/Item/UnorderedListItem.php`

```php
namespace UI\Page\Help\Item;

use AppUtils\Interfaces\StringableInterface as StringableInterface;
use UI_Exception as UI_Exception;
use UI_Page_Help_Item as UI_Page_Help_Item;

class UnorderedListItem extends UI_Page_Help_Item
{
	/**
	 * @param string|int|float|StringableInterface|NULL $item
	 * @return $this
	 * @throws UI_Exception
	 */
	public function addItem($item): self
	{
		/* ... */
	}


	/**
	 * @param array<int,string|int|float|StringableInterface|NULL>|string|int|float|StringableInterface|NULL ...$items
	 * @return $this
	 * @throws UI_Exception
	 */
	public function addItems(...$items): self
	{
		/* ... */
	}


	public function getDefaultOptions(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Help/Item/UnorderedListItem.php`

```php
namespace UI\Page\Help\Item;

use AppUtils\Interfaces\StringableInterface as StringableInterface;
use UI_Exception as UI_Exception;
use UI_Page_Help_Item as UI_Page_Help_Item;

class UnorderedListItem extends UI_Page_Help_Item
{
	/**
	 * @param string|int|float|StringableInterface|NULL $item
	 * @return $this
	 * @throws UI_Exception
	 */
	public function addItem($item): self
	{
		/* ... */
	}


	/**
	 * @param array<int,string|int|float|StringableInterface|NULL>|string|int|float|StringableInterface|NULL ...$items
	 * @return $this
	 * @throws UI_Exception
	 */
	public function addItems(...$items): self
	{
		/* ... */
	}


	public function getDefaultOptions(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation.php`

```php
namespace ;

use AppUtils\Interfaces\ClassableInterface as ClassableInterface;
use AppUtils\Traits\ClassableTrait as ClassableTrait;
use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

/**
 * Navigation handling class: used for the main navigation
 * and subnavigation. Can be used for other navigations as well.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 */
class UI_Page_Navigation extends UI_Renderable implements ClassableInterface
{
	use ClassableTrait;

	public function getTemplateID(): string
	{
		/* ... */
	}


	public static function create(string $id, ?UI_Page $page = null): UI_Page_Navigation
	{
		/* ... */
	}


	/**
	 * @return string
	 */
	public function getID(): string
	{
		/* ... */
	}


	/**
	 * Whether there are any items in the navigation.
	 *
	 * NOTE: Does NOT check whether the items are valid.
	 * Use {@see UI_Page_Navigation::hasValidItems()}
	 * instead if this is relevant.
	 *
	 * @return bool
	 */
	public function hasItems(): bool
	{
		/* ... */
	}


	/**
	 * Checks whether the navigation has any items that
	 * are valid to be displayed (that fulfill all conditions
	 * that may have been defined for them, see {@see UI_Interfaces_Conditional}).
	 *
	 * @return bool
	 */
	public function hasValidItems(): bool
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface|NULL $title
	 * @param string $jsStatement
	 * @return UI_Page_Navigation_Item_Clickable
	 * @throws Application_Exception
	 */
	public function addClickable($title, string $jsStatement): UI_Page_Navigation_Item_Clickable
	{
		/* ... */
	}


	/**
	 * @param string|class-string<UI_Page_Template> $templateID
	 * @return $this
	 */
	public function setTemplateID(string $templateID): self
	{
		/* ... */
	}


	/**
	 * Returns an indexed array with navigation items.
	 *
	 * @return UI_Page_Navigation_Item[]
	 */
	public function getItems(): array
	{
		/* ... */
	}


	/**
	 * Retrieves all navigation items from the specified group.
	 * Returns an indexed array with navigation objects.
	 *
	 * @param string $groupName
	 * @return UI_Page_Navigation_Item[]
	 */
	public function getItemsByGroup(string $groupName): array
	{
		/* ... */
	}


	public function isGroupActive(string $group): bool
	{
		/* ... */
	}


	public function getActiveGroupItem(string $group): ?UI_Page_Navigation_Item
	{
		/* ... */
	}


	/**
	 * Adds a link to an internal page. Returns the new
	 * navigation link item object.
	 *
	 * @param string $targetPageID
	 * @param string|number|UI_Renderable_Interface|NULL $title
	 * @param array<string,string> $params
	 * @return UI_Page_Navigation_Item_InternalLink
	 *
	 * @throws Application_Exception
	 * @throws UI_Exception
	 */
	public function addInternalLink(
		string $targetPageID,
		$title,
		array $params = [],
	): UI_Page_Navigation_Item_InternalLink
	{
		/* ... */
	}


	/**
	 * @param string $title
	 * @param string|AdminURLInterface $url
	 * @return UI_Page_Navigation_Item_ExternalLink
	 * @throws Application_Exception
	 * @throws UI_Exception
	 */
	public function addExternalLink(string $title, $url): UI_Page_Navigation_Item_ExternalLink
	{
		/* ... */
	}


	public function clearItems(): UI_Page_Navigation
	{
		/* ... */
	}


	/**
	 * Adds a subnavigation link that automatically adds
	 * the page variables to the parameters (mode, submode, etc.).
	 *
	 * @param string|number|UI_Renderable_Interface|NULL $title
	 * @param array<string,string>|AdminURLInterface|string $paramsOrURL If a URL is given, it is parsed to extract the query parameters.
	 * @return UI_Page_Navigation_Item_InternalLink
	 */
	public function addSubnavLink($title, $paramsOrURL = []): UI_Page_Navigation_Item_InternalLink
	{
		/* ... */
	}


	/**
	 * Adds a URL, which will be parsed automatically to add
	 * its parameters.
	 *
	 * @param string|number|UI_Renderable_Interface|NULL $title
	 * @param string|AdminURLInterface $url
	 * @return UI_Page_Navigation_Item_InternalLink|UI_Page_Navigation_Item_ExternalLink
	 */
	public function addURL($title, $url): UI_Page_Navigation_Item
	{
		/* ... */
	}


	/**
	 * Adds a search box to the navigation. Use the returned
	 * object to configure the widget further.
	 *
	 * The callback gets the following parameters:
	 *
	 * 1. The search item instance (@see UI_Page_Navigation_Item_Search)
	 * 2. The search term string
	 * 3. The scope string (if applicable)
	 * 4. The country name (if applicable)
	 *
	 * @param callable $callback
	 * @return UI_Page_Navigation_Item_Search
	 * @throws Application_Exception
	 */
	public function addSearch(callable $callback): UI_Page_Navigation_Item_Search
	{
		/* ... */
	}


	/**
	 * Adds a dropdown menu item.
	 * @param string|UI_Renderable_Interface|int|float $label
	 * @return UI_Page_Navigation_Item_DropdownMenu
	 * @throws Application_Exception
	 */
	public function addDropdownMenu($label): UI_Page_Navigation_Item_DropdownMenu
	{
		/* ... */
	}


	/**
	 * Adds an item with custom HTML code.
	 * @param string|number|UI_Renderable_Interface|NULL $html
	 * @return UI_Page_Navigation_Item_HTML
	 */
	public function addHTML($html): UI_Page_Navigation_Item_HTML
	{
		/* ... */
	}


	/**
	 * Force any navigation item active.
	 * @param UI_Page_Navigation_Item $item
	 * @return UI_Page_Navigation
	 */
	public function forceActiveItem(UI_Page_Navigation_Item $item): self
	{
		/* ... */
	}


	public function getForcedActiveItem(): ?UI_Page_Navigation_Item
	{
		/* ... */
	}


	/**
	 * Retrieves a navigation item by its alias (if it has any).
	 * @param string $alias
	 * @return UI_Page_Navigation_Item|NULL
	 */
	public function getItemByAlias(string $alias): ?UI_Page_Navigation_Item
	{
		/* ... */
	}


	/**
	 * Checks whether a context menu has been set for the navigation.
	 * @return boolean
	 */
	public function hasContextMenu(): bool
	{
		/* ... */
	}


	/**
	 * Retrieves the context menu to show within the navigation.
	 * It is created if it does not exist yet.
	 *
	 * @return UI_Bootstrap_DropdownMenu
	 */
	public function getContextMenu(): UI_Bootstrap_DropdownMenu
	{
		/* ... */
	}


	/**
	 * Called when the initialization of the navigation is complete.
	 * This is done automatically by the admin screen.
	 */
	public function initDone(): void
	{
		/* ... */
	}


	/**
	 * Allows appending HTML right after the HTML code of the navigation.
	 *
	 * @param string $html
	 * @return UI_Page_Navigation
	 */
	public function appendHTML(string $html): UI_Page_Navigation
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation/Item.php`

```php
namespace ;

use AppUtils\Interfaces\ClassableInterface as ClassableInterface;
use AppUtils\Traits\ClassableTrait as ClassableTrait;
use UI\Interfaces\TooltipableInterface as TooltipableInterface;
use UI\Traits\TooltipableTrait as TooltipableTrait;

/**
 * Base class for navigation items which should be extended
 * to create new specialized navigation items.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class UI_Page_Navigation_Item implements Application_Interfaces_Iconizable, ClassableInterface, UI_Interfaces_Conditional, Application_Interfaces_Loggable, TooltipableInterface
{
	use Application_Traits_Iconizable;
	use ClassableTrait;
	use UI_Traits_Conditional;
	use Application_Traits_Loggable;
	use TooltipableTrait;
	use UI_Traits_RenderableGeneric;

	public const ITEM_POSITION_INLINE = 'inline';
	public const ITEM_POSITION_BELOW = 'below';

	/**
	 * @param string|number|UI_Renderable_Interface|NULL $title
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setTitle($title): self
	{
		/* ... */
	}


	public function getUI(): UI
	{
		/* ... */
	}


	public function getID(): string
	{
		/* ... */
	}


	public function getTitle(): string
	{
		/* ... */
	}


	public function getAlias(): string
	{
		/* ... */
	}


	/**
	 * Retrieves the positioning of the item. Some items may
	 * be positioned directly below the navigation, while the
	 * default is within the navigation.
	 *
	 * @return string
	 *
	 * @see UI_Page_Navigation_Item::ITEM_POSITION_BELOW
	 * @see UI_Page_Navigation_Item::ITEM_POSITION_INLINE
	 */
	public function getPosition(): string
	{
		/* ... */
	}


	/**
	 * Whether the item is placed below the navigation.
	 * @return bool
	 */
	public function isPositionBelow(): bool
	{
		/* ... */
	}


	/**
	 * Adds a class that will be added to the navigation item's container element,
	 * typically the <li> element in a list.
	 *
	 * @param string $class
	 * @return $this
	 */
	public function addContainerClass(string $class): self
	{
		/* ... */
	}


	/**
	 * @return string[]
	 */
	public function getContainerClasses(): array
	{
		/* ... */
	}


	abstract public function getType(): string;


	/**
	 * @param array<string,string> $attributes
	 * @return string
	 */
	abstract public function render(array $attributes = []): string;


	/**
	 * Checks whether the current navigation item is
	 * the active navigation item.
	 *
	 * Note: this is not detected automatically, your
	 * driver has to specify this manually as there
	 * is no way for navigation items to know this
	 * for themselves (unless you extend an existing
	 * navigation item and add this functionality for
	 * your application).
	 *
	 * @return boolean
	 * @see setActive()
	 */
	public function isActive(): bool
	{
		/* ... */
	}


	/**
	 * Sets the current navigation item to the specified
	 * active state, or to active if not specified.
	 *
	 * @param bool $active
	 * @see isActive()
	 * @return $this
	 */
	public function setActive(bool $active = true): self
	{
		/* ... */
	}


	/**
	 * Sets the group for the navigation element: grouped elements
	 * are displayed as a submenu with items, the title being the
	 * label of the menu.
	 *
	 * @param string $title
	 * @return $this
	 */
	public function setGroup(string $title): self
	{
		/* ... */
	}


	/**
	 * The title of the group the navigation element should be filed under.
	 *
	 * @return string
	 */
	public function getGroup(): string
	{
		/* ... */
	}


	/**
	 * Sets an alias for the item, so it can easily be accessed later
	 * using the navigation's {@see UI_Page_Navigation::getItemByAlias()}
	 * method.
	 *
	 * @param string $alias
	 * @return $this
	 * @see UI_Page_Navigation::getItemByAlias()
	 */
	public function setAlias(string $alias): self
	{
		/* ... */
	}


	public function initDone(): void
	{
		/* ... */
	}


	public function getLogIdentifier(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation/Item.php`

```php
namespace ;

use AppUtils\Interfaces\ClassableInterface as ClassableInterface;
use AppUtils\Traits\ClassableTrait as ClassableTrait;
use UI\Interfaces\TooltipableInterface as TooltipableInterface;
use UI\Traits\TooltipableTrait as TooltipableTrait;

/**
 * Base class for navigation items which should be extended
 * to create new specialized navigation items.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class UI_Page_Navigation_Item implements Application_Interfaces_Iconizable, ClassableInterface, UI_Interfaces_Conditional, Application_Interfaces_Loggable, TooltipableInterface
{
	use Application_Traits_Iconizable;
	use ClassableTrait;
	use UI_Traits_Conditional;
	use Application_Traits_Loggable;
	use TooltipableTrait;
	use UI_Traits_RenderableGeneric;

	public const ITEM_POSITION_INLINE = 'inline';
	public const ITEM_POSITION_BELOW = 'below';

	/**
	 * @param string|number|UI_Renderable_Interface|NULL $title
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setTitle($title): self
	{
		/* ... */
	}


	public function getUI(): UI
	{
		/* ... */
	}


	public function getID(): string
	{
		/* ... */
	}


	public function getTitle(): string
	{
		/* ... */
	}


	public function getAlias(): string
	{
		/* ... */
	}


	/**
	 * Retrieves the positioning of the item. Some items may
	 * be positioned directly below the navigation, while the
	 * default is within the navigation.
	 *
	 * @return string
	 *
	 * @see UI_Page_Navigation_Item::ITEM_POSITION_BELOW
	 * @see UI_Page_Navigation_Item::ITEM_POSITION_INLINE
	 */
	public function getPosition(): string
	{
		/* ... */
	}


	/**
	 * Whether the item is placed below the navigation.
	 * @return bool
	 */
	public function isPositionBelow(): bool
	{
		/* ... */
	}


	/**
	 * Adds a class that will be added to the navigation item's container element,
	 * typically the <li> element in a list.
	 *
	 * @param string $class
	 * @return $this
	 */
	public function addContainerClass(string $class): self
	{
		/* ... */
	}


	/**
	 * @return string[]
	 */
	public function getContainerClasses(): array
	{
		/* ... */
	}


	abstract public function getType(): string;


	/**
	 * @param array<string,string> $attributes
	 * @return string
	 */
	abstract public function render(array $attributes = []): string;


	/**
	 * Checks whether the current navigation item is
	 * the active navigation item.
	 *
	 * Note: this is not detected automatically, your
	 * driver has to specify this manually as there
	 * is no way for navigation items to know this
	 * for themselves (unless you extend an existing
	 * navigation item and add this functionality for
	 * your application).
	 *
	 * @return boolean
	 * @see setActive()
	 */
	public function isActive(): bool
	{
		/* ... */
	}


	/**
	 * Sets the current navigation item to the specified
	 * active state, or to active if not specified.
	 *
	 * @param bool $active
	 * @see isActive()
	 * @return $this
	 */
	public function setActive(bool $active = true): self
	{
		/* ... */
	}


	/**
	 * Sets the group for the navigation element: grouped elements
	 * are displayed as a submenu with items, the title being the
	 * label of the menu.
	 *
	 * @param string $title
	 * @return $this
	 */
	public function setGroup(string $title): self
	{
		/* ... */
	}


	/**
	 * The title of the group the navigation element should be filed under.
	 *
	 * @return string
	 */
	public function getGroup(): string
	{
		/* ... */
	}


	/**
	 * Sets an alias for the item, so it can easily be accessed later
	 * using the navigation's {@see UI_Page_Navigation::getItemByAlias()}
	 * method.
	 *
	 * @param string $alias
	 * @return $this
	 * @see UI_Page_Navigation::getItemByAlias()
	 */
	public function setAlias(string $alias): self
	{
		/* ... */
	}


	public function initDone(): void
	{
		/* ... */
	}


	public function getLogIdentifier(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation/Item/ClickableNavItem.php`

```php
namespace ;

use AppUtils\AttributeCollection as AttributeCollection;

class UI_Page_Navigation_Item_Clickable extends UI_Page_Navigation_Item
{
	public function setJSStatement(string $statement): self
	{
		/* ... */
	}


	public function getType(): string
	{
		/* ... */
	}


	public function render(array $attributes = []): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation/Item/ClickableNavItem.php`

```php
namespace ;

use AppUtils\AttributeCollection as AttributeCollection;

class UI_Page_Navigation_Item_Clickable extends UI_Page_Navigation_Item
{
	public function setJSStatement(string $statement): self
	{
		/* ... */
	}


	public function getType(): string
	{
		/* ... */
	}


	public function render(array $attributes = []): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation/Item/DropdownMenu.php`

```php
namespace ;

use AppUtils\Interfaces\StringableInterface as StringableInterface;
use AppUtils\OutputBuffering as OutputBuffering;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

class UI_Page_Navigation_Item_DropdownMenu extends UI_Page_Navigation_Item
{
	public function getMenu(): UI_Bootstrap_DropdownMenu
	{
		/* ... */
	}


	/**
	 * @param string|UI_Renderable_Interface|int|float $label
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setLabel($label): UI_Page_Navigation_Item_DropdownMenu
	{
		/* ... */
	}


	/**
	 * Creates a split button for the menu, the menu itself
	 * opening by clicking the caret, and the main button label
	 * linking to its own destination.
	 *
	 * Use the {@link link()} or {@link click()} methods to
	 * set the target of the button.
	 *
	 * @return $this
	 */
	public function makeSplit(): UI_Page_Navigation_Item_DropdownMenu
	{
		/* ... */
	}


	/**
	 * Links the menu button to its own URL. Automatically
	 * turns the button into a split button with the caret
	 * used to access the menu.
	 *
	 * @param string|AdminURLInterface $url
	 * @return UI_Page_Navigation_Item_DropdownMenu
	 */
	public function link($url): UI_Page_Navigation_Item_DropdownMenu
	{
		/* ... */
	}


	/**
	 * Links the menu button to its own javascript statement.
	 * Automatically turns the button into a split button with
	 * the caret used to access the menu.
	 *
	 * @param string $statement
	 * @return UI_Page_Navigation_Item_DropdownMenu
	 */
	public function click(string $statement): UI_Page_Navigation_Item_DropdownMenu
	{
		/* ... */
	}


	public function getType(): string
	{
		/* ... */
	}


	/**
	 * Makes this the active menu item.
	 * @return $this
	 */
	public function makeActive(): UI_Page_Navigation_Item_DropdownMenu
	{
		/* ... */
	}


	public function render(array $attributes = []): string
	{
		/* ... */
	}


	/**
	 * Adds a menu item that links to a regular URL.
	 *
	 * @param string $label
	 * @param string|AdminURLInterface $url
	 * @return UI_Bootstrap_DropdownAnchor
	 * @throws UI_Exception
	 */
	public function addLink(string $label, $url): UI_Bootstrap_DropdownAnchor
	{
		/* ... */
	}


	public function setAutoActivate(bool $auto): self
	{
		/* ... */
	}


	/**
	 * @return UI_Bootstrap_DropdownMenu
	 * @throws Application_Exception
	 */
	public function addSeparator(): UI_Bootstrap_DropdownMenu
	{
		/* ... */
	}


	/**
	 * @param string|int|float|StringableInterface|NULL $label
	 * @return UI_Bootstrap_DropdownHeader
	 */
	public function addHeader($label): UI_Bootstrap_DropdownHeader
	{
		/* ... */
	}


	public function isActive(): bool
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function noCaret(): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation/Item/DropdownMenu.php`

```php
namespace ;

use AppUtils\Interfaces\StringableInterface as StringableInterface;
use AppUtils\OutputBuffering as OutputBuffering;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

class UI_Page_Navigation_Item_DropdownMenu extends UI_Page_Navigation_Item
{
	public function getMenu(): UI_Bootstrap_DropdownMenu
	{
		/* ... */
	}


	/**
	 * @param string|UI_Renderable_Interface|int|float $label
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setLabel($label): UI_Page_Navigation_Item_DropdownMenu
	{
		/* ... */
	}


	/**
	 * Creates a split button for the menu, the menu itself
	 * opening by clicking the caret, and the main button label
	 * linking to its own destination.
	 *
	 * Use the {@link link()} or {@link click()} methods to
	 * set the target of the button.
	 *
	 * @return $this
	 */
	public function makeSplit(): UI_Page_Navigation_Item_DropdownMenu
	{
		/* ... */
	}


	/**
	 * Links the menu button to its own URL. Automatically
	 * turns the button into a split button with the caret
	 * used to access the menu.
	 *
	 * @param string|AdminURLInterface $url
	 * @return UI_Page_Navigation_Item_DropdownMenu
	 */
	public function link($url): UI_Page_Navigation_Item_DropdownMenu
	{
		/* ... */
	}


	/**
	 * Links the menu button to its own javascript statement.
	 * Automatically turns the button into a split button with
	 * the caret used to access the menu.
	 *
	 * @param string $statement
	 * @return UI_Page_Navigation_Item_DropdownMenu
	 */
	public function click(string $statement): UI_Page_Navigation_Item_DropdownMenu
	{
		/* ... */
	}


	public function getType(): string
	{
		/* ... */
	}


	/**
	 * Makes this the active menu item.
	 * @return $this
	 */
	public function makeActive(): UI_Page_Navigation_Item_DropdownMenu
	{
		/* ... */
	}


	public function render(array $attributes = []): string
	{
		/* ... */
	}


	/**
	 * Adds a menu item that links to a regular URL.
	 *
	 * @param string $label
	 * @param string|AdminURLInterface $url
	 * @return UI_Bootstrap_DropdownAnchor
	 * @throws UI_Exception
	 */
	public function addLink(string $label, $url): UI_Bootstrap_DropdownAnchor
	{
		/* ... */
	}


	public function setAutoActivate(bool $auto): self
	{
		/* ... */
	}


	/**
	 * @return UI_Bootstrap_DropdownMenu
	 * @throws Application_Exception
	 */
	public function addSeparator(): UI_Bootstrap_DropdownMenu
	{
		/* ... */
	}


	/**
	 * @param string|int|float|StringableInterface|NULL $label
	 * @return UI_Bootstrap_DropdownHeader
	 */
	public function addHeader($label): UI_Bootstrap_DropdownHeader
	{
		/* ... */
	}


	public function isActive(): bool
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function noCaret(): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation/Item/ExternalLink.php`

```php
namespace ;

use AppUtils\AttributeCollection as AttributeCollection;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;
use UI\Page\Navigation\LinkItemBase as LinkItemBase;

class UI_Page_Navigation_Item_ExternalLink extends LinkItemBase
{
	public function getType(): string
	{
		/* ... */
	}


	public function getURL(): string
	{
		/* ... */
	}


	public function render(array $attributes = []): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation/Item/ExternalLink.php`

```php
namespace ;

use AppUtils\AttributeCollection as AttributeCollection;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;
use UI\Page\Navigation\LinkItemBase as LinkItemBase;

class UI_Page_Navigation_Item_ExternalLink extends LinkItemBase
{
	public function getType(): string
	{
		/* ... */
	}


	public function getURL(): string
	{
		/* ... */
	}


	public function render(array $attributes = []): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation/Item/HTML.php`

```php
namespace ;

class UI_Page_Navigation_Item_HTML extends UI_Page_Navigation_Item
{
	public function getType(): string
	{
		/* ... */
	}


	public function render(array $attributes = []): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation/Item/HTML.php`

```php
namespace ;

class UI_Page_Navigation_Item_HTML extends UI_Page_Navigation_Item
{
	public function getType(): string
	{
		/* ... */
	}


	public function render(array $attributes = []): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation/Item/InternalLink.php`

```php
namespace ;

use AppUtils\AttributeCollection as AttributeCollection;
use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;
use UI\Page\Navigation\LinkItemBase as LinkItemBase;

class UI_Page_Navigation_Item_InternalLink extends LinkItemBase
{
	public function getURL(): string
	{
		/* ... */
	}


	public function getType(): string
	{
		/* ... */
	}


	public function render(array $attributes = []): string
	{
		/* ... */
	}


	public function getAdminScreen(): ?AdminScreenInterface
	{
		/* ... */
	}


	public function getURLPath(): string
	{
		/* ... */
	}


	public function isActive(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation/Item/InternalLink.php`

```php
namespace ;

use AppUtils\AttributeCollection as AttributeCollection;
use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;
use UI\Page\Navigation\LinkItemBase as LinkItemBase;

class UI_Page_Navigation_Item_InternalLink extends LinkItemBase
{
	public function getURL(): string
	{
		/* ... */
	}


	public function getType(): string
	{
		/* ... */
	}


	public function render(array $attributes = []): string
	{
		/* ... */
	}


	public function getAdminScreen(): ?AdminScreenInterface
	{
		/* ... */
	}


	public function getURLPath(): string
	{
		/* ... */
	}


	public function isActive(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation/Item/Search.php`

```php
namespace ;

use AppUtils\ConvertHelper as ConvertHelper;
use Application\Application as Application;

class UI_Page_Navigation_Item_Search extends UI_Page_Navigation_Item
{
	public const ERROR_INVALID_CALLBACK = 22101;
	public const ERROR_INVALID_SCOPE = 22102;

	public function getTemplateName(): string
	{
		/* ... */
	}


	public function getPosition(): string
	{
		/* ... */
	}


	public function initDone(): void
	{
		/* ... */
	}


	/**
	 * Retrieves the name of the currently selected
	 * scope, if any. When not using scopes, this
	 * will always return an empty string.
	 *
	 * @return string
	 */
	public function getSelectedScopeID(): string
	{
		/* ... */
	}


	/**
	 * Retrieves the current search terms, if any.
	 *
	 * @param string $scopeID
	 * @return string
	 */
	public function getSearchTerms(string $scopeID = ''): string
	{
		/* ... */
	}


	public function getSelectedCountryID(string $scopeID = ''): string
	{
		/* ... */
	}


	public function getType(): string
	{
		/* ... */
	}


	public function getName(): string
	{
		/* ... */
	}


	/**
	 * @param array<string,string> $attributes (Unused)
	 * @return string
	 *
	 * @see template_default_ui_nav_search_inline
	 * @see template_default_ui_nav_search_full_width
	 */
	public function render(array $attributes = []): string
	{
		/* ... */
	}


	public function getSubmitElementName(): string
	{
		/* ... */
	}


	public function getSearchElementName(string $scope = ''): string
	{
		/* ... */
	}


	public function getScopeElementName(): string
	{
		/* ... */
	}


	public function getCountrySelectionElementName(string $scope): string
	{
		/* ... */
	}


	/**
	 * Retrieves all variables needed to persist the
	 * current search settings, when it is needed to
	 * inject these into another form for example.
	 *
	 * @return array<string,string>
	 */
	public function getPersistVars(): array
	{
		/* ... */
	}


	public function isSubmitted(): bool
	{
		/* ... */
	}


	/**
	 * Makes the search appear on the right hand side of the
	 * navigation bar.
	 *
	 * @return $this
	 */
	public function makeRightAligned()
	{
		/* ... */
	}


	/**
	 * Makes the search bar appear in full width right below the navigation.
	 *
	 * @return $this
	 */
	public function makeFullWidth()
	{
		/* ... */
	}


	public function isFullWidth(): bool
	{
		/* ... */
	}


	/**
	 * @return array<string,string>
	 */
	public function getHiddenVars(): array
	{
		/* ... */
	}


	public function getScopes(): array
	{
		/* ... */
	}


	/**
	 * Adds a hidden variable to the search form.
	 *
	 * @param string $name
	 * @param string $value
	 * @return UI_Page_Navigation_Item_Search
	 */
	public function addHiddenVar(string $name, $value)
	{
		/* ... */
	}


	/**
	 * Adds a collection of hidden variables, from an
	 * associative array with variable name => value pairs.
	 *
	 * @param array $vars
	 * @return UI_Page_Navigation_Item_Search
	 */
	public function addHiddenVars($vars)
	{
		/* ... */
	}


	public function addHiddenPageVars(): self
	{
		/* ... */
	}


	/**
	 * Adds a search scope: this will be added to a select
	 * element to allow the user to select a subset of the
	 * items that are searchable. The selected scope name
	 * is passed on to the search callback function.
	 *
	 * @param string $name
	 * @param string $label
	 * @return UI_Page_Navigation_Item_Search
	 */
	public function addScope($name, $label)
	{
		/* ... */
	}


	public function getCountries(): array
	{
		/* ... */
	}


	public function addCountry($name, $label): UI_Page_Navigation_Item_Search
	{
		/* ... */
	}


	public function hasCountries(): bool
	{
		/* ... */
	}


	/**
	 * Sets the minimum amount of characters for a search to be valid.
	 * @param int $length
	 * @return UI_Page_Navigation_Item_Search
	 */
	public function setMinSearchLength($length)
	{
		/* ... */
	}


	public function hasScopes(): bool
	{
		/* ... */
	}


	public function setPreSelectedScope(string $preSelectedScope)
	{
		/* ... */
	}


	public function setPreSelectedSearchTerms(string $preSelectedSearchTerms)
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation/Item/Search.php`

```php
namespace ;

use AppUtils\ConvertHelper as ConvertHelper;
use Application\Application as Application;

class UI_Page_Navigation_Item_Search extends UI_Page_Navigation_Item
{
	public const ERROR_INVALID_CALLBACK = 22101;
	public const ERROR_INVALID_SCOPE = 22102;

	public function getTemplateName(): string
	{
		/* ... */
	}


	public function getPosition(): string
	{
		/* ... */
	}


	public function initDone(): void
	{
		/* ... */
	}


	/**
	 * Retrieves the name of the currently selected
	 * scope, if any. When not using scopes, this
	 * will always return an empty string.
	 *
	 * @return string
	 */
	public function getSelectedScopeID(): string
	{
		/* ... */
	}


	/**
	 * Retrieves the current search terms, if any.
	 *
	 * @param string $scopeID
	 * @return string
	 */
	public function getSearchTerms(string $scopeID = ''): string
	{
		/* ... */
	}


	public function getSelectedCountryID(string $scopeID = ''): string
	{
		/* ... */
	}


	public function getType(): string
	{
		/* ... */
	}


	public function getName(): string
	{
		/* ... */
	}


	/**
	 * @param array<string,string> $attributes (Unused)
	 * @return string
	 *
	 * @see template_default_ui_nav_search_inline
	 * @see template_default_ui_nav_search_full_width
	 */
	public function render(array $attributes = []): string
	{
		/* ... */
	}


	public function getSubmitElementName(): string
	{
		/* ... */
	}


	public function getSearchElementName(string $scope = ''): string
	{
		/* ... */
	}


	public function getScopeElementName(): string
	{
		/* ... */
	}


	public function getCountrySelectionElementName(string $scope): string
	{
		/* ... */
	}


	/**
	 * Retrieves all variables needed to persist the
	 * current search settings, when it is needed to
	 * inject these into another form for example.
	 *
	 * @return array<string,string>
	 */
	public function getPersistVars(): array
	{
		/* ... */
	}


	public function isSubmitted(): bool
	{
		/* ... */
	}


	/**
	 * Makes the search appear on the right hand side of the
	 * navigation bar.
	 *
	 * @return $this
	 */
	public function makeRightAligned()
	{
		/* ... */
	}


	/**
	 * Makes the search bar appear in full width right below the navigation.
	 *
	 * @return $this
	 */
	public function makeFullWidth()
	{
		/* ... */
	}


	public function isFullWidth(): bool
	{
		/* ... */
	}


	/**
	 * @return array<string,string>
	 */
	public function getHiddenVars(): array
	{
		/* ... */
	}


	public function getScopes(): array
	{
		/* ... */
	}


	/**
	 * Adds a hidden variable to the search form.
	 *
	 * @param string $name
	 * @param string $value
	 * @return UI_Page_Navigation_Item_Search
	 */
	public function addHiddenVar(string $name, $value)
	{
		/* ... */
	}


	/**
	 * Adds a collection of hidden variables, from an
	 * associative array with variable name => value pairs.
	 *
	 * @param array $vars
	 * @return UI_Page_Navigation_Item_Search
	 */
	public function addHiddenVars($vars)
	{
		/* ... */
	}


	public function addHiddenPageVars(): self
	{
		/* ... */
	}


	/**
	 * Adds a search scope: this will be added to a select
	 * element to allow the user to select a subset of the
	 * items that are searchable. The selected scope name
	 * is passed on to the search callback function.
	 *
	 * @param string $name
	 * @param string $label
	 * @return UI_Page_Navigation_Item_Search
	 */
	public function addScope($name, $label)
	{
		/* ... */
	}


	public function getCountries(): array
	{
		/* ... */
	}


	public function addCountry($name, $label): UI_Page_Navigation_Item_Search
	{
		/* ... */
	}


	public function hasCountries(): bool
	{
		/* ... */
	}


	/**
	 * Sets the minimum amount of characters for a search to be valid.
	 * @param int $length
	 * @return UI_Page_Navigation_Item_Search
	 */
	public function setMinSearchLength($length)
	{
		/* ... */
	}


	public function hasScopes(): bool
	{
		/* ... */
	}


	public function setPreSelectedScope(string $preSelectedScope)
	{
		/* ... */
	}


	public function setPreSelectedSearchTerms(string $preSelectedSearchTerms)
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation/LinkItemBase.php`

```php
namespace UI\Page\Navigation;

use UI_Page_Navigation_Item as UI_Page_Navigation_Item;

abstract class LinkItemBase extends UI_Page_Navigation_Item
{
	public function setTarget(string $target): self
	{
		/* ... */
	}


	public function makeNewTab(): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation/LinkItemBase.php`

```php
namespace UI\Page\Navigation;

use UI_Page_Navigation_Item as UI_Page_Navigation_Item;

abstract class LinkItemBase extends UI_Page_Navigation_Item
{
	public function setTarget(string $target): self
	{
		/* ... */
	}


	public function makeNewTab(): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation/MetaNavigation.php`

```php
namespace UI\Page\Navigation;

use Application\AppFactory as AppFactory;
use Application\Application as Application;
use Application_Driver as Application_Driver;
use Application_User_Notepad as Application_User_Notepad;
use UI as UI;
use UI\Page\Navigation\MetaNavigation\DeveloperMenu as DeveloperMenu;
use UI\Page\Navigation\MetaNavigation\UserMenu as UserMenu;
use UI_Page_Navigation as UI_Page_Navigation;
use UI_Renderable_Interface as UI_Renderable_Interface;
use UI_Traits_RenderableGeneric as UI_Traits_RenderableGeneric;

/**
 * @see template_default_navigation_metanav
 */
class MetaNavigation implements UI_Renderable_Interface
{
	use UI_Traits_RenderableGeneric;

	public const META_LOOKUP = 'lookup';
	public const META_PRINT_PAGE = 'print-page';
	public const META_NOTEPAD = 'notepad';
	public const META_DEVELOPER = 'developer';
	public const META_USER = 'user';
	public const META_NEWS = 'news';

	public function getUI(): UI
	{
		/* ... */
	}


	public function getNavigation(): UI_Page_Navigation
	{
		/* ... */
	}


	public function configure(): void
	{
		/* ... */
	}


	public function isDeveloperMenuEnabled(): bool
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation/MetaNavigation.php`

```php
namespace UI\Page\Navigation;

use Application\AppFactory as AppFactory;
use Application\Application as Application;
use Application_Driver as Application_Driver;
use Application_User_Notepad as Application_User_Notepad;
use UI as UI;
use UI\Page\Navigation\MetaNavigation\DeveloperMenu as DeveloperMenu;
use UI\Page\Navigation\MetaNavigation\UserMenu as UserMenu;
use UI_Page_Navigation as UI_Page_Navigation;
use UI_Renderable_Interface as UI_Renderable_Interface;
use UI_Traits_RenderableGeneric as UI_Traits_RenderableGeneric;

/**
 * @see template_default_navigation_metanav
 */
class MetaNavigation implements UI_Renderable_Interface
{
	use UI_Traits_RenderableGeneric;

	public const META_LOOKUP = 'lookup';
	public const META_PRINT_PAGE = 'print-page';
	public const META_NOTEPAD = 'notepad';
	public const META_DEVELOPER = 'developer';
	public const META_USER = 'user';
	public const META_NEWS = 'news';

	public function getUI(): UI
	{
		/* ... */
	}


	public function getNavigation(): UI_Page_Navigation
	{
		/* ... */
	}


	public function configure(): void
	{
		/* ... */
	}


	public function isDeveloperMenuEnabled(): bool
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation/MetaNavigation/DeveloperMenu.php`

```php
namespace UI\Page\Navigation\MetaNavigation;

use Application\Application as Application;
use Application_Driver as Application_Driver;
use Application_LockManager as Application_LockManager;
use Application_Request as Application_Request;
use Application_Session_Base as Application_Session_Base;
use Application_User as Application_User;
use UI as UI;
use UI_Page_Navigation_Item_DropdownMenu as UI_Page_Navigation_Item_DropdownMenu;

class DeveloperMenu
{
	public function configure(): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation/MetaNavigation/DeveloperMenu.php`

```php
namespace UI\Page\Navigation\MetaNavigation;

use Application\Application as Application;
use Application_Driver as Application_Driver;
use Application_LockManager as Application_LockManager;
use Application_Request as Application_Request;
use Application_Session_Base as Application_Session_Base;
use Application_User as Application_User;
use UI as UI;
use UI_Page_Navigation_Item_DropdownMenu as UI_Page_Navigation_Item_DropdownMenu;

class DeveloperMenu
{
	public function configure(): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation/MetaNavigation/UserMenu.php`

```php
namespace UI\Page\Navigation\MetaNavigation;

use Application\Application as Application;
use Application_Bootstrap_Screen as Application_Bootstrap_Screen;
use Application_Driver as Application_Driver;
use Application_Request as Application_Request;
use Application_User as Application_User;
use Application_User_Notepad as Application_User_Notepad;
use UI as UI;
use UI_Bootstrap_DropdownMenu as UI_Bootstrap_DropdownMenu;
use UI_Page_Navigation_Item_DropdownMenu as UI_Page_Navigation_Item_DropdownMenu;

class UserMenu
{
	public function configure(): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation/MetaNavigation/UserMenu.php`

```php
namespace UI\Page\Navigation\MetaNavigation;

use Application\Application as Application;
use Application_Bootstrap_Screen as Application_Bootstrap_Screen;
use Application_Driver as Application_Driver;
use Application_Request as Application_Request;
use Application_User as Application_User;
use Application_User_Notepad as Application_User_Notepad;
use UI as UI;
use UI_Bootstrap_DropdownMenu as UI_Bootstrap_DropdownMenu;
use UI_Page_Navigation_Item_DropdownMenu as UI_Page_Navigation_Item_DropdownMenu;

class UserMenu
{
	public function configure(): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation/NavConfigurator.php`

```php
namespace UI\Page\Navigation;

use Application\Interfaces\Admin\AdminAreaInterface as AdminAreaInterface;
use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;
use Application_Driver as Application_Driver;
use UI\Page\Navigation\NavConfigurator\MenuConfigurator as MenuConfigurator;
use UI_Page_Navigation as UI_Page_Navigation;
use UI_Page_Navigation_Item as UI_Page_Navigation_Item;

abstract class NavConfigurator
{
	public const DRIVER_CONFIGURATOR_CLASS_NAME = 'MainNavConfigurator';

	public function getDriver(): Application_Driver
	{
		/* ... */
	}


	public function getNavigation(): UI_Page_Navigation
	{
		/* ... */
	}


	abstract public function configure(): void;


	public function addArea(string $urlName, bool $withIcon = false): ?UI_Page_Navigation_Item
	{
		/* ... */
	}


	public function getAreaByURLName(string $urlName): ?AdminAreaInterface
	{
		/* ... */
	}


	public function addMenu($label): MenuConfigurator
	{
		/* ... */
	}


	public function getScreenByPath(
		string $area,
		?string $mode = null,
		?string $submode = null,
		?string $action = null,
	): ?AdminScreenInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation/NavConfigurator.php`

```php
namespace UI\Page\Navigation;

use Application\Interfaces\Admin\AdminAreaInterface as AdminAreaInterface;
use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;
use Application_Driver as Application_Driver;
use UI\Page\Navigation\NavConfigurator\MenuConfigurator as MenuConfigurator;
use UI_Page_Navigation as UI_Page_Navigation;
use UI_Page_Navigation_Item as UI_Page_Navigation_Item;

abstract class NavConfigurator
{
	public const DRIVER_CONFIGURATOR_CLASS_NAME = 'MainNavConfigurator';

	public function getDriver(): Application_Driver
	{
		/* ... */
	}


	public function getNavigation(): UI_Page_Navigation
	{
		/* ... */
	}


	abstract public function configure(): void;


	public function addArea(string $urlName, bool $withIcon = false): ?UI_Page_Navigation_Item
	{
		/* ... */
	}


	public function getAreaByURLName(string $urlName): ?AdminAreaInterface
	{
		/* ... */
	}


	public function addMenu($label): MenuConfigurator
	{
		/* ... */
	}


	public function getScreenByPath(
		string $area,
		?string $mode = null,
		?string $submode = null,
		?string $action = null,
	): ?AdminScreenInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation/NavConfigurator/MenuConfigurator.php`

```php
namespace UI\Page\Navigation\NavConfigurator;

use UI\Page\Navigation\NavConfigurator as NavConfigurator;
use UI_Bootstrap_DropdownAnchor as UI_Bootstrap_DropdownAnchor;
use UI_Page_Navigation_Item_DropdownMenu as UI_Page_Navigation_Item_DropdownMenu;

class MenuConfigurator implements \Application_Interfaces_Loggable
{
	use \Application_Traits_Loggable;

	public function getLogIdentifier(): string
	{
		/* ... */
	}


	public function setAutoActivate(bool $auto): self
	{
		/* ... */
	}


	public function addAreaChained(string $urlName): self
	{
		/* ... */
	}


	public function addArea(string $urlName): ?UI_Bootstrap_DropdownAnchor
	{
		/* ... */
	}


	public function addPathChained(
		string $area,
		?string $mode = null,
		?string $submode = null,
		?string $action = null,
	): self
	{
		/* ... */
	}


	public function addPath(
		string $area,
		?string $mode = null,
		?string $submode = null,
		?string $action = null,
	): ?UI_Bootstrap_DropdownAnchor
	{
		/* ... */
	}


	public function addSeparator(): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation/NavConfigurator/MenuConfigurator.php`

```php
namespace UI\Page\Navigation\NavConfigurator;

use UI\Page\Navigation\NavConfigurator as NavConfigurator;
use UI_Bootstrap_DropdownAnchor as UI_Bootstrap_DropdownAnchor;
use UI_Page_Navigation_Item_DropdownMenu as UI_Page_Navigation_Item_DropdownMenu;

class MenuConfigurator implements \Application_Interfaces_Loggable
{
	use \Application_Traits_Loggable;

	public function getLogIdentifier(): string
	{
		/* ... */
	}


	public function setAutoActivate(bool $auto): self
	{
		/* ... */
	}


	public function addAreaChained(string $urlName): self
	{
		/* ... */
	}


	public function addArea(string $urlName): ?UI_Bootstrap_DropdownAnchor
	{
		/* ... */
	}


	public function addPathChained(
		string $area,
		?string $mode = null,
		?string $submode = null,
		?string $action = null,
	): self
	{
		/* ... */
	}


	public function addPath(
		string $area,
		?string $mode = null,
		?string $submode = null,
		?string $action = null,
	): ?UI_Bootstrap_DropdownAnchor
	{
		/* ... */
	}


	public function addSeparator(): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation/QuickNavigation.php`

```php
namespace UI\Page\Navigation;

use AppUtils\NamedClosure as NamedClosure;
use Application\Admin\Area\Events\UIHandlingCompleteEvent as UIHandlingCompleteEvent;
use Application\EventHandler\EventManager as EventManager;
use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;
use Application_Driver as Application_Driver;
use Application_Interfaces_Loggable as Application_Interfaces_Loggable;
use Application_Traits_Loggable as Application_Traits_Loggable;
use Closure as Closure;
use UI as UI;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;
use UI\Page\Navigation\QuickNavigation\Items\ScreenNavItem as ScreenNavItem;
use UI\Page\Navigation\QuickNavigation\Items\URLNavItem as URLNavItem;
use UI\Page\Navigation\QuickNavigation\ScreenItemsContainer as ScreenItemsContainer;
use UI_Exception as UI_Exception;
use UI_Page_Header as UI_Page_Header;
use UI_Page_Navigation as UI_Page_Navigation;
use UI_Renderable_Interface as UI_Renderable_Interface;

class QuickNavigation implements Application_Interfaces_Loggable
{
	use Application_Traits_Loggable;

	public const ERROR_NO_ACTIVE_SCREEN_SET = 110701;
	public const ERROR_NO_CONTAINER_FOR_SCREEN = 110702;
	public const NAV_AREA_QUICK_NAVIGATION = 'area-quick-nav';

	public function getUI(): UI
	{
		/* ... */
	}


	public function hasItems(): bool
	{
		/* ... */
	}


	/**
	 * Gets all screen item containers that must be
	 * rendered in the active admin screen. This is
	 * all of them, except when the active screen
	 * requested the exclusivity - then only the
	 * active screen's items container is used.
	 *
	 * @return ScreenItemsContainer[]
	 */
	public function resolveContainers(): array
	{
		/* ... */
	}


	public function getActiveContainer(): ?ScreenItemsContainer
	{
		/* ... */
	}


	public function setWorkScreen(AdminScreenInterface $screen): void
	{
		/* ... */
	}


	public function makeExclusive(): self
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface|NULL $label
	 * @param string|AdminURLInterface $url
	 * @return URLNavItem
	 * @throws UI_Exception
	 */
	public function addURL($label, $url): URLNavItem
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface|NULL $label
	 * @param array $params
	 * @return ScreenNavItem
	 * @throws UI_Exception
	 */
	public function addScreen($label, array $params = []): ScreenNavItem
	{
		/* ... */
	}


	public function getLogIdentifier(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation/QuickNavigation.php`

```php
namespace UI\Page\Navigation;

use AppUtils\NamedClosure as NamedClosure;
use Application\Admin\Area\Events\UIHandlingCompleteEvent as UIHandlingCompleteEvent;
use Application\EventHandler\EventManager as EventManager;
use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;
use Application_Driver as Application_Driver;
use Application_Interfaces_Loggable as Application_Interfaces_Loggable;
use Application_Traits_Loggable as Application_Traits_Loggable;
use Closure as Closure;
use UI as UI;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;
use UI\Page\Navigation\QuickNavigation\Items\ScreenNavItem as ScreenNavItem;
use UI\Page\Navigation\QuickNavigation\Items\URLNavItem as URLNavItem;
use UI\Page\Navigation\QuickNavigation\ScreenItemsContainer as ScreenItemsContainer;
use UI_Exception as UI_Exception;
use UI_Page_Header as UI_Page_Header;
use UI_Page_Navigation as UI_Page_Navigation;
use UI_Renderable_Interface as UI_Renderable_Interface;

class QuickNavigation implements Application_Interfaces_Loggable
{
	use Application_Traits_Loggable;

	public const ERROR_NO_ACTIVE_SCREEN_SET = 110701;
	public const ERROR_NO_CONTAINER_FOR_SCREEN = 110702;
	public const NAV_AREA_QUICK_NAVIGATION = 'area-quick-nav';

	public function getUI(): UI
	{
		/* ... */
	}


	public function hasItems(): bool
	{
		/* ... */
	}


	/**
	 * Gets all screen item containers that must be
	 * rendered in the active admin screen. This is
	 * all of them, except when the active screen
	 * requested the exclusivity - then only the
	 * active screen's items container is used.
	 *
	 * @return ScreenItemsContainer[]
	 */
	public function resolveContainers(): array
	{
		/* ... */
	}


	public function getActiveContainer(): ?ScreenItemsContainer
	{
		/* ... */
	}


	public function setWorkScreen(AdminScreenInterface $screen): void
	{
		/* ... */
	}


	public function makeExclusive(): self
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface|NULL $label
	 * @param string|AdminURLInterface $url
	 * @return URLNavItem
	 * @throws UI_Exception
	 */
	public function addURL($label, $url): URLNavItem
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface|NULL $label
	 * @param array $params
	 * @return ScreenNavItem
	 * @throws UI_Exception
	 */
	public function addScreen($label, array $params = []): ScreenNavItem
	{
		/* ... */
	}


	public function getLogIdentifier(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation/QuickNavigation/BaseQuickNavItem.php`

```php
namespace UI\Page\Navigation\QuickNavigation;

use AppUtils\Traits\RenderableTrait as RenderableTrait;
use Application_Interfaces_Iconizable as Application_Interfaces_Iconizable;
use Application_Traits_Iconizable as Application_Traits_Iconizable;
use UI\Interfaces\TooltipableInterface as TooltipableInterface;
use UI\Page\Navigation\QuickNavigation as QuickNavigation;
use UI\Traits\TooltipableTrait as TooltipableTrait;
use UI_Exception as UI_Exception;
use UI_Interfaces_Conditional as UI_Interfaces_Conditional;
use UI_Page_Navigation as UI_Page_Navigation;
use UI_Renderable_Interface as UI_Renderable_Interface;
use UI_Traits_Conditional as UI_Traits_Conditional;

/**
 * Abstract base class for navigation items in the quick navigation.
 *
 * @package UI
 * @subpackage QuickNavigation
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class BaseQuickNavItem implements Application_Interfaces_Iconizable, UI_Interfaces_Conditional, TooltipableInterface
{
	use Application_Traits_Iconizable;
	use UI_Traits_Conditional;
	use TooltipableTrait;
	use RenderableTrait;

	public function next(): QuickNavigation
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}


	abstract public function injectNavigation(UI_Page_Navigation $navigation): void;
}


```
###  Path: `/src/classes/UI/Page/Navigation/QuickNavigation/BaseQuickNavItem.php`

```php
namespace UI\Page\Navigation\QuickNavigation;

use AppUtils\Traits\RenderableTrait as RenderableTrait;
use Application_Interfaces_Iconizable as Application_Interfaces_Iconizable;
use Application_Traits_Iconizable as Application_Traits_Iconizable;
use UI\Interfaces\TooltipableInterface as TooltipableInterface;
use UI\Page\Navigation\QuickNavigation as QuickNavigation;
use UI\Traits\TooltipableTrait as TooltipableTrait;
use UI_Exception as UI_Exception;
use UI_Interfaces_Conditional as UI_Interfaces_Conditional;
use UI_Page_Navigation as UI_Page_Navigation;
use UI_Renderable_Interface as UI_Renderable_Interface;
use UI_Traits_Conditional as UI_Traits_Conditional;

/**
 * Abstract base class for navigation items in the quick navigation.
 *
 * @package UI
 * @subpackage QuickNavigation
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class BaseQuickNavItem implements Application_Interfaces_Iconizable, UI_Interfaces_Conditional, TooltipableInterface
{
	use Application_Traits_Iconizable;
	use UI_Traits_Conditional;
	use TooltipableTrait;
	use RenderableTrait;

	public function next(): QuickNavigation
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}


	abstract public function injectNavigation(UI_Page_Navigation $navigation): void;
}


```
###  Path: `/src/classes/UI/Page/Navigation/QuickNavigation/Items/ScreenNavItem.php`

```php
namespace UI\Page\Navigation\QuickNavigation\Items;

use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;
use Application_Request as Application_Request;
use UI\Page\Navigation\QuickNavigation as QuickNavigation;
use UI\Page\Navigation\QuickNavigation\BaseQuickNavItem as BaseQuickNavItem;
use UI_Exception as UI_Exception;
use UI_Page_Navigation as UI_Page_Navigation;
use UI_Renderable_Interface as UI_Renderable_Interface;

/**
 * Navigation item for adding a link to an admin screen
 * in the application.
 *
 * @package UI
 * @subpackage QuickNavigation
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ScreenNavItem extends BaseQuickNavItem
{
	public function setAreaID(string $areaID): self
	{
		/* ... */
	}


	public function setModeID(string $modeID): self
	{
		/* ... */
	}


	public function setSubmodeID(string $submodeID): self
	{
		/* ... */
	}


	public function setActionID(string $actionID): self
	{
		/* ... */
	}


	/**
	 * @param array<string,string> $params
	 * @return $this
	 */
	public function setParams(array $params): self
	{
		/* ... */
	}


	public function setParam(string $name, string $value): self
	{
		/* ... */
	}


	public function injectNavigation(UI_Page_Navigation $navigation): void
	{
		/* ... */
	}


	public function makeNewTab(bool $newTab = true): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation/QuickNavigation/Items/ScreenNavItem.php`

```php
namespace UI\Page\Navigation\QuickNavigation\Items;

use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;
use Application_Request as Application_Request;
use UI\Page\Navigation\QuickNavigation as QuickNavigation;
use UI\Page\Navigation\QuickNavigation\BaseQuickNavItem as BaseQuickNavItem;
use UI_Exception as UI_Exception;
use UI_Page_Navigation as UI_Page_Navigation;
use UI_Renderable_Interface as UI_Renderable_Interface;

/**
 * Navigation item for adding a link to an admin screen
 * in the application.
 *
 * @package UI
 * @subpackage QuickNavigation
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ScreenNavItem extends BaseQuickNavItem
{
	public function setAreaID(string $areaID): self
	{
		/* ... */
	}


	public function setModeID(string $modeID): self
	{
		/* ... */
	}


	public function setSubmodeID(string $submodeID): self
	{
		/* ... */
	}


	public function setActionID(string $actionID): self
	{
		/* ... */
	}


	/**
	 * @param array<string,string> $params
	 * @return $this
	 */
	public function setParams(array $params): self
	{
		/* ... */
	}


	public function setParam(string $name, string $value): self
	{
		/* ... */
	}


	public function injectNavigation(UI_Page_Navigation $navigation): void
	{
		/* ... */
	}


	public function makeNewTab(bool $newTab = true): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation/QuickNavigation/Items/URLNavItem.php`

```php
namespace UI\Page\Navigation\QuickNavigation\Items;

use UI\AdminURLs\AdminURLInterface as AdminURLInterface;
use UI\Page\Navigation\QuickNavigation as QuickNavigation;
use UI\Page\Navigation\QuickNavigation\BaseQuickNavItem as BaseQuickNavItem;
use UI_Exception as UI_Exception;
use UI_Page_Navigation as UI_Page_Navigation;
use UI_Renderable_Interface as UI_Renderable_Interface;

/**
 * Navigation item for adding a simple URL, with the
 * option to make it open in a new tab.
 *
 * @package UI
 * @subpackage QuickNavigation
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class URLNavItem extends BaseQuickNavItem
{
	public function injectNavigation(UI_Page_Navigation $navigation): void
	{
		/* ... */
	}


	public function makeNewTab(bool $external = true): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation/QuickNavigation/Items/URLNavItem.php`

```php
namespace UI\Page\Navigation\QuickNavigation\Items;

use UI\AdminURLs\AdminURLInterface as AdminURLInterface;
use UI\Page\Navigation\QuickNavigation as QuickNavigation;
use UI\Page\Navigation\QuickNavigation\BaseQuickNavItem as BaseQuickNavItem;
use UI_Exception as UI_Exception;
use UI_Page_Navigation as UI_Page_Navigation;
use UI_Renderable_Interface as UI_Renderable_Interface;

/**
 * Navigation item for adding a simple URL, with the
 * option to make it open in a new tab.
 *
 * @package UI
 * @subpackage QuickNavigation
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class URLNavItem extends BaseQuickNavItem
{
	public function injectNavigation(UI_Page_Navigation $navigation): void
	{
		/* ... */
	}


	public function makeNewTab(bool $external = true): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation/QuickNavigation/ScreenItemsContainer.php`

```php
namespace UI\Page\Navigation\QuickNavigation;

use UI\AdminURLs\AdminURLInterface as AdminURLInterface;
use UI\Page\Navigation\QuickNavigation as QuickNavigation;
use UI\Page\Navigation\QuickNavigation\Items\ScreenNavItem as ScreenNavItem;
use UI\Page\Navigation\QuickNavigation\Items\URLNavItem as URLNavItem;
use UI_Exception as UI_Exception;
use UI_Page_Navigation as UI_Page_Navigation;
use UI_Renderable_Interface as UI_Renderable_Interface;

/**
 * Container for navigation items tied to a specific
 * administration screen, to keep them separate from
 * each other.
 *
 * @package UI
 * @subpackage QuickNavigation
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ScreenItemsContainer
{
	public function hasItems(): bool
	{
		/* ... */
	}


	public function getValidItems(): array
	{
		/* ... */
	}


	public function makeExclusive(): self
	{
		/* ... */
	}


	public function isExclusive(): bool
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface|NULL $label
	 * @param string|AdminURLInterface $url
	 * @return URLNavItem
	 *
	 * @throws UI_Exception
	 */
	public function addURL($label, $url): URLNavItem
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface|NULL $label
	 * @param array $params
	 * @return ScreenNavItem
	 * @throws UI_Exception
	 */
	public function addScreen($label, array $params = []): ScreenNavItem
	{
		/* ... */
	}


	public function injectElements(UI_Page_Navigation $navigation): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation/QuickNavigation/ScreenItemsContainer.php`

```php
namespace UI\Page\Navigation\QuickNavigation;

use UI\AdminURLs\AdminURLInterface as AdminURLInterface;
use UI\Page\Navigation\QuickNavigation as QuickNavigation;
use UI\Page\Navigation\QuickNavigation\Items\ScreenNavItem as ScreenNavItem;
use UI\Page\Navigation\QuickNavigation\Items\URLNavItem as URLNavItem;
use UI_Exception as UI_Exception;
use UI_Page_Navigation as UI_Page_Navigation;
use UI_Renderable_Interface as UI_Renderable_Interface;

/**
 * Container for navigation items tied to a specific
 * administration screen, to keep them separate from
 * each other.
 *
 * @package UI
 * @subpackage QuickNavigation
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ScreenItemsContainer
{
	public function hasItems(): bool
	{
		/* ... */
	}


	public function getValidItems(): array
	{
		/* ... */
	}


	public function makeExclusive(): self
	{
		/* ... */
	}


	public function isExclusive(): bool
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface|NULL $label
	 * @param string|AdminURLInterface $url
	 * @return URLNavItem
	 *
	 * @throws UI_Exception
	 */
	public function addURL($label, $url): URLNavItem
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface|NULL $label
	 * @param array $params
	 * @return ScreenNavItem
	 * @throws UI_Exception
	 */
	public function addScreen($label, array $params = []): ScreenNavItem
	{
		/* ... */
	}


	public function injectElements(UI_Page_Navigation $navigation): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation/TextLinksNavigation.php`

```php
namespace UI\Page\Navigation;

use Application\Themes\DefaultTemplate\UI\Nav\TextLinksNavigationTmpl as TextLinksNavigationTmpl;
use UI_Page as UI_Page;
use UI_Page_Navigation as UI_Page_Navigation;

/**
 * A navigation bar that displays text links.
 *
 * @package UI
 * @subpackage Navigation
 *
 * @see TextLinksNavigationTmpl
 */
class TextLinksNavigation extends UI_Page_Navigation
{
	/**
	 * @return class-string<TextLinksNavigationTmpl>
	 */
	public function getTemplateID(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Navigation/TextLinksNavigation.php`

```php
namespace UI\Page\Navigation;

use Application\Themes\DefaultTemplate\UI\Nav\TextLinksNavigationTmpl as TextLinksNavigationTmpl;
use UI_Page as UI_Page;
use UI_Page_Navigation as UI_Page_Navigation;

/**
 * A navigation bar that displays text links.
 *
 * @package UI
 * @subpackage Navigation
 *
 * @see TextLinksNavigationTmpl
 */
class TextLinksNavigation extends UI_Page_Navigation
{
	/**
	 * @return class-string<TextLinksNavigationTmpl>
	 */
	public function getTemplateID(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/RevisionableTitle.php`

```php
namespace ;

use AppUtils\Interfaces\StringableInterface as StringableInterface;
use Application\Application as Application;
use Application\Revisionable\RevisionableInterface as RevisionableInterface;

/**
 * Wrapper for the page title class to handle revisionable
 * title specifics (state badges, etc.).
 *
 * @package Application
 * @subpackage User Interface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see UI_Page_Title
 */
class UI_Page_RevisionableTitle extends UI_Renderable
{
	/**
	 * @param string|number|UI_Renderable_Interface $subline
	 * @return $this
	 */
	public function setSubline($subline): UI_Page_RevisionableTitle
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface|NULL $label
	 * @return UI_Page_RevisionableTitle
	 */
	public function setLabel($label): UI_Page_RevisionableTitle
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface $subline
	 * @return UI_Page_RevisionableTitle
	 */
	public function addSubline($subline): UI_Page_RevisionableTitle
	{
		/* ... */
	}


	/**
	 * @param string|number|StringableInterface|NULL $text
	 * @return $this
	 * @throws UI_Exception
	 */
	public function addTextAppend($text): self
	{
		/* ... */
	}


	/**
	 * @param UI_Interfaces_Badge $badge
	 * @return UI_Page_RevisionableTitle
	 */
	public function addBadge(UI_Interfaces_Badge $badge): UI_Page_RevisionableTitle
	{
		/* ... */
	}


	/**
	 * @param UI_Interfaces_Badge $badge
	 * @return UI_Page_RevisionableTitle
	 */
	public function prependBadge(UI_Interfaces_Badge $badge): UI_Page_RevisionableTitle
	{
		/* ... */
	}


	public function configure(): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Section.php`

```php
namespace ;

use AppUtils\ClassHelper as ClassHelper;
use AppUtils\ClassHelper\ClassNotExistsException as ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException as ClassNotImplementsException;
use AppUtils\Interfaces\ClassableInterface as ClassableInterface;
use AppUtils\Interfaces\StringableInterface as StringableInterface;
use AppUtils\NumberInfo as NumberInfo;
use AppUtils\Traits\ClassableTrait as ClassableTrait;
use UI\Interfaces\CapturableInterface as CapturableInterface;
use UI\Page\Section\GroupControls as GroupControls;
use UI\Page\Section\SectionsRegistry as SectionsRegistry;
use UI\Traits\CapturableTrait as CapturableTrait;

/**
 * Helper class for creating and rendering content sections
 * in the UI. Offers an easy API to configure a section and
 * render/display it in several ways.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see UI_Page::createSection()
 * @see template_default_frame_content_section
 * @see template_default_frame_sidebar_section
 */
abstract class UI_Page_Section extends UI_Renderable implements UI_Interfaces_Conditional, Application_LockableItem_Interface, UI_Page_Sidebar_ItemInterface, Application_Interfaces_Iconizable, ClassableInterface, CapturableInterface, UI_Interfaces_StatusElementContainer
{
	use ClassableTrait;
	use CapturableTrait;
	use UI_Traits_Conditional;
	use UI_Traits_StatusElementContainer;
	use Application_Traits_LockableStatus;
	use Application_Traits_LockableItem;

	public const ERROR_INVALID_CONTEXT_BUTTON = 511001;
	public const ERROR_TAB_ALREADY_EXISTS = 511002;
	public const STYLE_DANGEROUS = 'dangerous';
	public const PROPERTY_VISUAL_STYLE = 'visual-style';
	public const DEFAULT_GROUP = 'default';
	public const TYPE_SUBSECTION = 'content-subsection';
	public const PROPERTY_CONTENT_INDENTED = 'content-indented';
	public const STYLESHEET_FILE = 'ui-sections.css';
	public const BACKGROUND_TYPE_SOLID_DEFAULT = 'solid-default';

	public function hasBeenRendered(): bool
	{
		/* ... */
	}


	public function makeDangerous(): self
	{
		/* ... */
	}


	public function setVisualStyle(string $style): self
	{
		/* ... */
	}


	public function getVisualStyle(): ?string
	{
		/* ... */
	}


	public function isSubsection(): bool
	{
		/* ... */
	}


	/**
	 * If enabled, the section's content will be indented to visually
	 * separate it from the rest of the page. Default is to keep all
	 * content left.
	 *
	 * @param bool $indented
	 * @return $this
	 */
	public function makeContentIndented(bool $indented = true): self
	{
		/* ... */
	}


	public function isContentIndented(): bool
	{
		/* ... */
	}


	public function getType(): string
	{
		/* ... */
	}


	/**
	 * Sets the name of the template that will be used to
	 * render the section. Default is the <code>frame.content.section</code>
	 * template.
	 *
	 * @param string $templateName
	 * @return $this
	 */
	public function setTemplateName(string $templateName): self
	{
		/* ... */
	}


	/**
	 * Overrides the section's automatic ID. Use this if you need
	 * to make sure the section always has the same ID.
	 *
	 * @param string $id
	 * @return $this
	 */
	public function setID(string $id): self
	{
		/* ... */
	}


	/**
	 * Retrieves the section's ID attribute.
	 * @return string
	 */
	public function getID(): string
	{
		/* ... */
	}


	/**
	 * Optional. Sets the section's heading title.
	 * @param string|number|UI_Renderable_Interface|NULL $title
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setTitle($title): self
	{
		/* ... */
	}


	/**
	 * @param string $anchor
	 * @return $this
	 */
	public function setAnchor(string $anchor): self
	{
		/* ... */
	}


	public function getAnchor(): string
	{
		/* ... */
	}


	/**
	 * Whether to skip the section rendering if its contents are empty.
	 * @return boolean
	 */
	public function isVisibleIfEmpty(): bool
	{
		/* ... */
	}


	/**
	 * Turns off the behavior that a section with empty
	 * content is not displayed in the generated HTML.
	 * This is mainly used to allow using a section
	 * clientside, and fill it there.
	 *
	 * @param bool $visible
	 * @return $this
	 */
	public function setVisibleIfEmpty(bool $visible = true): self
	{
		/* ... */
	}


	/**
	 * @param string $group
	 * @return $this
	 */
	public function setGroup(string $group): self
	{
		/* ... */
	}


	public function getGroup(): string
	{
		/* ... */
	}


	/**
	 * Makes the section's background a solid color, with the specified
	 * style. Use this in cases where the section should not have a transparent
	 * background.
	 *
	 * @param string $type
	 * @return $this
	 */
	public function makeSolidBackground(string $type): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 * @deprecated Use {@see makeBodySolidFill()} instead.
	 */
	public function makeLightBackground(): self
	{
		/* ... */
	}


	/**
	 * Fills the section body with the default content background
	 * color (default is a transparent background).
	 *
	 * @return $this
	 */
	public function makeBodySolidFill(): self
	{
		/* ... */
	}


	/**
	 * Retrieves the current title for the section's heading.
	 * @return string
	 */
	public function getTitle(): string
	{
		/* ... */
	}


	/**
	 * Optional. Sets the tagline under the title.
	 * Note: only used if a title is also set.
	 *
	 * @param string|number|UI_Renderable_Interface $text
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setTagline($text): self
	{
		/* ... */
	}


	public function getTagline(): string
	{
		/* ... */
	}


	public function hasTagline(): bool
	{
		/* ... */
	}


	/**
	 * Optional. Sets an abstract text that explains the contents of the section.
	 * @param string|number|UI_Renderable_Interface $text
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setAbstract($text): self
	{
		/* ... */
	}


	public function getAbstract(): string
	{
		/* ... */
	}


	/**
	 * Sets the markup to use as body for the section. If this is not set,
	 * the section will not be rendered.
	 *
	 * @param string|number|StringableInterface|NULL $content
	 * @return $this
	 * @throws UI_Exception
	 * @see startCapture()
	 */
	public function setContent($content): self
	{
		/* ... */
	}


	public function getContent(): string
	{
		/* ... */
	}


	/**
	 * Limits the height of the section body to the
	 * specified maximum pixel height.
	 * If the body is higher, a scrollbar will be shown.
	 *
	 * @param string|int|float|NULL $height A height in a format parsable by {@see NumberInfo}.
	 * @return $this
	 */
	public function setMaxBodyHeight($height): self
	{
		/* ... */
	}


	/**
	 * Appends markup to the existing section content.
	 * @param string|number|StringableInterface|NULL $content
	 * @return $this
	 * @throws UI_Exception
	 * @see prependContent()
	 */
	public function appendContent($content): self
	{
		/* ... */
	}


	/**
	 * Prepends content to the existing section content.
	 * @param string|number|UI_Renderable_Interface $content
	 * @return $this
	 * @throws UI_Exception
	 */
	public function prependContent($content): self
	{
		/* ... */
	}


	/**
	 * Retrieves a property value.
	 * @param string $name
	 * @return mixed|NULL
	 */
	public function getProperty(string $name)
	{
		/* ... */
	}


	public function getMaxBodyHeight(): ?NumberInfo
	{
		/* ... */
	}


	/**
	 * Whether the section has an abstract set.
	 * @return boolean
	 */
	public function hasAbstract(): bool
	{
		/* ... */
	}


	/**
	 * Whether the section has context buttons.
	 * @return boolean
	 */
	public function hasContextButtons(): bool
	{
		/* ... */
	}


	/**
	 * Turns the section into an empty section with just an informational message.
	 * The message is automatically set to not dismissible, and the message itself
	 * is prepended with an information icon.
	 *
	 * @param string|int|float|UI_Renderable_Interface|NULL $message
	 * @return $this
	 * @throws UI_Exception
	 */
	public function makeInfoMessage($message): self
	{
		/* ... */
	}


	/**
	 * Makes the section collapsible.
	 *
	 * NOTE: Requires a title to be set, or it will not work.
	 *
	 * @param boolean $collapsed Whether the section should start collapsed.
	 * @return $this
	 * @see UI_Page_Section::makeStatic()
	 */
	public function makeCollapsible(bool $collapsed = false): self
	{
		/* ... */
	}


	/**
	 * Sets the icon to use for the section. Note that if the
	 * section has no title, this is likely not to be shown.
	 *
	 * @param UI_Icon|NULL $icon
	 * @return $this
	 */
	public function setIcon(?UI_Icon $icon): self
	{
		/* ... */
	}


	/**
	 * Retrieves the section's icon, if any.
	 * @return NULL|UI_Icon
	 */
	public function getIcon(): ?UI_Icon
	{
		/* ... */
	}


	/**
	 * Checks whether the section has an icon set.
	 * @return boolean
	 */
	public function hasIcon(): bool
	{
		/* ... */
	}


	/**
	 * Makes the section static, as in not collapsible. This is the
	 * default behavior - call this after having made a section
	 * collapsible with the {@link makeCollapsible} method.
	 *
	 * @return $this
	 * @see UI_Page_Section::makeCollapsible()
	 */
	public function makeStatic(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeCompact(): self
	{
		/* ... */
	}


	public function isCompact(): bool
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function expand(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function collapse(): self
	{
		/* ... */
	}


	/**
	 * @param bool $collapsed
	 * @return $this
	 */
	public function setCollapsed(bool $collapsed = true): self
	{
		/* ... */
	}


	public function isExpanded(): bool
	{
		/* ... */
	}


	public function getForm(): ?UI_Form
	{
		/* ... */
	}


	public function isCollapsed(): bool
	{
		/* ... */
	}


	/**
	 * Creates and adds a quick selector to the section, which
	 * is shown to the right of the section title.
	 *
	 * @param string $id
	 * @return UI_QuickSelector
	 * @throws Application_Exception
	 */
	public function addQuickSelector(string $id = ''): UI_QuickSelector
	{
		/* ... */
	}


	/**
	 * Sets a previously created quick selector object to use
	 * as quick selection within the section.
	 *
	 * @param UI_QuickSelector $quick
	 * @return $this
	 */
	public function setQuickSelector(UI_QuickSelector $quick): self
	{
		/* ... */
	}


	public function hasQuickSelector(): bool
	{
		/* ... */
	}


	public function getQuickSelector(): ?UI_QuickSelector
	{
		/* ... */
	}


	/**
	 * Adds a context button to the section. These are usually
	 * displayed around the title somewhere - it depends on the
	 * template.
	 *
	 * @param UI_Button|UI_Bootstrap_ButtonDropdown|mixed $button
	 * @return $this
	 * @throws Application_Exception
	 */
	public function addContextButton($button): self
	{
		/* ... */
	}


	/**
	 * @return array<int,UI_Bootstrap_ButtonDropdown|UI_Button>
	 */
	public function getContextButtons(): array
	{
		/* ... */
	}


	/**
	 * Adds a new item selector content to the section, which
	 * can be used to display a list of possible items to choose
	 * from.
	 *
	 * @return UI_ItemsSelector
	 */
	public function addItemsSelector(): UI_ItemsSelector
	{
		/* ... */
	}


	/**
	 * Turns the section into a sidebar section, for use
	 * in the sidebar.
	 *
	 * @return $this
	 */
	public function makeSidebar(): self
	{
		/* ... */
	}


	/**
	 * Turns the section into a subsection made to be used
	 * within a regular page section.
	 *
	 * @return $this
	 */
	public function makeSubsection(): self
	{
		/* ... */
	}


	public static function getJSExpandGroup(string $group): string
	{
		/* ... */
	}


	public static function getJSCollapseGroup(string $group): string
	{
		/* ... */
	}


	/**
	 * Creates a button group that can be used to expand and collapse
	 * all sections of the specified section group.
	 *
	 * @param UI $ui
	 * @param string|NULL $group A group name. If NULL, the default group is used.
	 * @return GroupControls
	 */
	public static function createGroupControls(UI $ui, ?string $group = null): GroupControls
	{
		/* ... */
	}


	public function getJSExpand(): string
	{
		/* ... */
	}


	public function getJSCollapse(): string
	{
		/* ... */
	}


	public function isCollapsible(): bool
	{
		/* ... */
	}


	/**
	 * Adds a form instance to use as content of the section.
	 *
	 * If the section is set to collapsible, it is expanded
	 * automatically if the form has been submitted and is not
	 * valid.
	 *
	 * @param UI_Form $form
	 * @return $this
	 */
	public function addForm(UI_Form $form): self
	{
		/* ... */
	}


	/**
	 * Adds a renderable content to the section's content area.
	 * These will be rendered in the order they are added.
	 *
	 * @param UI_Renderable_Interface $renderable
	 * @return $this
	 */
	public function addRenderable(UI_Renderable_Interface $renderable): self
	{
		/* ... */
	}


	/**
	 * Adds a template to render as content in the section.
	 *
	 * @param string $templateID
	 * @param array<string,mixed> $params
	 * @return $this
	 */
	public function addTemplate(string $templateID, array $params = []): self
	{
		/* ... */
	}


	/**
	 * Adds custom HTML to the section.
	 *
	 * @param string $html
	 * @return $this
	 */
	public function addHTML(string $html): self
	{
		/* ... */
	}


	/**
	 * Adds a separator between other contents.
	 * @return $this
	 */
	public function addSeparator(): self
	{
		/* ... */
	}


	/**
	 * @param string|int|float|UI_Renderable_Interface|NULL $title
	 * @return $this
	 */
	public function addHeading($title): self
	{
		/* ... */
	}


	public function addSubsection(): UI_Page_Section
	{
		/* ... */
	}


	public function isSeparator(): bool
	{
		/* ... */
	}


	/**
	 * Registers the position of the item in the sidebar. Called automatically
	 * by the sidebar before it is rendered.
	 *
	 * @param UI_Page_Sidebar_ItemInterface|null $prev
	 * @param UI_Page_Sidebar_ItemInterface|null $next
	 * @return $this
	 * @see UI_Page_Sidebar::getItems()
	 */
	public function registerPosition(
		?UI_Page_Sidebar_ItemInterface $prev = null,
		?UI_Page_Sidebar_ItemInterface $next = null,
	): self
	{
		/* ... */
	}


	/**
	 * Retrieves the previous item in the sidebar before this one, if any.
	 * @return UI_Page_Sidebar_ItemInterface|NULL
	 */
	public function getPreviousSibling(): ?UI_Page_Sidebar_ItemInterface
	{
		/* ... */
	}


	/**
	 * Retrieves the next item in the sidebar after this one, if any.
	 * @return UI_Page_Sidebar_ItemInterface|NULL
	 */
	public function getNextSibling(): ?UI_Page_Sidebar_ItemInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Section/Content.php`

```php
namespace ;

use AppUtils\Interfaces\OptionableInterface as OptionableInterface;
use AppUtils\Traits\OptionableTrait as OptionableTrait;

/**
 * Base class for section contents: these are specialized
 * content types that are rendered automatically and can
 * be freely added to a section.
 *
 * @package UI
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class UI_Page_Section_Content extends UI_Renderable implements OptionableInterface
{
	use OptionableTrait;
}


```
###  Path: `/src/classes/UI/Page/Section/Content.php`

```php
namespace ;

use AppUtils\Interfaces\OptionableInterface as OptionableInterface;
use AppUtils\Traits\OptionableTrait as OptionableTrait;

/**
 * Base class for section contents: these are specialized
 * content types that are rendered automatically and can
 * be freely added to a section.
 *
 * @package UI
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class UI_Page_Section_Content extends UI_Renderable implements OptionableInterface
{
	use OptionableTrait;
}


```
###  Path: `/src/classes/UI/Page/Section/Content/HTML.php`

```php
namespace ;

class UI_Page_Section_Content_HTML extends UI_Page_Section_Content
{
	public function getDefaultOptions(): array
	{
		/* ... */
	}


	public function getHTML()
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Section/Content/HTML.php`

```php
namespace ;

class UI_Page_Section_Content_HTML extends UI_Page_Section_Content
{
	public function getDefaultOptions(): array
	{
		/* ... */
	}


	public function getHTML()
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Section/Content/Heading.php`

```php
namespace ;

class UI_Page_Section_Content_Heading extends UI_Page_Section_Content
{
	public function getDefaultOptions(): array
	{
		/* ... */
	}


	public function getTitle()
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Section/Content/Heading.php`

```php
namespace ;

class UI_Page_Section_Content_Heading extends UI_Page_Section_Content
{
	public function getDefaultOptions(): array
	{
		/* ... */
	}


	public function getTitle()
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Section/Content/Separator.php`

```php
namespace ;

class UI_Page_Section_Content_Separator extends UI_Page_Section_Content
{
	public function getDefaultOptions(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Section/Content/Separator.php`

```php
namespace ;

class UI_Page_Section_Content_Separator extends UI_Page_Section_Content
{
	public function getDefaultOptions(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Section/Content/Template.php`

```php
namespace ;

class UI_Page_Section_Content_Template extends UI_Page_Section_Content
{
	public function getDefaultOptions(): array
	{
		/* ... */
	}


	public function getTemplateID()
	{
		/* ... */
	}


	public function getParams()
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Section/Content/Template.php`

```php
namespace ;

class UI_Page_Section_Content_Template extends UI_Page_Section_Content
{
	public function getDefaultOptions(): array
	{
		/* ... */
	}


	public function getTemplateID()
	{
		/* ... */
	}


	public function getParams()
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Section/GroupControls.php`

```php
namespace UI\Page\Section;

use Application\Formable\Event\ClientFormRenderedEvent as ClientFormRenderedEvent;
use Application_Formable as Application_Formable;
use Closure as Closure;
use UI as UI;
use UI\Event\PageRendered as PageRendered;
use UI_Bootstrap_ButtonGroup as UI_Bootstrap_ButtonGroup;
use UI_Button as UI_Button;
use UI_Exception as UI_Exception;
use UI_Page_Section as UI_Page_Section;
use UI_Renderable_Interface as UI_Renderable_Interface;
use UI_Traits_RenderableGeneric as UI_Traits_RenderableGeneric;

/**
 * Helper class to handle the rendering of the collapse controls
 * for a section group.
 *
 * @package Application
 * @subpackage UserInterface
 */
class GroupControls implements UI_Renderable_Interface
{
	use UI_Traits_RenderableGeneric;

	public const CONTROLS_PREFIX = 'GROUP_CONTROLS_';

	public function getID(): string
	{
		/* ... */
	}


	/**
	 * Sets a CSS style for the rendered element.
	 *
	 * @param string $name
	 * @param string|int|float|NULL $value
	 * @return $this
	 */
	public function setStyle(string $name, $value): self
	{
		/* ... */
	}


	public function setDisplayThreshold(int $threshold): self
	{
		/* ... */
	}


	public function getDisplayThreshold(): int
	{
		/* ... */
	}


	/**
	 * @param string $class
	 * @return $this
	 */
	public function addClass(string $class): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeMini(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeSmall(): self
	{
		/* ... */
	}


	/**
	 * Sets / changes the name of the section group to render the controls for.
	 * @param string|null $group
	 * @return $this
	 */
	public function setGroup(?string $group): self
	{
		/* ... */
	}


	public function getPlaceholder(): string
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}


	/**
	 * Sets the tooltips to use for the collapse and expand buttons.
	 *
	 * @param string $expand
	 * @param string $collapse
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setTooltips(string $expand, string $collapse): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Section/GroupControls.php`

```php
namespace UI\Page\Section;

use Application\Formable\Event\ClientFormRenderedEvent as ClientFormRenderedEvent;
use Application_Formable as Application_Formable;
use Closure as Closure;
use UI as UI;
use UI\Event\PageRendered as PageRendered;
use UI_Bootstrap_ButtonGroup as UI_Bootstrap_ButtonGroup;
use UI_Button as UI_Button;
use UI_Exception as UI_Exception;
use UI_Page_Section as UI_Page_Section;
use UI_Renderable_Interface as UI_Renderable_Interface;
use UI_Traits_RenderableGeneric as UI_Traits_RenderableGeneric;

/**
 * Helper class to handle the rendering of the collapse controls
 * for a section group.
 *
 * @package Application
 * @subpackage UserInterface
 */
class GroupControls implements UI_Renderable_Interface
{
	use UI_Traits_RenderableGeneric;

	public const CONTROLS_PREFIX = 'GROUP_CONTROLS_';

	public function getID(): string
	{
		/* ... */
	}


	/**
	 * Sets a CSS style for the rendered element.
	 *
	 * @param string $name
	 * @param string|int|float|NULL $value
	 * @return $this
	 */
	public function setStyle(string $name, $value): self
	{
		/* ... */
	}


	public function setDisplayThreshold(int $threshold): self
	{
		/* ... */
	}


	public function getDisplayThreshold(): int
	{
		/* ... */
	}


	/**
	 * @param string $class
	 * @return $this
	 */
	public function addClass(string $class): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeMini(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeSmall(): self
	{
		/* ... */
	}


	/**
	 * Sets / changes the name of the section group to render the controls for.
	 * @param string|null $group
	 * @return $this
	 */
	public function setGroup(?string $group): self
	{
		/* ... */
	}


	public function getPlaceholder(): string
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}


	/**
	 * Sets the tooltips to use for the collapse and expand buttons.
	 *
	 * @param string $expand
	 * @param string $collapse
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setTooltips(string $expand, string $collapse): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Section/SectionsRegistry.php`

```php
namespace UI\Page\Section;

use UI\Event\PageRendered as PageRendered;
use UI_Page_Section as UI_Page_Section;

/**
 * Global registry of sections created in the current request.
 * Offers methods to access the section instances to fetch
 * information about them.
 *
 * > NOTE: This makes the most sense to be used at the end of
 * > the request, when all sections are known. Look at the
 * > event {@see PageRendered} for example.
 *
 * @package Application
 * @subpackage UserInterface
 */
class SectionsRegistry
{
	/**
	 * Registers a section instance.
	 *
	 * @param UI_Page_Section $section
	 * @return void
	 */
	public static function register(UI_Page_Section $section): void
	{
		/* ... */
	}


	public static function getAll(): array
	{
		/* ... */
	}


	/**
	 * @param string $group
	 * @return UI_Page_Section[]
	 */
	public static function getByGroup(string $group): array
	{
		/* ... */
	}


	/**
	 * Fetches all sections that have been rendered in the
	 * target group.
	 *
	 * @param string $group
	 * @return UI_Page_Section[]
	 */
	public static function getRenderedByGroup(string $group): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Section/SectionsRegistry.php`

```php
namespace UI\Page\Section;

use UI\Event\PageRendered as PageRendered;
use UI_Page_Section as UI_Page_Section;

/**
 * Global registry of sections created in the current request.
 * Offers methods to access the section instances to fetch
 * information about them.
 *
 * > NOTE: This makes the most sense to be used at the end of
 * > the request, when all sections are known. Look at the
 * > event {@see PageRendered} for example.
 *
 * @package Application
 * @subpackage UserInterface
 */
class SectionsRegistry
{
	/**
	 * Registers a section instance.
	 *
	 * @param UI_Page_Section $section
	 * @return void
	 */
	public static function register(UI_Page_Section $section): void
	{
		/* ... */
	}


	public static function getAll(): array
	{
		/* ... */
	}


	/**
	 * @param string $group
	 * @return UI_Page_Section[]
	 */
	public static function getByGroup(string $group): array
	{
		/* ... */
	}


	/**
	 * Fetches all sections that have been rendered in the
	 * target group.
	 *
	 * @param string $group
	 * @return UI_Page_Section[]
	 */
	public static function getRenderedByGroup(string $group): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Section/Type/Default.php`

```php
namespace ;

class UI_Page_Section_Type_Default extends UI_Page_Section
{
}


```
###  Path: `/src/classes/UI/Page/Section/Type/Default.php`

```php
namespace ;

class UI_Page_Section_Type_Default extends UI_Page_Section
{
}


```
###  Path: `/src/classes/UI/Page/Section/Type/Developer.php`

```php
namespace ;

class UI_Page_Section_Type_Developer extends UI_Page_Section
{
	/**
	 * Adds a button to the developer panel.
	 *
	 * @param UI_Button $button
	 * @return UI_Page_Section_Type_Developer
	 */
	public function addButton(UI_Button $button)
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Section/Type/Developer.php`

```php
namespace ;

class UI_Page_Section_Type_Developer extends UI_Page_Section
{
	/**
	 * Adds a button to the developer panel.
	 *
	 * @param UI_Button $button
	 * @return UI_Page_Section_Type_Developer
	 */
	public function addButton(UI_Button $button)
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Sidebar.php`

```php
namespace ;

use AppUtils\Interfaces\StringableInterface as StringableInterface;
use Application\FilterSettings\FilterSettingsInterface as FilterSettingsInterface;
use Application\Revisionable\RevisionableInterface as RevisionableInterface;

/**
 * Handles the sidebar in the application's UI. Provides an API to easily
 * add items in the sidebar.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see template_default_frame_sidebar
 */
class UI_Page_Sidebar implements Application_LockableItem_Interface, UI_Renderable_Interface
{
	use Application_Traits_LockableItem;
	use Application_Traits_LockableStatus;
	use UI_Traits_RenderableGeneric;

	public const DEFAULT_ELEMENT_ID = 'sidebar';

	public function getID(): string
	{
		/* ... */
	}


	public function setID(string $id): self
	{
		/* ... */
	}


	public function getInstanceID(): string
	{
		/* ... */
	}


	public function isCollapsed(): bool
	{
		/* ... */
	}


	/**
	 * Collapses the sidebar.
	 * @return $this
	 */
	public function makeCollapsed(): self
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}


	public function display(): void
	{
		/* ... */
	}


	public function hasItems(): bool
	{
		/* ... */
	}


	/**
	 * @return UI_Page_Template
	 * @throws Application_Exception
	 */
	public function getTemplate(): UI_Page_Template
	{
		/* ... */
	}


	/**
	 * @return UI_Page
	 */
	public function getPage(): UI_Page
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @param string|UI_Renderable_Interface|int|float $title
	 * @return UI_Page_Sidebar_Item_Button
	 */
	public function addButton(string $name, $title = ''): UI_Page_Sidebar_Item_Button
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @param string|StringableInterface|NULL $title
	 * @return UI_Page_Sidebar_Item_DropdownButton
	 */
	public function addDropdownButton(string $name, $title = null): UI_Page_Sidebar_Item_DropdownButton
	{
		/* ... */
	}


	/**
	 * Attempts to retrieve a button by its name.
	 *
	 * @param string $name
	 * @return UI_Page_Sidebar_Item_Button|NULL
	 */
	public function getButton(string $name): ?UI_Page_Sidebar_Item_Button
	{
		/* ... */
	}


	/**
	 * Checks if a button with this name exists.
	 * @param string $name
	 * @return bool
	 */
	public function hasButton(string $name): bool
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @param string|UI_Renderable_Interface|int|float $title
	 * @return UI_Page_Sidebar_Item_Button
	 */
	public function createButton(string $name, $title = ''): UI_Page_Sidebar_Item_Button
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @param string|UI_Renderable_Interface|int|float $title
	 * @return UI_Page_Sidebar_Item_DropdownButton
	 */
	public function createDropdownButton(string $name, $title = ''): UI_Page_Sidebar_Item_DropdownButton
	{
		/* ... */
	}


	/**
	 * @return UI_Page_Sidebar_Item_Separator|NULL
	 */
	public function addSeparator(): ?UI_Page_Sidebar_Item_Separator
	{
		/* ... */
	}


	/**
	 * Adds a message informing the user that no changes may be
	 * made to the revisionable if it is in a state that does
	 * not allow modifying it.
	 *
	 * @param RevisionableInterface $revisionable
	 * @return UI_Page_Sidebar_Item_Message
	 */
	public function addRevisionableStateInfo(RevisionableInterface $revisionable): UI_Page_Sidebar_Item_Message
	{
		/* ... */
	}


	/**
	 * Creates a sidebar section
	 * @return UI_Page_Section
	 */
	public function addSection(): UI_Page_Section
	{
		/* ... */
	}


	/**
	 * Adds a table of contents for the specified form.
	 * @param UI_Form $form
	 * @return UI_Page_Sidebar_Item_FormTOC
	 */
	public function addFormTOC(UI_Form $form): UI_Page_Sidebar_Item_FormTOC
	{
		/* ... */
	}


	public function addFormableTOC(Application_Interfaces_Formable $formable): UI_Page_Sidebar_Item_FormTOC
	{
		/* ... */
	}


	/**
	 * Adds the content of any template to the sidebar.
	 *
	 * @param string $templateID
	 * @param array<string,mixed> $params
	 * @return UI_Page_Sidebar_Item_Template
	 */
	public function addTemplate(string $templateID, array $params = []): UI_Page_Sidebar_Item_Template
	{
		/* ... */
	}


	/**
	 * Adds a collapsible help block with a short informational
	 * text for the current page.
	 *
	 * @param string|number|UI_Renderable_Interface|NULL $title
	 * @param string|number|UI_Renderable_Interface|NULL $content
	 * @param bool $startCollapsed
	 * @return UI_Page_Sidebar_Item_Template
	 * @throws UI_Exception
	 */
	public function addHelp($title, $content, bool $startCollapsed = true): UI_Page_Sidebar_Item_Template
	{
		/* ... */
	}


	/**
	 * @param string|StringableInterface|NULL $content
	 * @throws InvalidArgumentException
	 * @return UI_Page_Sidebar_Item_Custom
	 */
	public function addCustom($content): UI_Page_Sidebar_Item_Custom
	{
		/* ... */
	}


	/**
	 * Adds an information-styled message.
	 * @param string|number|UI_Renderable_Interface $message
	 * @param bool $icon
	 * @param bool $dismissable
	 * @return UI_Page_Sidebar_Item_Message
	 */
	public function addInfoMessage($message, bool $icon = false, bool $dismissable = false): UI_Page_Sidebar_Item_Message
	{
		/* ... */
	}


	/**
	 * Adds an error-styled message.
	 * @param string|number|UI_Renderable_Interface $message
	 * @param bool $icon
	 * @param bool $dismissable
	 * @return UI_Page_Sidebar_Item_Message
	 */
	public function addErrorMessage(
		$message,
		bool $icon = false,
		bool $dismissable = false,
	): UI_Page_Sidebar_Item_Message
	{
		/* ... */
	}


	/**
	 * Adds a success-styled message.
	 * @param string|number|UI_Renderable_Interface $message
	 * @param bool $icon
	 * @param bool $dismissable
	 * @return UI_Page_Sidebar_Item_Message
	 */
	public function addSuccessMessage(
		$message,
		bool $icon = false,
		bool $dismissable = false,
	): UI_Page_Sidebar_Item_Message
	{
		/* ... */
	}


	/**
	 * Adds a warning-styled message.
	 * @param string|number|UI_Renderable_Interface $message
	 * @param bool $icon
	 * @param bool $dismissable
	 * @return UI_Page_Sidebar_Item_Message
	 */
	public function addWarningMessage(
		$message,
		bool $icon = false,
		bool $dismissable = false,
	): UI_Page_Sidebar_Item_Message
	{
		/* ... */
	}


	/**
	 * Creates a message item instance and returns it.
	 * @param string|number|UI_Renderable_Interface $message
	 * @param string $type
	 * @param bool $icon
	 * @param bool $dismissable
	 * @return UI_Page_Sidebar_Item_Message
	 */
	public function addMessage(
		$message,
		string $type = UI::MESSAGE_TYPE_INFO,
		bool $icon = false,
		bool $dismissable = false,
	): UI_Page_Sidebar_Item_Message
	{
		/* ... */
	}


	/**
	 * @return UI_Page_Sidebar_Item[]
	 */
	public function getItems(): array
	{
		/* ... */
	}


	public function getUI(): UI
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeLarger(): self
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @return $this
	 */
	public function addClass(string $name): self
	{
		/* ... */
	}


	/**
	 * @return string[]
	 */
	public function getClasses(): array
	{
		/* ... */
	}


	public function isLarge(): bool
	{
		/* ... */
	}


	/**
	 * Adds a sidebar section available only to developers,
	 * and styled as such.
	 *
	 * @return UI_Page_Sidebar_Item_DeveloperPanel
	 */
	public function addDeveloperPanel(): UI_Page_Sidebar_Item_DeveloperPanel
	{
		/* ... */
	}


	public function addFilterSettings(FilterSettingsInterface $settings, $title = null): UI_Page_Sidebar_Item_Template
	{
		/* ... */
	}


	public function setTagName(string $name): self
	{
		/* ... */
	}


	public function getTagName(): string
	{
		/* ... */
	}


	public function makeAllItemsLockable(): self
	{
		/* ... */
	}


	/**
	 * The sidebar is always lockable.
	 * @return bool
	 */
	public function isLockable(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Sidebar/Item.php`

```php
namespace ;

abstract class UI_Page_Sidebar_Item extends UI_Renderable implements UI_Renderable_Interface, UI_Interfaces_Conditional, UI_Page_Sidebar_ItemInterface
{
	use UI_Traits_Conditional;

	public function createTemplate(string $templateIDOrClass): UI_Page_Template
	{
		/* ... */
	}


	/**
	 * Registers the position of the item in the sidebar. Called automatically
	 * by the sidebar before it is rendered.
	 *
	 * @param UI_Page_Sidebar_ItemInterface|null $prev
	 * @param UI_Page_Sidebar_ItemInterface|null $next
	 * @return $this
	 * @see UI_Page_Sidebar::getItems()
	 */
	public function registerPosition(
		?UI_Page_Sidebar_ItemInterface $prev = null,
		?UI_Page_Sidebar_ItemInterface $next = null,
	): self
	{
		/* ... */
	}


	/**
	 * Checks whether this is a separator item.
	 * @return boolean
	 */
	public function isSeparator()
	{
		/* ... */
	}


	/**
	 * Retrieves the previous item in the sidebar before this one, if any.
	 * @return UI_Page_Sidebar_ItemInterface|NULL
	 */
	public function getPreviousSibling()
	{
		/* ... */
	}


	/**
	 * Retrieves the next item in the sidebar after this one, if any.
	 * @return UI_Page_Sidebar_ItemInterface|NULL
	 */
	public function getNextSibling()
	{
		/* ... */
	}
}

interface UI_Page_Sidebar_ItemInterface
{
	public function isSeparator();


	/**
	 * @param UI_Page_Sidebar_ItemInterface|null $prev
	 * @param UI_Page_Sidebar_ItemInterface|null $next
	 * @return $this
	 */
	public function registerPosition(
		?UI_Page_Sidebar_ItemInterface $prev = null,
		?UI_Page_Sidebar_ItemInterface $next = null,
	): self;


	public function getPreviousSibling();


	public function getNextSibling();
}


```
###  Path: `/src/classes/UI/Page/Sidebar/Item.php`

```php
namespace ;

abstract class UI_Page_Sidebar_Item extends UI_Renderable implements UI_Renderable_Interface, UI_Interfaces_Conditional, UI_Page_Sidebar_ItemInterface
{
	use UI_Traits_Conditional;

	public function createTemplate(string $templateIDOrClass): UI_Page_Template
	{
		/* ... */
	}


	/**
	 * Registers the position of the item in the sidebar. Called automatically
	 * by the sidebar before it is rendered.
	 *
	 * @param UI_Page_Sidebar_ItemInterface|null $prev
	 * @param UI_Page_Sidebar_ItemInterface|null $next
	 * @return $this
	 * @see UI_Page_Sidebar::getItems()
	 */
	public function registerPosition(
		?UI_Page_Sidebar_ItemInterface $prev = null,
		?UI_Page_Sidebar_ItemInterface $next = null,
	): self
	{
		/* ... */
	}


	/**
	 * Checks whether this is a separator item.
	 * @return boolean
	 */
	public function isSeparator()
	{
		/* ... */
	}


	/**
	 * Retrieves the previous item in the sidebar before this one, if any.
	 * @return UI_Page_Sidebar_ItemInterface|NULL
	 */
	public function getPreviousSibling()
	{
		/* ... */
	}


	/**
	 * Retrieves the next item in the sidebar after this one, if any.
	 * @return UI_Page_Sidebar_ItemInterface|NULL
	 */
	public function getNextSibling()
	{
		/* ... */
	}
}

interface UI_Page_Sidebar_ItemInterface
{
	public function isSeparator();


	/**
	 * @param UI_Page_Sidebar_ItemInterface|null $prev
	 * @param UI_Page_Sidebar_ItemInterface|null $next
	 * @return $this
	 */
	public function registerPosition(
		?UI_Page_Sidebar_ItemInterface $prev = null,
		?UI_Page_Sidebar_ItemInterface $next = null,
	): self;


	public function getPreviousSibling();


	public function getNextSibling();
}


```
###  Path: `/src/classes/UI/Page/Sidebar/Item/Button.php`

```php
namespace ;

use AppUtils\ClassHelper\BaseClassHelperException as BaseClassHelperException;
use AppUtils\Traits\ClassableTrait as ClassableTrait;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;
use UI\Interfaces\ActivatableInterface as ActivatableInterface;
use UI\Interfaces\ButtonLayoutInterface as ButtonLayoutInterface;
use UI\Traits\ButtonLayoutTrait as ButtonLayoutTrait;

/**
 * A single button in the sidebar.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see template_default_sidebar_button
 */
class UI_Page_Sidebar_Item_Button extends UI_Page_Sidebar_LockableItem implements UI_Interfaces_Button
{
	use Application_Traits_Iconizable;
	use ClassableTrait;
	use UI_Traits_ClientConfirmable;
	use ButtonLayoutTrait;

	public const ERROR_CANNOT_DETERMINE_FORM_NAME = 55301;
	public const STATE_DISABLED = 'disabled';
	public const STATE_ENABLED = 'enabled';
	public const MODE_SUBMIT = 'submit';
	public const MODE_NONE = 'none';
	public const MODE_LINKED = 'linked';
	public const MODE_CLICKABLE = 'clickable';

	public function getName()
	{
		/* ... */
	}


	/**
	 * @param string|AdminURLInterface $url
	 * @param string $target
	 * @return $this
	 * @see UI_Page_Sidebar_Item_Button::makeLinked()
	 */
	public function link($url, string $target = ''): self
	{
		/* ... */
	}


	/**
	 * Makes the button link to the specified URL.
	 *
	 * @param string|AdminURLInterface|array<string,string> $urlOrParams
	 * @param boolean $newWindow Whether to open the link in a new tab/window
	 * @return $this
	 * @throws BaseClassHelperException
	 */
	public function makeLinked($urlOrParams, bool $newWindow = false): self
	{
		/* ... */
	}


	/**
	 * Whether the button's action is to open a URL.
	 * @return boolean
	 */
	public function isLinked(): bool
	{
		/* ... */
	}


	/**
	 * The URL the button links to (if any).
	 * @return string
	 */
	public function getURL(): string
	{
		/* ... */
	}


	/**
	 * @param string $statement
	 * @return $this
	 */
	public function click(string $statement): self
	{
		/* ... */
	}


	/**
	 * Turns the button into a javascript click button, which will
	 * execute the specified javascript code when clicked.
	 *
	 * @param string $javascript
	 * @return $this
	 */
	public function makeClickable(string $javascript): self
	{
		/* ... */
	}


	/**
	 * @return string
	 */
	public function getJavascript(): string
	{
		/* ... */
	}


	/**
	 * Retrieves the name of the form being submitted by
	 * this button (if any).
	 *
	 * @return string
	 */
	public function getFormName(): string
	{
		/* ... */
	}


	/**
	 * Whether the button's action is a javascript statement.
	 * @return boolean
	 */
	public function isClickable(): bool
	{
		/* ... */
	}


	public function isFormSubmit(): bool
	{
		/* ... */
	}


	/**
	 * Makes the button submit the specified form or datagrid on click.
	 *
	 * @param string|UI_Form|UI_DataGrid|Application_Interfaces_Formable $subject A form name, or supported form instance.
	 * @param boolean $simulate Whether to submit in simulation mode.
	 * @return $this
	 * @throws Application_Exception
	 */
	public function makeClickableSubmit($subject, bool $simulate = false)
	{
		/* ... */
	}


	public function setOnClick(string $statement): self
	{
		/* ... */
	}


	/**
	 * Makes the button a submit button.
	 *
	 * @return $this
	 */
	public function makeSubmit(): self
	{
		/* ... */
	}


	public function isSubmittable(): bool
	{
		/* ... */
	}


	/**
	 * Disables the button, so it gets displayed, but not clickable
	 *
	 * @param string|number|UI_Renderable_Interface $reason If specified, adds a tooltip that explains why the button is disabled.
	 * @return $this
	 * @throws UI_Exception
	 *
	 * @see enable()
	 */
	public function disable($reason = ''): self
	{
		/* ... */
	}


	/**
	 * Restore the button's function after a "disable()" call.
	 *
	 * @see disable()
	 * @return $this
	 */
	public function enable(): self
	{
		/* ... */
	}


	/**
	 * Whether the button is disabled.
	 * @return boolean
	 */
	public function isDisabled(): bool
	{
		/* ... */
	}


	public function isDangerous(): bool
	{
		/* ... */
	}


	/**
	 * Sets the button style to use. This depends on what the
	 * template does with it, default is "normal".
	 *
	 * @param string $style
	 * @return $this
	 */
	public function setStyle(string $style): self
	{
		/* ... */
	}


	/**
	 * Sets the tooltip text for the button, which will be
	 * shown in the UI as help for the button's function.
	 *
	 * @param number|string|UI_Renderable_Interface $tooltip
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setTooltip($tooltip): self
	{
		/* ... */
	}


	/**
	 * Sets the value of the button's id attribute, overwrites the default ID.
	 *
	 * @param string $id
	 * @return $this
	 */
	public function setID(string $id): self
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface $label
	 * @return $this
	 */
	public function setLabel($label): self
	{
		/* ... */
	}


	/**
	 * @param number|string|UI_Renderable_Interface $text
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setLoadingText($text): self
	{
		/* ... */
	}


	/**
	 * Does the button have a tooltip text?
	 * @return boolean
	 */
	public function hasTooltip(): bool
	{
		/* ... */
	}


	public function getTooltip(): string
	{
		/* ... */
	}


	public function getID(): string
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	/**
	 * @param bool $active
	 * @return $this
	 */
	public function makeActive(bool $active = true): self
	{
		/* ... */
	}


	/**
	 * @return false
	 */
	public function isActive(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Sidebar/Item/Button.php`

```php
namespace ;

use AppUtils\ClassHelper\BaseClassHelperException as BaseClassHelperException;
use AppUtils\Traits\ClassableTrait as ClassableTrait;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;
use UI\Interfaces\ActivatableInterface as ActivatableInterface;
use UI\Interfaces\ButtonLayoutInterface as ButtonLayoutInterface;
use UI\Traits\ButtonLayoutTrait as ButtonLayoutTrait;

/**
 * A single button in the sidebar.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see template_default_sidebar_button
 */
class UI_Page_Sidebar_Item_Button extends UI_Page_Sidebar_LockableItem implements UI_Interfaces_Button
{
	use Application_Traits_Iconizable;
	use ClassableTrait;
	use UI_Traits_ClientConfirmable;
	use ButtonLayoutTrait;

	public const ERROR_CANNOT_DETERMINE_FORM_NAME = 55301;
	public const STATE_DISABLED = 'disabled';
	public const STATE_ENABLED = 'enabled';
	public const MODE_SUBMIT = 'submit';
	public const MODE_NONE = 'none';
	public const MODE_LINKED = 'linked';
	public const MODE_CLICKABLE = 'clickable';

	public function getName()
	{
		/* ... */
	}


	/**
	 * @param string|AdminURLInterface $url
	 * @param string $target
	 * @return $this
	 * @see UI_Page_Sidebar_Item_Button::makeLinked()
	 */
	public function link($url, string $target = ''): self
	{
		/* ... */
	}


	/**
	 * Makes the button link to the specified URL.
	 *
	 * @param string|AdminURLInterface|array<string,string> $urlOrParams
	 * @param boolean $newWindow Whether to open the link in a new tab/window
	 * @return $this
	 * @throws BaseClassHelperException
	 */
	public function makeLinked($urlOrParams, bool $newWindow = false): self
	{
		/* ... */
	}


	/**
	 * Whether the button's action is to open a URL.
	 * @return boolean
	 */
	public function isLinked(): bool
	{
		/* ... */
	}


	/**
	 * The URL the button links to (if any).
	 * @return string
	 */
	public function getURL(): string
	{
		/* ... */
	}


	/**
	 * @param string $statement
	 * @return $this
	 */
	public function click(string $statement): self
	{
		/* ... */
	}


	/**
	 * Turns the button into a javascript click button, which will
	 * execute the specified javascript code when clicked.
	 *
	 * @param string $javascript
	 * @return $this
	 */
	public function makeClickable(string $javascript): self
	{
		/* ... */
	}


	/**
	 * @return string
	 */
	public function getJavascript(): string
	{
		/* ... */
	}


	/**
	 * Retrieves the name of the form being submitted by
	 * this button (if any).
	 *
	 * @return string
	 */
	public function getFormName(): string
	{
		/* ... */
	}


	/**
	 * Whether the button's action is a javascript statement.
	 * @return boolean
	 */
	public function isClickable(): bool
	{
		/* ... */
	}


	public function isFormSubmit(): bool
	{
		/* ... */
	}


	/**
	 * Makes the button submit the specified form or datagrid on click.
	 *
	 * @param string|UI_Form|UI_DataGrid|Application_Interfaces_Formable $subject A form name, or supported form instance.
	 * @param boolean $simulate Whether to submit in simulation mode.
	 * @return $this
	 * @throws Application_Exception
	 */
	public function makeClickableSubmit($subject, bool $simulate = false)
	{
		/* ... */
	}


	public function setOnClick(string $statement): self
	{
		/* ... */
	}


	/**
	 * Makes the button a submit button.
	 *
	 * @return $this
	 */
	public function makeSubmit(): self
	{
		/* ... */
	}


	public function isSubmittable(): bool
	{
		/* ... */
	}


	/**
	 * Disables the button, so it gets displayed, but not clickable
	 *
	 * @param string|number|UI_Renderable_Interface $reason If specified, adds a tooltip that explains why the button is disabled.
	 * @return $this
	 * @throws UI_Exception
	 *
	 * @see enable()
	 */
	public function disable($reason = ''): self
	{
		/* ... */
	}


	/**
	 * Restore the button's function after a "disable()" call.
	 *
	 * @see disable()
	 * @return $this
	 */
	public function enable(): self
	{
		/* ... */
	}


	/**
	 * Whether the button is disabled.
	 * @return boolean
	 */
	public function isDisabled(): bool
	{
		/* ... */
	}


	public function isDangerous(): bool
	{
		/* ... */
	}


	/**
	 * Sets the button style to use. This depends on what the
	 * template does with it, default is "normal".
	 *
	 * @param string $style
	 * @return $this
	 */
	public function setStyle(string $style): self
	{
		/* ... */
	}


	/**
	 * Sets the tooltip text for the button, which will be
	 * shown in the UI as help for the button's function.
	 *
	 * @param number|string|UI_Renderable_Interface $tooltip
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setTooltip($tooltip): self
	{
		/* ... */
	}


	/**
	 * Sets the value of the button's id attribute, overwrites the default ID.
	 *
	 * @param string $id
	 * @return $this
	 */
	public function setID(string $id): self
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface $label
	 * @return $this
	 */
	public function setLabel($label): self
	{
		/* ... */
	}


	/**
	 * @param number|string|UI_Renderable_Interface $text
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setLoadingText($text): self
	{
		/* ... */
	}


	/**
	 * Does the button have a tooltip text?
	 * @return boolean
	 */
	public function hasTooltip(): bool
	{
		/* ... */
	}


	public function getTooltip(): string
	{
		/* ... */
	}


	public function getID(): string
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	/**
	 * @param bool $active
	 * @return $this
	 */
	public function makeActive(bool $active = true): self
	{
		/* ... */
	}


	/**
	 * @return false
	 */
	public function isActive(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Sidebar/Item/Custom.php`

```php
namespace ;

use AppUtils\Interfaces\StringableInterface as StringableInterface;

class UI_Page_Sidebar_Item_Custom extends UI_Page_Sidebar_LockableItem
{
}


```
###  Path: `/src/classes/UI/Page/Sidebar/Item/Custom.php`

```php
namespace ;

use AppUtils\Interfaces\StringableInterface as StringableInterface;

class UI_Page_Sidebar_Item_Custom extends UI_Page_Sidebar_LockableItem
{
}


```
###  Path: `/src/classes/UI/Page/Sidebar/Item/DeveloperPanel.php`

```php
namespace ;

use Application\Application as Application;

class UI_Page_Sidebar_Item_DeveloperPanel extends UI_Page_Sidebar_Item
{
	public const ERROR_SOURCE_BUTTON_NOT_LINKED = 20601;

	/**
	 * Adds a button by converting an existing sidebar button
	 * to a developer button. Keeps the original button's
	 * settings (works with linked and form submit buttons).
	 *
	 * @param string $buttonName
	 * @throws Application_Exception
	 * @return UI_Page_Sidebar_Item_DeveloperPanel
	 */
	public function addConvertedButton($buttonName)
	{
		/* ... */
	}


	/**
	 * Adds a button to submit a form, formable or datagrid.
	 *
	 * @param string|UI_Form|UI_DataGrid|Application_Formable $subject
	 * @return UI_Page_Sidebar_Item_DeveloperPanel
	 */
	public function addSubmitButton($subject)
	{
		/* ... */
	}


	public function addButton(UI_Button $button)
	{
		/* ... */
	}


	public function addSeparator()
	{
		/* ... */
	}


	public function addHTML($code)
	{
		/* ... */
	}


	public function addHeading($title)
	{
		/* ... */
	}


	public function appendContent($content)
	{
		/* ... */
	}


	/**
	 * @return UI_Page_Section_Type_Developer
	 */
	public function getSection(): UI_Page_Section_Type_Developer
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Sidebar/Item/DeveloperPanel.php`

```php
namespace ;

use Application\Application as Application;

class UI_Page_Sidebar_Item_DeveloperPanel extends UI_Page_Sidebar_Item
{
	public const ERROR_SOURCE_BUTTON_NOT_LINKED = 20601;

	/**
	 * Adds a button by converting an existing sidebar button
	 * to a developer button. Keeps the original button's
	 * settings (works with linked and form submit buttons).
	 *
	 * @param string $buttonName
	 * @throws Application_Exception
	 * @return UI_Page_Sidebar_Item_DeveloperPanel
	 */
	public function addConvertedButton($buttonName)
	{
		/* ... */
	}


	/**
	 * Adds a button to submit a form, formable or datagrid.
	 *
	 * @param string|UI_Form|UI_DataGrid|Application_Formable $subject
	 * @return UI_Page_Sidebar_Item_DeveloperPanel
	 */
	public function addSubmitButton($subject)
	{
		/* ... */
	}


	public function addButton(UI_Button $button)
	{
		/* ... */
	}


	public function addSeparator()
	{
		/* ... */
	}


	public function addHTML($code)
	{
		/* ... */
	}


	public function addHeading($title)
	{
		/* ... */
	}


	public function appendContent($content)
	{
		/* ... */
	}


	/**
	 * @return UI_Page_Section_Type_Developer
	 */
	public function getSection(): UI_Page_Section_Type_Developer
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Sidebar/Item/DropdownButton.php`

```php
namespace ;

use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

/**
 * A dropdown button with a submenu.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Page_Sidebar_Item_DropdownButton extends UI_Page_Sidebar_Item_Button
{
	public const MODE_DROPDOWN_MENU = 'dropmenu';

	/**
	 * Adds a link to the dropdown menu.
	 *
	 * @param string|number|UI_Renderable_Interface|NULL $label
	 * @param string|AdminURLInterface $url
	 * @return UI_Bootstrap_DropdownAnchor
	 * @throws UI_Exception
	 */
	public function addLink($label, $url): UI_Bootstrap_DropdownAnchor
	{
		/* ... */
	}


	/**
	 * Adds a header to the dropdown menu.
	 *
	 * @param string|number|UI_Renderable_Interface|NULL $label
	 * @return UI_Bootstrap_DropdownHeader
	 */
	public function addHeader($label): UI_Bootstrap_DropdownHeader
	{
		/* ... */
	}


	public function getMenu(): UI_Bootstrap_DropdownMenu
	{
		/* ... */
	}


	public function hasCaret(): bool
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function noCaret(): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Sidebar/Item/DropdownButton.php`

```php
namespace ;

use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

/**
 * A dropdown button with a submenu.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Page_Sidebar_Item_DropdownButton extends UI_Page_Sidebar_Item_Button
{
	public const MODE_DROPDOWN_MENU = 'dropmenu';

	/**
	 * Adds a link to the dropdown menu.
	 *
	 * @param string|number|UI_Renderable_Interface|NULL $label
	 * @param string|AdminURLInterface $url
	 * @return UI_Bootstrap_DropdownAnchor
	 * @throws UI_Exception
	 */
	public function addLink($label, $url): UI_Bootstrap_DropdownAnchor
	{
		/* ... */
	}


	/**
	 * Adds a header to the dropdown menu.
	 *
	 * @param string|number|UI_Renderable_Interface|NULL $label
	 * @return UI_Bootstrap_DropdownHeader
	 */
	public function addHeader($label): UI_Bootstrap_DropdownHeader
	{
		/* ... */
	}


	public function getMenu(): UI_Bootstrap_DropdownMenu
	{
		/* ... */
	}


	public function hasCaret(): bool
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function noCaret(): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Sidebar/Item/FormTOC.php`

```php
namespace ;

class UI_Page_Sidebar_Item_FormTOC extends UI_Page_Sidebar_Item
{
}


```
###  Path: `/src/classes/UI/Page/Sidebar/Item/FormTOC.php`

```php
namespace ;

class UI_Page_Sidebar_Item_FormTOC extends UI_Page_Sidebar_Item
{
}


```
###  Path: `/src/classes/UI/Page/Sidebar/Item/Message.php`

```php
namespace ;

class UI_Page_Sidebar_Item_Message extends UI_Page_Sidebar_LockableItem
{
	/**
	 * Sets the message, replacing the existing message if any.
	 *
	 * @param string|number|UI_Renderable_Interface $message
	 * @return UI_Page_Sidebar_Item_Message
	 */
	public function setMessage($message): UI_Page_Sidebar_Item_Message
	{
		/* ... */
	}


	public function isValid(): bool
	{
		/* ... */
	}


	public function makeSlimLayout()
	{
		/* ... */
	}


	public function makeLargeLayout()
	{
		/* ... */
	}


	public function makeDefaultLayout()
	{
		/* ... */
	}


	public function isSlimLayout()
	{
		/* ... */
	}


	public function makeDismissable()
	{
		/* ... */
	}


	public function makeNotDismissable()
	{
		/* ... */
	}


	public function enableIcon(bool $icon = true)
	{
		/* ... */
	}


	public function setCustomIcon(UI_Icon $icon)
	{
		/* ... */
	}


	public function disableIcon()
	{
		/* ... */
	}


	public function makeInfo()
	{
		/* ... */
	}


	public function makeWarning()
	{
		/* ... */
	}


	public function makeError()
	{
		/* ... */
	}


	public function makeSuccess()
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Sidebar/Item/Message.php`

```php
namespace ;

class UI_Page_Sidebar_Item_Message extends UI_Page_Sidebar_LockableItem
{
	/**
	 * Sets the message, replacing the existing message if any.
	 *
	 * @param string|number|UI_Renderable_Interface $message
	 * @return UI_Page_Sidebar_Item_Message
	 */
	public function setMessage($message): UI_Page_Sidebar_Item_Message
	{
		/* ... */
	}


	public function isValid(): bool
	{
		/* ... */
	}


	public function makeSlimLayout()
	{
		/* ... */
	}


	public function makeLargeLayout()
	{
		/* ... */
	}


	public function makeDefaultLayout()
	{
		/* ... */
	}


	public function isSlimLayout()
	{
		/* ... */
	}


	public function makeDismissable()
	{
		/* ... */
	}


	public function makeNotDismissable()
	{
		/* ... */
	}


	public function enableIcon(bool $icon = true)
	{
		/* ... */
	}


	public function setCustomIcon(UI_Icon $icon)
	{
		/* ... */
	}


	public function disableIcon()
	{
		/* ... */
	}


	public function makeInfo()
	{
		/* ... */
	}


	public function makeWarning()
	{
		/* ... */
	}


	public function makeError()
	{
		/* ... */
	}


	public function makeSuccess()
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Sidebar/Item/Separator.php`

```php
namespace ;

class UI_Page_Sidebar_Item_Separator extends UI_Page_Sidebar_Item
{
}


```
###  Path: `/src/classes/UI/Page/Sidebar/Item/Separator.php`

```php
namespace ;

class UI_Page_Sidebar_Item_Separator extends UI_Page_Sidebar_Item
{
}


```
###  Path: `/src/classes/UI/Page/Sidebar/Item/Template.php`

```php
namespace ;

class UI_Page_Sidebar_Item_Template extends UI_Page_Sidebar_LockableItem
{
	/**
	 * @return UI_Page_Template
	 */
	public function getTemplate(): UI_Page_Template
	{
		/* ... */
	}


	public function setVars($vars)
	{
		/* ... */
	}


	public function setVar($name, $value)
	{
		/* ... */
	}


	public function disable()
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Sidebar/Item/Template.php`

```php
namespace ;

class UI_Page_Sidebar_Item_Template extends UI_Page_Sidebar_LockableItem
{
	/**
	 * @return UI_Page_Template
	 */
	public function getTemplate(): UI_Page_Template
	{
		/* ... */
	}


	public function setVars($vars)
	{
		/* ... */
	}


	public function setVar($name, $value)
	{
		/* ... */
	}


	public function disable()
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Sidebar/LockableItem.php`

```php
namespace ;

abstract class UI_Page_Sidebar_LockableItem extends UI_Page_Sidebar_Item implements Application_LockableItem_Interface
{
	use Application_Traits_LockableItem;
	use Application_Traits_LockableStatus;
}


```
###  Path: `/src/classes/UI/Page/Sidebar/LockableItem.php`

```php
namespace ;

abstract class UI_Page_Sidebar_LockableItem extends UI_Page_Sidebar_Item implements Application_LockableItem_Interface
{
	use Application_Traits_LockableItem;
	use Application_Traits_LockableStatus;
}


```
###  Path: `/src/classes/UI/Page/StepsNavigator.php`

```php
namespace ;

use AppUtils\Interfaces\StringableInterface as StringableInterface;

/**
 * Helper class used to generate the HTML markup for displaying a
 * wizard's step by step navigation.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Page_StepsNavigator implements UI_Renderable_Interface
{
	use UI_Traits_RenderableGeneric;

	public const ERROR_NO_STEPS_TO_SELECT = 556001;
	public const ERROR_UNKNOWN_STEP = 556002;
	public const OPTION_NUMBERED = 'numbered';

	public function getPage(): UI_Page
	{
		/* ... */
	}


	/**
	 * Adds a step to the navigator.
	 * @param string $name
	 * @param string|number|StringableInterface|NULL $label
	 * @return UI_Page_StepsNavigator_Step
	 * @throws UI_Exception
	 */
	public function addStep(string $name, $label): UI_Page_StepsNavigator_Step
	{
		/* ... */
	}


	/**
	 * Selects the step that should be marked as active in the
	 * navigator.
	 *
	 * @param string $name
	 * @throws UI_Exception
	 * @return $this
	 */
	public function selectStep(string $name): self
	{
		/* ... */
	}


	/**
	 * Retrieves the name of the step currently selected (marked
	 * as active) in the navigator. If none has been specifically
	 * selected, this will be the first in the list.
	 *
	 * @throws UI_Exception
	 * @return string
	 */
	public function getSelectedName(): string
	{
		/* ... */
	}


	public function isStepSelected(UI_Page_StepsNavigator_Step $step): bool
	{
		/* ... */
	}


	/**
	 * Retrieves the currently selected (marked as active) step
	 * object instance. If none has been specifically selected,
	 * this will be the first in the list.
	 *
	 * @return UI_Page_StepsNavigator_Step
	 * @throws UI_Exception
	 */
	public function getSelectedStep(): UI_Page_StepsNavigator_Step
	{
		/* ... */
	}


	public function isNumbered(): bool
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}


	/**
	 * Adds numbers to each step.
	 * @return $this
	 */
	public function makeNumbered(): self
	{
		/* ... */
	}


	/**
	 * Sets a navigator option.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return $this
	 */
	public function setOption(string $name, $value): self
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @param mixed $default
	 * @return mixed|null
	 */
	public function getOption(string $name, $default = null)
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/StepsNavigator/Step.php`

```php
namespace ;

use AppUtils\Interfaces\StringableInterface as StringableInterface;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

/**
 * Container for individual steps in the navigator.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Page_StepsNavigator_Step
{
	public function getName(): string
	{
		/* ... */
	}


	/**
	 * @param string $id
	 * @return $this
	 */
	public function setID(string $id): self
	{
		/* ... */
	}


	public function getID(): string
	{
		/* ... */
	}


	public function addClass(string $class): self
	{
		/* ... */
	}


	public function hasClass(string $class): bool
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @param string|int|float|NULL $value
	 * @return $this
	 */
	public function setAttribute(string $name, $value): self
	{
		/* ... */
	}


	public function getAttribute(string $name)
	{
		/* ... */
	}


	/**
	 * Turns the step into a linked text.
	 * @param string|AdminURLInterface $url
	 * @return $this
	 */
	public function link($url): self
	{
		/* ... */
	}


	public function isEnabled(): bool
	{
		/* ... */
	}


	/**
	 * @param bool $enabled
	 * @return $this
	 */
	public function setEnabled(bool $enabled = true): self
	{
		/* ... */
	}


	/**
	 * Enables the step, so it becomes clickable in the UI.
	 * @return $this
	 */
	public function makeEnabled(): self
	{
		/* ... */
	}


	/**
	 * Enables the step and marks it as the active one.
	 *
	 * @return $this
	 * @throws UI_Exception
	 */
	public function makeActive(): self
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/StepsNavigator/Step.php`

```php
namespace ;

use AppUtils\Interfaces\StringableInterface as StringableInterface;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

/**
 * Container for individual steps in the navigator.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Page_StepsNavigator_Step
{
	public function getName(): string
	{
		/* ... */
	}


	/**
	 * @param string $id
	 * @return $this
	 */
	public function setID(string $id): self
	{
		/* ... */
	}


	public function getID(): string
	{
		/* ... */
	}


	public function addClass(string $class): self
	{
		/* ... */
	}


	public function hasClass(string $class): bool
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @param string|int|float|NULL $value
	 * @return $this
	 */
	public function setAttribute(string $name, $value): self
	{
		/* ... */
	}


	public function getAttribute(string $name)
	{
		/* ... */
	}


	/**
	 * Turns the step into a linked text.
	 * @param string|AdminURLInterface $url
	 * @return $this
	 */
	public function link($url): self
	{
		/* ... */
	}


	public function isEnabled(): bool
	{
		/* ... */
	}


	/**
	 * @param bool $enabled
	 * @return $this
	 */
	public function setEnabled(bool $enabled = true): self
	{
		/* ... */
	}


	/**
	 * Enables the step, so it becomes clickable in the UI.
	 * @return $this
	 */
	public function makeEnabled(): self
	{
		/* ... */
	}


	/**
	 * Enables the step and marks it as the active one.
	 *
	 * @return $this
	 * @throws UI_Exception
	 */
	public function makeActive(): self
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Subtitle.php`

```php
namespace ;

class UI_Page_Subtitle extends UI_Page_Title
{
}


```
###  Path: `/src/classes/UI/Page/Template.php`

```php
namespace ;

use AppUtils\ClassHelper as ClassHelper;
use AppUtils\FileHelper as FileHelper;
use AppUtils\OutputBuffering as OutputBuffering;
use Application\Application as Application;
use UI\Interfaces\PageTemplateInterface as PageTemplateInterface;

/**
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Page_Template extends UI_Renderable implements PageTemplateInterface
{
	public const ERROR_TEMPLATE_FILE_NOT_FOUND = 27301;
	public const ERROR_NOT_EXPECTED_OBJECT_INSTANCE = 27302;

	public function setVars(array $vars): UI_Page_Template
	{
		/* ... */
	}


	/**
	 * @param string $message
	 * @param array<string,mixed> $options
	 * @return string
	 */
	public function renderSuccessMessage(string $message, array $options = []): string
	{
		/* ... */
	}


	/**
	 * @param string $message
	 * @param string $type
	 * @param array<string,mixed> $options
	 * @return string
	 */
	public function renderMessage(string $message, string $type, array $options = []): string
	{
		/* ... */
	}


	/**
	 * @param string $message
	 * @param array<string,mixed> $options
	 * @return string
	 */
	public function renderInfoMessage(string $message, array $options = []): string
	{
		/* ... */
	}


	/**
	 * @param string $message
	 * @param array<string,mixed> $options
	 * @return string
	 */
	public function renderErrorMessage(string $message, array $options = []): string
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @param mixed $value
	 * @return $this
	 */
	public function setVar(string $name, $value): self
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @param mixed|NULL $default
	 * @return mixed|NULL
	 */
	public function getVar(string $name, $default = null)
	{
		/* ... */
	}


	/**
	 * @template ClassInstanceType
	 * @param string $name
	 * @param class-string<ClassInstanceType> $className
	 * @return ClassInstanceType
	 *
	 * @throws ClassHelper\ClassNotExistsException
	 * @throws ClassHelper\ClassNotImplementsException
	 */
	public function getObjectVar(string $name, string $className)
	{
		/* ... */
	}


	public function getBoolVar(string $name): bool
	{
		/* ... */
	}


	public function getArrayVar(string $name): array
	{
		/* ... */
	}


	public function getStringVar(string $name): string
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @param mixed|NULL $default
	 * @return $this
	 */
	public function printVar(string $name, $default = null): self
	{
		/* ... */
	}


	public function getLogoutURL(): string
	{
		/* ... */
	}


	/**
	 * @param array<string,string> $params
	 * @return string
	 */
	public function buildURL(array $params = []): string
	{
		/* ... */
	}


	public function getImageURL(string $imageName): string
	{
		/* ... */
	}


	/**
	 * @param string $templateID
	 * @param array<string,mixed> $params
	 * @return $this
	 * @throws Application_Exception
	 */
	public function printTemplate(string $templateID, array $params = []): self
	{
		/* ... */
	}


	public function hasVar(string $name): bool
	{
		/* ... */
	}


	public function hasVarNonEmpty(string $name): bool
	{
		/* ... */
	}


	/**
	 * Renders the content template with sidebar.
	 *
	 * @param string $content
	 * @param string $title
	 * @param string $titleRight HTML content to float on the right of the title
	 * @return string
	 */
	public function renderContentWithSidebar(string $content, string $title = '', string $titleRight = '')
	{
		/* ... */
	}


	/**
	 * Renders the content template with sidebar and echos it.
	 *
	 * @param string $content
	 * @param string $title
	 * @param string $titleRight
	 * @return $this
	 *
	 * @throws Application_Exception
	 */
	public function printContentWithSidebar(string $content, string $title = '', string $titleRight = ''): self
	{
		/* ... */
	}


	/**
	 * Renders the content template without sidebar.
	 *
	 * @param string $content
	 * @param string $title
	 */
	public function renderContentWithoutSidebar($content, $title = null, $titleRight = null)
	{
		/* ... */
	}


	/**
	 * Renders the content template without sidebar and echos it.
	 *
	 * @param string $content
	 * @param string $title
	 */
	public function printContentWithoutSidebar($content, $title = null, $titleRight = null)
	{
		/* ... */
	}


	/**
	 * Renders a content section with the specified content and
	 * optional title. For more configuration options, consider
	 * using the {@link createSection} method to work with a
	 * section helper class instance directly.
	 *
	 * @param string $content
	 * @param string $title
	 * @param string $abstract
	 * @return string
	 * @see printSection()
	 * @see createSection()
	 */
	public function renderSection($content, $title = null, $abstract = null)
	{
		/* ... */
	}


	/**
	 * Like {@link renderSection()} but echos the generated content.
	 *
	 * @param string $content
	 * @param string $title
	 * @param string $abstract
	 * @see renderSection()
	 */
	public function printSection($content, $title = null, $abstract = null)
	{
		/* ... */
	}


	/**
	 * Creates a new page section object that can be used to
	 * configure a section further than the renderSection
	 * method allows.
	 *
	 * @return UI_Page_Section
	 */
	public function createSection(): UI_Page_Section
	{
		/* ... */
	}


	/**
	 * Renders an empty body with custom markup.
	 * @param string $html
	 * @return string
	 */
	public function renderCleanFrame($html)
	{
		/* ... */
	}


	public function getAppNameShort(): string
	{
		/* ... */
	}


	public function getAppName(): string
	{
		/* ... */
	}


	public function startOutput(): self
	{
		/* ... */
	}


	public function endOutput(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Page/Template/Custom.php`

```php
namespace ;

abstract class UI_Page_Template_Custom extends UI_Page_Template
{
}


```
###  Path: `/src/classes/UI/Page/Template/Custom.php`

```php
namespace ;

abstract class UI_Page_Template_Custom extends UI_Page_Template
{
}


```
###  Path: `/src/classes/UI/Page/Title.php`

```php
namespace ;

use AppUtils\Interfaces\ClassableInterface as ClassableInterface;
use AppUtils\Traits\ClassableTrait as ClassableTrait;

class UI_Page_Title extends UI_Renderable implements Application_Interfaces_Iconizable, ClassableInterface
{
	use Application_Traits_Iconizable;
	use ClassableTrait;

	/**
	 * Sets the title text.
	 *
	 * @param string|number|UI_Renderable_Interface $text
	 * @return UI_Page_Title
	 */
	public function setText($text): UI_Page_Title
	{
		/* ... */
	}


	/**
	 * Adds a bit of text that will be appended to the text.
	 *
	 * The advantage of using this instead of adding it to the
	 * text itself and using setText() is that these bits of
	 * text stay separate - the original text can still be
	 * retrieved with getText().
	 *
	 * NOTE: Empty strings or NULL values are ignored.
	 *
	 * @param string|number|UI_Renderable_Interface|NULL $text
	 * @return UI_Page_Title
	 * @throws UI_Exception
	 */
	public function addTextAppend($text): UI_Page_Title
	{
		/* ... */
	}


	public function hasAppends(): bool
	{
		/* ... */
	}


	public function getAppends(): array
	{
		/* ... */
	}


	/**
	 * Sets a subline to the title that is shown directly beneath, in a much smaller text.
	 *
	 * @param string|number|UI_Renderable_Interface $subline
	 * @return UI_Page_Title
	 */
	public function setSubline($subline): UI_Page_Title
	{
		/* ... */
	}


	/**
	 * Adds a subline, appending it to any already existing sublines.
	 *
	 * @param string|number|UI_Renderable_Interface|NULL $subline
	 * @return UI_Page_Title
	 * @throws UI_Exception
	 */
	public function addSubline($subline): UI_Page_Title
	{
		/* ... */
	}


	public function getBaseClass(): string
	{
		/* ... */
	}


	/**
	 * Whether a subline text has been set.
	 *
	 * @return bool
	 */
	public function hasSubline(): bool
	{
		/* ... */
	}


	public function getSubline(): string
	{
		/* ... */
	}


	public function getText(): string
	{
		/* ... */
	}


	public function hasText(): bool
	{
		/* ... */
	}


	/**
	 * Retrieves the name of the HTML tag to use for the title.
	 *
	 * @return string
	 */
	public function getTagName(): string
	{
		/* ... */
	}


	/**
	 * Adds a badge that is displayed next to the title.
	 *
	 * @param UI_Interfaces_Badge|NULL $badge Accepts null values for method chaining without additional checks.
	 * @return UI_Page_Title
	 */
	public function addBadge(?UI_Interfaces_Badge $badge): UI_Page_Title
	{
		/* ... */
	}


	/**
	 * @param UI_Interfaces_Badge|null $badge Accepts null values for method chaining without additional checks.
	 * @return $this
	 */
	public function prependBadge(?UI_Interfaces_Badge $badge): UI_Page_Title
	{
		/* ... */
	}


	/**
	 * Whether the title has any badges attached.
	 *
	 * @return bool
	 */
	public function hasBadges(): bool
	{
		/* ... */
	}


	/**
	 * Retrieves all badges that have been added to the title.
	 *
	 * @return UI_Interfaces_Badge[]
	 */
	public function getBadges(): array
	{
		/* ... */
	}


	/**
	 * Adds a context element to the title, like a badge.
	 *
	 * @param UI_Renderable_Interface|null $element Allows null values as utility for method chaining without additional checks.
	 * @return $this
	 */
	public function addContextElement(?UI_Renderable_Interface $element): UI_Page_Title
	{
		/* ... */
	}


	/**
	 * Whether any context elements were added (buttons, menus, etc).
	 *
	 * @return bool
	 */
	public function hasContextElements(): bool
	{
		/* ... */
	}


	/**
	 * Retrieves all context elements that were added.
	 *
	 * @return UI_Renderable_Interface[]
	 */
	public function getContextElements()
	{
		/* ... */
	}


	/**
	 * Whether the title has enough information to be displayed.
	 *
	 * @return bool
	 */
	public function isValid(): bool
	{
		/* ... */
	}
}


```
---
**File Statistics**
- **Size**: 164.08 KB
- **Lines**: 9192
File: `modules/ui/page/architecture.md`
