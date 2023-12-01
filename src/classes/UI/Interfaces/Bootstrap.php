<?php

use AppUtils\Interfaces\ClassableInterface;

interface UI_Interfaces_Bootstrap extends ClassableInterface, UI_Renderable_Interface
{
    public const ERROR_CHILD_NAME_ALREADY_EXISTS = 18601;
    public const ERROR_NOT_A_CHILD_ELEMENT_OF_PARENT = 18602;
    public const ERROR_INVALID_CHILD_ELEMENT = 18603;
    
    public function __construct(UI $ui);
    
    /**
     * Sets the element's name, which can be used to retrieve it when used in collections.
     * @param string $name
     * @return UI_Bootstrap
     */
    public function setName(string $name) : self;
    
   /**
    * @return string
    */
    public function getName() : string;
    
   /**
    * @param string $name
    * @return bool
    */
    public function isNamed(string $name) : bool;
    
    public function getID() : string;
    
    public function setID(string $id) : self;
    
    public function setAttribute(string $name, $value) : self;
    
    public function getAttribute(string $name, $default=null);
    
    public function hasAttribute(string $name) : bool;
    
    public function renderAttributes() : string;

    /**
     * @param string $name
     * @param string|number|NULL $value
     * @return $this
     */
    public function setStyle(string $name, $value) : self;
    
    public function appendChild(UI_Bootstrap $child) : self;
    
    public function setParent(UI_Bootstrap $parent) : self;
    
    public function getParent() : ?UI_Bootstrap;
    
    public function createChild(string $type) : UI_Interfaces_Bootstrap;
    
    public function hasChild(string $name) : bool;

    /**
     * @return UI_Bootstrap[]
     */
    public function getChildren() : array;
    
    public function hasChildren() : bool;
}
