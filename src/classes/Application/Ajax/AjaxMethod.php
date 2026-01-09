<?php
/**
 * @package Application
 * @subpackage AJAX
 */

declare(strict_types=1);

use Application\Ajax\AjaxMethodInterface;
use Application\Ajax\BaseHTMLAjaxMethod;
use Application\Ajax\BaseJSONAjaxMethod;
use Application\AppFactory;
use Application\Application;
use Application\Disposables\DisposableDisposedException;
use AppUtils\ArrayDataCollection;
use AppUtils\ConvertHelper;
use AppUtils\FileHelper;
use AppUtils\Request_Exception;

/**
 * Abstract base class for AJAX methods.
 *
 * @package Application
 * @subpackage AJAX
 *
 * @see Application_Bootstrap_Screen_Ajax
 */
abstract class Application_AjaxMethod implements AjaxMethodInterface
{
    protected Application_AjaxHandler $handler;
    protected Application_Request $request;
    protected Application_Driver $driver;
    protected Application_User $user;
    protected Application_CORS $CORS;

    /**
     * @var array<string,string>
     */
    protected array $supportedFormats = array();

    public function __construct(Application_AjaxHandler $handler)
    {
        $driver = Application_Driver::getInstance();

        $this->handler = $handler;
        $this->request = $handler->getRequest();
        $this->user = $driver->getUser();
        $this->driver = $driver;

        $this->CORS = new Application_CORS();

        $this->initCORS();
        $this->init();
        
        foreach (AjaxMethodInterface::RETURN_FORMATS as $format) {
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
        $this->format = AjaxMethodInterface::RETURNFORMAT_HTML;
    }

    public function getID() : string
    {
        return getClassTypeName($this);
    }

    public function isFormatSupported(string $formatName) : bool
    {
        return isset($this->supportedFormats[$formatName]);
    }

    protected string $format = '';

    /**
     * @param string $formatName
     * @return void
     * @throws Application_Exception
     * @see BaseHTMLAjaxMethod::processHTML()
     * @see BaseJSONAjaxMethod::processJSON()
     */
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
     * @param ArrayDataCollection|array<string|int,mixed>|string|NULL $data
     * @return never
     */
    protected function sendResponse($data = null)
    {
        if(DBHelper::isTransactionStarted()) {
            $this->endTransaction();
        }

        if($data instanceof ArrayDataCollection) {
            $data = $data->getData();
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
                AjaxMethodInterface::PAYLOAD_STATE => AjaxMethodInterface::STATE_SUCCESS,
                AjaxMethodInterface::PAYLOAD_REQUEST_URI => str_replace('&amp;', '&', $request->buildRefreshURL(array(), array('_loadkeys'))),
                AjaxMethodInterface::PAYLOAD_DATA => $data
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
            AjaxMethodInterface::PAYLOAD_STATE => AjaxMethodInterface::STATE_ERROR,
            AjaxMethodInterface::PAYLOAD_ERROR_MESSAGE => $message,
            AjaxMethodInterface::PAYLOAD_ERROR_CODE => $code,
            AjaxMethodInterface::PAYLOAD_DATA => $data
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
            $this->format = AjaxMethodInterface::RETURNFORMAT_HTML;
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
    * for example, when a specified element ID does not match a valid
    * element.
    *  
    * @param string $elementLabel The label of the element type, for example `product type`.
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

    /**
     * @return $this
     */
    public function enableDebug(bool $enable=true) : self
    {
        $this->debug = $enable;
        return $this;
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
    * Checks whether simulation mode is active.
    * See {@see Application::isSimulation()} for details.
    * 
    * @return boolean
    * @see Application::isSimulation()
    */
    protected function isSimulationEnabled() : bool
    {
        return Application::isSimulation();
    }
    
   /**
    * Overrides the request parameter {@see Application::REQUEST_VAR_SIMULATION}
    * and enables the simulation mode.
    * 
    * @return boolean
    */
    protected function forceStartSimulation() : bool
    {
        $this->request->setParam(Application::REQUEST_VAR_SIMULATION, 'yes');
        return $this->startSimulation();
    }

    protected bool $simulationStarted = false;
    
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
     * @throws DisposableDisposedException
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
