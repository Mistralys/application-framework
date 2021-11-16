<?php

declare(strict_types=1);

class TestIntegerBaseRecord extends Application_Collection_BaseRecord_IntegerPrimary
{
    protected function getRecordTypeLabel(): string
    {
        return 'TestRecord';
    }

    protected function getRecordPrimaryName(): string
    {
        return 'test_id';
    }

    public function getDate(string $name) : ?DateTime
    {
        return $this->getDataKeyDate($name);
    }

    public function getBool(string $name) : bool
    {
        return $this->getDataKeyBool($name);
    }

    public function setPrimary() : bool
    {
        return $this->setDataKey($this->getRecordPrimaryName(), 'value');
    }

    public function setKeyNotExists() : bool
    {
        return $this->setDataKey('not_exists', 'value');
    }

    public function setKeySameValue() : bool
    {
        return $this->setDataKey('key_exists', 'exists');
    }

    public function setKeyOverwriteValue() : bool
    {
        return $this->setDataKey('key_overwrite', 'new value');
    }

    protected function loadData(): array
    {
        return array(
            $this->getRecordPrimaryName() => 9999,

            'key_exists' => 'exists',
            'key_overwrite' => 'old value',

            'bool_true' => true,
            'bool_null' => null,
            'bool_string' => 'true',

            'date_not_string' => null,
            'date_string' => '2021-05-12 14:45:11',
            'date_invalid' => 'Not a valid date string',
            'date_object' => new DateTime()
        );
    }

    protected function init(): void
    {
    }
}
