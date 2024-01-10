<?php

declare(strict_types=1);

?><h3>Without numbering</h3><?php

$nav = new UI_Page_StepsNavigator();
$nav->addStep('first', 'Visited step')
    ->link('#')
    ->setEnabled();

$nav->addStep('second', 'Active step')
    ->link('#')
    ->setEnabled();

$nav->addStep('third', 'Inactive step')
    ->link('#');

$nav->selectStep('second');

echo $nav;


?><h3>With numbering</h3><?php

$nav = new UI_Page_StepsNavigator();
$nav->makeNumbered();

$nav->addStep('first', 'Visited step')
    ->link('#')
    ->setEnabled();

$nav->addStep('second', 'Active step')
    ->link('#')
    ->setEnabled();

$nav->addStep('third', 'Inactive step')
    ->link('#');

$nav->selectStep('second');

echo $nav;
