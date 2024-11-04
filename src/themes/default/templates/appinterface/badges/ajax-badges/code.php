<?php

declare(strict_types=1);

use TestDriver\AjaxMethods\AjaxRenderDropdownMenu;

echo UI::getInstance()
    ->createBadgeDropdown(t('AJAX Badge Dropdown'))
    ->makeAJAX(AjaxRenderDropdownMenu::METHOD_NAME);
