<?php

declare(strict_types=1);

use Mistralys\Examples\HerbsCollection;

// Create the data grid
require_once __DIR__ . '/_grid-setup.php';
/* @var UI_DataGrid $grid */

$entries = array();

foreach (HerbsCollection::getInstance()->getAll() as $herb) {
    $entries[] = array(
        'id' => $herb->getID(),
        'name' => sb()->link($herb->getName(), '#'),
        'grams' => $herb->getGrams(),
        'local' => UI::prettyBool($herb->isLocal())->makeYesNo(),
        'local_sort' => (int)$herb->isLocal()
    );
}

// Enable the paging feature using default items per page
$grid->enableLimitOptionsDefault();

// The total number of entries is needed for the pager
$grid->setTotal(count($entries));

// Since we are paging manually, the entries must be sorted
// before they can be sliced to the current page.
$entries = $grid->filterAndSortEntries($entries);

echo $grid->render(array_slice($entries, $grid->getOffset(), $grid->getLimit()));
