<?php

declare(strict_types=1);

class UI_Form_Renderer_RenderType_Static extends UI_Form_Renderer_RenderType
{
    private $template = 
'<div class="control-group form-container %1$s">
    <label class="control-label" for="%2$s">
        %3$s
    </label>
    <div class="controls">
        <div class="custom">
              %4$s
              %5$s      
        </div>
    </div>
</div>';

    public function includeInRegistry() : bool
    {
        return true;
    }
    
    protected function _render() : string
    {
        $classes = array();
        if($this->renderDef->isLast())
        {
            $classes[] = 'last';
        }
        
        return sprintf(
            $this->template,
            implode(' ', $classes),
            $this->renderDef->getElementID(),
            $this->renderDef->getElementLabel(),
            $this->renderDef->getAttribute('static_content'),
            $this->renderMarkupComments()
        );
    }
}
