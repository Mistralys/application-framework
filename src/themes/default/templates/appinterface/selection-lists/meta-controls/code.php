<?php

declare(strict_types=1);

$sel = UI::getInstance()->createBigSelection();

$sel->addItem('Item with a success icon')
    ->makeLinked('#')
    ->addMetaControl(UI::icon()->ok()->makeSuccess());

$sel->addItem('Item with a button')
    ->setDescription('Description of the item here.')
    ->makeLinked('#')
    ->addMetaControl(UI::button()
        ->makeDangerous()
        ->makeMini()
        ->setIcon(UI::icon()->delete())
        ->setLabel('Delete')
    )
    ->addMetaControl(UI::button()
        ->makeMini()
        ->setIcon(UI::icon()->copy())
        ->setLabel('Duplicate')
    );

echo $sel;

echo '<br>';
echo '<h4>Small selection</h4>';

$sel->makeSmall();

echo $sel;
