<?php
/**
 * File containing the {@link Application_AjaxMethods_LockingKeepAlive} class.
 *
 * @package Application
 * @subpackage LockManager
 * @see Application_AjaxMethods_LockingKeepAlive
 */

/**
 * This is called by the user visiting a locked page. It allows
 * refreshing the lock state, with the expiry delay. Additionally,
 * it keeps track of the user's unlock request.
 *
 * @package Application
 * @subpackage LockManager
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_AjaxMethods_LockingGetStatus extends Application_LockManager_AjaxMethod
{
    public const string METHOD_NAME = 'LockingGetStatus';

    public function getMethodName() : string
    {
        return self::METHOD_NAME;
    }

    public function processJSON()
    {
        if(!isset($this->record)) 
        {
            $this->sendResponse(array(
                'locked' => false
            ));
        }
        
        $this->startTransaction();
        
        if($this->request->getBool('request_unlock')) {
            $this->record->sendUnlockRequest($this->visitor, $this->request->getFilteredParam('request_unlock_message'));
        }
        
        $this->sendStatus(true);
    }
}
