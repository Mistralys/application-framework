<?php

declare(strict_types=1);

$section = UI::getInstance()
    ->createSection()
    ->setTitle('Item selection list')
    ->setAbstract('Please select a destination.')
    ->setIcon(UI::icon()->list());

$sel = $section->addItemsSelector();

$sel->addItem(
    'Application framework',
    'https://github.com/Mistralys/application-framework',
    'The AppFramework repository homepage.'
);

$sel->addItem(
    'Application Utils',
    'https://github.com/Mistralys/application-utils',
    'The AppUtils helper classes repository homepage.'
);

echo $section;
