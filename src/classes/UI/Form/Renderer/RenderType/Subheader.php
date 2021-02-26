<?php

declare(strict_types=1);

class UI_Form_Renderer_RenderType_Subheader extends UI_Form_Renderer_RenderType
{
    public function includeInRegistry() : bool
    {
        return true;
    }
    
    protected function _render() : string
    {
        return $this->renderMarkupHeader(
            $this->renderDef->getElementLabel(), 
            4
        );
    }
}
