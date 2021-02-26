<?php

class UI_Page_Section_Content_Separator extends UI_Page_Section_Content
{
    public function getDefaultOptions() : array
    {
        return array();
    }
    
    protected function _render()
    {
        return $this->page->renderTemplate(
            'sidebar.section.separator'
        );
    }
}