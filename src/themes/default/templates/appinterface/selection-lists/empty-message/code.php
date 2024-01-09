<?php

declare(strict_types=1);

$sel = UI::getInstance()
    ->createBigSelection()
    ->setEmptyMessage('No items to display.');

echo $sel;
