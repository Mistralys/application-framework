<?php

declare(strict_types=1);

$sel = UI::getInstance()
    ->createBigSelection()
    ->enableFiltering()
    ->setFilteringThreshold(3);

$sel->addItem('Regular linked item')
    ->makeLinked('#');

$sel->addItem('Item with description')
    ->setDescription('Description of the item here.')
    ->makeLinked('#');

$sel->addItem('Active item')
    ->setDescription('This item is marked as active.')
    ->makeActive();

$sel->addItem('JavaScript clickable item')
    ->makeClickable("alert('Item has been clicked.');");

echo $sel;
