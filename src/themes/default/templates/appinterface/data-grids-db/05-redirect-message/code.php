<?php

declare(strict_types=1);

use Mistralys\Examples\HerbsCollection;

$grid = UI::getInstance()->createDataGrid('redirect-message');

// (Configure data grid and entries here)

$grid->addAction('process', t('Process...'))
    ->setCallback('process_items');

function process_items(UI_DataGrid_Action $action) : void
{
    // Create the redirect message with the URL to redirect to,
    // and the text to use for the different scenarios.
    $message = $action->createRedirectMessage('https://mistralys.eu')
        ->none(t('No items were selected that could be processed.'))
        ->single(t('The item %1$s has been processed successfully at %2$s.', '$label', '$time'))
        ->multiple(t('%1$s items have been processed successfully at %2$s.', '$amount', '$time'));

    $herbs = HerbsCollection::getInstance();

    // Process items (pseudo code)
    foreach ($action->getSelectedValues() as $recordID)
    {
        $item = $herbs->getByID($recordID);

        if ($item->canBeProcessed()) {
            $item->process();

            $message->addAffected($item->getName());
        }
    }

    $message->redirect();
}
