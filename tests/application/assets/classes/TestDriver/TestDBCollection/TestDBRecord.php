<?php

declare(strict_types=1);

class TestDriver_TestDBCollection_TestDBRecord extends DBHelper_BaseRecord
{
    public const COL_LABEL = 'label';
    public const COL_ALIAS = 'alias';

    private array $custom = array();

    protected function recordRegisteredKeyModified($name, $label, $isStructural, $oldValue, $newValue)
    {
    }

    public function getLabel(): string
    {
        return $this->getRecordStringKey(self::COL_LABEL);
    }

    public function getAlias() : string
    {
        return $this->getRecordStringKey(self::COL_ALIAS);
    }

    public function setLabel(string $label) : bool
    {
        return $this->setRecordKey(self::COL_LABEL, $label);
    }

    public function setAlias(string $alias) : bool
    {
        return $this->setRecordKey(self::COL_ALIAS, $alias);
    }

    public function setCustomField(string $name, string $value) : bool
    {
        if(isset($this->custom[$name]) && $this->custom[$name] === $value) {
            return false;
        }

        $this->custom[$name] = $value;
        $this->setCustomModified($name);
        return true;
    }
}
