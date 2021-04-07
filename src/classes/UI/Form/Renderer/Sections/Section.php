<?php

declare(strict_types=1);

class UI_Form_Renderer_Sections_Section
{
   /**
    * @var UI_Form_Renderer_ElementFilter_RenderDef
    */
    private $renderDef;
    
   /**
    * @var UI_Form_Renderer_ElementFilter_RenderDef[]
    */
    private $elements = array();
    
   /**
    * @var UI_Page_Section
    */
    private $section;
    
    public function __construct(UI_Form_Renderer_ElementFilter_RenderDef $renderDef)
    {
        $this->renderDef = $renderDef;
        $this->section = $renderDef->getRenderer()->getForm()->getUI()->getPage()->createSubsection();

        $this->initSection();
    }
    
    public function getID() : string
    {
        return $this->section->getID();
    }
    
    public function getJSExpand() : string
    {
        return $this->section->getJSExpand();
    }
    
    public function getJSCollapse() : string
    {
        return $this->section->getJSCollapse();
    }
    
    public function isCollapsed() : bool
    {
        return $this->renderDef->getAttribute('data-collapsed') !== 'no';
    }
    
    public function appendContent(string $content) : void
    {
        $this->section->appendContent($content);
    }
    
    public function registerElement(UI_Form_Renderer_ElementFilter_RenderDef $renderDef) : void
    {
        // do not add sections
        if(!$renderDef->isSection())
        {
            $this->elements[] = $renderDef;
        }
    }
    
    public function makeLast() : void
    {
        $this->section->addClass('last');
    }
    
    public function makeStandalone() : void
    {
        $this->section->expand();
        $this->section->makeStatic();
    }
    
    public function hasErrors() : bool
    {
        foreach($this->elements as $element)
        {
            if($element->hasError())
            {
                return true;
            }
        }
        
        return false;
    }
    
    public function isRequired() : bool
    {
        foreach($this->elements as $element)
        {
            if($element->isRequired())
            {
                return true;
            }
        }
        
        return false;
    }
    
    public function getFirstInvalid() : ?UI_Form_Renderer_ElementFilter_RenderDef
    {
        foreach($this->elements as $element)
        {
            if($element->hasError())
            {
                return $element;
            }
        }
        
        return null;
    }
    
    public function render() : string
    {
        if($this->hasErrors()) 
        {
            $this->section->expand();
            $this->section->addClass('form-section-error');
        }
        
        // Add the required icon in the section title so the information
        // is readily available if the user collapses the section
        if($this->isRequired()) 
        {
            $this->section->addClass('form-section-required');

            // The required icons only make sense if the section
            // is collapsible: if they are collapsed, it's easy to
            // identify those that have required fields. Otherwise,
            // nothing is hidden, and the visual aid is not needed.
            if($this->section->isCollapsible()) {
                $this->section->setTitle(
                    $this->section->getTitle() . ' ' .
                    UI::icon()->required()
                        ->addClass('icon-form-required')
                        ->makeDangerous()
                        ->setTooltip(t('Contains required form fields.'))
                        ->cursorHelp()
                );
            }
        }
        
        $anchor = strval($this->renderDef->getAttribute('data-anchor'));

        if(!empty($anchor))
        {
            $this->section->setAnchor($anchor);
        }
        
        return $this->section->render();
    }
    
    public function getLabel() : string
    {
        return $this->renderDef->getElementLabel();
    }
    
    private function initSection() : void
    {
        $this->section->setGroup($this->renderDef->getRenderer()->getID());
        $this->section->setTitle($this->getLabel());
        $this->section->makeCollapsible($this->isCollapsed());
        $this->section->setAbstract($this->renderDef->getAttribute('data-abstract'));
    }
}
