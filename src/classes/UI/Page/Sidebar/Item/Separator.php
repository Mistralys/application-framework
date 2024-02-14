<?php

declare(strict_types=1);

class UI_Page_Sidebar_Item_Separator extends UI_Page_Sidebar_Item
{
    protected function _render() : string
    {
        return $this->createTemplate('sidebar.separator')->render();
    }
}
