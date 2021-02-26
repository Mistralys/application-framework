<?php

class Application_Ratings_Screens_Screen extends DBHelper_BaseRecord
{
    protected function recordRegisteredKeyModified($name, $label, $isStructural, $oldValue, $newValue)
    {
    }

    public function getLabel() : string
    {
        return $this->getDispatcher().'/'.$this->getPath();
    }

    public function getPath()
    {
        return $this->getRecordKey('path');
    }

    public function getDispatcher()
    {
        return $this->getRecordKey('dispatcher');
    }

    public function getHash()
    {
        return $this->getRecordKey('hash');
    }
}