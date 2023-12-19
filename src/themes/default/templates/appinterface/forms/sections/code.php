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

echo $form;
