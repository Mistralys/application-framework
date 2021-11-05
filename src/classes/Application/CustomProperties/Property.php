<?php

class Application_CustomProperties_Property extends DBHelper_BaseRecord
{
    public const ERROR_CANNOT_FIND_OWNER = 16901;
    
   /**
    * @var Application_CustomProperties
    */
    protected $collection;
    
   /**
    * @var Application_CustomProperties_Presets 
    */
    protected $presets;

    protected $preset;

    protected $editable = true;
    
   /**
    * @var Application_Interfaces_Propertizable
    */
    protected $owner;
    
    protected function init()
    {
        $this->presets = $this->collection->getPresets();
        
        $preset_id = $this->getPresetID();
        if(!empty($preset_id)) {
            $this->preset = $this->presets->getByID($preset_id);
            $this->editable = $this->preset->isEditable();
        }
        
        $this->owner = Application_CustomProperties::resolveOwner($this->getOwnerType(), $this->getOwnerKey());
        if(!$this->owner) {
            throw new Application_Exception(
                'Property owner not found',
                sprintf(
                    'Could not find owner of property [%s], owner type [%s] and key [%s].',
                    $this->getID(),
                    $this->getOwnerType(),
                    $this->getOwnerKey()
                ),
                self::ERROR_CANNOT_FIND_OWNER
            );
        }
        
        $this->registerRecordKey('label', t('Label'));
        $this->registerRecordKey('name', t('Alias'), true);
        $this->registerRecordKey('value', t('Value'), true);
        $this->registerRecordKey('is_structural', t('Is structural?'), true);
    }
    
    public function getLabel() : string
    {
        if(!$this->editable) {
            return $this->preset->getLabel();
        }
        
        return $this->getRecordKey('label');
    }

    public function getOwnerType()
    {
        return $this->getRecordKey('owner_type');
    }
    
    public function getOwnerKey()
    {
        return $this->getRecordKey('owner_key');
    }
    
    public function getName()
    {
        if(!$this->editable) {
            return $this->preset->getName();
        }
        
        return $this->getRecordKey('name');
    }
    
    public function isStructural()
    {
        if(!$this->editable) {
            return $this->preset->isStructural();
        }
        
        return $this->getRecordBooleanKey('is_structural');
    }
    
    public function getValue()
    {
        return $this->getRecordKey('value');
    }
    
    public function getPresetID()
    {
        return $this->getRecordKey('preset_id');
    }
    
   /**
    * @return Application_CustomProperties_Presets_Preset
    */
    public function getPreset()
    {
        return $this->preset;
    }
    
    public function toArray()
    {
        return array(
            'property_id' => $this->getID(),
            'preset_id' => $this->getPresetID(),
            'owner_type' => $this->getOwnerType(),
            'owner_key' => $this->getOwnerKey(),
            'label' => $this->getLabel(),
            'name' => $this->getName(),
            'is_structural' => $this->isStructural(),
            'value' => $this->getValue()
        );
    }
    
    public function injectJS(UI $ui)
    {
        $varName = $this->collection->getJSCollectionVarName();
        
        $ui->addJavascriptHeadStatement(
            sprintf('%s.RegisterProperty', $varName),
            $this->getID(),
            $this->getLabel(),
            $this->getName(),
            $this->getValue(),
            $this->isStructural(),
            '',
            $this->getPresetID()
        );
    }
    
    public function setLabel($label)
    {
        return $this->setRecordKey('label', $label);
    }
    
    public function setIsStructural($isStructural)
    {
        return $this->setRecordBooleanKey('is_structural', $isStructural);
    }
    
    public function setName($name)
    {
        return $this->setRecordKey('name', $name);
    }
    
    public function setValue($value)
    {
        return $this->setRecordKey('value', $value);
    }
    
    public function getOwner()
    {
        return $this->owner;
    }

    protected function recordRegisteredKeyModified($name, $label, $isStructural, $oldValue, $newValue)
    {
        $this->owner->handle_propertyModified($this, $label, $oldValue, $newValue, $isStructural);
    }
}