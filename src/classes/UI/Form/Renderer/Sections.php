<?php

declare(strict_types=1);

class UI_Form_Renderer_Sections
{
   /**
    * @var UI_Form_Renderer
    */
    private $renderer;
    
   /**
    * @var UI_Form_Renderer_Sections_Section[]
    */
    private $sections = array();
    
   /**
    * @var UI
    */
    private $ui;
    
    public function __construct(UI_Form_Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->ui = $renderer->getUI();
    }
    
    public function create(UI_Form_Renderer_ElementFilter_RenderDef $renderDef) : UI_Form_Renderer_Sections_Section
    {
        $section = new UI_Form_Renderer_Sections_Section($renderDef);
        
        $this->sections[] = $section;
        
        return $section;
    }
    
   /**
    * @return UI_Form_Renderer_Sections_Section[]
    */
    public function getAll() : array
    {
        return $this->sections;
    }
    
    public function render() : string
    {
        if(empty($this->sections))
        {
            return '';
        }
        
        $this->initLast();
        $this->initSingleSection();
        $this->initDefaultElement();
        
        return
        $this->renderCollapseControls().
        $this->renderSections();
    }
    
    private function renderSections() : string
    {
        $html = '';
        
        foreach($this->sections as $section) 
        {
            $html .= $section->render();
        }
        
        return $html;
    }
    
   /**
    * Automatically puts the focus on the first erroneous
    * element in the sections.
    */
    private function initDefaultElement() : void
    {
        $form = $this->renderer->getForm();
        
        if(!$form->isSubmitted())
        {
            return;
        }
        
        foreach($this->sections as $section)
        {
            $el = $section->getFirstInvalid();
            
            if($el)
            {
                $form->setDefaultElement($el->getElement());
                return;
            }
        }
    }
    
    private function renderCollapseControls() : string
    {
        return (string)UI_Page_Section::createGroupControls()
            ->addClass('form-sections-toolbar')
            ->makeMini()
            ->setTooltips(
                t('Expands all sections of the form.'),
                t('Collapses all sections of the form.')
            );
    }
    
   /**
    * Tells the last section in the list that
    * it is the last one.
    */
    private function initLast() : void
    {
        $last = end($this->sections);
        
        $last->makeLast();
        
        reset($this->sections);
    }

   /**
    * Just one section in the page: expand it by default,
    * and remove the collapsing controls.
    */
    private function initSingleSection() : void
    {
        if(count($this->sections) === 1)
        {
            $this->sections[0]->makeStandalone();
        }
    }
}
