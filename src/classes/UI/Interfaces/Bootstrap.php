<?php

use AppUtils\Interface_Classable;

interface UI_Interfaces_Bootstrap extends Interface_Classable, UI_Renderable_Interface
{
    const ERROR_CHILD_NAME_ALREADY_EXISTS = 18601;
    const ERROR_NOT_A_CHILD_ELEMENT_OF_PARENT = 18602;
    const ERROR_INVALID_CHILD_ELEMENT = 18603;
    
    public function __construct(UI $ui);
    
    /**
     * Sets the element's name, which can be used to retrieve it when used in collections.
     * @param string $name
     * @return UI_Bootstrap
     */
    public function setName($name);
    
   /**
    * @return string
    */
    public function getName();
    
   /**
    * @param string $name
    * @return bool
    */
    public function isNamed($name);
    
    public function getID();
    
    public function setID($id);
    
    public function setAttribute($name, $value);
    
    public function getAttribute($name, $default=null);
    
    public function hasAttribute($name);
    
    public function renderAttributes();
    
    public function setStyle($name, $value);
    
    public function appendChild(UI_Bootstrap $child);
    
    public function setParent(UI_Bootstrap $parent);
    
    public function getParent();
    
    public function createChild(string $type) : UI_Interfaces_Bootstrap;
    
    public function hasChild(string $name) : bool;
    
    public function getChildren();
    
    public function hasChildren();
}
