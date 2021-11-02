<?php

use AppUtils\Traits_Classable;

abstract class UI_Bootstrap extends UI_Renderable implements UI_Interfaces_Bootstrap, UI_Interfaces_Conditional
{
    use Traits_Classable;
    use UI_Traits_Conditional;
    
   /**
    * @var string
    */    
    protected $name;
    
   /**
    * @var UI_Bootstrap[]
    */
    protected $children = array();

    public function __construct(UI $ui)
    {
        parent::__construct($ui->getPage());

        $this->init();
    }
    
    protected function init()
    {
        $id = nextJSID();
        $this->setAttribute('id', 'bt'.$id);
        $this->name = 'BS'.$id;
    }

   /**
    * Sets the element's name, which can be used to retrieve it when used in collections.
    * @param string $name
    * @return UI_Bootstrap
    */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
   /**
    * Helper method to check if this element has the specified name.
    * @param string $name
    * @return boolean
    */
    public function isNamed($name)
    {
        if($this->name === $name) {
            return true;
        }
        
        return false;
    }

    public function getID()
    {
        return $this->getAttribute('id');
    }
    
    public function setID($id)
    {
        return $this->setAttribute('id', $id);
    }
    
    protected $attributes = array();
    
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
        return $this;
    }
    
    public function getAttribute($name, $default=null)
    {
        if(isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }
        
        return $default;
    }
    
    public function hasAttribute($name)
    {
        return isset($this->attributes[$name]) && $this->attributes[$name] != '';
    }
    
    public function renderAttributes()
    {
        $atts = $this->attributes;
        
        if(!empty($this->classes)) {
            $atts['class'] = $this->classesToString();
        }
        
        if(!empty($this->styles)) {
            $atts['style'] = AppUtils\ConvertHelper::array2styleString($this->styles);
        }
        
        return compileAttributes($atts);
    }
    
    protected $styles = array();
    
    public function setStyle($name, $value)
    {
        $this->styles[$name] = $value;
    }
    
   /**
    * Appends a child content to the bootstrap element.
    * Note that the element has to support this, otherwise
    * it will have no effect.
    * 
    * @param UI_Bootstrap $child
    * @throws Application_Exception
    * @return UI_Bootstrap
    */
    public function appendChild(UI_Bootstrap $child)
    {
        $this->requireNotHasChild($child);

        $this->children[] = $child;
        
        $child->setParent($this);
        
        return $this;
    }

    /**
     * @param UI_Bootstrap $child
     * @return $this
     * @throws Application_Exception
     */
    public function prependChild(UI_Bootstrap $child)
    {
        $this->requireNotHasChild($child);

        array_unshift($this->children, $child);

        $child->setParent($this);

        return $this;
    }
    
   /**
    * Sets the parent element of a child element.
    * 
    * @param UI_Bootstrap $parent
    * @return UI_Bootstrap
    */
    public function setParent(UI_Bootstrap $parent)
    {
        $this->requireParentHasChild($parent);

        $this->parent = $parent;
        return $this;
    }
    
   /**
    * @var UI_Bootstrap
    */
    protected $parent = null;
    
   /**
    * Retrieves the element's parent element, if any.
    * @return UI_Bootstrap
    */
    public function getParent()
    {
        return $this->parent;
    }

   /**
    * Creates a child content instance. Note that this does not
    * add the child: it is orphaned until it is actually added
    * to a parent element.
    * 
    * @param string $type
    * @return UI_Interfaces_Bootstrap
    */
    public function createChild(string $type) : UI_Interfaces_Bootstrap
    {
        return $this->ui->createBootstrap($type);
    }

   /**
    * Checks whether the item has a child with the specified name.
    * @param string $name
    * @return boolean
    */
    public function hasChild(string $name) : bool
    {
        foreach($this->children as $child) {
            if($child->getName() == $name) {
                return true;
            }
        }
        
        return false;
    }
    
   /**
    * @return UI_Bootstrap[]
    */
    public function getChildren()
    {
        return $this->children;
    }
    
    public function hasChildren()
    {
        return !empty($this->children);
    }

    public function render(): string
    {
        if($this->isValid())
        {
            return parent::render();
        }

        return '';
    }

    /**
     * @param UI_Bootstrap $child
     * @throws Application_Exception
     */
    private function requireNotHasChild(UI_Bootstrap $child) : void
    {
        if (!$this->hasChild($child->getName()))
        {
            return;
        }

        throw new Application_Exception(
            'A child with the same name already exists',
            sprintf(
                'A child of type [%s] cannot be added: a child with the name [%s] already exists in the parent of type [%s]. Names have to be unique within a parent.',
                get_class($child),
                $child->getName(),
                get_class($this)
            ),
            self::ERROR_CHILD_NAME_ALREADY_EXISTS
        );
    }

    /**
     * @param UI_Bootstrap $parent
     * @throws Application_Exception
     */
    private function requireParentHasChild(UI_Bootstrap $parent) : void
    {
        if ($parent->hasChild($this->getName()))
        {
            return;
        }

        throw new Application_Exception(
            'Child element has no such parent',
            sprintf(
                'Cannot add element [%s] of type [%s] as parent of element [%s] of type [%s]: It is not a child of the parent element.',
                $parent->getName(),
                get_class($parent),
                $this->getName(),
                get_class($this)
            ),
            self::ERROR_NOT_A_CHILD_ELEMENT_OF_PARENT
        );
    }
}
