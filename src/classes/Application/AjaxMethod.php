<?php

abstract class Application_AjaxMethod
{
    public const ERROR_MALFORMED_JSON_DATA = 554001;
    
    const RETURNFORMAT_HTML = 'HTML';
    const RETURNFORMAT_JSON = 'JSON';
    const RETURNFORMAT_TEXT = 'TXT';
    const RETURNFORMAT_XML = 'XML';

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
    
    protected function setReturnFormatHTML()
    {
        $this->format = self::RETURNFORMAT_HTML;
    }

    public function getID()
    {
        $tokens = explode('_', get_class($this));

        return array_pop($tokens);
    }

    public function isFormatSupported($formatName)
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
        
        $info = \AppUtils\ConvertHelper::throwable2info($e);
        
        $data = array(
            'isExceptionData' => 'yes',
            'eid' => $eid,
            'class' => get_class($e),
            'file' => \AppUtils\FileHelper::relativizePath($e->getFile(), APP_ROOT),
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

    public function getSupportedFormats()
    {
        return array_keys($this->supportedFormats);
    }

    /**
     * @param array<string|int,mixed>|string|NULL $data
     * @return never-returns
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
    
    protected function sendHTMLResponse($html)
    {
        Application_Request::sendHTML($html);
    }
    
    /**
     * Sends a JSON response with a success state and the specified
     * data payload.
     *
     * @param mixed $data
     */
    protected function sendJSONResponse($data)
    {
        $this->sendJSON($this->formatJSONResponse($data));
    }
    
    public static function formatJSONResponse($data)
    {
        $request = Application_Request::getInstance();
        
        $json = json_encode(array(
            'state' => 'success',
            'request_uri' => str_replace('&amp;', '&', $request->buildRefreshURL(array(), array('_loadkeys'))),
            'data' => $data
        ));
        
        if($json===false) {
            return self::formatJSONError(
                'The response data contains malformed JSON data',
                'Json encoder error message: '.json_last_error_msg(),
                self::ERROR_MALFORMED_JSON_DATA
            );
        }
        
        return $json;
    }
    
    public static function formatJSONError($message, $data=null, $code=null)
    {
        if(is_string($data)) {
            $data = array(
                'details' => $data,
                'trace' => ''
            );
        }
        
        $response = array(
            'state' => 'error',
            'message' => $message,
            'code' => $code,
            'data' => $data
        );
        
        return $response;
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
     * @return never-returns
     */
    protected function sendError(string $message, ?array $data=null, ?int $code=null) : void
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
        
        if(!empty($code)) {
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
    * @return never-returns
    */
    protected function sendErrorUnknownElement(string $elementLabel, ?array $data=null, ?int $code=null) : void
    {
        $this->sendError(t('Unknown %1$s specified.', $elementLabel), $data, $code);
    }

    protected function sendJSONError($message, $data=null, $code=null)
    {
        $this->sendJSON(self::formatJSONError($message, $data, $code));
    }

    /**
     * Sends a raw JSON string as response. Use the {@link sendJSONResponse()}
     * method if you want to send a regularly formatted response.
     *
     * @param string $json
     */
    protected function sendJSON($json)
    {
        if($this->isSimulationEnabled()) {
            Application::log('Response', true);
            Application::log(sprintf('Response format: %s ', $this->format));
            echo '<pre>'.print_r($json, true).'</pre>';
            $this->endSimulation();
            Application::exit();
        }
        
        Application_Request::sendJSON($json);
    }

    protected $debug = false;

    public function enableDebug()
    {
        $this->debug = true;
    }

    protected function log($message)
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
    protected function forceStartSimulation()
    {
        $this->request->setParam('simulate_only', 'yes');
        return $this->startSimulation();
    }
    
    protected $simulationStarted = false;
    
   /**
    * If the simulation mode is active, starts the simulation mode which
    * echos all application log messages. Includes a dump of the current 
    * request variables for debugging purposes. 
    *  
    * @return boolean
    * @see endSimulation()
    */
    protected function startSimulation()
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
       
        $logger = Application::getLogger();
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
    
    protected function getPermalink()
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
    protected function endSimulation()
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
    protected function startTransaction()
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
    protected function endTransaction()
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
    */
    protected function validateRequest()
    {
        
    }
}