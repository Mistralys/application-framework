<?php
/**
 * File containing the {@link Application_Request} class.
 * @package Application
 * @subpackage Core
 * @see Application_Request
 */

/**
 * Request management: wrapper around request variables with validation
 * capabilities and overall easier and more robust request variable handling.
 *
 * Usage:
 *
 * // get a parameter. If it does not exist, returns null.
 * $request->getParam('name');
 *
 * // get a parameter and specifiy the default value to return if it does not exist.
 * $request->getParam('name', 'Default value');
 *
 * // register a parameter to specify its validation: if the existing
 * // value does not match the type, it will be considered inexistent.
 * $request->registerParam('name')->setInteger();
 *
 * @package Application
 * @subpackage Core
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 */
class Application_Request extends AppUtils\Request
{
   /**
    * @var Application_Request
    */
    protected static $instance;
    
    public function __construct()
    {
        self::$instance = $this;
        
        $this->setBaseURL(APP_URL);
    }
    
   /**
    * @return Application_Request
    */
    public static function getInstance()
    {
        return self::$instance;
    }
    
    public function getDispatcher() : string
    {
        return Application_Driver::getInstance()->getApplication()->getBootScreen()->getDispatcher(); 
    }
    
    public function getExcludeParams()
    {
        return array(
            Application_Session_Base::KEY_NAME_SIMULATED_ID,
            'lockmanager_enable'
        );
    }
    
    public function buildPrintURL($params = array())
    {
        return $this->buildRefreshURL(array('print' => 'yes'));
    }
}
