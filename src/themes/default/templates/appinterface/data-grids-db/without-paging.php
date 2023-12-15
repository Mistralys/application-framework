<?php

declare(strict_types=1);

use TestDriver\TestDBRecords\TestDBCollection;

require_once __DIR__.'/_grid-setup.php';

/* @var UI_DataGrid $grid */

// Get all items from the DB.
$items = TestDBCollection::getInstance()
    ->getFilterCriteria()
    ->configure($grid) // to apply sorting
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
