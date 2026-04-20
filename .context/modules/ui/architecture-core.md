# UI Module - Core Architecture
_SOURCE: UI singleton, root-level components, interfaces and traits_
# UI singleton, root-level components, interfaces and traits
```
// Structure of documents
└── src/
    └── classes/
        └── UI/
            └── Badge.php
            └── BaseLockable.php
            └── BaseUIEvent.php
            └── Bootstrap.php
            └── Button.php
            └── CSSClasses.php
            └── CSSGenerator/
                ├── CSSGen.php
                ├── CSSGenException.php
                ├── CSSGenFile.php
                ├── CSSGenLocation.php
            └── ClientConfirmable/
                ├── Message.php
            └── ClientResource.php
            └── ClientResourceCollection.php
            └── CriticalityEnum.php
            └── DataGrid.php
            └── Event/
                ├── FormCreatedEvent.php
                ├── PageRendered.php
            └── Exception.php
            └── Form.php
            └── HTMLElement.php
            └── Icon.php
            └── Icons/
                ├── IconCollection.php
                ├── IconInfo.php
            └── Interfaces/
                ├── ActivatableInterface.php
                ├── Badge.php
                ├── Bootstrap.php
                ├── Button.php
                ├── ButtonLayoutInterface.php
                ├── ButtonSizeInterface.php
                ├── CapturableInterface.php
                ├── ClientConfirmable.php
                ├── Conditional.php
                ├── ListBuilderInterface.php
                ├── MessageLayoutInterface.php
                ├── MessageWrapperInterface.php
                ├── NamedItemInterface.php
                ├── PageTemplateInterface.php
                ├── Renderable.php
                ├── StatusElementContainer.php
                ├── Statuses/
                │   ├── Status.php
                ├── TooltipableInterface.php
            └── ItemsSelector.php
            └── JSHelper.php
            └── Label.php
            └── MarkupEditor.php
            └── MarkupEditorInfo.php
            └── Message.php
            └── Page.php
            └── PaginationRenderer.php
            └── PrettyBool.php
            └── PropertiesGrid.php
            └── QuickSelector.php
            └── Renderable.php
            └── ResourceManager.php
            └── Statuses.php
            └── Statuses/
                ├── Generic.php
                ├── GenericSelectable.php
                ├── Selectable.php
                ├── Status.php
            └── StringBuilder.php
            └── SystemHint.php
            └── Targets/
                ├── BaseTarget.php
                ├── ClickTarget.php
                ├── URLTarget.php
            └── Themes.php
            └── TooltipInfo.php
            └── Traits/
                ├── ActivatableTrait.php
                ├── ButtonDecoratorInterface.php
                ├── ButtonDecoratorTrait.php
                ├── ButtonLayoutTrait.php
                ├── ButtonSizeTrait.php
                ├── CapturableTrait.php
                ├── ClientConfirmable.php
                ├── Conditional.php
                ├── MessageWrapperTrait.php
                ├── RenderableGeneric.php
                ├── ScriptInjectableInterface.php
                ├── ScriptInjectableTrait.php
                ├── StatusElementContainer.php
                ├── TooltipableTrait.php
            └── UI.php

```
###  Path: `/src/classes/UI/Badge.php`

```php
namespace ;

use UI\CriticalityEnum as CriticalityEnum;

/**
 * UI helper class for creating colored badges.
 *
 * @package Application
 * @subpackage UI
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Badge extends UI_HTMLElement implements Application_Interfaces_Iconizable, UI_Interfaces_Badge
{
	use Application_Traits_Iconizable;

	public const ERROR_WRAPPER_PLACEHOLDER_MISSING = 430002;
	public const WRAPPER_PLACEHOLDER = '{badge}';
	public const TYPE_DEFAULT = 'default';

	/**
	 * Sets HTML code that will wrap around the badge.
	 *
	 * A placeholder must be inserted in the code to
	 * specify where the badge will be injected.
	 *
	 * @param string|number|UI_Renderable_Interface|NULL $code
	 * @throws Application_Exception
	 * @return $this
	 *
	 * @see UI_Badge::WRAPPER_PLACEHOLDER
	 * @see UI_Badge::ERROR_WRAPPER_PLACEHOLDER_MISSING
	 */
	public function setWrapper($code): self
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	/**
	 * Sets the badge's label, overwriting the existing label.
	 *
	 * @param string|number|UI_Renderable_Interface|NULL $label
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setLabel($label): self
	{
		/* ... */
	}


	/**
	 * Styles the button as a button for a dangerous operation, like deleting records.
	 *
	 * @returns $this
	 */
	public function makeDangerous(): self
	{
		/* ... */
	}


	/**
	 * Styles the button as an informational button.
	 *
	 * @returns $this
	 */
	public function makeInfo(): self
	{
		/* ... */
	}


	/**
	 * Styles the button as a success button.
	 *
	 * @returns $this
	 */
	public function makeSuccess(): self
	{
		/* ... */
	}


	/**
	 * Styles the button as a warning button for potentially dangerous operations.
	 *
	 * @returns $this
	 */
	public function makeWarning(): self
	{
		/* ... */
	}


	/**
	 * Styles the button as an inverted button.
	 *
	 * @returns $this
	 */
	public function makeInverse(): self
	{
		/* ... */
	}


	/**
	 * Styles the label as inactive.
	 *
	 * @return $this
	 * @throws Application_Exception
	 */
	public function makeInactive(): self
	{
		/* ... */
	}


	/**
	 * @param string $type
	 * @return $this
	 * @throws Application_Exception
	 */
	public function makeType(string $type): self
	{
		/* ... */
	}


	/**
	 * Sets the cursor of the element to the "help" cursor.
	 * @return $this
	 */
	public function cursorHelp(): self
	{
		/* ... */
	}


	/**
	 * Makes the whole badge larger.
	 *
	 * @return $this
	 */
	public function makeLarge(): self
	{
		/* ... */
	}


	/**
	 * Makes the whole badge small.
	 *
	 * @return $this
	 */
	public function makeSmall(): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/BaseLockable.php`

```php
namespace ;

abstract class UI_BaseLockable implements Application_LockableItem_Interface
{
	public function isLockable(): bool
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function lock(string $reason): self
	{
		/* ... */
	}


	public function getLockReason(): string
	{
		/* ... */
	}


	public function unlock(): self
	{
		/* ... */
	}


	public function isLocked(): bool
	{
		/* ... */
	}


	/**
	 * Makes the button lockable: it will automatically be disabled
	 * if the administration screen is locked by the lockmanager.
	 *
	 * @param bool $lockable
	 * @return $this
	 */
	public function makeLockable(bool $lockable = true): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/BaseUIEvent.php`

```php
namespace UI;

use Application\EventHandler\Event\BaseEvent as BaseEvent;
use UI as UI;

abstract class BaseUIEvent extends BaseEvent
{
	final public function getUI(): UI
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Bootstrap.php`

```php
namespace ;

use AppUtils\ConvertHelper as ConvertHelper;
use AppUtils\Traits\ClassableTrait as ClassableTrait;

/**
 * Abstract class for Bootstrap elements in the User Interface.
 *
 * @package User Interface
 * @subpackage Bootstrap
 */
abstract class UI_Bootstrap extends UI_Renderable implements UI_Interfaces_Bootstrap, UI_Interfaces_Conditional
{
	use ClassableTrait;
	use UI_Traits_Conditional;

	/**
	 * Sets the element's name, which can be used to retrieve it when used in collections.
	 * @param string $name
	 * @return $this
	 */
	public function setName(string $name): self
	{
		/* ... */
	}


	public function getName(): ?string
	{
		/* ... */
	}


	/**
	 * Helper method to check if this element has the specified name.
	 * @param string $name
	 * @return boolean
	 */
	public function isNamed(string $name): bool
	{
		/* ... */
	}


	public function getID(): string
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


	/**
	 * @param string $name
	 * @param string|number $value
	 * @return $this
	 */
	public function setAttribute(string $name, $value): self
	{
		/* ... */
	}


	public function getAttribute(string $name, $default = null)
	{
		/* ... */
	}


	public function hasAttribute(string $name): bool
	{
		/* ... */
	}


	public function renderAttributes(): string
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @param string|number|NULL $value
	 * @return $this
	 */
	public function setStyle(string $name, $value): self
	{
		/* ... */
	}


	/**
	 * Appends a child content to the bootstrap element.
	 * Note that the element has to support this, otherwise
	 * it will have no effect.
	 *
	 * @param UI_Bootstrap $child
	 * @throws Application_Exception
	 * @return $this
	 */
	public function appendChild(UI_Bootstrap $child): self
	{
		/* ... */
	}


	/**
	 * @param UI_Bootstrap $child
	 * @return $this
	 * @throws Application_Exception
	 */
	public function prependChild(UI_Bootstrap $child): self
	{
		/* ... */
	}


	/**
	 * Sets the parent element of a child element.
	 *
	 * @param UI_Bootstrap $parent
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setParent(UI_Bootstrap $parent): self
	{
		/* ... */
	}


	/**
	 * Retrieves the element's parent element, if any.
	 * @return UI_Bootstrap|NULL
	 */
	public function getParent(): ?UI_Bootstrap
	{
		/* ... */
	}


	/**
	 * Creates a child content instance. Note that this does not
	 * add the child: it is orphaned until it is actually added
	 * to a parent element.
	 *
	 * @param string $type
	 * @return UI_Interfaces_Bootstrap
	 */
	public function createChild(string $type): UI_Interfaces_Bootstrap
	{
		/* ... */
	}


	/**
	 * Checks whether the item has a child with the specified name.
	 * @param string $name
	 * @return boolean
	 */
	public function hasChild(string $name): bool
	{
		/* ... */
	}


	/**
	 * @return UI_Bootstrap[]
	 */
	public function getChildren(): array
	{
		/* ... */
	}


	public function hasChildren(): bool
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Button.php`

```php
namespace ;

use AppUtils\Interfaces\StringableInterface as StringableInterface;
use AppUtils\JSHelper as JSHelper;
use AppUtils\Traits\ClassableTrait as ClassableTrait;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;
use UI\Bootstrap\ButtonGroup\ButtonGroupItemInterface as ButtonGroupItemInterface;
use UI\Interfaces\ButtonLayoutInterface as ButtonLayoutInterface;
use UI\Traits\ActivatableTrait as ActivatableTrait;
use UI\Traits\ButtonLayoutTrait as ButtonLayoutTrait;
use UI\Traits\ButtonSizeTrait as ButtonSizeTrait;

/**
 * A configurable HTML `button` element. Use the
 * {@see UI::button()} method to instantiate a new
 * button instance.
 *
 * @package User Interface
 * @subpackage UI Elements
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Button extends UI_BaseLockable implements UI_Renderable_Interface, UI_Interfaces_Button, ButtonGroupItemInterface
{
	use Application_Traits_Iconizable;
	use UI_Traits_RenderableGeneric;
	use ClassableTrait;
	use UI_Traits_Conditional;
	use UI_Traits_ClientConfirmable;
	use ButtonSizeTrait;
	use ActivatableTrait;
	use ButtonLayoutTrait;

	public const MODE_CLICKABLE = 'clickable';
	public const MODE_LINKED = 'linked';
	public const MODE_SUBMIT = 'submit';

	public function getName(): string
	{
		/* ... */
	}


	/**
	 * Sets an attribute of the button tag.
	 *
	 * @param string $name
	 * @param string|number|UI_Renderable_Interface|NULL $value
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setAttribute(string $name, $value): self
	{
		/* ... */
	}


	/**
	 * Alias for {@setStyle()}.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return $this
	 */
	public function addStyle(string $name, $value): self
	{
		/* ... */
	}


	public function setLabel($label): self
	{
		/* ... */
	}


	public function getLabel(): string
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


	/**
	 * @return $this
	 * @deprecated Not used anymore.
	 */
	public function makeSpecial(): self
	{
		/* ... */
	}


	/**
	 * Styles the button as an informational button.
	 *
	 * @deprecated Use {@see self::makeInfo()} instead.
	 * @return $this
	 */
	public function makeInformational(): self
	{
		/* ... */
	}


	/**
	 * Turns the button into a submit button.
	 *
	 * @param string $name
	 * @param string|int|float|UI_Renderable_Interface $value
	 * @return $this
	 */
	public function makeSubmit(string $name, $value): self
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setName(string $name): self
	{
		/* ... */
	}


	/**
	 * Retrieves the button's ID attribute.
	 *
	 * @return string
	 */
	public function getID(): string
	{
		/* ... */
	}


	/**
	 * Sets a javascript statement to use as click handler of the button.
	 *
	 * @param string $statement
	 * @return $this
	 */
	public function click(string $statement): self
	{
		/* ... */
	}


	/**
	 * Sets the title attribute of the button.
	 *
	 * @param string|number|UI_Renderable_Interface $title
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setTitle($title): self
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface $tooltip
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setTooltip($tooltip): self
	{
		/* ... */
	}


	public function getTooltip(): string
	{
		/* ... */
	}


	/**
	 * Sets the tooltip text, to enable the button tooltip.
	 *
	 * @deprecated Use {@see self::setTooltip()} instead.
	 * @param string|number|UI_Renderable_Interface $text
	 * @return $this
	 */
	public function setTooltipText($text): self
	{
		/* ... */
	}


	/**
	 * Styles the button like a regular link (but keeping the button size).
	 *
	 * @param bool $buttonLink Use the `btn-link` class? Otherwise, it will be a regular link tag.
	 * @return $this
	 */
	public function makeLink(bool $buttonLink = true): self
	{
		/* ... */
	}


	/**
	 * Sets the text to display on the button when it is
	 * switched to the loading state. Note that the loading
	 * state can only be triggered clientside, however.
	 *
	 * @param string|number|UI_Renderable_Interface $text
	 * @return $this
	 */
	public function setLoadingText($text): self
	{
		/* ... */
	}


	public function __toString()
	{
		/* ... */
	}


	public function getType(): string
	{
		/* ... */
	}


	/**
	 * Ensures that the text in the button does not wrap to the next line.
	 *
	 * @return $this
	 */
	public function setNowrap(): self
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


	/**
	 * @param string|AdminURLInterface $url
	 * @param string $target
	 * @return $this
	 */
	public function link($url, string $target = ''): self
	{
		/* ... */
	}


	/**
	 * Sets the button as a block element that will fill
	 * all the available horizontal space.
	 *
	 * @return $this
	 */
	public function makeBlock(): self
	{
		/* ... */
	}


	/**
	 * Sets a style for the main body tag's <code>style</code> attribute.
	 *
	 * @param string $style The style to set, e.g. <code>padding-top</code>
	 * @param mixed $value The value to set the style to.
	 * @return $this
	 */
	public function setStyle(string $style, $value): self
	{
		/* ... */
	}


	/**
	 * Enables the button's "pushed" state.
	 *
	 * @return $this
	 */
	public function push(): self
	{
		/* ... */
	}


	/**
	 * Removes the button's "pushed" state.
	 *
	 * @return $this
	 */
	public function unpush(): self
	{
		/* ... */
	}


	/**
	 * Makes the button redirect to the target URL, displaying
	 * a clientside loader while the target page loads.
	 *
	 * @param string|AdminURLInterface $url
	 * @param string $loaderText
	 * @return $this
	 */
	public function loaderRedirect($url, string $loaderText = ''): self
	{
		/* ... */
	}


	/**
	 * Makes the button disabled.
	 *
	 * @param string|number|UI_Renderable_Interface|NULL $reason
	 * @return $this
	 * @throws UI_Exception
	 */
	public function disable($reason = ''): self
	{
		/* ... */
	}


	public function isDisabled(): bool
	{
		/* ... */
	}


	public function getURL(): string
	{
		/* ... */
	}


	public function isClickable(): bool
	{
		/* ... */
	}


	public function isLinked(): bool
	{
		/* ... */
	}


	public function isSubmittable(): bool
	{
		/* ... */
	}


	public function getJavascript(): string
	{
		/* ... */
	}


	public function isDangerous(): bool
	{
		/* ... */
	}


	/**
	 * Makes the button use an existing popover instance.
	 * It will be reconfigured to be used with the button.
	 *
	 * @param UI_Bootstrap_Popover $popover
	 * @return $this
	 */
	public function setPopover(UI_Bootstrap_Popover $popover): self
	{
		/* ... */
	}


	/**
	 * Makes the button display a popover.
	 *
	 * NOTE: Button will only handle the popover.
	 * Setting a click handler or link will be ignored.
	 *
	 * @return UI_Button
	 * @throws UI_Exception
	 */
	public function makePopover(): self
	{
		/* ... */
	}


	/**
	 * Retrieves the button's popover instance to
	 * configure it. Automatically makes the button
	 * a popover button.
	 *
	 * @return UI_Bootstrap_Popover
	 * @throws UI_Exception
	 */
	public function getPopover(): UI_Bootstrap_Popover
	{
		/* ... */
	}


	public function addDataAttribute(string $name, string $value): self
	{
		/* ... */
	}


	/**
	 * @param string|AdminURLInterface $url
	 * @param string $target
	 * @return self
	 */
	public function presetView($url, string $target = ''): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/CSSClasses.php`

```php
namespace UI;

/**
 * This class contains a repository of CSS classes globally
 * available for applications, independently of the selected
 * theme.
 *
 * @package User Interface
 * @subpackage Themes
 */
class CSSClasses
{
	/**
	 * Class for developer-only visual elements. Automatically
	 * hidden for non-developer users.
	 *
	 * @see \UI_StringBuilder::developer()
	 */
	public const RIGHT_DEVELOPER = 'right-developer';

	/**
	 * Class to highlight parts of a text that refer to concepts,
	 * names or the like. Typically used for dynamically inserted
	 * text in translatable texts.
	 *
	 * Example:
	 *
	 * ```php
	 * $message = t(
	 *   'The product %1$s has been updated successfully at %2$s.',
	 *   sb()->reference($productName),
	 *   sb()->time()
	 * );
	 * ```
	 *
	 * @see \UI_StringBuilder::reference()
	 */
	public const TEXT_REFERENCE = 'text-reference';

	/**
	 * Marks any element as clickable by giving it the click cursor.
	 *
	 * **Note**: The functionality must be added separately.
	 * The method {@see \UI_StringBuilder::clickable()} can help
	 * with that.
	 *
	 * @see \UI_StringBuilder::clickable()
	 */
	public const CLICKABLE = 'clickable';

	/**
	 * Styles text in a monospace font, without using
	 * a `code` tag.
	 *
	 * @see \UI_StringBuilder::mono()
	 */
	public const TEXT_MONOSPACE = 'monospace';
	public const TEXT_ERROR_XXL = 'text-error-xxl';
	public const TEXT_SUCCESS = 'text-success';
	public const TEXT_MUTED = 'muted';
	public const TEXT_WARNING = 'text-warning';
	public const TEXT_ERROR = 'text-error';
	public const TEXT_SECONDARY = 'text-secondary';
	public const TEXT_INVERTED = 'text-inverted';
	public const TEXT_INFO = 'text-info';
	public const INPUT_XLARGE = 'input-xlarge';
	public const INPUT_LARGE = 'input-large';
	public const INPUT_XXLARGE = 'input-xxlarge';
	public const INPUT_XSMALL = 'input-mini';
	public const INPUT_SMALL = 'input-small';
	public const INPUT_MEDIUM = 'input-medium';
	public const INPUT_SELECT_FILTERABLE = 'filterable';
}


```
###  Path: `/src/classes/UI/CSSGenerator/CSSGen.php`

```php
namespace UI\CSSGenerator;

use AppUtils\Collections\BaseStringPrimaryCollection as BaseStringPrimaryCollection;
use AppUtils\FileHelper as FileHelper;
use AppUtils\FileHelper\FileInfo as FileInfo;
use AppUtils\FileHelper\FolderInfo as FolderInfo;
use Application\AppFactory as AppFactory;
use Application\Development\Admin\Screens\DevelArea as DevelArea;
use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;
use UI as UI;
use UI\Admin\Screens\CSSGenDevelMode as CSSGenDevelMode;

/**
 * CSS Generator: Detects all CSS template files in the framework and driver
 * theme CSS folders, and compiles them into production CSS files.
 *
 * @package Application
 * @subpackage Development Tooling
 *
 * @method CSSGenFile[] getAll()
 * @method CSSGenFile getByID(string $id)
 */
class CSSGen extends BaseStringPrimaryCollection
{
	public const LOCATION_FRAMEWORK = 'default';
	public const LOCATION_DRIVER = 'driver';
	public const CSS_TEMPLATE_EXTENSION = 'csst';
	public const FOLDER_PROPERTY_BASE_FOLDER = 'baseFolder';

	public static function create(): self
	{
		/* ... */
	}


	public function getDefaultID(): string
	{
		/* ... */
	}


	public function getAdminGenerateURL(array $params = []): string
	{
		/* ... */
	}


	public function getAdminURL(array $params = []): string
	{
		/* ... */
	}


	public function generateAll(): self
	{
		/* ... */
	}


	/**
	 * @return CSSGenLocation[]
	 */
	public function getLocations(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/CSSGenerator/CSSGen.php`

```php
namespace UI\CSSGenerator;

use AppUtils\Collections\BaseStringPrimaryCollection as BaseStringPrimaryCollection;
use AppUtils\FileHelper as FileHelper;
use AppUtils\FileHelper\FileInfo as FileInfo;
use AppUtils\FileHelper\FolderInfo as FolderInfo;
use Application\AppFactory as AppFactory;
use Application\Development\Admin\Screens\DevelArea as DevelArea;
use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;
use UI as UI;
use UI\Admin\Screens\CSSGenDevelMode as CSSGenDevelMode;

/**
 * CSS Generator: Detects all CSS template files in the framework and driver
 * theme CSS folders, and compiles them into production CSS files.
 *
 * @package Application
 * @subpackage Development Tooling
 *
 * @method CSSGenFile[] getAll()
 * @method CSSGenFile getByID(string $id)
 */
class CSSGen extends BaseStringPrimaryCollection
{
	public const LOCATION_FRAMEWORK = 'default';
	public const LOCATION_DRIVER = 'driver';
	public const CSS_TEMPLATE_EXTENSION = 'csst';
	public const FOLDER_PROPERTY_BASE_FOLDER = 'baseFolder';

	public static function create(): self
	{
		/* ... */
	}


	public function getDefaultID(): string
	{
		/* ... */
	}


	public function getAdminGenerateURL(array $params = []): string
	{
		/* ... */
	}


	public function getAdminURL(array $params = []): string
	{
		/* ... */
	}


	public function generateAll(): self
	{
		/* ... */
	}


	/**
	 * @return CSSGenLocation[]
	 */
	public function getLocations(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/CSSGenerator/CSSGenException.php`

```php
namespace UI\CSSGenerator;

use Application\Exception\ApplicationException as ApplicationException;

class CSSGenException extends ApplicationException
{
}


```
###  Path: `/src/classes/UI/CSSGenerator/CSSGenException.php`

```php
namespace UI\CSSGenerator;

use Application\Exception\ApplicationException as ApplicationException;

class CSSGenException extends ApplicationException
{
}


```
###  Path: `/src/classes/UI/CSSGenerator/CSSGenFile.php`

```php
namespace UI\CSSGenerator;

use AppUtils\FileHelper as FileHelper;
use AppUtils\FileHelper\FileInfo as FileInfo;
use AppUtils\Interfaces\StringPrimaryRecordInterface as StringPrimaryRecordInterface;
use DateTime as DateTime;

class CSSGenFile implements StringPrimaryRecordInterface
{
	public const ERROR_SOURCE_FILE_NO_MODIFIED_DATE = 148401;

	public function getID(): string
	{
		/* ... */
	}


	public function getName(): string
	{
		/* ... */
	}


	public function getLocation(): CSSGenLocation
	{
		/* ... */
	}


	public function getTargetFile(): FileInfo
	{
		/* ... */
	}


	public function getRelativePath(): string
	{
		/* ... */
	}


	public function getModifiedDate(): DateTime
	{
		/* ... */
	}


	public function getStatusPretty(): string
	{
		/* ... */
	}


	public function generate(): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/CSSGenerator/CSSGenFile.php`

```php
namespace UI\CSSGenerator;

use AppUtils\FileHelper as FileHelper;
use AppUtils\FileHelper\FileInfo as FileInfo;
use AppUtils\Interfaces\StringPrimaryRecordInterface as StringPrimaryRecordInterface;
use DateTime as DateTime;

class CSSGenFile implements StringPrimaryRecordInterface
{
	public const ERROR_SOURCE_FILE_NO_MODIFIED_DATE = 148401;

	public function getID(): string
	{
		/* ... */
	}


	public function getName(): string
	{
		/* ... */
	}


	public function getLocation(): CSSGenLocation
	{
		/* ... */
	}


	public function getTargetFile(): FileInfo
	{
		/* ... */
	}


	public function getRelativePath(): string
	{
		/* ... */
	}


	public function getModifiedDate(): DateTime
	{
		/* ... */
	}


	public function getStatusPretty(): string
	{
		/* ... */
	}


	public function generate(): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/CSSGenerator/CSSGenLocation.php`

```php
namespace UI\CSSGenerator;

use AppUtils\FileHelper as FileHelper;
use AppUtils\FileHelper\FolderInfo as FolderInfo;

class CSSGenLocation
{
	public function getID(): string
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	public function getBaseFolder(): FolderInfo
	{
		/* ... */
	}


	public function getCSSFolder(): FolderInfo
	{
		/* ... */
	}


	public function getRelativePath(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/CSSGenerator/CSSGenLocation.php`

```php
namespace UI\CSSGenerator;

use AppUtils\FileHelper as FileHelper;
use AppUtils\FileHelper\FolderInfo as FolderInfo;

class CSSGenLocation
{
	public function getID(): string
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	public function getBaseFolder(): FolderInfo
	{
		/* ... */
	}


	public function getCSSFolder(): FolderInfo
	{
		/* ... */
	}


	public function getRelativePath(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/ClientConfirmable/Message.php`

```php
namespace ;

/**
 * Container for a button's confirmation message. Allows
 * customizing the message dialog.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_ClientConfirmable_Message
{
	public const ERROR_UNSUPPORTED_ELEMENT_MODE = 54401;

	/**
	 * Sets the message body of the dialog. May contain HTML.
	 *
	 * @param string|int|float|UI_Renderable_Interface|NULL $message
	 * @return UI_ClientConfirmable_Message
	 * @throws UI_Exception
	 */
	public function setMessage($message): UI_ClientConfirmable_Message
	{
		/* ... */
	}


	/**
	 * Whether to display an input field that the user has to type
	 * a confirmation text in to confirm the operation.
	 *
	 * @param bool $withInput
	 * @return $this
	 */
	public function makeWithInput(bool $withInput = true): UI_ClientConfirmable_Message
	{
		/* ... */
	}


	/**
	 * Whether to add a comments input field to enter comments regarding
	 * the operation.
	 *
	 * @param bool $withComments
	 * @return $this
	 *
	 * @see UI_ClientConfirmable_Message::setCommentsDescription()
	 * @see UI_ClientConfirmable_Message::getCommentsRequestVar()
	 */
	public function makeWithComments(bool $withComments = true): UI_ClientConfirmable_Message
	{
		/* ... */
	}


	/**
	 * Sets a description text for the comments field (used only if
	 * the comment field is enabled).
	 *
	 * @param string $description
	 * @return $this
	 */
	public function setCommentsDescription(string $description): UI_ClientConfirmable_Message
	{
		/* ... */
	}


	/**
	 * Sets the text to display in the loader shown when the user
	 * confirms (to replace the default loading text).
	 *
	 * @param string|int|float|UI_Renderable_Interface|NULL $text
	 * @throws UI_Exception
	 */
	public function setLoaderText($text): UI_ClientConfirmable_Message
	{
		/* ... */
	}


	/**
	 * Sets the name of the request variable that is used to add the
	 * comments text to the redirect URL when using the `makeWithComments()`
	 * method, and a linked button.
	 *
	 * @param string $name
	 * @return $this
	 * @see UI_ClientConfirmable_Message::getCommentsRequestVar()
	 */
	public function setCommentsRequestVar(string $name): UI_ClientConfirmable_Message
	{
		/* ... */
	}


	/**
	 * Gets the name of the request variable that is used to add the
	 * comments text to the redirect URL when using the `makeWithComments()`
	 * method, and a linked button.
	 *
	 * @return string
	 * @see UI_ClientConfirmable_Message::setCommentsRequestVar()
	 */
	public function getCommentsRequestVar(): string
	{
		/* ... */
	}


	public function getJavaScript(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/ClientConfirmable/Message.php`

```php
namespace ;

/**
 * Container for a button's confirmation message. Allows
 * customizing the message dialog.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_ClientConfirmable_Message
{
	public const ERROR_UNSUPPORTED_ELEMENT_MODE = 54401;

	/**
	 * Sets the message body of the dialog. May contain HTML.
	 *
	 * @param string|int|float|UI_Renderable_Interface|NULL $message
	 * @return UI_ClientConfirmable_Message
	 * @throws UI_Exception
	 */
	public function setMessage($message): UI_ClientConfirmable_Message
	{
		/* ... */
	}


	/**
	 * Whether to display an input field that the user has to type
	 * a confirmation text in to confirm the operation.
	 *
	 * @param bool $withInput
	 * @return $this
	 */
	public function makeWithInput(bool $withInput = true): UI_ClientConfirmable_Message
	{
		/* ... */
	}


	/**
	 * Whether to add a comments input field to enter comments regarding
	 * the operation.
	 *
	 * @param bool $withComments
	 * @return $this
	 *
	 * @see UI_ClientConfirmable_Message::setCommentsDescription()
	 * @see UI_ClientConfirmable_Message::getCommentsRequestVar()
	 */
	public function makeWithComments(bool $withComments = true): UI_ClientConfirmable_Message
	{
		/* ... */
	}


	/**
	 * Sets a description text for the comments field (used only if
	 * the comment field is enabled).
	 *
	 * @param string $description
	 * @return $this
	 */
	public function setCommentsDescription(string $description): UI_ClientConfirmable_Message
	{
		/* ... */
	}


	/**
	 * Sets the text to display in the loader shown when the user
	 * confirms (to replace the default loading text).
	 *
	 * @param string|int|float|UI_Renderable_Interface|NULL $text
	 * @throws UI_Exception
	 */
	public function setLoaderText($text): UI_ClientConfirmable_Message
	{
		/* ... */
	}


	/**
	 * Sets the name of the request variable that is used to add the
	 * comments text to the redirect URL when using the `makeWithComments()`
	 * method, and a linked button.
	 *
	 * @param string $name
	 * @return $this
	 * @see UI_ClientConfirmable_Message::getCommentsRequestVar()
	 */
	public function setCommentsRequestVar(string $name): UI_ClientConfirmable_Message
	{
		/* ... */
	}


	/**
	 * Gets the name of the request variable that is used to add the
	 * comments text to the redirect URL when using the `makeWithComments()`
	 * method, and a linked button.
	 *
	 * @return string
	 * @see UI_ClientConfirmable_Message::setCommentsRequestVar()
	 */
	public function getCommentsRequestVar(): string
	{
		/* ... */
	}


	public function getJavaScript(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/ClientResource.php`

```php
namespace ;

use AppUtils\FileHelper as FileHelper;

/**
 * Abstract base class for clientside resource files.
 *
 * @package UserInterface
 * @subpackage ClientResources
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 *
 * @see UI_ResourceManager
 * @see UI_ClientResource_Javascript
 * @see UI_ClientResource_Stylesheet
 */
abstract class UI_ClientResource
{
	public function getFileOrURL(): string
	{
		/* ... */
	}


	public function getKey(): int
	{
		/* ... */
	}


	public function disable(): UI_ClientResource
	{
		/* ... */
	}


	public function isEnabled(): bool
	{
		/* ... */
	}


	/**
	 * Whether this resource has already been loaded in the current
	 * request, and does not have to be included again.
	 *
	 * @return bool
	 */
	public function isAvoidable(): bool
	{
		/* ... */
	}


	/**
	 * Whether the resource has been specified with an absolute
	 * `http` URL, meaning it is an external resource (like jQuery).
	 *
	 * @return bool
	 */
	public function isAbsoluteURL(): bool
	{
		/* ... */
	}


	/**
	 * File type identifier, e.g. "js".
	 *
	 * @return string
	 *
	 * @see UI_Themes_Theme::FILE_TYPE_STYLESHEET
	 * @see UI_Themes_Theme::FILE_TYPE_JAVASCRIPT
	 * @see UI_Themes_Theme::FILE_TYPE_TEMPLATE
	 * @see UI_Themes_Theme::FILE_TYPE_GRAPHIC
	 */
	public function getFileType(): string
	{
		/* ... */
	}


	/**
	 * Sets the priority with which it should be included
	 * in the page.
	 *
	 * @param int $priority
	 * @return UI_ClientResource
	 */
	public function setPriority(int $priority): UI_ClientResource
	{
		/* ... */
	}


	public function getPriority(): int
	{
		/* ... */
	}


	/**
	 * Retrieves the URL to include this resource.
	 *
	 * Automatically returns the minified version
	 * if enabled, and includes the application's
	 * build key parameter if present.
	 *
	 * @return string
	 */
	public function getURL(): string
	{
		/* ... */
	}


	/**
	 * If we want to use the minified versions of scripts,
	 * this will check if there is a file with the same name,
	 * but with `-min` appended. This is then used instead.
	 *
	 * Note: this will not be applied to absolute URLs.
	 *
	 * @return string
	 */
	public function getMinifiedFileName(): string
	{
		/* ... */
	}


	public function getPath(): string
	{
		/* ... */
	}


	/**
	 * Relative path to the resource file.
	 *
	 * @return string
	 */
	public function getRelativePath(): string
	{
		/* ... */
	}


	public function toArray(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/ClientResourceCollection.php`

```php
namespace UI;

use UI as UI;
use UI_ClientResource as UI_ClientResource;
use UI_ClientResource_Javascript as UI_ClientResource_Javascript;
use UI_ClientResource_Stylesheet as UI_ClientResource_Stylesheet;
use UI_ResourceManager as UI_ResourceManager;

/**
 * Helper class that can be used to keep track of all client
 * resources that get added, and access the list afterwards.
 * Resources can be added just like the {@see UI} class.
 *
 * @package UserInterface
 * @subpackage ClientResources
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 */
class ClientResourceCollection
{
	public function getUI(): UI
	{
		/* ... */
	}


	/**
	 * @return UI_ClientResource[]
	 */
	public function getResources(): array
	{
		/* ... */
	}


	public function hasResources(): bool
	{
		/* ... */
	}


	public function addJavascript(
		string $fileOrUrl,
		int $priority = 0,
		bool $defer = false,
	): UI_ClientResource_Javascript
	{
		/* ... */
	}


	public function addVendorJavascript(
		string $packageName,
		string $file,
		int $priority = 0,
	): UI_ClientResource_Javascript
	{
		/* ... */
	}


	public function addVendorStylesheet(
		string $packageName,
		string $file,
		int $priority = 0,
	): UI_ClientResource_Stylesheet
	{
		/* ... */
	}


	public function addStylesheet(
		string $fileOrUrl,
		string $media = 'all',
		int $priority = 0,
	): UI_ClientResource_Stylesheet
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/CriticalityEnum.php`

```php
namespace UI;

use BasicEnum as BasicEnum;

class CriticalityEnum extends BasicEnum
{
	public const DANGEROUS = 'important';
	public const INFO = 'info';
	public const SUCCESS = 'success';
	public const WARNING = 'warning';
	public const INVERSE = 'inverse';
	public const INACTIVE = 'default';
}


```
###  Path: `/src/classes/UI/DataGrid.php`

```php
namespace ;

use AppUtils\HTMLTag as HTMLTag;
use AppUtils\Interfaces\StringableInterface as StringableInterface;
use Application\AppFactory as AppFactory;
use Application\Application as Application;
use Application\Driver\DriverException as DriverException;
use Application\FilterSettings\FilterSettingsInterface as FilterSettingsInterface;
use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;
use Application\Interfaces\FilterCriteriaInterface as FilterCriteriaInterface;
use Application\Interfaces\HiddenVariablesInterface as HiddenVariablesInterface;
use Application\Traits\HiddenVariablesTrait as HiddenVariablesTrait;
use UI\DataGrid\GridClientCommands as GridClientCommands;
use UI\DataGrid\GridConfigurator as GridConfigurator;

/**
 * Handles displaying data in a tabular grid, with extended functionality
 * like applying custom actions to entries, allowing the user to reorder
 * list items, and more.
 *
 * @package User Interface
 * @subpackage Data Grids
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_DataGrid implements HiddenVariablesInterface
{
	use HiddenVariablesTrait;

	public const ERROR_MISSING_PRIMARY_KEY_NAME = 599901;
	public const ERROR_ALLSELECTED_FILTER_CRITERIA_MISSING = 599903;
	public const ERROR_ALLSELECTED_PRIMARY_KEYNAME_MISSING = 599904;
	public const ERROR_DUPLICATE_DATAGRID_ID = 599905;
	public const ERROR_UNKNOWN_OPTION = 599907;
	public const ERROR_COLUMN_NAME_DOES_NOT_EXIST = 599908;
	public const ERROR_ACTION_NOT_FOUND = 599909;
	public const ERROR_ACTION_ALREADY_ADDED = 599910;
	public const REQUEST_PARAM_ORDERBY = 'datagrid_orderby';
	public const REQUEST_PARAM_ORDERDIR = 'datagrid_orderdir';
	public const REQUEST_PARAM_ACTION = 'datagrid_action';
	public const REQUEST_PARAM_SUBMITTED = 'datagrid_submitted';
	public const REQUEST_PARAM_PERPAGE = 'datagrid_perpage';
	public const REQUEST_PARAM_PAGE = 'datagrid_page';
	public const REQUEST_PARAM_CONFIGURE_GRID = 'configure_data_grid';
	public const COLUMN_START_INDEX = 1;
	public const SETTING_SEPARATOR = '_';
	public const DEFAULT_LIMIT_CHOICES = [10, 20, 40, 60, 120];

	/**
	 * @param string|number|UI_Renderable_Interface|NULL $message
	 * @return $this
	 */
	public function setEmptyMessage($message): UI_DataGrid
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
	 * @return UI
	 */
	public function getUI(): UI
	{
		/* ... */
	}


	/**
	 * Retrieves the currently selected sorting column.
	 *
	 * @return UI_DataGrid_Column|NULL
	 */
	public function getOrderColumn(): ?UI_DataGrid_Column
	{
		/* ... */
	}


	/**
	 * Enables clientside controls to adjust the number of columns that get
	 * displayed, and to navigate between them.
	 *
	 * @param int $maxColumns The maximum number of columns to display; Any above will be hidden.
	 * @return $this
	 */
	public function enableColumnControls(int $maxColumns = 5): self
	{
		/* ... */
	}


	/**
	 * Retrieves a column by its data key name.
	 * @param string $dataKeyName
	 * @return UI_DataGrid_Column|NULL
	 */
	public function getColumnByName(string $dataKeyName): ?UI_DataGrid_Column
	{
		/* ... */
	}


	/**
	 * Retrieves a column by its order key name.
	 * @param string $orderKeyName
	 * @return UI_DataGrid_Column|NULL
	 */
	public function getColumnByOrderKey(string $orderKeyName): ?UI_DataGrid_Column
	{
		/* ... */
	}


	/**
	 * Distributes widths evenly over all columns in the grid.
	 * The optional parameter can force all existing with settings
	 * of individual columns to be overwritten. Default is to
	 * retain any existing width settings.
	 *
	 * Note: any columns that have a pixel width set will be
	 * reset and given an automatic percentual width, since the
	 * two do not mix well.
	 *
	 * @param bool $overwriteExisting
	 * @return $this
	 */
	public function makeEvenColumnWidths(bool $overwriteExisting = false): self
	{
		/* ... */
	}


	/**
	 * Moves the specified column to the desired position, starting at
	 * 1 for the first column in the grid.
	 *
	 * @param UI_DataGrid_Column $column
	 * @param int|string $position
	 * @return boolean Whether the column was moved
	 * @deprecated Not supported anymore. Use custom ordering instead.
	 */
	public function moveColumn(UI_DataGrid_Column $column, $position): bool
	{
		/* ... */
	}


	public function resetColumnWidths(): self
	{
		/* ... */
	}


	/**
	 * Retrieves the column by its column number (starting at 1).
	 * @param integer $number
	 * @return UI_DataGrid_Column|NULL
	 */
	public function getColumn(int $number): ?UI_DataGrid_Column
	{
		/* ... */
	}


	public function getLastColumn(): ?UI_DataGrid_Column
	{
		/* ... */
	}


	/**
	 * Counts all columns, excluding hidden columns.
	 * @return int
	 */
	public function countColumns(): int
	{
		/* ... */
	}


	/**
	 * Counts the number of columns that the user has
	 * chosen not to display.
	 *
	 * @return int
	 */
	public function countUserHiddenColumns(): int
	{
		/* ... */
	}


	/**
	 * @param string $dataKey
	 * @param string|number|UI_Renderable_Interface $title
	 * @param array<string,mixed> $options
	 * @return UI_DataGrid_Column
	 */
	public function addColumn(string $dataKey, $title, array $options = []): UI_DataGrid_Column
	{
		/* ... */
	}


	public function setColumnEnabled(string $keyName, bool $enabled): self
	{
		/* ... */
	}


	/**
	 * @param string $keyName
	 * @return false|UI_DataGrid_Column
	 */
	public function hasColumn(string $keyName)
	{
		/* ... */
	}


	/**
	 * Adds a row with sums of values in columns.
	 *
	 * @return UI_DataGrid_Row_Sums
	 */
	public function addSumsRow(): UI_DataGrid_Row_Sums
	{
		/* ... */
	}


	/**
	 * Adds controls in the grid to select multiple entries and add
	 * actions for them. Use the {@link addAction()} method to add
	 * available actions.
	 *
	 * @param string $primaryKeyName The name of the primary key in the records, only optional if you plan to set it separately.
	 * @param bool $forced Whether the force the checkboxes even when the grid has no actions (if you plan on processing the selected items manually)
	 * @see addAction()
	 */
	public function enableMultiSelect(string $primaryKeyName = '', bool $forced = false): UI_DataGrid
	{
		/* ... */
	}


	/**
	 * Makes the multiselect menu open towards the top of
	 * the page instead of towards the bottom. Only used
	 * if the multiselect is enabled.
	 *
	 * @return UI_DataGrid
	 */
	public function setMultiSelectDropUp(): UI_DataGrid
	{
		/* ... */
	}


	public function setPrimaryName(string $keyName): UI_DataGrid
	{
		/* ... */
	}


	/**
	 * Disables the multiselect functionality, even
	 * if it was enabled prior to calling this.
	 */
	public function disableMultiSelect(): UI_DataGrid
	{
		/* ... */
	}


	public function optionExists(string $name): bool
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @param mixed $value
	 * @return $this
	 * @throws Exception
	 */
	public function setOption(string $name, $value): UI_DataGrid
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @return mixed
	 * @throws UI_DataGrid_Exception
	 */
	public function getOption(string $name)
	{
		/* ... */
	}


	public function getClientObjectName(): string
	{
		/* ... */
	}


	/**
	 * @param string $actionName
	 * @return string
	 * @deprecated Use {@see self::clientCommands()} instead.
	 */
	public function getClientSubmitStatement(string $actionName): string
	{
		/* ... */
	}


	/**
	 * @return string
	 * @deprecated Use {@see self::clientCommands()} instead.
	 */
	public function getClientToggleSelectionStatement(): string
	{
		/* ... */
	}


	/**
	 * Gets the helper class used to access client-side commands
	 * related to this data grid.
	 *
	 * @return GridClientCommands
	 */
	public function clientCommands(): GridClientCommands
	{
		/* ... */
	}


	/**
	 * Adds an action to the grid that can be run for the selected
	 * elements (works only if multi select is enabled).
	 * @param string $name
	 * @param string|int|float|StringableInterface|null $label
	 * @return UI_DataGrid_Action_Default
	 */
	public function addAction(string $name, string|int|float|StringableInterface|null $label): UI_DataGrid_Action_Default
	{
		/* ... */
	}


	/**
	 * Adds a separator between multiselect actions.
	 * @see addConfirmAction()
	 * @see addAction()
	 */
	public function addSeparatorAction(): void
	{
		/* ... */
	}


	/**
	 * Adds an action to the grid that can be run for the selected
	 * elements, but which will display a confirmation dialog before
	 * starting the action. Only works if multi select is enabled.
	 * @param string $name
	 * @param string|int|float|StringableInterface|NULL $label
	 * @param string|int|float|StringableInterface|NULL $confirmMessage
	 * @return UI_DataGrid_Action_Confirm
	 * @throws UI_Exception
	 */
	public function addConfirmAction(
		string $name,
		string|int|float|StringableInterface|null $label,
		string|int|float|StringableInterface|null $confirmMessage,
	): UI_DataGrid_Action_Confirm
	{
		/* ... */
	}


	/**
	 * Checks whether any actions have been added to the grid.
	 * @return boolean
	 */
	public function hasActions(): bool
	{
		/* ... */
	}


	/**
	 * Adds an action that executed the specified javascript function
	 * when the action is selected. For this to work correctly, place
	 * the placeholder <code>%1$s</code> where you wish the datagrid
	 * object instance to be inserted, and <code>%2$s</code> for the
	 * name of the action.
	 *
	 * Example:
	 *
	 * <pre>
	 * addJSAction('do_something(%1$s, %2$s)');
	 * </pre>
	 *
	 * Important: use only single quotes in the code!
	 *
	 * @param string $name
	 * @param string $label
	 * @param string $function
	 * @return UI_DataGrid_Action_Javascript
	 */
	public function addJSAction(string $name, string $label, string $function): UI_DataGrid_Action_Javascript
	{
		/* ... */
	}


	public function disableRowSeparator(): UI_DataGrid
	{
		/* ... */
	}


	public function enableRowSeparator(): UI_DataGrid
	{
		/* ... */
	}


	public function disableBorder(): UI_DataGrid
	{
		/* ... */
	}


	public function enableBorder(): UI_DataGrid
	{
		/* ... */
	}


	public function disableMargins(): UI_DataGrid
	{
		/* ... */
	}


	public function enableMargins(): UI_DataGrid
	{
		/* ... */
	}


	/**
	 * Makes the list more compact by reducing cell padding.
	 * Alias for setting the "compact" option to true.
	 */
	public function enableCompactMode(): UI_DataGrid
	{
		/* ... */
	}


	/**
	 * Makes a mini table of the list by removing table borders and reducing padding/margin.
	 * Alias for setting the "mini" option to true.
	 */
	public function enableMiniMode(): UI_DataGrid
	{
		/* ... */
	}


	/**
	 * Reduces the size of the columns to fit the content inside
	 * Alias for setting the "fit-content" option to true.
	 */
	public function enableFitContent(): UI_DataGrid
	{
		/* ... */
	}


	/**
	 * If disabled, the datagrid will be rendered without an
	 * enclosing form tag. In this case, actions and the like
	 * which depend on the form will be disabled as well.
	 *
	 * @return UI_DataGrid
	 */
	public function disableForm(): UI_DataGrid
	{
		/* ... */
	}


	public function isFormEnabled(): bool
	{
		/* ... */
	}


	public function disableCompactMode(): UI_DataGrid
	{
		/* ... */
	}


	public function enableHover(): UI_DataGrid
	{
		/* ... */
	}


	public function disableHover(): UI_DataGrid
	{
		/* ... */
	}


	/**
	 * Enables the multiple choice selector for choosing the
	 * number of items to display per page. If no current
	 * choice is set, the first item in the selector is used.
	 * The choices have to be an indexed array of numeric values.
	 *
	 * @param int[] $choices
	 * @return UI_DataGrid
	 */
	public function enableLimitOptions(array $choices): UI_DataGrid
	{
		/* ... */
	}


	public function enableLimitOptionsDefault(): UI_DataGrid
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @return string
	 */
	public function resolveSettingName(string $name): string
	{
		/* ... */
	}


	/**
	 * Disables the multiple choice selector for choosing the
	 * number of items to show per page (only useful if it
	 * has been enabled prior to calling this, as it is
	 * disabled by default).
	 */
	public function disableLimitOptions(): UI_DataGrid
	{
		/* ... */
	}


	/**
	 * Disables the hint message "X items are hidden by filter settings"
	 * that is displayed when no entries are found.
	 *
	 * @return UI_DataGrid
	 */
	public function disableFilterHint(): UI_DataGrid
	{
		/* ... */
	}


	public function enableFilterHint(): UI_DataGrid
	{
		/* ... */
	}


	public function getOffset(): int
	{
		/* ... */
	}


	/**
	 * Retrieves the current items per page limit.
	 * @return int
	 */
	public function getLimit(): int
	{
		/* ... */
	}


	/**
	 * Retrieves the name of the primary field. The value
	 * has to be set in the data records, even if it is not
	 * shown in a column.
	 *
	 * @return string
	 */
	public function getPrimaryField(): string
	{
		/* ... */
	}


	/**
	 * Checks whether the primary field name has been set.
	 * @return boolean
	 */
	public function hasPrimaryField(): bool
	{
		/* ... */
	}


	/**
	 * Adds a class name that will be added to the data grid's main table HTML element.
	 * @param string $class
	 * @return $this
	 */
	public function addTableClass(string $class): self
	{
		/* ... */
	}


	/**
	 * Renders the grid with the specified set of data rows.
	 *
	 * Expects an indexed array with associative array entries
	 * containing key => value pairs of column data, or entry
	 * objects, or a mix of both.
	 *
	 * If you need to customize individual rows in the grid, you
	 * have the possibility to create entry objects manually,
	 * and mix these into the set of entries.
	 *
	 * Example:
	 *
	 * <pre>
	 * $entries = array();
	 *
	 * // add a traditional entry
	 * $entries[] = array(
	 *    'title' => 'First product',
	 *    'state' => 'Published'
	 * );
	 *
	 * // create a custom entry and give the table row a custom class
	 * $entries[] = $datagrid->createEntry(array(
	 *    'title' => 'Second product',
	 *    'state' => 'Draft'
	 * ))->addClass('custom-class');
	 *
	 * $datagrid->render($entries);
	 * </pre>
	 *
	 * @param array<int,array<string,mixed>|UI_DataGrid_Entry> $entries
	 * @return string
	 * @throws Application_Exception
	 */
	public function render(array $entries): string
	{
		/* ... */
	}


	/**
	 * Renders a JS statement that can be used to submit the grid's form.
	 * @return string
	 */
	public function renderJSSubmitHandler(bool $simulate = false): string
	{
		/* ... */
	}


	public function renderHiddenVars(): string
	{
		/* ... */
	}


	/**
	 * Configures the data grid using the specified filter settings and filter criteria.
	 *
	 * @param FilterSettingsInterface $settings
	 * @param FilterCriteriaInterface $criteria
	 * @return UI_DataGrid
	 */
	public function configure(FilterSettingsInterface $settings, FilterCriteriaInterface $criteria): UI_DataGrid
	{
		/* ... */
	}


	/**
	 * Parses the specified set of entries and converts
	 * all array data sets to entry objects. Entries that
	 * are already entry objects are not modified.
	 *
	 * @param array<int,array<string,mixed>|UI_DataGrid_Entry> $entries
	 * @return UI_DataGrid_Entry[]
	 */
	public function filterAndSortEntries(array $entries): array
	{
		/* ... */
	}


	/**
	 * Creates an entry object for the grid: these are used internally
	 * to handle individual rows in the table.
	 *
	 * @param array<string, string|int|float|DateTime|StringableInterface|NULL> $data Associative array with key => value pairs for columns in the row.
	 * @return UI_DataGrid_Entry
	 */
	public function createEntry(array $data = []): UI_DataGrid_Entry
	{
		/* ... */
	}


	/**
	 * Creates a heading entry that can be used to create subtitles in a grid.
	 *
	 * @param string|StringableInterface $title
	 * @return UI_DataGrid_Entry_Heading
	 */
	public function createHeadingEntry(string|StringableInterface $title): UI_DataGrid_Entry_Heading
	{
		/* ... */
	}


	/**
	 * Creates a merged entry that spans the whole columns.
	 *
	 * @param string $title
	 * @return UI_DataGrid_Entry_Merged
	 */
	public function createMergedEntry(string $title): UI_DataGrid_Entry_Merged
	{
		/* ... */
	}


	public function getFormID(string $part = ''): string
	{
		/* ... */
	}


	public function getAction(): string
	{
		/* ... */
	}


	/**
	 * @return string[]
	 */
	public function getSelected(): array
	{
		/* ... */
	}


	public function isSubmitted(): bool
	{
		/* ... */
	}


	/**
	 * @return $this
	 * @deprecated
	 * @see UI_DataGrid::disableFooter()
	 */
	public function hideFooter(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 * @throws Exception
	 */
	public function disableFooter(): UI_DataGrid
	{
		/* ... */
	}


	public function enableFooter(): UI_DataGrid
	{
		/* ... */
	}


	public function isFooterEnabled(): bool
	{
		/* ... */
	}


	/**
	 * Ensures that the primary key name has been set.
	 * Throws an exception otherwise.
	 *
	 * @throws Application_Exception
	 */
	public function requirePrimaryName(): void
	{
		/* ... */
	}


	public function getValidActions(): array
	{
		/* ... */
	}


	public function getTotal(): int
	{
		/* ... */
	}


	public function getTotalUnfiltered(): ?int
	{
		/* ... */
	}


	/**
	 * Counts the amount of items that have been added to the
	 * grid. Note that this does not necessarily match the actual
	 * amount of rows, since these can be excluded from the count.
	 *
	 * @return int
	 * @see UI_DataGrid_Entry::isCountable()
	 */
	public function countEntries(): int
	{
		/* ... */
	}


	/**
	 * @return int
	 */
	public function countPages(): int
	{
		/* ... */
	}


	public function getPage(): int
	{
		/* ... */
	}


	public function setTotal(int $total): UI_DataGrid
	{
		/* ... */
	}


	/**
	 * Sets the total number of records without any filtering.
	 * If no set, assumes the total is the unfiltered total.
	 * Otherwise, displays information about filtered item counts
	 * as needed.
	 *
	 * Note: set automatically if a filter criteria instance is provided.
	 *
	 * @param integer $total
	 * @return UI_DataGrid
	 */
	public function setTotalUnfiltered(int $total): UI_DataGrid
	{
		/* ... */
	}


	public function configureFromFilters(FilterCriteriaInterface $criteria): UI_DataGrid
	{
		/* ... */
	}


	/**
	 * @deprecated
	 * @return $this
	 * @throws Exception
	 * @see UI_DataGrid::disableHeader()
	 */
	public function hideHeader(): UI_DataGrid
	{
		/* ... */
	}


	public function disableHeader(): UI_DataGrid
	{
		/* ... */
	}


	public function enableHeader(): UI_DataGrid
	{
		/* ... */
	}


	public function isHeaderEnabled(): bool
	{
		/* ... */
	}


	public function renderCells(UI_DataGrid_Entry $cell, bool $register = true): string
	{
		/* ... */
	}


	/**
	 * Executes the action callbacks if the data grid has been
	 * submitted, an action has been selected, and any action
	 * callbacks have been defined. Use this to automate the
	 * handling of actions.
	 */
	public function executeCallbacks(): UI_DataGrid
	{
		/* ... */
	}


	/**
	 * Checks whether the grid is currently in batch processing mode.
	 * This is different from the list being in AJAX mode:
	 * The list can be in batch processing mode but not in AJAX mode.
	 *
	 * @return boolean
	 */
	public function isBatchProcessing(): bool
	{
		/* ... */
	}


	public function isBatchComplete(): bool
	{
		/* ... */
	}


	/**
	 * Retrieves the currently selected action, if any.
	 * @return UI_DataGrid_Action|NULL
	 */
	public function getActiveAction(): ?UI_DataGrid_Action
	{
		/* ... */
	}


	public function isAllSelected(): bool
	{
		/* ... */
	}


	/**
	 * Whether the grid is currently in AJAX mode.
	 * @return boolean
	 */
	public function isAjax(): bool
	{
		/* ... */
	}


	/**
	 * Sets the optional title for the grid.
	 *
	 * @param string|number|UI_Renderable_Interface|NULL $title
	 * @throws UI_Exception
	 */
	public function setTitle($title): self
	{
		/* ... */
	}


	/**
	 * Sets the title of the table when it is shown in the full view mode
	 * (which is only available when the column controls are enabled).
	 *
	 * @param string|number|StringableInterface|NULL $title
	 * @return $this
	 */
	public function setFullViewTitle($title): self
	{
		/* ... */
	}


	/**
	 * Sets that the visible entries in the grid can be sorted
	 * clientside. Requires a clientside object name to be set
	 * that will handle the sorting events for the list, as well
	 * as provide additional configuration options.
	 *
	 * @param string|StringableInterface $clientsideHandler The name of a clientside variable holding the sorting events handler object
	 * @param string|NULL $primaryKeyName The name of the primary key in the records. Optional only if set separately.
	 * @return $this
	 */
	public function makeEntriesSortable($clientsideHandler, ?string $primaryKeyName = null): self
	{
		/* ... */
	}


	/**
	 * Sets that elements may be dragged into the list to add new
	 * entries.
	 *
	 * @param string|StringableInterface $clientsideHandler The name of a clientside variable holding the droppable events handler object
	 * @param string|NULL $primaryKeyName The name of the primary key in the records. Optional only if set separately.
	 * @return $this
	 */
	public function makeEntriesDroppable($clientsideHandler, ?string $primaryKeyName = null): self
	{
		/* ... */
	}


	/**
	 * Checks whether the sortable entries feature is enabled.
	 * @return boolean
	 */
	public function isEntriesSortable(): bool
	{
		/* ... */
	}


	/**
	 * @return string|NULL
	 */
	public function getOrderBy(): ?string
	{
		/* ... */
	}


	/**
	 * Retrieves the selected direction in which to sort the grid.
	 *
	 * @return string asc|desc
	 */
	public function getOrderDir()
	{
		/* ... */
	}


	/**
	 * @param string $dir
	 * @return $this
	 */
	public function setDefaultOrderDir(string $dir): self
	{
		/* ... */
	}


	/**
	 * Sets the column to use as default sorting column.
	 * @param UI_DataGrid_Column $column
	 * @return $this
	 */
	public function setDefaultSortColumn(UI_DataGrid_Column $column, string $dir = 'ASC'): self
	{
		/* ... */
	}


	/**
	 * Adds the java scripts and stylesheets required to use the
	 * data grid support clientside to build grids with the API.
	 */
	public static function addClientSupport(): void
	{
		/* ... */
	}


	/**
	 * Configures the data grid for the administration screen,
	 * by setting all required hidden variables to stay on the
	 * current page when using the pager.
	 *
	 * @param AdminScreenInterface $screen
	 * @return $this
	 * @throws Application_Exception
	 */
	public function configureForScreen(AdminScreenInterface $screen): self
	{
		/* ... */
	}


	/**
	 * @param string $footerText
	 */
	public function setFooterCountText(string $footerText): void
	{
		/* ... */
	}


	/**
	 * @param int $from
	 * @param int $to
	 * @param int $total
	 * @return string
	 */
	public function getFooterCountText(int $from, int $to, int $total): string
	{
		/* ... */
	}


	/**
	 * Sets the `action` parameter of the data grid's form.
	 *
	 * @param string $dispatcher
	 * @return $this
	 */
	public function setDispatcher(string $dispatcher): UI_DataGrid
	{
		/* ... */
	}


	public function getRefreshURL(array $params = []): string
	{
		/* ... */
	}


	/**
	 * @return UI_DataGrid_Column[]
	 */
	public function getAllColumns(): array
	{
		/* ... */
	}


	/**
	 * @return UI_DataGrid_Column[]
	 */
	public function getValidColumns(): array
	{
		/* ... */
	}


	public function requireColumnByName(string $columnName): UI_DataGrid_Column
	{
		/* ... */
	}


	public function resetSettings(): void
	{
		/* ... */
	}


	/**
	 * Turns off the default behavior of tables to fill 100%
	 * of the available space, making the grid use only the
	 * space that its columns require.
	 *
	 * @return $this
	 */
	public function makeAutoWidth(): self
	{
		/* ... */
	}


	/**
	 * @return string[]
	 */
	public function getActionNames(): array
	{
		/* ... */
	}


	public function actionExists(string $name): bool
	{
		/* ... */
	}


	public function getActionByName(string $actionName): UI_DataGrid_Action
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Event/FormCreatedEvent.php`

```php
namespace UI\Event;

use UI\BaseUIEvent as BaseUIEvent;
use UI_Form as UI_Form;

class FormCreatedEvent extends BaseUIEvent
{
	public const EVENT_NAME = 'FormCreated';

	public function getName(): string
	{
		/* ... */
	}


	public function getForm(): UI_Form
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Event/FormCreatedEvent.php`

```php
namespace UI\Event;

use UI\BaseUIEvent as BaseUIEvent;
use UI_Form as UI_Form;

class FormCreatedEvent extends BaseUIEvent
{
	public const EVENT_NAME = 'FormCreated';

	public function getName(): string
	{
		/* ... */
	}


	public function getForm(): UI_Form
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Event/PageRendered.php`

```php
namespace UI\Event;

use Application\EventHandler\Event\BaseEvent as BaseEvent;
use Application\EventHandler\Traits\HTMLProcessingEventTrait as HTMLProcessingEventTrait;
use Application\Formable\Event\HTMLProcessingEventInterface as HTMLProcessingEventInterface;
use UI_Page as UI_Page;

class PageRendered extends BaseEvent implements HTMLProcessingEventInterface
{
	use HTMLProcessingEventTrait;

	public const EVENT_NAME = 'PageRendered';

	public function getName(): string
	{
		/* ... */
	}


	public function getPage(): UI_Page
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Event/PageRendered.php`

```php
namespace UI\Event;

use Application\EventHandler\Event\BaseEvent as BaseEvent;
use Application\EventHandler\Traits\HTMLProcessingEventTrait as HTMLProcessingEventTrait;
use Application\Formable\Event\HTMLProcessingEventInterface as HTMLProcessingEventInterface;
use UI_Page as UI_Page;

class PageRendered extends BaseEvent implements HTMLProcessingEventInterface
{
	use HTMLProcessingEventTrait;

	public const EVENT_NAME = 'PageRendered';

	public function getName(): string
	{
		/* ... */
	}


	public function getPage(): UI_Page
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Exception.php`

```php
namespace ;

class UI_Exception extends Application_Exception
{
}


```
###  Path: `/src/classes/UI/Form.php`

```php
namespace ;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use AppUtils\ClassHelper as ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException as BaseClassHelperException;
use AppUtils\ConvertHelper as ConvertHelper;
use AppUtils\ConvertHelper\JSONConverter\JSONConverterException as JSONConverterException;
use AppUtils\ConvertHelper_Exception as ConvertHelper_Exception;
use AppUtils\FileHelper as FileHelper;
use AppUtils\Interfaces\StringableInterface as StringableInterface;
use AppUtils\JSHelper as JSHelper;
use AppUtils\RegexHelper as RegexHelper;
use Application\AppFactory as AppFactory;
use Application\Application as Application;
use HTML\QuickForm2\DataSource\ManualSubmitDataSource as ManualSubmitDataSource;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;
use UI\Form\FormException as FormException;

/**
 * Form handling class used to create form elements, as well as
 * rules and utilities all around the elements.
 *
 * @package Application
 * @subpackage Forms
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Form extends UI_Renderable
{
	public const ERROR_DUPLICATE_ELEMENT_ID = 45524001;
	public const ERROR_INVALID_FORM_DATA = 45524002;
	public const ERROR_UNKNOWN_REGEX_HINT = 45524003;
	public const ERROR_UNKNOWN_EVENT_HANDLER = 45524004;
	public const ERROR_INVALID_EVENT_HANDLER = 45524005;
	public const ERROR_INVALID_LENGTH_LIMIT = 45524006;
	public const ERROR_ELEMENT_HAS_NO_ID = 45524007;
	public const ERROR_MINMAX_VALUES_EMPTY = 45524008;
	public const ERROR_INVALID_MINMAX_VALUES = 45524009;
	public const ERROR_MINMAX_VALUES_NOT_A_NUMBER = 45524010;
	public const ERROR_OBSOLETE_IMAGE_ELEMENT = 45524011;
	public const ERROR_INVALID_RENDER_CALLBACK = 45524012;
	public const ERROR_UNHANDLED_SUBMIT_HANDLER_SUBJECT = 45524013;
	public const ERROR_INVALID_FORM_RENDERER = 45524014;
	public const ERROR_INVALID_DATEPICKER_ELEMENT = 45524015;
	public const ERROR_CANNOT_CREATE_ELEMENT = 45524016;
	public const ERROR_COULD_NOT_SUBMIT_FORM = 45524017;
	public const ERROR_ELEMENT_NOT_FOUND = 45524018;

	/**
	 * Stores the string that form element IDs get prefixed with.
	 * @var string
	 */
	public const ID_PREFIX = 'f-';
	public const ATTRIBUTE_LABEL_ID = 'data-label-id';
	public const REL_BUTTON = 'Button';
	public const REL_LAYOUT_LESS_GROUP = 'LayoutlessGroup';
	public const FORM_PREFIX = 'form-';
	public const ELEMENT_TYPE_DATE_PICKER = 'datepicker';

	public function getID(): string
	{
		/* ... */
	}


	/**
	 * Sets the ID to use for the form element's <code>&lt;label&gt;</code>
	 * tag. This is used by the {@see UI_Form_Renderer} to adjust the label's
	 * target (instead of using the element's own ID).
	 *
	 * Use cases are when an element has sub-elements (like groups), to be
	 * able to specify what the target should be.
	 *
	 * @param HTML_QuickForm2_Node $node
	 * @param string $id
	 * @return void
	 */
	public static function setElementLabelID(HTML_QuickForm2_Node $node, string $id): void
	{
		/* ... */
	}


	public function getJSID(): string
	{
		/* ... */
	}


	public function callback_onNodeAdded(HTML_QuickForm2_Event_NodeAdded $event): void
	{
		/* ... */
	}


	/**
	 * Registers a custom form element rule.
	 *
	 * Example:
	 *
	 * registerCustomRule('rule_alias', 'RuleName');
	 *
	 * This would register an element that can be added to
	 * a form using addRule('rule_alias'). The class for
	 * it has to be called HTML_QuickForm2_Rule_RuleName.
	 *
	 * @param string $alias
	 * @param string $ruleName
	 */
	public function registerCustomRule(string $alias, string $ruleName): void
	{
		/* ... */
	}


	/**
	 * Registers a custom form Element class.
	 *
	 * Example:
	 *
	 * registerCustomElement('element_alias', 'ElementName');
	 *
	 * This would register an element that can be added to
	 * a form using addElement('element_alias'). The class
	 * for it has to be called HTML_QuickForm2_Element_ElementName.
	 *
	 * @param string $alias
	 * @param string $elementName
	 * @throws BaseClassHelperException
	 */
	public function registerCustomElement(string $alias, string $elementName): void
	{
		/* ... */
	}


	/**
	 * Retrieves a list of all registered custom elements.
	 * @return array Indexed array with these keys in each entry: "alias", "name" and "file"
	 */
	public function getCustomElements(): array
	{
		/* ... */
	}


	/**
	 * Retrieves the default data source of the form, which
	 * is used to set the default values of elements.
	 *
	 * @return HTML_QuickForm2_DataSource_Array
	 */
	public function getDefaultDataSource(): HTML_QuickForm2_DataSource_Array
	{
		/* ... */
	}


	/**
	 * @param array<string,mixed> $values
	 * @return $this
	 */
	public function setDefaultValues(array $values): self
	{
		/* ... */
	}


	/**
	 * Selects the default element in the form. If possible, when the page is
	 *  loaded, the field will automatically get focus.
	 *
	 * @param HTML_QuickForm2_Node $element
	 * @return $this
	 */
	public function setDefaultElement(HTML_QuickForm2_Node $element): self
	{
		/* ... */
	}


	/**
	 * Sets an attribute of the form element itself.
	 *
	 * @param string $name
	 * @param string|int|float|NULL $value
	 * @return UI_Form
	 */
	public function setAttribute(string $name, $value): self
	{
		/* ... */
	}


	/**
	 * @return HTML_QuickForm2_Node[]
	 */
	public function getErroneousElements(array $result = []): array
	{
		/* ... */
	}


	public function renderErrorMessages(): string
	{
		/* ... */
	}


	/**
	 * Manually submits the form given the specified data.
	 *
	 * @param array<string,mixed> $formValues
	 * @return $this
	 * @throws Application_Formable_Exception
	 */
	public function makeSubmitted(array $formValues = []): self
	{
		/* ... */
	}


	public function addGroupLayoutless(
		string $name,
		?HTML_QuickForm2_Container $container = null,
	): HTML_QuickForm2_Container_Group
	{
		/* ... */
	}


	/**
	 * @param HTML_QuickForm2_Node $element
	 * @return HTML_QuickForm2_Node
	 * @see UI_Form_Renderer_CommentGenerator::addMarkdownComment()
	 */
	public function addMarkdownSupport(HTML_QuickForm2_Node $element): HTML_QuickForm2_Node
	{
		/* ... */
	}


	public function renderJSSelectFilterable(string $selector): string
	{
		/* ... */
	}


	/**
	 * Adds a class to the form tag itself.
	 *
	 * @param string $className
	 * @return UI_Form
	 */
	public function addClass(string $className): self
	{
		/* ... */
	}


	public function removeClass(string $className): self
	{
		/* ... */
	}


	/**
	 * Returns an element if its id is found
	 *
	 * @param string $id Element id to search for
	 * @return HTML_QuickForm2_Node|null
	 */
	public function getElementByID(string $id): ?HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Retrieves the first element in the form whose name
	 * matches the specified name.
	 *
	 * @param string $name
	 * @return HTML_QuickForm2_Node|null
	 * @throws BaseClassHelperException
	 */
	public function getElementByName(string $name): ?HTML_QuickForm2_Node
	{
		/* ... */
	}


	public function requireElementByName(string $name): HTML_QuickForm2_Node
	{
		/* ... */
	}


	public function getValue($elementID)
	{
		/* ... */
	}


	/**
	 * Checks whether the form has been submitted.
	 * @return boolean
	 */
	public function isSubmitted(): bool
	{
		/* ... */
	}


	/**
	 * @return HTML_QuickForm2
	 */
	public function getForm(): HTML_QuickForm2
	{
		/* ... */
	}


	/**
	 * Retrieves all required elements in the form, or the
	 * specified container if the first parameter is set.
	 *
	 * @param HTML_QuickForm2_Container|NULL $container
	 * @param array $result
	 * @return HTML_QuickForm2_Node[]
	 */
	public function getRequiredElements(?HTML_QuickForm2_Container $container = null, array $result = []): array
	{
		/* ... */
	}


	/**
	 * In silent validation mode, validation errors are not
	 * displayed to the user, and the form does not add any
	 * error messages to the UI.
	 *
	 * @param bool $enabled
	 * @return $this
	 */
	public function setSilentValidation(bool $enabled = true): self
	{
		/* ... */
	}


	/**
	 * Simulates the form being submitted using the form's current
	 * values. This can be used to validate an arbitrary set of values
	 * without needing to submit an actual form mask.
	 *
	 * To use this, create a form with the values you wish to validate
	 * as default values, then validate the form as per usual.
	 *
	 * @return $this
	 */
	public function simulateSubmit(): self
	{
		/* ... */
	}


	/**
	 * Attempts to validate the form and returns the success state.
	 *
	 * Automatically adds a UI message to tell the user that something
	 * is missing in the form, unless silent validation mode is enabled
	 * ({@see self::setSilentValidation()}).
	 *
	 * @return boolean
	 * @throws UI_Exception
	 */
	public function validate(): bool
	{
		/* ... */
	}


	/**
	 * Retrieves all form element instances that have errors
	 * after validation, as an indexed array with form element
	 * instances.
	 *
	 * @return HTML_QuickForm2_Node[]
	 * @throws UI_Exception
	 */
	public function getInvalidElements(bool $simulateSubmit = false): array
	{
		/* ... */
	}


	/**
	 * Checks whether the form's submitted data is valid. If it
	 * has not been validated yet, it is validated automatically.
	 *
	 * @return boolean
	 * @throws UI_Exception
	 */
	public function isValid(): bool
	{
		/* ... */
	}


	/**
	 * Retrieves all form element values in an associative array.
	 *
	 * @param boolean $removeTrackingVar
	 * @return array<string,mixed>
	 */
	public function getValues(bool $removeTrackingVar = false): array
	{
		/* ... */
	}


	/**
	 * @return HTML_QuickForm2_Element_ImageUploader[]
	 */
	public function getImageUploaderElements(): array
	{
		/* ... */
	}


	/**
	 * Retrieves the name of the request variable that is used by the
	 * form to track whether it has been submitted.
	 *
	 * @return string
	 */
	public function getTrackingName(): string
	{
		/* ... */
	}


	public function isTrackingElement(HTML_QuickForm2_Node $element): bool
	{
		/* ... */
	}


	public function isDummyElement(HTML_QuickForm2_Node $element): bool
	{
		/* ... */
	}


	public function renderHorizontal(): string
	{
		/* ... */
	}


	public function renderColumnized(): string
	{
		/* ... */
	}


	/**
	 * Makes the form readonly so that it only shows element values,
	 * without editing capabilities.
	 *
	 * @param bool $readonly
	 * @return $this
	 */
	public function makeReadonly(bool $readonly = true): self
	{
		/* ... */
	}


	public function isReadonly(): bool
	{
		/* ... */
	}


	/**
	 * Makes the field labels wider to allow for longer labels.
	 * @return $this
	 */
	public function makeLabelsWider(bool $enabled = true): self
	{
		/* ... */
	}


	/**
	 * Turns the form into a more compact form layout.
	 * @return $this
	 */
	public function makeCondensed(bool $enabled = true): self
	{
		/* ... */
	}


	/**
	 * Marks the form as being collapsible: all headers within the
	 * form will be rendered so that their contained form elements
	 * can be collapsed/expanded at will.
	 *
	 * @return $this
	 */
	public function makeCollapsible(bool $enabled = true): self
	{
		/* ... */
	}


	/**
	 * Retrieves a format hint for any of the common
	 * regexes. The name is the name of the regex constant
	 * minus the <code>REGEX_</code> (case-insensitive),
	 * so for example:
	 *
	 * getRegexHint('alias');
	 * getRegexHint('name_or_title');
	 *
	 * @param string $name
	 * @return string
	 * @throws FormException
	 */
	public static function getRegexHint(string $name): string
	{
		/* ... */
	}


	/**
	 * Replaces commas with dots in a number, and removes spaces.
	 * @param string|NULL $value
	 * @return string
	 */
	public function filter_adjustNumericNotation(?string $value): string
	{
		/* ... */
	}


	/**
	 * Retrieves the first element in the container's element collection,
	 * or null if it does not have any elements.
	 *
	 * @param HTML_QuickForm2_Container $container
	 * @return NULL|HTML_QuickForm2_Node
	 */
	public function getFirstElement(HTML_QuickForm2_Container $container): ?HTML_QuickForm2_Node
	{
		/* ... */
	}


	public function handle_validateMinMax($value, ?int $min, ?int $max): bool
	{
		/* ... */
	}


	/**
	 * Sets the onsubmit attribute of the form tag to the specified
	 * javascript statement string.
	 *
	 * @param string $statement
	 * @return $this
	 */
	public function onSubmit(string $statement): self
	{
		/* ... */
	}


	/**
	 * Creates an ID for a form element following the naming scheme standard
	 * so that clientside scripts can access them easily as well.
	 *
	 * @param string $jsid
	 * @param string|NULL $elementName
	 * @return string
	 * @throws FormException
	 */
	public function createElementID(string $jsid, ?string $elementName): string
	{
		/* ... */
	}


	/**
	 * Validates the specified string with the regex for
	 * regular item labels.
	 *
	 * @param string|NULL $label
	 * @return boolean
	 */
	public static function validateLabel(?string $label): bool
	{
		/* ... */
	}


	/**
	 * Validates the specified string with the regex for
	 * item aliases.
	 *
	 * @param string|NULL $alias
	 * @return boolean
	 */
	public static function validateAlias(?string $alias): bool
	{
		/* ... */
	}


	/**
	 * Validates the specified string with the regex for
	 * email addresses.
	 *
	 * @param string|NULL $email
	 * @return boolean
	 */
	public static function validateEmail(?string $email): bool
	{
		/* ... */
	}


	/**
	 * Adds an event handler for the specified event type.
	 * Throws an exception for unknown event types and uncallable
	 * handlers.
	 *
	 * When the event is triggered, the handler is called with two
	 * parameters:
	 *
	 * - The UI_Form instance
	 * - An associative array with additional event-specific data
	 *
	 * @param string $name
	 * @param callable $handler
	 * @return $this
	 *
	 * @throws Application_Exception
	 * @throws FormException
	 */
	public function addEventHandler(string $name, callable $handler): self
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @param string $label
	 * @param HTML_QuickForm2_Container|null $container
	 * @return HTML_QuickForm2_Element_InputText
	 *
	 * @throws BaseClassHelperException
	 * @throws FormException
	 */
	public function addText(
		string $name,
		string $label,
		?HTML_QuickForm2_Container $container = null,
	): HTML_QuickForm2_Element_InputText
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @param string $label
	 * @param HTML_QuickForm2_Container|null $container
	 * @return HTML_QuickForm2_Element_Textarea
	 *
	 * @throws FormException
	 * @throws BaseClassHelperException
	 */
	public function addTextarea(
		string $name,
		string $label,
		?HTML_QuickForm2_Container $container = null,
	): HTML_QuickForm2_Element_Textarea
	{
		/* ... */
	}


	/**
	 * Adds a subheader to the form, which does not contain any data.
	 * It is purely cosmetic and rendered using the form renderer.
	 *
	 * @param string|number|StringableInterface|NULL $header
	 * @param null|HTML_QuickForm2_Container $container
	 * @return HTML_QuickForm2_Node
	 *
	 * @throws BaseClassHelperException
	 * @throws HTML_QuickForm2_Exception
	 * @throws UI_Exception
	 */
	public function addSubheader($header, ?HTML_QuickForm2_Container $container = null): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Adds a collection of hidden variables to the form.
	 *
	 * NOTE: The ID of the generated elements can only be
	 * specified when using the {@see self::addHiddenVar()} method.
	 *
	 * @param array<string,string|number|NULL> $vars Name => value pairs
	 * @return $this
	 * @throws HTML_QuickForm2_InvalidArgumentException
	 */
	public function addHiddenVars(array $vars): self
	{
		/* ... */
	}


	/**
	 * Adds an element to enter a percentage.
	 *
	 * @param string $name
	 * @param string $label
	 * @param HTML_QuickForm2_Container|null $container
	 * @param float $min
	 * @param float $max
	 * @return HTML_QuickForm2_Element_InputText
	 *
	 * @throws BaseClassHelperException
	 * @throws FormException
	 * @throws UI_Exception
	 */
	public function addPercent(
		string $name,
		string $label,
		?HTML_QuickForm2_Container $container = null,
		float $min = 0,
		float $max = 100,
	): HTML_QuickForm2_Element_InputText
	{
		/* ... */
	}


	/**
	 * Creates and adds an image uploader element (specific to SPIN
	 * with the media classes).
	 *
	 * @param string $name
	 * @param HTML_QuickForm2_Container|NULL $container The container to add the element to, defaults to the form itself.
	 * @return HTML_QuickForm2_Element_ImageUploader
	 *
	 * @throws BaseClassHelperException
	 * @throws FormException
	 */
	public function addImageUploader(
		string $name,
		?HTML_QuickForm2_Container $container = null,
	): HTML_QuickForm2_Element_ImageUploader
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @param HTML_QuickForm2_Container|null $container
	 * @return HTML_QuickForm2_Element_ExpandableSelect
	 *
	 * @throws BaseClassHelperException
	 * @throws FormException
	 */
	public function addExpandableSelect(
		string $name,
		?HTML_QuickForm2_Container $container = null,
	): HTML_QuickForm2_Element_ExpandableSelect
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @param string $label
	 * @param HTML_QuickForm2_Container|null $container
	 * @return HTML_QuickForm2_Element_InputFile
	 *
	 * @throws BaseClassHelperException
	 * @throws FormException
	 */
	public function addFile(
		string $name,
		string $label,
		?HTML_QuickForm2_Container $container = null,
	): HTML_QuickForm2_Element_InputFile
	{
		/* ... */
	}


	public function addVisualSelect(
		string $name,
		?HTML_QuickForm2_Container $container = null,
	): HTML_QuickForm2_Element_VisualSelect
	{
		/* ... */
	}


	/**
	 * @param string $type
	 * @param string $name
	 * @param HTML_QuickForm2_Container|null $container
	 * @return HTML_QuickForm2_Node
	 * @throws FormException
	 */
	public function addElement(string $type, string $name, ?HTML_QuickForm2_Container $container): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Adds a group to the form that will be rendered as
	 * a tab. Note that if you use this, you should add
	 * all form elements to tabs, and not add any single
	 * elements to the form.
	 *
	 * @param string $name
	 * @param string|number|StringableInterface|NULL $label
	 * @param string|number|StringableInterface|NULL $description
	 * @return HTML_QuickForm2_Container_Group
	 *
	 * @throws HTML_QuickForm2_Exception
	 * @throws UI_Exception
	 */
	public function addTab(string $name, $label, $description = null): HTML_QuickForm2_Container_Group
	{
		/* ... */
	}


	/**
	 * Adds a purely cosmetic header to the form that has no data.
	 *
	 * @param string $title
	 * @param HTML_QuickForm2_Container|NULL $container
	 * @param string|null $anchor The name of an anchor to jump to this header in the page
	 * @param bool $collapsed
	 * @return HTML_QuickForm2_Element_InputText
	 *
	 * @throws BaseClassHelperException
	 * @throws HTML_QuickForm2_Exception
	 *
	 * @deprecated
	 */
	public function addHeader(
		string $title,
		?HTML_QuickForm2_Container $container = null,
		?string $anchor = null,
		bool $collapsed = true,
	): HTML_QuickForm2_Element_InputText
	{
		/* ... */
	}


	/**
	 * Adds an element for entering a hexadecimal color code.
	 *
	 * @param string $name
	 * @param string $label
	 * @param HTML_QuickForm2_Container|NULL $container
	 * @return HTML_QuickForm2_Element_InputText
	 *
	 * @throws BaseClassHelperException
	 * @throws FormException
	 * @throws HTML_QuickForm2_Exception
	 * @throws UI_Exception
	 */
	public function addHexColor(
		string $name,
		string $label,
		?HTML_QuickForm2_Container $container = null,
	): HTML_QuickForm2_Element_InputText
	{
		/* ... */
	}


	public function addStatic(
		string $label,
		string $content,
		?HTML_QuickForm2_Container $container = null,
	): HTML_QuickForm2_Element_InputText
	{
		/* ... */
	}


	/**
	 * Adds arbitrary HTML code to the form.
	 *
	 * @param string|int|float|bool|StringableInterface|NULL $html
	 * @param HTML_QuickForm2_Container|NULL $container
	 * @return HTML_QuickForm2_Element_InputText
	 *
	 * @throws BaseClassHelperException
	 * @throws HTML_QuickForm2_Exception
	 * @throws UI_Exception
	 */
	public function addHTML($html, ?HTML_QuickForm2_Container $container = null): HTML_QuickForm2_Element_InputText
	{
		/* ... */
	}


	/**
	 * Adds a purely cosmetic hint message to the form, styled as an
	 * informational message that has no data.
	 *
	 * @param string|number|StringableInterface|NULL $text
	 * @param HTML_QuickForm2_Container|NULL $container
	 * @return HTML_QuickForm2_Node
	 *
	 * @throws BaseClassHelperException
	 * @throws HTML_QuickForm2_Exception
	 * @throws UI_Exception
	 */
	public function addHint($text, ?HTML_QuickForm2_Container $container = null): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Adds an ISO 8601 date element (YYYY-MM-DD).
	 *
	 * @param string $name
	 * @param string $label
	 * @param HTML_QuickForm2_Container|NULL $container
	 * @return HTML_QuickForm2_Element_InputText
	 *
	 * @throws BaseClassHelperException
	 * @throws HTML_QuickForm2_Exception
	 */
	public function addISODate(
		string $name,
		string $label,
		?HTML_QuickForm2_Container $container = null,
	): HTML_QuickForm2_Element_InputText
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @param string $label
	 * @param HTML_QuickForm2_Container|null $container
	 * @param int $min
	 * @param int $max
	 * @return HTML_QuickForm2_Element_InputText
	 *
	 * @throws BaseClassHelperException
	 * @throws FormException
	 * @throws HTML_QuickForm2_Exception
	 */
	public function addInteger(
		string $name,
		string $label,
		?HTML_QuickForm2_Container $container = null,
		int $min = 0,
		int $max = 0,
	): HTML_QuickForm2_Element_InputText
	{
		/* ... */
	}


	/**
	 * Adds a purely cosmetic text paragraph to the form. Has no data.
	 *
	 * @param string|number|StringableInterface|NULL $text
	 * @param HTML_QuickForm2_Container|NULL $container
	 * @return HTML_QuickForm2_Node
	 *
	 * @throws BaseClassHelperException
	 * @throws HTML_QuickForm2_Exception
	 * @throws UI_Exception
	 */
	public function addParagraph($text, ?HTML_QuickForm2_Container $container = null): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Adds a hidden variable to the form that will get submitted along with visible fields.
	 *
	 * @param string $name
	 * @param string|int|float|null $value
	 * @param string|null $id
	 * @return HTML_QuickForm2_Element_InputHidden
	 * @throws HTML_QuickForm2_InvalidArgumentException
	 */
	public function addHiddenVar(
		string $name,
		string|int|float|null $value = null,
		?string $id = null,
	): HTML_QuickForm2_Element_InputHidden
	{
		/* ... */
	}


	/**
	 * Adds a clickable button to the form that links to the specified URL.
	 *
	 * @param string|AdminURLInterface $url
	 * @param string|number|UI_Renderable_Interface|NULL $label
	 * @param string|number|UI_Renderable_Interface|NULL $tooltip
	 * @return HTML_QuickForm2_Element_UIButton
	 *
	 * @throws FormException
	 * @throws BaseClassHelperException
	 * @throws UI_Exception
	 */
	public function addLinkButton($url, $label, $tooltip = ''): HTML_QuickForm2_Element_UIButton
	{
		/* ... */
	}


	/**
	 * Adds the primary submit button to the form's footer.
	 *
	 * @param string|number|UI_Renderable_Interface|NULL $label
	 * @param string $name
	 * @param string|number|UI_Renderable_Interface|NULL $tooltip
	 * @return HTML_QuickForm2_Element_UIButton
	 *
	 * @throws FormException
	 * @throws BaseClassHelperException
	 * @throws UI_Exception
	 * @throws BaseClassHelperException
	 */
	public function addPrimarySubmit($label, string $name = 'save', $tooltip = ''): HTML_QuickForm2_Element_UIButton
	{
		/* ... */
	}


	/**
	 * Adds a primary styled button to the form's footer to submit it.
	 * It automatically enables simulation mode before submitting
	 * the form.
	 *
	 * @param string $label
	 * @param string $name
	 * @return HTML_QuickForm2_Element_UIButton
	 *
	 * @throws BaseClassHelperException
	 * @throws FormException
	 * @throws UI_Exception
	 */
	public function addDevPrimarySubmit(string $label, string $name = 'save'): HTML_QuickForm2_Element_UIButton
	{
		/* ... */
	}


	/**
	 * Adds a blank button to the footer of the form,
	 * which must be configured further with a label
	 * and action.
	 *
	 * @param string $name
	 * @return HTML_QuickForm2_Element_UIButton
	 *
	 * @throws FormException
	 * @throws BaseClassHelperException
	 */
	public function addButton(string $name): HTML_QuickForm2_Element_UIButton
	{
		/* ... */
	}


	/**
	 * Adds a button to the form's footer to submit it.
	 *
	 * @param string|number|UI_Renderable_Interface|NULL $label
	 * @param string $name
	 * @param string|number|UI_Renderable_Interface|NULL $tooltip
	 * @return HTML_QuickForm2_Element_UIButton
	 *
	 * @throws BaseClassHelperException
	 * @throws FormException
	 * @throws UI_Exception
	 */
	public function addSubmit($label, string $name = 'save', $tooltip = null): HTML_QuickForm2_Element_UIButton
	{
		/* ... */
	}


	/**
	 * Creates a bootstrap switch element.
	 *
	 * @param string $name
	 * @param string $label
	 * @param HTML_QuickForm2_Container|NULL $container Optional container to add the element to, defaults to the form itself.
	 * @return HTML_QuickForm2_Element_Switch
	 *
	 * @throws BaseClassHelperException
	 * @throws HTML_QuickForm2_Exception
	 */
	public function addSwitch(
		string $name,
		string $label,
		?HTML_QuickForm2_Container $container = null,
	): HTML_QuickForm2_Element_Switch
	{
		/* ... */
	}


	/**
	 * Adds a tree selection element that uses a {@see \UI\Tree\TreeRenderer}
	 * to display the item tree.
	 *
	 * @param string $name
	 * @param string $label
	 * @param HTML_QuickForm2_Container|NULL $container
	 * @return HTML_QuickForm2_Element_TreeSelect
	 *
	 * @throws BaseClassHelperException
	 * @throws HTML_QuickForm2_Exception
	 */
	public function addTreeSelect(
		string $name,
		string $label,
		?HTML_QuickForm2_Container $container = null,
	): HTML_QuickForm2_Element_TreeSelect
	{
		/* ... */
	}


	/**
	 * Adds a select element.
	 *
	 * @param string $name
	 * @param string $label
	 * @param HTML_QuickForm2_Container|NULL $container
	 * @return HTML_QuickForm2_Element_Select
	 *
	 * @throws BaseClassHelperException
	 * @throws HTML_QuickForm2_Exception
	 */
	public function addSelect(
		string $name,
		string $label,
		?HTML_QuickForm2_Container $container = null,
	): HTML_QuickForm2_Element_Select
	{
		/* ... */
	}


	/**
	 * Adds a bootstrap multiselect element.
	 *
	 * @param string $name
	 * @param string $label
	 * @param HTML_QuickForm2_Container|NULL $container
	 * @return HTML_QuickForm2_Element_Multiselect
	 *
	 * @throws FormException
	 * @throws BaseClassHelperException
	 */
	public function addMultiselect(
		string $name,
		string $label,
		?HTML_QuickForm2_Container $container = null,
	): HTML_QuickForm2_Element_Multiselect
	{
		/* ... */
	}


	/**
	 * Creates and adds a generic alias form element, complete
	 * with validation rule and validation hints.
	 *
	 * @param string|null $name Defaults to [alias].
	 * @param string|null $label Defaults to [Alias].
	 * @param string|null $comment Additional text to prepend before the validation hints.
	 * @param boolean $structural Whether this alias is to be marked as a structural field.
	 * @return HTML_QuickForm2_Element_InputText
	 *
	 * @throws BaseClassHelperException
	 * @throws ConvertHelper_Exception
	 * @throws FormException
	 * @throws HTML_QuickForm2_Exception
	 * @throws UI_Exception
	 */
	public function addAlias(
		?string $name = null,
		?string $label = null,
		?string $comment = null,
		bool $structural = true,
	): HTML_QuickForm2_Element_InputText
	{
		/* ... */
	}


	/**
	 * Adds an abstract text to the form.
	 *
	 * @param string $abstract
	 * @param string[] $classes
	 * @param HTML_QuickForm2_Container|null $container
	 * @return HTML_QuickForm2_Element_InputText
	 *
	 * @throws BaseClassHelperException
	 * @throws HTML_QuickForm2_Exception
	 * @throws UI_Exception
	 */
	public function addAbstract(
		string $abstract,
		array $classes = [],
		?HTML_QuickForm2_Container $container = null,
	): HTML_QuickForm2_Element_InputText
	{
		/* ... */
	}


	/**
	 * Creates a datepicker element and adds it to the form, or the
	 * specified container.
	 *
	 * @param string $name
	 * @param string $label
	 * @param HTML_QuickForm2_Container|NULL $container
	 * @return HTML_QuickForm2_Element_HTMLDateTimePicker
	 *
	 * @throws FormException
	 * @throws BaseClassHelperException
	 */
	public function addDatepicker(
		string $name,
		string $label,
		?HTML_QuickForm2_Container $container = null,
	): HTML_QuickForm2_Element_HTMLDateTimePicker
	{
		/* ... */
	}


	/**
	 * Adds an email validation rule to the element.
	 * @param HTML_QuickForm2_Node $element
	 * @return HTML_QuickForm2_Node
	 *
	 * @throws HTML_QuickForm2_Exception
	 */
	public function addRuleEmail(HTML_QuickForm2_Node $element): HTML_QuickForm2_Node
	{
		/* ... */
	}


	public function addRuleURL(HTML_QuickForm2_Element $element): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Adds a phone number validation rule to the element.
	 *
	 * @param HTML_QuickForm2_Node $element
	 * @return HTML_QuickForm2_Node
	 *
	 * @throws HTML_QuickForm2_Exception
	 */
	public function addRulePhone(HTML_QuickForm2_Node $element): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Adds an alias validation rule to the element.
	 *
	 * @param HTML_QuickForm2_Node $element
	 * @param bool $allowCapitalLetters
	 * @return HTML_QuickForm2_Node
	 *
	 * @throws FormException
	 * @throws HTML_QuickForm2_Exception
	 */
	public function addRuleAlias(HTML_QuickForm2_Node $element, bool $allowCapitalLetters = false): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Adds a callback rule to the element. The first argument
	 * is always the value to validate, and the last is the
	 * rule object instance, even if custom arguments are specified.
	 *
	 * @param HTML_QuickForm2_Node $element
	 * @param callable $callback
	 * @param string $errorMessage
	 * @param mixed|array<int,mixed>|NULL $arguments Arguments for the callback, as an indexed array of parameters or a single value.
	 * @return HTML_QuickForm2_Rule_Callback
	 *
	 * @throws BaseClassHelperException
	 * @throws HTML_QuickForm2_Exception
	 */
	public function addRuleCallback(
		HTML_QuickForm2_Node $element,
		callable $callback,
		string $errorMessage,
		$arguments = null,
	): HTML_QuickForm2_Rule_Callback
	{
		/* ... */
	}


	/**
	 * Adds a filename validation rule, which checks that the
	 * name has an extension and contains only valid characters.
	 *
	 * @param HTML_QuickForm2_Node $element
	 * @return HTML_QuickForm2_Node
	 * @throws HTML_QuickForm2_Exception
	 */
	public function addRuleFilename(HTML_QuickForm2_Node $element): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Adds an integer validation rule to the element.
	 *
	 * @param HTML_QuickForm2_Node $element
	 * @param int $min
	 * @param int $max
	 * @return HTML_QuickForm2_Node
	 *
	 * @throws BaseClassHelperException
	 * @throws HTML_QuickForm2_Exception
	 */
	public function addRuleInteger(HTML_QuickForm2_Node $element, int $min = 0, int $max = 0): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Adds a datetime validation rule to the element.
	 *
	 * @param HTML_QuickForm2_Node $element
	 * @return HTML_QuickForm2_Node
	 *
	 * @throws BaseClassHelperException
	 * @throws HTML_QuickForm2_Exception
	 */
	public function addRuleISODate(HTML_QuickForm2_Node $element): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Adds a float validation rule to the element.
	 *
	 * @param HTML_QuickForm2_Node $element
	 * @param float $min
	 * @param float $max
	 * @return HTML_QuickForm2_Node
	 *
	 * @throws BaseClassHelperException
	 * @throws HTML_QuickForm2_Exception
	 */
	public function addRuleFloat(HTML_QuickForm2_Node $element, float $min = 0.0, float $max = 0.0): HTML_QuickForm2_Node
	{
		/* ... */
	}


	public function addRulePercent(
		HTML_QuickForm2_Element $element,
		float $min = 0,
		float $max = 100,
	): HTML_QuickForm2_Element
	{
		/* ... */
	}


	/**
	 * Converts commas in the value to dots (used for numeric values)
	 * @param mixed $value
	 * @return string
	 */
	public function callback_convertComma($value): string
	{
		/* ... */
	}


	/**
	 * Adds a rule for a date format. This is always in the english format
	 * with the month on front, e.g. [11/02/2015]. Can optionally contain
	 * a time at the end, in the form [11/02/2015 08:42].
	 *
	 * @param HTML_QuickForm2_Node $element
	 * @return HTML_QuickForm2_Node
	 *
	 * @throws BaseClassHelperException
	 * @throws HTML_QuickForm2_Exception
	 */
	public function addRuleDate(HTML_QuickForm2_Node $element): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Makes the page initially scroll to the header identified
	 * by its anchor name.
	 *
	 * Example usage:
	 *
	 * <pre>
	 * $wrapper->addHeader('Title', null, 'AnchorName');
	 * $wrapper->scrollToHeader('AnchorName');
	 * </pre>
	 *
	 * @param string $anchorName
	 * @return $this
	 */
	public function scrollToHeader(string $anchorName): self
	{
		/* ... */
	}


	/**
	 * Adds a redactor to the target element, and returns the
	 * redactor helper instance for additional configuration.
	 *
	 * @param HTML_QuickForm2_Element $element
	 * @param Application_Countries_Country $country
	 * @return UI_MarkupEditor_Redactor
	 *
	 * @throws BaseClassHelperException
	 */
	public function makeRedactor(
		HTML_QuickForm2_Element $element,
		Application_Countries_Country $country,
	): UI_MarkupEditor_Redactor
	{
		/* ... */
	}


	public function makeCKEditor(
		HTML_QuickForm2_Element $element,
		Application_Countries_Country $country,
	): UI_MarkupEditor_CKEditor
	{
		/* ... */
	}


	public function makeMarkupEditor(
		string $editorID,
		HTML_QuickForm2_Element $element,
		Application_Countries_Country $country,
	): UI_MarkupEditor
	{
		/* ... */
	}


	/**
	 * Adds a callback function that will be called when the element is
	 * rendered, to be able to influence how the element is rendered.
	 *
	 * The callback function gets an instance of {@see UI_Form_Renderer_Element}.
	 *
	 * @param HTML_QuickForm2_Node $element
	 * @param callable $callback
	 *
	 * @throws FormException
	 * @throws Application_Exception
	 *
	 * @see UI_Form_Renderer_Element
	 */
	public function addRenderCallback(HTML_QuickForm2_Node $element, callable $callback): void
	{
		/* ... */
	}


	/**
	 * Marks the element as structural, meaning changing
	 * it will trigger a new revision when saving.
	 *
	 * @param HTML_QuickForm2_Node $el
	 * @param boolean $structural Whether it is structural or not
	 * @return HTML_QuickForm2_Node
	 *
	 * @throws ConvertHelper_Exception
	 */
	public function makeStructural(HTML_QuickForm2_Node $el, bool $structural = true): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Sets the element to be rendered as a standalone element:
	 * this hides the label, and removes the element's indentation,
	 * so it can use the full available width.
	 *
	 * @param HTML_QuickForm2_Node $element
	 * @return HTML_QuickForm2_Node
	 */
	public function makeStandalone(HTML_QuickForm2_Node $element): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Adds a validation rule to make the element required.
	 *
	 * @param HTML_QuickForm2_Node $el
	 * @param string|number|StringableInterface|NULL $message
	 * @return HTML_QuickForm2_Node
	 *
	 * @throws HTML_QuickForm2_Exception
	 * @throws UI_Exception
	 */
	public function makeRequired(HTML_QuickForm2_Node $el, $message = null): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Adds a validation rule to the element to limit the length
	 * to the specified number of characters.
	 *
	 * Automatically adds a validation hint for the length as well.
	 *
	 * Note: To limit to a specific length, set the min and max
	 * to the same value.
	 *
	 * @param HTML_QuickForm2_Node $el
	 * @param int|NULL $min
	 * @param int|NULL $max
	 * @return HTML_QuickForm2_Node
	 *
	 * @throws FormException
	 * @throws HTML_QuickForm2_Exception
	 */
	public function makeLengthLimited(HTML_QuickForm2_Node $el, ?int $min, ?int $max): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * @param HTML_QuickForm2_Node $el
	 * @param int|null $min
	 * @param int|null $max
	 * @return HTML_QuickForm2_Node
	 *
	 * @throws FormException
	 * @throws HTML_QuickForm2_Exception
	 */
	public function makeMinMax(HTML_QuickForm2_Node $el, ?int $min = null, ?int $max = null): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Hides this element from the frozen variant of the form.
	 *
	 * @param HTML_QuickForm2_Node $element
	 * @return HTML_QuickForm2_Node
	 */
	public function makeHiddenWhenReadonly(HTML_QuickForm2_Node $element): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Appends the related units for the element's values to the
	 * element in the UI.
	 *
	 * Example: "Centimetres".
	 *
	 * @param HTML_QuickForm2_Node $element
	 * @param string|number|StringableInterface|NULL $units
	 * @return HTML_QuickForm2_Node
	 *
	 * @throws UI_Exception
	 */
	public function setElementUnits(HTML_QuickForm2_Node $element, $units): HTML_QuickForm2_Node
	{
		/* ... */
	}


	public function addFilterComma2Dot(HTML_QuickForm2_Element $element): HTML_QuickForm2_Element
	{
		/* ... */
	}


	/**
	 * Adds a string to append to an element.
	 * For example for units, like "Centimetres".
	 *
	 * @param HTML_QuickForm2_Node $element
	 * @param string|number|StringableInterface|NULL $appendString
	 * @return HTML_QuickForm2_Node
	 *
	 * @throws UI_Exception
	 */
	public function setElementAppend(HTML_QuickForm2_Node $element, $appendString): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Adds a string to prepend to an element.
	 * For example for units, like "Centimetres".
	 *
	 * @param HTML_QuickForm2_Node $element
	 * @param string|number|StringableInterface $prependString
	 * @return HTML_QuickForm2_Node
	 *
	 * @throws UI_Exception
	 */
	public function setElementPrepend(HTML_QuickForm2_Node $element, $prependString): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Adds custom HTML after the element's form input element.
	 * Not to mistake with {@link self::setElementAppend()}.
	 *
	 * @param HTML_QuickForm2_Node $element
	 * @param string|number|StringableInterface $html
	 * @param boolean $whenFrozen Whether to display this even when the element is frozen.
	 * @return HTML_QuickForm2_Node
	 *
	 * @see self::prependElementHTML()
	 * @see self::setElementAppend()
	 */
	public function appendElementHTML(
		HTML_QuickForm2_Node $element,
		$html,
		bool $whenFrozen = false,
	): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Appends a button after the element's input.
	 *
	 * @param HTML_QuickForm2_Node $element
	 * @param UI_Button|UI_Bootstrap $button
	 * @param boolean $whenFrozen Whether to display this even when the element is frozen.
	 * @return HTML_QuickForm2_Node
	 */
	public function appendElementButton(
		HTML_QuickForm2_Node $element,
		$button,
		bool $whenFrozen = false,
	): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Appends a button to the element to generate an alias from the content
	 * of the target element. Uses the AJAX transliterate function to create
	 * the alias from a string.
	 *
	 * @param HTML_QuickForm2_Node $aliasElement
	 * @param HTML_QuickForm2_Node $fromElement
	 * @return HTML_QuickForm2_Node
	 *
	 * @throws JSONConverterException
	 */
	public function appendGenerateAliasButton(
		HTML_QuickForm2_Node $aliasElement,
		HTML_QuickForm2_Node $fromElement,
	): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Adds custom HTML before the element's form input element.
	 * Not to mistake with {@see self::setElementPrepend()}.
	 *
	 * @param HTML_QuickForm2_Node $element
	 * @param string|number|StringableInterface $html
	 * @param bool $whenFrozen
	 * @return HTML_QuickForm2_Node
	 *
	 * @see self::appendElementHTML()
	 * @see self::setElementPrepend()
	 */
	public function prependElementHTML(
		HTML_QuickForm2_Node $element,
		$html,
		bool $whenFrozen = false,
	): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Adds a label validation rule to the element.
	 * @param HTML_QuickForm2_Node $element
	 * @return HTML_QuickForm2_Node
	 *
	 * @throws HTML_QuickForm2_Exception
	 */
	public function addRuleLabel(HTML_QuickForm2_Node $element): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Adds a name or title validation rule to the element.
	 * @param HTML_QuickForm2_Node $element
	 * @return HTML_QuickForm2_Node
	 *
	 * @throws HTML_QuickForm2_Exception
	 */
	public function addRuleNameOrTitle(HTML_QuickForm2_Node $element): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Adds a rule that disallows using HTML in the element.
	 * @param HTML_QuickForm2_Node $element
	 * @return HTML_QuickForm2_Node
	 *
	 * @throws HTML_QuickForm2_Exception
	 */
	public function addRuleNoHTML(HTML_QuickForm2_Node $element): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Adds a rule to confirm the input to the specified regex.
	 *
	 * @param HTML_QuickForm2_Node $element
	 * @param string $regex
	 * @param string $message
	 * @return HTML_QuickForm2_Node
	 *
	 * @throws HTML_QuickForm2_Exception
	 */
	public function addRuleRegex(HTML_QuickForm2_Node $element, string $regex, string $message): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Parses a date string into a date object. The date must have
	 * a format matching the  {@see RegexHelper::REGEX_DATE} regular
	 * expression.
	 *
	 * @param string|NULL $dateString
	 * @return NULL|DateTime
	 */
	public static function parseDate(?string $dateString): ?DateTime
	{
		/* ... */
	}


	/**
	 * Sets the title of the form. This is typically used
	 * in the form rendering template as title for the content
	 * section in which the form is shown.
	 *
	 * @param string|number|StringableInterface|NULL $title
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setTitle($title): self
	{
		/* ... */
	}


	public function getTitle(): string
	{
		/* ... */
	}


	/**
	 * Sets the abstract of the form. This is typically used
	 * in the form rendering template as title for the content
	 * section in which the form is shown.
	 *
	 * @param string|number|StringableInterface|NULL $abstract
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setAbstract($abstract): self
	{
		/* ... */
	}


	public function getAbstract(): string
	{
		/* ... */
	}


	/**
	 * Retrieves the matching UI_Form instance for the specified
	 * QuickForm element, or NULL if it could not be found.
	 *
	 * @param HTML_QuickForm2_Node $el
	 * @return UI_Form|NULL
	 * @throws FormException
	 */
	public static function getInstanceByElement(HTML_QuickForm2_Node $el): ?UI_Form
	{
		/* ... */
	}


	public function hasElements(bool $includeHiddens = false): bool
	{
		/* ... */
	}


	public function getName(): string
	{
		/* ... */
	}


	/**
	 * Gets the javascript statement that can be used to submit
	 * the form, optionally in simulation mode.
	 *
	 * @param bool $simulate
	 * @return string
	 * @throws Application_Formable_Exception
	 * @throws ConvertHelper_Exception
	 * @throws FormException
	 */
	public function getJSSubmitHandler(bool $simulate = false): string
	{
		/* ... */
	}


	/**
	 * @param class-string|UI_DataGrid|UI_Form|Application_Formable|Application_Interfaces_Formable|mixed $subject
	 * @return string
	 * @throws Application_Formable_Exception
	 */
	public static function resolveFormName($subject): string
	{
		/* ... */
	}


	/**
	 * Renders a JavaScript statement that can be used to
	 * submit the target form.
	 *
	 * @param string|UI_Form|Application_Formable|UI_DataGrid $subject
	 * @param boolean $simulate
	 * @return string
	 *
	 * @throws Application_Formable_Exception
	 * @throws ConvertHelper_Exception
	 * @throws FormException
	 */
	public static function renderJSSubmitHandler($subject, bool $simulate = false): string
	{
		/* ... */
	}


	/**
	 * Enables or disables the clientside form elements registry: this
	 * is an easy way to access information on the form on the client
	 * side, from sections to individual elements. By default, this is
	 * disabled.
	 *
	 * @param bool $enabled
	 * @return $this
	 */
	public function enableClientRegistry(bool $enabled = true): self
	{
		/* ... */
	}


	/**
	 * When there are no "submit" buttons directly in a form,
	 * we can make it possible to submit it via the enter key by
	 * adding this invisible "submit" button. The div does not
	 * use visibility or display to hide it, since Google
	 * Chrome will not accept the enter key for a hidden
	 * "submit" element.
	 *
	 * @param string $id Custom ID for the element, if needed
	 * @return string
	 */
	public static function renderDummySubmit(string $id = ''): string
	{
		/* ... */
	}


	public function compileExamples(): string
	{
		/* ... */
	}


	public function compileValues(): string
	{
		/* ... */
	}


	public function resolveContainer(?HTML_QuickForm2_Container $container = null): HTML_QuickForm2_Container
	{
		/* ... */
	}


	public function getElementValidator(HTML_QuickForm2_Element $element): ?UI_Form_Validator
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/HTMLElement.php`

```php
namespace ;

use AppUtils\AttributeCollection as AttributeCollection;
use AppUtils\Interfaces\ClassableInterface as ClassableInterface;
use AppUtils\Interfaces\StringableInterface as StringableInterface;
use AppUtils\Traits\ClassableTrait as ClassableTrait;
use UI\TooltipInfo as TooltipInfo;

/**
 * Base class for dynamically generated HTML UI elements. Offers
 * a basic API for modifying element attributes and common
 * characteristics.
 *
 * @package Application
 * @subpackage UI
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class UI_HTMLElement extends UI_Renderable implements ClassableInterface
{
	use ClassableTrait;

	/**
	 * Sets an attribute of the element: adds it if it does
	 * not exist yet, and overwrites it otherwise.
	 *
	 * @param string $name
	 * @param string $value
	 * @return $this
	 */
	public function setAttribute(string $name, $value): self
	{
		/* ... */
	}


	/**
	 * Retrieves the value of an attribute.
	 *
	 * @param string $name
	 * @param string $default The default value to return if it does not exist.
	 * @return string|null
	 */
	public function getAttribute($name, $default = null)
	{
		/* ... */
	}


	/**
	 * Adds a style part for the <code>style</code> attribute of the element.
	 *
	 * Example:
	 *
	 * <pre>
	 * addStyle('display', 'none');
	 * </pre>
	 *
	 * @param string $name
	 * @param string $value
	 * @return $this
	 */
	public function addStyle(string $name, string $value): self
	{
		/* ... */
	}


	/**
	 * Sets the element's ID for the <code>id</code> attribute.
	 * @param string $id
	 * @return $this
	 */
	public function setID($id)
	{
		/* ... */
	}


	/**
	 * Sets the <code>title</code> attribute of the element.
	 *
	 * @param string $title
	 * @return $this
	 */
	public function setTitle($title)
	{
		/* ... */
	}


	/**
	 * Sets a tooltip for the element, which will be shown
	 * as a tooltip popup when the user hovers over it.
	 *
	 * @param string|number|StringableInterface|TooltipInfo|NULL $tooltip
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setTooltip($tooltip): self
	{
		/* ... */
	}


	public function getTooltip(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Icon.php`

```php
namespace ;

use AppUtils\ConvertHelper as ConvertHelper;
use AppUtils\Interfaces\StringableInterface as StringableInterface;

/**
 * Icon class used to display FontAwesome icons in the
 * user interface. Convertable to string, the class generates
 * the according HTML code to display the selected icon.
 *
 * @package User Interface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Icon implements StringableInterface, UI_Renderable_Interface
{
	use UI_Traits_RenderableGeneric;

	public const ERROR_INVALID_TYPE_SELECTED = 95601;
	public const ERROR_INVALID_COLOR_STYLE = 95602;
	public const ERROR_INVALID_TOOLTIP_POSITION = 95603;
	public const COLOR_STYLE_DANGER = 'danger';
	public const COLOR_STYLE_WARNING = 'warning';
	public const COLOR_STYLE_MUTED = 'muted';
	public const COLOR_STYLE_SUCCESS = 'success';
	public const COLOR_STYLE_INFO = 'info';
	public const COLOR_STYLE_WHITE = 'white';
	public const TOOLTIP_POSITION_TOP = 'top';
	public const TOOLTIP_POSITION_BOTTOM = 'bottom';
	public const TOOLTIP_POSITION_LEFT = 'left';
	public const TOOLTIP_POSITION_RIGHT = 'right';

	/**
	 * @return $this
	 */
	public function actioncode(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function activate(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function activity(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function add(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function apiClients(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function apiKeys(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function attentionRequired(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function audience(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function back(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function backToCurrent(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function backup(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function box(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function browse(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function bugreport(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function build(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function business(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function button(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function cache(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function calculate(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function calendar(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function campaigns(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function cancel(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function caretDown(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function caretUp(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function category(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function changeOrder(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function changelog(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function check(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function code(): self
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
	 * @return $this
	 */
	public function collapseLeft(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function collapseRight(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function colors(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function combination(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function combine(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function commandDeck(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function commands(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function comment(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function comtypes(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function contentTypes(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function convert(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function copy(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function countdown(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function countries(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function css(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function csv(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function customVariables(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function deactivate(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function deactivated(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function delete(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function deleteSign(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function deleted(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function deselectAll(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function destroy(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function developer(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function disabled(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function discard(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function disconnect(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function download(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function draft(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function drag(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function dropdown(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function edit(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function editor(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function email(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function enabled(): self
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
	public function expandLeft(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function expandRight(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function export(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function exportArchive(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function featuretables(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function feedback(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function file(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function filter(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function first(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function flat(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function forward(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function generate(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function global(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function globalContent(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function grouped(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function help(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function hide(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function home(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function html(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function id(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function image(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function import(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function inactive(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function information(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function itemActive(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function itemInactive(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function jumpTo(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function jumpUp(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function keyword(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function last(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function link(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function list(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function load(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function locked(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function logIn(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function logOut(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function lookup(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function mailHeaderTitle(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function mailHeaders(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function mailTests(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function mails(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function maximize(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function media(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function menu(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function merge(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function message(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function minus(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function money(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function move(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function moveLeftRight(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function moveTo(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function moveUpDown(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function news(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function next(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function no(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function notAvailable(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function notRequired(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function notepad(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function off(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function ok(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function oms(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function on(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function options(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function page(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function pagemodel(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function pause(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function pin(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function play(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function plus(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function positionAny(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function positionBottom(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function positionTop(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function presets(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function preview(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function previous(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function price(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function print(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function printer(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function product(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function proms(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function proofing(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function properties(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function publish(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function published(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function rating(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function recordType(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function refresh(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function required(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function reset(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function restore(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function revert(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function review(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function save(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function search(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function selectAll(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function selected(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function send(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function settings(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function shop(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function snowflake(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function sort(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function sortAsc(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function sortDesc(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function sorting(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function status(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function stop(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function structural(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function suggest(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function switch(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function switchCampaign(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function switchMode(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function table(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function tags(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function tariffMatrix(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function task(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function template(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function tenant(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function text(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function time(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function timeTracker(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function toggle(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function tools(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function translation(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function transmission(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function tree(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function uncombine(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function uncombined(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function undelete(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function unlock(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function unlocked(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function upload(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function user(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function users(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function utils(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function validate(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function variables(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function variations(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function view(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function waiting(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function warning(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function whitelist(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function wizard(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function wordwrap(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function workflow(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function xml(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function yes(): self
	{
		/* ... */
	}


	public function spinner(): self
	{
		/* ... */
	}


	/**
	 * Sets the icon's type.
	 * @param string $name
	 * @param string $prefix
	 * @return $this
	 */
	public function setType(string $name, string $prefix = ''): self
	{
		/* ... */
	}


	/**
	 * Gets the icon's type/name, e.g. `folder-open`.
	 * @return string
	 */
	public function getType(): string
	{
		/* ... */
	}


	/**
	 * Gets the icon's prefix, e.g. `fa` or `fas`.
	 * @return string
	 */
	public function getPrefix(): string
	{
		/* ... */
	}


	/**
	 * Adds a class name that will be added to the
	 * icon tag's class attribute.
	 *
	 * @param string $className
	 * @return $this
	 */
	public function addClass(string $className): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeSpinner(): self
	{
		/* ... */
	}


	/**
	 * @param string $style
	 * @throws UI_Exception
	 */
	public static function requireValidColorStyle(string $style): void
	{
		/* ... */
	}


	public function makeColorStyle(string $style): self
	{
		/* ... */
	}


	/**
	 * Resets the color style to the default mode to
	 * inherit the surrounding text's color.
	 *
	 * @return $this
	 */
	public function makeRegular(): self
	{
		/* ... */
	}


	public function makeDangerous(): self
	{
		/* ... */
	}


	public function makeWarning(): self
	{
		/* ... */
	}


	public function makeMuted(): self
	{
		/* ... */
	}


	public function makeSuccess(): self
	{
		/* ... */
	}


	public function makeInformation(): self
	{
		/* ... */
	}


	public function makeWhite(): self
	{
		/* ... */
	}


	/**
	 * Gives the icon a clickable style: the cursor
	 * will be the click-enabled cursor.
	 *
	 * Optionally, a click handling statement can be specified.
	 */
	public function makeClickable(?string $statement = null): self
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface $text
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setTooltip($text): self
	{
		/* ... */
	}


	public function setID(string $id): self
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @param mixed $value
	 * @return $this
	 */
	public function setAttribute(string $name, $value): self
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}


	public function getID(): string
	{
		/* ... */
	}


	public function getTooltip(): string
	{
		/* ... */
	}


	/**
	 * Override the toString method to allow an easier syntax
	 * without having to call the render method manually.
	 */
	public function __toString()
	{
		/* ... */
	}


	/**
	 * Displays a help cursor when hovering over the icon.
	 * @return self
	 */
	public function cursorHelp(): self
	{
		/* ... */
	}


	/**
	 * Sets a style for the icon's <code>style</code> attribute.
	 *
	 * Example:
	 *
	 * <pre>
	 * $icon->setStyle('margin-right', '10px');
	 * </pre>
	 *
	 * @param string $name
	 * @param string $value
	 * @return $this
	 */
	public function setStyle(string $name, string $value): self
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @return $this
	 */
	public function removeStyle(string $name): self
	{
		/* ... */
	}


	public static function requireValidTooltipPosition(string $pos): void
	{
		/* ... */
	}


	/**
	 * Sets the position for the tooltip if one is used.
	 *
	 * @param string $position "top" (default), "left", "right", "bottom"
	 * @return self
	 * @throws UI_Exception
	 */
	public function setTooltipPosition(string $position = self::TOOLTIP_POSITION_TOP): self
	{
		/* ... */
	}


	public function makeTooltipTop(): self
	{
		/* ... */
	}


	public function makeTooltipLeft(): self
	{
		/* ... */
	}


	public function makeTooltipRight(): self
	{
		/* ... */
	}


	public function makeTooltipBottom(): self
	{
		/* ... */
	}


	public function setHidden(bool $hidden = true): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Icons/IconCollection.php`

```php
namespace UI\Icons;

use AppUtils\FileHelper\JSONFile as JSONFile;

/**
 * Singleton registry of all available icons — both framework standard icons
 * and application custom icons. On first access the collection loads and
 * merges the two JSON sources, normalises IDs (hyphens/spaces → underscores),
 * and sorts the result alphabetically by icon ID.
 *
 * Custom icons with the same ID as a standard icon replace the standard entry,
 * allowing applications to override framework icons.
 *
 * @package UI
 * @subpackage Icons
 * @see IconInfo
 */
class IconCollection
{
	public static function getInstance(): self
	{
		/* ... */
	}


	/**
	 * Resets the singleton instance to null.
	 *
	 * @internal For use in tests only — allows each test to start with a
	 *           fresh collection instance and prevents state leaking between
	 *           test cases.
	 * @return void
	 */
	public static function resetInstance(): void
	{
		/* ... */
	}


	/**
	 * Returns all available icons sorted alphabetically by ID.
	 *
	 * @return IconInfo[]
	 */
	public function getAll(): array
	{
		/* ... */
	}


	/**
	 * Returns only the framework standard icons, sorted alphabetically by ID.
	 *
	 * @return IconInfo[]
	 */
	public function getStandardIcons(): array
	{
		/* ... */
	}


	/**
	 * Returns only the application custom icons, sorted alphabetically by ID.
	 *
	 * @return IconInfo[]
	 */
	public function getCustomIcons(): array
	{
		/* ... */
	}


	/**
	 * Checks whether an icon with the given ID exists in the collection.
	 *
	 * @param string $iconID
	 * @return bool
	 */
	public function idExists(string $iconID): bool
	{
		/* ... */
	}


	/**
	 * Returns the {@see IconInfo} for the given icon ID.
	 *
	 * NOTE: The ID must be in its normalised form — hyphens and spaces
	 * converted to underscores (e.g. `time_tracker`, not `time-tracker`).
	 * To look up an icon using an un-normalised key, normalise it first via
	 * {@see IconInfo::normaliseID()}. Use {@see self::idExists()}
	 * to test existence before calling this method.
	 *
	 * @param string $iconID Normalised icon ID (underscores, no hyphens/spaces).
	 * @return IconInfo
	 * @throws \RuntimeException When no icon with the given ID exists.
	 */
	public function getByID(string $iconID): IconInfo
	{
		/* ... */
	}


	/**
	 * Returns the total number of icons in the collection.
	 *
	 * @return int
	 */
	public function countIcons(): int
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Icons/IconInfo.php`

```php
namespace UI\Icons;

use UI_Icon as UI_Icon;

/**
 * Read-only value object for a single available icon. Holds the icon's
 * ID, FA icon name, FA prefix, and whether it is a custom (application)
 * icon or a standard (framework) icon. Provides a factory method to
 * create the matching {@see UI_Icon} instance.
 *
 * @package UI
 * @subpackage Icons
 * @see IconCollection
 */
class IconInfo
{
	/**
	 * @return string
	 */
	public function getID(): string
	{
		/* ... */
	}


	/**
	 * @return string
	 */
	public function getIconName(): string
	{
		/* ... */
	}


	/**
	 * @return string
	 */
	public function getPrefix(): string
	{
		/* ... */
	}


	/**
	 * @return bool
	 */
	public function isCustom(): bool
	{
		/* ... */
	}


	/**
	 * @return bool
	 */
	public function isStandard(): bool
	{
		/* ... */
	}


	/**
	 * Creates a UI_Icon instance with this icon's type pre-configured.
	 *
	 * When the prefix is empty, {@see UI_Icon::setType()} is called with
	 * one argument only (matching the generated method convention). When a
	 * prefix is present it is passed as the second argument.
	 *
	 * @return UI_Icon
	 */
	public function createIcon(): UI_Icon
	{
		/* ... */
	}


	/**
	 * Normalises an icon ID by replacing hyphens and spaces with underscores.
	 *
	 * This is the canonical normalisation method used by both the runtime
	 * registry ({@see IconCollection}) and the build-time code generator
	 * ({@see \Application\Composer\IconBuilder\IconsReader}). Always delegate
	 * to this method rather than repeating the inline formula.
	 *
	 * Example:
	 * ```php
	 * IconInfo::normaliseID('time-tracker');  // → 'time_tracker'
	 * IconInfo::normaliseID('my icon name');  // → 'my_icon_name'
	 * IconInfo::normaliseID('already_ok');    // → 'already_ok'
	 * ```
	 *
	 * @param string $id Raw icon ID (may contain hyphens or spaces).
	 * @return string Normalised icon ID with underscores only.
	 * @since 1.0.0
	 */
	public static function normaliseID(string $id): string
	{
		/* ... */
	}


	/**
	 * Returns the method name used in the icon classes, derived by
	 * converting the underscore-separated ID to camelCase.
	 *
	 * Examples: `attention_required` → `attentionRequired`, `add` → `add`.
	 *
	 * @return string
	 */
	public function getMethodName(): string
	{
		/* ... */
	}


	/**
	 * Returns the full icon name including prefix, e.g. `far:sun`.
	 * When the prefix is empty, only the icon name is returned, e.g. `rocket`.
	 *
	 * @return string
	 */
	public function getFullIconName(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Interfaces/ActivatableInterface.php`

```php
namespace UI\Interfaces;

use UI\Traits\ActivatableTrait as ActivatableTrait;

/**
 * @see ActivatableTrait
 */
interface ActivatableInterface
{
	/**
	 * @param bool $active
	 * @return self
	 */
	public function makeActive(bool $active = true): self;


	public function isActive(): bool;
}


```
###  Path: `/src/classes/UI/Interfaces/ActivatableInterface.php`

```php
namespace UI\Interfaces;

use UI\Traits\ActivatableTrait as ActivatableTrait;

/**
 * @see ActivatableTrait
 */
interface ActivatableInterface
{
	/**
	 * @param bool $active
	 * @return self
	 */
	public function makeActive(bool $active = true): self;


	public function isActive(): bool;
}


```
###  Path: `/src/classes/UI/Interfaces/Badge.php`

```php
namespace ;

interface UI_Interfaces_Badge
{
	/**
	 * @param string|number|UI_Renderable_Interface|NULL $label
	 * @return $this
	 */
	public function setLabel($label): self;


	/**
	 * @param string|number|UI_Renderable_Interface|NULL $code
	 * @return $this
	 */
	public function setWrapper($code): self;


	/**
	 * @return $this
	 */
	public function makeDangerous(): self;


	/**
	 * @return $this
	 */
	public function makeInfo(): self;


	/**
	 * @return $this
	 */
	public function makeSuccess(): self;


	/**
	 * @return $this
	 */
	public function makeWarning(): self;


	/**
	 * @return $this
	 */
	public function makeInverse(): self;


	/**
	 * @return $this
	 */
	public function makeInactive(): self;


	/**
	 * @return $this
	 */
	public function cursorHelp(): self;


	/**
	 * @return $this
	 */
	public function makeLarge(): self;


	public function getLabel(): string;
}


```
###  Path: `/src/classes/UI/Interfaces/Badge.php`

```php
namespace ;

interface UI_Interfaces_Badge
{
	/**
	 * @param string|number|UI_Renderable_Interface|NULL $label
	 * @return $this
	 */
	public function setLabel($label): self;


	/**
	 * @param string|number|UI_Renderable_Interface|NULL $code
	 * @return $this
	 */
	public function setWrapper($code): self;


	/**
	 * @return $this
	 */
	public function makeDangerous(): self;


	/**
	 * @return $this
	 */
	public function makeInfo(): self;


	/**
	 * @return $this
	 */
	public function makeSuccess(): self;


	/**
	 * @return $this
	 */
	public function makeWarning(): self;


	/**
	 * @return $this
	 */
	public function makeInverse(): self;


	/**
	 * @return $this
	 */
	public function makeInactive(): self;


	/**
	 * @return $this
	 */
	public function cursorHelp(): self;


	/**
	 * @return $this
	 */
	public function makeLarge(): self;


	public function getLabel(): string;
}


```
###  Path: `/src/classes/UI/Interfaces/Bootstrap.php`

```php
namespace ;

use AppUtils\Interfaces\ClassableInterface as ClassableInterface;
use UI\Interfaces\NamedItemInterface as NamedItemInterface;

interface UI_Interfaces_Bootstrap extends ClassableInterface, UI_Renderable_Interface, NamedItemInterface
{
	public const ERROR_CHILD_NAME_ALREADY_EXISTS = 18601;
	public const ERROR_NOT_A_CHILD_ELEMENT_OF_PARENT = 18602;
	public const ERROR_INVALID_CHILD_ELEMENT = 18603;

	/**
	 * @param string $name
	 * @return bool
	 */
	public function isNamed(string $name): bool;


	public function getID(): string;


	public function setID(string $id): self;


	public function setAttribute(string $name, $value): self;


	public function getAttribute(string $name, $default = null);


	public function hasAttribute(string $name): bool;


	public function renderAttributes(): string;


	/**
	 * @param string $name
	 * @param string|number|NULL $value
	 * @return $this
	 */
	public function setStyle(string $name, $value): self;


	public function appendChild(UI_Bootstrap $child): self;


	public function setParent(UI_Bootstrap $parent): self;


	public function getParent(): ?UI_Bootstrap;


	public function createChild(string $type): UI_Interfaces_Bootstrap;


	public function hasChild(string $name): bool;


	/**
	 * @return UI_Bootstrap[]
	 */
	public function getChildren(): array;


	public function hasChildren(): bool;
}


```
###  Path: `/src/classes/UI/Interfaces/Bootstrap.php`

```php
namespace ;

use AppUtils\Interfaces\ClassableInterface as ClassableInterface;
use UI\Interfaces\NamedItemInterface as NamedItemInterface;

interface UI_Interfaces_Bootstrap extends ClassableInterface, UI_Renderable_Interface, NamedItemInterface
{
	public const ERROR_CHILD_NAME_ALREADY_EXISTS = 18601;
	public const ERROR_NOT_A_CHILD_ELEMENT_OF_PARENT = 18602;
	public const ERROR_INVALID_CHILD_ELEMENT = 18603;

	/**
	 * @param string $name
	 * @return bool
	 */
	public function isNamed(string $name): bool;


	public function getID(): string;


	public function setID(string $id): self;


	public function setAttribute(string $name, $value): self;


	public function getAttribute(string $name, $default = null);


	public function hasAttribute(string $name): bool;


	public function renderAttributes(): string;


	/**
	 * @param string $name
	 * @param string|number|NULL $value
	 * @return $this
	 */
	public function setStyle(string $name, $value): self;


	public function appendChild(UI_Bootstrap $child): self;


	public function setParent(UI_Bootstrap $parent): self;


	public function getParent(): ?UI_Bootstrap;


	public function createChild(string $type): UI_Interfaces_Bootstrap;


	public function hasChild(string $name): bool;


	/**
	 * @return UI_Bootstrap[]
	 */
	public function getChildren(): array;


	public function hasChildren(): bool;
}


```
###  Path: `/src/classes/UI/Interfaces/Button.php`

```php
namespace ;

use AppUtils\Interfaces\ClassableInterface as ClassableInterface;
use AppUtils\Interfaces\StringableInterface as StringableInterface;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;
use UI\Interfaces\ButtonLayoutInterface as ButtonLayoutInterface;

interface UI_Interfaces_Button extends Application_Interfaces_Iconizable, ClassableInterface, Application_LockableItem_Interface, UI_Interfaces_ClientConfirmable, UI_Interfaces_Conditional, ButtonLayoutInterface
{
	/**
	 * @param string|number|UI_Renderable_Interface|NULL $reason
	 * @return $this
	 */
	public function disable($reason = ''): self;


	public function isDisabled(): bool;


	/**
	 * @param string $statement
	 * @return $this
	 */
	public function click(string $statement): self;


	/**
	 * @param string|AdminURLInterface $url
	 * @param string $target
	 * @return $this
	 */
	public function link($url, string $target = ''): self;


	/**
	 * @param string|number|UI_Renderable_Interface $tooltip
	 * @return $this
	 */
	public function setTooltip($tooltip): self;


	/**
	 * @param string|number|UI_Renderable_Interface $text
	 * @return $this
	 */
	public function setLoadingText($text): self;


	/**
	 * @param string $id
	 * @return $this
	 */
	public function setID(string $id): self;


	/**
	 * @param string|number|StringableInterface|NULL $label
	 * @return $this
	 */
	public function setLabel($label): self;


	public function getLabel(): string;


	public function getID(): string;


	public function getTooltip(): string;
}


```
###  Path: `/src/classes/UI/Interfaces/Button.php`

```php
namespace ;

use AppUtils\Interfaces\ClassableInterface as ClassableInterface;
use AppUtils\Interfaces\StringableInterface as StringableInterface;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;
use UI\Interfaces\ButtonLayoutInterface as ButtonLayoutInterface;

interface UI_Interfaces_Button extends Application_Interfaces_Iconizable, ClassableInterface, Application_LockableItem_Interface, UI_Interfaces_ClientConfirmable, UI_Interfaces_Conditional, ButtonLayoutInterface
{
	/**
	 * @param string|number|UI_Renderable_Interface|NULL $reason
	 * @return $this
	 */
	public function disable($reason = ''): self;


	public function isDisabled(): bool;


	/**
	 * @param string $statement
	 * @return $this
	 */
	public function click(string $statement): self;


	/**
	 * @param string|AdminURLInterface $url
	 * @param string $target
	 * @return $this
	 */
	public function link($url, string $target = ''): self;


	/**
	 * @param string|number|UI_Renderable_Interface $tooltip
	 * @return $this
	 */
	public function setTooltip($tooltip): self;


	/**
	 * @param string|number|UI_Renderable_Interface $text
	 * @return $this
	 */
	public function setLoadingText($text): self;


	/**
	 * @param string $id
	 * @return $this
	 */
	public function setID(string $id): self;


	/**
	 * @param string|number|StringableInterface|NULL $label
	 * @return $this
	 */
	public function setLabel($label): self;


	public function getLabel(): string;


	public function getID(): string;


	public function getTooltip(): string;
}


```
###  Path: `/src/classes/UI/Interfaces/ButtonLayoutInterface.php`

```php
namespace UI\Interfaces;

use UI\Traits\ButtonLayoutTrait as ButtonLayoutTrait;

/**
 * Interface for the available button layouts.
 *
 * @package User Interface
 * @subpackage Interfaces
 *
 * @see ButtonLayoutTrait
 */
interface ButtonLayoutInterface extends ActivatableInterface
{
	public const LAYOUT_DEFAULT = 'default';
	public const LAYOUT_DEVELOPER = 'developer';
	public const LAYOUT_WARNING = 'warning';
	public const LAYOUT_INVERSE = 'inverse';
	public const LAYOUT_SUCCESS = 'success';
	public const LAYOUT_INFO = 'info';
	public const LAYOUT_DANGER = 'danger';
	public const LAYOUT_PRIMARY = 'primary';
	public const LAYOUT_LINK = 'link';

	/**
	 * Styles the button as a button for a dangerous operation, like deleting records.
	 *
	 * @param bool $enable Can be used as a toggle. If set to false, the layout will not be applied.
	 * @return $this
	 */
	public function makeDangerous(bool $enable = true): self;


	/**
	 * @param bool $enable Can be used as a toggle. If set to false, the layout will not be applied.
	 * @return $this
	 */
	public function makePrimary(bool $enable = true): self;


	/**
	 * @param bool $enable Can be used as a toggle. If set to false, the layout will not be applied.
	 * @return $this
	 */
	public function makeSuccess(bool $enable = true): self;


	/**
	 * @param bool $enable Can be used as a toggle. If set to false, the layout will not be applied.
	 * @return $this
	 */
	public function makeDeveloper(bool $enable = true): self;


	/**
	 * @param bool $enable Can be used as a toggle. If set to false, the layout will not be applied.
	 * @return $this
	 */
	public function makeWarning(bool $enable = true): self;


	/**
	 * @param bool $enable Can be used as a toggle. If set to false, the layout will not be applied.
	 * @return $this
	 */
	public function makeInfo(bool $enable = true): self;


	/**
	 * @param bool $enable Can be used as a toggle. If set to false, the layout will not be applied.
	 * @return $this
	 */
	public function makeInverse(bool $enable = true): self;


	/**
	 * Sets the button's layout to the specified type.
	 *
	 * @param string $layoutID
	 * @param bool $enabled Can be used as a toggle. If set to false, the layout will not be applied.
	 * @return $this
	 */
	public function makeLayout(string $layoutID, bool $enabled = true): self;


	/**
	 * Sets the button's layout when it is active.
	 *
	 * @param string $layoutID
	 * @return $this
	 */
	public function makeActiveLayout(string $layoutID): self;
}


```
###  Path: `/src/classes/UI/Interfaces/ButtonLayoutInterface.php`

```php
namespace UI\Interfaces;

use UI\Traits\ButtonLayoutTrait as ButtonLayoutTrait;

/**
 * Interface for the available button layouts.
 *
 * @package User Interface
 * @subpackage Interfaces
 *
 * @see ButtonLayoutTrait
 */
interface ButtonLayoutInterface extends ActivatableInterface
{
	public const LAYOUT_DEFAULT = 'default';
	public const LAYOUT_DEVELOPER = 'developer';
	public const LAYOUT_WARNING = 'warning';
	public const LAYOUT_INVERSE = 'inverse';
	public const LAYOUT_SUCCESS = 'success';
	public const LAYOUT_INFO = 'info';
	public const LAYOUT_DANGER = 'danger';
	public const LAYOUT_PRIMARY = 'primary';
	public const LAYOUT_LINK = 'link';

	/**
	 * Styles the button as a button for a dangerous operation, like deleting records.
	 *
	 * @param bool $enable Can be used as a toggle. If set to false, the layout will not be applied.
	 * @return $this
	 */
	public function makeDangerous(bool $enable = true): self;


	/**
	 * @param bool $enable Can be used as a toggle. If set to false, the layout will not be applied.
	 * @return $this
	 */
	public function makePrimary(bool $enable = true): self;


	/**
	 * @param bool $enable Can be used as a toggle. If set to false, the layout will not be applied.
	 * @return $this
	 */
	public function makeSuccess(bool $enable = true): self;


	/**
	 * @param bool $enable Can be used as a toggle. If set to false, the layout will not be applied.
	 * @return $this
	 */
	public function makeDeveloper(bool $enable = true): self;


	/**
	 * @param bool $enable Can be used as a toggle. If set to false, the layout will not be applied.
	 * @return $this
	 */
	public function makeWarning(bool $enable = true): self;


	/**
	 * @param bool $enable Can be used as a toggle. If set to false, the layout will not be applied.
	 * @return $this
	 */
	public function makeInfo(bool $enable = true): self;


	/**
	 * @param bool $enable Can be used as a toggle. If set to false, the layout will not be applied.
	 * @return $this
	 */
	public function makeInverse(bool $enable = true): self;


	/**
	 * Sets the button's layout to the specified type.
	 *
	 * @param string $layoutID
	 * @param bool $enabled Can be used as a toggle. If set to false, the layout will not be applied.
	 * @return $this
	 */
	public function makeLayout(string $layoutID, bool $enabled = true): self;


	/**
	 * Sets the button's layout when it is active.
	 *
	 * @param string $layoutID
	 * @return $this
	 */
	public function makeActiveLayout(string $layoutID): self;
}


```
###  Path: `/src/classes/UI/Interfaces/ButtonSizeInterface.php`

```php
namespace UI\Interfaces;

use UI\Traits\ButtonSizeTrait as ButtonSizeTrait;

/**
 * @see ButtonSizeTrait
 */
interface ButtonSizeInterface
{
	/** @var array<int,array<string,string>> */
	public const BUTTON_SIZES_TABLE = [
		2 => array(
		            self::SIZE_LARGE => 'large',
		            self::SIZE_SMALL => 'small',
		            self::SIZE_MINI => 'mini'
		        ),
		4 => array(
		            self::SIZE_LARGE => 'lg',
		            self::SIZE_SMALL => 'sm',
		            self::SIZE_MINI => 'xs'
		        ),
	];

	public const SIZE_MINI = 'mini';
	public const SIZE_LARGE = 'large';
	public const SIZE_SMALL = 'small';
	public const ERROR_UNKNOWN_BOOTSTRAP_SIZE_VERSION = 66601;
	public const ERROR_UNKNOWN_BOOTSTRAP_SIZE = 66602;

	/**
	 * @return $this
	 */
	public function makeSmall(): self;


	/**
	 * @return $this
	 */
	public function makeMini(): self;


	/**
	 * @return $this
	 */
	public function makeLarge(): self;


	/**
	 * @param string $size
	 * @return $this
	 */
	public function makeSize(string $size): self;


	public function getSize(): ?string;


	public function isLarge(): bool;


	public function isSmall(): bool;


	public function isMini(): bool;
}


```
###  Path: `/src/classes/UI/Interfaces/ButtonSizeInterface.php`

```php
namespace UI\Interfaces;

use UI\Traits\ButtonSizeTrait as ButtonSizeTrait;

/**
 * @see ButtonSizeTrait
 */
interface ButtonSizeInterface
{
	/** @var array<int,array<string,string>> */
	public const BUTTON_SIZES_TABLE = [
		2 => array(
		            self::SIZE_LARGE => 'large',
		            self::SIZE_SMALL => 'small',
		            self::SIZE_MINI => 'mini'
		        ),
		4 => array(
		            self::SIZE_LARGE => 'lg',
		            self::SIZE_SMALL => 'sm',
		            self::SIZE_MINI => 'xs'
		        ),
	];

	public const SIZE_MINI = 'mini';
	public const SIZE_LARGE = 'large';
	public const SIZE_SMALL = 'small';
	public const ERROR_UNKNOWN_BOOTSTRAP_SIZE_VERSION = 66601;
	public const ERROR_UNKNOWN_BOOTSTRAP_SIZE = 66602;

	/**
	 * @return $this
	 */
	public function makeSmall(): self;


	/**
	 * @return $this
	 */
	public function makeMini(): self;


	/**
	 * @return $this
	 */
	public function makeLarge(): self;


	/**
	 * @param string $size
	 * @return $this
	 */
	public function makeSize(string $size): self;


	public function getSize(): ?string;


	public function isLarge(): bool;


	public function isSmall(): bool;


	public function isMini(): bool;
}


```
###  Path: `/src/classes/UI/Interfaces/CapturableInterface.php`

```php
namespace UI\Interfaces;

use AppUtils\Interfaces\StringableInterface as StringableInterface;

interface CapturableInterface
{
	public function startCapture(): self;


	public function endCapture(): self;


	public function endCaptureAppend(): self;


	/**
	 * @param string|number|StringableInterface|NULL $content
	 * @return self
	 */
	public function setContent($content): self;


	/**
	 * @param string|number|StringableInterface|NULL $content
	 * @return self
	 */
	public function appendContent($content): self;


	public function getContent(): string;
}


```
###  Path: `/src/classes/UI/Interfaces/CapturableInterface.php`

```php
namespace UI\Interfaces;

use AppUtils\Interfaces\StringableInterface as StringableInterface;

interface CapturableInterface
{
	public function startCapture(): self;


	public function endCapture(): self;


	public function endCaptureAppend(): self;


	/**
	 * @param string|number|StringableInterface|NULL $content
	 * @return self
	 */
	public function setContent($content): self;


	/**
	 * @param string|number|StringableInterface|NULL $content
	 * @return self
	 */
	public function appendContent($content): self;


	public function getContent(): string;
}


```
###  Path: `/src/classes/UI/Interfaces/ClientConfirmable.php`

```php
namespace ;

interface UI_Interfaces_ClientConfirmable
{
	/**
	 * Adds a confirmation message to the element, as a dialog that is shown
	 * before the action is executed.
	 *
	 * @param string|number|UI_Renderable_Interface|NULL $message
	 * @param boolean $withInput Whether to have the user confirm the operation by typing a confirmation string.
	 * @return $this
	 */
	public function makeConfirm($message, bool $withInput = false): self;


	public function getConfirmMessage(): UI_ClientConfirmable_Message;


	public function getURL(): string;


	public function isClickable(): bool;


	public function isSubmittable(): bool;


	public function isLinked(): bool;


	public function getJavascript(): string;


	public function getUI(): UI;


	public function isConfirm(): bool;


	public function isDangerous(): bool;
}


```
###  Path: `/src/classes/UI/Interfaces/ClientConfirmable.php`

```php
namespace ;

interface UI_Interfaces_ClientConfirmable
{
	/**
	 * Adds a confirmation message to the element, as a dialog that is shown
	 * before the action is executed.
	 *
	 * @param string|number|UI_Renderable_Interface|NULL $message
	 * @param boolean $withInput Whether to have the user confirm the operation by typing a confirmation string.
	 * @return $this
	 */
	public function makeConfirm($message, bool $withInput = false): self;


	public function getConfirmMessage(): UI_ClientConfirmable_Message;


	public function getURL(): string;


	public function isClickable(): bool;


	public function isSubmittable(): bool;


	public function isLinked(): bool;


	public function getJavascript(): string;


	public function getUI(): UI;


	public function isConfirm(): bool;


	public function isDangerous(): bool;
}


```
###  Path: `/src/classes/UI/Interfaces/Conditional.php`

```php
namespace ;

use Application\Revisionable\RevisionableInterface as RevisionableInterface;

interface UI_Interfaces_Conditional
{
	/**
	 * @param bool $statement
	 * @param string $reason
	 * @return $this
	 */
	public function requireTrue(bool $statement, string $reason = ''): self;


	/**
	 * @param bool $statement
	 * @param string $reason
	 * @return $this
	 */
	public function requireFalse(bool $statement, string $reason = ''): self;


	/**
	 * @param RevisionableInterface $revisionable
	 * @return $this
	 */
	public function requireChanging(RevisionableInterface $revisionable): self;


	/**
	 * @param Application_LockableRecord_Interface $record
	 * @return $this
	 */
	public function requireEditable(Application_LockableRecord_Interface $record): self;


	/**
	 * @return bool
	 */
	public function isValid(): bool;


	public function getInvalidReason(): string;
}


```
###  Path: `/src/classes/UI/Interfaces/Conditional.php`

```php
namespace ;

use Application\Revisionable\RevisionableInterface as RevisionableInterface;

interface UI_Interfaces_Conditional
{
	/**
	 * @param bool $statement
	 * @param string $reason
	 * @return $this
	 */
	public function requireTrue(bool $statement, string $reason = ''): self;


	/**
	 * @param bool $statement
	 * @param string $reason
	 * @return $this
	 */
	public function requireFalse(bool $statement, string $reason = ''): self;


	/**
	 * @param RevisionableInterface $revisionable
	 * @return $this
	 */
	public function requireChanging(RevisionableInterface $revisionable): self;


	/**
	 * @param Application_LockableRecord_Interface $record
	 * @return $this
	 */
	public function requireEditable(Application_LockableRecord_Interface $record): self;


	/**
	 * @return bool
	 */
	public function isValid(): bool;


	public function getInvalidReason(): string;
}


```
###  Path: `/src/classes/UI/Interfaces/ListBuilderInterface.php`

```php
namespace UI\Interfaces;

use Application\FilterSettings\FilterSettingsInterface as FilterSettingsInterface;
use Application\Interfaces\FilterCriteriaInterface as FilterCriteriaInterface;
use DateTime as DateTime;
use UI_DataGrid as UI_DataGrid;
use UI_Page_Sidebar as UI_Page_Sidebar;
use UI_Renderable_Interface as UI_Renderable_Interface;

/**
 * Interface for classes that build a list of records.
 *
 * @package User Interface
 * @package Data Grids
 * @see BaseListBuilder
 */
interface ListBuilderInterface extends UI_Renderable_Interface
{
	public function getDataGrid(): UI_DataGrid;


	public function isColumnEnabled(string $colName): bool;


	public function disableColumn(string $colName): self;


	public function getFilterCriteria(): FilterCriteriaInterface;


	/**
	 * @return \Application\FilterSettings\FilterSettingsInterface|NULL Can be `null` if there are no records to filter.
	 */
	public function getFilterSettings(): ?FilterSettingsInterface;


	/**
	 * Gets the filter criteria with all applied filters and settings.
	 * @return FilterCriteriaInterface
	 */
	public function getFilteredCriteria(): FilterCriteriaInterface;


	public function getFullViewTitle(): string;


	public function getEmptyMessage(): string;


	public function enableAdvancedMode(bool $enabled): self;


	public function addHiddenVars(array $vars): self;


	public function addHiddenVar(string $name, $value): self;


	public function disableEntryActions(): self;


	public function disableMultiActions(): self;


	public function setListID(string $id): self;


	public function handleActions(): self;


	public function addFilterSettings(UI_Page_Sidebar $sidebar): self;


	public function renderDate(?DateTime $date = null): string;


	public function adjustLabel(string $label): string;


	public function getRecordTypeLabelSingular(): string;


	public function getRecordTypeLabelPlural(): string;


	public function getPrimaryColumnName(): string;


	public function debug(bool $enabled = true): self;
}


```
###  Path: `/src/classes/UI/Interfaces/ListBuilderInterface.php`

```php
namespace UI\Interfaces;

use Application\FilterSettings\FilterSettingsInterface as FilterSettingsInterface;
use Application\Interfaces\FilterCriteriaInterface as FilterCriteriaInterface;
use DateTime as DateTime;
use UI_DataGrid as UI_DataGrid;
use UI_Page_Sidebar as UI_Page_Sidebar;
use UI_Renderable_Interface as UI_Renderable_Interface;

/**
 * Interface for classes that build a list of records.
 *
 * @package User Interface
 * @package Data Grids
 * @see BaseListBuilder
 */
interface ListBuilderInterface extends UI_Renderable_Interface
{
	public function getDataGrid(): UI_DataGrid;


	public function isColumnEnabled(string $colName): bool;


	public function disableColumn(string $colName): self;


	public function getFilterCriteria(): FilterCriteriaInterface;


	/**
	 * @return \Application\FilterSettings\FilterSettingsInterface|NULL Can be `null` if there are no records to filter.
	 */
	public function getFilterSettings(): ?FilterSettingsInterface;


	/**
	 * Gets the filter criteria with all applied filters and settings.
	 * @return FilterCriteriaInterface
	 */
	public function getFilteredCriteria(): FilterCriteriaInterface;


	public function getFullViewTitle(): string;


	public function getEmptyMessage(): string;


	public function enableAdvancedMode(bool $enabled): self;


	public function addHiddenVars(array $vars): self;


	public function addHiddenVar(string $name, $value): self;


	public function disableEntryActions(): self;


	public function disableMultiActions(): self;


	public function setListID(string $id): self;


	public function handleActions(): self;


	public function addFilterSettings(UI_Page_Sidebar $sidebar): self;


	public function renderDate(?DateTime $date = null): string;


	public function adjustLabel(string $label): string;


	public function getRecordTypeLabelSingular(): string;


	public function getRecordTypeLabelPlural(): string;


	public function getPrimaryColumnName(): string;


	public function debug(bool $enabled = true): self;
}


```
###  Path: `/src/classes/UI/Interfaces/MessageLayoutInterface.php`

```php
namespace UI\Interfaces;

use UI_Icon as UI_Icon;

interface MessageLayoutInterface
{
	/**
	 * @return $this
	 */
	public function makeDismissable(): self;


	/**
	 * @return $this
	 */
	public function makeNotDismissable(): self;


	/**
	 * @return $this
	 */
	public function makeSlimLayout(): self;


	/**
	 * @return $this
	 */
	public function makeInline(): self;


	/**
	 * @return $this
	 */
	public function makeError(): self;


	/**
	 * @return $this
	 */
	public function makeSuccess(): self;


	/**
	 * @return $this
	 */
	public function makeWarning(): self;


	/**
	 * @return $this
	 */
	public function makeInfo(): self;


	/**
	 * @return $this
	 */
	public function makeLargeLayout(): self;


	/**
	 * @return $this
	 */
	public function makeDefaultLayout(): self;


	/**
	 * @return $this
	 */
	public function enableIcon(): self;


	/**
	 * @return $this
	 */
	public function disableIcon(): self;


	/**
	 * @param UI_Icon $icon
	 * @return $this
	 */
	public function setCustomIcon(UI_Icon $icon): self;
}


```
###  Path: `/src/classes/UI/Interfaces/MessageLayoutInterface.php`

```php
namespace UI\Interfaces;

use UI_Icon as UI_Icon;

interface MessageLayoutInterface
{
	/**
	 * @return $this
	 */
	public function makeDismissable(): self;


	/**
	 * @return $this
	 */
	public function makeNotDismissable(): self;


	/**
	 * @return $this
	 */
	public function makeSlimLayout(): self;


	/**
	 * @return $this
	 */
	public function makeInline(): self;


	/**
	 * @return $this
	 */
	public function makeError(): self;


	/**
	 * @return $this
	 */
	public function makeSuccess(): self;


	/**
	 * @return $this
	 */
	public function makeWarning(): self;


	/**
	 * @return $this
	 */
	public function makeInfo(): self;


	/**
	 * @return $this
	 */
	public function makeLargeLayout(): self;


	/**
	 * @return $this
	 */
	public function makeDefaultLayout(): self;


	/**
	 * @return $this
	 */
	public function enableIcon(): self;


	/**
	 * @return $this
	 */
	public function disableIcon(): self;


	/**
	 * @param UI_Icon $icon
	 * @return $this
	 */
	public function setCustomIcon(UI_Icon $icon): self;
}


```
###  Path: `/src/classes/UI/Interfaces/MessageWrapperInterface.php`

```php
namespace UI\Interfaces;

use UI_Message as UI_Message;

interface MessageWrapperInterface extends MessageLayoutInterface
{
	public function getMessage(): UI_Message;
}


```
###  Path: `/src/classes/UI/Interfaces/MessageWrapperInterface.php`

```php
namespace UI\Interfaces;

use UI_Message as UI_Message;

interface MessageWrapperInterface extends MessageLayoutInterface
{
	public function getMessage(): UI_Message;
}


```
###  Path: `/src/classes/UI/Interfaces/NamedItemInterface.php`

```php
namespace UI\Interfaces;

/**
 * Interface for elements that can be given a name.
 *
 * @package User Interface
 * @subpackage Interfaces
 */
interface NamedItemInterface
{
	/**
	 * Sets the element's name, which can be used to retrieve it when used in collections.
	 * @param string $name
	 * @return $this
	 */
	public function setName(string $name): self;


	/**
	 * @return string|null
	 */
	public function getName(): ?string;
}


```
###  Path: `/src/classes/UI/Interfaces/NamedItemInterface.php`

```php
namespace UI\Interfaces;

/**
 * Interface for elements that can be given a name.
 *
 * @package User Interface
 * @subpackage Interfaces
 */
interface NamedItemInterface
{
	/**
	 * Sets the element's name, which can be used to retrieve it when used in collections.
	 * @param string $name
	 * @return $this
	 */
	public function setName(string $name): self;


	/**
	 * @return string|null
	 */
	public function getName(): ?string;
}


```
###  Path: `/src/classes/UI/Interfaces/PageTemplateInterface.php`

```php
namespace UI\Interfaces;

/**
 * Template class: this class is instantiated for each
 * template file, and is the context of the template
 * in $this, when not using a class based template.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see \UI_Page_Template
 */
interface PageTemplateInterface
{
	/**
	 * @param array<string,mixed> $vars
	 * @return $this
	 */
	public function setVars(array $vars): self;


	/**
	 * @param string $name
	 * @param mixed $value
	 * @return $this
	 */
	public function setVar(string $name, $value): self;


	public function getVar(string $name, $default = null);


	/**
	 * Retrieves the variable, and ensures that it is an instance
	 * of the specified class.
	 *
	 * @template ClassInstanceType
	 * @param string $name
	 * @param class-string<ClassInstanceType> $className
	 * @return ClassInstanceType
	 */
	public function getObjectVar(string $name, string $className);


	public function getBoolVar(string $name): bool;


	public function getArrayVar(string $name): array;


	public function getStringVar(string $name): string;


	public function printVar(string $name, $default = null): self;


	public function getLogoutURL(): string;


	/**
	 * @param array<string,string|int|float> $params
	 * @return string
	 */
	public function buildURL(array $params = []): string;


	public function getImageURL(string $imageName): string;


	/**
	 * Checks if the specified variable has been set.
	 */
	public function hasVar(string $name): bool;


	public function hasVarNonEmpty(string $name): bool;


	public function getAppNameShort(): string;


	public function getAppName(): string;
}


```
###  Path: `/src/classes/UI/Interfaces/PageTemplateInterface.php`

```php
namespace UI\Interfaces;

/**
 * Template class: this class is instantiated for each
 * template file, and is the context of the template
 * in $this, when not using a class based template.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see \UI_Page_Template
 */
interface PageTemplateInterface
{
	/**
	 * @param array<string,mixed> $vars
	 * @return $this
	 */
	public function setVars(array $vars): self;


	/**
	 * @param string $name
	 * @param mixed $value
	 * @return $this
	 */
	public function setVar(string $name, $value): self;


	public function getVar(string $name, $default = null);


	/**
	 * Retrieves the variable, and ensures that it is an instance
	 * of the specified class.
	 *
	 * @template ClassInstanceType
	 * @param string $name
	 * @param class-string<ClassInstanceType> $className
	 * @return ClassInstanceType
	 */
	public function getObjectVar(string $name, string $className);


	public function getBoolVar(string $name): bool;


	public function getArrayVar(string $name): array;


	public function getStringVar(string $name): string;


	public function printVar(string $name, $default = null): self;


	public function getLogoutURL(): string;


	/**
	 * @param array<string,string|int|float> $params
	 * @return string
	 */
	public function buildURL(array $params = []): string;


	public function getImageURL(string $imageName): string;


	/**
	 * Checks if the specified variable has been set.
	 */
	public function hasVar(string $name): bool;


	public function hasVarNonEmpty(string $name): bool;


	public function getAppNameShort(): string;


	public function getAppName(): string;
}


```
###  Path: `/src/classes/UI/Interfaces/Renderable.php`

```php
namespace ;

use AppUtils\Interfaces\RenderableInterface as RenderableInterface;

/**
 * Interface for renderable elements, which can generate HTML.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see UI_Renderable
 * @see UI_Traits_RenderableGeneric
 */
interface UI_Renderable_Interface extends RenderableInterface
{
	public function getPage(): UI_Page;


	public function getTheme(): UI_Themes_Theme;


	public function getUI(): UI;


	public function getInstanceID(): string;


	public function getRenderer(): UI_Themes_Theme_ContentRenderer;
}


```
###  Path: `/src/classes/UI/Interfaces/Renderable.php`

```php
namespace ;

use AppUtils\Interfaces\RenderableInterface as RenderableInterface;

/**
 * Interface for renderable elements, which can generate HTML.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see UI_Renderable
 * @see UI_Traits_RenderableGeneric
 */
interface UI_Renderable_Interface extends RenderableInterface
{
	public function getPage(): UI_Page;


	public function getTheme(): UI_Themes_Theme;


	public function getUI(): UI;


	public function getInstanceID(): string;


	public function getRenderer(): UI_Themes_Theme_ContentRenderer;
}


```
###  Path: `/src/classes/UI/Interfaces/StatusElementContainer.php`

```php
namespace ;

/**
 * Interface for UI elements that allow status elements
 * to be added, like warning icons and the like.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see UI_Traits_StatusElementContainer
 */
interface UI_Interfaces_StatusElementContainer
{
	/**
	 * @param UI_Icon $icon
	 * @return UI_Interfaces_StatusElementContainer
	 */
	public function addStatusIcon(UI_Icon $icon);


	/**
	 * @param UI_Renderable_Interface $element
	 * @return UI_Interfaces_StatusElementContainer
	 */
	public function addStatusElement(UI_Renderable_Interface $element);


	public function hasStatusElements(): bool;


	/**
	 * @return UI_Renderable_Interface[]
	 */
	public function getStatusElements(): array;
}


```
###  Path: `/src/classes/UI/Interfaces/StatusElementContainer.php`

```php
namespace ;

/**
 * Interface for UI elements that allow status elements
 * to be added, like warning icons and the like.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see UI_Traits_StatusElementContainer
 */
interface UI_Interfaces_StatusElementContainer
{
	/**
	 * @param UI_Icon $icon
	 * @return UI_Interfaces_StatusElementContainer
	 */
	public function addStatusIcon(UI_Icon $icon);


	/**
	 * @param UI_Renderable_Interface $element
	 * @return UI_Interfaces_StatusElementContainer
	 */
	public function addStatusElement(UI_Renderable_Interface $element);


	public function hasStatusElements(): bool;


	/**
	 * @return UI_Renderable_Interface[]
	 */
	public function getStatusElements(): array;
}


```
###  Path: `/src/classes/UI/Interfaces/Statuses/Status.php`

```php
namespace ;

use UI\CriticalityEnum as CriticalityEnum;

interface UI_Interfaces_Statuses_Status extends Application_Interfaces_Iconizable
{
	/**
	 * The ID of the status, as specified when it was created, e.g. "warning".
	 *
	 * @return string
	 */
	public function getID(): string;


	public function getLabel(): string;


	/**
	 * @param string $label
	 * @return $this
	 */
	public function setLabel(string $label);


	/**
	 * @param mixed|UI_Renderable_Interface $tooltip
	 * @return $this
	 * @throws Application_Exception
	 */
	public function setTooltip($tooltip);


	public function getBadge(): UI_Label;


	/**
	 * @return $this
	 */
	public function makeInformation();


	/**
	 * @return $this
	 */
	public function makeWarning();


	/**
	 * @return $this
	 */
	public function makeSuccess();


	/**
	 * @return $this
	 */
	public function makeDangerous();


	/**
	 * @return $this
	 */
	public function makeInactive();


	/**
	 * @param string $criticality
	 * @return $this
	 * @see CriticalityEnum
	 */
	public function setCriticality(string $criticality);


	public function getCriticality(): string;


	public function isCriticality(string $criticality): bool;


	public function isSuccess(): bool;


	public function isWarning(): bool;


	public function isInformation(): bool;


	public function isInactive(): bool;


	public function isDangerous(): bool;
}


```
###  Path: `/src/classes/UI/Interfaces/Statuses/Status.php`

```php
namespace ;

use UI\CriticalityEnum as CriticalityEnum;

interface UI_Interfaces_Statuses_Status extends Application_Interfaces_Iconizable
{
	/**
	 * The ID of the status, as specified when it was created, e.g. "warning".
	 *
	 * @return string
	 */
	public function getID(): string;


	public function getLabel(): string;


	/**
	 * @param string $label
	 * @return $this
	 */
	public function setLabel(string $label);


	/**
	 * @param mixed|UI_Renderable_Interface $tooltip
	 * @return $this
	 * @throws Application_Exception
	 */
	public function setTooltip($tooltip);


	public function getBadge(): UI_Label;


	/**
	 * @return $this
	 */
	public function makeInformation();


	/**
	 * @return $this
	 */
	public function makeWarning();


	/**
	 * @return $this
	 */
	public function makeSuccess();


	/**
	 * @return $this
	 */
	public function makeDangerous();


	/**
	 * @return $this
	 */
	public function makeInactive();


	/**
	 * @param string $criticality
	 * @return $this
	 * @see CriticalityEnum
	 */
	public function setCriticality(string $criticality);


	public function getCriticality(): string;


	public function isCriticality(string $criticality): bool;


	public function isSuccess(): bool;


	public function isWarning(): bool;


	public function isInformation(): bool;


	public function isInactive(): bool;


	public function isDangerous(): bool;
}


```
###  Path: `/src/classes/UI/Interfaces/TooltipableInterface.php`

```php
namespace UI\Interfaces;

use AppUtils\Interfaces\RenderableInterface as RenderableInterface;
use UI\TooltipInfo as TooltipInfo;
use UI\Traits\TooltipableTrait as TooltipableTrait;

/**
 * Interface for UI elements that can be assigned a tooltip text.
 *
 * @package Application
 * @subpackage UserInterface
 *
 * @see TooltipableTrait
 */
interface TooltipableInterface extends RenderableInterface
{
	public function setTooltip(?TooltipInfo $tooltip): self;


	public function hasTooltip(): bool;


	public function getTooltip(): ?TooltipInfo;
}


```
###  Path: `/src/classes/UI/Interfaces/TooltipableInterface.php`

```php
namespace UI\Interfaces;

use AppUtils\Interfaces\RenderableInterface as RenderableInterface;
use UI\TooltipInfo as TooltipInfo;
use UI\Traits\TooltipableTrait as TooltipableTrait;

/**
 * Interface for UI elements that can be assigned a tooltip text.
 *
 * @package Application
 * @subpackage UserInterface
 *
 * @see TooltipableTrait
 */
interface TooltipableInterface extends RenderableInterface
{
	public function setTooltip(?TooltipInfo $tooltip): self;


	public function hasTooltip(): bool;


	public function getTooltip(): ?TooltipInfo;
}


```
###  Path: `/src/classes/UI/ItemsSelector.php`

```php
namespace ;

use AppUtils\Interfaces\StringableInterface as StringableInterface;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

class UI_ItemsSelector extends UI_Renderable
{
	/**
	 * Sets the ID of the selector's HTML wrapping element.
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


	/**
	 * Adds a new item to link to.
	 *
	 * @param string|int|float|StringableInterface|NULL $label
	 * @param string|AdminURLInterface $url
	 * @param string|int|float|StringableInterface|NULL $description Optional description text to show as help.
	 * @return $this
	 * @throws UI_Exception
	 */
	public function addItem($label, $url, $description = null): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/JSHelper.php`

```php
namespace ;

class JSHelper extends AppUtils\JSHelper
{
	public const TOOLTIP_TOP = 'top';
	public const TOOLTIP_BOTTOM = 'bottom';
	public const TOOLTIP_LEFT = 'left';
	public const TOOLTIP_RIGHT = 'right';

	/**
	 * Adds a tooltip for the selected DOM element ID. For this to
	 * work, the element has to have the <code>title</code> attribute,
	 * which is used for the tooltip.
	 *
	 * @param string $elementID
	 * @param string $placement
	 * @throws UI_Exception
	 */
	public static function tooltipify(string $elementID, string $placement = self::TOOLTIP_TOP): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Label.php`

```php
namespace ;

/**
 * UI helper class for creating colored labels.
 *
 * @package Application
 * @subpackage UI
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Label extends UI_Badge
{
}


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
###  Path: `/src/classes/UI/Message.php`

```php
namespace ;

use UI\Interfaces\CapturableInterface as CapturableInterface;
use UI\Interfaces\MessageLayoutInterface as MessageLayoutInterface;
use UI\Traits\CapturableTrait as CapturableTrait;

class UI_Message extends UI_Renderable implements MessageLayoutInterface, CapturableInterface
{
	use CapturableTrait;

	public const ERROR_INVALID_LAYOUT = 35901;
	public const LAYOUT_DEFAULT = 'default';
	public const LAYOUT_SLIM = 'slim';
	public const LAYOUT_LARGE = 'large';

	/**
	 * Sets the message text.
	 *
	 * @param string|number|UI_Renderable_Interface|NULL $message
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setMessage($message): self
	{
		/* ... */
	}


	/**
	 * Enables the icon, and sets to use the specified icon.
	 *
	 * @param UI_Icon $icon
	 * @return $this
	 * @see UI_Message::enableIcon()
	 */
	public function setCustomIcon(UI_Icon $icon): self
	{
		/* ... */
	}


	/**
	 * @param string $layout
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setLayout(string $layout): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeDismissable(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeInfo(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeWarning(): self
	{
		/* ... */
	}


	public function makeWarningXL(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeError(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeSuccess(): self
	{
		/* ... */
	}


	/**
	 * @param string $type
	 * @return $this
	 */
	public function setType(string $type): self
	{
		/* ... */
	}


	/**
	 * Makes the whole message box inline, so it can be integrated into text.
	 * @return UI_Message
	 */
	public function makeInline(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 * @throws UI_Exception
	 */
	public function makeSlimLayout(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 * @throws UI_Exception
	 */
	public function makeLargeLayout(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 * @throws UI_Exception
	 */
	public function makeDefaultLayout(): self
	{
		/* ... */
	}


	/**
	 * Enables the icon that is automatically adjusted to
	 * the message type, i.e. an information icon for an
	 * information message for example.
	 *
	 * @return $this
	 * @see UI_Message::disableIcon()
	 */
	public function enableIcon(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeNotDismissable(): self
	{
		/* ... */
	}


	/**
	 * @param bool $dismissable
	 * @return $this
	 */
	public function setDismissable(bool $dismissable = true): self
	{
		/* ... */
	}


	/**
	 * Disables the automatic icon, if it was enabled.
	 * @return $this
	 * @see UI_Message::enableIcon()
	 * @see UI_Message::setCustomIcon()
	 */
	public function disableIcon(): self
	{
		/* ... */
	}


	public function isSlimLayout(): bool
	{
		/* ... */
	}


	public function isDefaultLayout(): bool
	{
		/* ... */
	}


	public function isLayout(string $layout): bool
	{
		/* ... */
	}


	public function addClass(string $className): self
	{
		/* ... */
	}


	public function getMessage(): string
	{
		/* ... */
	}


	public function setContent($content): self
	{
		/* ... */
	}


	public function appendContent($content): CapturableInterface
	{
		/* ... */
	}


	public function getContent(): string
	{
		/* ... */
	}
}


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
###  Path: `/src/classes/UI/PaginationRenderer.php`

```php
namespace UI;

use AppUtils\Interfaces\RenderableInterface as RenderableInterface;
use AppUtils\PaginationHelper as PaginationHelper;
use AppUtils\Traits\RenderableTrait as RenderableTrait;
use AppUtils\URLInfo as URLInfo;
use Application\Interfaces\FilterCriteriaInterface as FilterCriteriaInterface;
use TestDriver\ClassFactory as ClassFactory;
use UI as UI;

/**
 * Helper class for rendering pagination controls.
 *
 * See {@see UI::createPagination()} to create an instance
 * of the renderer for a {@see PaginationHelper} instance.
 *
 * @package UI
 */
class PaginationRenderer implements RenderableInterface
{
	use RenderableTrait;

	public function configureFilters(FilterCriteriaInterface $filters): self
	{
		/* ... */
	}


	public function setAdjacentPages(int $pages): self
	{
		/* ... */
	}


	public function getOffset(): int
	{
		/* ... */
	}


	public function getURL(int $page): string
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}


	public function getTotalPages(): int
	{
		/* ... */
	}


	public function setCurrentPage(int $pageNumber): self
	{
		/* ... */
	}


	public function setCurrentPageFromRequest(): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/PrettyBool.php`

```php
namespace ;

use AppUtils\ConvertHelper as ConvertHelper;
use AppUtils\ConvertHelper_Exception as ConvertHelper_Exception;
use AppUtils\Interfaces\StringableInterface as StringableInterface;
use UI\TooltipInfo as TooltipInfo;

class UI_PrettyBool implements UI_Renderable_Interface
{
	use UI_Traits_RenderableGeneric;

	public const COLORS_DEFAULT = 'default';
	public const COLORS_NEUTRAL = 'neutral';
	public const COLORS_INVERTED = 'inverted';
	public const LAYOUT_BADGE = 'badge';
	public const LAYOUT_ICON = 'icon';
	public const CRITICALITY_SUCCESS = 'success';
	public const CRITICALITY_WARNING = 'warning';
	public const CRITICALITY_DANGEROUS = 'dangerous';

	/**
	 * @return string
	 */
	public function render(): string
	{
		/* ... */
	}


	public function getIconTrue(): UI_Icon
	{
		/* ... */
	}


	public function getIconFalse(): UI_Icon
	{
		/* ... */
	}


	public function getIcon(): UI_Icon
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	public function getTooltip(): string
	{
		/* ... */
	}


	/**
	 * Invert the colors so that true = dangerous instead
	 * of the default false = dangerous.
	 *
	 * @return $this
	 */
	public function makeColorsInverted(): UI_PrettyBool
	{
		/* ... */
	}


	/**
	 * Sets that true = success, or false = success in inverted mode.
	 *
	 * @return $this
	 */
	public function makeSuccess(): UI_PrettyBool
	{
		/* ... */
	}


	/**
	 * Sets that true = warning, or false = warning in inverted mode.
	 *
	 * @return $this
	 */
	public function makeWarning(): UI_PrettyBool
	{
		/* ... */
	}


	/**
	 * Sets that true = dangerous, or false = dangerous in inverted mode.
	 *
	 * @return $this
	 */
	public function makeDangerous(): UI_PrettyBool
	{
		/* ... */
	}


	/**
	 * @param string $criticality
	 * @return $this
	 */
	public function setCriticality(string $criticality): UI_PrettyBool
	{
		/* ... */
	}


	public function getCriticality(): string
	{
		/* ... */
	}


	/**
	 * By default, the false state is rendered in an inactive
	 * color, so the true state stands out. With this option,
	 * both true and false will be colorized.
	 *
	 * @return UI_PrettyBool
	 */
	public function enableFalseColor(): UI_PrettyBool
	{
		/* ... */
	}


	public function disableIcon(bool $disable = true): UI_PrettyBool
	{
		/* ... */
	}


	/**
	 * Turns the layout into a badge (default).
	 * @return $this
	 */
	public function makeBadge(): UI_PrettyBool
	{
		/* ... */
	}


	/**
	 * Turns the layout into an icon only.
	 * @return $this
	 */
	public function makeIcon(bool $withLabel = true): UI_PrettyBool
	{
		/* ... */
	}


	/**
	 * Use the same colors for true and false.
	 * @return $this
	 */
	public function makeColorsNeutral(): UI_PrettyBool
	{
		/* ... */
	}


	/**
	 * Use "Yes" and "No" as labels for true and false.
	 * @return $this
	 */
	public function makeYesNo(): UI_PrettyBool
	{
		/* ... */
	}


	/**
	 * Use "Enabled" and "Disabled" as labels for true and false.
	 * @return $this
	 */
	public function makeEnabledDisabled(): UI_PrettyBool
	{
		/* ... */
	}


	/**
	 * Use "Active" and "Inactive" as labels for true and false.
	 * @return $this
	 */
	public function makeActiveInactive(): UI_PrettyBool
	{
		/* ... */
	}


	/**
	 * Set custom labels for true and false.
	 *
	 * @param string $labelTrue
	 * @param string $labelFalse
	 * @return $this
	 */
	public function setLabels(string $labelTrue, string $labelFalse): UI_PrettyBool
	{
		/* ... */
	}


	public function setIcons(UI_Icon $iconTrue, UI_Icon $iconFalse): UI_PrettyBool
	{
		/* ... */
	}


	/**
	 * @param string|int|float|StringableInterface|TooltipInfo|NULL $tooltipTrue
	 * @param string|int|float|StringableInterface|TooltipInfo|NULL $tooltipFalse
	 * @return self
	 * @throws UI_Exception
	 */
	public function setTooltip(
		string|int|float|StringableInterface|TooltipInfo|null $tooltipTrue,
		string|int|float|StringableInterface|TooltipInfo|null $tooltipFalse,
	): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/PropertiesGrid.php`

```php
namespace ;

use AppUtils\ConvertHelper as ConvertHelper;
use AppUtils\ConvertHelper_Exception as ConvertHelper_Exception;
use AppUtils\Interfaces\OptionableInterface as OptionableInterface;
use AppUtils\Interfaces\StringableInterface as StringableInterface;
use AppUtils\OutputBuffering as OutputBuffering;
use AppUtils\OutputBuffering_Exception as OutputBuffering_Exception;
use AppUtils\Traits\OptionableTrait as OptionableTrait;
use Application\Application as Application;
use Application\Revisionable\RevisionableInterface as RevisionableInterface;
use Application\Tags\Taggables\TaggableInterface as TaggableInterface;
use UI\PropertiesGrid\Property\MarkdownGridProperty as MarkdownGridProperty;
use UI\PropertiesGrid\Property\TagsGridProperty as TagsGridProperty;

/**
 * Specialized table view used to display item
 * properties, with the property names on the
 * right side.
 *
 * @package UserInterface
 * @subpackage Helpers
 */
class UI_PropertiesGrid extends UI_Renderable implements OptionableInterface, UI_Interfaces_Conditional
{
	use OptionableTrait;
	use UI_Traits_Conditional;

	public const ERROR_ONLY_NUMERIC_VALUES_ALLOWED = 599502;
	public const OPTION_LABEL_WIDTH = 'label-width-percent';
	public const DEFAULT_LABEL_WIDTH = 20;

	public function getID(): string
	{
		/* ... */
	}


	public function getDefaultOptions(): array
	{
		/* ... */
	}


	/**
	 * Adds a new property to the grid, and returns the new property instance.
	 *
	 * @param string|int|float|bool|StringableInterface $label
	 * @param string|int|float|bool|StringableInterface|null $text
	 * @return UI_PropertiesGrid_Property_Regular
	 * @throws UI_Exception
	 */
	public function add(
		string|int|float|bool|StringableInterface $label,
		string|int|float|bool|StringableInterface|null $text,
	): UI_PropertiesGrid_Property_Regular
	{
		/* ... */
	}


	/**
	 * Adds a property without a label, with all cells merged to be
	 * able to use the full width of the table.
	 *
	 * @param string|int|float|StringableInterface $content
	 * @return UI_PropertiesGrid_Property_Merged
	 * @throws UI_Exception
	 */
	public function addMerged(string|int|float|StringableInterface $content): UI_PropertiesGrid_Property_Merged
	{
		/* ... */
	}


	/**
	 * @param string|int|float|StringableInterface $message
	 * @return UI_PropertiesGrid_Property_Message
	 * @throws UI_Exception
	 */
	public function addMessage(string|int|float|StringableInterface $message): UI_PropertiesGrid_Property_Message
	{
		/* ... */
	}


	/**
	 * @param string|int|float|StringableInterface $label
	 * @param DateTime|null $date
	 * @return UI_PropertiesGrid_Property_DateTime
	 * @throws UI_Exception
	 */
	public function addDate(
		string|int|float|StringableInterface $label,
		?DateTime $date = null,
	): UI_PropertiesGrid_Property_DateTime
	{
		/* ... */
	}


	/**
	 * Displays a list of all tags associated with the taggable item.
	 * If tagging is disabled, the property will not be displayed.
	 *
	 * @param string|int|float|StringableInterface $label
	 * @param TaggableInterface|NULL $taggable The taggable item to display tags for.
	 * @return TagsGridProperty
	 * @throws UI_Exception
	 */
	public function addTags(string|int|float|StringableInterface $label, ?TaggableInterface $taggable): TagsGridProperty
	{
		/* ... */
	}


	/**
	 * Adds a property for an amount of something.
	 * The empty text is added automatically.
	 *
	 * @param string|int|float|StringableInterface $label
	 * @param int|float|null $amount
	 * @return UI_PropertiesGrid_Property_Amount
	 * @throws UI_Exception
	 */
	public function addAmount(
		string|int|float|StringableInterface $label,
		int|float|null $amount,
	): UI_PropertiesGrid_Property_Amount
	{
		/* ... */
	}


	/**
	 * Adds a text that will be rendered as Markdown formatted text.
	 *
	 * @param string|int|float|StringableInterface|NULL $markdownText
	 * @return MarkdownGridProperty
	 * @throws UI_Exception
	 */
	public function addMarkdown(string|int|float|StringableInterface|null $markdownText): MarkdownGridProperty
	{
		/* ... */
	}


	/**
	 * Adds a number of bytes, which are converted to a readable human format.
	 *
	 * @param string|int|float|StringableInterface $label
	 * @param int|null $bytes
	 * @return UI_PropertiesGrid_Property_ByteSize
	 * @throws UI_Exception
	 */
	public function addByteSize(
		string|int|float|StringableInterface $label,
		int|null $bytes,
	): UI_PropertiesGrid_Property_ByteSize
	{
		/* ... */
	}


	/**
	 * Adds a boolean value, which is visually styled.
	 *
	 * @param string|int|float|StringableInterface $label
	 * @param bool|null $bool
	 * @return UI_PropertiesGrid_Property_Boolean
	 * @throws UI_Exception
	 */
	public function addBoolean(
		string|int|float|StringableInterface $label,
		bool|null $bool,
	): UI_PropertiesGrid_Property_Boolean
	{
		/* ... */
	}


	/**
	 * Adds a header to divide sets of properties.
	 *
	 * @param string|int|float|StringableInterface $label
	 * @return UI_PropertiesGrid_Property_Header
	 * @throws UI_Exception
	 */
	public function addHeader(string|int|float|StringableInterface $label): UI_PropertiesGrid_Property_Header
	{
		/* ... */
	}


	/**
	 * Adds all relevant revision information for revisionable items.
	 *
	 * @param RevisionableInterface $revisionable
	 * @param string|NULL $changelogURL Optional URL to the changelog; Adds a button to view the changelog.
	 * @return $this
	 *
	 * @throws Application_Exception
	 * @throws DBHelper_Exception
	 * @throws UI_Exception
	 * @throws ConvertHelper_Exception
	 */
	public function injectRevisionDetails(RevisionableInterface $revisionable, ?string $changelogURL = null): self
	{
		/* ... */
	}


	/**
	 * Retrieves the current value of the label width
	 * percentage, which is used for the label column.
	 *
	 * @return int|float
	 */
	public function getLabelWidth(): int|float
	{
		/* ... */
	}


	/**
	 * Sets the percentual width of the label column.
	 *
	 * @param int|float|string $percent
	 * @throws UI_Exception
	 * @return $this
	 */
	public function setLabelWidth(int|float|string $percent): self
	{
		/* ... */
	}


	/**
	 * Configures the grid to display in a content section.
	 *
	 * @param string|int|float|StringableInterface $title
	 * @return $this
	 * @see collapse()
	 */
	public function makeSection(string|int|float|StringableInterface $title = ''): self
	{
		/* ... */
	}


	/**
	 * If the property grid has been set to render as
	 * a section using {@link makeSection()}, this returns
	 * the section instance for further configuration.
	 *
	 * @return UI_Page_Section|NULL
	 */
	public function getSection(): ?UI_Page_Section
	{
		/* ... */
	}


	public function __toString(): string
	{
		/* ... */
	}


	/**
	 * Collapses or un-collapses the grid. Has no effect
	 * unless the grid is configured as a content section.
	 *
	 * @param boolean $collapse
	 * @return $this
	 * @see makeSection()
	 */
	public function collapse(bool $collapse = true): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/QuickSelector.php`

```php
namespace ;

use AppUtils\Interfaces\ClassableInterface as ClassableInterface;
use AppUtils\Interfaces\OptionableInterface as OptionableInterface;
use AppUtils\Traits\ClassableTrait as ClassableTrait;
use AppUtils\Traits\OptionableTrait as OptionableTrait;

/**
 * UI helper class for creating quick item selection elements with
 * integrated next/previous elements.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 */
class UI_QuickSelector extends UI_QuickSelector_Container implements UI_Renderable_Interface, ClassableInterface, OptionableInterface, Application_Interfaces_Iconizable
{
	use ClassableTrait;
	use UI_Traits_RenderableGeneric;
	use OptionableTrait;
	use Application_Traits_Iconizable;

	public const ERROR_UNKNOWN_LAYOUT_PART = 24901;

	public function getDefaultOptions(): array
	{
		/* ... */
	}


	/**
	 * Sets the pre-selected item from the list. Default is the first item in the list.
	 *
	 * @param string $id
	 * @return UI_QuickSelector
	 */
	public function setSelectedItem(string $id): UI_QuickSelector
	{
		/* ... */
	}


	/**
	 * Whether to enable automatic sorting of the entries (by their label).
	 * @param bool $enable
	 * @return UI_QuickSelector
	 */
	public function enableSorting(bool $enable = true): UI_QuickSelector
	{
		/* ... */
	}


	/**
	 * Disables the previous/next buttons, so that only the selector itself is shown.
	 *
	 * @return UI_QuickSelector
	 */
	public function disableButtons(): UI_QuickSelector
	{
		/* ... */
	}


	/**
	 * Disables the "Quick switch" label in front of the selector.
	 * @return UI_QuickSelector
	 */
	public function disableLabel(): UI_QuickSelector
	{
		/* ... */
	}


	/**
	 * Sets the label for the types of items in the list. This is
	 * used in tooltips, for example, when the text makes reference
	 * to the items.
	 *
	 * For example, if the list contained apples, you would use this
	 * method to specify the item type:
	 *
	 * setItemTypeLabel('apple', 'apples');
	 *
	 * @param string $singular
	 * @param string $plural
	 * @return UI_QuickSelector
	 */
	public function setItemTypeLabel(string $singular, string $plural): UI_QuickSelector
	{
		/* ... */
	}


	public function getJSName(): string
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}


	public function isSortingEnabled(): bool
	{
		/* ... */
	}


	public function getSelectedID(): string
	{
		/* ... */
	}


	/**
	 * Makes the selector more compact: removes the "Quick select" label
	 * in front, and makes the selector small-sized.
	 *
	 * @return UI_QuickSelector
	 */
	public function makeCompact(): UI_QuickSelector
	{
		/* ... */
	}


	public function setPartEnabled(string $part, bool $enabled = true): UI_QuickSelector
	{
		/* ... */
	}


	public function isPartEnabled(string $part): bool
	{
		/* ... */
	}


	public function makeSmall(): UI_QuickSelector
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Renderable.php`

```php
namespace ;

use AppUtils\ClassHelper as ClassHelper;
use AppUtils\ClassHelper\ClassNotExistsException as ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException as ClassNotImplementsException;
use AppUtils\FileHelper as FileHelper;

/**
 * Base class for elements that can be rendered to HTML.
 * Made to be extended, and offer some utility methods
 * on top of the base interface implementation.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see UI_Renderable_Interface
 */
abstract class UI_Renderable implements UI_Renderable_Interface
{
	use UI_Traits_RenderableGeneric;

	/**
	 * Creates a new template object for the specified template.
	 * Templates are stored in the `templates` subfolder, specify
	 * the name here (without the extension).
	 *
	 * Example:
	 *
	 * <pre>
	 * // loads templates/content.my-template.php
	 * createTemplate('content.my-template');
	 * </pre>
	 *
	 * @param string|class-string<UI_Page_Template> $templateIDOrClass
	 * @return UI_Page_Template
	 *
	 * @throws ClassNotExistsException
	 * @throws ClassNotImplementsException
	 * @throws UI_Themes_Exception
	 */
	public function createTemplate(string $templateIDOrClass): UI_Page_Template
	{
		/* ... */
	}


	/**
	 * Creates a template, renders it and returns the generated contents.
	 *
	 * @param string $templateID
	 * @param array<string,mixed> $params
	 * @return string
	 *
	 * @throws ClassNotExistsException
	 * @throws ClassNotImplementsException
	 * @throws UI_Themes_Exception
	 *
	 * @see UI_Renderable::createTemplate()
	 */
	public function renderTemplate(string $templateID, array $params = []): string
	{
		/* ... */
	}


	/**
	 * @param string $templateID
	 * @param array<string,mixed> $params
	 *
	 * @throws ClassNotExistsException
	 * @throws ClassNotImplementsException
	 * @throws UI_Themes_Exception
	 */
	public function displayTemplate(string $templateID, array $params = []): void
	{
		/* ... */
	}


	/**
	 * Creates a new UI message instance and returns it.
	 *
	 * @param string|number|UI_Renderable_Interface $message
	 * @param string $type
	 * @param array<string,mixed> $options
	 * @return UI_Message
	 */
	public function createMessage($message, string $type = UI::MESSAGE_TYPE_INFO, array $options = []): UI_Message
	{
		/* ... */
	}


	public function getRenderer(): UI_Themes_Theme_ContentRenderer
	{
		/* ... */
	}


	public function getPage(): UI_Page
	{
		/* ... */
	}


	public function getUI(): UI
	{
		/* ... */
	}


	public function getTheme(): UI_Themes_Theme
	{
		/* ... */
	}


	public function getInstanceID(): string
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


	public function __toString(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/ResourceManager.php`

```php
namespace ;

use AppUtils\ClassHelper as ClassHelper;
use AppUtils\FileHelper as FileHelper;

/**
 * Handles the loading of clientside resources.
 *
 * Each stylesheet or javascript include file gets a
 * unique load key, which is automatically registered
 * clientside via the `application.registerLoadKey()`
 * method. This works also with dynamically loaded
 * contents.
 *
 * These load keys are then used to determine which
 * of the requested includes actually have to be
 * included in the current page. They are submitted
 * in every AJAX request via the `_loadkeys` parameter.
 *
 * @package UserInterface
 * @subpackage ClientResources
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 */
class UI_ResourceManager
{
	public const ERROR_INVALID_RESOURCE_TYPE_PRIORITY = 62201;
	public const ERROR_UNKNOWN_RESOURCE_EXTENSION = 62202;
	const LOADKEYS_REQUEST_VARIABLE = '_loadkeys';

	public function getUI(): UI
	{
		/* ... */
	}


	public function addJavascript(
		string $fileOrURL,
		int $priority = 0,
		bool $defer = false,
	): UI_ClientResource_Javascript
	{
		/* ... */
	}


	public function addStylesheet(
		string $fileOrURL,
		string $media = 'all',
		int $priority = 0,
	): UI_ClientResource_Stylesheet
	{
		/* ... */
	}


	public function addResource(string $fileOrURL): UI_ClientResource
	{
		/* ... */
	}


	public function getVendorURL(): string
	{
		/* ... */
	}


	public function addVendorJavascript(
		string $packageName,
		string $file,
		int $priority = 0,
	): UI_ClientResource_Javascript
	{
		/* ... */
	}


	public function addVendorStylesheet(
		string $packageName,
		string $file,
		int $priority = 0,
	): UI_ClientResource_Stylesheet
	{
		/* ... */
	}


	/**
	 * Retrieves the unique load key that identifies javascript
	 * or stylesheet includes.
	 *
	 * @param string $fileOrUrl The relative path to the file, e.g. <code>file.js</code> or <code>file.css</code>
	 * @return integer
	 */
	public function registerClientResource(string $fileOrUrl): int
	{
		/* ... */
	}


	/**
	 * Returns an indexed array with client resource keys
	 * that have been specified as already loaded in the
	 * request, using the <code>_loadkeys</code> parameter.
	 * This parameter is set automatically by an AJAX calls
	 * in the application, in order to avoid loading resources
	 * multiple times.
	 *
	 * @return integer[]
	 */
	public function getLoadedResourceKeys(): array
	{
		/* ... */
	}


	/**
	 * Clears all script load keys present in the current request,
	 * if any. Use this if you do not wish to avoid loading stylesheets
	 * and javascripts when using AJAX calls.
	 */
	public function clearLoadkeys()
	{
		/* ... */
	}


	/**
	 * @return UI_ClientResource_Javascript[]
	 */
	public function getJavascripts(): array
	{
		/* ... */
	}


	/**
	 * @return UI_ClientResource_Stylesheet[]
	 */
	public function getStylesheets(): array
	{
		/* ... */
	}


	public function renderIncludes(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Statuses.php`

```php
namespace ;

/**
 * Base class for handling states of an object, with different
 * levels of criticality.
 *
 * There are several ways to use this:
 *
 * 1) Extending this class
 *
 * This allows optionally using a custom status class, as well
 * as adding any custom functionality.
 *
 * 2) Generic usage without extending the class:
 *
 * The {@see UI_Statuses_Generic} class allows defining and working
 * with states without having to extend this class.
 *
 * 3) With selectable statuses
 *
 * The {@see UI_Statuses_Selectable} class is made to be extended,
 * and adds the possibility to select an active state.
 *
 * @package User Interface
 * @subpackage Statuses
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see UI_Statuses_Generic
 */
abstract class UI_Statuses
{
	public const ERROR_INVALID_STATUS_CLASS = 87101;
	public const ERROR_STATUS_ID_DOES_NOT_EXIST = 87102;

	/**
	 * Overridable: Returns the name of the status class that
	 * should be used for the status instances. Must be a class
	 * that implements the {@see UI_Interfaces_Statuses_Status} interface.
	 *
	 * @return string
	 * @see UI_Interfaces_Statuses_Status
	 */
	public function getStatusClass(): string
	{
		/* ... */
	}


	/**
	 * @return UI_Interfaces_Statuses_Status[]
	 */
	public function getAll(): array
	{
		/* ... */
	}


	/**
	 * @param string $statusID
	 * @return UI_Interfaces_Statuses_Status
	 * @throws UI_Exception
	 */
	public function getByID(string $statusID): UI_Interfaces_Statuses_Status
	{
		/* ... */
	}


	/**
	 * Retrieves a list of all available status IDs,
	 * sorted alphabetically.
	 *
	 * @return string[]
	 */
	public function getIDs(): array
	{
		/* ... */
	}


	/**
	 * Checks whether the status ID exists.
	 *
	 * @param string $id
	 * @return bool
	 */
	public function idExists(string $id): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Statuses/Generic.php`

```php
namespace ;

/**
 * Utility class for handling generic status cases, where
 * it is not necessary to create a custom status class.
 *
 * Usage:
 *
 * 1. Instantiate the class
 * 2. Register possible states with `addStatus()`
 *
 * @package User Interface
 * @subpackage Statuses
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Statuses_Generic extends UI_Statuses
{
	public function addStatus(string $id, string $label): UI_Interfaces_Statuses_Status
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Statuses/Generic.php`

```php
namespace ;

/**
 * Utility class for handling generic status cases, where
 * it is not necessary to create a custom status class.
 *
 * Usage:
 *
 * 1. Instantiate the class
 * 2. Register possible states with `addStatus()`
 *
 * @package User Interface
 * @subpackage Statuses
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Statuses_Generic extends UI_Statuses
{
	public function addStatus(string $id, string $label): UI_Interfaces_Statuses_Status
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Statuses/GenericSelectable.php`

```php
namespace ;

/**
 * Utility class for handling generic status cases, where
 * it is not necessary to create a custom status class. Supports
 * selecting an active state.
 *
 * Usage:
 *
 * 1. Instantiate the class.
 * 2. Register possible states with `addStatus()`.
 * 3. Set the default state to use with `setDefaultID()`.
 *
 * @package User Interface
 * @subpackage Statuses
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Statuses_GenericSelectable extends UI_Statuses_Selectable
{
	public function addStatus(string $id, string $label): UI_Interfaces_Statuses_Status
	{
		/* ... */
	}


	/**
	 * @param string $id
	 * @return $this
	 */
	public function setDefaultID(string $id)
	{
		/* ... */
	}


	public function getDefaultID(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Statuses/GenericSelectable.php`

```php
namespace ;

/**
 * Utility class for handling generic status cases, where
 * it is not necessary to create a custom status class. Supports
 * selecting an active state.
 *
 * Usage:
 *
 * 1. Instantiate the class.
 * 2. Register possible states with `addStatus()`.
 * 3. Set the default state to use with `setDefaultID()`.
 *
 * @package User Interface
 * @subpackage Statuses
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Statuses_GenericSelectable extends UI_Statuses_Selectable
{
	public function addStatus(string $id, string $label): UI_Interfaces_Statuses_Status
	{
		/* ... */
	}


	/**
	 * @param string $id
	 * @return $this
	 */
	public function setDefaultID(string $id)
	{
		/* ... */
	}


	public function getDefaultID(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Statuses/Selectable.php`

```php
namespace ;

/**
 * This adds the functionality to select an active state to
 * the statuses manager.
 *
 * @package User Interface
 * @subpackage Statuses
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class UI_Statuses_Selectable extends UI_Statuses
{
	public const ERROR_CANNOT_SELECT_INVALID_STATUS = 87401;

	abstract public function getDefaultID(): string;


	/**
	 * Retrieves the default status to use, when none
	 * has been specifically selected.
	 *
	 * @return UI_Interfaces_Statuses_Status
	 * @throws UI_Exception
	 */
	public function getDefault(): UI_Interfaces_Statuses_Status
	{
		/* ... */
	}


	/**
	 * Makes a status active by its ID.
	 *
	 * @param string $id
	 * @return $this
	 * @throws UI_Exception
	 */
	public function selectByID(string $id)
	{
		/* ... */
	}


	/**
	 * Makes the specified status the active one.
	 *
	 * @param UI_Interfaces_Statuses_Status $status
	 * @return $this
	 * @throws UI_Exception
	 *
	 * @see UI_Statuses_Selectable::ERROR_CANNOT_SELECT_INVALID_STATUS
	 */
	public function select(UI_Interfaces_Statuses_Status $status)
	{
		/* ... */
	}


	/**
	 * Whether a status has been specifically selected.
	 * @return bool
	 */
	public function hasActive(): bool
	{
		/* ... */
	}


	public function getActiveID(): string
	{
		/* ... */
	}


	/**
	 * Retrieves the currently active status, or the default
	 * status if none has been specifically selected.
	 *
	 * @return UI_Interfaces_Statuses_Status
	 * @throws UI_Exception
	 */
	public function getActive(): UI_Interfaces_Statuses_Status
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Statuses/Selectable.php`

```php
namespace ;

/**
 * This adds the functionality to select an active state to
 * the statuses manager.
 *
 * @package User Interface
 * @subpackage Statuses
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class UI_Statuses_Selectable extends UI_Statuses
{
	public const ERROR_CANNOT_SELECT_INVALID_STATUS = 87401;

	abstract public function getDefaultID(): string;


	/**
	 * Retrieves the default status to use, when none
	 * has been specifically selected.
	 *
	 * @return UI_Interfaces_Statuses_Status
	 * @throws UI_Exception
	 */
	public function getDefault(): UI_Interfaces_Statuses_Status
	{
		/* ... */
	}


	/**
	 * Makes a status active by its ID.
	 *
	 * @param string $id
	 * @return $this
	 * @throws UI_Exception
	 */
	public function selectByID(string $id)
	{
		/* ... */
	}


	/**
	 * Makes the specified status the active one.
	 *
	 * @param UI_Interfaces_Statuses_Status $status
	 * @return $this
	 * @throws UI_Exception
	 *
	 * @see UI_Statuses_Selectable::ERROR_CANNOT_SELECT_INVALID_STATUS
	 */
	public function select(UI_Interfaces_Statuses_Status $status)
	{
		/* ... */
	}


	/**
	 * Whether a status has been specifically selected.
	 * @return bool
	 */
	public function hasActive(): bool
	{
		/* ... */
	}


	public function getActiveID(): string
	{
		/* ... */
	}


	/**
	 * Retrieves the currently active status, or the default
	 * status if none has been specifically selected.
	 *
	 * @return UI_Interfaces_Statuses_Status
	 * @throws UI_Exception
	 */
	public function getActive(): UI_Interfaces_Statuses_Status
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Statuses/Status.php`

```php
namespace ;

use UI\CriticalityEnum as CriticalityEnum;

/**
 * Container for an individual status.
 *
 * @package User Interface
 * @subpackage Statuses
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Statuses_Status implements UI_Interfaces_Statuses_Status
{
	use Application_Traits_Iconizable;

	const DEFAULT_CRITICALITY = CriticalityEnum::INFO;

	public function getID(): string
	{
		/* ... */
	}


	/**
	 * @return string
	 */
	public function getLabel(): string
	{
		/* ... */
	}


	/**
	 * @param string $label
	 * @return $this
	 */
	public function setLabel(string $label)
	{
		/* ... */
	}


	/**
	 * @param mixed|UI_Renderable_Interface $tooltip
	 * @return $this
	 * @throws Application_Exception
	 */
	public function setTooltip($tooltip)
	{
		/* ... */
	}


	/**
	 * Converts the status to a badge/label.
	 *
	 * @return UI_Label
	 * @throws Application_Exception
	 */
	public function getBadge(): UI_Label
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeInformation()
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeWarning()
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeSuccess()
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeDangerous()
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeInactive()
	{
		/* ... */
	}


	/**
	 * @param string $criticality
	 * @return $this
	 */
	public function setCriticality(string $criticality)
	{
		/* ... */
	}


	public function getCriticality(): string
	{
		/* ... */
	}


	public function isCriticality(string $criticality): bool
	{
		/* ... */
	}


	public function isSuccess(): bool
	{
		/* ... */
	}


	public function isWarning(): bool
	{
		/* ... */
	}


	public function isInformation(): bool
	{
		/* ... */
	}


	public function isInactive(): bool
	{
		/* ... */
	}


	public function isDangerous(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Statuses/Status.php`

```php
namespace ;

use UI\CriticalityEnum as CriticalityEnum;

/**
 * Container for an individual status.
 *
 * @package User Interface
 * @subpackage Statuses
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Statuses_Status implements UI_Interfaces_Statuses_Status
{
	use Application_Traits_Iconizable;

	const DEFAULT_CRITICALITY = CriticalityEnum::INFO;

	public function getID(): string
	{
		/* ... */
	}


	/**
	 * @return string
	 */
	public function getLabel(): string
	{
		/* ... */
	}


	/**
	 * @param string $label
	 * @return $this
	 */
	public function setLabel(string $label)
	{
		/* ... */
	}


	/**
	 * @param mixed|UI_Renderable_Interface $tooltip
	 * @return $this
	 * @throws Application_Exception
	 */
	public function setTooltip($tooltip)
	{
		/* ... */
	}


	/**
	 * Converts the status to a badge/label.
	 *
	 * @return UI_Label
	 * @throws Application_Exception
	 */
	public function getBadge(): UI_Label
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeInformation()
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeWarning()
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeSuccess()
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeDangerous()
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeInactive()
	{
		/* ... */
	}


	/**
	 * @param string $criticality
	 * @return $this
	 */
	public function setCriticality(string $criticality)
	{
		/* ... */
	}


	public function getCriticality(): string
	{
		/* ... */
	}


	public function isCriticality(string $criticality): bool
	{
		/* ... */
	}


	public function isSuccess(): bool
	{
		/* ... */
	}


	public function isWarning(): bool
	{
		/* ... */
	}


	public function isInformation(): bool
	{
		/* ... */
	}


	public function isInactive(): bool
	{
		/* ... */
	}


	public function isDangerous(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/StringBuilder.php`

```php
namespace ;

use AppUtils\AttributeCollection as AttributeCollection;
use AppUtils\Interfaces\StringableInterface as StringableInterface;
use AppUtils\StringBuilder as StringBuilder;
use Application\Application as Application;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;
use UI\CSSClasses as CSSClasses;

/**
 * Extension to the app utils StringBuilder class, with
 * framework-specific methods.
 *
 * @package UI
 * @subpackage StringBuilder
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see AppUtils\StringBuilder
 * @see UI_Renderable_Interface
 * @see UI_Traits_RenderableGeneric
 */
class UI_StringBuilder extends StringBuilder implements UI_Renderable_Interface, UI_Interfaces_Conditional
{
	use UI_Traits_RenderableGeneric;
	use UI_Traits_Conditional;

	public const CLASS_BTN_CLIPBOARD_COPY = 'btn-clipboard-copy';

	/**
	 * Delay, in seconds, after which to hide the status
	 * text saying that the text has been copied.
	 */
	public const FADE_OUT_DELAY = 2.1;

	/**
	 * Adds an icon.
	 *
	 * @param UI_Icon $icon
	 * @return $this
	 */
	public function icon(UI_Icon $icon): self
	{
		/* ... */
	}


	/**
	 * Adds an informational styled text.
	 *
	 * @param string|int|float|StringableInterface $string
	 * @return $this
	 */
	public function info(string|int|float|StringableInterface $string): self
	{
		/* ... */
	}


	/**
	 * Adds the danger-styled text "This cannot be undone, are you sure?".
	 * @return $this
	 */
	public function cannotBeUndone(): self
	{
		/* ... */
	}


	/**
	 * Adds a muted text.
	 *
	 * @param string|int|float|StringableInterface $string
	 * @return $this
	 */
	public function muted(string|int|float|StringableInterface $string): self
	{
		/* ... */
	}


	/**
	 * Adds a button.
	 *
	 * @param UI_Button $button
	 * @return $this
	 */
	public function button(UI_Button $button): self
	{
		/* ... */
	}


	/**
	 * Adds a danger-styled text.
	 *
	 * @param string|int|float|StringableInterface $string
	 * @return $this
	 */
	public function danger(string|int|float|StringableInterface $string): self
	{
		/* ... */
	}


	public function dangerXXL($string): self
	{
		/* ... */
	}


	/**
	 * Adds a warning-styled text.
	 *
	 * @param string|int|float|StringableInterface $string
	 * @return $this
	 */
	public function warning(string|int|float|StringableInterface $string): self
	{
		/* ... */
	}


	/**
	 * Adds a success-styled text.
	 *
	 * @param string|int|float|StringableInterface $string
	 * @return $this
	 */
	public function success(string|int|float|StringableInterface $string): self
	{
		/* ... */
	}


	/**
	 * Adds an inverted color-styled text.
	 *
	 * @param string|int|float|StringableInterface $string
	 * @return $this
	 */
	public function inverted(string|int|float|StringableInterface $string): self
	{
		/* ... */
	}


	/**
	 * Adds a secondary-styled text, which is slightly more
	 * marked than muted text.
	 *
	 * @param string|int|float|StringableInterface $string
	 * @return $this
	 */
	public function secondary(string|int|float|StringableInterface $string): self
	{
		/* ... */
	}


	/**
	 * Adds a monospace-styled text by giving it the
	 * class {@link CSSClasses::TEXT_MONOSPACE}.
	 *
	 * @param string|int|float|StringableInterface $string
	 * @return $this
	 */
	public function mono(string|int|float|StringableInterface $string): self
	{
		/* ... */
	}


	/**
	 * Renders an HTML link, but only if the user has the specified right.
	 * Otherwise, the link label is used.
	 *
	 * @param string $label
	 * @param string|AdminURLInterface $url
	 * @param string $right
	 * @param bool $newTab
	 * @return UI_StringBuilder
	 * @throws Application_Exception
	 */
	public function linkRight(
		string $label,
		string|AdminURLInterface $url,
		string $right = '',
		bool $newTab = false,
	): self
	{
		/* ... */
	}


	/**
	 * @param string $label
	 * @param string|AdminURLInterface $url
	 * @param bool $newTab
	 * @param AttributeCollection|null $attributes
	 * @return self
	 */
	public function adminLink(
		string $label,
		string|AdminURLInterface $url,
		bool $newTab = false,
		?AttributeCollection $attributes = null,
	): self
	{
		/* ... */
	}


	/**
	 * Adds a tooltip to the text. Includes styling to mark
	 * the text as having a tooltip.
	 *
	 * NOTE: This will only work correctly with text content.
	 * Markup may require adding styling exceptions, see the
	 * `ui-core.css` file, and the `text-tooltip` class.
	 *
	 * @param string|int|float|StringableInterface $string
	 * @param string|int|float|StringableInterface $tooltip
	 * @return $this
	 * @throws UI_Exception
	 */
	public function tooltip(
		string|int|float|StringableInterface $string,
		string|int|float|StringableInterface $tooltip,
	): self
	{
		/* ... */
	}


	/**
	 * @param string|int|float|StringableInterface $string
	 * @param string|int|float|StringableInterface $author
	 * @return $this
	 * @throws UI_Exception
	 */
	public function blockquote(
		string|int|float|StringableInterface $string,
		string|int|float|StringableInterface $author = '',
	): self
	{
		/* ... */
	}


	/**
	 * @param string|int|float|StringableInterface $string
	 * @return UI_StringBuilder
	 * @throws UI_Exception
	 */
	public function parentheses(string|int|float|StringableInterface $string): self
	{
		/* ... */
	}


	/**
	 * Renders a text clickable, with an optional tooltip.
	 *
	 * @param string|int|float|StringableInterface $string
	 * @param string $statement The JavaScript statement to execute on click.
	 *                          Warning: must not include any double quotes, since
	 *                          It is inserted in an HTML attribute.
	 * @param string|int|float|StringableInterface $tooltip
	 * @return UI_StringBuilder
	 * @throws UI_Exception
	 */
	public function clickable(
		string|int|float|StringableInterface $string,
		string $statement,
		string|int|float|StringableInterface $tooltip = '',
	): self
	{
		/* ... */
	}


	/**
	 * Formats a text as code and adds a button next to it to
	 * copy the text to the clipboard.
	 *
	 * @param string|int|float|StringableInterface $string
	 * @param string|int|float|StringableInterface|null $emptyText The text to display if the string is empty.
	 * @return UI_StringBuilder
	 * @throws Application_Exception
	 * @throws UI_Exception
	 */
	public function codeCopy(
		string|int|float|StringableInterface $string,
		string|int|float|StringableInterface|null $emptyText = null,
	): self
	{
		/* ... */
	}


	/**
	 * Highlight parts of a text that refer to concepts,
	 * names or the like using the class {@see CSSClasses::TEXT_REFERENCE}.
	 *
	 * @param string|int|float|StringableInterface $string
	 * @param AttributeCollection|null $attributes
	 * @return $this
	 */
	public function reference($string, ?AttributeCollection $attributes = null): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function hr(): self
	{
		/* ... */
	}


	/**
	 * Content shown only for developer users, or in devel mode.
	 *
	 * This is added as an inline-block `<div>` element to allow
	 * nesting other block-level elements. This can be overridden
	 * by passing a custom `display` style in the attributes.
	 *
	 * @param string|int|float|StringableInterface $content
	 * @param AttributeCollection|null $attributes
	 * @return $this
	 */
	public function developer(
		string|int|float|StringableInterface $content,
		?AttributeCollection $attributes = null,
	): self
	{
		/* ... */
	}


	/**
	 * @param string|int|float|StringableInterface $string
	 * @param AttributeCollection|null $attributes
	 * @return $this
	 */
	public function h1(string|int|float|StringableInterface $string, ?AttributeCollection $attributes = null): self
	{
		/* ... */
	}


	/**
	 * @param string|int|float|StringableInterface $string
	 * @param AttributeCollection|null $attributes
	 * @return $this
	 */
	public function h2(string|int|float|StringableInterface $string, ?AttributeCollection $attributes = null): self
	{
		/* ... */
	}


	/**
	 * @param string|int|float|StringableInterface $string
	 * @param AttributeCollection|null $attributes
	 * @return $this
	 */
	public function h3(string|int|float|StringableInterface $string, ?AttributeCollection $attributes = null): self
	{
		/* ... */
	}


	/**
	 * @param int $level
	 * @param string|int|float|StringableInterface $string
	 * @param AttributeCollection|null $attributes
	 * @return $this
	 */
	public function heading(
		int $level,
		string|int|float|StringableInterface $string,
		?AttributeCollection $attributes = null,
	): self
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/SystemHint.php`

```php
namespace UI;

use AppUtils\Interfaces\ClassableInterface as ClassableInterface;
use AppUtils\Interfaces\OptionableInterface as OptionableInterface;
use AppUtils\Interfaces\StringableInterface as StringableInterface;
use AppUtils\Traits\ClassableTrait as ClassableTrait;
use AppUtils\Traits\OptionableTrait as OptionableTrait;
use UI_Renderable as UI_Renderable;

/**
 * System-internal hints, for use in the UI.
 *
 * @package UserInterface
 * @subpackage Helpers
 * @see \template_default_ui_system_hint
 */
class SystemHint extends UI_Renderable implements OptionableInterface, ClassableInterface
{
	use OptionableTrait;
	use ClassableTrait;

	public const LAYOUT_SYSTEM = 'system';
	public const LAYOUT_SUCCESS = 'success';
	public const LAYOUT_DEVELOPER = 'developer';
	public const DEFAULT_LAYOUT = self::LAYOUT_SYSTEM;
	public const OPTION_LAYOUT = 'layout';
	public const OPTION_CLASSES = 'classes';
	public const OPTION_CONTENT = 'content';

	public function makeSystem(): self
	{
		/* ... */
	}


	public function makeSuccess(): self
	{
		/* ... */
	}


	public function makeDeveloper(): self
	{
		/* ... */
	}


	public function setLayout(string $layout): self
	{
		/* ... */
	}


	/**
	 * @param string|int|float|StringableInterface|NULL $content
	 * @return $this
	 */
	public function setContent(string|int|float|StringableInterface|null $content): self
	{
		/* ... */
	}


	public function getDefaultOptions(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Targets/BaseTarget.php`

```php
namespace UI\Targets;

use AppUtils\HTMLTag as HTMLTag;

abstract class BaseTarget
{
	public function getLinkTag(): HTMLTag
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Targets/BaseTarget.php`

```php
namespace UI\Targets;

use AppUtils\HTMLTag as HTMLTag;

abstract class BaseTarget
{
	public function getLinkTag(): HTMLTag
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Targets/ClickTarget.php`

```php
namespace UI\Targets;

use AppUtils\HTMLTag as HTMLTag;
use JSHelper as JSHelper;

class ClickTarget extends BaseTarget
{
	public static function create(string $statement): self
	{
		/* ... */
	}


	public function getStatement(): string
	{
		/* ... */
	}


	public function setStatement(string $statement): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Targets/ClickTarget.php`

```php
namespace UI\Targets;

use AppUtils\HTMLTag as HTMLTag;
use JSHelper as JSHelper;

class ClickTarget extends BaseTarget
{
	public static function create(string $statement): self
	{
		/* ... */
	}


	public function getStatement(): string
	{
		/* ... */
	}


	public function setStatement(string $statement): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Targets/URLTarget.php`

```php
namespace UI\Targets;

use AppUtils\HTMLTag as HTMLTag;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

class URLTarget extends BaseTarget
{
	/**
	 * @param string|AdminURLInterface $url
	 * @param bool $newTab
	 * @return self
	 */
	public static function create($url, bool $newTab): self
	{
		/* ... */
	}


	public function getURL(): string
	{
		/* ... */
	}


	public function getTarget(): ?string
	{
		/* ... */
	}


	public function setTarget(?string $target): self
	{
		/* ... */
	}


	public function makeNewTab(): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Targets/URLTarget.php`

```php
namespace UI\Targets;

use AppUtils\HTMLTag as HTMLTag;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

class URLTarget extends BaseTarget
{
	/**
	 * @param string|AdminURLInterface $url
	 * @param bool $newTab
	 * @return self
	 */
	public static function create($url, bool $newTab): self
	{
		/* ... */
	}


	public function getURL(): string
	{
		/* ... */
	}


	public function getTarget(): ?string
	{
		/* ... */
	}


	public function setTarget(?string $target): self
	{
		/* ... */
	}


	public function makeNewTab(): self
	{
		/* ... */
	}
}


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
###  Path: `/src/classes/UI/TooltipInfo.php`

```php
namespace UI;

use AppUtils\AttributeCollection as AttributeCollection;
use AppUtils\Interfaces\StringableInterface as StringableInterface;
use Application_Interfaces_Loggable as Application_Interfaces_Loggable;
use Application_Traits_Loggable as Application_Traits_Loggable;
use JSHelper as JSHelper;
use UI as UI;
use UI_Exception as UI_Exception;
use UI_Renderable_Interface as UI_Renderable_Interface;
use UI_Traits_RenderableGeneric as UI_Traits_RenderableGeneric;

/**
 * Helper class used to configure a tooltip.
 *
 * Use the method {@see UI::tooltip()} to create an instance.
 *
 * Usage for rendering:
 *
 * 1) Set the element ID to attach it to.
 *    Either use {@see TooltipInfo::attachToID()}, or
 *    {@see TooltipInfo::injectAttributes()} to use an
 *    existing `id` attribute (or create one automatically).
 * 2) Enable the tooltip. It will be automatically enabled
 *    if it is rendered to string, if {@see TooltipInfo::injectAttributes()}
 *    is called, or if {@see TooltipInfo::injectJS()} is called.
 *
 * @package Application
 * @subpackage UserInterface
 * @see UI::tooltip()
 */
class TooltipInfo implements UI_Renderable_Interface, Application_Interfaces_Loggable
{
	use UI_Traits_RenderableGeneric;
	use Application_Traits_Loggable;

	/**
	 * @param string|number|StringableInterface|TooltipInfo|NULL $content
	 * @return TooltipInfo
	 * @throws UI_Exception
	 */
	public static function create($content): TooltipInfo
	{
		/* ... */
	}


	public function makeTop(): self
	{
		/* ... */
	}


	public function makeBottom(): self
	{
		/* ... */
	}


	public function makeLeft(): self
	{
		/* ... */
	}


	public function makeRight(): self
	{
		/* ... */
	}


	public function setPlacement(string $placement): self
	{
		/* ... */
	}


	public function attachToID(string $id): self
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}


	public function injectJS(): self
	{
		/* ... */
	}


	public function injectAttributes(AttributeCollection $attributes): self
	{
		/* ... */
	}


	public function getLogIdentifier(): string
	{
		/* ... */
	}


	public function getContent(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Traits/ActivatableTrait.php`

```php
namespace UI\Traits;

use UI\Interfaces\ActivatableInterface as ActivatableInterface;

/**
 * @see ActivatableInterface
 */
trait ActivatableTrait
{
	/**
	 * @param bool $active
	 * @return $this
	 */
	public function makeActive(bool $active = true): self
	{
		/* ... */
	}


	public function isActive(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Traits/ActivatableTrait.php`

```php
namespace UI\Traits;

use UI\Interfaces\ActivatableInterface as ActivatableInterface;

/**
 * @see ActivatableInterface
 */
trait ActivatableTrait
{
	/**
	 * @param bool $active
	 * @return $this
	 */
	public function makeActive(bool $active = true): self
	{
		/* ... */
	}


	public function isActive(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Traits/ButtonDecoratorInterface.php`

```php
namespace UI\Traits;

use UI_Button as UI_Button;
use UI_Interfaces_Button as UI_Interfaces_Button;

/**
 * Interface for classes using the {@see ButtonDecoratorTrait}.
 *
 * @package User Interface
 * @subpackage Traits
 * @see ButtonDecoratorTrait
 */
interface ButtonDecoratorInterface extends UI_Interfaces_Button
{
	public function getButtonInstance(): UI_Button;
}


```
###  Path: `/src/classes/UI/Traits/ButtonDecoratorInterface.php`

```php
namespace UI\Traits;

use UI_Button as UI_Button;
use UI_Interfaces_Button as UI_Interfaces_Button;

/**
 * Interface for classes using the {@see ButtonDecoratorTrait}.
 *
 * @package User Interface
 * @subpackage Traits
 * @see ButtonDecoratorTrait
 */
interface ButtonDecoratorInterface extends UI_Interfaces_Button
{
	public function getButtonInstance(): UI_Button;
}


```
###  Path: `/src/classes/UI/Traits/ButtonDecoratorTrait.php`

```php
namespace UI\Traits;

use UI as UI;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;
use UI_Button as UI_Button;
use UI_ClientConfirmable_Message as UI_ClientConfirmable_Message;
use UI_Exception as UI_Exception;
use UI_Icon as UI_Icon;
use UI_Interfaces_Button as UI_Interfaces_Button;
use UI_Page as UI_Page;
use UI_Renderable_Interface as UI_Renderable_Interface;
use UI_Themes_Theme as UI_Themes_Theme;
use UI_Themes_Theme_ContentRenderer as UI_Themes_Theme_ContentRenderer;

/**
 * Trait that can be used to add all button interface methods,
 * without extending the {@see UI_Button} class. Instead, all
 * methods are wrapper methods around a button instance.
 *
 * ## Usage
 *
 * - Use this trait.
 * - Implement the matching interface {@see ButtonDecoratorInterface}.
 * - Implement the {@see self::_getButtonInstance()} method.
 * - When rendering, use the button instance.
 *
 * @package User Interface
 * @subpackage Traits
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see ButtonDecoratorInterface
 */
trait ButtonDecoratorTrait
{
	final public function getButtonInstance(): UI_Button
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	/**
	 * Turns the button into a submit button.
	 *
	 * @param string $name
	 * @param string|int|float|UI_Renderable_Interface $value
	 * @return $this
	 */
	public function makeSubmit(string $name, $value): self
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface|NULL $title
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setTitle($title): self
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface|NULL $tooltip
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setTooltip($tooltip): self
	{
		/* ... */
	}


	public function makeDangerous(bool $enabled = true): self
	{
		/* ... */
	}


	public function makePrimary(bool $enabled = true): self
	{
		/* ... */
	}


	public function makeSuccess(bool $enabled = true): self
	{
		/* ... */
	}


	public function makeDeveloper(bool $enabled = true): self
	{
		/* ... */
	}


	public function makeWarning(bool $enabled = true): self
	{
		/* ... */
	}


	public function makeInfo(bool $enabled = true): self
	{
		/* ... */
	}


	public function makeInverse(bool $enabled = true): self
	{
		/* ... */
	}


	public function makeLayout(string $layoutID, bool $enabled = true): self
	{
		/* ... */
	}


	public function makeActiveLayout(string $layoutID): self
	{
		/* ... */
	}


	public function setID(string $id): self
	{
		/* ... */
	}


	public function setLabel($label): self
	{
		/* ... */
	}


	public function makeActive(bool $active = true): self
	{
		/* ... */
	}


	public function isActive(): bool
	{
		/* ... */
	}


	public function disable($reason = ''): UI_Interfaces_Button
	{
		/* ... */
	}


	public function isDisabled(): bool
	{
		/* ... */
	}


	public function getID(): string
	{
		/* ... */
	}


	public function isSubmittable(): bool
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
	 * @param string|AdminURLInterface $url
	 * @param string $target
	 * @return $this
	 */
	public function link($url, string $target = ''): self
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface|NULL $text
	 * @return $this
	 */
	public function setLoadingText($text): self
	{
		/* ... */
	}


	public function getTooltip(): string
	{
		/* ... */
	}


	/**
	 * NOTE: This is not type hinted on purpose
	 * to stay compatible with the
	 * `HTML_Common2::hasClass()` method.
	 *
	 * @param string $name
	 * @return bool
	 */
	public function hasClass(string $name): bool
	{
		/* ... */
	}


	public function hasClasses(): bool
	{
		/* ... */
	}


	public function addClasses(array $names): self
	{
		/* ... */
	}


	public function getClasses(): array
	{
		/* ... */
	}


	public function addClass($name): self
	{
		/* ... */
	}


	public function removeClass(string $name): self
	{
		/* ... */
	}


	public function classesToString(): string
	{
		/* ... */
	}


	public function classesToAttribute(): string
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface|NULL $message
	 * @param bool $withInput
	 * @return $this
	 * @throws UI_Exception
	 */
	public function makeConfirm($message, bool $withInput = false): self
	{
		/* ... */
	}


	public function getConfirmMessage(): UI_ClientConfirmable_Message
	{
		/* ... */
	}


	public function getURL(): string
	{
		/* ... */
	}


	public function isClickable(): bool
	{
		/* ... */
	}


	public function isLinked(): bool
	{
		/* ... */
	}


	public function getJavascript(): string
	{
		/* ... */
	}


	public function isConfirm(): bool
	{
		/* ... */
	}


	public function isDangerous(): bool
	{
		/* ... */
	}


	public function setIcon(?UI_Icon $icon): self
	{
		/* ... */
	}


	public function hasIcon(): bool
	{
		/* ... */
	}


	public function getIcon(): ?UI_Icon
	{
		/* ... */
	}


	public function isLocked(): bool
	{
		/* ... */
	}


	public function getLockReason(): string
	{
		/* ... */
	}


	public function makeLockable($lockable = true): self
	{
		/* ... */
	}


	public function isLockable(): bool
	{
		/* ... */
	}


	public function lock($reason): self
	{
		/* ... */
	}


	public function unlock(): self
	{
		/* ... */
	}


	public function getPage(): UI_Page
	{
		/* ... */
	}


	public function getTheme(): UI_Themes_Theme
	{
		/* ... */
	}


	public function getUI(): UI
	{
		/* ... */
	}


	public function getInstanceID(): string
	{
		/* ... */
	}


	public function getRenderer(): UI_Themes_Theme_ContentRenderer
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Traits/ButtonDecoratorTrait.php`

```php
namespace UI\Traits;

use UI as UI;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;
use UI_Button as UI_Button;
use UI_ClientConfirmable_Message as UI_ClientConfirmable_Message;
use UI_Exception as UI_Exception;
use UI_Icon as UI_Icon;
use UI_Interfaces_Button as UI_Interfaces_Button;
use UI_Page as UI_Page;
use UI_Renderable_Interface as UI_Renderable_Interface;
use UI_Themes_Theme as UI_Themes_Theme;
use UI_Themes_Theme_ContentRenderer as UI_Themes_Theme_ContentRenderer;

/**
 * Trait that can be used to add all button interface methods,
 * without extending the {@see UI_Button} class. Instead, all
 * methods are wrapper methods around a button instance.
 *
 * ## Usage
 *
 * - Use this trait.
 * - Implement the matching interface {@see ButtonDecoratorInterface}.
 * - Implement the {@see self::_getButtonInstance()} method.
 * - When rendering, use the button instance.
 *
 * @package User Interface
 * @subpackage Traits
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see ButtonDecoratorInterface
 */
trait ButtonDecoratorTrait
{
	final public function getButtonInstance(): UI_Button
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	/**
	 * Turns the button into a submit button.
	 *
	 * @param string $name
	 * @param string|int|float|UI_Renderable_Interface $value
	 * @return $this
	 */
	public function makeSubmit(string $name, $value): self
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface|NULL $title
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setTitle($title): self
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface|NULL $tooltip
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setTooltip($tooltip): self
	{
		/* ... */
	}


	public function makeDangerous(bool $enabled = true): self
	{
		/* ... */
	}


	public function makePrimary(bool $enabled = true): self
	{
		/* ... */
	}


	public function makeSuccess(bool $enabled = true): self
	{
		/* ... */
	}


	public function makeDeveloper(bool $enabled = true): self
	{
		/* ... */
	}


	public function makeWarning(bool $enabled = true): self
	{
		/* ... */
	}


	public function makeInfo(bool $enabled = true): self
	{
		/* ... */
	}


	public function makeInverse(bool $enabled = true): self
	{
		/* ... */
	}


	public function makeLayout(string $layoutID, bool $enabled = true): self
	{
		/* ... */
	}


	public function makeActiveLayout(string $layoutID): self
	{
		/* ... */
	}


	public function setID(string $id): self
	{
		/* ... */
	}


	public function setLabel($label): self
	{
		/* ... */
	}


	public function makeActive(bool $active = true): self
	{
		/* ... */
	}


	public function isActive(): bool
	{
		/* ... */
	}


	public function disable($reason = ''): UI_Interfaces_Button
	{
		/* ... */
	}


	public function isDisabled(): bool
	{
		/* ... */
	}


	public function getID(): string
	{
		/* ... */
	}


	public function isSubmittable(): bool
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
	 * @param string|AdminURLInterface $url
	 * @param string $target
	 * @return $this
	 */
	public function link($url, string $target = ''): self
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface|NULL $text
	 * @return $this
	 */
	public function setLoadingText($text): self
	{
		/* ... */
	}


	public function getTooltip(): string
	{
		/* ... */
	}


	/**
	 * NOTE: This is not type hinted on purpose
	 * to stay compatible with the
	 * `HTML_Common2::hasClass()` method.
	 *
	 * @param string $name
	 * @return bool
	 */
	public function hasClass(string $name): bool
	{
		/* ... */
	}


	public function hasClasses(): bool
	{
		/* ... */
	}


	public function addClasses(array $names): self
	{
		/* ... */
	}


	public function getClasses(): array
	{
		/* ... */
	}


	public function addClass($name): self
	{
		/* ... */
	}


	public function removeClass(string $name): self
	{
		/* ... */
	}


	public function classesToString(): string
	{
		/* ... */
	}


	public function classesToAttribute(): string
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface|NULL $message
	 * @param bool $withInput
	 * @return $this
	 * @throws UI_Exception
	 */
	public function makeConfirm($message, bool $withInput = false): self
	{
		/* ... */
	}


	public function getConfirmMessage(): UI_ClientConfirmable_Message
	{
		/* ... */
	}


	public function getURL(): string
	{
		/* ... */
	}


	public function isClickable(): bool
	{
		/* ... */
	}


	public function isLinked(): bool
	{
		/* ... */
	}


	public function getJavascript(): string
	{
		/* ... */
	}


	public function isConfirm(): bool
	{
		/* ... */
	}


	public function isDangerous(): bool
	{
		/* ... */
	}


	public function setIcon(?UI_Icon $icon): self
	{
		/* ... */
	}


	public function hasIcon(): bool
	{
		/* ... */
	}


	public function getIcon(): ?UI_Icon
	{
		/* ... */
	}


	public function isLocked(): bool
	{
		/* ... */
	}


	public function getLockReason(): string
	{
		/* ... */
	}


	public function makeLockable($lockable = true): self
	{
		/* ... */
	}


	public function isLockable(): bool
	{
		/* ... */
	}


	public function lock($reason): self
	{
		/* ... */
	}


	public function unlock(): self
	{
		/* ... */
	}


	public function getPage(): UI_Page
	{
		/* ... */
	}


	public function getTheme(): UI_Themes_Theme
	{
		/* ... */
	}


	public function getUI(): UI
	{
		/* ... */
	}


	public function getInstanceID(): string
	{
		/* ... */
	}


	public function getRenderer(): UI_Themes_Theme_ContentRenderer
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Traits/ButtonLayoutTrait.php`

```php
namespace UI\Traits;

use UI\Interfaces\ButtonLayoutInterface as ButtonLayoutInterface;

/**
 * Trait to implement the interface {@see ButtonLayoutInterface}.
 *
 * @package User Interface
 * @subpackage Traits
 *
 * @see ButtonLayoutInterface
 */
trait ButtonLayoutTrait
{
	/**
	 * @return $this
	 */
	public function makeInfo(bool $enabled = true): self
	{
		/* ... */
	}


	/**
	 * Styles the button as a success button.
	 *
	 * @return $this
	 */
	public function makeSuccess(bool $enabled = true): self
	{
		/* ... */
	}


	/**
	 * Styles the button as a warning button for potentially dangerous operations.
	 *
	 * @return $this
	 */
	public function makeWarning(bool $enabled = true): self
	{
		/* ... */
	}


	/**
	 * Styles the button as an inverted button.
	 *
	 * @return $this
	 */
	public function makeInverse(bool $enabled = true): self
	{
		/* ... */
	}


	/**
	 * Sets the button's layout to the specified type.
	 *
	 * @param string $layoutID
	 * @param bool $enabled If set to false, the layout will not be applied.
	 * @return $this
	 */
	public function makeLayout(string $layoutID, bool $enabled = true): self
	{
		/* ... */
	}


	/**
	 * Sets the button's layout when it is active.
	 *
	 * @param string $layoutID
	 * @return $this
	 */
	public function makeActiveLayout(string $layoutID): self
	{
		/* ... */
	}


	/**
	 * Styles the button as a primary button.
	 *
	 * @return $this
	 */
	public function makePrimary(bool $enabled = true): self
	{
		/* ... */
	}


	/**
	 * Styles the button as a button for a dangerous operation, like deleting records.
	 *
	 * @return $this
	 */
	public function makeDangerous(bool $enabled = true): self
	{
		/* ... */
	}


	/**
	 * Styles the button for developers.
	 *
	 * @return $this
	 */
	public function makeDeveloper(bool $enabled = true): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Traits/ButtonLayoutTrait.php`

```php
namespace UI\Traits;

use UI\Interfaces\ButtonLayoutInterface as ButtonLayoutInterface;

/**
 * Trait to implement the interface {@see ButtonLayoutInterface}.
 *
 * @package User Interface
 * @subpackage Traits
 *
 * @see ButtonLayoutInterface
 */
trait ButtonLayoutTrait
{
	/**
	 * @return $this
	 */
	public function makeInfo(bool $enabled = true): self
	{
		/* ... */
	}


	/**
	 * Styles the button as a success button.
	 *
	 * @return $this
	 */
	public function makeSuccess(bool $enabled = true): self
	{
		/* ... */
	}


	/**
	 * Styles the button as a warning button for potentially dangerous operations.
	 *
	 * @return $this
	 */
	public function makeWarning(bool $enabled = true): self
	{
		/* ... */
	}


	/**
	 * Styles the button as an inverted button.
	 *
	 * @return $this
	 */
	public function makeInverse(bool $enabled = true): self
	{
		/* ... */
	}


	/**
	 * Sets the button's layout to the specified type.
	 *
	 * @param string $layoutID
	 * @param bool $enabled If set to false, the layout will not be applied.
	 * @return $this
	 */
	public function makeLayout(string $layoutID, bool $enabled = true): self
	{
		/* ... */
	}


	/**
	 * Sets the button's layout when it is active.
	 *
	 * @param string $layoutID
	 * @return $this
	 */
	public function makeActiveLayout(string $layoutID): self
	{
		/* ... */
	}


	/**
	 * Styles the button as a primary button.
	 *
	 * @return $this
	 */
	public function makePrimary(bool $enabled = true): self
	{
		/* ... */
	}


	/**
	 * Styles the button as a button for a dangerous operation, like deleting records.
	 *
	 * @return $this
	 */
	public function makeDangerous(bool $enabled = true): self
	{
		/* ... */
	}


	/**
	 * Styles the button for developers.
	 *
	 * @return $this
	 */
	public function makeDeveloper(bool $enabled = true): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Traits/ButtonSizeTrait.php`

```php
namespace UI\Traits;

use UI as UI;
use UI\Interfaces\ButtonSizeInterface as ButtonSizeInterface;
use UI_Exception as UI_Exception;

/**
 * @see ButtonSizeInterface
 */
trait ButtonSizeTrait
{
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
	 * @return $this
	 */
	public function makeLarge(): self
	{
		/* ... */
	}


	/**
	 * @param string $size
	 * @return $this
	 * @throws UI_Exception
	 */
	public function makeSize(string $size): self
	{
		/* ... */
	}


	public function getSize(): ?string
	{
		/* ... */
	}


	public function isLarge(): bool
	{
		/* ... */
	}


	public function isSmall(): bool
	{
		/* ... */
	}


	public function isMini(): bool
	{
		/* ... */
	}


	public function getSizeClass(): ?string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Traits/ButtonSizeTrait.php`

```php
namespace UI\Traits;

use UI as UI;
use UI\Interfaces\ButtonSizeInterface as ButtonSizeInterface;
use UI_Exception as UI_Exception;

/**
 * @see ButtonSizeInterface
 */
trait ButtonSizeTrait
{
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
	 * @return $this
	 */
	public function makeLarge(): self
	{
		/* ... */
	}


	/**
	 * @param string $size
	 * @return $this
	 * @throws UI_Exception
	 */
	public function makeSize(string $size): self
	{
		/* ... */
	}


	public function getSize(): ?string
	{
		/* ... */
	}


	public function isLarge(): bool
	{
		/* ... */
	}


	public function isSmall(): bool
	{
		/* ... */
	}


	public function isMini(): bool
	{
		/* ... */
	}


	public function getSizeClass(): ?string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Traits/CapturableTrait.php`

```php
namespace UI\Traits;

use AppUtils\OutputBuffering as OutputBuffering;
use AppUtils\OutputBuffering_Exception as OutputBuffering_Exception;
use UI_Exception as UI_Exception;

trait CapturableTrait
{
	/**
	 * Starts output buffering to capture the content to use for the section's body.
	 * @return $this
	 * @throws OutputBuffering_Exception
	 * @see self::endCapture()
	 */
	public function startCapture(): self
	{
		/* ... */
	}


	/**
	 * Stops the output buffering started with {@link self::startCapture()}.
	 *
	 * @return $this
	 * @throws OutputBuffering_Exception
	 * @throws UI_Exception
	 */
	public function endCapture(): self
	{
		/* ... */
	}


	/**
	 * Like {@see self::endCapture()}, but appends the captured content
	 * to any existing content in the section.
	 *
	 * @return $this
	 * @throws OutputBuffering_Exception
	 * @throws UI_Exception
	 */
	public function endCaptureAppend(): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Traits/CapturableTrait.php`

```php
namespace UI\Traits;

use AppUtils\OutputBuffering as OutputBuffering;
use AppUtils\OutputBuffering_Exception as OutputBuffering_Exception;
use UI_Exception as UI_Exception;

trait CapturableTrait
{
	/**
	 * Starts output buffering to capture the content to use for the section's body.
	 * @return $this
	 * @throws OutputBuffering_Exception
	 * @see self::endCapture()
	 */
	public function startCapture(): self
	{
		/* ... */
	}


	/**
	 * Stops the output buffering started with {@link self::startCapture()}.
	 *
	 * @return $this
	 * @throws OutputBuffering_Exception
	 * @throws UI_Exception
	 */
	public function endCapture(): self
	{
		/* ... */
	}


	/**
	 * Like {@see self::endCapture()}, but appends the captured content
	 * to any existing content in the section.
	 *
	 * @return $this
	 * @throws OutputBuffering_Exception
	 * @throws UI_Exception
	 */
	public function endCaptureAppend(): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Traits/ClientConfirmable.php`

```php
namespace ;

/**
 * @see UI_Interfaces_ClientConfirmable
 */
trait UI_Traits_ClientConfirmable
{
	/**
	 * Adds a confirmation dialog with the specified message
	 * before the button action is executed. Automatically
	 * styles the confirmation dialog according to the button
	 * style, e.g. if it's a danger button the dialog will be
	 * a dangerous operation dialog.
	 *
	 * @param string|number|UI_Renderable_Interface|NULL $message Can contain HTML code.
	 * @param boolean $withInput Whether to have the user confirm the operation by typing a confirmation string.
	 * @return $this
	 * @throws UI_Exception
	 */
	public function makeConfirm($message, bool $withInput = false): self
	{
		/* ... */
	}


	/**
	 * Returns the confirmation message instance to be able to configure it further.
	 * If none exists yet, it is created.
	 *
	 * @return UI_ClientConfirmable_Message
	 */
	public function getConfirmMessage(): UI_ClientConfirmable_Message
	{
		/* ... */
	}


	public function isConfirm(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Traits/ClientConfirmable.php`

```php
namespace ;

/**
 * @see UI_Interfaces_ClientConfirmable
 */
trait UI_Traits_ClientConfirmable
{
	/**
	 * Adds a confirmation dialog with the specified message
	 * before the button action is executed. Automatically
	 * styles the confirmation dialog according to the button
	 * style, e.g. if it's a danger button the dialog will be
	 * a dangerous operation dialog.
	 *
	 * @param string|number|UI_Renderable_Interface|NULL $message Can contain HTML code.
	 * @param boolean $withInput Whether to have the user confirm the operation by typing a confirmation string.
	 * @return $this
	 * @throws UI_Exception
	 */
	public function makeConfirm($message, bool $withInput = false): self
	{
		/* ... */
	}


	/**
	 * Returns the confirmation message instance to be able to configure it further.
	 * If none exists yet, it is created.
	 *
	 * @return UI_ClientConfirmable_Message
	 */
	public function getConfirmMessage(): UI_ClientConfirmable_Message
	{
		/* ... */
	}


	public function isConfirm(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Traits/Conditional.php`

```php
namespace ;

use Application\Application as Application;
use Application\Revisionable\RevisionableInterface as RevisionableInterface;

trait UI_Traits_Conditional
{
	/**
	 * The element will only be shown if the specified condition evaluates to true.
	 *
	 * @param bool $enabled
	 * @param string $reason
	 * @return $this
	 */
	public function requireTrue(bool $enabled, string $reason = ''): self
	{
		/* ... */
	}


	/**
	 * @param string $rightName
	 * @return $this
	 * @throws Application_Exception
	 */
	public function requireRight(string $rightName): self
	{
		/* ... */
	}


	/**
	 * @param string|string[] $rightNames
	 * @return $this
	 * @throws Application_Exception
	 */
	public function requireRights($rightNames): self
	{
		/* ... */
	}


	/**
	 * The element will only be shown if the specified condition evaluates to false.
	 *
	 * @param bool $enabled
	 * @param string $reason
	 * @return $this
	 */
	public function requireFalse(bool $enabled, string $reason = ''): self
	{
		/* ... */
	}


	/**
	 * The button will only be shown if the lockable item is editable.
	 * @param Application_LockableRecord_Interface $record
	 * @return $this
	 */
	public function requireEditable(Application_LockableRecord_Interface $record): self
	{
		/* ... */
	}


	/**
	 * Requires the revisionable to be in a state that allows changes.
	 * @param RevisionableInterface $revisionable
	 * @return $this
	 */
	public function requireChanging(RevisionableInterface $revisionable): self
	{
		/* ... */
	}


	public function isValid(): bool
	{
		/* ... */
	}


	/**
	 * Retrieves the validation message (if any) that details
	 * why this item is invalid.
	 *
	 * @return string
	 */
	public function getInvalidReason(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Traits/Conditional.php`

```php
namespace ;

use Application\Application as Application;
use Application\Revisionable\RevisionableInterface as RevisionableInterface;

trait UI_Traits_Conditional
{
	/**
	 * The element will only be shown if the specified condition evaluates to true.
	 *
	 * @param bool $enabled
	 * @param string $reason
	 * @return $this
	 */
	public function requireTrue(bool $enabled, string $reason = ''): self
	{
		/* ... */
	}


	/**
	 * @param string $rightName
	 * @return $this
	 * @throws Application_Exception
	 */
	public function requireRight(string $rightName): self
	{
		/* ... */
	}


	/**
	 * @param string|string[] $rightNames
	 * @return $this
	 * @throws Application_Exception
	 */
	public function requireRights($rightNames): self
	{
		/* ... */
	}


	/**
	 * The element will only be shown if the specified condition evaluates to false.
	 *
	 * @param bool $enabled
	 * @param string $reason
	 * @return $this
	 */
	public function requireFalse(bool $enabled, string $reason = ''): self
	{
		/* ... */
	}


	/**
	 * The button will only be shown if the lockable item is editable.
	 * @param Application_LockableRecord_Interface $record
	 * @return $this
	 */
	public function requireEditable(Application_LockableRecord_Interface $record): self
	{
		/* ... */
	}


	/**
	 * Requires the revisionable to be in a state that allows changes.
	 * @param RevisionableInterface $revisionable
	 * @return $this
	 */
	public function requireChanging(RevisionableInterface $revisionable): self
	{
		/* ... */
	}


	public function isValid(): bool
	{
		/* ... */
	}


	/**
	 * Retrieves the validation message (if any) that details
	 * why this item is invalid.
	 *
	 * @return string
	 */
	public function getInvalidReason(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Traits/MessageWrapperTrait.php`

```php
namespace UI\Traits;

use UI_Icon as UI_Icon;

trait MessageWrapperTrait
{
	/**
	 * @return $this
	 */
	public function makeDismissable(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeNotDismissable(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeSlimLayout(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeInline(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeError(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeSuccess(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeWarning(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeInfo(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeLargeLayout(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeDefaultLayout(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function enableIcon(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function disableIcon(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function setCustomIcon(UI_Icon $icon): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Traits/MessageWrapperTrait.php`

```php
namespace UI\Traits;

use UI_Icon as UI_Icon;

trait MessageWrapperTrait
{
	/**
	 * @return $this
	 */
	public function makeDismissable(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeNotDismissable(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeSlimLayout(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeInline(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeError(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeSuccess(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeWarning(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeInfo(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeLargeLayout(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeDefaultLayout(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function enableIcon(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function disableIcon(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function setCustomIcon(UI_Icon $icon): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Traits/RenderableGeneric.php`

```php
namespace ;

/**
 * Trait used to implement the interface methods for a renderable
 * object in a generic way, without requiring a UI instance or
 * page to be set. Uses the active global UI instance.
 *
 * The only method left to implement is the actual `render()` method.
 *
 * @package UI
 * @subpackage Traits
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see UI_Renderable_Interface
 */
trait UI_Traits_RenderableGeneric
{
	public function getUI(): UI
	{
		/* ... */
	}


	public function getTheme(): UI_Themes_Theme
	{
		/* ... */
	}


	public function getInstanceID(): string
	{
		/* ... */
	}


	public function getRenderer(): UI_Themes_Theme_ContentRenderer
	{
		/* ... */
	}


	public function getPage(): UI_Page
	{
		/* ... */
	}


	public function display(): void
	{
		/* ... */
	}


	public function __toString()
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Traits/RenderableGeneric.php`

```php
namespace ;

/**
 * Trait used to implement the interface methods for a renderable
 * object in a generic way, without requiring a UI instance or
 * page to be set. Uses the active global UI instance.
 *
 * The only method left to implement is the actual `render()` method.
 *
 * @package UI
 * @subpackage Traits
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see UI_Renderable_Interface
 */
trait UI_Traits_RenderableGeneric
{
	public function getUI(): UI
	{
		/* ... */
	}


	public function getTheme(): UI_Themes_Theme
	{
		/* ... */
	}


	public function getInstanceID(): string
	{
		/* ... */
	}


	public function getRenderer(): UI_Themes_Theme_ContentRenderer
	{
		/* ... */
	}


	public function getPage(): UI_Page
	{
		/* ... */
	}


	public function display(): void
	{
		/* ... */
	}


	public function __toString()
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Traits/ScriptInjectableInterface.php`

```php
namespace UI\Traits;

use UI as UI;
use UI\ClientResourceCollection as ClientResourceCollection;

/**
 * Interface for all objects that have client resources
 * to inject into a UI instance.
 *
 * @package UserInterface
 * @subpackage Traits
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 *
 * @see ScriptInjectableTrait
 */
interface ScriptInjectableInterface
{
	/**
	 * @param UI $ui
	 * @return $this
	 */
	public function injectUIScripts(UI $ui): self;


	/**
	 * @param UI $ui
	 * @return ClientResourceCollection
	 */
	public function getUIScripts(UI $ui): ClientResourceCollection;
}


```
###  Path: `/src/classes/UI/Traits/ScriptInjectableInterface.php`

```php
namespace UI\Traits;

use UI as UI;
use UI\ClientResourceCollection as ClientResourceCollection;

/**
 * Interface for all objects that have client resources
 * to inject into a UI instance.
 *
 * @package UserInterface
 * @subpackage Traits
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 *
 * @see ScriptInjectableTrait
 */
interface ScriptInjectableInterface
{
	/**
	 * @param UI $ui
	 * @return $this
	 */
	public function injectUIScripts(UI $ui): self;


	/**
	 * @param UI $ui
	 * @return ClientResourceCollection
	 */
	public function getUIScripts(UI $ui): ClientResourceCollection;
}


```
###  Path: `/src/classes/UI/Traits/ScriptInjectableTrait.php`

```php
namespace UI\Traits;

use UI as UI;
use UI\ClientResourceCollection as ClientResourceCollection;

/**
 * Implementation of the matching interface for any objects
 * that add client resources. Uses a {@see ClientResourceCollection}
 * to keep track of the resources added by the class.
 *
 * @package UserInterface
 * @subpackage Traits
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 *
 * @see ScriptInjectableInterface
 */
trait ScriptInjectableTrait
{
	public function injectUIScripts(UI $ui): self
	{
		/* ... */
	}


	public function getUIScripts(UI $ui): ClientResourceCollection
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Traits/ScriptInjectableTrait.php`

```php
namespace UI\Traits;

use UI as UI;
use UI\ClientResourceCollection as ClientResourceCollection;

/**
 * Implementation of the matching interface for any objects
 * that add client resources. Uses a {@see ClientResourceCollection}
 * to keep track of the resources added by the class.
 *
 * @package UserInterface
 * @subpackage Traits
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 *
 * @see ScriptInjectableInterface
 */
trait ScriptInjectableTrait
{
	public function injectUIScripts(UI $ui): self
	{
		/* ... */
	}


	public function getUIScripts(UI $ui): ClientResourceCollection
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Traits/StatusElementContainer.php`

```php
namespace ;

/**
 * Interface for UI elements that allow status elements
 * to be added, like warning icons and the like.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see UI_Interfaces_StatusElementContainer
 */
trait UI_Traits_StatusElementContainer
{
	/**
	 * @param UI_Icon $icon
	 * @return $this
	 */
	public function addStatusIcon(UI_Icon $icon)
	{
		/* ... */
	}


	/**
	 * @param UI_Renderable_Interface $element
	 * @return $this
	 */
	public function addStatusElement(UI_Renderable_Interface $element)
	{
		/* ... */
	}


	public function hasStatusElements(): bool
	{
		/* ... */
	}


	/**
	 * @return UI_Renderable_Interface[]
	 */
	public function getStatusElements(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Traits/StatusElementContainer.php`

```php
namespace ;

/**
 * Interface for UI elements that allow status elements
 * to be added, like warning icons and the like.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see UI_Interfaces_StatusElementContainer
 */
trait UI_Traits_StatusElementContainer
{
	/**
	 * @param UI_Icon $icon
	 * @return $this
	 */
	public function addStatusIcon(UI_Icon $icon)
	{
		/* ... */
	}


	/**
	 * @param UI_Renderable_Interface $element
	 * @return $this
	 */
	public function addStatusElement(UI_Renderable_Interface $element)
	{
		/* ... */
	}


	public function hasStatusElements(): bool
	{
		/* ... */
	}


	/**
	 * @return UI_Renderable_Interface[]
	 */
	public function getStatusElements(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Traits/TooltipableTrait.php`

```php
namespace UI\Traits;

use UI\TooltipInfo as TooltipInfo;

/**
 * @package Application
 * @subpackage UserInterface
 * @see TooltipableInterface
 */
trait TooltipableTrait
{
	/**
	 * @param TooltipInfo|NULL $tooltip
	 * @return $this
	 */
	public function setTooltip(?TooltipInfo $tooltip): self
	{
		/* ... */
	}


	public function hasTooltip(): bool
	{
		/* ... */
	}


	public function getTooltip(): ?TooltipInfo
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Traits/TooltipableTrait.php`

```php
namespace UI\Traits;

use UI\TooltipInfo as TooltipInfo;

/**
 * @package Application
 * @subpackage UserInterface
 * @see TooltipableInterface
 */
trait TooltipableTrait
{
	/**
	 * @param TooltipInfo|NULL $tooltip
	 * @return $this
	 */
	public function setTooltip(?TooltipInfo $tooltip): self
	{
		/* ... */
	}


	public function hasTooltip(): bool
	{
		/* ... */
	}


	public function getTooltip(): ?TooltipInfo
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/UI.php`

```php
namespace ;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use AppUtils\ClassHelper as ClassHelper;
use AppUtils\ClassHelper\ClassNotExistsException as ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException as ClassNotImplementsException;
use AppUtils\ConvertHelper_Exception as ConvertHelper_Exception;
use AppUtils\FileHelper as FileHelper;
use AppUtils\Interfaces\StringableInterface as StringableInterface;
use AppUtils\OutputBuffering as OutputBuffering;
use AppUtils\PaginationHelper as PaginationHelper;
use Application\AppFactory as AppFactory;
use Application\Application as Application;
use Application\ConfigSettings\BaseConfigRegistry as BaseConfigRegistry;
use Application\EventHandler\Event\EventListener as EventListener;
use Application\EventHandler\EventManager as EventManager;
use Application\Exception\UnexpectedInstanceException as UnexpectedInstanceException;
use UI\AdminURLs\AdminURL as AdminURL;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;
use UI\Bootstrap\BigSelection\BigSelectionWidget as BigSelectionWidget;
use UI\ClientResourceCollection as ClientResourceCollection;
use UI\Event\FormCreatedEvent as FormCreatedEvent;
use UI\PaginationRenderer as PaginationRenderer;
use UI\SystemHint as SystemHint;
use UI\TooltipInfo as TooltipInfo;
use UI\Tree\TreeNode as TreeNode;
use UI\Tree\TreeRenderer as TreeRenderer;

/**
 * UI management class that handles display-related
 * functions like including JavaScript files and the
 * like.
 *
 * @package UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 *
 * @event FormCreated UI_Event_FormCreated
 */
class UI
{
	public const ERROR_CANNOT_SELECT_INSTANCE_BEFORE_MAIN = 39747001;
	public const ERROR_NO_UI_INSTANCE_AVAILABLE_YET = 39747002;
	public const ERROR_CANNOT_SELECT_PREVIOUS_INSTANCE = 39747003;
	public const ERROR_NOT_A_RENDERABLE = 39747005;
	public const ERROR_CANNOT_SET_PAGE_INSTANCE_AGAIN = 39747007;
	public const MESSAGE_TYPE_SUCCESS = 'success';
	public const MESSAGE_TYPE_ERROR = 'error';
	public const MESSAGE_TYPE_WARNING = 'warning';
	public const MESSAGE_TYPE_WARNING_XL = 'warning-xl';
	public const MESSAGE_TYPE_INFO = 'info';
	public const EVENT_PAGE_RENDERED = 'pageRendered';
	public const APP_INSTANCE_PREFIX = 'app-';
	public const FONT_AWESOME_URL = 'https://use.fontawesome.com/releases/v5.15.4/css/all.css';

	/**
	 * Retrieves this UI object's instance key, which is unique
	 * to each UI object.
	 *
	 * @return string
	 */
	public function getInstanceKey(): string
	{
		/* ... */
	}


	/**
	 * Retrieves the currently selected UI instance. A UI instance
	 * is created automatically be the application when it is instantiated,
	 * after that this can be called to retrieve the active instance.
	 *
	 * @see self::selectInstance()
	 *
	 * @return UI
	 * @throws UI_Exception
	 */
	public static function getInstance(): UI
	{
		/* ... */
	}


	/**
	 * Creates a new UI instance for the specified application.
	 *
	 * @param Application $app
	 * @return UI
	 */
	public static function createInstance(Application $app): UI
	{
		/* ... */
	}


	/**
	 * Selects a UI instance that can be used in parallel
	 * to the main UI instance.
	 *
	 * Any JavaScript or styles added to this instance
	 * will only be included when the instance is used,
	 * and will not be available in the main instance.
	 *
	 * One use-case is for client-side forms: They use
	 * a dedicated UI instance to collect the necessary
	 * JavaScript and includes.
	 *
	 * @param string $instanceName
	 * @return UI
	 * @throws Application_Exception
	 * @throws UI_Exception
	 */
	public static function selectInstance(string $instanceName): UI
	{
		/* ... */
	}


	public static function selectDefaultInstance(): void
	{
		/* ... */
	}


	/**
	 * Restores the previously selected UI instance after
	 * switching to another UI instance using the
	 * {@see self::selectInstance()} method.
	 *
	 * @param boolean $ignoreErrors Whether to ignore errors when no previous instance is present
	 * @throws Application_Exception
	 */
	public static function selectPreviousInstance(bool $ignoreErrors = false): void
	{
		/* ... */
	}


	/**
	 * @return Application
	 */
	public function getApplication(): Application
	{
		/* ... */
	}


	public function createResourceCollection(): ClientResourceCollection
	{
		/* ... */
	}


	/**
	 * Retrieves the resource manager instance, which is used
	 * to keep track of all clientside resources, like JavaScript
	 * and stylesheet includes.
	 *
	 * @return UI_ResourceManager
	 */
	public function getResourceManager(): UI_ResourceManager
	{
		/* ... */
	}


	public function addStylesheet(
		string $fileOrUrl,
		string $media = 'all',
		int $priority = 0,
	): UI_ClientResource_Stylesheet
	{
		/* ... */
	}


	/**
	 * Adds a JavaScript or stylesheet to include clientside.
	 *
	 * @param string $fileOrURL
	 * @throws Application_Exception
	 * @return UI_ClientResource
	 *
	 * @see UI::addStylesheet()
	 * @see UI::addJavascript()
	 */
	public function addResource(string $fileOrURL): UI_ClientResource
	{
		/* ... */
	}


	/**
	 * Adds a JavaScript file to include. This can be either
	 * the filename of a file from the js/ subfolder, or a
	 * full URL to an external file.
	 *
	 * If the configuration setting APP_JAVASCRIPT_MINIFIED is
	 * set to true, for local file this will check if a minified
	 * version is available. The file name for the minified
	 * version is determined automatically by appending "-min"
	 * to the file name.
	 *
	 * Example:
	 *
	 * myscript.js
	 * myscript-min.js
	 *
	 * The priority parameter allows influencing the order in
	 * which the scripts are added to the HTML source. A higher
	 * priority will make the script move higher to the top.
	 *
	 * @param string $fileOrUrl
	 * @param int $priority
	 * @param bool $defer
	 * @return UI_ClientResource_Javascript The client resource instance
	 */
	public function addJavascript(
		string $fileOrUrl,
		int $priority = 0,
		bool $defer = false,
	): UI_ClientResource_Javascript
	{
		/* ... */
	}


	public function addVendorJavascript(
		string $packageName,
		string $file,
		int $priority = 0,
	): UI_ClientResource_Javascript
	{
		/* ... */
	}


	public function addVendorStylesheet(
		string $packageName,
		string $file,
		int $priority = 0,
	): UI_ClientResource_Stylesheet
	{
		/* ... */
	}


	/**
	 * Retrieves the build-specific load key that is appended
	 * to all JavaScript and stylesheet includes to force a
	 * refresh in browsers when deploying a new application
	 * version.
	 *
	 * @return string
	 */
	public function getBuildKey(): string
	{
		/* ... */
	}


	public function hasBuildKey(): bool
	{
		/* ... */
	}


	/**
	 * Adds a JavaScript statement to run on when the page
	 * has loaded using the jquery.ready() function.
	 *
	 * Example:
	 *
	 * addJavascriptOnload("alert('Hello World')");
	 *
	 * @param string $statement
	 * @param boolean $avoidDuplicates Whether to ignore identical statements that have already been added
	 * @return $this
	 */
	public function addJavascriptOnload(string $statement, bool $avoidDuplicates = false): self
	{
		/* ... */
	}


	/**
	 * Adds a JavaScript statement to add to the head script tag.
	 * The semicolon is added automatically, so you do not have to
	 * include it.
	 *
	 * Example:
	 *
	 * addJavascriptHead("alert('Hello World')");
	 *
	 * @param string $statement
	 * @param bool $addSemicolon
	 * @return $this
	 */
	public function addJavascriptHead(string $statement, bool $addSemicolon = true): self
	{
		/* ... */
	}


	/**
	 * @param string|int|float|StringableInterface|NULL $comment
	 * @return $this
	 */
	public function addJavascriptHeadComment(string|int|float|StringableInterface|null $comment = null): self
	{
		/* ... */
	}


	/**
	 * @param string|int|float|StringableInterface|NULL $heading
	 * @return $this
	 */
	public function addJavascriptHeadHeading(string|int|float|StringableInterface|null $heading): self
	{
		/* ... */
	}


	/**
	 * Adds a JavaScript variable to the head script tag. The variable
	 * is automatically converted to the JavaScript equivalent.
	 *
	 * @param string $varName
	 * @param mixed $varValue
	 * @return $this
	 */
	public function addJavascriptHeadVariable(string $varName, mixed $varValue): self
	{
		/* ... */
	}


	/**
	 * Builds and adds a JavaScript statement to the head script tag.
	 * The first parameter is the JavaScript function to call, any
	 * additional parameters are used as arguments for the JavaScript
	 * function call. Variable types are automagically converted to
	 * JavaScript types.
	 *
	 * Examples:
	 *
	 * // add an alert(); statement:
	 * addJavascriptHeadStatement('alert');
	 *
	 * // add an alert('Alert text'); statement
	 * addJavascriptHeadStatement('alert', 'Alert text');
	 */
	public function addJavascriptHeadStatement(): self
	{
		/* ... */
	}


	/**
	 * Like {@link addJavascriptHeadStatement()}, but adds the statement
	 * to the onload script block.
	 *
	 * @see addJavascriptHeadStatement()
	 */
	public function addJavascriptOnloadStatement(): self
	{
		/* ... */
	}


	/**
	 * Adds a message to be displayed to the user. It is stored in
	 * the session, so it will be displayed on the next request if
	 * it cannot be shown during the current request (like after
	 * saving a record followed by a redirect).
	 *
	 * @param string|int|float|StringableInterface $message
	 * @param string $type
	 * @throws UI_Exception
	 * @see getMessages()
	 * @see clearMessages()
	 * @see hasMessages()
	 */
	public function addMessage(string|int|float|StringableInterface $message, string $type = UI::MESSAGE_TYPE_INFO): void
	{
		/* ... */
	}


	/**
	 * @param string|int|float|StringableInterface $message
	 * @throws UI_Exception
	 */
	public function addSuccessMessage(string|int|float|StringableInterface $message): void
	{
		/* ... */
	}


	/**
	 * @param string|int|float|StringableInterface $message
	 * @throws UI_Exception
	 */
	public function addErrorMessage(string|int|float|StringableInterface $message): void
	{
		/* ... */
	}


	/**
	 * @param string|int|float|StringableInterface $message
	 * @throws UI_Exception
	 */
	public function addInfoMessage(string|int|float|StringableInterface $message): void
	{
		/* ... */
	}


	/**
	 * @param string|int|float|StringableInterface $message
	 * @throws UI_Exception
	 */
	public function addWarningMessage(string|int|float|StringableInterface $message): void
	{
		/* ... */
	}


	/**
	 * Checks if any user messages are present.
	 * @return boolean
	 */
	public function hasMessages(): bool
	{
		/* ... */
	}


	/**
	 * Sets messages to be deferred to the next request. No messages
	 * will be cleared this request, and any new ones will be added
	 * to the queue.
	 *
	 * @return UI
	 */
	public function deferMessages(): UI
	{
		/* ... */
	}


	/**
	 * Checks whether messages are currently set to be deferred.
	 *
	 * @return boolean
	 * @since 3.3.10
	 */
	public function isMessagesDeferred(): bool
	{
		/* ... */
	}


	/**
	 * Retrieves all user messages that are present as
	 * an indexed array with on message per entry in the
	 * order that they were added.
	 *
	 * Messages are not cleared automatically, you have
	 * to clear them using the {@link clearMessages()}
	 * method.
	 *
	 * @return array<int,array{type:string,message:string}>
	 * @see addMessage()
	 * @see hasMessages()
	 * @see clearMessages()
	 */
	public function getMessages(): array
	{
		/* ... */
	}


	/**
	 * Clears all messages.
	 *
	 * @see addMessage()
	 * @see hasMessages()
	 * @see getMessages()
	 */
	public function clearMessages(): UI
	{
		/* ... */
	}


	/**
	 * Sets the current page object; this is done automatically by the
	 * application on startup.
	 *
	 * @param UI_Page $page
	 * @throws UI_Exception
	 */
	public function setPage(UI_Page $page): void
	{
		/* ... */
	}


	public function hasPage(): bool
	{
		/* ... */
	}


	/**
	 * Retrieves the current page object.
	 *
	 * @throws Exception
	 * @return UI_Page
	 */
	public function getPage(): UI_Page
	{
		/* ... */
	}


	public function renderHeadIncludes(): string
	{
		/* ... */
	}


	/**
	 * @param array<string,string|int|float|bool|null> $params
	 * @return AdminURLInterface
	 */
	public static function adminURL(array $params = []): AdminURLInterface
	{
		/* ... */
	}


	public static function createNavigation(string $navigationID): UI_Page_Navigation
	{
		/* ... */
	}


	/**
	 * @param string|int|float|StringableInterface|NULL $hint
	 * @return SystemHint
	 * @throws UI_Exception
	 */
	public static function systemHint(string|int|float|StringableInterface|null $hint = null): SystemHint
	{
		/* ... */
	}


	/**
	 * Creates a popover instance, which can be used to display
	 * a toggleable detailed popup. It is the big brother of the
	 * tooltip, but less detailed than a dialog.
	 *
	 * @param string $attachToID The ID of the element to attach to.
	 * @return UI_Bootstrap_Popover
	 * @throws UI_Exception
	 */
	public static function popover(string $attachToID): UI_Bootstrap_Popover
	{
		/* ... */
	}


	/**
	 * Creates a tooltip instance used to hold information
	 * for a tooltip, to allow configuring it further.
	 *
	 * Usage:
	 *
	 * 1) Set the element ID to attach it to.
	 *    Either use {@see TooltipInfo::attachToID()}, or
	 *    {@see TooltipInfo::injectAttributes()} to use an
	 *    existing `id` attribute (or create one automatically).
	 * 2) Enable the tooltip. It will be automatically enabled
	 *    if it is rendered to string, if {@see TooltipInfo::injectAttributes()}
	 *    is called, or if {@see TooltipInfo::injectJS()} is called.
	 *
	 * @param string|int|float|StringableInterface|TooltipInfo|NULL $content
	 * @return TooltipInfo
	 * @throws UI_Exception
	 */
	public static function tooltip(string|int|float|StringableInterface|TooltipInfo|null $content): TooltipInfo
	{
		/* ... */
	}


	/**
	 * Creates a new Badge UI element and returns it. These can
	 * be converted to string, so they can be inserted directly
	 * into any content strings.
	 *
	 * @param string|int|float|StringableInterface $label
	 * @return UI_Badge
	 * @throws UI_Exception
	 */
	public static function badge(string|int|float|StringableInterface $label): UI_Badge
	{
		/* ... */
	}


	/**
	 * Creates a new Label UI element and returns it. These can
	 * be converted to string, so they can be inserted directly
	 * into any content strings.
	 *
	 * @param string|int|float|StringableInterface $label
	 * @return UI_Label
	 * @throws UI_Exception
	 */
	public static function label(string|int|float|StringableInterface $label): UI_Label
	{
		/* ... */
	}


	public function createPagination(
		PaginationHelper $helper,
		string $pageParamName,
		string $baseURL,
	): PaginationRenderer
	{
		/* ... */
	}


	/**
	 * @param string $type
	 * @return UI_Page_Section
	 */
	public function createSection(string $type = ''): UI_Page_Section
	{
		/* ... */
	}


	/**
	 * @param string $id
	 * @return UI_Page
	 */
	public function createPage(string $id): UI_Page
	{
		/* ... */
	}


	/**
	 * Creates a new instance of the quick selector helper
	 * class, which can be used to create quick selection
	 * UI elements for switching between items.
	 *
	 * @param string $id
	 * @return UI_QuickSelector
	 */
	public function createQuickSelector(string $id = ''): UI_QuickSelector
	{
		/* ... */
	}


	/**
	 * Creates a new UI message instance and returns it.
	 *
	 * @param string|int|float|StringableInterface|NULL $message
	 * @param string $type
	 * @param array<string,mixed> $options
	 * @return UI_Message
	 */
	public function createMessage(
		string|int|float|StringableInterface|null $message = null,
		string $type = UI::MESSAGE_TYPE_INFO,
		array $options = [],
	): UI_Message
	{
		/* ... */
	}


	/**
	 * Creates a new template instance for the specified template ID or class name.
	 *
	 * @param string|class-string<UI_Page_Template> $templateIDOrClass
	 * @return UI_Page_Template
	 */
	public function createTemplate(string $templateIDOrClass): UI_Page_Template
	{
		/* ... */
	}


	public static function string(): UI_StringBuilder
	{
		/* ... */
	}


	/**
	 * Creates a new form object used as wrapper around the HTML_QuickForm2
	 * object to make handling forms easier within the application.
	 *
	 * @param string $id
	 * @param array<string,mixed>|ArrayDataCollection $defaultData
	 * @return UI_Form
	 * @see createGetForm()
	 */
	public function createForm(string $id, array|ArrayDataCollection $defaultData = []): UI_Form
	{
		/* ... */
	}


	/**
	 * Creates a new form object that gets submitted via get instead
	 * of the default post method.
	 *
	 * @param string $id
	 * @param array<string,mixed> $defaultData
	 * @return UI_Form
	 */
	public function createGetForm(string $id, array $defaultData = []): UI_Form
	{
		/* ... */
	}


	/**
	 * Creates a new data grid object that can be used to display
	 * a list of items with added functionality like multiple
	 * selection and the like.
	 *
	 * @param string $id
	 * @param boolean $allowDuplicateID Allow using the same grid ID more than once?
	 * @return UI_DataGrid
	 */
	public function createDataGrid(string $id, bool $allowDuplicateID = false): UI_DataGrid
	{
		/* ... */
	}


	/**
	 * @param string $type
	 * @return UI_Interfaces_Bootstrap
	 * @throws ClassNotExistsException
	 * @throws ClassNotImplementsException
	 */
	public function createBootstrap(string $type): UI_Interfaces_Bootstrap
	{
		/* ... */
	}


	/**
	 * @param string|int|float|StringableInterface|NULL $label
	 * @return UI_Bootstrap_ButtonDropdown
	 */
	public function createButtonDropdown(
		string|int|float|StringableInterface|null $label = null,
	): UI_Bootstrap_ButtonDropdown
	{
		/* ... */
	}


	/**
	 * @param string $label
	 * @return UI_Bootstrap_BadgeDropdown
	 */
	public function createBadgeDropdown(string $label = ''): UI_Bootstrap_BadgeDropdown
	{
		/* ... */
	}


	/**
	 * @param string $label
	 * @param string|AdminURLInterface $url
	 * @return UI_Bootstrap_Anchor
	 */
	public function createAnchor(string $label = '', string|AdminURLInterface $url = ''): UI_Bootstrap_Anchor
	{
		/* ... */
	}


	/**
	 * Creates and returns a new button group helper instance,
	 * which can be used to group buttons together.
	 *
	 * @return UI_Bootstrap_ButtonGroup
	 */
	public function createButtonGroup(): UI_Bootstrap_ButtonGroup
	{
		/* ... */
	}


	/**
	 * Creates and returns a big selection instance, which
	 * is used to let the user select from a prominent list
	 * of items.
	 *
	 * @return BigSelectionWidget
	 */
	public function createBigSelection(): BigSelectionWidget
	{
		/* ... */
	}


	public function createTreeRenderer(TreeNode $rootNode): TreeRenderer
	{
		/* ... */
	}


	/**
	 * Creates a new tabs element.
	 *
	 * @param string $name
	 * @return UI_Bootstrap_Tabs
	 */
	public function createTabs(string $name = ''): UI_Bootstrap_Tabs
	{
		/* ... */
	}


	/**
	 * @param string|int|float|StringableInterface|NULL $label
	 * @return UI_Bootstrap_DropdownAnchor
	 * @throws UI_Exception
	 */
	public function createDropdownAnchor(string|int|float|StringableInterface|null $label): UI_Bootstrap_DropdownAnchor
	{
		/* ... */
	}


	/**
	 * @return UI_Bootstrap_DropdownMenu
	 */
	public function createDropdownMenu(): UI_Bootstrap_DropdownMenu
	{
		/* ... */
	}


	/**
	 * @param string $title
	 * @return UI_Bootstrap_DropdownHeader
	 */
	public function createDropdownHeader(string $title = ''): UI_Bootstrap_DropdownHeader
	{
		/* ... */
	}


	/**
	 * @param string|int|float|StringableInterface|NULL $content
	 * @return UI_Bootstrap_DropdownStatic
	 * @throws UI_Exception
	 */
	public function createDropdownStatic(string|int|float|StringableInterface|null $content): UI_Bootstrap_DropdownStatic
	{
		/* ... */
	}


	/**
	 * @param string|int|float|StringableInterface|NULL $title
	 * @return UI_Bootstrap_DropdownSubmenu
	 * @throws UI_Exception
	 */
	public function createDropdownSubmenu(
		string|int|float|StringableInterface|null $title = null,
	): UI_Bootstrap_DropdownSubmenu
	{
		/* ... */
	}


	/**
	 * Creates and returns a new UI icon object.
	 * @return UI_Icon
	 */
	public static function icon(): UI_Icon
	{
		/* ... */
	}


	/**
	 * @param string|bool|int $boolValue
	 * @return UI_PrettyBool
	 * @throws ConvertHelper_Exception
	 */
	public static function prettyBool(string|bool|int $boolValue): UI_PrettyBool
	{
		/* ... */
	}


	/**
	 * Creates and returns a new UI button object.
	 * Use the button's API to configure its looks
	 * and functions. It supports string conversion.
	 *
	 * @param string|int|float|StringableInterface|NULL $label
	 * @return UI_Button
	 */
	public static function button(string|int|float|StringableInterface|null $label = null): UI_Button
	{
		/* ... */
	}


	/**
	 * @param string|int|float|StringableInterface|NULL $label
	 * @return UI_Bootstrap_ButtonDropdown
	 * @throws UI_Exception
	 */
	public static function buttonDropdown(
		string|int|float|StringableInterface|null $label = null,
	): UI_Bootstrap_ButtonDropdown
	{
		/* ... */
	}


	public function createPropertiesGrid(string $id = ''): UI_PropertiesGrid
	{
		/* ... */
	}


	/**
	 * Adds output to the console output, which is displayed
	 * for developer users.
	 *
	 * @since 3.3.5
	 * @param string $markup
	 */
	public function addConsoleOutput(string $markup): void
	{
		/* ... */
	}


	/**
	 * Sets the key to add to all scripts to make sure they are
	 * refreshed on the client when the key changes. Note that this
	 * has to set before any scripts are added to make sure it
	 * is used everywhere.
	 *
	 * @param string $key
	 */
	public function setIncludesLoadKey(string $key): void
	{
		/* ... */
	}


	/**
	 * Adds clientside support for adding progress bars to the page
	 * using the ProgressBar class.
	 */
	public function addProgressBar(): void
	{
		/* ... */
	}


	public function addBootstrap(): self
	{
		/* ... */
	}


	public function addSelect2(): self
	{
		/* ... */
	}


	public function addFontAwesome(): self
	{
		/* ... */
	}


	public function addJqueryUI(): self
	{
		/* ... */
	}


	public function addJquery(): self
	{
		/* ... */
	}


	/**
	 * Adds a redactor UI element.
	 *
	 * @param HTML_QuickForm2_Element $element
	 * @param Application_Countries_Country $country
	 * @return UI_MarkupEditor_Redactor
	 *
	 * @throws UnexpectedInstanceException
	 */
	public function addRedactor(
		HTML_QuickForm2_Element $element,
		Application_Countries_Country $country,
	): UI_MarkupEditor_Redactor
	{
		/* ... */
	}


	/**
	 * Creates an instance of a markup editor helper class.
	 * This includes all the necessary clientside includes, but
	 * also pre-configures matching WYSIYWG elements on page load.
	 *
	 * @param string $id The Markup editor ID, e.g. "Redactor", "Quill"
	 * @param HTML_QuickForm2_Element $element The element the editor should be tied to
	 * @param Application_Countries_Country $country The country for language of the content being edited
	 * @return UI_MarkupEditor
	 */
	public function addMarkupEditor(
		string $id,
		HTML_QuickForm2_Element $element,
		Application_Countries_Country $country,
	): UI_MarkupEditor
	{
		/* ... */
	}


	public static function printBacktrace(): void
	{
		/* ... */
	}


	/**
	 * Adds support for clientside data grid building with
	 * the `UI_DataGrid` classes.
	 *
	 * @return UI
	 */
	public function addDataGridSupport(): UI
	{
		/* ... */
	}


	/**
	 * Adds support for the screened dialogs.
	 *
	 * @return UI
	 */
	public function addScreenedDialogs(): UI
	{
		/* ... */
	}


	/**
	 * @return UI_Themes_Theme
	 */
	public function getTheme(): UI_Themes_Theme
	{
		/* ... */
	}


	public static function selectBootstrap4(): void
	{
		/* ... */
	}


	/**
	 * @return integer
	 */
	public static function getBoostrapVersion(): int
	{
		/* ... */
	}


	public static function isBootstrap4(): bool
	{
		/* ... */
	}


	/**
	 * Requires the subject to be a scalar value, or an object instance of the renderable interface.
	 *
	 * @param mixed|StringableInterface $subject
	 * @throws UI_Exception
	 * @return string|int|float|bool|StringableInterface
	 *
	 * @see UI::ERROR_NOT_A_RENDERABLE
	 */
	public static function requireRenderable(mixed $subject): string|int|float|bool|StringableInterface
	{
		/* ... */
	}


	/**
	 * Retrieves a list of all supported markup editors.
	 *
	 * @return UI_MarkupEditorInfo[]
	 */
	public function getMarkupEditors(): array
	{
		/* ... */
	}


	public function getDefaultMarkupEditor(): UI_MarkupEditorInfo
	{
		/* ... */
	}


	public static function isJavascriptMinified(): bool
	{
		/* ... */
	}


	/**
	 * Adds a listener for the page rendered event, which
	 * is called once the whole page to be sent to the browser
	 * has been rendered. It allows modifying the HTML code
	 * before it is sent to the browser.
	 *
	 * @param callable $listener
	 * @return EventListener
	 */
	public static function onPageRendered(callable $listener): EventListener
	{
		/* ... */
	}
}


```
---
**File Statistics**
- **Size**: 295.44 KB
- **Lines**: 17156
File: `modules/ui/architecture-core.md`
