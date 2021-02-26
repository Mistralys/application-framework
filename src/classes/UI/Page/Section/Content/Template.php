<?php 

class UI_Page_Section_Content_Template extends UI_Page_Section_Content
{
    public function getDefaultOptions() : array
    {
        return array(
            'templateID' => null,
            'params' => array()
        );
    }
    
    protected function _render()
    {
        return $this->section->getPage()->renderTemplate(
            $this->getTemplateID(), 
            $this->getParams()
        );
    }
    
    public function getTemplateID()
    {
        return $this->getOption('templateID');
    }
    
    public function getParams()
    {
        return $this->getOption('params');
    }
}