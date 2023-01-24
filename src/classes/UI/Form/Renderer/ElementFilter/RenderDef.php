<?php

declare(strict_types=1);

class UI_Form_Renderer_ElementFilter_RenderDef
{
    /**
    * @var array<string,mixed>
    */
    private $itemDef;
    
   /**
    * @var HTML_QuickForm2_Node
    */
    private $node;
    
   /**
    * @var boolean
    */
    private $isLast = false;
    
   /**
    * @var UI_Form_Renderer_RenderType|NULL
    */
    private ?UI_Form_Renderer_RenderType $typeRenderer = null;
    
   /**
    * @var UI_Form_Renderer_Sections_Section|NULL
    */
    private $section = null;
    
   /**
    * @var UI_Form_Renderer
    */
    private $renderer;
    
   /**
    * @var integer
    */
    private $level = 0;
    
   /**
    * Automatic rel attribute by form element type.
    * 
    * @var array<string,string>
    */
    private array $relByType;
    
   /**
    * 
    * @param array $itemDef
    * @param HTML_QuickForm2_Node $node Can be an element, or a container.
    */
    public function __construct(UI_Form_Renderer $renderer, array $itemDef, HTML_QuickForm2_Node $node, int $level, ?UI_Form_Renderer_Sections_Section $section=null)
    {
        $this->renderer = $renderer;
        $this->itemDef = $itemDef;
        $this->node = $node;
        $this->level = $level;
        $this->relByType = $this->getRelValues();
        
        if($this->isSection())
        {
            $section = $this->renderer->getSections()->create($this);
            $this->renderer->registerSection($section);
        }
        else
        {
            $this->section = $section;
        }
    }
    
   /**
    * The level at which this form element is being rendered,
    * zero-based, zero being the form itself. Elements in
    * groups and the like have accordingly higher levels.
    * 
    * @return int
    */
    public function getLevel() : int
    {
        return $this->level;
    }
    
    public function getRenderer() : UI_Form_Renderer
    {
        return $this->renderer;
    }
    
    public function hasError() : bool
    {
        $msg = $this->getErrorMessage();
        return !empty($msg);
    }
    
    public function isSection() : bool
    {
        return $this->getRel() === 'header';
    }
    
    public function getErrorMessage() : string
    {
        return strval($this->getItemProperty('error'));
    }
    
    public function getElementLabel() : string
    {
        return strval($this->getItemProperty('label'));
    }
    
    public function getElementTypeID() : string
    {
        if($this->isRegularElement())
        {
            return getClassTypeName($this->node);
        }
        
        return '';
    }
    
    public function getTypeClass() : string
    {
        $rel = ucfirst($this->getRel());
        
        $relClass = UI_Form_Renderer_RenderType::class.'_' . $rel;
        
        if(class_exists($relClass))
        {
            return $relClass;
        }
        
        return UI_Form_Renderer_RenderType_Default::class;
    }
    
    public function isRegularElement() : bool
    {
        return $this->node instanceof HTML_QuickForm2_Element;
    }
    
    public function isStandalone() : bool
    {
        return $this->getAttribute('standalone') === 'yes';
    }
    
    public function isContainerElement() : bool
    {
        return $this->node instanceof HTML_QuickForm2_Container;
    }
    
    public function getElementComment() : string
    {
        return strval($this->node->getComment());
    }
    
    public function getDataType() : string
    {
        return strval($this->node->getAttribute('data-type'));
    }

    public function getElementValue() : string
    {
        return strval($this->getItemProperty('value'));
    }
    
    public function isStructural() : bool
    {
        return $this->getAttribute('structural') === 'yes';
    }
    
    public function getElementID() : string
    {
        return $this->node->getId();
    }
    
    public function getElementHTML() : string
    {
        return (string)$this->getItemProperty('html');
    }
    
    public function getRel() : string
    {
        $rel = $this->getAttribute('rel');
        
        // Ensure that containers that do not have
        // a rel attribute set are handled as well
        if(empty($rel) && $this->getElement() instanceof HTML_QuickForm2_Container)
        {
            $rel = UI_Form::REL_LAYOUT_LESS_GROUP;
        }
        else
        {
            $type = $this->getElementTypeID();
            
            if(isset($this->relByType[$type]))
            {
                $rel = $this->relByType[$type];
            }
        }
        
        return $rel;
    }

    public function getRelValues() : array
    {
        return array(
            getClassTypeName(HTML_QuickForm2_Element_Button::class) => UI_Form::REL_BUTTON,
            getClassTypeName(HTML_QuickForm2_Element_UIButton::class) => UI_Form::REL_BUTTON
        );
    }
    
    public function isDummy() : bool
    {
        return strpos($this->getElementID(), 'dummy') === 0;
    }
    
    public function isLast() : bool
    {
        return $this->isLast;
    }
    
    public function makeLast() : void
    {
        $this->isLast = true;
    }
    
    public function setSection(UI_Form_Renderer_Sections_Section $section) : void
    {
        $this->section = $section;
    }
    
    public function getSection() : ?UI_Form_Renderer_Sections_Section
    {
        return $this->section;
    }
    
    public function getSectionID() : string
    {
        $section = $this->getSection();
        
        if($section)
        {
            return $section->getID();
        }
        
        return '';
    }
    
    public function includeInRegistry() : bool
    {
        return $this->getTypeRenderer()->includeInRegistry();
    }
    
    public function getTypeRenderer() : UI_Form_Renderer_RenderType
    {
        if(isset($this->typeRenderer))
        {
            return $this->typeRenderer;
        }
        
        $typeClass = $this->getTypeClass();
        
        $this->typeRenderer = ensureType(
            UI_Form_Renderer_RenderType::class,
            new $typeClass($this, $this->section)
        );
        
        return $this->typeRenderer;
    }
    
    public function getElement() : HTML_QuickForm2_Node
    {
        return $this->node;
    }
    
   /**
    * Retrieves a property of the element available only at runtime.
    * 
    * @param string $name
    * @return mixed
    */
    public function getRuntimeProperty(string $name)
    {
        return $this->node->getRuntimeProperty($name);
    }
    
    public function getAttribute(string $name) : string
    {
        return strval($this->node->getAttribute($name));
    }
    
    public function resolveComments() : string
    {
        $gen = new UI_Form_Renderer_CommentGenerator($this);
        return $gen->getComment();
    }
    
    public function getSubDefs() : array
    {
        if(isset($this->itemDef['elements']) && is_array($this->itemDef['elements']))
        {
            return $this->itemDef['elements'];
        }
        
        return array();
    }
    
    public function isRequired() : bool
    {
        return $this->getItemProperty('required') === true || $this->getAttribute('data-required') === 'true';
    }
    
    public function isFrozen() : bool
    {
        return $this->getItemProperty('frozen') === true;
    }
        
    private function getItemProperty(string $name)
    {
        if(isset($this->itemDef[$name]))
        {
            return $this->itemDef[$name];
        }
        
        return null;
    }
}
