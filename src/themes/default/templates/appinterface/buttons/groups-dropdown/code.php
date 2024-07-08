<?php

declare(strict_types=1);

use Mistralys\Examples\UserInterface\ExampleFile;

$group = UI::getInstance()->createButtonGroup();

$group->addButton(UI::button('Regular button'));

$dropdown = UI::buttonDropdown('Dropdown button');

$dropdown->addLink('Menu item 1', ExampleFile::buildURL());
$dropdown->addLink('Menu item 2', ExampleFile::buildURL());

$group->addButton($dropdown);

$group->display();
