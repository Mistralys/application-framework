<?php
/**
 * File containing the {@link Application_AjaxMethods_LockingKeepAlive} class.
 * 
 * @package Application
 * @subpackage LockManager
 * @see Application_AjaxMethods_LockingKeepAlive
 */

/**
 * This is called by the user locking a page to keep his lock 
 * of the page alive. Additionally it sends back information
 * on all locks the current user may have in other browser tabs,
 * so the UI can keep track of them all.
 *
 * @package Application
 * @subpackage LockManager
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_AjaxMethods_LockingKeepAlive extends Application_LockManager_AjaxMethod
{
    public const string METHOD_NAME = 'LockingKeepAlive';

    public function getMethodName() : string
    {
        return self::METHOD_NAME;
    }

    public function processJSON()
    {
        if(!isset($this->record)) 
        {
            $this->sendResponse(array('locked' => false));
        }
        
        $this->startTransaction();
        
        // Using strtotime because the browser may have its own timezone.
        // Since it is a UTC time string, we can use strtotime to get the 
        // equivalent local time.
        $ts = strtotime($this->request->getParam('last_activity'));
        if($ts > -1) {
            $date = new DateTime();
            $date->setTimestamp($ts);
            
            $this->record->extend($date);
            $this->record->save();
        }

        $this->sendStatus();
    }
}
