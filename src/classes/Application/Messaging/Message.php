<?php

class Application_Messaging_Message extends DBHelper_BaseRecord
{
    public const ERROR_INVALID_CUSTOM_DATA = 13501;
    
   /**
    * @var Application_Messaging
    */
    protected $messaging;
    
    protected $data;
    
    protected function init() : void
    {
        $this->messaging = Application::createMessaging();
    }
    
    public function getLabel() : string
    {
        return $this->getMessage();
    }
    
    public function getMessage()
    {
        return $this->getRecordKey('message');
    }
    
    public function getPriority()
    {
        return $this->getRecordKey('priority');
    }
    
    public function setPriority($priority)
    {
        Application_Messaging::requirePriorityExists($priority);
        return $this->setRecordKey('priority', $priority);
    }
    
    public function setCustomData($dataString)
    {
        if(!is_string($dataString) && !is_numeric($dataString)) {
            throw new Application_Exception(
                'Invalid custom message data',
                sprintf(
                    'The custom data must be a string, [%s] given.',
                    gettype($dataString)
                ),
                self::ERROR_INVALID_CUSTOM_DATA
            );
        }
        
        return $this->setRecordKey('custom_data', $dataString);
    }
    
    public function getInReplyToID()
    {
        return $this->getRecordKey('in_reply_to');
    }
    
    public function isReply()
    {
        $id = $this->getInReplyToID();
        return !empty($id);
    }
    
    public function getFromUser()
    {
        return Application::getUser()->createByID($this->getFromUserID());
    }
    
    public function getFromUserID()
    {
        return $this->getRecordKey('from_user');
    }
    
    public function getToUserID()
    {
        return $this->getRecordKey('to_user');
    }
    
    public function getToUser()
    {
        return Application::getUser()->createByID($this->getToUserID());
    }
    
    public function getPriorityPretty()
    {
        return Application_Messaging::getPriorityLabel($this->getPriority());
    }
    
    public function getDateSent()
    {
        return new DateTime($this->getRecordKey('date_sent'));
    }
    
    public function getDateReceived()
    {
        $date = $this->getRecordKey('date_received');
        if(!empty($date)) {
            return new DateTime($date);
        }
        
        return null;
    }

    public function getDateResponded()
    {
        $date = $this->getRecordKey('date_responded');
        if(!empty($date)) {
            return new DateTime($date);
        }
    
        return null;
    }
    
    public function getResponse()
    {
        return $this->getRecordKey('response');
    }
    
    public function getCustomData()
    {
        return $this->getRecordKey('custom_data');
    }
    
   /**
    * Retrieves the amount of time since the message was created.
    * @return DateInterval
    */
    public function getAge()
    {
        $now = new DateTime();
        return $now->diff($this->getDateSent());
    }
    
    public function getAgePretty()
    {
        return AppUtils\ConvertHelper::interval2string($this->getAge());
    }
    
    public function toArray()
    {
        $dateReceived = $this->getDateReceived();
        
        if($dateReceived) {
            $dateReceived = $dateReceived->format('Y-m-d H:i:s');
        }
        
        $responded = false;
        $dateResponded = $this->getDateResponded();
        if($dateResponded) {
            $dateResponded = $dateResponded->format('Y-m-d H:i:s');
            $responded = true;
        }
        
        return array(
            'message_id' => $this->getID(),
            'is_reply' => $this->isReply(),
            'in_reply_to' => $this->getInReplyToID(),
            'from_user' => $this->getFromUserID(),
            'from_user_name' => $this->getFromUser()->getName(),
            'to_user' => $this->getToUserID(),
            'to_user_name' => $this->getToUser()->getName(),
            'message' => $this->getMessage(),
            'priority' => $this->getPriority(),
            'priority_pretty' => $this->getPriorityPretty(),
            'date_sent' => $this->getDateSent()->format('Y-m-d H:i:s'),
            'date_received' => $dateReceived,
            'responded' => $responded,
            'date_responded' => $dateResponded,
            'response' => $this->getResponse(),
            'custom_data' => $this->getCustomData(),
            'age_pretty' => $this->getAgePretty()
        );
    }
    
    public function delete()
    {
        DBHelper::requireTransaction('Delete a message');
        DBHelper::deleteRecords('app_messaging', array('message_id' => $this->getID()));
    }
    
   /**
    * Connects this message to a page lock. This is used to ensure that the 
    * messages get deleted automatically when the lock is released.
    * 
    * @param Application_LockManager_Lock $lock
    * @return boolean
    */
    public function setLock(Application_LockManager_Lock $lock)
    {
        return $this->setRecordKey('lock_id', $lock->getID());
    }
    
    public function getRecordPrimaryName() : string
    {
        return 'message_id';
    }
    
    public function getRecordTable() : string
    {
        return 'app_messaging';
    }
    
    public function getRecordTypeName() : string
    {
        return 'message';
    }

    protected function recordRegisteredKeyModified($name, $label, $isStructural, $oldValue, $newValue)
    {
        // nothing to do here.
    }

}