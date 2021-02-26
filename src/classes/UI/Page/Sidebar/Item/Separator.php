<?php

class UI_Page_Sidebar_Item_Separator extends UI_Page_Sidebar_Item
{
    protected function _render()
    {
        $tpl = $this->createTemplate('sidebar.separator');

        return $tpl->render();
    }
}
