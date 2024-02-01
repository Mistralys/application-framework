<?php

declare(strict_types=1);

namespace UI\Tree;

use Application_Interfaces_Iconizable;
use Application_Traits_Iconizable;
use AppUtils\HTMLTag;
use AppUtils\Interfaces\StringableInterface;
use AppUtils\OutputBuffering;
use UI;
use UI\Targets\BaseTarget;
use UI\Targets\ClickTarget;
use UI\Targets\URLTarget;
use UI_Button;
use UI_Exception;

class TreeNode implements Application_Interfaces_Iconizable
{
    use Application_Traits_Iconizable;

    private string $label;
    private ?BaseTarget $target = null;

    /**
     * @var TreeNode[]
     */
    private array $childNodes = array();
    private ?TreeNode $parentNode;
    private bool $active = false;
    private bool $selected = false;

    /**
     * @var UI_Button[]
     */
    private array $actions = array();
    private string $value = '';
    private string $id;
    private UI $ui;

    /**
     * @param string|number|StringableInterface|NULL $label
     * @throws UI_Exception
     */
    public function __construct(UI $ui, $label, ?TreeNode $parentNode=null)
    {
        $this->ui = $ui;
        $this->label = toString($label);
        $this->parentNode = $parentNode;
        $this->id = 'node'.nextJSID();
    }

    public function getID() : string
    {
        return $this->id;
    }

    public function getCheckboxID() : string
    {
        return $this->id.'-cb';
    }

    /**
     * @param UI $ui
     * @param string|number|StringableInterface|NULL $label
     * @param TreeNode|null $parentNode
     * @return TreeNode
     * @throws UI_Exception
     */
    public static function create(UI $ui, $label, ?TreeNode $parentNode=null) : TreeNode
    {
        return new TreeNode($ui, $label, $parentNode);
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getTarget(): ?BaseTarget
    {
        return $this->target;
    }

    public function isRootNode() : bool
    {
        return !isset($this->parentNode);
    }

    public function isActive() : bool
    {
        return $this->active;
    }

    public function makeActive() : self
    {
        return $this->setActive(true);
    }

    public function setActive(bool $active) : self
    {
        $this->active = $active;
        return $this;
    }

    public function isSelected() : bool
    {
        return $this->selected;
    }

    public function setSelected(bool $selected) : self
    {
        $this->selected = $selected;
        return $this;
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
    public function setValue($value) : self
    {
        $this->value = (string)$value;
        return $this;
    }

    public function getValue() : string
    {
        return $this->value;
    }

    /**
     * @param string|number|StringableInterface|NULL $label
     * @return TreeNode
     * @throws UI_Exception
     */
    public function createChildNode($label) : TreeNode
    {
        $node = self::create($this->ui, $label, $this);
        $this->addChildNode($node);
        return $node;
    }

    public function addChildNode(TreeNode $node) : self
    {
        $this->childNodes[] = $node;
        $node->setParentNode($this);
        return $this;
    }

    public function setParentNode(?TreeNode $node) : self
    {
        $this->parentNode = $node;
        return $this;
    }

    public function getChildNodes() : array
    {
        return $this->childNodes;
    }

    public function link(string $url, bool $newTab=false) : self
    {
        return $this->setTarget(URLTarget::create($url, $newTab));
    }

    public function click(string $statement) : self
    {
        return $this->setTarget(ClickTarget::create($statement));
    }

    public function setTarget(BaseTarget $target) : self
    {
        $this->target = $target;
        return $this;
    }

    public function renderLabel(TreeRenderer $renderer) : string
    {
        $label = $this->renderIconLabel($this->getLabel());

        if(isset($this->target)) {
            return $this->target->getLinkTag()->setContent($label)->render();
        }

        if(!$this->isRootNode() && $renderer->isSelectable()) {
            return $this->renderCheckboxLabel($label);
        }

        return $label;
    }

    public function addButton(UI_Button $button) : self
    {
        $this->actions[] = $button;
        return $this;
    }

    public function renderActions() : string
    {
        OutputBuffering::start();

        ?>
        <div class="node-entry-actions">
            <?php
            echo $this->ui
                ->createButtonGroup()
                ->makeMini()
                ->addButtons($this->actions);
            ?>
        </div>
        <?php

        return OutputBuffering::get();
    }

    /**
     * @param string|number|StringableInterface|NULL $label
     * @return string
     */
    public function renderCheckboxLabel($label) : string
    {
        return HTMLTag::create('label')
            ->attr('for', $this->getCheckboxID())
            ->setContent($label)
            ->render();
    }

    public function renderCheckbox(TreeRenderer $renderer) : string
    {
        if($this->isRootNode()) {
            return '';
        }

        return HTMLTag::create('input')
            ->id($this->getCheckboxID())
            ->attr('type', 'checkbox')
            ->name($renderer->getElementName().'[]')
            ->attr('value', $this->value)
            ->prop('checked', $this->isSelected())
            ->addClass('tree-checkbox')
            ->setSelfClosing()
            ->render();
    }

    public function getJSObjectName() : string
    {
        return $this->getID().'obj';
    }

    public function injectJS(string $treeObjectName) : void
    {
        $this->ui->addJavascriptHead(sprintf(
            "const %s = %s.RegisterNode('%s', '%s')",
            $this->getJSObjectName(),
            $treeObjectName,
            $this->getID(),
            $this->getCheckboxID()
        ));

        $childNodes = $this->getChildNodes();

        foreach ($childNodes as $childNode) {
            $childNode->injectJS($treeObjectName);
        }
    }

    public function findNodeByValue($value) : ?TreeNode
    {
        if($value === null || $value === '') {
            return null;
        }

        if($this->getValue() === (string)$value) {
            return $this;
        }

        $childNodes = $this->getChildNodes();

        foreach ($childNodes as $childNode) {
            $node = $childNode->findNodeByValue($value);
            if($node !== null) {
                return $node;
            }
        }

        return null;
    }

    public function getSelectedNodes(array $result=array()) : array
    {
        if($this->isSelected()) {
            $result[] = $this;
        }

        $childNodes = $this->getChildNodes();

        foreach ($childNodes as $childNode) {
            $result = $childNode->getSelectedNodes($result);
        }

        return $result;
    }
}
