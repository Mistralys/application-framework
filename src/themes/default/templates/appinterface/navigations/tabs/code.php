<?php

declare(strict_types=1);

/** @var string $activeURL The URL to this example */

$tabs = UI::getInstance()->createTabs('tabs-example');

$tabs->appendTab('Content 1', 'switch-1')
    ->setContent('<br><p>Content of tab <strong>#1</strong></p>');

$tabs->appendTab('Content 2', 'switch-2')
    ->setContent('<br><p>Content of tab <strong>#2</strong></p>');

$tabs->appendTab('Internal URL', 'url')
    ->makeLinked($activeURL);

$tabs->appendTab('External URL', 'url-external')
    ->makeLinked('https://mistralys.eu', true);

$menu = UI::getInstance()->createDropdownMenu();
$menu->addLink('Link 1', '#');
$menu->addLink('Link 2', '#');

$tabs->appendTab('Dropdown menu', 'dropdown')
    ->makeDropdown($menu);

// Tabs can have a select handler set executed
// every time the user switches to the tab.
$tabs->appendTab('JavaScript', 'js')
    ->clientOnSelect('alert("Tab is now selected!");')
    ->setContent('<br><p>Content of JavaScript tab content</p>');

echo $tabs;
