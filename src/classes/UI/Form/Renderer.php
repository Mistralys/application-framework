<?php

declare(strict_types=1);

class UI_Form_Renderer extends UI_Renderable
{
    protected UI_Form $wrapper;
    protected HTML_QuickForm2 $form;

   /**
    * @var array<string,mixed>
    */
    protected array $formDef;

   /**
    * @var UI_Form_Renderer_RenderType_Button[]
    */
    protected array $submits = array();

    protected string $id;
    protected string $layout = 'horizontal';
    private UI_Form_Renderer_Tabs $tabs;
    private UI_Form_Renderer_Sections $sections;
    private ?UI_Form_Renderer_Sections_Section $activeSection = null;
    private UI_Form_Renderer_Registry $registry;
    private UI_Form_Renderer_ElementFilter $elements;
    private bool $rendered = false;
    
    public function __construct(UI_Form $form, array $elementArray, string $layout='horizontal')
    {
        parent::__construct($form->getUI()->getPage());
        
        $this->id = nextJSID();
        $this->wrapper = $form;
        $this->form = $this->wrapper->getForm();
        $this->formDef = $elementArray;
        $this->layout = $layout;
        $this->sections = new UI_Form_Renderer_Sections($this);
        $this->tabs = new UI_Form_Renderer_Tabs($this);
        $this->registry = new UI_Form_Renderer_Registry($this);
        $this->elements = $this->filterElements($this->formDef['elements']);
    }
    
   /**
    * Unique ID of the rendered form within the request.
    * 
    * @return string
    */
    public function getID() : string
    {
        return $this->id;
    }
    
    public function getForm() : UI_Form
    {
        return $this->wrapper;
    }

    public function setRegistryEnabled(bool $enabled=true) : void
    {
        $this->registry->setEnabled($enabled);
        
        if($this->rendered)
        {
            $this->registry->injectJS();
        }
    }
    
    public function getRegistry() : UI_Form_Renderer_Registry
    {
        return $this->registry;
    }
    
    protected function _render() : string
    {
        if($this->rendered)
        {
            return '';
        }
        
        $this->registry->injectJS();
        
        $parts = array(
            'body' => $this->renderBody(),
            'tabs' => $this->tabs->render(),
            'hiddens' => $this->renderHidden(),
            'controls' => $this->renderControls()
        );
        
        $html =
        '<form ' . $this->formDef['attributes'] . '>' . PHP_EOL .
            $parts['hiddens'] .
            $parts['tabs'] .
            $parts['body'] .
            $parts['controls'] .
        '</form>';

        $this->rendered = true;
            
        return $html;
    }

    protected function renderControls() : string
    {
        if(empty($this->submits))
        {
            return UI_Form::renderDummySubmit();
        }

        $html = '';
        
        foreach($this->submits as $submit) 
        {
            $html .= $submit->getHTML();
        }
        
        return sprintf(
            '<div class="form-actions">%s</div>',
            $html
        );
    }

    protected function renderBody() : string
    {
        return 
        $this->renderElements($this->elements).
        $this->sections->render();
    }

    protected function renderHidden() : string
    {
        $html =
            "\t" . '<div class="hiddens">' . PHP_EOL;
        foreach ($this->formDef['hidden'] as $hiddenElement) {
            $html .= "\t\t" . $hiddenElement . PHP_EOL;
        }
        $html .=
            "\t" . '</div>' . PHP_EOL;

        return $html;
    }

   /**
    * Registers a new form section: any elements rendered after
    * this method call will be appended to that section.
    * 
    * NOTE: The sections are created in the "Header" render type.
    * 
    * @param UI_Form_Renderer_Sections_Section $section
    * 
    * @see UI_Form_Renderer_RenderType_Header
    */
    public function registerSection(UI_Form_Renderer_Sections_Section $section) : void
    {
        $this->activeSection = $section;
    }
    
    public function getActiveSection() : ?UI_Form_Renderer_Sections_Section
    {
        return $this->activeSection;
    }
    
    public function getSections() : UI_Form_Renderer_Sections
    {
        return $this->sections;
    }
    
    public function getTabs() : UI_Form_Renderer_Tabs
    {
        return $this->tabs;
    }

    /**
     * @var int[]
     */
    protected array $headers = array();

   /**
    * Renders a form header of the specified level.
    * 
    * @param string $label
    * @param integer $level
    * @return string
    */
    public function renderHeader(string $label, int $level) : string
    {
        $anchorName = 'heading'.nextJSID();

        $html = '<a id="' . $anchorName . '"></a>';
        
        $classes = array('form-header');
        if(!in_array($level, $this->headers)) {
            $this->headers[] = $level;
            $classes[] = 'form-header-first';
        }
        
        $html .=
        '<h' . $level . ' class="'.implode(' ', $classes).'">' .
            $label .
        '</h' . $level . '>';
        
        return $html;
    }

   /**
    * Renders the elements from a filtered elements collection.
    * 
    * @param UI_Form_Renderer_ElementFilter $filter
    * @return string
    */
    public function renderElements(UI_Form_Renderer_ElementFilter $filter) : string
    {
        $items = $filter->getFiltered();
        
        $html = '';
        
        foreach($items as $item) 
        {
            $html .= $this->renderElementDef($item);
        }
        
        return $html;
    }

   /**
    * Filters the HTML_QuickForm rendered elements collection,
    * to keep only the relevant elements and to convert them
    * to the renderer element definition instances.
    * 
    * @param array $elements
    * @param int $level
    * @return UI_Form_Renderer_ElementFilter
    */
    public function filterElements(array $elements, int $level=0) : UI_Form_Renderer_ElementFilter
    {
        return new UI_Form_Renderer_ElementFilter($this, $elements, $level);
    }
    
   /**
    * Renders a single element to HTML, according to its type.
    * This is dispatched to the type classes.
    * 
    * @param UI_Form_Renderer_ElementFilter_RenderDef $def
    * @return string
    * 
    * @see UI_Form_Renderer_RenderType
    */
    private function renderElementDef(UI_Form_Renderer_ElementFilter_RenderDef $def) : string
    {
        return $def->getTypeRenderer()->render();
    }
    
   /**
    * Registers a "submit" button in the form.
    * 
    * @param UI_Form_Renderer_RenderType_Button $button
    */
    public function registerButton(UI_Form_Renderer_RenderType_Button $button) : void
    {
        $this->submits[] = $button;
    }
    
    public function getRootElements() : UI_Form_Renderer_ElementFilter
    {
        return $this->elements;
    }
}
