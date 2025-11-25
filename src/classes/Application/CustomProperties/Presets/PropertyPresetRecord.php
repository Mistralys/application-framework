<?php

declare(strict_types=1);

namespace Application\CustomProperties\Presets;

use DBHelper_BaseRecord;

class PropertyPresetRecord extends DBHelper_BaseRecord
{
    public function getLabel(): string
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

    public function isEditable() : bool
    {
        return $this->getRecordBooleanKey('editable');
    }

    public function isStructural() : bool
    {
        return $this->getRecordBooleanKey('is_structural');
    }

    public function getDefaultValue()
    {
        return $this->getRecordKey('default_value');
    }

    public function toArray() : array
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

    protected function recordRegisteredKeyModified($name, $label, $isStructural, $oldValue, $newValue): void
    {
    }
}
