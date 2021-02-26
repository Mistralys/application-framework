<?php

declare(strict_types=1);

class UI_Form_Renderer_RenderType_Button extends UI_Form_Renderer_RenderType
{
    public function includeInRegistry() : bool
    {
        return false;
    }
    
    protected function _render() : string
    {
        $this->renderer->registerButton($this);
        
        return '';
    }
}
