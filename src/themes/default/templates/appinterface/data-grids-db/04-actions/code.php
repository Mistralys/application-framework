<?php

declare(strict_types=1);

use AppUtils\OutputBuffering;
use TestDriver\ClassFactory;
use TestDriver\TestDBRecords\TestDBCollection;

// Configure the data grid
require_once __DIR__.'/../01-grid-setup/code.php';

/* @var UI_DataGrid $grid */
/* @var string $activeURL */

// Enable the paging feature using default items per page
$grid->enableLimitOptionsDefault();

// ----------------------------------------
// Configure actions
// ----------------------------------------

// Enable the multi-select feature, and specify the
// name of the column that holds the required identifiers.
$grid->enableMultiSelect('record_id');

// Add the delete action
$grid->addAction('delete', t('Delete...'))
    ->makeDangerous()
    ->makeConfirm('Are you sure you want to delete these items?')
    ->setCallback('action_delete')
    ->setParam('redirect_url', $activeURL); // To have this available in the function

/**
 * Called when the grid's delete action is triggered.
 *
 * @param UI_DataGrid_Action $action
 * @return void
 */
function action_delete(UI_DataGrid_Action $action) : void
{
    $ids = $action->getSelectedValues();

    OutputBuffering::start();
    ?>
        <p>Submitted the action "Delete".</p>
        <p>Selected item IDs:</p>
        <pre><?php echo print_r($ids, true); ?></pre>
    <?php

    // Redirect to the same page, with a success message.
    // This prevents users from re-submitting the form
    // when refreshing the page.
    ClassFactory::createDriver()->redirectWithSuccessMessage(
        OutputBuffering::get(),
        $action->getParam('redirect_url')
    );
}

// ----------------------------------------
// Fetch items, and display the grid
// ----------------------------------------

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

