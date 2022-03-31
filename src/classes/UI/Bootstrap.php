<?php

use AppUtils\ConvertHelper;
use AppUtils\Traits_Classable;

abstract class UI_Bootstrap extends UI_Renderable implements UI_Interfaces_Bootstrap, UI_Interfaces_Conditional
{
    use Traits_Classable;
    use UI_Traits_Conditional;
    
   /**
    * @var string
    */    
    protected string $name;
    
   /**
    * @var UI_Bootstrap[]
    */
    protected array $children = array();

    public function __construct(UI $ui)
    {
        parent::__construct($ui->getPage());

        $this->init();
    }
    
    protected function init() : void
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
    public function setName(string $name) : self
    {
        $this->name = $name;
        return $this;
    }
    
    public function getName() : string
    {
        return $this->name;
    }
    
   /**
    * Helper method to check if this element has the specified name.
    * @param string $name
    * @return boolean
    */
    public function isNamed(string $name) : bool
    {
        return $this->name === $name;
    }

    public function getID() : string
    {
        return (string)$this->getAttribute('id');
    }

    /**
     * @param string $id
     * @return $this
     */
    public function setID(string $id) : self
    {
        return $this->setAttribute('id', $id);
    }

    /**
     * @var array<string,string>
     */
    protected $attributes = array();

    /**
     * @param string $name
     * @param string|number $value
     * @return $this
     */
    public function setAttribute(string $name, $value) : self
    {
        $this->attributes[$name] = (string)$value;
        return $this;
    }
    
    public function getAttribute(string $name, $default=null)
    {
        if(isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }
        
        return $default;
    }
    
    public function hasAttribute(string $name) : bool
    {
        return isset($this->attributes[$name]) && $this->attributes[$name] !== '';
    }
    
    public function renderAttributes() : string
    {
        $attributes = $this->attributes;
        
        if(!empty($this->classes)) {
            $attributes['class'] = $this->classesToString();
        }
        
        if(!empty($this->styles)) {
            $attributes['style'] = ConvertHelper::array2styleString($this->styles);
        }
        
        return compileAttributes($attributes);
    }

    /**
     * @var array<string,string>
     */
    protected $styles = array();

    /**
     * @param string $name
     * @param string|number|NULL $value
     * @return $this
     */
    public function setStyle(string $name, $value) : self
    {
        $this->styles[$name] = (string)$value;

        return $this;
    }
    
   /**
    * Appends a child content to the bootstrap element.
    * Note that the element has to support this, otherwise
    * it will have no effect.
    * 
    * @param UI_Bootstrap $child
    * @throws Application_Exception
    * @return $this
    */
    public function appendChild(UI_Bootstrap $child) : self
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
    public function prependChild(UI_Bootstrap $child) : self
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
     * @return $this
     * @throws UI_Exception
     */
    public function setParent(UI_Bootstrap $parent) : self
    {
        $this->requireParentHasChild($parent);

        $this->parent = $parent;
        return $this;
    }
    
   /**
    * @var UI_Bootstrap|NULL
    */
    protected ?UI_Bootstrap $parent = null;
    
   /**
    * Retrieves the element's parent element, if any.
    * @return UI_Bootstrap|NULL
    */
    public function getParent() : ?UI_Bootstrap
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
     * @throws Application_Exception
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
        foreach($this->children as $child)
        {
            if($child->getName() === $name)
            {
                return true;
            }
        }
        
        return false;
    }
    
   /**
    * @return UI_Bootstrap[]
    */
    public function getChildren() : array
    {
        return $this->children;
    }
    
    public function hasChildren() : bool
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
     * @throws UI_Exception
     */
    private function requireParentHasChild(UI_Bootstrap $parent) : void
    {
        if ($parent->hasChild($this->getName()))
        {
            return;
        }

        throw new UI_Exception(
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
