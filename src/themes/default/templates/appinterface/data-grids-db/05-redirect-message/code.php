<?php

declare(strict_types=1);

use Mistralys\Examples\HerbsCollection;

$grid = UI::getInstance()->createDataGrid('redirect-message');

// (Configure data grid and entries here)

$grid->addAction('dry-herbs', t('Dry herbs...'))
    ->setCallback('dry_herbs');

function dry_herbs(UI_DataGrid_Action $action) : void
{
    // Create the redirect message with the URL to redirect to,
    // and the text to use for the different scenarios.
    $message = $action->createRedirectMessage('https://mistralys.eu')
        ->none(t('No herbs were selected that need to be dried.'))
        ->single(t('The herb %1$s has been dried successfully at %2$s.', '$label', '$time'))
        ->multiple(t('%1$s herbs have been dried successfully at %2$s.', '$amount', '$time'));

    $herbs = HerbsCollection::getInstance();

    // Process items
    foreach ($action->getSelectedValues() as $recordID)
    {
        $herb = $herbs->getByID($recordID);

        // Let's say only locally sourced herbs need to be dried.
        if ($herb->isLocal()) {
            $herb->dryAndStore();

            $message->addAffected($herb->getName());
        }
    }

    $message->redirect();
}
