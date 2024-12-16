<?php

declare(strict_types=1);

abstract class BaseCustomPropertiesMethod extends Application_AjaxMethod
{
    public const METHOD_NAME = 'CustomProperties';

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    protected $label;
    
    protected function requireLabel()
    {
        if(isset($this->label)) {
            return;
        }
        
        $this->label = $this->request->registerParam('label')->setNameOrTitle()->get();
        if(empty($this->label)) {
            $this->sendErrorUnknownElement(t('label'));
        }
    }
    
    protected $name;
    
    protected function requireName()
    {
        if(isset($this->name)) {
            return;
        }
        
        $this->name = $this->request->registerParam('name')->setAlias()->get();
        if(empty($this->name)) {
            $this->sendErrorUnknownElement(t('Alias'));
        }
    }
    
    protected ?Application_Interfaces_Propertizable $owner = null;
    
   /**
    * Requires that a valid owner be specified in the request
    * for the custom property, with the <code>owner_type</code>
    * and <code>owner_key</code> parameters.
    * 
    * @return Application_Interfaces_Propertizable
    */
    protected function requireOwner() : Application_Interfaces_Propertizable
    {
        if(isset($this->owner)) {
            return $this->owner;
        }
        
        $ownerType = $this->request->registerParam('owner_type')
        ->addStripTagsFilter()
        ->addHTMLSpecialcharsFilter()
        ->addFilterTrim()
        ->get();
        
        $ownerKey = $this->request->registerParam('owner_key')
        ->addStripTagsFilter()
        ->addHTMLSpecialcharsFilter()
        ->addFilterTrim()
        ->get();
        
        if(empty($ownerType)) {
            $this->sendErrorUnknownElement(t('property owner type'));
        }
        
        $owner = Application_CustomProperties::resolveOwner($ownerType, $ownerKey);
        if($owner === null) {
            $this->sendErrorUnknownElement(t('custom property owner'));
        }

        $this->owner = $owner;

        return $this->owner;
    }
    
    protected $isStructural;
    
    protected function requireIsStructural()
    {
        if(isset($this->isStructural)) {
            return;
        }
        
        $this->isStructural = $this->request->registerParam('is_structural')->setBoolean()->get();
    }
    
    protected $value;
    
    protected function requireValue()
    {
        if(isset($this->value)) {
            return;
        }
        
        $this->value = $this->request->registerParam('value')->addFilterTrim()->get();
        if(empty($this->value)) {
            $this->sendErrorUnknownElement(t('property value'));
        }
    }
    
    protected $preset = null;
    
    protected function resolvePreset()
    {
        if(isset($this->preset)) {
            return $this->preset;
        }
        
        /*
        $this->presetID = $this->request->registerParam('preset_id')
        ->setCallback(array(''));
        */
        
        return $this->preset;
    }
    
   /**
    * @var Application_CustomProperties_Property
    */
    protected $property;
    
    protected function requireProperty()
    {
        $this->requireOwner();
        
        $property_id = $this->request->registerParam('property_id')->setInteger()->get();
        if(empty($property_id)) {
            $this->sendErrorUnknownElement('custom property ID');
        }
        
        $this->property = $this->owner->getPropertyByID($property_id);
        if(!$this->property) {
            $this->sendErrorUnknownElement('custom property');
        }
    }
}
