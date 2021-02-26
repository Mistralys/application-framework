<?php

abstract class UI_Page_Template_Custom extends UI_Page_Template
{
    protected function _render() : string
    {
        $this->preRender();
        
        ob_start();
        $this->generateOutput();
        $output = ob_get_clean();
        
        return $this->filterOutput($output);
    }
    
    abstract protected function preRender() : void;
    
    abstract protected function generateOutput() : void;
    
    protected function filterOutput(string $output) : string
    {
        return $output;
    }
}
