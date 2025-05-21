<?php

declare(strict_types=1);

use Mistralys\Examples\HerbsCollection;

// Create the data grid
require_once __DIR__ . '/../01-grid-setup/code.php';

/** @var UI_DataGrid $grid */

$entries = array();

// Build the array of entries to display in the grid,
// and format the cell values as needed.
foreach (HerbsCollection::getInstance()->getAll() as $herb) {
    $entries[] = array(
        'id' => $herb->getID(),
        'name' => sb()->link($herb->getName(), '#'),
        'grams' => $herb->getGrams(),
        'local' => UI::prettyBool($herb->isLocal())->makeYesNo(),
        'local_sort' => (int)$herb->isLocal() // Not displayed; used for sorting
    );
}

echo $grid->render($entries);
