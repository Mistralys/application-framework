<?php

declare(strict_types=1);

$sel = UI::getInstance()->createBigSelection();

$sel->addItem('Regular linked item')
    ->makeLinked('#');

$sel->addItem('Item with description')
    ->setDescription('Description of the item here.')
    ->makeLinked('#');

$sel->addItem('Active item')
    ->setDescription('This item is marked as active.')
    ->makeActive();

$sel->addHeader('Heading to separate items');

$sel->addItem('JavaScript clickable item')
    ->makeClickable("alert('Item has been clicked.');");

echo $sel;
