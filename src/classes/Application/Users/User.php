<?php

class Application_Users_User extends DBHelper_BaseRecord
{
    protected function recordRegisteredKeyModified($name, $label, $isStructural, $oldValue, $newValue)
    {
        // nothing to do
    }

    public function getLabel() : string
    {
        return $this->getName();
    }
    
    public function getName()
    {
        return $this->getFirstname().' '.$this->getLastname();
    }
    
    public function getFirstname()
    {
        return $this->getRecordKey('firstname');
    }
    
    public function getLastname()
    {
        return $this->getRecordKey('lastname');
    }
    
    public function getEmail()
    {
        return $this->getRecordKey('email');
    }

    public function setEmail(string $email) : bool
    {
        return $this->setRecordKey('email', $email);
    }

    public function setFirstName(string $firstName) : bool
    {
        return $this->setRecordKey('firstname', $firstName);
    }

    public function setLastName(string $lastName) : bool
    {
        return $this->setRecordKey('lastname', $lastName);
    }

    public function setForeignID(string $foreignID) : bool
    {
        return $this->setRecordKey('foreign_id', $foreignID);
    }
}
