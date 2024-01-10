<?php

declare(strict_types=1);

$sidebar = new UI_Page_Sidebar('sidebar-example');

$menu = $sidebar->addDropdownButton('default', 'Button menu')
    ->setIcon(UI::icon()->menu());

$menu->addLink('Link 1', '#');
$menu->addLink('Link 2', '#');
$menu->addLink('Link 3', '#');

$menu->addHeader('Subheader');

$menu->addLink('Link 4', '#');
$menu->addLink('Link 5', '#');

echo $sidebar;
