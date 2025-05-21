<?php
/**
 * File containing the {@link Application_AjaxMethods_CustomPropertySave} class.
 *
 * @package Application
 * @subpackage Custom properties
 * @see Application_AjaxMethods_CustomPropertySave
 */

/**
 * This is called to save a custom property's data.
 *
 * @package Application
 * @subpackage Custom properties
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_AjaxMethods_CustomPropertySave extends BaseCustomPropertiesMethod
{
    public const METHOD_NAME = 'CustomPropertySave';

    public function getMethodName() : string
    {
        return self::METHOD_NAME;
    }

    public function processJSON()
    {
        //$this->forceStartSimulation();
        $this->startTransaction();

        $this->owner = $this->owner->startPropertiesTransaction();
        
        $this->requireProperty();
        
        if(!$this->request->getBool('value_only')) {
            $this->property->setIsStructural($this->isStructural);
            $this->property->setLabel($this->label);
            $this->property->setName($this->name);
        } else {
            $this->property->setValue($this->value);
        }
        
        $this->property->save();
        
        $this->owner = $this->owner->endPropertiesTransaction();
        
        $this->endTransaction();
        
        $payload = array(
            'edited' => $this->owner->getPropertyByID($this->property->getID())->toArray(),
            'owner_key' => $this->owner->getPropertiesOwnerKey()
        );
        
        $this->sendResponse($payload);
    }
    
    protected function validateRequest()
    {
        $this->requireOwner();
        
        if(!$this->request->getBool('value_only')) {
            $this->requireLabel();
            $this->requireName();
            $this->requireIsStructural();
            $this->resolvePreset();
        } else {
            $this->requireValue();
        }
    }
}