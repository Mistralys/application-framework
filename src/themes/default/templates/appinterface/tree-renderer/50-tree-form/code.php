<?php

declare(strict_types=1);

use Application\AppFactory;
use UI\Tree\TreeNode;

/** @var string $activeExampleID */
/** @var string $activeURL */

$ui = UI::getInstance();

// ----------------------------------------------------------
// Create a tree
// ----------------------------------------------------------

$root = TreeNode::create($ui, 'Root');

$root->createChildNode('Item A')
    ->setValue('A');

$b = $root->createChildNode('Item B')
    ->setValue('B');

$b1 = $b->createChildNode('Item B 1')
    ->setValue('B.1');

$b1->createChildNode('Item B 1.1')
    ->setValue('B.1.1');

$root->createChildNode('Item C')
    ->setValue('C');

$tree = $ui
    ->createTreeRenderer($root)
    ->setShowRoot(false);

// ----------------------------------------------------------
// Create the form
// ----------------------------------------------------------

$form = Application_Formable_Generic::create('treeform');
$form->addHiddenVar('example', $activeExampleID);
$form->addFormablePageVars();

$treeEL = $form->addElementTreeSelect('tree_items', 'Tree items')
    ->setTree($tree)
    ->makeRequired();

$form->getFormInstance()->addPrimarySubmit('Confirm selection', 'confirm')
    ->setIcon(UI::icon()->ok());

if($form->isFormValid())
{
    AppFactory::createDriver()->redirectWithSuccessMessage(
        'Selected items were: '.sb()->ul($treeEL->getValues()),
        $activeURL
    );
}

echo $form;
