<?php

declare(strict_types=1);

/** @var string $activeExampleID */

$grid = UI::getInstance()->createDataGrid('datagrid-example-form-target');

$grid->addColumn('id', t('ID'));
$grid->addColumn('label', t('Label'));

$grid->enableMultiSelect('id');
$grid->addHiddenScreenVars();
$grid->addHiddenVar(Application_Admin_Area_Devel_Appinterface::REQUEST_PARAM_EXAMPLE_ID, $activeExampleID);

// Make the grid's actions open in a new tab by default.
$grid->enableSubmitInNewTab();

$grid->addAction('submit-new', t('Submit (%1$s)', t('new tab')))
    ->setCallback('collectSubmittedGridValues');

// The form target can be overridden on a per-action basis.
$grid->addAction('submit-self', t('Submit (%1$s)', t('same tab')))
    ->setFormTarget('_self')
    ->setCallback('collectSubmittedGridValues');

$grid->addConfirmAction('confirm-new', t('Confirm (%1$s)', t('new tab')), t('Please confirm.'))
    ->setCallback('collectSubmittedGridValues');

$grid->addConfirmAction('confirm-self', t('Confirm (%1$s)', t('same tab')), t('Please confirm.'))
    ->setFormTarget('_self')
    ->setCallback('collectSubmittedGridValues');


// Display the grid
echo $grid->render(array(
    array(
        'id' => 42,
        'label' => t('The answer')
    ),
    array(
        'id' => 13,
        'label' => t('Friday!')
    )
));


/**
 * Adds the grid's submitted values to the UI message stack
 * to be displayed on the next request.
 *
 * @param UI_DataGrid_Action $action
 * @return void
 * @throws UI_Exception
 */
function collectSubmittedGridValues(UI_DataGrid_Action $action) : void
{
    UI::getInstance()->addMessage(sb()
        ->para(t('Submitted grid values:'))
        ->ul($action->getSelectedValues())
    );
}
