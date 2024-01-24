<?php

declare(strict_types=1);

use UI\Tree\TreeNode;

$root = TreeNode::create('Root');

$root->createChildNode('Editable item')
    ->addButton(
        UI::button('Edit')
            ->link('#')
            ->setIcon(UI::icon()->edit())
    );

$root->createChildNode('Deletable item')
    ->addButton(
        UI::button('Delete')
            ->link('#')
            ->setIcon(UI::icon()->delete())
            ->makeDangerous()
    );

$root->createChildNode('Multiple actions')
    ->addButton(
        UI::button('Edit')
            ->link('#')
            ->setIcon(UI::icon()->edit())
    )
    ->addButton(
        UI::button('Delete')
            ->link('#')
            ->setIcon(UI::icon()->delete())
            ->makeDangerous()
    );

echo UI::getInstance()
    ->createTreeRenderer($root)
    ->makeEditable();
