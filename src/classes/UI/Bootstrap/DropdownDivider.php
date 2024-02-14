<?php

declare(strict_types=1);

class UI_Bootstrap_DropdownDivider
    extends UI_Bootstrap
    implements UI_Interfaces_Bootstrap_DropdownItem
{
    protected function _render() : string
    {
        return '<li class="dropdown-item divider"></li>';
	}
}
