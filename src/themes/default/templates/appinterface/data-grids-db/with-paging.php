<?php

declare(strict_types=1);

use TestDriver\TestDBRecords\TestDBCollection;

require_once __DIR__.'/_grid-setup.php';

/* @var UI_DataGrid $grid */

// Enable the paging feature using default items per page
$grid->enableLimitOptionsDefault();

// Get all DB items from the test collection
$items = TestDBCollection::getInstance()
    ->getFilterCriteria()
    ->configure($grid) // to fetch sorted and sliced items
    ->getItemsObjects();

$entries = array();

foreach($items as $item) {
    $entries[] = array(
        'record_id' => $item->getID(),
        'label' => $item->getLabelLinked(),
        'alias' => sb()->codeCopy($item->getAlias()),
    );
}

echo $grid->render($entries);
