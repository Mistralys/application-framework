<?php

/**
 * File containing the {@link Application_LockManager} class.
 * 
 * @package Application
 * @subpackage LockManager
 * @see Application_LockManager
 */

use AppUtils\ClassHelper;

/**
 * Class handling application screen locking to avoid several
 * users editing records at the same time.
 * 
 * @package Application
 * @subpackage LockManager
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @author Andreas Martin <a.martin1@1und1.de>
 */
class Application_LockManager extends DBHelper_BaseCollection
{
    public const ERROR_STATE_MAY_NOT_BE_MODIFIED = 551001;
    public const ERROR_REVISIONABLE_IS_NOT_LOCKABLE = 551002;
    public const ERROR_REVISIONABLE_PRIMARY_UNHANDLED = 551003;

   /**
    * The amount of seconds to show the "Are you still there?" dialog before the automatic unlock.
    * @var integer
    */
    const AUTO_UNLOCK_DIALOG_DELAY = 30;
    
    public function getRecordPrimaryName() : string
    {
        return 'lock_id';
    }
    
    public function getRecordTableName() : string
    {
        return 'app_locking';
    }
    
    public function getRecordSearchableColumns() : array
    {
        return array(
            'lock_label' => t('Label'),
            'screen_name' => t('Page name')
        );
    }
    
    public function getRecordFiltersClassName() : string
    {
        return 'Application_LockManager_FilterCriteria';
    }
    
    public function getRecordFilterSettingsClassName() : string
    {
        return 'Application_LockManager_FilterSettings';
    }
    
    public function getRecordClassName() : string
    {
        return 'Application_LockManager_Lock';
    }
    
    public function getRecordDefaultSortKey() : string
    {
        return 'locked_until';
    }
    
    public function getRecordTypeName() : string
    {
        return 'lock';
    }
    
    
   /**
    * The amount of seconds between AJAX requests to keep a user's lock alive.
    * @var integer
    */
    const KEEP_ALIVE_DELAY = 4;
    
   /**
    * The amount of seconds between AJAX requests to update the UI for a visiting user.
    * @var integer
    */
    const REFRESH_STATUS_DELAY = 4;
    
   /**
    * The amount of minutes before a lock is automatically removed from the database.
    * This should be slightly higher than the auto unlock delay. Note that this gets
    * extended automatically each time the user is active.
    * 
    * @var integer
    */
    const EXPIRY_DELAY = 20; 
    
   /**
    * The amount of seconds to set the expiry delay to when a user leaves the page 
    * he was locking: this allows for the user to reload the page safely without 
    * losing his lock on the page, and avoids too many lock challenges.
    * 
    * @var integer
    */
    const EXPIRY_LEAVE_DELAY = 20;

   /**
    * @var Application_Interfaces_Admin_LockableScreen
    */
    protected $adminScreen;
    
    protected $primary = '';
    
    protected $simulateLock = false;
    
    /**
     * @param Application_Interfaces_Admin_LockableScreen $adminScreen
     */
    public function bindScreen(Application_Interfaces_Admin_LockableScreen $adminScreen)
    {
        $this->adminScreen = $adminScreen;
        $request = Application_Driver::getInstance()->getRequest();
        
        if($request->getParam('simulate_lock') == 'yes' && Application::getUser()->isDeveloper()) {
            $this->simulateLock = true;
        }
    }
    
   /**
    * Sets the primary key for which the lock manager should be
    * locked. This effectively locks the current administration screen
    * only for this specific primary key.
    * 
    * @param mixed|Application_LockableRecord_Interface $primary Custom string-based ID, or a revisionable item.
    * @see getPrimary()
    */
    public function setPrimary($primary)
    {
        if($primary instanceof Application_LockableRecord_Interface) 
        {
            if(!$primary->isLockable()) {
                throw new Application_Exception(
                    'Item is not lockable',
                    sprintf(
                        'The lockable item of type [%s] cannot be locked. Tried to use it as lockable item on the administration screen [%s].',
                        get_class($primary),
                        $this->adminScreen->getURLPath()
                    ),
                    self::ERROR_REVISIONABLE_IS_NOT_LOCKABLE
                );
            }
            
            $this->log('Primary is a lockable item.');
            
            $primary->setLockManager($this);
            
            $primary = $primary->getLockPrimary();
        } 
        else if(!is_string($primary) && !is_numeric($primary)) 
        {
            throw new Application_Exception(
                'Unhandled locking primary',
                'Specified a non-string, non-numeric value as primary value. Please re-check your getLockManagerPrimary method return value.',
                self::ERROR_REVISIONABLE_PRIMARY_UNHANDLED
            );
        }
        
        $this->log(sprintf('Using primary [%s].', $primary));
        $this->primary = $primary;
    }
    
    protected function _getIdentification() : string
    {
        return 'LockManager';
    }

   /**
    * Retrieves the primary key for which the lock manager is 
    * currently locked. This is always a string, and can be
    * empty if no primary value is required in the locked screen.
    * 
    * @return string
    * @see setPrimary()
    */
    public function getPrimary()
    {
        return $this->primary;
    }
    
   /**
    * Retrieves the path to the locked administration screen,
    * e.g. <code>area.mode.submode</code>.
    * 
    * @return string
    */
    public function getURLPath()
    {
        return $this->adminScreen->getURLPath();
    }
    
   /**
    * Checks whether the lock manager is enabled globally. In developer
    * mode, the lock manager can be disabled for development purposes.
    * Outside of developer mode, this always returns true.
    * 
    * @return boolean
    */
    public static function isEnabled() : bool
    {
        if(!isDevelMode()) {
            return true;
        }
        
        $state = Application_Driver::createSettings()
            ->get('lockmanager_state', 'enabled');

        return $state === 'enabled';
    }
    
   /**
    * Turns on the lock management.
    * Note: only available in developer mode.
    */
    public static function enable()
    {
        self::setState('enabled');
    }
    
   /**
    * Turns off the lock management.
    * Note: only available in developer mode.
    */
    public static function disable()
    {
        self::setState('disabled');
    }
    
   /**
    * Sets the global state of the lock manager. 
    * Note: only available in developer mode.
    * 
    * @param string $state [enabled|disabled]
    * @throws Application_Exception
    */
    protected static function setState(string $state) : void
    {
        if(!isDevelMode()) {
            throw new Application_Exception(
                'Disabling or enabling the lock manager is not allowed',
                'The lock manager state can only modified when the application is in developer mode.',
                self::ERROR_STATE_MAY_NOT_BE_MODIFIED    
            );
        }
        
        Application_Driver::createSettings()->set('lockmanager_state', $state);
    }

    protected $loaded = false;
    
    protected $locked = false;
    
    protected $data = null;
    
    protected function load()
    {
        if($this->loaded) {
            return;
        }
        
        $this->cleanUpExpired();
        
        $entry = DBHelper::fetch(
            "SELECT
                `locked_by`,
                `locked_until`
            FROM
                `app_locking`
            WHERE
                `screen_url_path` = :screen_url_path
            AND
                `item_primary` = :item_primary",
            array(
                'screen_url_path' => $this->adminScreen->getURLPath(),
                'item_primary' => $this->primary
            )
        );
        
        if(!empty($entry)) {
            $this->locked = true;
            $this->data = $entry;
        }

        $this->loaded = true;
    }
    
   /**
    * Cleans up all locks from the database that have expired.
    */
    public static function cleanUpExpired()
    {
        DBHelper::delete(
            "DELETE FROM
                `app_locking`
            WHERE
                `locked_until` < :expiry_date",
            array(
                'expiry_date' => date('Y-m-d H:i:s')
            )
        );
    }
    
   /**
    * @return DateInterval
    */
    public static function getExpiryDelay()
    {
        return new DateInterval('PT'.(self::EXPIRY_DELAY * 60).'S');
    }

    /**
     * The interval for the expiry when a user leaves the page he was 
     * locking: this allows for the user to reload the page safely without 
    *  losing his lock on the page, and avoids too many lock challenges.
    *  
     * @return DateInterval
     */
    public static function getExpiryLeaveDelay()
    {
        return new DateInterval('PT'.(self::EXPIRY_LEAVE_DELAY).'S');
    }
    
   /**
    * Tries to lock the administration screen for the current user.
    * If another user is already locking the screen, this will return
    * false. The expiry date is updated if the current user is already
    * the one locking the screen.
    * 
    * @return boolean
    */
    public function lock()
    {
        if(!self::isEnabled()) {
            return false;
        }
        
        $this->load();
        
        $currentUser = Application::getUser();
        $expiry = new DateTime();
        $expiry->add($this->getExpiryDelay());
        
        $now = new DateTime();
        
        if(!$this->locked) 
        {
            $name = $this->adminScreen->getTitle();
            if(empty($name)) {
                $name = $this->adminScreen->getURLPath();
            }
            
            $this->locked = true;
            $this->data = array(
                'screen_url_path' => $this->adminScreen->getURLPath(),
                'screen_name' => $name,
                'lock_label' => $this->adminScreen->getLockLabel(),
                'item_primary' => $this->primary,
                'locked_by' => $currentUser->getID(),
                'locked_time' => $now->format('Y-m-d H:i:s'),
                'locked_until' => $expiry->format('Y-m-d H:i:s'),
                'last_activity' => $now->format('Y-m-d H:i:s'),
                'properties' => ''
            );
            
            DBHelper::insertDynamic('app_locking', $this->data);
            
            return true;
        }
        
        // another user already locks the screen
        if($this->isLocked()) 
        {
            return false;
        }
        
        DBHelper::update(
            "UPDATE
                `app_locking`
            SET
                `locked_until` = :locked_until,
                `last_activity` = :last_activity
            WHERE
                `screen_url_path` = :screen_url_path
            AND
                `item_primary` = :item_primary",
            array(
                'locked_until' => $expiry->format('Y-m-d H:i:s'),
                'last_activity' => $now->format('Y-m-d H:i:s'),
                'screen_url_path' => $this->adminScreen->getURLPath(),
                'item_primary' => $this->primary
            )
        );
        
        return true;
    }

   /**
    * Checks whether the current administration screen is locked for the current user.
    * @return boolean
    */
    public function isLocked()
    {
        return $this->isLockedFor(Application::getUser()); 
    }
    
    public function getLockReason() : ?string
    {
        if($this->isLocked()) {
            return t('Locked by %1$s.', $this->getUser()->getName());
        }
        
        return null;
    }
    
   /**
    * Checks whether the current administration screen is locked for the specified user.
    * @param Application_User $user
    * @return boolean
    */
    public function isLockedFor(Application_User $user)
    {
        if(!self::isEnabled()) {
            return false;
        }
        
        if($this->simulateLock) {
            return true;
        }
        
        $current = $this->getUser();
        if($current === null) {
            return false;
        }
        
        if($current->getID() != $user->getID()) {
            return true;
        }
        
        return false;
    }

    /**
     * Retrieves the user currently locking the screen, or NULL if
     * the page is not locked.
     * 
     * @return Application_User|NULL
     */
    public function getUser()
    {
        $this->load();
        
        if($this->locked) {
            return Application::getUser()->createByID($this->data['locked_by']);
        }

        return null;
    }

    /**
     * @return bool|string
     */
    public function getBadge()
    {
        if (!$this->isLocked()) {
            return false;
        }
        
        return UI::label($this->getIcon() . ' ' . t('Locked'))->makeDangerous();
    }

    /**
     * @return string
     */
    public function getTooltip()
    {
        $user = $this->getUser();
        if($user) {
            return t('This page is edited by %1$s', $user->getName());
        }
        
        return t('This page is locked.');
    }

    /**
     * @param boolean $tooltip
     * @return UI_Icon
     */
    public function getIcon($tooltip = true)
    {
        $icon = UI::icon()->locked();

        if ($tooltip) {
            $icon->setTooltip($this->getTooltip());
        }

        return $icon;
    }
    
    public function getExpiry()
    {
        $this->load();
        
        if($this->locked) {
            return new DateTime($this->data['locked_until']);
        }
    }
    
   /**
    * 
    * @return DateInterval|NULL
    */
    public function getUnlockDuration()
    {
        $this->load();
        
        if(!$this->locked) {
            return null;
        } 
        
        $now = new DateTime();
        return $now->diff($this->getExpiry());
    }
    
   /**
    * Injects the javascript required for the lock manager support. This 
    * is static because it is required even if the lock manager is disabled:
    * When it is enabled, the actual manager instance is specified as parameter.
    * 
    * @param UI $ui
    * @param Application_LockManager $manager
    */
    public static function injectJS(UI $ui, Application_LockManager $manager=null)
    {
        $ui->addJavascript('LockManager.js');
        $ui->addStylesheet('ui-locking.css');
        
        $autoUnlockDelay = (self::EXPIRY_DELAY * 60) - (self::AUTO_UNLOCK_DIALOG_DELAY + 20);
        
        $ui->addJavascriptHeadVariable('LockManager.Enabled', self::isEnabled());
        $ui->addJavascriptHeadVariable('LockManager.autoUnlockDialogDelay', self::AUTO_UNLOCK_DIALOG_DELAY);
        $ui->addJavascriptHeadVariable('LockManager.autoUnlockDelay', $autoUnlockDelay);
        $ui->addJavascriptHeadVariable('LockManager.keepAliveDelay', self::KEEP_ALIVE_DELAY);
        $ui->addJavascriptHeadVariable('LockManager.refreshStatusDelay', self::REFRESH_STATUS_DELAY);
        
        if(!$manager) {
            $ui->addJavascriptHeadVariable('LockManager.Locked', false);
            return;
        }
        
        $ui->addJavascriptOnload('LockManager.Start()');
        
        $request = Application_Driver::getInstance()->getRequest();
        
        $ui->addJavascriptHeadVariable('LockManager.Locked', $manager->isLocked());
        $ui->addJavascriptHeadVariable('LockManager.Primary', $manager->getPrimary());
        $ui->addJavascriptHeadVariable('LockManager.UrlPath', $manager->getURLPath());
        $ui->addJavascriptHeadVariable('LockManager.ScreenURL', $request->buildRefreshURL());
        $ui->addJavascriptHeadVariable('LockManager.CritLevels', Application_LockManager_Lock::getCritLevels());
        
        if($manager->isLocked()) {
            $user = $manager->getUser();
            $ui->addJavascriptHeadVariable(
                'LockManager.LockedBy', 
                array(
                    'Name' => $user->getName(),
                    'ID' => $user->getID()
                )
            );
            $ui->addJavascriptHead(sprintf(
                "LockManager.LockedUntil = new Date('%s')", 
                $manager->getExpiry()->format('Y-m-d H:i:s')
            ));
        }
    }
    
   /**
    * Tries to get a lock by the administration screen url, primary and locking user.
    * 
    * @param string $urlPath
    * @param string $primary
    * @param Application_User $user
    * @return Application_LockManager_Lock|NULL
    */
    public static function getByPath($urlPath, $primary, Application_User $user)
    {
        return self::createFromRecord(DBHelper::fetch(
            "SELECT
                `lock_id`,
                `screen_url_path`
            FROM
                `app_locking`
            WHERE
                `screen_url_path`=:screen_url_path
            AND
                `item_primary`=:item_primary
            AND
                `locked_by`=:locked_by",
            array(
                'screen_url_path' => $urlPath,
                'item_primary' => $primary,
                'locked_by' => $user->getID()
            )
        ));
    }
    
    public static function findByID($lock_id)
    {
        return self::createFromRecord(DBHelper::fetch(
            "SELECT
                `lock_id`,
                `screen_url_path`
            FROM
                `app_locking`
            WHERE
                `lock_id`=:lock_id",
            array(
                'lock_id' => $lock_id
            )
        ));
    }
    
    protected static function createFromRecord($record)
    {
        if(empty($record)) {
            return null;
        }
        
        $screen = ClassHelper::requireObjectInstanceOf(
            Application_Interfaces_Admin_LockableScreen::class,
            Application_Driver::getInstance()->getScreenByPath($record['screen_url_path'])
        );
        
        $manager = new Application_LockManager();
        $manager->bindScreen($screen);
        
        return $manager->getByID($record['lock_id']);
    }
    
    public static function start() : void
    {
        $request = Application_Driver::getInstance()->getRequest();
        
        if($request->getParam('redirect_reason') == 'lock_expired') {
            UI::getInstance()->addInfoMessage(
                UI::icon()->information().' '.
                '<b>' . t('You were inactive for too long.').'</b> '.
                t('To return to the page you were editing, press the back button in your browser.')
            );
        }
        
        if($request->getParam('redirect_reason') == 'lock_released') {
            UI::getInstance()->addInfoMessage(
                UI::icon()->information().' '.
                '<b>' . t('You released the page lock manually.').'</b> '.
                t('To return to the page you were editing, press the back button in your browser.')
            );
        }
    }
    
   /**
    * Retrieves all locks for the specified user.
    * 
    * @param Application_User $user
    * @return Application_LockManager_Lock[]
    */
    public static function getByUser(Application_User $user)
    {
        $ids = DBHelper::fetchAll(
            "SELECT
                `lock_id`,
                `screen_url_path`
            FROM
                `app_locking`
            WHERE
                `locked_by`=:locked_by",
            array(
                'locked_by' => $user->getID()
            )
        );
        
        $result = array();
        foreach($ids as $entry) {
            $result[] = self::createFromRecord($entry);
        }
        
        return $result;
    }
    
    public static function deleteLock(Application_LockManager_Lock $lock)
    {
        DBHelper::deleteRecords('app_locking', array('lock_id' => $lock->getID()));
    }

    public function getCollectionLabel() : string
    {
        return t('Lock manager');    
    }

    public function getRecordLabel() : string
    {
        return t('Page lock');
    }

    public function getRecordProperties() : array
    {
        return array();
    }
}