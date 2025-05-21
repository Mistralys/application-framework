<?php
/**
 * File containing the {@link Application_AjaxMethods_CustomPropertyAdd} class.
 *
 * @package Application
 * @subpackage Custom properties
 * @see Application_AjaxMethods_CustomPropertyAdd
 */

/**
 * This is called to add a new custom property for an item.
 *
 * @package Application
 * @subpackage Custom properties
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_AjaxMethods_CustomPropertyAdd extends BaseCustomPropertiesMethod
{
    public const METHOD_NAME = 'CustomPropertyAdd';

    public function getMethodName() : string
    {
        return self::METHOD_NAME;
    }

    public function processJSON()
    {
        //$this->forceStartSimulation();
        $this->startTransaction();
        
        $this->owner = $this->owner->startPropertiesTransaction();
        
        $property = $this->owner->getProperties()->addProperty(
            $this->label, 
            $this->name, 
            $this->value, 
            '', 
            $this->isStructural,
            $this->preset
        );
        
        $this->owner = $this->owner->endPropertiesTransaction();
        
        $this->endTransaction();
        
        $payload = array(
            'added' => $this->owner->getPropertyByID($property->getID())->toArray(),
            'owner_key' => $this->owner->getPropertiesOwnerKey()
        );
        
        $this->sendResponse($payload);
    }
    
    protected function validateRequest()
    {
        $this->requireLabel();
        $this->requireName();
        $this->requireOwner();        
        $this->requireIsStructural();
        $this->requireValue();
        $this->resolvePreset();
    }
}