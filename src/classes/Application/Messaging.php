<?php
/**
 * File containing the {@link Application_Messaging} class.
 * 
 * @package Application
 * @subpackage Messaging
 * @see Application_Messaging
 */

/**
 * Helper class for managing messages between application users.
 * 
 * @package Application
 * @subpackage Messaging
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_Messaging extends DBHelper_BaseCollection
{
    const ERROR_INVALID_MESSAGE_PRIORITY = 13401;
    const ERROR_TO_AND_FROM_USERS_IDENTICAL = 13402;
    
   /**
    * The amount of time to wait between requests to the server
    * to update the messages list.
    * 
    * @var integer The time, in seconds
    */
    const UI_PULL_DELAY = 20;
    
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    
    protected static $injected = array();
    
   /**
    * Injects the javascript required to make the messaging work
    * clientside, with all needed includes and statements.
    * 
    * @param UI $ui
    */
    public function injectJS(UI $ui)
    {
        $uiKey = $ui->getInstanceKey();
        
        if(isset(self::$injected[$uiKey])) {
            return;
        }
        
        $ui->addJavascript('application/messaging.js');
        $ui->addJavascriptHeadVariable('Application_Messaging.PullDelay', self::UI_PULL_DELAY);
        $ui->addJavascriptOnload('Application_Messaging.Start()');
        
        self::$injected[$uiKey] = true;
    }
    
    protected $messages = array();
    
   /**
    * Retrieves a message by its ID.
    * 
    * @param int $message_id
    * @return Application_Messaging_Message
    */
    public function getByID(int $message_id) : DBHelper_BaseRecord
    {
        if(isset($this->messages[$message_id])) {
            return $this->messages[$message_id];
        }
        
        $message = new Application_Messaging_Message($message_id, $this);
        $this->messages[$message_id] = $message;
        
        return $message;
    }
    
    protected static $priorities;

   /**
    * Retrieves a list of all available priorities with their
    * human readable labels.
    * 
    * @return string[]
    */
    public static function getPriorities()
    {
        if(!isset(self::$priorities)) {
            self::$priorities = array(
                self::PRIORITY_NORMAL => t('Normal priority'),
                self::PRIORITY_HIGH => t('High priority')
            );
        }
        
        return self::$priorities;
    }
    
    public static function priorityExists($priority)
    {
        $priorities = self::getPriorities();
        return isset($priorities[$priority]);
    }
    
    public static function requirePriorityExists($priority)
    {
        if(self::priorityExists($priority)) {
            return;
        }
        
        $priorities = self::getPriorities();
        
        throw new Application_Exception(
            'Invalid message priority',
            sprintf(
                'Tried adding a message with an invalid priority [%s]. Valid priorities are: [%s].',
                AppUtils\ConvertHelper::var_dump($priority),
                implode(', ', array_keys($priorities))
            ),
            self::ERROR_INVALID_MESSAGE_PRIORITY
        );
    }
    
   /**
    * Adds a new message, and returns the message instance. Use this
    * to configure it further as needed.
    * 
    * @param Application_User $toUser
    * @param string $message
    * @param string $priority
    * @param Application_User $fromUser
    * @return Application_Messaging_Message
    */
    public function addMessage(Application_User $toUser, $message, $priority=self::PRIORITY_NORMAL, Application_User $fromUser = null)
    {
        DBHelper::requireTransaction('Add a message');
        
        $this->requirePriorityExists($priority);
        
        if($fromUser == null) {
            $fromUser = Application::getUser();
        }
        
        if($fromUser->getID() == $toUser->getID()) {
            throw new Application_Exception(
                'Source and target users cannot be the same',
                sprintf(
                    'The to and from users are the same, [%s].',
                    $toUser->getID()
                ),
                self::ERROR_TO_AND_FROM_USERS_IDENTICAL
            );
        }
        
        $now = new DateTime();
        
        $message_id = intval(DBHelper::insert(
            "INSERT INTO
                `app_messaging`
            SET
                `from_user`=:from_user,
                `to_user`=:to_user,
                `message`=:message,
                `priority`=:priority,
                `date_sent`=:date_sent",
            array(
                'from_user' => $fromUser->getID(),
                'to_user' => $toUser->getID(),
                'message' => $message,
                'priority' => $priority,
                'date_sent' => $now->format('Y-m-d H:i:s')
            )
        ));
        
        return $this->getByID($message_id);
    }
    
    public static function getPriorityLabel($priority)
    {
        self::requirePriorityExists($priority);
        $priorities = self::getPriorities();
        return $priorities[$priority];
    }
    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordClassName()
     */
    public function getRecordClassName() : string
    {
        return 'Application_Messaging_Message';
    }

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordFiltersClassName()
     */
    public function getRecordFiltersClassName() : string
    {
        return 'Application_Messaging_FilterCriteria';
    }

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordFilterSettingsClassName()
     */
    public function getRecordFilterSettingsClassName() : string
    {
        return 'Application_Messaging_FilterSettings';
    }

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordDefaultSortKey()
     */
    public function getRecordDefaultSortKey() : string
    {
        return 'date_sent';
    }

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordSearchableColumns()
     */
    public function getRecordSearchableColumns() : array
    {
        return array(
            'message' => t('Message text')
        );
    }

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordTableName()
     */
    public function getRecordTableName() : string
    {
        return 'app_messaging';
    }

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordPrimaryName()
     */
    public function getRecordPrimaryName() : string
    {
        return 'message_id';
    }

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordTypeName()
     */
    public function getRecordTypeName() : string
    {
        return 'message';
    }

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getCollectionLabel()
     */
    public function getCollectionLabel() : string
    {
        return t('Application messages');
    }

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordLabel()
     */
    public function getRecordLabel() : string
    {
        return t('Application message');
    }

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordProperties()
     */
    public function getRecordProperties() : array
    {
        return array();
    }
}