<?php

declare(strict_types=1);

$menu = UI::getInstance()
    ->createDropdownMenu()
    ->addClass('examples-visible'); // Menus are hidden by default

$menu->addHeader('Header');

$menu->addLink('Regular link', '#');

$menu->addLink('External link', '#')
    ->setTarget('_blank');

$menu->addLink('With icon', '#')
    ->setIcon(UI::icon()->information());

$menu->addClickable('Clickable item', "alert('Clicked!');");

$menu->addMenu(t('Submenu'))
    ->addLink('Submenu item', '#');

$menu->addStatic('Static item (requires custom styling)');

$menu->addLink('Active item', '#')
    ->makeActive();

$menu->addSeparator();

$menu->addLink('Success item', '#')
    ->makeSuccess();

$menu->addLink('Warning item', '#')
    ->makeWarning();

$menu->addLink('Dangerous item', '#')
    ->makeDangerous();

$menu->addLink('Developer-only item', '#')
    ->makeDeveloper();

echo $menu;
