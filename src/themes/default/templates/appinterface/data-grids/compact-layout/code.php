<?php

declare(strict_types=1);

/** @var string $activeExampleID */

use Mistralys\Examples\HerbsCollection;

$grid = UI::getInstance()->createDataGrid('datagrid-example-compact-layout');

$grid->addColumn('id', t('ID'));
$grid->addColumn('name', t('Name'));
$grid->addColumn('grams', t('Grams'))->alignRight();

$grid->makeAutoWidth(); // Don't use 100% width
$grid->enableCompactMode(); // Smaller cell paddings
$grid->disableForm(); // No form functionality needed
$grid->disableFooter();
$grid->disableHeader();

$entries = array();
foreach(HerbsCollection::getInstance()->getAll() as $herb) {
    $entries[] = array(
        'id' => $herb->getID(),
        'name' => $herb->getName(),
        'grams' => $herb->getGrams().' g',
    );
}

// Display the grid
echo $grid->render($entries);
