<?php

declare(strict_types=1);

namespace AppFrameworkTests\UI\TreeRenderer;

use AppFrameworkTestClasses\ApplicationTestCase;
use UI;
use UI\Tree\TreeNode;

final class NodeTreeTest extends ApplicationTestCase
{
    /**
     * Empty values must be ignored when using {@see TreeRenderer::findNodeByValue()},
     * otherwise nodes without values would match.
     */
    public function test_getNodeByEmptyValue() : void
    {
        $ui = UI::getInstance();

        $root = TreeNode::create($ui, 'Root');
        $a = $root->createChildNode('Item A');

        $this->assertSame('', $a->getValue());

        $tree = $ui->createTreeRenderer($root);

        $this->assertNull($tree->findNodeByValue(''));
        $this->assertNull($tree->findNodeByValue(null));
    }

    public function test_getNodeByValueSameValues() : void
    {
        $ui = UI::getInstance();

        $root = TreeNode::create($ui, 'Root');

        $a = $root->createChildNode('Item A')
            ->setValue('a');

        $a->createChildNode('Item B')
            ->setValue('a');

        $tree = $ui->createTreeRenderer($root);

        $this->assertSame($a, $tree->findNodeByValue('a'));
    }

    public function test_getNodeByValueNested() : void
    {
        $ui = UI::getInstance();

        $root = TreeNode::create($ui, 'Root');

        $a = $root->createChildNode('Item A')
            ->setValue('a');

        $b = $a->createChildNode('Item B')
            ->setValue('b');

        $c = $b->createChildNode('Item C')
            ->setValue('c');

        $tree = $ui->createTreeRenderer($root);

        $this->assertSame($a, $tree->findNodeByValue('a'));
        $this->assertSame($b, $tree->findNodeByValue('b'));
        $this->assertSame($c, $tree->findNodeByValue('c'));
    }

    /**
     * String and numeric values must be interchangeable
     * in both directions (setting and getting).
     */
    public function test_getNodeByValueInteger() : void
    {
        $ui = UI::getInstance();

        $root = TreeNode::create($ui, 'Root');

        $a = $root->createChildNode('Item 0')
            ->setValue(0);

        $b = $a->createChildNode('Item 1')
            ->setValue(1);

        $c = $b->createChildNode('Item 2')
            ->setValue('2');

        $tree = $ui->createTreeRenderer($root);

        $this->assertSame($a, $tree->findNodeByValue(0));
        $this->assertSame($a, $tree->findNodeByValue('0'));

        $this->assertSame($b, $tree->findNodeByValue(1));
        $this->assertSame($b, $tree->findNodeByValue('1'));

        $this->assertSame($c, $tree->findNodeByValue(2));
        $this->assertSame($c, $tree->findNodeByValue('2'));
    }

    public function test_getSelectedNodes() : void
    {
        $ui = UI::getInstance();

        $root = TreeNode::create($ui, 'Root');

        $a = $root->createChildNode('Item A')
            ->setValue('a');

        $b = $a->createChildNode('Item B')
            ->setValue('b');

        $c = $b->createChildNode('Item C')
            ->setValue('c');

        $tree = $ui->createTreeRenderer($root)
            ->addSelectedValue('a')
            ->addSelectedValue('c');

        $this->assertSame(array('a', 'c'), $tree->getSelectedValues());
        $this->assertSame(array($a, $c), $tree->getSelectedNodes());
    }
}
