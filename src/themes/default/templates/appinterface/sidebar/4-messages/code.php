<?php

declare(strict_types=1);

$sidebar = new UI_Page_Sidebar('sidebar-example');

$sidebar->addInfoMessage('Informational message');

$sidebar->addSuccessMessage('Success message');

$sidebar->addWarningMessage('Warning message');

$sidebar->addErrorMessage('Error message');

$sidebar->addSeparator();

$sidebar->addInfoMessage('With icon')
    ->setCustomIcon(UI::icon()->convert());

$sidebar->addInfoMessage('Compact message')
    ->makeSlimLayout();

$sidebar->addInfoMessage('Dismissible message')
    ->makeDismissable();

echo $sidebar;
