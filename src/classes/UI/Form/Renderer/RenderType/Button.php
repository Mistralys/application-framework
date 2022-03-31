<?php
/**
 * File containing the class {@see UI_Form_Renderer_RenderType_Button}.
 *
 * @package User Interface
 * @subpackage Forms
 * @see UI_Form_Renderer_RenderType_Button
 */

declare(strict_types=1);

/**
 * A button is detected by checking the element's `rel`
 * attribute. This is set automatically by form element
 * type in the render def.
 *
 * @package User Interface
 * @subpackage Forms
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see UI_Form::REL_BUTTON
 * @see UI_Form_Renderer_ElementFilter_RenderDef::$relByType
 * @see UI_Form_Renderer_ElementFilter_RenderDef::getTypeClass()
 */
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
