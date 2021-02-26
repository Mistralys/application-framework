<?php

declare(strict_types=1);

class UI_Form_Renderer_RenderType_Paragraph extends UI_Form_Renderer_RenderType
{
    private $template =
'<p>
    %1$s
</p>';
    
    public function includeInRegistry() : bool
    {
        return false;
    }
    
    protected function _render() : string
    {
        return sprintf(
            $this->template,
            $this->renderDef->getElementLabel()
        );
    }
}
