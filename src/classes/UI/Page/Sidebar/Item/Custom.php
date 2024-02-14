<?php

class UI_Page_Sidebar_Item_Custom extends UI_Page_Sidebar_LockableItem
{
    protected $content = '';

    public function __construct(UI_Page_Sidebar $sidebar, $content)
    {
        parent::__construct($sidebar);
        
        $this->content = $content;
    }

    protected function _render() : string
    {
        $tpl = $this->createTemplate('sidebar.custom');
        
        $tpl->setVar('locked', $this->locked);
        $tpl->setVar('content', $this->content);

        return $tpl->render();
    }
}
