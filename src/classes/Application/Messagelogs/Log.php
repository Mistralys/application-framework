<?php

class Application_Messagelogs_Log extends DBHelper_BaseRecord
{
    protected function recordRegisteredKeyModified($name, $label, $isStructural, $oldValue, $newValue)
    {
    }

    public function getLabel() : string
    {
        return t(
            'Log %1$s, %2$s', 
            $this->getID(), 
            AppUtils\ConvertHelper::date2listLabel($this->getDate(), true, true)
        );
    }
    
    public function getDate() : DateTime
    {
        return $this->getRecordDateKey('date');
    }
    
    public function getMessage() : string
    {
        return $this->getRecordStringKey('message');
    }
    
    public function getType() : string
    {
        return $this->getRecordStringKey('type');
    }
    
    public function getCategory() : string
    {
        return $this->getRecordStringKey('category');
    }
    
    public function isInfo() : bool
    {
        return $this->isType(Application_Messagelogs::MESSAGELOG_INFORMATION);
    }

    public function isError() : bool
    {
        return $this->isType(Application_Messagelogs::MESSAGELOG_ERROR);
    }
    
    public function isWarning() : bool
    {
        return $this->isType(Application_Messagelogs::MESSAGELOG_WARNING);
    }
    
    public function isType(string $type) : bool
    {
        return $this->getType() === $type;
    }
    
    public function getUserID() : int
    {
        return $this->getRecordIntKey('user_id');
    }
    
   /**
    * @return Application_Users_User
    */
    public function getUser() : Application_Users_User
    {
        $users = Application_Driver::createUsers();
        return $users->getByID($this->getUserID());
    }
}
