<?php

declare(strict_types=1);

$sidebar = new UI_Page_Sidebar('sidebar-example');

$sidebar->addButton('default', 'Default button')
    ->link('#');

$sidebar->addButton('primary', 'Primary button')
    ->makePrimary()
    ->link('#');

$sidebar->addButton('info', 'Info button')
    ->makeInfo()
    ->link('#');

$sidebar->addButton('success', 'Success button')
    ->makeSuccess()
    ->link('#');

$sidebar->addButton('warning', 'Warning button')
    ->makeWarning()
    ->link('#');

$sidebar->addButton('danger', 'Dangerous button')
    ->makeDangerous()
    ->link('#');

$sidebar->addButton('inverse', 'Inverted button')
    ->makeInverse()
    ->link('#');

$sidebar->addButton('developer', 'Developer button')
    ->makeDeveloper()
    ->link('#');

echo $sidebar;
