<?php

declare(strict_types=1);

abstract class UI_Form_Renderer_RenderType
{
   /**
    * @var UI_Form_Renderer_ElementFilter_RenderDef
    */
    protected $renderDef;
    
   /**
    * @var UI_Form_Renderer_Sections_Section|NULL
    */
    private ?UI_Form_Renderer_Sections_Section $section;
    
   /**
    * @var UI_Form_Renderer
    */
    protected $renderer;

   /**
    * @var UI_Form
    */
    protected $form;
    
   /**
    * @var UI
    */
    protected $ui;
    
   /**
    * @var UI_Form_Renderer_ElementFilter
    */
    protected $subElements;
    
    public function __construct(UI_Form_Renderer_ElementFilter_RenderDef $renderDef, ?UI_Form_Renderer_Sections_Section $section)
    {
        $this->renderDef = $renderDef;
        $this->renderer = $renderDef->getRenderer();
        $this->ui = $this->renderer->getUI();
        $this->form = $this->renderer->getForm();
        $this->section = $section;
        
        $this->subElements = $this->renderDef->getRenderer()->filterElements(
            $this->renderDef->getSubDefs(),
            $this->renderDef->getLevel() + 1
        );

        $this->init();
    }

    protected function init() : void
    {

    }
    
    public function getRenderDef() : UI_Form_Renderer_ElementFilter_RenderDef
    {
        return $this->renderDef;
    }
    
    public function getRenderer() : UI_Form_Renderer
    {
        return $this->renderer;
    }
    
    public function render() : string
    {
        $html = $this->_render();
        
        // if a section is present, the generated html
        // has to be appended there instead.
        if(isset($this->section))
        {
            $this->section->appendContent($html);
            $this->section->registerElement($this->renderDef);
            
            return '';
        }
        
        return $html;
    }
    
    abstract public function includeInRegistry() : bool;
    
    abstract protected function _render() : string;

    public function renderMarkupError() : string
    {
        $hint = '';
        if($this->renderDef->hasError())
        {
            $hint = sprintf(
                '<span class="form-error-hint">%s</span>',
                $this->renderDef->getErrorMessage()
            );
        }
        
        return sprintf(
            '<span id="%1$s_form_error" class="form-error-message">%2$s</span>',
            $this->renderDef->getElementID(),
            $hint
        );
    }
    
    public function renderMarkupComments() : string
    {
        $comments = $this->renderDef->resolveComments();
        
        if(empty($comments)) 
        {
            return '';
        }
        
        return sprintf(
            '<span class="help-block">%s</span>',
            $comments
        );
    }
    
    public function getHTML() : string
    {
        return $this->renderDef->getElementHTML();
    }
    
    public function renderMarkupHeader(string $label, int $level) : string
    {
        return $this->renderDef->getRenderer()->renderHeader($label, $level);
    }
    
    public function getSubElements() : UI_Form_Renderer_ElementFilter
    {
        return $this->subElements;
    }
    
    protected function renderSubElements() : string
    {
        return $this->renderDef->getRenderer()->renderElements($this->getSubElements());
    }
}
