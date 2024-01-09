<?php

declare(strict_types=1);

$form = Application_Formable_Generic::create('example-sections');

$form->addSection('Configuration options')
    ->setIcon(UI::icon()->options());

$form->addElementText('text1', 'First element');
$form->addElementText('text2', 'Second element');

$form->addSection('Advanced settings')
    ->setIcon(UI::icon()->settings());

$form->addElementText('text3', 'Third element');
$form->addElementText('text4', 'Fourth element');

$form->addSection('Backup settings')
    ->setIcon(UI::icon()->backup());

$form->addElementText('text5', 'Fifth element');
$form->addElementText('text6', 'Sixth element');

?>
<h3>Table of contents</h3>
<?php

// Simulating a sidebar to be able to show the TOC
$sidebar = new UI_Page_Sidebar(UI::getInstance()->getPage());
$sidebar->addFormableTOC($form);
echo $sidebar;

?>
<hr>
<h3>Form</h3>
<?php
echo $form;
