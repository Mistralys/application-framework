<?php

declare(strict_types=1);

use UI\Form\CustomElementInterface;
use UI\Form\CustomElementTrait;
use UI\Tree\TreeNode;
use UI\Tree\TreeRenderer;

class HTML_QuickForm2_Element_TreeSelect extends HTML_QuickForm2_Element implements CustomElementInterface
{
    use CustomElementTrait;

    public const ERROR_TREE_NOT_SET = 149701;

    public const ELEMENT_TYPE = 'treeselect';

    public static function getElementTypeID(): string
    {
        return self::ELEMENT_TYPE;
    }

    public static function getElementTypeLabel(): string
    {
        return t('Tree select');
    }

    private ?TreeRenderer $treeRenderer = null;

    /**
     * @var string[]
     */
    private array $values = array();

    public function setTree(TreeRenderer $renderer) : self
    {
        $this->treeRenderer = $renderer;
        return $this;
    }

    public function getTree() : ?TreeRenderer
    {
        if(!isset($this->treeRenderer) && $this->isDemoMode()) {
            $ui = UI::getInstance();
            $rootNode = new TreeNode($ui,t('Demo root node'));
            $rootNode->addChildNode(new TreeNode($ui, t('Child node'). ' 1'));
            $rootNode->addChildNode(new TreeNode($ui, t('Child node'). ' 2'));
            $this->treeRenderer = new TreeRenderer($ui, $rootNode);
        }

        return $this->treeRenderer;
    }

    public function requireTree() : TreeRenderer
    {
        $tree = $this->getTree();

        if($tree !== null) {
            return $tree;
        }

        throw new HTML_QuickForm2_NotFoundException(
            'TreeRenderer is not set',
            self::ERROR_TREE_NOT_SET
        );
    }

    public function __toString() : string
    {
        $tree = $this->getTree();
        if($tree === null) {
            return '';
        }

        $tree
            ->makeSelectable($this->getName())
            ->addSelectedValues($this->getValue());

        return $tree->render();
    }

    public function makeRequired() : self
    {
        $this->addRuleRequired(t('Please select at least one item.'));
        return $this;
    }

    public function getType(): string
    {
        return '';
    }

    /**
     * Fetches the currently selected values from the tree.
     *
     * @return string[]
     * @throws HTML_QuickForm2_NotFoundException
     */
    public function getRawValue() : array
    {
        return array_intersect($this->values, $this->requireTree()->getPossibleValues());
    }

    /**
     * @param array|mixed|NULL $value Flat, indexed array of tree node values or NULL for none. All other values are ignored.
     * @return $this
     */
    public function setValue($value): self
    {
        if(!is_array($value)) {
            return $this;
        }

        $this->values = array();

        foreach ($value as $val)
        {
            $val = trim((string)$val);
            if($val === '') {
                continue;
            }

            $this->values[] = $val;
        }

        return $this;
    }

    /**
     * Sets the values that should be marked as selected
     * in the tree.
     *
     * Note: This is an alias for {@see self::setValue()},
     * as it is more intuitive to use.
     *
     * @param string[]|null $values
     * @return $this
     */
    public function setValues(?array $values) : self
    {
        return $this->setValue($values);
    }

    /**
     * Fetches all currently selected values in the tree.
     *
     * Note: This is an alias for {@see self::getValue()},
     * as it is more intuitive to use.
     *
     * @return string[]
     */
    public function getValues() : array
    {
        $values = $this->getValue();

        if(is_array($values)) {
            return $values;
        }

        return array();
    }
}
