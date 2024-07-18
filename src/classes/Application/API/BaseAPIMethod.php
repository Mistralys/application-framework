<?php

declare(strict_types=1);

use AppUtils\Request\RequestParam;

abstract class BaseAPIMethod
{
    public const ERROR_NO_OUTPUT_METHODS = 59213001;
    public const ERROR_INVALID_INPUT_FORMAT = 59213002;
    public const ERROR_INVALID_OUTPUT_FORMAT = 59213003;
    public const ERROR_NO_INPUT_METHODS = 59213004;

    public const PARAM_API_VERSION = 'apiVersion';

    public const DATA_TYPE_JSON = 'json';

    protected Application_Driver $driver;
    protected Application_Request $request;
    protected Application_API $api;
    protected string $version;
    protected Application_CORS $CORS;

    public function __construct(Application_API $api)
    {
        $this->api = $api;
        $this->driver = Application_Driver::getInstance();
        $this->request = $this->driver->getRequest();
        $this->version = $this->getCurrentVersion();
        $this->CORS = new Application_CORS();

        $this->configure();

        // initialize cross-domain requests
        $this->CORS->init();
    }

   /**
    * Retrieves an indexed array containing available API
    * version numbers that can be specified to work with.
    * 
    * @return string[]
    */
    abstract public function getVersions() : array;

   /**
    * Retrieves the current version of the API endpoint.
    * 
    * @return string
    */
    abstract public function getCurrentVersion() : string;

    public function process() : void
    {
        $this->validate();
        $this->processInput();
        $this->processOutput();
    }

    abstract protected function processInput() : void;
    abstract protected function processOutput() : void;

    /**
     * Used to give a method the opportunity to configure request
     * parameters before it is processed. Note: use the
     * {@link registerParam()} method to register parameters.
     *
     * @see registerParam()
     */
    abstract protected function configure() : void;

    /**
     * List of reserved API Method request parameters.
     * @var array
     */
    protected array $reservedParams = array(
        'method',
        'input',
        'output',
        self::PARAM_API_VERSION
    );

    /**
     * @var array<string,Application_API_Parameter>
     */
    protected array $params = array();

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
     * @param bool $required
     * @param string|NULL $description
     * @return RequestParam
     * @throws Application_Exception
     */
    protected function registerParam(string $name, string $label, bool $required = false, ?string $description = null) : RequestParam
    {
        if (in_array($name, $this->reservedParams, true)) {
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
     * @param string|NULL $default
     * @return string
     */
    protected function getParam(string $name, ?string $default = null) : ?string
    {
        $value = (string)$this->request->getParam($name, $default);
        if($value !== '') {
            return $value;
        }

        return null;
    }

    /**
     * Returns the ID of the method (its name)
     * @return string
     */
    abstract public function getID() : string;

    /**
     * Validates the parameters of the method to make sure all required
     * request parameters are present and valid.
     *
     * @throws Application_Exception
     */
    protected function validate() : void
    {
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

    protected $responseData;

    /**
     * @param $format
     * @param $data
     * @return never
     */
    protected function sendResponse($format, $data = null)
    {
        if($this->isSimulation()) {
            Application::log('RESPONSE', true);
            $this->log(sprintf('Response format: [%s]', $format));
            $this->log('Data to send:');
            echo '<pre>';
            echo print_r($data, true);
            echo '</pre>';
            Application::exit('Sent API response.');
        }
        
        $method = 'serveResponse_' . $format;
        if (method_exists($this, $method)) {
            $this->$method($data);
            exit;
        }

        http_response_code(Connectors_ResponseCode::HTTP_OK);
        Application::exit();
    }

    private function serveResponse_json(array $data) : void
    {
        $json = json_encode(array(
            self::PARAM_API_VERSION => $this->version,
            'state' => 'success',
            'data' => $data
        ));
        $this->serveJSON($json);
    }

    protected function sendJSONError($message, $responseCode = Connectors_ResponseCode::HTTP_BAD_REQUEST)
    {
        $this->sendError(self::DATA_TYPE_JSON, $message, $responseCode);
    }

    protected function sendJSONResponse($data)
    {
        $this->sendResponse(self::DATA_TYPE_JSON, $data);
    }

    protected function sendError($format, $message, int $responseCode = Connectors_ResponseCode::HTTP_BAD_REQUEST)
    {
        http_response_code($responseCode);

        $method = 'serveError_' . $format;
        if (method_exists($this, $method)) {
            $this->$method($message);
            exit;
        }

        header('HTTP/1.1 ' . $responseCode . ' ' . $message);
        exit;
    }

    protected function serveError_json($message)
    {
        $json = json_encode(array(
            self::PARAM_API_VERSION => $this->version,
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
    * @return BaseAPIMethod
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