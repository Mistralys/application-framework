<?php

declare(strict_types=1);

/* @var string $activeURL */

$sidebar = new UI_Page_Sidebar('sidebar-example');

$sidebar->addButton('confirm', 'Click to confirm...')
    ->makeWarning()
    ->makeConfirm('This dialog is shown to confirm an action.')
    ->link($activeURL);

$btn = $sidebar->addButton('confirm-danger', 'Click to confirm...')
    ->makeDangerous()
    ->link($activeURL);

$btn->getConfirmMessage()
    ->setLoaderText('UI loader shown after confirmation')
    ->setMessage(t('For critical actions, user input can be requested.'))
    ->makeWithInput();

echo $sidebar;
