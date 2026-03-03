# UI Bootstrap - Architecture
_SOURCE: Public class signatures for all Bootstrap components_
# Public class signatures for all Bootstrap components
```
// Structure of documents
└── src/
    └── classes/
        └── UI/
            └── Bootstrap/
                └── Anchor.php
                └── BadgeDropdown.php
                └── BaseDropdown.php
                └── BigSelection/
                    ├── BaseItem.php
                    ├── BigSelectionCSS.php
                    ├── BigSelectionWidget.php
                    ├── Item/
                    │   └── HeaderItem.php
                    │   └── RegularItem.php
                    │   └── SeparatorItem.php
                └── ButtonDropdown.php
                └── ButtonGroup.php
                └── ButtonGroup/
                    ├── ButtonGroupItemInterface.php
                └── Dropdown/
                    ├── AJAXLoader.php
                └── DropdownAnchor.php
                └── DropdownDivider.php
                └── DropdownHeader.php
                └── DropdownMenu.php
                └── DropdownStatic.php
                └── DropdownSubmenu.php
                └── Popover.php
                └── Tab.php
                └── Tab/
                    ├── Renderer.php
                    ├── Renderer/
                    │   └── Link.php
                    │   └── Menu.php
                    │   └── Toggle.php
                └── Tabs.php

```
###  Path: `/src/classes/UI/Bootstrap/Anchor.php`

```php
namespace ;

use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

/**
 * Bootstrap anchor element.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Bootstrap_Anchor extends UI_Bootstrap implements Application_Interfaces_Iconizable
{
	use Application_Traits_Iconizable;

	/**
	 * @param string|AdminURLInterface $href
	 * @return UI_Bootstrap|UI_Bootstrap_Anchor
	 */
	public function setHref($href)
	{
		/* ... */
	}


	public function setClick($statement)
	{
		/* ... */
	}


	public function setTarget($target)
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface|NULL $label
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setLabel($label): self
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Bootstrap/BadgeDropdown.php`

```php
namespace ;

class UI_Bootstrap_BadgeDropdown extends UI_Bootstrap_BaseDropdown implements UI_Interfaces_Badge
{
	public function makeLabel(): self
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface|NULL $label
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setLabel($label): self
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface|NULL $code
	 * @return $this
	 * @throws Application_Exception
	 */
	public function setWrapper($code): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeDangerous(): self
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
	public function makeInverse(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 * @throws Application_Exception
	 */
	public function makeInactive(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function cursorHelp(): self
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
	 * @param UI_Icon|NULL $icon
	 * @return $this
	 */
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
}


```
###  Path: `/src/classes/UI/Bootstrap/BaseDropdown.php`

```php
namespace ;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use AppUtils\Interfaces\StringableInterface as StringableInterface;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;
use UI\Bootstrap\Dropdown\AJAXLoader as AJAXLoader;
use UI\Interfaces\TooltipableInterface as TooltipableInterface;
use UI\Traits\TooltipableTrait as TooltipableTrait;

abstract class UI_Bootstrap_BaseDropdown extends UI_Bootstrap implements UI_Interfaces_Bootstrap_DropdownItem, TooltipableInterface
{
	use Application_Traits_Iconizable;
	use TooltipableTrait;

	/**
	 * Replaces the menu of the dropdown with the specified one.
	 *
	 * @param UI_Bootstrap_DropdownMenu $menu
	 * @return UI_Bootstrap_BaseDropdown
	 */
	public function setMenu(UI_Bootstrap_DropdownMenu $menu): self
	{
		/* ... */
	}


	public function makeNavItem(): self
	{
		/* ... */
	}


	/**
	 * @param string|int|float|StringableInterface|NULL $label
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setLabel($label): self
	{
		/* ... */
	}


	/**
	 * @return UI_Bootstrap_DropdownMenu
	 */
	public function getMenu(): UI_Bootstrap_DropdownMenu
	{
		/* ... */
	}


	/**
	 * Creates and adds a new anchor menu item.
	 * @param string|int|float|StringableInterface|NULL $label
	 * @param string|AdminURLInterface $url
	 * @return UI_Bootstrap_DropdownAnchor
	 *
	 * @throws UI_Exception
	 */
	public function addLink($label, $url): UI_Bootstrap_DropdownAnchor
	{
		/* ... */
	}


	/**
	 * Creates and adds a new anchor menu item
	 * linked to the specified javascript statement.
	 *
	 * @param string|int|float|StringableInterface|NULL $label
	 * @param string $statement
	 * @return UI_Bootstrap_DropdownAnchor
	 *
	 * @throws UI_Exception
	 */
	public function addClickable($label, string $statement): UI_Bootstrap_DropdownAnchor
	{
		/* ... */
	}


	/**
	 * Adds a header to the dropdown, to group items.
	 * @param string|int|float|StringableInterface|NULL $label
	 * @return UI_Bootstrap_DropdownHeader
	 */
	public function addHeader($label): UI_Bootstrap_DropdownHeader
	{
		/* ... */
	}


	public function addSeparator(): self
	{
		/* ... */
	}


	/**
	 * @param string|int|float|StringableInterface|NULL $content
	 * @return UI_Bootstrap_DropdownStatic
	 * @throws UI_Exception
	 */
	public function addStatic($content): UI_Bootstrap_DropdownStatic
	{
		/* ... */
	}


	/**
	 * @param bool $enabled
	 * @return $this
	 */
	public function setCaretEnabled(bool $enabled): self
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


	/**
	 * @param string $type
	 * @return $this
	 */
	public function setType(string $type): self
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
	public function makeInfo(): self
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}


	/**
	 * @param string $whichItem The name of the item to move
	 * @param string $afterItem The name of the item to move it after
	 * @return $this
	 */
	public function moveAfter(string $whichItem, string $afterItem): self
	{
		/* ... */
	}


	/**
	 * Attempts to retrieve an item by its name.
	 * @param string $name
	 * @return UI_Interfaces_Bootstrap_DropdownItem|NULL
	 */
	public function getItemByName(string $name): ?UI_Interfaces_Bootstrap_DropdownItem
	{
		/* ... */
	}


	/**
	 * @param string $methodName
	 * @param ArrayDataCollection|null $payload Optional parameters for the AJAX call.
	 * @return $this
	 */
	public function makeAJAX(string $methodName, ?ArrayDataCollection $payload = null): self
	{
		/* ... */
	}


	public function makeHeightLimited(string $heightStyle = '200px'): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Bootstrap/BigSelection/BaseItem.php`

```php
namespace UI\Bootstrap\BigSelection;

use Application_Interfaces_Iconizable as Application_Interfaces_Iconizable;
use Application_Traits_Iconizable as Application_Traits_Iconizable;
use UI_Bootstrap as UI_Bootstrap;

/**
 * @property BigSelectionWidget $parent
 */
abstract class BaseItem extends UI_Bootstrap implements Application_Interfaces_Iconizable
{
	use Application_Traits_Iconizable;

	public const CLASS_NAME_ENTRY = BigSelectionCSS::ITEM_ENTRY;

	/**
	 * Sets an optional reference ID for the item, which can be
	 * used to uniquely identify it.
	 *
	 * For example, when creating a list of products, using the
	 * product ID as reference ID will allow finding the item
	 * again using the product ID.
	 *
	 * @param string $referenceID
	 * @return $this
	 */
	public function setReferenceID(string $referenceID): self
	{
		/* ... */
	}


	/**
	 * @return string
	 */
	public function getReferenceID(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Bootstrap/BigSelection/BigSelectionCSS.php`

```php
namespace UI\Bootstrap\BigSelection;

/**
 * CSS class constants for the BigSelection widget and its items.
 *
 * This class provides a centralized location for all CSS class names
 * used by the BigSelection widget, making it easy to maintain and adjust
 * the styling across the entire component.
 */
class BigSelectionCSS
{
	public const WIDGET = 'bigselection';
	public const WIDGET_WRAPPER = 'bigselection-wrapper';
	public const WIDGET_HEIGHT_LIMITED = 'bigselection-height-limited';
	public const WIDGET_SIZE_SMALL = 'size-small';
	public const ITEM_ENTRY = 'bigselection-entry';
	public const ITEM_HEADER = 'bigselection-header';
	public const ITEM_SEPARATOR = 'bigselection-separator';
	public const ANCHOR = 'bigselection-anchor';
	public const LABEL = 'bigselection-label';
	public const DESCRIPTION = 'bigselection-description';
	public const META_CONTROLS_LIST = 'bigselection-meta-controls';
	public const META_CONTROL_ITEM = 'bigselection-meta-control';
	public const FILTERING_ENABLED = 'bigselection-filtering-enabled';
	public const FILTERING_CONTAINER = 'bigselection-filtering';
	public const SEARCH_INPUT = 'bigselection-search-terms';
	public const CLEAR_BUTTON = 'bigselection-clear-btn';
	public const STATE_ACTIVE = 'active';

	/** @see src/themes/default/js/ui/bigselection/static.js */
	public const RESOURCES_JS_HANDLER = 'ui/bigselection/static.js';

	/** @see src/themes/default/css/ui-bigselection.css */
	public const RESOURCES_STYLE_SHEET = 'ui-bigselection.css';
}


```
###  Path: `/src/classes/UI/Bootstrap/BigSelection/BigSelectionWidget.php`

```php
namespace UI\Bootstrap\BigSelection;

use AppUtils\Interfaces\OptionableInterface as OptionableInterface;
use AppUtils\Interfaces\StringableInterface as StringableInterface;
use AppUtils\NumberInfo as NumberInfo;
use AppUtils\Traits\OptionableTrait as OptionableTrait;
use Application_Exception as Application_Exception;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;
use UI\Bootstrap\BigSelection\Item\HeaderItem as HeaderItem;
use UI\Bootstrap\BigSelection\Item\RegularItem as RegularItem;
use UI\Bootstrap\BigSelection\Item\SeparatorItem as SeparatorItem;
use UI_Bootstrap as UI_Bootstrap;
use UI_Exception as UI_Exception;
use UI_Renderable_Interface as UI_Renderable_Interface;
use template_default_ui_bootstrap_big_selection as template_default_ui_bootstrap_big_selection;

/**
 * @package Application
 * @subpackage User Interface
 *
 * @property BaseItem[] $children
 *
 * @see BigSelectionCSS All CSS classes used by the widget and theme resource files.
 * @see template_default_ui_bootstrap_big_selection Template that renders the widget.
 */
class BigSelectionWidget extends UI_Bootstrap implements OptionableInterface
{
	use OptionableTrait;

	public const OPTION_FILTERING_THRESHOLD = 'filteringThreshold';
	public const OPTION_FILTERING_ENABLED = 'filteringEnabled';
	public const OPTION_EMPTY_MESSAGE = 'emptyMessage';
	public const OPTION_HEIGHT_LIMITED = 'heightLimited';

	public function getDefaultOptions(): array
	{
		/* ... */
	}


	/**
	 * Makes the list scroll if it becomes too long.
	 *
	 * @param string|int|float|NULL $maxHeight Height value parsable by {@see NumberInfo}. Set to NULL to disable.
	 * @return $this
	 * @see BigSelectionWidget::isHeightLimited()
	 */
	public function makeHeightLimited($maxHeight): self
	{
		/* ... */
	}


	public function getMaxHeight(): ?NumberInfo
	{
		/* ... */
	}


	/**
	 * Whether the list is limited in height.
	 *
	 * @return bool
	 * @see BigSelectionWidget::makeHeightLimited()
	 */
	public function isHeightLimited(): bool
	{
		/* ... */
	}


	/**
	 * Sets the message text to show when the list is empty.
	 *
	 * @param string|number|UI_Renderable_Interface $message
	 * @return BigSelectionWidget
	 */
	public function setEmptyMessage($message): BigSelectionWidget
	{
		/* ... */
	}


	/**
	 * Adds controls to filter the list by search terms.
	 *
	 * @param bool $enable
	 * @return BigSelectionWidget
	 */
	public function enableFiltering(bool $enable = true): BigSelectionWidget
	{
		/* ... */
	}


	/**
	 * Whether the filtering widget should be shown (it also
	 * depends on the filtering threshold, the minimum number
	 * of items to display it).
	 *
	 * @return bool
	 * @see BigSelectionWidget::setFilteringThreshold()
	 */
	public function isFilteringEnabled(): bool
	{
		/* ... */
	}


	/**
	 * Whether filtering is enabled, and there are enough
	 * items to actually display the filtering widget.
	 *
	 * @return bool
	 */
	public function isFilteringInUse(): bool
	{
		/* ... */
	}


	/**
	 * Counts the number of items in the selection.
	 *
	 * @return int
	 */
	public function countItems(): int
	{
		/* ... */
	}


	public function getFilteringThreshold(): int
	{
		/* ... */
	}


	/**
	 * Sets the number of items from which the filtering
	 * widget is displayed if filtering is enabled.
	 *
	 * @param int $amount
	 * @return BigSelectionWidget
	 */
	public function setFilteringThreshold(int $amount): BigSelectionWidget
	{
		/* ... */
	}


	public function getEmptyMessage(): string
	{
		/* ... */
	}


	/**
	 * Makes the items smaller.
	 *
	 * @return BigSelectionWidget
	 */
	public function makeSmall(): BigSelectionWidget
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface $label
	 * @return RegularItem
	 * @throws Application_Exception
	 * @throws UI_Exception
	 */
	public function prependItem($label): RegularItem
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface $title
	 * @return HeaderItem
	 * @throws Application_Exception
	 * @throws UI_Exception
	 */
	public function prependHeader($title): HeaderItem
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface $label
	 * @param string $url
	 * @return RegularItem
	 * @throws Application_Exception
	 * @throws UI_Exception
	 */
	public function prependLink($label, string $url): RegularItem
	{
		/* ... */
	}


	/**
	 * Adds a link to the list. Shortcut for adding the item and setting the link.
	 *
	 * @param string|number|UI_Renderable_Interface $label
	 * @param string|AdminURLInterface $url
	 * @return RegularItem
	 */
	public function addLink($label, $url): RegularItem
	{
		/* ... */
	}


	/**
	 * Adds an item to the list.
	 * Can be further configured via the returned instance.
	 *
	 * @param string|number|UI_Renderable_Interface $label
	 * @return RegularItem
	 * @throws Application_Exception
	 * @throws UI_Exception
	 */
	public function addItem($label): RegularItem
	{
		/* ... */
	}


	/**
	 * @param string|int|float|StringableInterface $title
	 * @return HeaderItem
	 * @throws Application_Exception
	 */
	public function addHeader(string|int|float|StringableInterface $title): HeaderItem
	{
		/* ... */
	}


	/**
	 * Adds a separator line to the list.
	 *
	 * @return SeparatorItem
	 * @throws UI_Exception
	 */
	public function addSeparator(): SeparatorItem
	{
		/* ... */
	}


	/**
	 * Prepends a separator line to the list.
	 *
	 * @return SeparatorItem
	 * @throws UI_Exception
	 */
	public function prependSeparator(): SeparatorItem
	{
		/* ... */
	}


	/**
	 * Retrieves all items that have been added.
	 *
	 * @return BaseItem[]
	 */
	public function getItems(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Bootstrap/BigSelection/Item/HeaderItem.php`

```php
namespace UI\Bootstrap\BigSelection\Item;

use AppUtils\Interfaces\StringableInterface as StringableInterface;
use AppUtils\OutputBuffering as OutputBuffering;
use UI\Bootstrap\BigSelection\BaseItem as BaseItem;
use UI\Bootstrap\BigSelection\BigSelectionCSS as BigSelectionCSS;
use UI_Exception as UI_Exception;

class HeaderItem extends BaseItem
{
	/**
	 * @param string|int|float|StringableInterface $title
	 * @throws UI_Exception
	 */
	public function setTitle(string|int|float|StringableInterface $title): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Bootstrap/BigSelection/Item/RegularItem.php`

```php
namespace UI\Bootstrap\BigSelection\Item;

use AppUtils\AttributeCollection as AttributeCollection;
use AppUtils\Interfaces\StringableInterface as StringableInterface;
use AppUtils\OutputBuffering as OutputBuffering;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;
use UI\Bootstrap\BigSelection\BaseItem as BaseItem;
use UI\Bootstrap\BigSelection\BigSelectionCSS as BigSelectionCSS;
use UI_Exception as UI_Exception;
use UI_Renderable_Interface as UI_Renderable_Interface;

class RegularItem extends BaseItem
{
	public const ATTRIBUTE_DESCRIPTION = 'description';
	public const ATTRIBUTE_HREF = 'href';
	public const ATTRIBUTE_ONCLICK = 'onclick';

	/**
	 * Changes the label after instantiating the item.
	 *
	 * @param string|number|UI_Renderable_Interface $label
	 * @return RegularItem
	 * @throws UI_Exception
	 */
	public function setLabel($label): RegularItem
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	/**
	 * Sets a description that will be shown along with the label.
	 *
	 * @param string|number|UI_Renderable_Interface $text
	 * @return RegularItem
	 * @throws UI_Exception
	 */
	public function setDescription($text): RegularItem
	{
		/* ... */
	}


	public function getDescription(): string
	{
		/* ... */
	}


	/**
	 * Adds a control to the meta area of the item (typically floating on the right side).
	 *
	 * @param string|StringableInterface $control
	 * @param AttributeCollection|null $attributes Optional attributes for the meta-control element.
	 * @return $this
	 */
	public function addMetaControl($control, ?AttributeCollection $attributes = null): self
	{
		/* ... */
	}


	/**
	 * @param string|AdminURLInterface $url
	 * @return $this
	 */
	public function makeLinked($url): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeActive(): self
	{
		/* ... */
	}


	public function makeClickable($statement): RegularItem
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Bootstrap/BigSelection/Item/SeparatorItem.php`

```php
namespace UI\Bootstrap\BigSelection\Item;

use UI\Bootstrap\BigSelection\BaseItem as BaseItem;
use UI\Bootstrap\BigSelection\BigSelectionCSS as BigSelectionCSS;

class SeparatorItem extends BaseItem
{
}


```
###  Path: `/src/classes/UI/Bootstrap/ButtonDropdown.php`

```php
namespace ;

use AppUtils\ConvertHelper as ConvertHelper;
use AppUtils\Interfaces\StringableInterface as StringableInterface;
use UI\Bootstrap\ButtonGroup\ButtonGroupItemInterface as ButtonGroupItemInterface;
use UI\Traits\ActivatableTrait as ActivatableTrait;
use UI\Traits\ButtonSizeTrait as ButtonSizeTrait;

class UI_Bootstrap_ButtonDropdown extends UI_Bootstrap_BaseDropdown implements ButtonGroupItemInterface
{
	use ButtonSizeTrait;
	use ActivatableTrait;

	/**
	 * Makes the button a link.
	 *
	 * @return $this
	 */
	public function makeLink(): self
	{
		/* ... */
	}


	/**
	 * Makes the menu open on the left side of the toggle,
	 * instead of the default right side.
	 *
	 * @return $this
	 */
	public function openLeft(): self
	{
		/* ... */
	}


	/**
	 * @param string $class
	 * @return $this
	 */
	public function addLinkClass(string $class): self
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @param string|number|StringableInterface|NULL $value
	 * @return $this
	 */
	public function setLinkAttribute(string $name, $value): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Bootstrap/ButtonGroup.php`

```php
namespace ;

use Application\AppFactory as AppFactory;
use UI\Bootstrap\ButtonGroup\ButtonGroupItemInterface as ButtonGroupItemInterface;
use UI\Interfaces\ButtonSizeInterface as ButtonSizeInterface;
use UI\Traits\ButtonSizeTrait as ButtonSizeTrait;

class UI_Bootstrap_ButtonGroup extends UI_Bootstrap implements ButtonSizeInterface
{
	use ButtonSizeTrait;

	public const ERROR_BUTTON_NAME_NOT_FOUND = 159301;
	public const ERROR_BUTTON_NAME_NOT_SET = 159302;

	/**
	 * Adds a button to the group.
	 *
	 * @param ButtonGroupItemInterface $button
	 * @param string|null $name Optional name to be able to get/select the button later.
	 * @return $this
	 */
	public function addButton(ButtonGroupItemInterface $button, ?string $name = null): self
	{
		/* ... */
	}


	/**
	 * @param ButtonGroupItemInterface[] $buttons
	 * @return self
	 */
	public function addButtons(array $buttons): self
	{
		/* ... */
	}


	public function nameExists(string $name): bool
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @return ButtonGroupItemInterface
	 * @throws UI_Exception
	 */
	public function getByName(string $name): ButtonGroupItemInterface
	{
		/* ... */
	}


	/**
	 * @return ButtonGroupItemInterface[]
	 */
	public function getAll(): array
	{
		/* ... */
	}


	public function selectButton(UI_Button $button): self
	{
		/* ... */
	}


	public function selectByName(string $name): self
	{
		/* ... */
	}


	public function selectByRequestParam(string $paramName): self
	{
		/* ... */
	}


	public function getSelected(): ?ButtonGroupItemInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Bootstrap/ButtonGroup/ButtonGroupItemInterface.php`

```php
namespace UI\Bootstrap\ButtonGroup;

use AppUtils\Interfaces\RenderableInterface as RenderableInterface;
use UI\Interfaces\ActivatableInterface as ActivatableInterface;
use UI\Interfaces\ButtonSizeInterface as ButtonSizeInterface;
use UI\Interfaces\NamedItemInterface as NamedItemInterface;

/**
 * Interface for items that can be added to a button group.
 *
 * @package User Interface
 * @subpackage Interfaces
 */
interface ButtonGroupItemInterface extends ButtonSizeInterface, ActivatableInterface, RenderableInterface, NamedItemInterface
{
}


```
###  Path: `/src/classes/UI/Bootstrap/Dropdown/AJAXLoader.php`

```php
namespace UI\Bootstrap\Dropdown;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use AppUtils\ConvertHelper\JSONConverter as JSONConverter;
use UI as UI;

class AJAXLoader
{
	/**
	 * Renders the placeholder for the menu.
	 * @return string
	 */
	public function renderPlaceholder(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Bootstrap/DropdownAnchor.php`

```php
namespace ;

use AppUtils\OutputBuffering as OutputBuffering;

/**
 * Bootstrap dropdown anchor element.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Bootstrap_DropdownAnchor extends UI_Bootstrap implements Application_Interfaces_Iconizable, UI_Interfaces_Bootstrap_DropdownItem
{
	public function init(): void
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface|NULL $title
	 * @return UI_Bootstrap_DropdownAnchor
	 * @throws UI_Exception
	 */
	public function setTitle($title): self
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface|NULL $label
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setLabel($label): self
	{
		/* ... */
	}


	public function setHref(string $url): self
	{
		/* ... */
	}


	/**
	 * Sets the target of the anchor tag for the link.
	 *
	 * @param string $target
	 * @return UI_Bootstrap_DropdownAnchor
	 */
	public function setTarget(string $target): self
	{
		/* ... */
	}


	public function setOnclick(string $statement): self
	{
		/* ... */
	}


	/**
	 * @param UI_Icon|NULL $icon
	 * @return UI_Bootstrap_DropdownAnchor
	 */
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


	public function makeDangerous(): self
	{
		/* ... */
	}


	public function makeSuccess(): self
	{
		/* ... */
	}


	public function makeWarning(): self
	{
		/* ... */
	}


	public function makeActive(): self
	{
		/* ... */
	}


	public function makeDeveloper(): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Bootstrap/DropdownDivider.php`

```php
namespace ;

class UI_Bootstrap_DropdownDivider extends UI_Bootstrap implements UI_Interfaces_Bootstrap_DropdownItem
{
}


```
###  Path: `/src/classes/UI/Bootstrap/DropdownHeader.php`

```php
namespace ;

use AppUtils\OutputBuffering as OutputBuffering;

/**
 * Bootstrap dropdown header element.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Bootstrap_DropdownHeader extends UI_Bootstrap implements Application_Interfaces_Iconizable, UI_Interfaces_Bootstrap_DropdownItem
{
	use Application_Traits_Iconizable;

	/**
	 * @param string|number|UI_Renderable_Interface|NULL $title
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setTitle($title): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Bootstrap/DropdownMenu.php`

```php
namespace ;

use AppUtils\ClassHelper as ClassHelper;
use AppUtils\ClassHelper\ClassNotExistsException as ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException as ClassNotImplementsException;
use AppUtils\OutputBuffering as OutputBuffering;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

class UI_Bootstrap_DropdownMenu extends UI_Bootstrap
{
	public function renderMenuItems(): string
	{
		/* ... */
	}


	/**
	 * Makes the menu open on the left side of the toggle,
	 * instead of the default right side.
	 *
	 * @return $this
	 */
	public function openLeft(): self
	{
		/* ... */
	}


	/**
	 * Adds a submenu item: creates the menu instance
	 * and returns it to be configured.
	 *
	 * @param string|number|UI_Renderable_Interface|NULL $label The label of the submenu item
	 * @return UI_Bootstrap_DropdownSubmenu
	 * @throws UI_Exception
	 */
	public function addMenu($label): UI_Bootstrap_DropdownSubmenu
	{
		/* ... */
	}


	/**
	 * Adds a menu item that links to a regular URL.
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
	 * Whether the menu has items.
	 * @return boolean
	 */
	public function hasItems(): bool
	{
		/* ... */
	}


	/**
	 * Adds a subheader within the menu.
	 * @param string|number|UI_Renderable_Interface|NULL $label
	 * @return UI_Bootstrap_DropdownHeader
	 */
	public function addHeader($label): UI_Bootstrap_DropdownHeader
	{
		/* ... */
	}


	/**
	 * Adds a menu item that executes the specified javascript
	 * statement when clicked.
	 *
	 * @param string|number|UI_Renderable_Interface|NULL $label
	 * @param string $statement
	 * @return UI_Bootstrap_DropdownAnchor
	 * @throws UI_Exception
	 */
	public function addClickable($label, string $statement): UI_Bootstrap_DropdownAnchor
	{
		/* ... */
	}


	/**
	 * @return $this
	 * @throws Application_Exception
	 */
	public function addSeparator(): self
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface|NULL $content
	 * @return UI_Bootstrap_DropdownStatic
	 * @throws UI_Exception
	 */
	public function addStatic($content): UI_Bootstrap_DropdownStatic
	{
		/* ... */
	}


	/**
	 * Moves the specified item directly after another item
	 * in the menu, by their names. Note: you have to set the
	 * names of the items for this to work.
	 *
	 * @param string $whichItem The name of the item to move
	 * @param string $afterItem The name of the item to move it after
	 * @return UI_Bootstrap_DropdownMenu
	 */
	public function moveAfter(string $whichItem, string $afterItem): self
	{
		/* ... */
	}


	/**
	 * Retrieves a menu item by its name.
	 *
	 * @param string $name
	 * @return UI_Interfaces_Bootstrap_DropdownItem|NULL
	 */
	public function getItemByName(string $name): ?UI_Interfaces_Bootstrap_DropdownItem
	{
		/* ... */
	}


	public function makeHeightLimited(string $heightStyle = '200px'): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Bootstrap/DropdownStatic.php`

```php
namespace ;

use AppUtils\Interfaces\StringableInterface as StringableInterface;

/**
 * Bootstrap dropdown static HTML element.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Bootstrap_DropdownStatic extends UI_Bootstrap implements UI_Interfaces_Bootstrap_DropdownItem
{
	/**
	 * @param string|int|float|StringableInterface|NULL $content
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setContent(string|int|float|StringableInterface|null $content): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Bootstrap/DropdownSubmenu.php`

```php
namespace ;

use AppUtils\Interfaces\StringableInterface as StringableInterface;
use AppUtils\OutputBuffering as OutputBuffering;

class UI_Bootstrap_DropdownSubmenu extends UI_Bootstrap_DropdownMenu implements Application_Interfaces_Iconizable, UI_Interfaces_Bootstrap_DropdownItem
{
	use Application_Traits_Iconizable;

	public const ERROR_MENU_INSTANCE_NOT_SET = 101101;

	/**
	 * @param string|int|float|StringableInterface|NULL $title
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setTitle(string|int|float|StringableInterface|null $title): self
	{
		/* ... */
	}


	public function setMenu(UI_Bootstrap_DropdownMenu $menu): self
	{
		/* ... */
	}


	/**
	 * @param string $class
	 * @return $this
	 */
	public function addLIClass(string $class): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 * @throws UI_Exception
	 */
	public function makeOpenUp(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeOpenLeft(): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Bootstrap/Popover.php`

```php
namespace ;

class UI_Bootstrap_Popover extends UI_Bootstrap
{
	public const ERROR_INVALID_PLACEMENT = 89801;
	const TEMPLATE_ID = 'ui/bootstrap/popover';
	const PLACEMENT_RIGHT = 'right';
	const PLACEMENT_LEFT = 'left';
	const PLACEMENT_TOP = 'top';
	const PLACEMENT_BOTTOM = 'bottom';
	const TEMPLATE_KEY_POPOVER = 'popover';
	const TEMPLATE_KEY_ATTACH_TO_ID = 'attachToID';
	const TEMPLATE_KEY_CONTENT = 'content';
	const TEMPLATE_KEY_PLACEMENT = 'placement';
	const TEMPLATE_KEY_TITLE = 'title';

	/**
	 * @param string $title
	 * @return UI_Bootstrap_Popover
	 */
	public function setTitle(string $title): UI_Bootstrap_Popover
	{
		/* ... */
	}


	/**
	 * @return string
	 */
	public function getTitle(): string
	{
		/* ... */
	}


	/**
	 * Sets the ID of the element to attach the popover to.
	 *
	 * @param string $attachToID
	 * @return UI_Bootstrap_Popover
	 */
	public function setAttachToID(string $attachToID): UI_Bootstrap_Popover
	{
		/* ... */
	}


	public function getAttachToID(): string
	{
		/* ... */
	}


	/**
	 * Sets the content to display in the body of
	 * the popover. May contain HTML.
	 *
	 * @param scalar|UI_Renderable_Interface $content
	 * @return UI_Bootstrap_Popover
	 * @throws UI_Exception
	 *
	 * @see UI::ERROR_NOT_A_RENDERABLE
	 */
	public function setContent($content): UI_Bootstrap_Popover
	{
		/* ... */
	}


	public function getContent(): string
	{
		/* ... */
	}


	public function getValidPlacements(): array
	{
		/* ... */
	}


	public function isValidPlacement(string $placement): bool
	{
		/* ... */
	}


	/**
	 * Sets the placement to use for the popover.
	 *
	 * @param string $placement
	 * @return $this
	 * @throws UI_Exception
	 *
	 * @see UI_Bootstrap_Popover::PLACEMENT_TOP
	 * @see UI_Bootstrap_Popover::PLACEMENT_BOTTOM
	 * @see UI_Bootstrap_Popover::PLACEMENT_LEFT
	 * @see UI_Bootstrap_Popover::PLACEMENT_RIGHT
	 *
	 * @see UI_Bootstrap_Popover::ERROR_INVALID_PLACEMENT
	 */
	public function setPlacement(string $placement): UI_Bootstrap_Popover
	{
		/* ... */
	}


	/**
	 * Places the popover on the left of the target element.
	 *
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setPlacementLeft(): UI_Bootstrap_Popover
	{
		/* ... */
	}


	/**
	 * Places the popover on the right of the target element.
	 *
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setPlacementRight(): UI_Bootstrap_Popover
	{
		/* ... */
	}


	/**
	 * Places the popover above the target element.
	 *
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setPlacementTop(): UI_Bootstrap_Popover
	{
		/* ... */
	}


	/**
	 * Places the popover below the target element.
	 *
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setPlacementBottom(): UI_Bootstrap_Popover
	{
		/* ... */
	}


	public function getPlacement(): string
	{
		/* ... */
	}


	public function getShowStatement(): string
	{
		/* ... */
	}


	public function getHideStatement(): string
	{
		/* ... */
	}


	public function getToggleStatement(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Bootstrap/Tab.php`

```php
namespace ;

use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

/**
 * Handles individual tabs in a tab container.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @method UI_Bootstrap_Tabs getParent()
 */
class UI_Bootstrap_Tab extends UI_Bootstrap implements Application_Interfaces_Iconizable
{
	use Application_Traits_Iconizable;

	const TYPE_TOGGLE = 'Toggle';
	const TYPE_LINK = 'Link';
	const TYPE_MENU = 'Menu';

	/**
	 * @param string|int|float|UI_Renderable_Interface $label
	 * @return $this
	 */
	public function setLabel($label): UI_Bootstrap_Tab
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	public function select(): UI_Bootstrap_Tab
	{
		/* ... */
	}


	public function deselect(): UI_Bootstrap_Tab
	{
		/* ... */
	}


	public function isSelected(): bool
	{
		/* ... */
	}


	/**
	 * @param string $statement
	 * @return $this
	 */
	public function clientOnSelect($statement)
	{
		/* ... */
	}


	/**
	 * Retrieves the javascript statement string for the specified
	 * event, if any.
	 *
	 * @param string $eventName The name of the event, e.g. "select".
	 * @return string The statement string, or an empty string if none present.
	 */
	public function getEventStatement(string $eventName): string
	{
		/* ... */
	}


	/**
	 * @return UI_Bootstrap_Tabs
	 */
	public function getTabs(): UI_Bootstrap_Tabs
	{
		/* ... */
	}


	public function getID(): string
	{
		/* ... */
	}


	/**
	 * @param string|int|float|UI_Renderable_Interface $content
	 * @return $this
	 */
	public function setContent($content): self
	{
		/* ... */
	}


	/**
	 * @return string
	 */
	public function getURLTarget(): string
	{
		/* ... */
	}


	/**
	 * Whether the tab has a toggleable body.
	 *
	 * @return bool
	 */
	public function hasBody(): bool
	{
		/* ... */
	}


	public function getLinkID(): string
	{
		/* ... */
	}


	/**
	 * Turns the tab into a static link that does not have any content.
	 *
	 * @param string|AdminURLInterface $url
	 * @param bool $newTab
	 * @return UI_Bootstrap_Tab
	 */
	public function makeLinked($url, bool $newTab = false): UI_Bootstrap_Tab
	{
		/* ... */
	}


	/**
	 * Sets a tooltip to shown when hovering over the tab.
	 *
	 * @param string|int|float|UI_Renderable_Interface $tooltip
	 * @return UI_Bootstrap_Tab
	 */
	public function setTooltip($tooltip): UI_Bootstrap_Tab
	{
		/* ... */
	}


	public function makeDropdown(UI_Bootstrap_DropdownMenu $menu): UI_Bootstrap_Tab
	{
		/* ... */
	}


	public function getMenu(): ?UI_Bootstrap_DropdownMenu
	{
		/* ... */
	}


	/**
	 * Returns the URL the tab links to, if it is a static link
	 * instead of a toggleable tab (see <code>makeLinked</code>).
	 *
	 * @return string
	 */
	public function getURL(): string
	{
		/* ... */
	}


	public function renderTab(): string
	{
		/* ... */
	}


	/**
	 * Sets the target of the URL to open when the tab
	 * is in URL mode.
	 *
	 * @param string $target
	 * @return $this
	 */
	public function setURLTarget(string $target): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Bootstrap/Tab/Renderer.php`

```php
namespace ;

abstract class UI_Bootstrap_Tab_Renderer extends UI_Renderable
{
}


```
###  Path: `/src/classes/UI/Bootstrap/Tab/Renderer/Link.php`

```php
namespace ;

class UI_Bootstrap_Tab_Renderer_Link extends UI_Bootstrap_Tab_Renderer
{
}


```
###  Path: `/src/classes/UI/Bootstrap/Tab/Renderer/Menu.php`

```php
namespace ;

class UI_Bootstrap_Tab_Renderer_Menu extends UI_Bootstrap_Tab_Renderer
{
}


```
###  Path: `/src/classes/UI/Bootstrap/Tab/Renderer/Toggle.php`

```php
namespace ;

class UI_Bootstrap_Tab_Renderer_Toggle extends UI_Bootstrap_Tab_Renderer
{
}


```
###  Path: `/src/classes/UI/Bootstrap/Tabs.php`

```php
namespace ;

use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;

/**
 * Bootstrap tabs container.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Bootstrap_Tabs extends UI_Bootstrap
{
	public const ERROR_TAB_ALREADY_EXISTS = 18501;
	public const ERROR_TAB_NAME_NOT_FOUND = 18502;

	public function getID(): string
	{
		/* ... */
	}


	/**
	 * @return UI_Bootstrap_Tab
	 */
	public function getSelectedTab(): UI_Bootstrap_Tab
	{
		/* ... */
	}


	/**
	 * Selects the active tab by fetching the tab name from
	 * a request variable.
	 *
	 * @param string $varName
	 * @return $this
	 * @throws Application_Exception
	 */
	public function selectByRequestVar(string $varName): UI_Bootstrap_Tabs
	{
		/* ... */
	}


	/**
	 * Selects the active tab by fetching the name from the current `submode` request variable.
	 *
	 * @return $this
	 * @throws Application_Exception
	 */
	public function selectBySubmode(): UI_Bootstrap_Tabs
	{
		/* ... */
	}


	/**
	 * Selects the active tab by fetching the name from the current `action` request variable.
	 *
	 * @return $this
	 * @throws Application_Exception
	 */
	public function selectByAction(): UI_Bootstrap_Tabs
	{
		/* ... */
	}


	/**
	 * Selects the active tab by fetching the name from the current `mode` request variable.
	 *
	 * @return $this
	 * @throws Application_Exception
	 */
	public function selectByMode(): UI_Bootstrap_Tabs
	{
		/* ... */
	}


	public function selectTab(UI_Bootstrap_Tab $target): UI_Bootstrap_Tabs
	{
		/* ... */
	}


	/**
	 * Adds a new tab at the end of the tabs list.
	 *
	 * @param string $label
	 * @param string $name
	 * @return UI_Bootstrap_Tab
	 * @throws Application_Exception
	 */
	public function appendTab(string $label, string $name = ''): UI_Bootstrap_Tab
	{
		/* ... */
	}


	public function hasTab(string $name): bool
	{
		/* ... */
	}


	public function getTabByName(string $name): UI_Bootstrap_Tab
	{
		/* ... */
	}


	/**
	 * @return UI_Bootstrap_Tab[]
	 */
	public function getTabs(): array
	{
		/* ... */
	}
}


```
---
**File Statistics**
- **Size**: 38.76 KB
- **Lines**: 2123
File: `modules/ui/bootstrap/architecture.md`
