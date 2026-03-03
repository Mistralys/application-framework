# UI Tree - Architecture
_SOURCE: Public class signatures for TreeNode and TreeRenderer_
# Public class signatures for TreeNode and TreeRenderer
```
// Structure of documents
└── src/
    └── classes/
        └── UI/
            └── Tree/
                └── TreeNode.php
                └── TreeRenderer.php

```
###  Path: `/src/classes/UI/Tree/TreeNode.php`

```php
namespace UI\Tree;

use AppUtils\HTMLTag as HTMLTag;
use AppUtils\Interfaces\StringableInterface as StringableInterface;
use AppUtils\OutputBuffering as OutputBuffering;
use Application_Interfaces_Iconizable as Application_Interfaces_Iconizable;
use Application_Traits_Iconizable as Application_Traits_Iconizable;
use UI as UI;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;
use UI\Targets\BaseTarget as BaseTarget;
use UI\Targets\ClickTarget as ClickTarget;
use UI\Targets\URLTarget as URLTarget;
use UI_Button as UI_Button;
use UI_Exception as UI_Exception;

class TreeNode implements Application_Interfaces_Iconizable
{
	use Application_Traits_Iconizable;

	public function getID(): string
	{
		/* ... */
	}


	public function getCheckboxID(): string
	{
		/* ... */
	}


	/**
	 * @param UI $ui
	 * @param string|number|StringableInterface|NULL $label
	 * @param TreeNode|null $parentNode
	 * @return TreeNode
	 * @throws UI_Exception
	 */
	public static function create(UI $ui, $label, ?TreeNode $parentNode = null): TreeNode
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	public function getTarget(): ?BaseTarget
	{
		/* ... */
	}


	public function isRootNode(): bool
	{
		/* ... */
	}


	public function isActive(): bool
	{
		/* ... */
	}


	public function makeActive(): self
	{
		/* ... */
	}


	public function setActive(bool $active): self
	{
		/* ... */
	}


	public function isSelected(): bool
	{
		/* ... */
	}


	public function setSelected(bool $selected): self
	{
		/* ... */
	}


	/**
	 * Sets a value for the node, which is used when the tree
	 * renderer is set to selectable mode. When the tree's form
	 * is submitted, this value is used as the value of the
	 * checkbox.
	 *
	 * NOTE: Duplicate values are not allowed, but are not
	 * validated. If you set the same value on multiple nodes,
	 * the first one will be used.
	 *
	 * @param string|int|float $value
	 * @return $this
	 */
	public function setValue($value): self
	{
		/* ... */
	}


	public function getValue(): string
	{
		/* ... */
	}


	/**
	 * @param string|number|StringableInterface|NULL $label
	 * @return TreeNode
	 * @throws UI_Exception
	 */
	public function createChildNode($label): TreeNode
	{
		/* ... */
	}


	public function addChildNode(TreeNode $node): self
	{
		/* ... */
	}


	public function setParentNode(?TreeNode $node): self
	{
		/* ... */
	}


	public function getChildNodes(): array
	{
		/* ... */
	}


	/**
	 * @param string|AdminURLInterface $url
	 * @param bool $newTab
	 * @return $this
	 */
	public function link($url, bool $newTab = false): self
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
	 * @param BaseTarget $target
	 * @return $this
	 */
	public function setTarget(BaseTarget $target): self
	{
		/* ... */
	}


	public function renderLabel(TreeRenderer $renderer): string
	{
		/* ... */
	}


	public function addButton(UI_Button $button): self
	{
		/* ... */
	}


	public function renderActions(): string
	{
		/* ... */
	}


	/**
	 * @param string|number|StringableInterface|NULL $label
	 * @return string
	 */
	public function renderCheckboxLabel($label): string
	{
		/* ... */
	}


	public function renderCheckbox(TreeRenderer $renderer): string
	{
		/* ... */
	}


	public function getJSObjectName(): string
	{
		/* ... */
	}


	public function injectJS(string $treeObjectName): void
	{
		/* ... */
	}


	public function findNodeByValue($value): ?TreeNode
	{
		/* ... */
	}


	public function getSelectedNodes(array $result = []): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Tree/TreeRenderer.php`

```php
namespace UI\Tree;

use AppUtils\AttributeCollection as AttributeCollection;
use AppUtils\Interfaces\OptionableInterface as OptionableInterface;
use AppUtils\OutputBuffering as OutputBuffering;
use AppUtils\Traits\OptionableTrait as OptionableTrait;
use UI as UI;
use UI_Renderable as UI_Renderable;

/**
 * Usage:
 *
 * 1. Create a tree using a {@see TreeNode} instance as root node.
 * 2. Create the renderer instance using {@see UI::createTreeRenderer()}.
 * 3. Configure the renderer's options
 * 4. Display the tree
 *
 * @package User Interface
 * @subpackage Tree Renderer
 */
class TreeRenderer extends UI_Renderable implements OptionableInterface
{
	use OptionableTrait;

	public const OPTION_SHOW_ROOT = 'showRoot';
	public const OPTION_EDITABLE = 'editable';
	public const OPTION_SELECTABLE = 'selectable';
	public const OPTION_ELEMENT_NAME = 'selectable_name';
	public const DEFAULT_ELEMENT_NAME = 'tree_items';
	public const OPTION_STANDALONE_FORM = 'standalone_form';
	public const STYLESHEET_FILE = 'ui/node-tree.css';

	public function getID(): string
	{
		/* ... */
	}


	public function getJSObjectName(): string
	{
		/* ... */
	}


	/**
	 * Adds a node value that should be marked as selected:
	 *
	 *
	 * @param string|int|float $value
	 * @return $this
	 */
	public function addSelectedValue($value): self
	{
		/* ... */
	}


	/**
	 * @param array<int,string|int|float> $values
	 * @return self
	 */
	public function addSelectedValues(array $values): self
	{
		/* ... */
	}


	/**
	 * @return string[]
	 */
	public function getSelectedValues(): array
	{
		/* ... */
	}


	/**
	 * @return string[]
	 */
	public function getPossibleValues(): array
	{
		/* ... */
	}


	/**
	 * @param bool $showRoot
	 * @return $this
	 */
	public function setShowRoot(bool $showRoot): self
	{
		/* ... */
	}


	public function isRootShown(): bool
	{
		/* ... */
	}


	public function makeEditable(): self
	{
		/* ... */
	}


	public function setEditable(bool $editable): self
	{
		/* ... */
	}


	public function isEditable(): bool
	{
		/* ... */
	}


	public function setSelectable(bool $selectable): self
	{
		/* ... */
	}


	public function isSelectable(): bool
	{
		/* ... */
	}


	public function makeSelectable(string $elementName): self
	{
		/* ... */
	}


	public function setElementName(string $elementName): self
	{
		/* ... */
	}


	public function getElementName(): string
	{
		/* ... */
	}


	public function getDefaultOptions(): array
	{
		/* ... */
	}


	/**
	 * Retrieves a flattened list of all selected nodes.
	 * @return TreeNode[]
	 */
	public function getSelectedNodes(): array
	{
		/* ... */
	}


	public function getRootNode(): TreeNode
	{
		/* ... */
	}


	/**
	 * Attempts to find a node by its value.
	 *
	 * @param string|int|float|NULL $value
	 * @return TreeNode|null
	 */
	public function findNodeByValue($value): ?TreeNode
	{
		/* ... */
	}
}


```
---
**File Statistics**
- **Size**: 6.91 KB
- **Lines**: 447
File: `modules/ui/tree/architecture.md`
