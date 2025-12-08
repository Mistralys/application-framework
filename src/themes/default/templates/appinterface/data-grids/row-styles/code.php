<?php

declare(strict_types=1);

/** @var string $activeExampleID */

$grid = UI::getInstance()->createDataGrid('datagrid-example-row-styles');

$grid->addColumn('id', t('ID'));
$grid->addColumn('label', t('Label'));

$grid->enableMultiSelect('id');

// Add a stub action to enable the checkboxes
$grid->addAction('nil', t('Do nothing'))
    ->setJSMethod("alert('".t('Do nothing')."');return false;");

// Display the grid
echo $grid->render(array(

    $grid->createEntry(array(
        'id' => 1,
        'label' => t('Success-styled row')
    ))
        ->makeSuccess(),

    $grid->createEntry(array(
        'id' => 2,
        'label' => t('Warning-styled row')
    ))
        ->makeWarning(),

    $grid->createEntry(array(
        'id' => 3,
        'label' => t('Regular row')
    )),

    $grid->createEntry(array(
        'id' => 4,
        'label' => t('Selected row')
    ))
        ->select()
));

