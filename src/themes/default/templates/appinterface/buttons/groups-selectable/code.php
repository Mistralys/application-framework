<?php

declare(strict_types=1);

use Mistralys\Examples\UserInterface\ExampleFile;

$group = UI::getInstance()->createButtonGroup();

$group->addButton(
    UI::button('Button 1')
        ->setName('button1')
        ->link(ExampleFile::buildURL(array('selected-button' => 'button1')))
);

$group->addButton(
    UI::button('Button 2')
        ->setName('button2')
        ->link(ExampleFile::buildURL(array('selected-button' => 'button2')))
);

$group->addButton(
    UI::button('Button 3')
        ->setName('button3')
        ->link(ExampleFile::buildURL(array('selected-button' => 'button3')))
);

// Select the active button by using the button
// name specified in the request.
$group->selectByRequestParam('selected-button');

$group->display();
