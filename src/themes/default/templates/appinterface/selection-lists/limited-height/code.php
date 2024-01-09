<?php

declare(strict_types=1);

$sel = UI::getInstance()
    ->createBigSelection()
    ->makeHeightLimited('300px');

for($i = 1; $i <= 10; $i++) {
    $sel->addItem('List item ' . $i)
        ->makeLinked('#');
}

echo $sel;
