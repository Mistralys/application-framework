<?php

declare(strict_types=1);

use UI\Tree\TreeNode;

$root = TreeNode::create('Sitemap')
    ->setIcon(UI::icon()->home());

$root->createChildNode('Home');

$products = $root->createChildNode('Products');

$prod1 = $products->createChildNode('Product 1');
$prod1->createChildNode('Features');
$prod1->createChildNode('Pricing');

$prod2 = $products->createChildNode('Product 2');
$prod2->createChildNode('Features');
$prod2->createChildNode('Pricing');

$about = $root->createChildNode('About');
$about->createChildNode('The Company');
$about->createChildNode('Contact');

echo UI::getInstance()->createTreeRenderer($root);
