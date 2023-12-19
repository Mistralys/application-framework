<?php

/* @var string $activeExampleID */

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
$grid->addHiddenVar(Application_Admin_Area_Devel_Appinterface::REQUEST_PARAM_EXAMPLE_ID, $activeExampleID);
