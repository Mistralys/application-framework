<?php

declare(strict_types=1);

class UI_Form_Renderer_RenderType_Radio extends UI_Form_Renderer_RenderType
{
    private $template =
'<a id="%1$s-anchor"></a>
<label class="radio">
    %2$s
    %3$s
</label>';
    
    public function includeInRegistry() : bool
    {
        return true;
    }
    
    protected function _render() : string
    {
        return sprintf(
            $this->template,
            $this->renderDef->getElementID(),
            $this->renderDef->getElementHTML(),
            $this->renderDef->getElementLabel()
        );
    }
}
