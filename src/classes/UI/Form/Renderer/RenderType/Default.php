<?php
/**
 * File containing the {@link UI_Form_Renderer_RenderType_Default} class.
 *
 * @package Forms
 * @subpackage Renderer
 * @see UI_Form_Renderer_RenderType_Default
 */

declare(strict_types=1);

use AppUtils\Interfaces\ClassableInterface;
use AppUtils\Traits\ClassableTrait;

/**
 * Renderer for all non-special form elements: this handles all regular
 * HTML QuickForm elements (like text input, select, etc...).
 * 
 * These elements can be customized using callbacks, via the 
 * {@see UI_Form_Renderer_Element} class. To add a render callback for
 * an element, see the {@see UI_Form::addRenderCallback()} method.
 * 
 * @package Forms
 * @subpackage Renderer
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * 
 * @see UI_Form_Renderer_Element
 * @see UI_Form::addRenderCallback()
 */
class UI_Form_Renderer_RenderType_Default extends UI_Form_Renderer_RenderType implements ClassableInterface
{
    use ClassableTrait;
    
    public function includeInRegistry(): bool
    {
        return true;
    }
    
    protected function _render() : string
    {
        $renderer = new UI_Form_Renderer_Element($this);
        
        $this->initClasses();
        
        $callbacks = $this->renderDef->getElement()->getRuntimeProperty('render-callbacks', array());
        foreach($callbacks as $callback)
        {
            $callback($renderer);
        }
        
        return sprintf(
            '<div class="control-group form-container %s" id="%s_form_container">%s</div>',
            $this->classesToString(),
            $this->renderDef->getElementID(),
            $this->renderElement($renderer)
        );
    }

    protected function renderElement(UI_Form_Renderer_Element $renderer) : string
    {
        return $renderer->render();
    }
    
    private function initClasses() : void
    {
        if($this->renderDef->hasError())
        {
            $this->addClass('error');
        }
        
        if($this->renderDef->isRegularElement())
        {
            $this->addClass('element-type-'.$this->renderDef->getElementTypeID());
        }
        
        if($this->renderDef->isStandalone()) 
        {
            $this->addClass('standalone');
        }
    }
}
