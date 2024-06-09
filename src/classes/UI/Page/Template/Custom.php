<?php

declare(strict_types=1);

abstract class UI_Page_Template_Custom extends UI_Page_Template
{
    protected function _render() : string
    {
        ob_start();

        try
        {
            $this->preRender();

            $this->generateOutput();
            $output = ob_get_clean();

            return $this->filterOutput($output);
        }
        catch(Exception $e)
        {
            ob_end_flush();
            throw $e;
        }
    }
    
    abstract protected function preRender() : void;
    
    abstract protected function generateOutput() : void;
    
    protected function filterOutput(string $output) : string
    {
        return $output;
    }
}
