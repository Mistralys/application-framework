<?php

/** @var string $activeExampleID */

use UI\Admin\Screens\AppInterfaceDevelMode;

$grid = UI::getInstance()->createDataGrid('datagrid-example');

// ----------------------------------------------
// Configure grid columns
// ----------------------------------------------

$grid->addColumn('id', t('ID'))
    ->setCompact()
    ->alignRight();

$grid->addColumn('name', t('Name'))
    ->setSortable()
    ->setSortingString();

$grid->addColumn('local', t('Locally sourced'))
    ->alignCenter()
    ->setCompact()
    ->setNowrap()
    ->setSortingNumeric('local_sort');

$grid->addColumn('grams', t('Grams'))
    ->setSortingNumeric()
    ->alignRight();

// ----------------------------------------------
// Configure request vars
// ----------------------------------------------

$grid->addHiddenScreenVars();
$grid->addHiddenVar(AppInterfaceDevelMode::REQUEST_PARAM_EXAMPLE_ID, $activeExampleID);
