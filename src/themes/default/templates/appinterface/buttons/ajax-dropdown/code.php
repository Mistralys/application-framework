<?php

declare(strict_types=1);

use AppUtils\ArrayDataCollection;
use TestDriver\AjaxMethods\AjaxRenderDropdownMenu;

echo UI::getInstance()
    ->createButtonDropdown(t('AJAX Button Dropdown'))
    ->makeAJAX(AjaxRenderDropdownMenu::METHOD_NAME);

echo '<hr>';

echo UI::getInstance()
    ->createButtonDropdown(t('Empty menu handling'))
    ->makeAJAX(
        AjaxRenderDropdownMenu::METHOD_NAME,
        ArrayDataCollection::create()
            ->setKey(AjaxRenderDropdownMenu::REQUEST_PARAM_EMPTY_MENU, 'yes')
    );

echo '<hr>';

echo UI::getInstance()
    ->createButtonDropdown(t('Error handling'))
    ->makeAJAX(
        AjaxRenderDropdownMenu::METHOD_NAME,
        ArrayDataCollection::create()
            ->setKey(AjaxRenderDropdownMenu::REQUEST_PARAM_TRIGGER_ERROR, 'yes')
    );
