<?php

declare(strict_types=1);

class UI_Form_Renderer_RenderType_Group extends UI_Form_Renderer_RenderType
{
    private $template =
'<a id="%1$s-anchor"></a>
<label class="control-label" for="%1$s">
    %2$s
</label>
<div class="controls">
    %3$s
    %4$s    
</div>';

    public function includeInRegistry(): bool
    {
        return false;
    }
    
    protected function _render() : string
    {
        return sprintf(
            $this->template,
            $this->renderDef->getElementID(),
            $this->renderDef->getElementLabel(),
            $this->renderSubElements(),
            $this->renderMarkupError()
        );      
    }
}
