<?php
/**
 * File containing the {@link Application_AjaxMethods_CustomPropertyDelete} class.
 *
 * @package Application
 * @subpackage Custom properties
 * @see Application_AjaxMethods_CustomPropertyDelete
 */

/**
 * This is called to save a custom property's data.
 *
 * @package Application
 * @subpackage Custom properties
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_AjaxMethods_CustomPropertyDelete extends BaseCustomPropertiesMethod
{
    public const METHOD_NAME = 'CustomPropertyDelete';

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
        
        $this->owner->getProperties()->deleteRecord($this->property);
        
        $this->owner = $this->owner->endPropertiesTransaction();
        
        $this->endTransaction();
        
        $payload = array(
            'deleted' => $this->property->toArray(),
            'owner_key' => $this->owner->getPropertiesOwnerKey()
        );
        
        $this->sendResponse($payload);
    }
    
    protected function validateRequest()
    {
        $this->requireOwner();
    }
}