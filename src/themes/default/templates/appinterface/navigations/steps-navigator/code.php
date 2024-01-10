<?php

declare(strict_types=1);

?><h3>Without numbering</h3><?php

$nav = new UI_Page_StepsNavigator();

$nav->addStep('first', 'Visited step')
    ->link('#')
    ->makeEnabled();

$nav->addStep('second', 'Active step')
    ->link('#')
    ->makeActive();

$nav->addStep('third', 'Inactive step')
    ->link('#');

echo $nav;

// ---------------------------------------------------

?><h3>With numbering</h3><?php

$nav = new UI_Page_StepsNavigator();

$nav->makeNumbered();

$nav->addStep('first', 'Visited step')
    ->link('#')
    ->makeEnabled();

$nav->addStep('second', 'Active step')
    ->link('#')
    ->makeActive();

$nav->addStep('third', 'Inactive step')
    ->link('#');

echo $nav;
