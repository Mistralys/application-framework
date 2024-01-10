<?php

declare(strict_types=1);

$sidebar = new UI_Page_Sidebar('sidebar-example');

$sidebar->addSection()
    ->setIcon(UI::icon()->structural())
    ->setTitle('Static section')
    ->setContent('Content of the section.');

$sidebar->addSection()
    ->setTitle('Collapsible section')
    ->collapse()
    ->setIcon(UI::icon()->settings())
    ->setContent('Content of the section.');

echo $sidebar;