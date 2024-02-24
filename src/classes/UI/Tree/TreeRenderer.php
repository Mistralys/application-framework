<?php
/**
 * @package User Interface
 * @subpackage Tree Renderer
 */

declare(strict_types=1);

namespace UI\Tree;

use AppUtils\AttributeCollection;
use AppUtils\Interfaces\OptionableInterface;
use AppUtils\OutputBuffering;
use AppUtils\Traits\OptionableTrait;
use UI;
use UI_Renderable;

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
class TreeRenderer extends UI_Renderable
    implements OptionableInterface
{
    use OptionableTrait;

    public const OPTION_SHOW_ROOT = 'showRoot';
    public const OPTION_EDITABLE = 'editable';
    public const OPTION_SELECTABLE = 'selectable';
    public const OPTION_ELEMENT_NAME = 'selectable_name';
    public const DEFAULT_ELEMENT_NAME = 'tree_items';
    public const OPTION_STANDALONE_FORM = 'standalone_form';

    private TreeNode $rootNode;
    /**
     * @var string[]
     */
    private array $selectedValues = array();
    private string $id;

    public function __construct(?UI $ui, TreeNode $rootNode)
    {
        if($ui === null) {
            $ui = UI::getInstance();
        }

        $this->rootNode = $rootNode;
        $this->id = 'tree'.nextJSID();

        parent::__construct($ui->getPage());
    }

    public function getID(): string
    {
        return $this->id;
    }

    public function getJSObjectName() : string
    {
        return $this->id.'obj';
    }

    /**
     * Adds a node value that should be marked as selected:
     *
     *
     * @param string|int|float $value
     * @return $this
     */
    public function addSelectedValue($value) : self
    {
        if(!in_array($value, $this->selectedValues, true)) {
            $this->selectedValues[] = (string)$value;
        }

        return $this;
    }

    /**
     * @param array<int,string|int|float> $values
     * @return self
     */
    public function addSelectedValues(array $values) : self
    {
        foreach($values as $value) {
            $this->addSelectedValue($value);
        }

        return $this;
    }


    /**
     * @return string[]
     */
    public function getSelectedValues() : array
    {
        sort($this->selectedValues);

        return $this->selectedValues;
    }

    /**
     * @return string[]
     */
    public function getPossibleValues() : array
    {
        return $this->findNodeValues($this->rootNode);
    }

    private function findNodeValues(TreeNode $node, array $values=array()) : array
    {
        $value = $node->getValue();

        if(!empty($value)) {
            $values[] = $value;
        }

        foreach($node->getChildNodes() as $childNode) {
            $values = $this->findNodeValues($childNode, $values);
        }

        sort($values);

        return $values;
    }

    protected function _render() : string
    {
        $this->ui->addStylesheet('ui/node-tree.css');

        $this->injectJS();

        OutputBuffering::start();

        $this->updateSelectedNodes($this->rootNode);

        $nodes = $this->rootNode->getChildNodes();

        ?>
        <div class="node-tree-wrapper" id="<?php echo $this->id ?>">
            <ul class="node-tree last-with-spacing <?php if(!empty($nodes)) { echo 'with-children'; } ?>">
                <?php
                if($this->isRootShown()) {
                    $this->renderTree($this->rootNode);
                } else {
                    foreach ($nodes as $node) {
                        $this->renderTree($node);
                    }
                }
                ?>
            </ul>
        </div>
        <?php

        return OutputBuffering::get();
    }

    private function injectJS() : void
    {
        $this->ui->addJavascript('ui/node-tree/tree-renderer.js');
        $this->ui->addJavascript('ui/node-tree/tree-node.js');

        $objName = $this->getJSObjectName();

        $this->ui->addJavascriptHead(sprintf(
            "const %s = new TreeRenderer('%s')",
            $objName,
            $this->getID()
        ));

        $this->rootNode->injectJS($objName);

        $this->ui->addJavascriptOnload(sprintf('%s.Start()', $objName));
    }

    private function renderTree(TreeNode $node) : void
    {
        $childNodes = $node->getChildNodes();

        $attributes = AttributeCollection::create()
            ->id($node->getID())
            ->attr('data-label', $node->getLabel())
            ->addClass('node-entry');

        if($node->isActive()) { $attributes->addClass('active'); }
        if($node->isSelected()) { $attributes->addClass('selected'); }
        if($node->isRootNode()) { $attributes->addClass('root'); }

        ?>
        <li<?php echo $attributes ?>>
            <div class="node-label">
                <?php
                if($this->isSelectable()) {
                    echo $node->renderCheckbox($this);
                }
                ?>
                <?php echo $node->renderLabel($this); ?>
                <?php
                if($this->isEditable()) {
                    echo $node->renderActions();
                }
                ?>
            </div>
            <?php

            if(!empty($childNodes))
            {
                ?>
                <ul class="node-tree last-with-spacing with-children">
                    <?php
                    foreach ($childNodes as $childNode) {
                        $this->renderTree($childNode);
                    }
                    ?>
                </ul>
                <?php
            }
            ?>
        </li>
        <?php
    }

    private function updateSelectedNodes(TreeNode $node) : void
    {
        $value = $node->getValue();
        $node->setSelected((!empty($value) && in_array($value, $this->selectedValues)));

        $childNodes = $node->getChildNodes();

        foreach($childNodes as $childNode) {
            $this->updateSelectedNodes($childNode);
        }
    }

    /**
     * @param bool $showRoot
     * @return $this
     */
    public function setShowRoot(bool $showRoot) : self
    {
        return $this->setOption(self::OPTION_SHOW_ROOT, $showRoot);
    }

    public function isRootShown() : bool
    {
        return $this->getBoolOption(self::OPTION_SHOW_ROOT);
    }

    public function makeEditable() : self
    {
        return $this->setEditable(true);
    }

    public function setEditable(bool $editable) : self
    {
        return $this->setOption(self::OPTION_EDITABLE, $editable);
    }

    public function isEditable() : bool
    {
        return $this->getBoolOption(self::OPTION_EDITABLE);
    }

    public function setSelectable(bool $selectable) : self
    {
        return $this->setOption(self::OPTION_SELECTABLE, $selectable);
    }

    public function isSelectable() : bool
    {
        return $this->getBoolOption(self::OPTION_SELECTABLE);
    }

    public function makeSelectable(string $elementName) : self
    {
        $this->setSelectable(true);
        $this->setElementName($elementName);

        return $this;
    }

    public function setElementName(string $elementName) : self
    {
        return $this->setOption(self::OPTION_ELEMENT_NAME, $elementName);
    }

    public function getElementName() : string
    {
        return $this->getStringOption(self::OPTION_ELEMENT_NAME);
    }

    public function getDefaultOptions(): array
    {
        return array(
            self::OPTION_SHOW_ROOT => true,
            self::OPTION_EDITABLE => false,
            self::OPTION_SELECTABLE => false,
            self::OPTION_ELEMENT_NAME => self::DEFAULT_ELEMENT_NAME
        );
    }

    /**
     * Retrieves a flattened list of all selected nodes.
     * @return TreeNode[]
     */
    public function getSelectedNodes() : array
    {
        $this->updateSelectedNodes($this->rootNode);

        return $this->rootNode->getSelectedNodes();
    }

    public function getRootNode() : TreeNode
    {
        return $this->rootNode;
    }


    /**
     * Attempts to find a node by its value.
     *
     * @param string|int|float|NULL $value
     * @return TreeNode|null
     */
    public function findNodeByValue($value) : ?TreeNode
    {
        if($value === null || $value === '') {
            return null;
        }

        return $this->rootNode->findNodeByValue($value);
    }
}
