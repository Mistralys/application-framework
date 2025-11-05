<?php

trait Application_Traits_Propertizable
{
    /**
     * @var Application_CustomProperties
     */
    protected $properties;

    /**
     * Retrieves the group's properties collection. This can be
     * used to access and modify the free properties stored
     * alongside the group.
     *
     * @return Application_CustomProperties
     */
    public function getProperties()
    {
        if(!isset($this->properties)) {
            $this->properties = new Application_CustomProperties();
            $this->properties->bindRecord($this);
        }

        return $this->properties;
    }
    
   /**
    * @param integer $property_id
    * @return Application_CustomProperties_Property
    */
    public function getPropertyByID($property_id)
    {
        return $this->getProperties()->getByID($property_id);
    }
}
