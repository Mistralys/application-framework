<?php
/**
 * File containing the {@link Application_URL} class.
 * 
 * @package Application
 * @subpackage Core
 * @see Application_URL 
 */

declare(strict_types=1);

use function AppUtils\parseURL;
use AppUtils\URLInfo;

/**
 * Application URL parser: this is used to access information
 * about an application-internal URL. To create an instance,
 * use the method {@link Application_Driver::parseURL()}.
 * 
 * @package Application
 * @subpackage Core
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see Application_Driver::parseURL()
 */
class Application_URL
{
    const ERROR_INCOMPLETE_URL = 29801;

    /**
     * @var string
     */
    protected $rawURL;

    /**
     * @var string
     */
    protected $dispatcher = '';

    /**
     * @var string
     */
    protected $screenPath;

    /**
     * @var URLInfo
     */
    protected $info;

    public function __construct($url)
    {
        $this->rawURL = $url;
        $this->info = $info = \AppUtils\parseURL($this->rawURL);

        $this->parse();
    }
    
    protected function parse()
    {
        if(!$this->info->hasScheme())
        {
            throw new Application_Exception(
                'Scheme missing: Only full URLs are allowed',
                sprintf('Tried parsing the URL [%s].', $this->rawURL),
                self::ERROR_INCOMPLETE_URL
            );
        }

        $this->detectDispatcher();
        $this->detectScreen();
    }

    private function getAppPath() : string
    {
        return rtrim(trim(parseURL(APP_URL)->getPath()), '/');
    }

    private function getURLPath() : string
    {
        return rtrim(trim($this->info->getPath()), '/');
    }

    private function detectScreen() : void
    {
        $screenTokens = array();
        $params = $this->info->getParams();
        $pageVars = Application_Driver::getInstance()->getURLParamNames();

        foreach($pageVars as $varName)
        {
            if(!isset($params[$varName])) {
                break;
            }

            $value = $params[$varName];
            $screenTokens[] = $value;
        }

        if(empty($screenTokens))
        {
            return;
        }

        $this->screenPath = implode('.', $screenTokens);
    }

    private function detectDispatcher() : void
    {
        $path = $this->getURLPath();
        $appPath = $this->getAppPath();

        $path = str_replace($appPath, '', $path);
        $path = trim($path, '/');
        $path = str_replace('index.php', '', $path);
        $path = rtrim($path, '/');

        if(empty($path)) {
            return;
        }

        $this->dispatcher = $path;
    }
    
   /**
    * Retrieves the path to the dispatcher file that handled
    * the request in the URL. <code>index.php</code> is stripped
    * to keep this consistent: filenames are only included if
    * they are not an index file. An empty dispatcher means the
    * main index.php file.
    * 
    * @return string
    */
    public function getDispatcher() : string
    {
        return $this->dispatcher;
    }
    
   /**
    * Retrieves the path to the target administration screen, if any.
    * This is formatted like:
    * 
    * <code>page.mode.submode.action</code>
    * 
    * Example:
    *
    * <code>devel.maintenance</code>
    * 
    * @return string
    */
    public function getScreenPath()
    {
        return $this->screenPath;
    }
    
    public function getHash() : string
    {
        return $this->info->getHash();
    }
    
   /**
    * Retrieves all parameters beyond those selecting
    * the target administration screen.
    * 
    * @return string[]
    */
    public function getParams() : array
    {
        $pageVars = Application_Driver::getInstance()->getURLParamNames();
        $params = $this->info->getParams();
        $result = array();

        foreach($params as $name => $value)
        {
            if(!in_array($name, $pageVars))
            {
                $result[$name] = $value;
            }
        }

        return $result;
    }
    
   /**
    * Checks whether the URL has parameters.
    * @return bool
    */
    public function hasParams() : bool
    {
        return $this->info->hasParams();
    }
}
