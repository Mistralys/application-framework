<?php

require_once 'Application/CORS.php';

abstract class Application_API_Method
{
    /**
     * @var Application_Driver
     */
    protected $driver;

    /**
     * @var Application_Request
     */
    protected $request;

    /**
     * @var Application_API
     */
    protected $api;

    protected $version;

    const ERROR_NO_OUTPUT_METHODS = 59213001;

    const ERROR_INVALID_INPUT_FORMAT = 59213002;

    const ERROR_INVALID_OUTPUT_FORMAT = 59213003;

    const ERROR_NO_INPUT_METHODS = 59213004;

    const PROCESS_MODE_REQUEST = 'request';
    
    const PROCESS_MODE_RETURN = 'return';
    
   /**
    * @var Application_CORS
    */
    protected $CORS;
    
    protected $processMode;
    
    public function __construct(Application_API $api)
    {
        $this->api = $api;
        $this->driver = Application_Driver::getInstance();
        $this->request = $this->driver->getRequest();
        $this->version = $this->getCurrentVersion();
        $this->CORS = new Application_CORS();
        $this->processMode = self::PROCESS_MODE_REQUEST;

        $this->configure();

        // initialize cross-domain requests
        $this->CORS->init();
    }

    abstract public function getDefaultInputFormat();

    abstract public function getDefaultOutputFormat();

   /**
    * Retrieves an indexed array containing available API
    * version numbers that can be specified to work with.
    * 
    * @return string[]
    */
    abstract public function getVersions();

   /**
    * Retrieves the current version of the API endpoint.
    * 
    * @return string
    */
    abstract public function getCurrentVersion();

    public function setProcessMode($mode)
    {
        if(in_array($mode, array(self::PROCESS_MODE_REQUEST, self::PROCESS_MODE_RETURN))) {
            $this->processMode = $mode;
        }
        
        return $this;
    }
    
    public function process()
    {
        if (!isset($this->inputFormat)) {
            $this->selectInputFormat($this->getDefaultInputFormat());
        }

        if (!isset($this->outputFormat)) {
            $this->selectOutputFormat($this->getDefaultOutputFormat());
        }

        $this->validate();

        $inputMethod = 'input_' . $this->inputFormat;
        $this->$inputMethod();

        $outputMethod = 'output_' . $this->outputFormat;
        $this->$outputMethod();
        
        if($this->processMode == self::PROCESS_MODE_RETURN) {
            return $this->responseData;
        }
    }

    private $inputFormats;

    /**
     * Retrieves an indexed array with format names supported
     * by this method. Throws an exception if the method has
     * no input formats at all.
     *
     * @throws Application_Exception
     * @return array
     */
    public function getInputFormats()
    {
        if (isset($this->inputFormats)) {
            return $this->inputFormats;
        }

        $this->inputFormats = array();
        $methods = get_class_methods($this);
        foreach ($methods as $method) {
            if (substr($method, 0, 6) == 'input_') {
                $this->inputFormats[] = substr($method, 6);
            }
        }

        if (empty($this->inputFormats)) {
            throw new Application_Exception(
                'No input formats available',
                sprintf(
                    'The method [%1$s] has no input methods.',
                    $this->getID()
                ),
                self::ERROR_NO_INPUT_METHODS
            );
        }

        return $this->inputFormats;
    }

    private $outputFormats;

    /**
     * Retrieves an indexed array with format names supported
     * by this method. Throws an exception if the method has
     * no output formats at all.
     *
     * @throws Application_Exception
     * @return array
     */
    public function getOutputFormats()
    {
        if (isset($this->outputFormats)) {
            return $this->outputFormats;
        }

        $this->outputFormats = array();
        $methods = get_class_methods($this);
        foreach ($methods as $method) {
            if (substr($method, 0, 7) == 'output_') {
                $this->outputFormats[] = substr($method, 7);
            }
        }

        if (empty($this->outputFormats)) {
            throw new Application_Exception(
                'No output formats available',
                sprintf(
                    'The method [%1$s] has no output methods.',
                    $this->getID()
                ),
                self::ERROR_NO_OUTPUT_METHODS
            );
        }

        return $this->outputFormats;
    }

    protected $inputFormat;

    /**
     * Selects the input format to use. Must be a supported format
     * for the method, triggers an exception otherwise.
     *
     * @param string $format
     * @throws Application_Exception
     */
    public function selectInputFormat($format)
    {
        $formats = $this->getInputFormats();
        if (!in_array($format, $formats)) {
            throw new Application_Exception(
                'Invalid input format specified',
                sprintf(
                    'The input format [%1$s] is not a valid format for method [%2$s]. Valid input formats are: [%3$s].',
                    $format,
                    $this->getID(),
                    implode(', ', $formats)
                ),
                self::ERROR_INVALID_INPUT_FORMAT
            );
        }

        $this->inputFormat = $format;
    }

    protected $outputFormat;

    /**
     * Selects the output format to use. Must be a supported format
     * for the method, triggers an exception otherwise.
     *
     * @param string $format
     * @throws Application_Exception
     */
    public function selectOutputFormat($format)
    {
        $formats = $this->getOutputFormats();
        if (!in_array($format, $formats)) {
            throw new Application_Exception(
                'Invalid output format specified',
                sprintf(
                    'The output format [%1$s] is not a valid format for method [%2$s]. Valid output formats are: [%3$s].',
                    $format,
                    $this->getID(),
                    implode(', ', $formats)
                ),
                self::ERROR_INVALID_OUTPUT_FORMAT
            );
        }

        $this->outputFormat = $format;
    }

    /**
     * Used to give a method the opportunity to configure request
     * parameters before it is processed. Note: use the
     * {@link registerParam()} method to register parameters.
     *
     * @see registerParam()
     */
    abstract protected function configure();

    /**
     * List of reserved API Method request parameters.
     * @var array
     */
    protected $reservedParams = array(
        'method',
        'input',
        'output',
        'api_version'
    );

    protected $params = array();

    /**
     * Registers an API parameter: this is used to be able to create
     * the reflection and methods discovery feature. Returns the request
     * parameter object which can then be used to specify the parameter's
     * validation method.
     *
     * Example:
     *
     * registerParam('myparam', 'My Parameter')->setInteger();
     *
     * @param string $name
     * @param string $label
     * @param string $description
     * @return AppUtils\Request_Param
     */
    protected function registerParam($name, $label, $required = false, $description = null)
    {
        require_once 'Application/API/Parameter.php';

        if (in_array($name, $this->reservedParams)) {
            throw new Application_Exception(
                'Tried registering a reserved parameter',
                sprintf(
                    'The parameter [%1$s] is a reserved parameter, method [%2$s] may not register it for itself.',
                    $name,
                    $this->getID()
                )
            );
        }

        if (isset($this->params[$name])) {
            throw new Application_Exception(
                'Duplicate parameters',
                sprintf(
                    'Cannot register the same parameter [%1$s] again for method [%2$s].',
                    $name,
                    $this->getID()
                )
            );
        }

        $rparam = $this->request->registerParam($name);
        $param = new Application_API_Parameter($rparam, $label, $required, $description);
        $this->params[$name] = $param;

        return $rparam;
    }

    /**
     * Retrieves the value of a request parameter. Alias for the
     * request's getParam Method.
     *
     * @param string $name
     * @param string $default
     * @return string
     */
    protected function getParam($name, $default = null)
    {
        return $this->request->getParam($name, $default);
    }

    protected $id;

    /**
     * Returns the ID of the method (its name)
     * @return string
     */
    public function getID()
    {
        if (isset($this->id)) {
            return $this->id;
        }

        // since the method's class name depends on the
        // class repository from which it has been loaded,
        // we simply strip all relevant class names.
        $classes = array_keys($this->api->getRepositories());
        $search = array();
        $replace = array();
        foreach ($classes as $class) {
            $search[] = $class . '_';
            $replace[] = '';
        }

        $this->id = str_replace(
            $search,
            $replace,
            get_class($this)
        );

        return $this->id;
    }

    /**
     * Validates the parameters of the method to make sure all required
     * request parameters are present and valid.
     *
     * @throws Application_Exception
     */
    protected function validate()
    {
        /* @var $param Application_API_Parameter */

        foreach ($this->params as $param) {
            if ($param->isRequired() && !$this->request->hasParam($param->getName())) {
                throw new Application_Exception(
                    'Missing parameter',
                    sprintf(
                        'The parameter [%1$s] is required for the method [%2$s].',
                        $param->getName(),
                        $this->getID()
                    )
                );
            }
        }
    }
    
    protected function isSimulation() : bool
    {
        return Application::isSimulation();
    }

    const DATA_TYPE_JSON = 'json';

    protected $responseData;
    
    protected function sendResponse($format, $data = null)
    {
        if($this->processMode == self::PROCESS_MODE_RETURN) {
            $this->responseData = $data;
            return;
        }
        
        if($this->isSimulation()) {
            Application::log('RESPONSE', true);
            $this->log(sprintf('Response format: [%s]', $format));
            $this->log('Data to send:');
            echo '<pre>';
            print_r($data);
            echo '</pre>';
            exit;
        }
        
        $method = 'serveResponse_' . $format;
        if (method_exists($this, $method)) {
            $this->$method($data);
            exit;
        }

        header('HTTP/1.1 200');
        exit;
    }

    protected function serveResponse_json($data)
    {
        $json = json_encode(array(
            'api_version' => $this->version,
            'state' => 'success',
            'data' => $data
        ));
        $this->serveJSON($json);
    }

    protected function sendJSONError($message)
    {
        $this->sendError(self::DATA_TYPE_JSON, $message);
    }

    protected function sendJSONResponse($data)
    {
        $this->sendResponse(self::DATA_TYPE_JSON, $data);
    }

    protected function sendError($format, $message)
    {
        $method = 'serveError_' . $format;
        if (method_exists($this, $method)) {
            $this->$method($message);
            exit;
        }

        header('HTTP/1.1 500 ' . $message);
        exit;
    }

    protected function serveError_json($message)
    {
        $json = json_encode(array(
            'api_version' => $this->version,
            'state' => 'error',
            'message' => $message
        ));
        $this->serveJSON($json);
    }

    protected function serveJSON($json)
    {
        if (!is_string($json)) {
            $json = json_encode($json);
        }

        header('Content-Type: application/json');
        echo $json;
        exit;
    }

   /**
    * Adds a domain name to the list of allowed cross-origin
    * request sources. Adding one of these enables CORS for
    * this API endpoint.
    *
    * Note: use the wildcard <code>*</code> as domain to enable
    * all cross origin sources.
    *
    * @param string $domain
    * @return Application_API_Method
    */
    protected function allowCORSDomain($domain)
    {
        $this->CORS->allowDomain($domain);
        return $this;
    }
    
    protected function log($message)
    {
        Application::log(sprintf(
            'API Method [%s] | %s',
            $this->getID(),
            $message
        ));
    }
}