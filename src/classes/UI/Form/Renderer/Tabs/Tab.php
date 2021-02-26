<?php

declare(strict_types=1);

class UI_Form_Renderer_Tabs_Tab
{
   /**
    * @var UI_Form_Renderer_ElementFilter_RenderDef
    */
    private $renderDef;
    
   /**
    * @var UI_Form_Renderer_ElementFilter
    */
    private $elements;
    
    public function __construct(UI_Form_Renderer_ElementFilter_RenderDef $renderDef, UI_Form_Renderer_ElementFilter $elements)
    {
        $this->renderDef = $renderDef;
        $this->elements = $elements;
    }
    
    public function getLabel() : string
    {
        return $this->renderDef->getElementLabel();
    }
    
    public function getID() : string
    {
        return $this->renderDef->getElementID();
    }
    
    public function getDescription() : string
    {
        return $this->renderDef->resolveComments();
    }
    
    public function renderContent() : string
    {
        return $this->renderDef->getRenderer()->renderElements($this->elements);
    }
    
    public function hasErrors() : bool
    {
        return $this->elements->hasErrors();
    }
    
    public function renderLabel() : string
    {
        $label = sb();
        
        // add the small error icon to any tab that has elements
        // with error messages.
        if($this->hasErrors()) 
        {
            $label->icon(
                UI::icon()
                ->warning()
                ->makeDangerous()
                ->setTooltip(t('Form fields in this tab require your attention.'))
            );
        }
        
        $label->add($this->getLabel());
        
        return (string)$label;
    }
    
    public function renderAbstract() : string
    {
        $descr = $this->getDescription();
        
        if(empty($descr))
        {
            return '';
        }
        
        return sprintf(
            '<p class="abstract">%s</p>',
            $descr
        );
    }
}
