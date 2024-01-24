<?php

declare(strict_types=1);

use UI\Tree\TreeNode;

$root = TreeNode::create('Root');

$root->createChildNode('Unlinked item');

$root->createChildNode('Linked item')
    ->link('https://mistralys.eu');

$root->createChildNode('External link')
    ->link('https://mistralys.eu', true);

$root->createChildNode('JavaScript item')
    ->click("alert('Hello world!')");

$root->createChildNode('Active item')
    ->makeActive();

$root->createChildNode('Active linked item')
    ->makeActive()
    ->link('https://mistralys.eu');


echo UI::getInstance()->createTreeRenderer($root);
