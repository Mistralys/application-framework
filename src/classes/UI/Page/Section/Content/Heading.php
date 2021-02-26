<?php

class UI_Page_Section_Content_Heading extends UI_Page_Section_Content
{
    public function getDefaultOptions() : array
    {
        return array(
            'title' => null,
        );
    }
    
    protected function _render()
    {
        return $this->page->renderTemplate(
            'sidebar.section.heading', 
            array(
                'title' => $this->getTitle()
            )
        );
    }
    
    public function getTitle()
    {
        return $this->getOption('title');
    }
}