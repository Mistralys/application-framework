<?php

class UI_Page_Section_Content_HTML extends UI_Page_Section_Content
{
    public function getDefaultOptions() : array
    {
        return array(
            'html' => null,
        );
    }
    
    protected function _render() : string
    {
        return $this->getHTML();
    }
    
    public function getHTML()
    {
        return $this->getOption('html');
    }
}