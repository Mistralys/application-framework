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

echo $grid->render($entries);
