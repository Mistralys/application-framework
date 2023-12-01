<?php
/**
 * @package UserInterface
 * @subpackage Forms
 * @see UI_Form_Renderer_RenderType_SelfRenderingGroup
 */

declare(strict_types=1);

/**
 * A group of elements that has a custom renderer:
 * The group's HTML is rendered by the group instance
 * itself. It is effectively treated as a default,
 * single element.
 *
 * @package UserInterface
 * @subpackage Forms
 */
class UI_Form_Renderer_RenderType_SelfRenderingGroup extends UI_Form_Renderer_RenderType_Default
{
    protected function renderElement(UI_Form_Renderer_Element $renderer): string
    {
        $renderer->setElementHTML((string)$this->renderDef->getElement());

        return parent::renderElement($renderer);
    }
}
