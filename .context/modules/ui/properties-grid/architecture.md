# UI Properties Grid - Architecture
_SOURCE: Public class signatures for the grid and property types_
# Public class signatures for the grid and property types
```
// Structure of documents
└── src/
    └── classes/
        └── UI/
            └── PropertiesGrid.php
            └── PropertiesGrid/
                └── Property.php
                └── Property/
                    └── Amount.php
                    └── Boolean.php
                    └── ByteSize.php
                    └── DateTime.php
                    └── Header.php
                    └── MarkdownGridProperty.php
                    └── Merged.php
                    └── Message.php
                    └── Regular.php
                    └── TagsGridProperty.php

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
###  Path: `/src/classes/UI/PropertiesGrid/Property.php`

```php
namespace ;

use AppUtils\OutputBuffering as OutputBuffering;

abstract class UI_PropertiesGrid_Property implements UI_Interfaces_Conditional
{
	use UI_Traits_Conditional;

	public function render(): string
	{
		/* ... */
	}


	/**
	 * Selects the text to show instead of the text if it is empty.
	 * @param string|number|UI_Renderable_Interface $text
	 * @return $this
	 * @throws UI_Exception
	 */
	public function ifEmpty($text)
	{
		/* ... */
	}


	/**
	 * @param UI_Button $button
	 * @return $this
	 * @throws Application_Exception
	 */
	public function addButton(UI_Button $button)
	{
		/* ... */
	}


	/**
	 * Typically shown inline next to the content of the property.
	 *
	 * @param string|number|UI_Renderable_Interface|NULL $comment
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setComment($comment): self
	{
		/* ... */
	}


	/**
	 * This text is typically shown with a help icon, and available by
	 * clicking on it.
	 *
	 * @param string|number|UI_Renderable_Interface|NULL $help
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setHelpText($help): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/PropertiesGrid/Property/Amount.php`

```php
namespace ;

class UI_PropertiesGrid_Property_Amount extends UI_PropertiesGrid_Property
{
}


```
###  Path: `/src/classes/UI/PropertiesGrid/Property/Amount.php`

```php
namespace ;

class UI_PropertiesGrid_Property_Amount extends UI_PropertiesGrid_Property
{
}


```
###  Path: `/src/classes/UI/PropertiesGrid/Property/Boolean.php`

```php
namespace ;

use AppUtils\ConvertHelper as ConvertHelper;
use AppUtils\ConvertHelper_Exception as ConvertHelper_Exception;

class UI_PropertiesGrid_Property_Boolean extends UI_PropertiesGrid_Property
{
	const TYPE_TRUEFALSE = 'truefalse';
	const TYPE_YESNO = 'yesno';
	const TYPE_ENABLEDDISABLED = 'enableddisabled';
	const TYPE_ACTIVEINACTIVE = 'activeinactive';
	const COLORS_DEFAULT = 'default';
	const COLORS_NEUTRAL = 'neutral';

	public function makeColorsNeutral(): UI_PropertiesGrid_Property_Boolean
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeYesNo()
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeEnabledDisabled()
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeActiveInactive()
	{
		/* ... */
	}


	/**
	 * @param string $labelTrue
	 * @param string $labelFalse
	 * @return $this
	 */
	public function setLabels(string $labelTrue, string $labelFalse)
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/PropertiesGrid/Property/Boolean.php`

```php
namespace ;

use AppUtils\ConvertHelper as ConvertHelper;
use AppUtils\ConvertHelper_Exception as ConvertHelper_Exception;

class UI_PropertiesGrid_Property_Boolean extends UI_PropertiesGrid_Property
{
	const TYPE_TRUEFALSE = 'truefalse';
	const TYPE_YESNO = 'yesno';
	const TYPE_ENABLEDDISABLED = 'enableddisabled';
	const TYPE_ACTIVEINACTIVE = 'activeinactive';
	const COLORS_DEFAULT = 'default';
	const COLORS_NEUTRAL = 'neutral';

	public function makeColorsNeutral(): UI_PropertiesGrid_Property_Boolean
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeYesNo()
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeEnabledDisabled()
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeActiveInactive()
	{
		/* ... */
	}


	/**
	 * @param string $labelTrue
	 * @param string $labelFalse
	 * @return $this
	 */
	public function setLabels(string $labelTrue, string $labelFalse)
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/PropertiesGrid/Property/ByteSize.php`

```php
namespace ;

use AppUtils\ConvertHelper as ConvertHelper;

class UI_PropertiesGrid_Property_ByteSize extends UI_PropertiesGrid_Property_Regular
{
}


```
###  Path: `/src/classes/UI/PropertiesGrid/Property/ByteSize.php`

```php
namespace ;

use AppUtils\ConvertHelper as ConvertHelper;

class UI_PropertiesGrid_Property_ByteSize extends UI_PropertiesGrid_Property_Regular
{
}


```
###  Path: `/src/classes/UI/PropertiesGrid/Property/DateTime.php`

```php
namespace ;

use AppUtils\ConvertHelper as ConvertHelper;

class UI_PropertiesGrid_Property_DateTime extends UI_PropertiesGrid_Property_Regular
{
	public function withTime(): UI_PropertiesGrid_Property_DateTime
	{
		/* ... */
	}


	public function withDiff(): UI_PropertiesGrid_Property_DateTime
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/PropertiesGrid/Property/DateTime.php`

```php
namespace ;

use AppUtils\ConvertHelper as ConvertHelper;

class UI_PropertiesGrid_Property_DateTime extends UI_PropertiesGrid_Property_Regular
{
	public function withTime(): UI_PropertiesGrid_Property_DateTime
	{
		/* ... */
	}


	public function withDiff(): UI_PropertiesGrid_Property_DateTime
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/PropertiesGrid/Property/Header.php`

```php
namespace ;

use AppUtils\OutputBuffering as OutputBuffering;

class UI_PropertiesGrid_Property_Header extends UI_PropertiesGrid_Property
{
	public function render(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/PropertiesGrid/Property/Header.php`

```php
namespace ;

use AppUtils\OutputBuffering as OutputBuffering;

class UI_PropertiesGrid_Property_Header extends UI_PropertiesGrid_Property
{
	public function render(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/PropertiesGrid/Property/MarkdownGridProperty.php`

```php
namespace UI\PropertiesGrid\Property;

use Application\MarkdownRenderer\MarkdownRenderer as MarkdownRenderer;
use UI_PropertiesGrid_Property_Merged as UI_PropertiesGrid_Property_Merged;
use UI_StringBuilder as UI_StringBuilder;

class MarkdownGridProperty extends UI_PropertiesGrid_Property_Merged
{
}


```
###  Path: `/src/classes/UI/PropertiesGrid/Property/MarkdownGridProperty.php`

```php
namespace UI\PropertiesGrid\Property;

use Application\MarkdownRenderer\MarkdownRenderer as MarkdownRenderer;
use UI_PropertiesGrid_Property_Merged as UI_PropertiesGrid_Property_Merged;
use UI_StringBuilder as UI_StringBuilder;

class MarkdownGridProperty extends UI_PropertiesGrid_Property_Merged
{
}


```
###  Path: `/src/classes/UI/PropertiesGrid/Property/Merged.php`

```php
namespace ;

use AppUtils\OutputBuffering as OutputBuffering;

class UI_PropertiesGrid_Property_Merged extends UI_PropertiesGrid_Property
{
	public function render(): string
	{
		/* ... */
	}


	public function addClass(string $class): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/PropertiesGrid/Property/Merged.php`

```php
namespace ;

use AppUtils\OutputBuffering as OutputBuffering;

class UI_PropertiesGrid_Property_Merged extends UI_PropertiesGrid_Property
{
	public function render(): string
	{
		/* ... */
	}


	public function addClass(string $class): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/PropertiesGrid/Property/Message.php`

```php
namespace ;

use UI\Interfaces\MessageWrapperInterface as MessageWrapperInterface;
use UI\Traits\MessageWrapperTrait as MessageWrapperTrait;

class UI_PropertiesGrid_Property_Message extends UI_PropertiesGrid_Property_Merged implements MessageWrapperInterface
{
	use MessageWrapperTrait;

	public function getMessage(): UI_Message
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/PropertiesGrid/Property/Message.php`

```php
namespace ;

use UI\Interfaces\MessageWrapperInterface as MessageWrapperInterface;
use UI\Traits\MessageWrapperTrait as MessageWrapperTrait;

class UI_PropertiesGrid_Property_Message extends UI_PropertiesGrid_Property_Merged implements MessageWrapperInterface
{
	use MessageWrapperTrait;

	public function getMessage(): UI_Message
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/PropertiesGrid/Property/Regular.php`

```php
namespace ;

class UI_PropertiesGrid_Property_Regular extends UI_PropertiesGrid_Property
{
}


```
###  Path: `/src/classes/UI/PropertiesGrid/Property/Regular.php`

```php
namespace ;

class UI_PropertiesGrid_Property_Regular extends UI_PropertiesGrid_Property
{
}


```
###  Path: `/src/classes/UI/PropertiesGrid/Property/TagsGridProperty.php`

```php
namespace UI\PropertiesGrid\Property;

use Application\Tags\Taggables\TaggableInterface as TaggableInterface;
use UI_PropertiesGrid_Property as UI_PropertiesGrid_Property;
use UI_StringBuilder as UI_StringBuilder;

/**
 * Displays all tags of a tabbable object.
 *
 * NOTE: Shown only if the tagging is enabled for the object.
 *
 * @package User Interface
 * @subpackage Properties Grid
 */
class TagsGridProperty extends UI_PropertiesGrid_Property
{
}


```
###  Path: `/src/classes/UI/PropertiesGrid/Property/TagsGridProperty.php`

```php
namespace UI\PropertiesGrid\Property;

use Application\Tags\Taggables\TaggableInterface as TaggableInterface;
use UI_PropertiesGrid_Property as UI_PropertiesGrid_Property;
use UI_StringBuilder as UI_StringBuilder;

/**
 * Displays all tags of a tabbable object.
 *
 * NOTE: Shown only if the tagging is enabled for the object.
 *
 * @package User Interface
 * @subpackage Properties Grid
 */
class TagsGridProperty extends UI_PropertiesGrid_Property
{
}


```
---
**File Statistics**
- **Size**: 16.73 KB
- **Lines**: 825
File: `modules/ui/properties-grid/architecture.md`
