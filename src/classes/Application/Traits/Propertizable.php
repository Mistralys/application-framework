<?php

declare(strict_types=1);

trait Application_Traits_Propertizable
{
    protected ?Application_CustomProperties $properties = null;

    /**
     * Retrieves the group's properties collection. This can be
     * used to access and modify the free properties stored
     * alongside the group.
     *
     * @return Application_CustomProperties
     */
    public function getProperties() : Application_CustomProperties
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
    public function getPropertyByID(int $property_id) : Application_CustomProperties_Property
    {
        return $this->getProperties()->getByID($property_id);
    }
}
