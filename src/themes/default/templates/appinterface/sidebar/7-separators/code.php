<?php

declare(strict_types=1);

$sidebar = new UI_Page_Sidebar('sidebar-example');

$sidebar->addButton('button-1', 'Button 1');

$sidebar->addSeparator();

// This button will not be displayed, because the condition is false.
$sidebar->addButton('button-2', 'Button 2')
    ->requireTrue(false);

$sidebar->addSeparator();

$sidebar->addButton('button-3', 'Button 3');

echo $sidebar;
