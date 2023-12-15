<?php

/* @var string $activeExampleID */

use TestDriver\TestDBRecords\TestDBCollection;

$grid = UI::getInstance()->createDataGrid('datagrid-example');

// ----------------------------------------------
// Configure grid columns
// ----------------------------------------------

$grid->addColumn('record_id', t('ID'))
    ->setCompact()
    ->alignRight();

$grid->addColumn('label', t('Label'))
    ->setCompact()
    ->setSortable(true, TestDBCollection::COL_LABEL);

$grid->addColumn('alias', t('Alias'))
    ->setSortable(false, TestDBCollection::COL_ALIAS);


// ----------------------------------------------
// Configure request vars
// ----------------------------------------------

$grid->addHiddenScreenVars();
$grid->addHiddenVar(Application_Admin_Area_Devel_Appinterface::REQUEST_PARAM_EXAMPLE_ID, $activeExampleID);

// ----------------------------------------------
// Add some test records
// ----------------------------------------------

DBHelper::startTransaction();

$collection = TestDBCollection::getInstance();

$greekFigures = array(
    'Aphrodite',
    'Hermes',
    'Zeus',
    'Hera',
    'Poseidon',
    'Hades',
    'Athena',
    'Ares',
    'Apollo',
    'Artemis',
    'Hephaestus',
    'Demeter',
    'Dionysos',
    'Hestia'
);

foreach($greekFigures as $name) {
    $collection->createTestRecord($name, strtolower($name));
}
