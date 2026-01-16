<?php

class Application_AjaxMethods_LockingReleaseLock extends Application_AjaxMethod
{
    public const string METHOD_NAME = 'LockingReleaseLock';

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function processJSON()
    {
        $lock_id = $this->request->registerParam('lock_id')->setInteger()->get();
        $lock = Application_LockManager::findByID($lock_id);
        
        if($lock) 
        {
            $transferTo = null;
            $user_id = $this->request->registerParam('transfer_to_user')->setInteger()->get();
            if(!empty($user_id)) {
                $transferTo = $this->user->createByID($user_id);
            }
            
            $this->startTransaction();
            $lock->release($transferTo);
            $this->endTransaction();
        }
        
        $this->sendResponse(array(
            'found' => isset($lock)
        ));
    }
}