<?php
/**
 * @package Ratings
 * @subpackage Screens
 */

declare(strict_types=1);

/**
 * @package Ratings
 * @subpackage Screens
 */
class RatingScreenRecord extends DBHelper_BaseRecord
{
    protected function recordRegisteredKeyModified($name, $label, $isStructural, $oldValue, $newValue) : void
    {
    }

    public function getLabel() : string
    {
        return $this->getDispatcher().'/'.$this->getPath();
    }

    public function getPath() : string
    {
        return $this->getRecordStringKey('path');
    }

    public function getDispatcher() : string
    {
        return $this->getRecordStringKey('dispatcher');
    }

    public function getHash() : string
    {
        return $this->getRecordStringKey('hash');
    }
}
