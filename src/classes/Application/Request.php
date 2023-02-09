<?php
/**
 * File containing the {@link Application_Request} class.
 * @package Application
 * @subpackage Core
 * @see Application_Request
 */

declare(strict_types=1);

use Application\AppFactory;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException;
use AppUtils\Request;
use function AppUtils\parseURL;

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
    private static ?string $requestID = null;

    /**
     * @return void
     * @see Application_Driver::__construct()
     */
    protected function init() : void
    {
        $this->setBaseURL(APP_URL);

        AppFactory::createLogger()->logSF(
            'Initialized request [%s].',
            Application_Logger::CATEGORY_REQUEST,
            self::getRequestID()
        );
    }

    /**
     * @return Application_Request
     *
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     */
    public static function getInstance() : self
    {
        return ClassHelper::requireObjectInstanceOf(
            __CLASS__,
            parent::getInstance()
        );
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
    
    public function getExcludeParams() : array
    {
        return array(
            Application_Session_Base::KEY_NAME_SIMULATED_ID,
            'lockmanager_enable'
        );
    }

    /**
     * @param array<string,string|number> $params
     * @return string
     */
    public function buildPrintURL(array $params = array()) : string
    {
        $params['print'] = 'yes';

        return $this->buildRefreshURL($params);
    }

    /**
     * @param string|array<string,string|number>$urlOrParams
     * @return array
     */
    public static function resolveParams($urlOrParams) : array
    {
        if(is_array($urlOrParams))
        {
            return $urlOrParams;
        }

        return parseURL($urlOrParams)->getParams();
    }
}
