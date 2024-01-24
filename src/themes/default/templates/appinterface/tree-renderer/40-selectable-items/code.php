<?php

declare(strict_types=1);

use UI\Tree\TreeNode;

$ui = UI::getInstance();

$root = TreeNode::create($ui, 'Root');

$root->createChildNode('Item A')
    ->setValue('a');

$root->createChildNode('Item B')
    ->setValue('b');

$root->createChildNode('Item C')
    ->setValue('c');

echo $ui
    ->createTreeRenderer($root)
    ->setShowRoot(false)
    ->addSelectedValue('b')
    ->makeSelectable('tree_items');
