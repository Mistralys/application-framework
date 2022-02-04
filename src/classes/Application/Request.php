<?php
/**
 * File containing the {@link Application_Request} class.
 * @package Application
 * @subpackage Core
 * @see Application_Request
 */

use AppUtils\Request;

/**
 * Request management: wrapper around request variables with validation
 * capabilities and overall easier and more robust request variable handling.
 *
 * Usage:
 *
 * // get a parameter. If it does not exist, returns null.
 * $request->getParam('name');
 *
 * // get a parameter and specify the default value to return if it does not exist.
 * $request->getParam('name', 'Default value');
 *
 * // register a parameter to specify its validation: if the existing
 * // value does not match the type, it will be considered inexistent.
 * $request->registerParam('name')->setInteger();
 *
 * @package Application
 * @subpackage Core
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 *
 * @see Application_Driver::__construct()
 */
class Application_Request extends Request
{
    /**
     * @var string|NULL
     */
    private static $requestID;

    /**
     * @return void
     * @see Application_Driver::__construct()
     */
    protected function init()
    {
        $this->setBaseURL(APP_URL);

        Application::getLogger()->logSF(
            'Initialized request [%s].',
            self::getRequestID()
        );
    }

    /**
     * @return Application_Request
     */
    public static function getInstance()
    {
        $instance = parent::getInstance();

        if($instance instanceof Application_Request)
        {
            return $instance;
        }

        throw new Application_Exception_UnexpectedInstanceType(Application_Request::class, $instance);
    }

    public static function getRequestID() : string
    {
        if(!isset(self::$requestID))
        {
            self::$requestID = md5('request-id-'.microtime(true));
        }

        return self::$requestID;
    }
    
    public function getDispatcher() : string
    {
        return Application_Driver::getInstance()
            ->getApplication()
            ->getBootScreen()
            ->getDispatcher();
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
