<?php

class Application_CustomProperties_Presets_Preset extends DBHelper_BaseRecord
{
    public function getLabel() : string
    {
        return $this->getRecordKey('label');
    }
    
    public function getOwnerType()
    {
        return $this->getRecordKey('owner_type');
    }
    
    public function getName()
    {
        return $this->getRecordKey('name');
    }
    
    public function isEditable()
    {
        return $this->getRecordBooleanKey('editable');
    }
    
    public function isStructural()
    {
        return $this->getRecordBooleanKey('is_structural');
    }
    
    public function getDefaultValue()
    {
        return $this->getRecordKey('default_value');
    }
    
    public function toArray()
    {
        return array(
            'preset_id' => $this->getID(),
            'owner_type' => $this->getOwnerType(),
            'label' => $this->getLabel(),
            'name' => $this->getName(),
            'editable' => $this->isEditable(),
            'is_structural' => $this->isStructural(),
            'default_value' => $this->getDefaultValue()
        );
    }
    /**
     * {@inheritDoc}
     * @see DBHelper_BaseRecord::recordRegisteredKeyModified()
     */
    protected function recordRegisteredKeyModified($name, $label, $isStructural, $oldValue, $newValue)
    {
    }
}
