<?php
/**
 * @package Messaging
 * @subpackage Collection
 */

declare(strict_types=1);

use Application\Messaging\MessagingCollection;
use Application\Messaging\MessagingException;
use AppUtils\ConvertHelper;
use AppUtils\Microtime;

/**
 * @package Messaging
 * @subpackage Collection
 */
class Application_Messaging_Message extends DBHelper_BaseRecord
{
    public function getLabel() : string
    {
        return $this->getMessage();
    }

    public function getMessage() : string
    {
        return $this->getRecordStringKey(MessagingCollection::COL_MESSAGE);
    }
    
    public function getPriority() : string
    {
        return $this->getRecordStringKey(MessagingCollection::COL_PRIORITY);
    }
    
    public function setPriority(string $priority) : bool
    {
        MessagingCollection::requirePriorityExists($priority);
        return $this->setRecordKey(MessagingCollection::COL_PRIORITY, $priority);
    }
    
    public function setCustomData(int|float|string|null $dataString) : bool
    {
        return $this->setRecordKey(MessagingCollection::COL_CUSTOM_DATA, $dataString);
    }
    
    public function getInReplyToID() : int
    {
        return $this->getRecordIntKey(MessagingCollection::COL_IN_REPLY_TO);
    }
    
    public function isReply() : bool
    {
        $id = $this->getInReplyToID();
        return $id > 0;
    }
    
    public function getFromUser() : Application_User
    {
        return Application::createUser($this->getFromUserID());
    }
    
    public function getFromUserID() : int
    {
        return $this->getRecordIntKey(MessagingCollection::COL_FROM_USER);
    }
    
    public function getToUserID() : int
    {
        return $this->getRecordIntKey(MessagingCollection::COL_TO_USER);
    }
    
    public function getToUser() : Application_User
    {
        return Application::createUser($this->getToUserID());
    }
    
    public function getPriorityPretty() : string
    {
        return MessagingCollection::getPriorityLabel($this->getPriority());
    }
    
    public function getDateSent() : Microtime
    {
        return Microtime::createFromString($this->getRecordKey(MessagingCollection::COL_DATE_SENT));
    }
    
    public function getDateReceived() : ?Microtime
    {
        return $this->getRecordMicrotimeKey(MessagingCollection::COL_DATE_RECEIVED);
    }

    public function getDateResponded() : ?Microtime
    {
        return $this->getRecordMicrotimeKey(MessagingCollection::COL_DATE_RESPONDED);
    }
    
    public function getResponse() : string
    {
        return $this->getRecordStringKey(MessagingCollection::COL_RESPONSE);
    }
    
    public function getCustomData() : string|int|float|null
    {
        $data = $this->getRecordKey(MessagingCollection::COL_CUSTOM_DATA);
        if(is_string($data) || is_numeric($data)) {
            return $data;
        }

        return null;
    }
    
   /**
    * Retrieves the amount of time since the message was created.
    * @return DateInterval
    */
    public function getAge() : DateInterval
    {
        return new DateTime()->diff($this->getDateSent());
    }
    
    public function getAgePretty() : string
    {
        return ConvertHelper::interval2string($this->getAge());
    }
    
    public function toArray() : array
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
            MessagingCollection::COL_IN_REPLY_TO => $this->getInReplyToID(),
            MessagingCollection::COL_FROM_USER => $this->getFromUserID(),
            'from_user_name' => $this->getFromUser()->getName(),
            MessagingCollection::COL_TO_USER => $this->getToUserID(),
            'to_user_name' => $this->getToUser()->getName(),
            'message' => $this->getMessage(),
            'priority' => $this->getPriority(),
            'priority_pretty' => $this->getPriorityPretty(),
            MessagingCollection::COL_DATE_SENT => $this->getDateSent()->format('Y-m-d H:i:s'),
            MessagingCollection::COL_DATE_RECEIVED => $dateReceived,
            'responded' => $responded,
            MessagingCollection::COL_DATE_RESPONDED => $dateResponded,
            MessagingCollection::COL_RESPONSE => $this->getResponse(),
            MessagingCollection::COL_CUSTOM_DATA => $this->getCustomData(),
            'age_pretty' => $this->getAgePretty()
        );
    }
    
    public function delete() : void
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
    public function setLock(Application_LockManager_Lock $lock) : bool
    {
        return $this->setRecordKey(MessagingCollection::COL_LOCK_ID, $lock->getID());
    }
    
    protected function recordRegisteredKeyModified($name, $label, $isStructural, $oldValue, $newValue) : void
    {
        // nothing to do here.
    }
}
