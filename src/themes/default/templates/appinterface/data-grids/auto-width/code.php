<?php

declare(strict_types=1);

$grid = UI::getInstance()->createDataGrid('datagrid-example-auto-width');

$grid->addColumn('id', t('ID'));
$grid->addColumn('label', t('Label'));

// Enable the automatic width
$grid->makeAutoWidth();

$entries = array(
    array(
        'id' => 42,
        'label' => t('The answer')
    ),
    array(
        'id' => 13,
        'label' => t('Friday!')
    )
);

echo $grid->render($entries);
