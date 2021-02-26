<?php

declare(strict_types=1);

class UI_Form_Renderer_Registry
{
   /**
    * @var UI_Form_Renderer
    */
    private $renderer;
    
   /**
    * @var string
    */
    private $id;
    
   /**
    * @var boolean
    */
    private $enabled = false;
    
   /**
    * @var boolean
    */
    private $injected = false;
    
   /**
    * @var UI
    */
    private $ui;
    
    public function __construct(UI_Form_Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->ui = $renderer->getUI();
        $this->id = 'freg'.nextJSID();
    }
    
    public function setEnabled(bool $enabled) : void
    {
        $this->enabled = $enabled;
    }
    
    public function injectJS() : void
    {
        if($this->injected)
        {
            return;
        }
        
        $this->ui->addJavascriptHead(sprintf(
            "var %s = FormHelper.getRegistry('%s')",
            $this->id,
            $this->renderer->getForm()->getName()
        ));
        
        $this->injectSections();
        $this->injectElements($this->renderer->getRootElements());
        
        $this->injected = true;
    }
    
    private function injectSections() : void
    {
        $sections = $this->renderer->getSections()->getAll();
        
        foreach($sections as $section)
        {
            $this->ui->addJavascriptHeadStatement(
                sprintf('%s.AddSection', $this->id),
                $section->getID(),
                $section->getLabel()
            );
        }
    }
    
    private function injectElements(UI_Form_Renderer_ElementFilter $filter) : void
    {
        $elements = $filter->getFiltered();
        
        foreach($elements as $element)
        {
            if($element->includeInRegistry())
            {
                $this->ui->addJavascriptHeadStatement(
                    sprintf('%s.AddElement', $this->id),
                    $element->getElementID(),
                    $element->getElementLabel(),
                    $element->getElementTypeID(),
                    $element->getSectionID()
                );
            }
            
            $this->injectElements($element->getTypeRenderer()->getSubElements());
        }
    }
}
