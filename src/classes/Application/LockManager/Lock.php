<?php
/**
 * File containing the {@link Application_LockManager_Lock} class.
 * 
 * @package Application
 * @subpackage LockManager
 * @see Application_LockManager_Lock
 */

/**
 * Container class for a single locked administration screen.
 * Offers an easy to use API to access the lock information.
 * 
 * @package Application
 * @subpackage LockManager
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_LockManager_Lock extends DBHelper_BaseRecord
{
   /**
    * Retrieves the primary value used, if any.
    * @return string
    */
    public function getPrimary()
    {
        return $this->getRecordKey('item_primary');
    }
    
   /**
    * Retrieves the lock's label, if any. This is
    * usually the title of the administration screen
    * being locked.
    * 
    * @return string
    */
    public function getLabel() : string
    {
        return $this->getRecordKey('lock_label');
    }
    
   /**
    * Retrieves the administration screen's URL path,
    * e.g. area.mode.submode
    * 
    * @return string
    */
    public function getURLPath()
    {
        return $this->getRecordKey('screen_url_path');
    }
    
   /**
    * Retrieves the user ID of the owner of the lock.
    * 
    * @return integer
    */
    public function getLockedByID() : int
    {
        return intval($this->getRecordKey('locked_by'));
    }
    
   /**
    * Retrieves the owner of the lock.
    * @return Application_User
    */
    public function getLockedBy()
    {
        $user = Application::getUser();
        return $user->createByID($this->getLockedByID());
    }
    
   /**
    * Retrieves the date and time this lock was created.
    * @return DateTime
    */
    public function getLockedTime($utc=false)
    {
        return $this->getDate(new DateTime($this->getRecordKey('locked_time')));
    }
    
    protected function getDate(DateTime $date, $utc=false)
    {
        if($utc) {
            $date->setTimezone(new DateTimeZone('UTC'));
        }
            
        return $date;
    }
    
   /**
    * Retrieves the date and time at which the lock expires.
    * This gets updated each time the user is detected as being active.
    *  
    * @return DateTime
    */
    public function getLockedUntil($utc=false)
    {
        return $this->getDate(new DateTime($this->getRecordKey('locked_until')));
    }
    
   /**
    * Retrieves the date and time the user was last seen active 
    * in the administration screen.
    * 
    * @return DateTime
    */
    public function getLastActivity($utc=false)
    {
        return $this->getDate(new DateTime($this->getRecordKey('last_activity')));
    }

   /**
    * Retrieves the amount of time the owner of the lock has been active in the page.
    * @return DateInterval
    */
    public function getTimeActive()
    {
        return $this->getLockedTime()->diff(new DateTime());
    }
    
    public function getTimeInactive()
    {
        return $this->getLastActivity()->diff(new DateTime());
    }
    
   /**
    * Retrieves the amount of time until the lock expires automatically.
    * @return DateInterval
    */
    public function getTimeToUnlock()
    {
        $expiry = $this->getLockedUntil();
        
        $lockedUntil = $this->getLastActivity();
        $lockedUntil->add(Application_LockManager::getExpiryDelay());
        
        if($expiry->getTimestamp() < $lockedUntil->getTimestamp()) {
            $lockedUntil = $expiry;
        }
        
        $now = new DateTime();
        
        $diff = date_diff($now, $lockedUntil);
        return $diff;
    }
    
   /**
    * Retrieves the amount of time the owner was active in the page in a human readable version.
    * @return string
    */
    public function getTimeActivePretty()
    {
        return AppUtils\ConvertHelper::interval2string($this->getTimeActive());
    }
    
    public function getTimeToUnlockPretty()
    {
        return AppUtils\ConvertHelper::interval2string($this->getTimeToUnlock());
    }
    
    public function updateActive(DateTime $lastActivity=null)
    {
        if(!$lastActivity) {
            $lastActivity = new DateTime();
        }
        
        if($this->getLastActivity()->getTimestamp() > $lastActivity->getTimestamp() ) {
            return $this;
        }
        
        return $this->setTimeActive($lastActivity);
    }
    
    public function setTimeActive(DateTime $time)
    {
        return $this->setRecordKey('last_activity', $time->format('Y-m-d H:i:s'));
    }
    
    public function setLockedUntil(DateTime $time)
    {
        return $this->setRecordKey('locked_until', $time->format('Y-m-d H:i:s'));
    }
    
    public function setLockedTime(DateTime $time)
    {
        return $this->setRecordKey('locked_time', $time->format('Y-m-d H:i:s'));
    }
    
   /**
    * Extends the lock for another duration. This is used to prolong the
    * lock when the user is detected as being active in the locked page.
    * 
    * @return Application_LockManager_Lock
    */
    public function extend(DateTime $lastActivity=null)
    {
        $extended = new DateTime();
        $extended->add(Application_LockManager::getExpiryDelay());

        $this->setLockedUntil($extended);
        $this->updateActive($lastActivity);
        $this->setProperty('released', 'no');
        
        return $this;
    }
    
   /**
    * Serialized the lock information to an array with all
    * relevant information. If the visiting user is specified,
    * information on any unlock request the user may have sent
    * is included as well.
    * 
    * NOTE: All dates are UTC to be compatible with the client.
    * 
    * @param Application_User|NULL $visitor
    * @return array<string,mixed>
    */
    public function toArray(Application_User $visitor = null) : array
    {
        $info = array(
            'lock_id' => $this->getID(),
            'lock_label' => $this->getLabel(),
            'locked_until' => $this->getLockedUntil(true)->format('Y-m-d H:i:s'),
            'last_activity' => $this->getLastActivity(true)->format('Y-m-d H:i:s'),
            'screen_url_path' => $this->getRecordKey('screen_url_path'),
            'screen_name' => $this->getRecordKey('screen_name'),
            'item_primary' => $this->getRecordKey('item_primary'),
            'locked_by' => $this->getRecordKey('locked_by'),
            'time_locked' => $this->getLockedTime(true)->format('Y-m-d H:i:s'),
            'time_editing_elapsed' => $this->getTimeActivePretty(),
            'time_to_unlock' => $this->getTimeToUnlockPretty(),
            'time_to_unlock_short' => $this->getTimeToUnlock()->format('%H:%I:%S'),
            'time_to_unlock_critlevel' => $this->getTimeToUnlockCritLevel(),
            'time_inactive' => $this->getTimeInactive()->format('%H:%I:%S'),
            'user_name' => $this->getLockedBy()->getName(),
            'is_released' => $this->isReleased(),
            'is_forced_release' => $this->isForcedRelease(),
            'unlock_requests' => array()
        );
        
        $entries = DBHelper::fetchAllKey(
            'message_id',
            "SELECT
                `message_id`
            FROM
                `app_locking_messages`
            WHERE
                `lock_id`=:lock_id",
            array(
                'lock_id' => $this->getID()
            )
        );
        
        if(!empty($entries)) {
            $messaging = Application::createMessaging();
            foreach($entries as $message_id) {
                $info['unlock_requests'][] = $messaging->getByID($message_id)->toArray();
            }
        }
        
        return $info;
    }
    
    public function isForcedRelease()
    {
        if($this->getProperty('forced_release') == 'yes') {
            return true;
        }
        
        return false;
    }
   
   /**
    * Expiry criticality levels: these labels are sent with
    * the lock info to the client to allow layout adjustments
    * based on how much time is left before it expires.
    * 
    * @var array Percent time => label pairs
    */
    protected static $unlockCritLevels = array(
        10 => 'critical',
        30 => 'warning',
        50 => 'attention',
        70 => 'minimal'
    );
    
   /**
    * Retrieves all expiry critical levels, as percent => label pairs.
    * @return array
    */
    public static function getCritLevels()
    {
        return self::$unlockCritLevels;
    }

   /**
    * Retrieves the criticality level of the time left until
    * the automatic unlock, from the locking user's viewpoint.
    * 
    * @return string
    */
    public function getTimeToUnlockCritLevel()
    {
        $time = $this->getTimeToUnlock();
        $lockMinutes = AppUtils\ConvertHelper::interval2total($time, AppUtils\ConvertHelper::INTERVAL_MINUTES);
        $maxMinutes = Application_LockManager::EXPIRY_DELAY;
        
        foreach(self::$unlockCritLevels as $percent => $level) {
            $minutes = floor($percent * $maxMinutes / 100);
            if($lockMinutes <= $minutes) {
                return $level;
            }
        }
        
        return 'normal';
    }

    /**
     * Sends an unlock request from a visiting user to the blocking
     * user using the lock ID.
     *
     * @param Application_User $visitor
     * @param string $text An optional, custom message to send. 
     * @return Application_Messaging_Message
     */
    public function sendUnlockRequest(Application_User $visitor, $text=null)    
    {
        DBHelper::requireTransaction('Send a locking unlock request');
        
        $message = $this->getRequestUnlockMessage($visitor);
        if($message) {
            return $message;
        }
        
        $messaging = Application::createMessaging();
        
        if(empty($text)) {
            $text = t('Please allow me to edit this page.');
        }
        
        $message = $messaging->addMessage(
            $this->getLockedBy(), 
            $text, 
            Application_Messaging::PRIORITY_HIGH, 
            $visitor
        );
        
        // connect the message to this lock
        $message->setLock($this);
        $message->save();
        
        // register this request
        DBHelper::insertDynamic(
            'app_locking_messages', 
            array(
                'lock_id' => $this->getID(), 
                'message_id' => $message->getID(),
                'requested_by' => $visitor->getID()
            )
        );
        
        return $message;
    }
    
   /**
    * Retrieves the unlock request message the specified user sent, if any. 
    * @param Application_User $visitor
    * @return Application_Messaging_Message|NULL
    */
    public function getRequestUnlockMessage(Application_User $visitor)
    {
        $entry = DBHelper::fetch(
            "SELECT
                *
            FROM
                `app_locking_messages`
            WHERE
                `lock_id`=:lock_id
            AND 
                `requested_by`=:requested_by",
            array(
                'lock_id' => $this->getID(),
                'requested_by' => $visitor->getID()
            )
        );
        
        if(is_array($entry) && isset($entry['message_id'])) {
            $messaging = Application::createMessaging();
            return $messaging->getByID($entry['message_id']);
        }
        
        return null;
    }
    
    public function getRequestUnlockMessages()
    {
        $entries = DBHelper::fetchAllKey(
            'message_id',
            "SELECT
                `message_id`
            FROM
                `app_locking_messages`
            WHERE
                `lock_id`=:lock_id",
            array(
                'lock_id' => $this->getID()
            )
        );
        
        $result = array();
        if(is_array($entries)) {
            $messaging = Application::createMessaging();
            foreach($entries as $message_id) {
                $result[] = $messaging->getByID($message_id);
            }
        }
        
        return $result;
    }
    
   /**
    * Checks whether the specified user already sent an unlock request
    * for this lock.
    * 
    * @param Application_User $visitor
    * @return boolean
    */
    public function isUnlockRequestSent(Application_User $visitor)
    {
        return DBHelper::recordExists(
            'app_locking_messages', 
            array(
                'lock_id' => $this->getID(), 
                'requested_by' => $visitor->getID()            
            )
        );
    }
    
   /**
    * Releases a lock on a page. Removes all related messages, and
    * removes the lock. If a user is specified to transfer the lock
    * to, it is transferred and all messages removed.
    * 
    * @param Application_User $transferTo
    */
    public function release(Application_User $transferTo=null)
    {
        DBHelper::requireTransaction('Release a page lock');

        // transfer to another user? In this case, we simply
        // update the owner in the database.
        if($transferTo) 
        {
            $messages = $this->getRequestUnlockMessages();
            foreach($messages as $message) {
                $message->delete();
            }
            
            $this->setRecordKey('locked_by', $transferTo->getID());
            $this->setLockedTime(new DateTime());
            $this->extend();
            $this->save();
            return;
        }
        
        // set the expiry to X seconds in the future, just enough
        // time for the user to reload the page or come back.
        $extended = new DateTime();
        $extended->add(Application_LockManager::getExpiryLeaveDelay());
       
        $active = new DateTime();
        
        $this->setLockedUntil($extended);
        $this->setTimeActive($active);
        $this->setProperty('released', 'yes');
        $this->save();
    }
    
    public function forcedRelease()
    {
        $this->setProperty('forced_release', 'yes');
        $this->release();
    }
    
    protected $properties = array();
    
    protected function setProperty($name, $value)
    {
        $this->properties[$name] = $value;
        return $this;
    }
    
    protected function getProperty($name, $default=null)
    {
        if(isset($this->properties[$name])) {
            return $this->properties[$name];
        }
        
        return $default;
    }
    
    public function getRecordPrimaryName()
    {
        return 'lock_id';
    }
    
    public function getRecordTable()
    {
        return 'app_locking';
    }
    
    public function getRecordTypeName()
    {
        return 'lock';
    }
 
    public function save(bool $silent=false) : bool
    {
        $this->setRecordKey('properties', json_encode($this->properties));
        
        return parent::save();
    }
    
    public function isReleased()
    {
        if($this->getProperty('released', 'no') === 'yes') {
            return true;
        }
        
        return false;
    }
    
    protected function init()
    {
        $props = $this->getRecordKey('properties');
        if(!empty($props)) {
            $this->properties = json_decode($props, true);
        }
    }

    protected function recordRegisteredKeyModified($name, $label, $isStructural, $oldValue, $newValue)
    {
    }
}