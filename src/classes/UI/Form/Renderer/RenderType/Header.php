<?php

declare(strict_types=1);

class UI_Form_Renderer_RenderType_Header extends UI_Form_Renderer_RenderType
{
    public function includeInRegistry() : bool
    {
        return false;
    }
    
    protected function _render() : string
    {
        return '';
    }
}
