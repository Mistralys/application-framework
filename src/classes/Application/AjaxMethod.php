<?php

use Application\AppFactory;
use AppUtils\ConvertHelper;
use AppUtils\FileHelper;
use AppUtils\Request_Exception;

abstract class Application_AjaxMethod
{
    public const ERROR_MALFORMED_JSON_DATA = 554001;

    public const RETURNFORMAT_HTML = 'HTML';
    public const RETURNFORMAT_JSON = 'JSON';
    public const RETURNFORMAT_TEXT = 'TXT';
    public const RETURNFORMAT_XML = 'XML';

    /**
     * @var Application_AjaxHandler
     */
    protected $handler;

    /**
     * @var Application_Request
     */
    protected $request;

    /**
     * @var Application_Driver
     */
    protected $driver;

    /**
     * @var Application_User
     */
    protected $user;

    /**
     * @var Application_CORS
     */
    protected $CORS;

    protected $supportedFormats = array();

    public function __construct(Application_AjaxHandler $handler)
    {
        $driver = Application_Driver::getInstance();

        $this->handler = $handler;
        $this->request = $handler->getRequest();
        $this->user = $driver->getUser();
        $this->driver = $driver;

        $formats = array(
            self::RETURNFORMAT_HTML,
            self::RETURNFORMAT_JSON,
            self::RETURNFORMAT_TEXT,
            self::RETURNFORMAT_XML
        );

        $this->CORS = new Application_CORS();

        $this->initCORS();
        $this->init();
        
        foreach ($formats as $format) {
            $method = 'process' . $format;
            if (method_exists($this, $method)) {
                $this->supportedFormats[$format] = $method;
            }
        }

        // initialize cross-domain requests
        $this->CORS->init();
    }
    
   /**
    * Can be extended to handle things done after constructing the instance.
    */
    protected function init()
    {
    }

   /**
    * Can be extended to handle CORS configuration
    * specific to any ajax method.
    */
    protected function initCORS()
    {
    }
    
    protected function setReturnFormatHTML() : void
    {
        $this->format = self::RETURNFORMAT_HTML;
    }

    public function getID() : string
    {
        return getClassTypeName($this);
    }

    public function isFormatSupported(string $formatName) : bool
    {
        return isset($this->supportedFormats[$formatName]);
    }

    protected $format;

    public function process(string $formatName) : void
    {
        $this->startSimulation();
        
        if (!$this->isFormatSupported($formatName)) {
            throw new Application_Exception(
                'AJAX Handling configuration error',
                'Tried processing a format not supported by the ajax method'
            );
        }

        try
        {
            $this->format = $formatName;
            
            $this->validateRequest();
            
            $method = $this->supportedFormats[$formatName];
            $this->$method();
        } 
        catch(Exception $e) 
        {
            if($this->isSimulationEnabled() || isDevelMode()) {
                displayError($e);
            }
            
            $this->sendError(
                $e->getMessage(),
                $this->exception2array($e),
                $e->getCode()
            );
        }
    }
    
   /**
    * @param Throwable $e
    * @return array<string,mixed>
    */
    protected function exception2array(Throwable $e) : array
    {
        $details = '';
        $eid = null;
        
        if($e instanceof Application_Exception)
        {
            $eid = $e->getID();
            $details .= $e->getDeveloperInfo();
            $details = str_replace('<br/>', " ", $details);
            $details = strip_tags($details);
        }
        
        $info = ConvertHelper::throwable2info($e);
        
        $data = array(
            'isExceptionData' => 'yes',
            'eid' => $eid,
            'class' => get_class($e),
            'file' => FileHelper::relativizePath($e->getFile(), APP_ROOT),
            'line' => $e->getLine(),
            'details' => $details,
            'trace' => $info->toString(),
            'previous' => null
        );
        
        if($info->hasPrevious())
        {
            $data['previous'] = $this->exception2array($e->getPrevious());
        }
        
        return $data;
    }

    public function getSupportedFormats() : array
    {
        return array_keys($this->supportedFormats);
    }

    /**
     * @param array<string|int,mixed>|string|NULL $data
     * @return never
     */
    protected function sendResponse($data = null)
    {
        if(DBHelper::isTransactionStarted()) {
            $this->endTransaction();
        }
        
        if($this->isSimulationEnabled()) {
            Application::log(' ');
            Application::log('------------------------------------------------');
            Application::log('RESPONSE');
            Application::log('------------------------------------------------');
            Application::log(' ');
            Application::log(sprintf('Response format: %s ', $this->format));
            Application::log(' ');
            echo '<pre>'.print_r($data, true).'</pre>';
            $this->endSimulation();
            Application::exit();
        }
        
        $method = 'send' . $this->format . 'Response';
        if (method_exists($this, $method)) {
            $this->$method($data);
            Application::exit();
        }

        header('HTTP/1.1 200');
        Application::exit();
    }

    /**
     * @param string $html
     * @return never
     */
    protected function sendHTMLResponse(string $html)
    {
        Application_Request::sendHTML($html);
        Application::exit();
    }
    
    /**
     * Sends a JSON response with a success state and the specified
     * data payload.
     *
     * @param array $data
     */
    protected function sendJSONResponse(array $data) : void
    {
        $this->sendJSON(self::formatJSONResponse($data));
    }

    /**
     * @param array $data
     * @return string
     */
    public static function formatJSONResponse(array $data) : string
    {
        $request = Application_Request::getInstance();
        
        return json_encode(
            array(
                'state' => 'success',
                'request_uri' => str_replace('&amp;', '&', $request->buildRefreshURL(array(), array('_loadkeys'))),
                'data' => $data
            ),
            JSON_THROW_ON_ERROR
        );
    }

    /**
     * @param string $message
     * @param string|array|null $data
     * @param int|null $code
     * @return array{state:string,message:string,code:int|null,data:array<string,mixed>}
     */
    public static function formatJSONError(string $message, $data=null, ?int $code=null) : array
    {
        if(is_string($data)) {
            $data = array(
                'details' => $data,
                'trace' => ''
            );
        }
        
        return array(
            'state' => 'error',
            'message' => $message,
            'code' => $code,
            'data' => $data
        );
    }

    public static function formatJSONException(Exception $e)
    {
        $devinfo = null;
        if($e instanceof Application_Exception) {
            $devinfo = $e->getDeveloperInfo();
        }
        
        return self::formatJSONError(
            'Exception: ' . $e->getMessage(),
            $devinfo,
            $e->getCode()
        );
    }

    /**
     * @param string $message
     * @param array|NULL $data
     * @param int|NULL $code
     * @return never
     */
    protected function sendError(string $message, ?array $data=null, ?int $code=null)
    {
        // fallback to avoid deadlocks calling the same method 
        if(empty($this->format)) {
            $this->format = 'JSON';
        }
        
        $method = 'send' . $this->format . 'Error';
        if (method_exists($this, $method)) {
            $this->$method($message, $data, $code);
            Application::exit();
        }
        
        if($code !== null) {
            $message = 'Error #'.$code.': '.$message;
        }

        header('HTTP/1.1 500 ' . $message);
        Application::exit();
    }
    
   /**
    * Sends a generic error response for unknown specified elements,
    * for example when a specified element ID does not match a valid
    * element.
    *  
    * @param string $elementLabel The label of the element type, for example <code>product type</code>
    * @param array|NULL $data Data to include in the response
    * @param int|NULL $code
    * @return never
    */
    protected function sendErrorUnknownElement(string $elementLabel, ?array $data=null, ?int $code=null)
    {
        $this->sendError(t('Unknown %1$s specified.', $elementLabel), $data, $code);
    }

    /**
     * @param string $message
     * @param array|null $data
     * @param int|null $code
     * @throws JsonException
     */
    protected function sendJSONError(string $message, ?array $data=null, ?int $code=null) : void
    {
        $json = json_encode(self::formatJSONError($message, $data, $code), JSON_THROW_ON_ERROR);

        $this->sendJSON($json);
    }

    /**
     * Sends a raw JSON string as response. Use the {@link sendJSONResponse()}
     * method if you want to send a regularly formatted response.
     *
     * @param string $json
     * @return never
     */
    protected function sendJSON(string $json)
    {
        if($this->isSimulationEnabled()) {
            Application::log('Response', true);
            Application::log(sprintf('Response format: %s ', $this->format));
            echo '<pre>'.print_r($json, true).'</pre>';
            $this->endSimulation();
            Application::exit();
        }
        
        Application_Request::sendJSON($json);
        Application::exit();
    }

    /**
     * @var bool
     */
    protected bool $debug = false;

    public function enableDebug() : void
    {
        $this->debug = true;
    }

    protected function log(string $message) : void
    {
        Application::log(sprintf(
            'AjaxMethod [%1$s] | %2$s',
            $this->getID(),
            $message
        ));
    }

   /**
    * Checks whether simulation mode is active, which can be enabled by
    * setting the <code>simulate_only</code> request parameter to <code>yes</code>.
    * The use that is logged in additionally needs to be a developer for this
    * to work.
    * 
    * @return boolean
    */
    protected function isSimulationEnabled() : bool
    {
        return Application::isSimulation();
    }
    
   /**
    * Overrides the request parameter <code>simulate_only</code> and enables
    * the simulation mode.
    * 
    * @return boolean
    */
    protected function forceStartSimulation() : bool
    {
        $this->request->setParam(Application::REQUEST_VAR_SIMULATION, 'yes');
        return $this->startSimulation();
    }

    /**
     * @var bool
     */
    protected $simulationStarted = false;
    
   /**
    * If the simulation mode is active, starts the simulation mode which
    * echos all application log messages. Includes a dump of the current 
    * request variables for debugging purposes. 
    *  
    * @return boolean
    * @see endSimulation()
    */
    protected function startSimulation() : bool
    {
        if(!$this->isSimulationEnabled()) {
            return false;
        }
        
        if($this->simulationStarted) {
            return true;
        }
        
        $this->simulationStarted = true;
        
        if(!headers_sent()) {
            header('Content-Type:text/html; charset=UTF-8');
        }
       
        $logger = AppFactory::createLogger();
        $logger->enableHTML();
        $logger->logModeEcho();
        
        Application::logHeader('Simulation mode active');
        Application::log('Request variables:');
        Application::logData($_REQUEST);
        Application::log('');
        Application::log('<a href="'.$this->getPermalink().'">Permalink for this request</a>');
        Application::log('');
        return true;
    }
    
    protected function getPermalink() : string
    {
        $vars = $_REQUEST;
        if(isset($vars['_loadkeys'])) {
            unset($vars['_loadkeys']);
        }
        
        return APP_URL.'/ajax/?'.http_build_query($vars);
    }
    
   /**
    * Ends the simulation output and exits the script, but only
    * if simulation is active.
    * 
    * @see startSimulation()
    */
    protected function endSimulation() : void
    {
        if(!$this->isSimulationEnabled()) {
            return;
        }
        
        Application::log('All done.', true);
        Application::exit();
    }
    
   /**
    * Utility method: alias for starting a DBHelper transaction.
    * @see endTransaction()
    */
    protected function startTransaction() : void
    {
        DBHelper::startTransaction();
    }

   /**
    * Utility method: ends a previously opened DBHelper transaction,
    * rolling back the transaction if simulation mode is enabled, or
    * committing it otherwise.
    * 
    * @see startTransaction()
    */
    protected function endTransaction() : void
    {
        if($this->isSimulationEnabled()) {
            DBHelper::rollbackTransaction();
            return;
        }
        
        DBHelper::commitTransaction();
    }
    
   /**
    * Called before the processXXX() method, to validate
    * any request variables.
    *
    * @return void
    */
    protected function validateRequest()
    {
        
    }

    /**
     * Ensures that a country has been specified in the request,
     * and returns its instance.
     *
     * @return Application_Countries_Country
     * @throws Application_Exception_DisposableDisposed
     * @throws DBHelper_Exception
     * @throws Request_Exception
     */
    protected function requireCountry() : Application_Countries_Country
    {
        $country = Application_Countries::getInstance()->getByRequest();

        if($country !== null)
        {
            return $country;
        }

        $this->sendErrorUnknownElement(t('Country'));
    }
}
