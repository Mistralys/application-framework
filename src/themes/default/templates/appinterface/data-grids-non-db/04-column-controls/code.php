<?php

declare(strict_types=1);

/** @var string $activeExampleID */

$grid = UI::getInstance()->createDataGrid('datagrid-example-column-controls');

$grid->addHiddenScreenVars();
$grid->addHiddenVar('example', $activeExampleID);

$letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L');

// The ID column, used to store the primary key of the row.
$grid->addColumn('id', 'ID')
    ->setCompact()
    ->alignRight();

// Add a bunch of regular columns
foreach($letters as $letter) {
    $grid->addColumn('col'.$letter, 'Column '.$letter);
}

// An expressly hidden column: Typically only used to
// store information that is not meant to be displayed.
// Cannot be reordered or shown/hidden.
$grid->addColumn('hidden', 'Hidden')
    ->setHidden();

// An actions column: Used to display actions for each row.
// Cannot be reordered or shown/hidden, and is always visible
// even when scrolling horizontally.
$grid->addColumn('actions', 'Actions')
    ->roleActions()
    ->setCompact();

$entries = array();

for ($row = 1; $row <= 10; $row++) {
    $entry = array();

    $entry['id'] = $row;
    $entry['hidden'] = 'Hidden '.$row;

    foreach($letters as $letter) {
        $entry['col'.$letter] = $letter.$row;
    }

    $entry['actions'] = UI::button(t('Action'))
        ->setIcon(UI::icon()->edit())
        ->makeSmall();

    $entries[] = $entry;
}

// Displays the toolbar to customize the layout
$grid->enableColumnControls();

$grid->enableMultiSelect('id');

// Add a stub action (required to enable the multiselect column)
$grid->addJSAction('example', t('Empty action'), "alert('Action clicked.')");

echo $grid->render($entries);
