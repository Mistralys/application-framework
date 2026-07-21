<?php

declare(strict_types=1);

$sel = UI::getInstance()->createBigSelection('options');

$sel->addHeader('Quick links');
$sel->addItem('Documentation')->makeLinked('#');
$sel->addItem('Support')->makeLinked('#');
$sel->addSeparator();
$sel->addCheckable('Enable feature A', 'feature_a');
$sel->addCheckable('Enable feature B', 'feature_b');

echo '<form method="post">';
echo $sel;
echo '</form>';
