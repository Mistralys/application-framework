<?php

declare(strict_types=1);

class UI_Form_Renderer_ElementFilter
{
    const ERROR_UNKNOWN_ELEMENT = 64501;
    
   /**
    * @var array
    */
    private $elements;
    
   /**
    * @var integer
    */
    private $level = 0; 
    
   /**
    * @var string[]
    */
    private $regularElementIDs = array();
    
   /**
    * @var UI_Form
    */
    private $form;
    
   /**
    * @var UI_Form_Renderer_ElementFilter_RenderDef[]
    */
    private $filtered = array();
    
   /**
    * @var UI_Form_Renderer
    */
    private $renderer;
    
    public function __construct(UI_Form_Renderer $renderer, array $elements, int $level)
    {
        $this->elements = $elements;
        $this->level = $level;
        $this->form = $renderer->getForm();
        $this->renderer = $renderer;
        
        $total = count($this->elements);
        
        for($i=0; $i<$total; $i++)
        {
            $this->filterElement($this->elements[$i]);
        }
        
        if(!empty($this->regularElementIDs)) 
        {
            $last = array_pop($this->regularElementIDs);
            $this->getByID($last)->makeLast();
        }
    }
    
    public function getByID(string $id) : UI_Form_Renderer_ElementFilter_RenderDef
    {
        foreach($this->filtered as $element)
        {
            if($element->getElementID() === $id)
            {
                return $element;
            }
        }
        
        throw new Application_Exception(
            'Cannot find element by ID',
            sprintf(
                'No element found with the ID [%s]',
                $id
            ),
            self::ERROR_UNKNOWN_ELEMENT
        );
    }
    
    public function hasErrors() : bool
    {
        foreach($this->filtered as $element)
        {
            if($element->hasError())
            {
                return true;
            }
        }
        
        return false;
    }
    
    private function filterElement(array $itemDef) : void
    {
        $id = $itemDef['id'];
        
        $element = $this->form->getElementByID($id);
        if(!$element) 
        {
            return;
        }
        
        // file elements are completely ignored in readonly mode
        if ($this->form->isReadonly() && $element->getType() === 'file') 
        {
            return;
        }
        
        if($this->form->isReadonly() && $element->getAttribute('data-hidden-when-frozen') === 'yes') 
        {
            return;
        }
        
        $renderDef = new UI_Form_Renderer_ElementFilter_RenderDef(
            $this->renderer,
            $itemDef,
            $element,
            $this->level,
            $this->renderer->getActiveSection()
        );
        
        if($renderDef->isRegularElement()) 
        {
            $this->regularElementIDs[] = $id;
        }
        
        $this->filtered[] = $renderDef;
    }
    
   /**
    * @return string[]
    */
    public function getRegularElementIDs() : array
    {
        return $this->regularElementIDs;
    }
    
   /**
    * 
    * @return UI_Form_Renderer_ElementFilter_RenderDef[]
    */
    public function getFiltered() : array
    {
        return $this->filtered;
    }
}
