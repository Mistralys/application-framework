<?php

declare(strict_types=1);

use TestDriver\AjaxMethods\AjaxRenderDropdownMenu;

echo UI::getInstance()
    ->createButtonDropdown(t('AJAX Button Dropdown'))
    ->makeAJAX(AjaxRenderDropdownMenu::METHOD_NAME);

echo '<hr>';

echo UI::getInstance()
    ->createBadgeDropdown(t('AJAX Badge Dropdown'))
    ->makeAJAX(AjaxRenderDropdownMenu::METHOD_NAME);
