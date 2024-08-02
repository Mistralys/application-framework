<?php

class Application_Users_User extends DBHelper_BaseRecord
{
    public const COL_EMAIL = 'email';
    public const COL_FIRST_NAME = 'firstname';
    public const COL_LAST_NAME = 'lastname';

    public function getUserInstance() : Application_User
    {
        return Application::createUser($this->getID());
    }

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
        return $this->getRecordKey(self::COL_FIRST_NAME);
    }
    
    public function getLastname()
    {
        return $this->getRecordKey(self::COL_LAST_NAME);
    }
    
    public function getEmail()
    {
        return $this->getRecordKey(self::COL_EMAIL);
    }

    public function setEmail(string $email) : bool
    {
        return $this->setRecordKey(self::COL_EMAIL, $email);
    }

    public function setFirstName(string $firstName) : bool
    {
        return $this->setRecordKey(self::COL_FIRST_NAME, $firstName);
    }

    public function setLastName(string $lastName) : bool
    {
        return $this->setRecordKey(self::COL_LAST_NAME, $lastName);
    }

    public function setForeignID(string $foreignID) : bool
    {
        return $this->setRecordKey('foreign_id', $foreignID);
    }
}
