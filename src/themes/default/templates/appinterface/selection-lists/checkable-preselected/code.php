<?php

declare(strict_types=1);

$sel = UI::getInstance()->createBigSelection('fruits');

$sel->addCheckable('Apple', 'apple')
    ->setDescription('A sweet red or green fruit.')
    ->makeSelected();

$sel->addCheckable('Banana', 'banana')
    ->setDescription('A yellow tropical fruit.')
    ->makeSelected();

$sel->addCheckable('Cherry', 'cherry')
    ->setDescription('A small stone fruit.');

echo '<form method="post">';
echo $sel;
echo '</form>';
