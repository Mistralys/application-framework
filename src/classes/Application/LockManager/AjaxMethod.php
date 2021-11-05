<?php

abstract class Application_LockManager_AjaxMethod extends Application_AjaxMethod
{
    public const ERROR_NO_URL_PATH_SPECIFIED = 13101;
    
    public const ERROR_NO_USER_SPECIFIED = 13102;
    
    public const ERROR_UNKNOWN_USER = 13103;
    
    public const ERROR_NO_RECORD_FOUND = 13104;
    
    public const ERROR_INVALID_LOCK_ID_TO_EXTEND = 13105;
    
    public const ERROR_INVALID_LOCK_ID_TO_RELEASE = 13106;
    
    protected $data;
    
    protected function validateRequest()
    {
        Application_LockManager::cleanUpExpired();
        
        $primary = htmlentities(strip_tags($this->request->getParam('primary', '')));
        $path = htmlentities(strip_tags($this->request->getParam('url_path', '')));
        $user_id = $this->request->registerParam('user_id')->setInteger()->get();
        
        if(empty($user_id) || !Application::userIDExists($user_id)) {
            $this->sendError(
                t('No user specified'),
                null,
                self::ERROR_NO_USER_SPECIFIED
            );
        }

        if(empty($path)) {
            $this->sendError(
                t('No path specified.'),
                null,
                self::ERROR_NO_URL_PATH_SPECIFIED
            );
        }
        
        $this->owner = Application::createUser($user_id);

        $visitor_id = $this->request->registerParam('visitor_id')->setInteger()->get();
        if(!empty($visitor_id)) {
            $this->visitor = $this->user->createByID($visitor_id);
        }
        
        $this->record = Application_LockManager::getByPath(
            $path,
            $primary,
            $this->owner
        );
    }
    
   /**
    * @var Application_User
    */
    protected $owner;
    
   /**
    * @var Application_User
    */
    protected $visitor;
    
   /**
    * @var Application_LockManager_Lock
    */
    protected $record;
    
    protected function requireRecord()
    {
        if(isset($this->record)) {
           return;
        }
         
        $this->sendError(
            t('No lock was found for this page.'),
            null,
            self::ERROR_NO_RECORD_FOUND
        );
    }
 
    protected function sendStatus($isVisitor=false)
    {
        $data = array();
        $data['locked'] = true;
        $data['current_lock_id'] = $this->record->getID();
        $data['current_lock'] = $this->record->toArray($this->visitor);
        $data['active_locks'] = array();
        $data['extended_locks'] = array();
        
        // did the user request to extend any of his other locks?
        // check this first so all times get updated automatically
        // in the active locks array.
        $this->extendLocks($data);
        $this->releaseLocks($data);
        
        $user = $this->user;
        if($isVisitor) {
            $user = $this->visitor;
        }
        
        $records = Application_LockManager::getByUser($user);

        $data['active_locks_count'] = count($records);
        
        foreach($records as $record) {
            $data['active_locks'][$record->getID()] = $record->toArray();
        }
        
        $this->sendResponse($data);
    }
    
   /**
    * Checks the request for any lock IDs that the user wishes to extend,
    * and extends them as required. Adds the extended lock IDs to the 
    * data array under the <code>extended_locks</code> key. This is used
    * clientside to update the list.
    * 
    * @param array $data
    * @throws Application_Exception
    */
    protected function extendLocks(&$data)
    {
        $lockIDs = $this->request->registerParam('extend_locks')->setArray()->get();
        if(empty($lockIDs)) {
            return;
        }
        
        foreach($lockIDs as $lockID) {
            if(!is_numeric($lockID)) {
                throw new Application_Exception(
                    'Invalid lock specified',
                    sprintf(
                        'Expected a lock ID to extend, got [%s] in the [%s] request parameter.',
                        $lockID,
                        'extend_locks'
                    ),
                    self::ERROR_INVALID_LOCK_ID_TO_EXTEND
                );
            }
            
            $lock = Application_LockManager::findByID($lockID);
            if(!$lock) {
                continue;
            }
            
            $lock->extend();
            $lock->save();
            $data['extended_locks'][] = $lockID;
        }
    }
    
    protected function releaseLocks(&$data)
    {
        $lockIDs = $this->request->registerParam('release_locks')->setArray()->get();
        if(empty($lockIDs)) {
            return;
        }
        
        foreach($lockIDs as $lockID) {
            if(!is_numeric($lockID)) {
                throw new Application_Exception(
                    'Invalid lock specified',
                    sprintf(
                        'Expected a lock ID to release, got [%s] in the [%s] request parameter.',
                        $lockID,
                        'release_locks'
                    ),
                    self::ERROR_INVALID_LOCK_ID_TO_RELEASE
                );
            }
        
            $lock = Application_LockManager::findByID($lockID);
            if(!$lock) {
                continue;
            }
        
            $lock->forcedRelease();
            $data['released_locks'][] = $lockID;
        }
    }
}