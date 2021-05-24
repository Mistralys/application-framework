<?php

abstract class Connectors_Request implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    const ERROR_INVALID_AUTH_SCHEME = 339001;
    const ERROR_REQUEST_FAILED = 339002;
    const ERROR_JSON_PARSE_ERROR = 339003;
    const ERROR_UNEXPECTED_DATA_FORMAT = 339004;
    const ERROR_NO_REQUEST_SENT_YET = 339005;
    const ERROR_INVALID_DATA_VALUE = 339006;
    const ERROR_URL_MAY_NOT_CONTAIN_QUERY = 339007;
    const ERROR_INVALID_METHOD = 339008;

    /**
     * @var array<string,string>
     */
    protected $postData = array();

    /**
     * @var array<string,string>
     */
    protected $getData = array();

    /**
     * @var string
     */
    protected $url;

    /**
     * @var int
     */
    protected $timeoutSeconds = 0;
    
   /**
    * @var Connectors_Connector
    */
    protected $connector;
    
   /**
    * @var string
    */
    protected $body = '';
    
   /**
    * @var bool
    */
    protected $json = false;

    /**
     * @var string
     */
    protected $HTTPMethod = HTTP_Request2::METHOD_POST;
    
   /**
    * @var HTTP_Request2
    */
    protected $request;

    /**
     * @var string
     */
    protected $requestURL = '';

    /**
     * @var Connectors_Response
     */
    protected $response;

    /**
     * @var float
     */
    protected $timeStart = 0;

    /**
     * @var float
     */
    protected $timeTaken;

    /**
     * @var array<string,string>
     */
    protected $headers = array();

    /**
     * @var array<string,string>
     */
    protected $auth;

    /**
     * @var array<string,string>
     */
    protected $proxyConfig;

    /**
     * @var int
     */
    private $id = 0;

    /**
     * @var Connectors_Request_Cache
     */
    protected $cache;

    /**
    * @param Connectors_Connector $connector
    * @param string $url The URL to the API endpoint
    * @param array<string,mixed> $postData POST data to send with the request
    * @param array<string,mixed> $getData GET data to append the URL
    */
    public function __construct(Connectors_Connector $connector, string $url, array $postData=array(), array $getData=array())
    {
        $this->url = $url;
        $this->connector = $connector;
        $this->cache = new Connectors_Request_Cache($this);
        $this->id = $this->createID();
        
        if(!empty($postData)) {
            foreach($postData as $name => $value) {
                $this->setPOSTData($name, $value);
            }
        }
        
        if(!empty($getData)) {
            foreach($getData as $name => $value) {
                $this->setGETData($name, $value);
            }
        }
    }

    /**
     * Unique request ID.
     *
     * @return int
     */
    public function getID() : int
    {
        return $this->id;
    }

    /**
     * Creates a unique request ID.
     *
     * @return int
     * @throws Application_Exception
     */
    private function createID() : int
    {
        $counter = intval(Application_Driver::getSetting('request_counter', '0'));

        $counter++;

        Application_Driver::setSetting('request_counter', strval($counter));

        return $counter;
    }

   /**
    * Tells the request to use authentication in the requested URL.
    * 
    * @param string $user
    * @param string $password
    * @return $this
    */
    public function useAuth(string $user, string $password)
    {
        $this->log(sprintf('Enabling use of authentication with user [%s].', $user));
        
        $this->auth = array(
            'user' => $user,
            'password' => $password
        );
        
        return $this;
    }

   /**
    * Tells the request to use a proxy server.
    * 
    * @param string $host
    * @param string $port
    * @param string $user
    * @param string $password
    * @param string $authScheme
    * @throws Application_Exception
    * @return $this
    */
    public function useProxy(string $host, string $port, string $user, string $password, string $authScheme=HTTP_Request2::AUTH_DIGEST)
    {
        $this->log(sprintf('Enabling use of the proxy [%s].', $host));
        
        $validAuths = array(HTTP_Request2::AUTH_BASIC, HTTP_Request2::AUTH_DIGEST);
        
        if(!in_array($authScheme, $validAuths)) 
        {
            $ex = new Connectors_Exception(
                $this->connector,
                'Invalid authentication scheme',
                sprintf(
                    'The authentication scheme [%s] is not valid. Valid schemes are: [%s]',
                    $authScheme,
                    implode(', ', $validAuths)
                ),
                self::ERROR_INVALID_AUTH_SCHEME
            );
            
            $ex->setRequest($this);
            
            throw $ex;
        }
        
        $this->proxyConfig = array(
            'proxy_host' => $host,
            'proxy_port' => $port,
            'proxy_user' => $user,
            'proxy_password' => $password,
            'proxy_auth_scheme' => $authScheme
        );

        return $this;
    }

    public function getRequestURL() : string
    {
        $this->requireResponse();
        return $this->response->getURL();
    }
    
   /**
    * Gets the request instance (available after sending a request).
    * @return HTTP_Request2|NULL
    */
    public function getRequest() : ?HTTP_Request2
    {
        return $this->request;
    }

    /**
     * @throws Connectors_Exception
     */
    protected function requireResponse() : void
    {
        if(isset($this->response)) {
            return;
        }
        
        $ex = new Connectors_Exception(
            $this->connector,
            'No request sent yet',
            'Cannot access some information before the request has been sent using the [getData] method.',
            self::ERROR_NO_REQUEST_SENT_YET    
        );
        
        $ex->setRequest($this);
        
        throw $ex;
    }

    /**
     * @return float
     * @throws Connectors_Exception
     */
    public function getTimeTaken() : float
    {
        $this->requireResponse();
        
        return $this->timeTaken;
    }
    
   /**
    * Sets the method to use to send the request: either
    * POST or GET.
    * 
    * @param string $method
    * @throws Application_Exception
    * @return $this
    */
    public function setHTTPMethod(string $method)
    {
        $validTypes = array(
            HTTP_Request2::METHOD_POST, 
            HTTP_Request2::METHOD_GET,
            HTTP_Request2::METHOD_DELETE,
            HTTP_Request2::METHOD_PUT
        );
        
        if(!in_array($method, $validTypes)) 
        {
            $ex = new Connectors_Exception(
                $this->connector,
                sprintf('Invalid request method [%s]', $method),
                'The method [%s] is not a valid request method. Valid methods are available in the constants HTTP_Request2::METHOD_POST and HTTP_Request2::METHOD_GET.',
                self::ERROR_INVALID_METHOD
            );
            
            $ex->setRequest($this);
            
            throw $ex;
        }
        
        $this->HTTPMethod = $method;
        return $this;
    }

    /**
     * @return $this
     * @throws Application_Exception
     */
    public function makeGET()
    {
        return $this->setHTTPMethod(HTTP_Request2::METHOD_GET);
    }

    /**
     * @return $this
     * @throws Application_Exception
     */
    public function makePOST()
    {
        return $this->setHTTPMethod(HTTP_Request2::METHOD_POST);
    }
    
    public function isPOST() : bool
    {
        return $this->isHTTPMethod(HTTP_Request2::METHOD_POST);
    }
    
    public function isPUT() : bool
    {
        return $this->isHTTPMethod(HTTP_Request2::METHOD_PUT);
    }
    
    public function isGET() : bool
    {
        return $this->isHTTPMethod(HTTP_Request2::METHOD_GET);
    }
    
    public function isHTTPMethod(string $method) : bool
    {
        return $this->HTTPMethod === $method;
    }
    
    public function getPayload() : array
    {
        return array_merge($this->getData, $this->postData);
    }
    
    public function getHTTPMethod() : string
    {
        return $this->HTTPMethod;
    }
    
   /**
    * Sets the timeout for the request in seconds.
    * Set to 0 for no limit.
    * 
    * @param int $seconds
    * @return Connectors_Request
    */
    public function setTimeout($seconds)
    {
        $this->timeoutSeconds = $seconds;
        return $this;
    }
    
   /**
    * Sets the body of the request to send.
    * 
    * @param string $content
    * @return Connectors_Request
    */
    public function setBody(string $content) : Connectors_Request
    {
        $this->body = $content;
        
        return $this;
    }

    /**
     * @param string $mime
     * @return $this
     */
    public function setContentType(string $mime)
    {
        return $this->setHeader('Content-Type', $mime);
    }
    
   /**
    * Gives the request the <code>multipart/form-data</code>
    * content type for file uploads.
    * 
    * @return $this
    */
    public function makeMultipart()
    {
        return $this->setContentType('multipart/form-data; boundary='.$this->getBoundary());
    }

    /**
     * @return array<string,string>
     */
    public function getHeaders() : array
    {
        return $this->headers;
    }
    
   /**
    * Retrieves the string that is used to separate mime boundaries.
    * 
    * @return string
    */
    public function getBoundary() : string
    {
        return md5('connectors-multipart-request-boundary');
    }
    
   /**
    * Sets a HTTP header to send with the request. Overwrites
    * existing headers.
    * 
    * @param string $name
    * @param string $value
    * @return $this
    */
    public function setHeader(string $name, string $value)
    {
        $this->headers[$name] = $value;
        
        return $this;
    }

   /**
    * Requests data from a SPIN API method.
    *
    * @throws Application_Exception
    * @return Connectors_Response
    */
    public function getData() : Connectors_Response
    {
        $url = $this->buildURL();
        
        $this->log(sprintf('Fetching data for URL: [%s].', $url));

        $this->handleRequestStarted();

        // Bypass the request entirely if the cache is valid.
        if($this->cache->isValid())
        {
            $this->log('Cache is valid, fetching cached response.');
            return $this->cache->fetchResponse();
        }

        $this->log('Cache is not valid, fetching live data.');

        $request = $this->createRequest();
        
        if(!$this->connector->isLiveRequestsEnabled())
        {
            $result = $this->simulateResponse($request);
        }
        else 
        {
            $this->log('Sending the live request.');
            $result = $request->send();
        }
    
        $this->handleRequestCompleted();

        $response = $this->createResponse($result);

        // Store the response in the cache, if applicable.
        $this->cache->storeResponse($response);

        return $response;
    }
    
   /**
    * Creates a dummy response that matches a valid response code
    * for the specified request method.
    * 
    * @param HTTP_Request2 $request
    * @return HTTP_Request2_Response
    */
    protected function simulateResponse(HTTP_Request2 $request) : HTTP_Request2_Response
    {   
        $this->log('Simulation mode | Creating a simulated response.');
            
        $responseCode = 200;
        
        if(isset($this->codesByMethod[$this->HTTPMethod])) 
        {
            $responseCode = $this->codesByMethod[$this->HTTPMethod][0];
        }
        
        return new HTTP_Request2_Response(
            'HTTP/1.0 '.$responseCode.' Simulation OK', 
            true, 
            $request->getUrl()->getURL()
        );
    }
    
    protected function handleRequestStarted() : void
    {
        $this->timeStart = microtime(true);
    }
    
    protected function handleRequestCompleted() : void
    {
        $end = microtime(true);
        
        $this->timeTaken = $end - $this->timeStart;
        
        $this->log(sprintf('Time taken: [%s].', number_format($this->timeTaken, 4)));
    }
    
   /**
    * Builds the full URL for the request, appending any
    * GET variables that may have been added.
    * 
    * @throws Connectors_Exception If the URL is invalid.
    * @return string
    */
    protected function buildURL() : string
    {
        $url = $this->url;
        $getData = $this->getData;
        
        if(empty($getData)) {
            return $url;
        }
        
        // check that the URL does not already contain a query
        // string, since we're adding one ourselves.
        if(strstr($url, '?') || strstr($url, '&')) 
        {
            $ex = new Connectors_Exception(
                $this->connector,
                'Invalid URL',
                sprintf(
                    'The base URL for the connector may not already contain a query string. '.
                    'If you need to add GET parameters to the URL, there are methods to add them.'
                ),
                self::ERROR_URL_MAY_NOT_CONTAIN_QUERY
            );
            
            $ex->setRequest($this);
            
            throw $ex;
        }
            
        $url .= '?' . http_build_query($getData, '', '&');
        
        return $url;
    }

   /**
    * Creates a connector response instance from an HTTP_Request2
    * response object. 
    * 
    * @param HTTP_Request2_Response $response
    * @throws Connectors_Exception If the response code is not valid for the type of HTTP method.
    * @return Connectors_Response
    */
    protected function createResponse(HTTP_Request2_Response $response) : Connectors_Response
    {
        if(!$this->isValidResponseCode($response->getStatus())) {
            throw $this->createResponseException($response);
        }
        
        $this->response = new Connectors_Response($this, $response);
        
        return $this->response;
    }

    private function createResponseException(HTTP_Request2_Response $response) : Connectors_Exception
    {
        $body = $response->getBody();

        $ex = new Connectors_Exception(
            $this->connector,
            'Remote API request failed, invalid response code.',
            sprintf(
                'Got response code [%s], valid response codes are [%s].'.PHP_EOL.
                'Response message is [%s].'.PHP_EOL.
                'Accessed from API endpoint at [%s].'.PHP_EOL.
                'Response body (%s characters):'.PHP_EOL.
                '%s',
                $response->getStatus(),
                implode(', ', $this->getCodesByMethod($this->HTTPMethod)),
                $response->getReasonPhrase(),
                $response->getEffectiveUrl(),
                strlen($body),
                $body
            ),
            self::ERROR_REQUEST_FAILED
        );

        $ex->setResponse($response);
        $ex->setRequest($this);

        return $ex;
    }

    /**
     * Creates and configures the HTTP request instance used
     * to send the request.
     *
     * @return HTTP_Request2
     * @throws Connectors_Exception
     * @throws HTTP_Request2_LogicException
     */
    protected function createRequest() : HTTP_Request2
    {
        $this->log('Creating and configuring request.');
        $this->log(sprintf('HTTP request method: [%s].', $this->HTTPMethod));
        $this->log(sprintf('Simulation mode: [%s]', bool2string($this->connector->isSimulationEnabled())));
        $this->log(sprintf('Live requests: [%s] (turn on in simulation mode with parameter live-requests=yes)', bool2string($this->connector->isLiveRequestsEnabled())));
        
        $req = new HTTP_Request2($this->buildURL(), $this->HTTPMethod);
        $req->setAdapter('curl');
        $req->setConfig('follow_redirects', true);
        $req->setConfig('ssl_verify_peer', false);
        $req->setConfig('ssl_verify_host', false);
        
        if(!empty($this->headers))
        {
            foreach($this->headers as $name => $value)
            {
                $req->setHeader($name, $value);
                
                $this->log(sprintf(
                    'Using header [%s] with value [%s].',
                    $name,
                    $value
                ));
            }
        }
        
        if(isset($this->timeoutSeconds))
        {
            $this->log(sprintf(
                'Timeout is set to %s seconds (%s).',
                $this->timeoutSeconds,
                AppUtils\ConvertHelper::time2string($this->timeoutSeconds)
            ));
            
            $req->setConfig('timeout', $this->timeoutSeconds);
        }
        
        if($this->isPUT() || $this->isPOST())
        {
            if(!empty($this->postData))
            {
                foreach($this->postData as $param => $value)
                {
                    $req->addPostParameter($param, $value);
                }
                
                $this->log(sprintf('Also sending data keys via post: [%s].', implode(', ', array_keys($this->postData))));
            }
            
            if(!empty($this->body))
            {
                $req->setBody($this->body);
                
                $this->log(sprintf('Also sending a body via post, of length [%s].', mb_strlen($this->body)));
            }
        }
        
        // when not in a development environment, make sure
        // we use the proxy to connect to the target URL.
        if(isset($this->proxyConfig) && !Application::isDevelEnvironment())
        {
            $req->setConfig($this->proxyConfig);
            
            $this->log(sprintf('Proxy is enabled via host [%s].', $this->proxyConfig['proxy_host']));
        }
        
        if(isset($this->auth))
        {
            $req->setAuth($this->auth['user'], $this->auth['password']);
            
            $this->log(sprintf('Using authentication with user [%s].', $this->auth['user']));
        }
        
        $this->request = $req;
        
        return $req;
    }
    
   /**
    * Valid HTTP response codes by request method.
    * @var array[string]array
    */
    protected $codesByMethod = array(
        HTTP_Request2::METHOD_GET => array(200),
        HTTP_Request2::METHOD_DELETE => array(200, 204),
        HTTP_Request2::METHOD_PUT => array(201),
        HTTP_Request2::METHOD_POST => array(200, 202)
    );
    
   /**
    * Checks whether the specified response code is
    * valid for the currently configured HTTP request
    * method.
    *  
    * @param int $code
    * @return boolean
    */
    public function isValidResponseCode(int $code) : bool
    {
        $codes = $this->getCodesByMethod($this->HTTPMethod);

        return in_array($code, $codes);
    }

    /**
     * @param string $method
     * @return int[]
     *
     * @see HTTP_Request2::METHOD_GET
     * @see HTTP_Request2::METHOD_DELETE
     * @see HTTP_Request2::METHOD_POST
     * @see HTTP_Request2::METHOD_PUT
     */
    public function getCodesByMethod(string $method) : array
    {
        if(isset($this->codesByMethod[$this->HTTPMethod]))
        {
            return $this->codesByMethod[$this->HTTPMethod];
        }

        return array(200);
    }
    
    public function getConnector() : Connectors_Connector
    {
        return $this->connector;
    }
    
    public function getLogIdentifier() : string
    {
        return sprintf(
            'Connector [%s] | Request',
            $this->connector->getID()
        );
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     * @throws Connectors_Exception
     */
    public function setPOSTData(string $name, $value)
    {
        if(!is_string($value) && !is_numeric($value)) 
        {
            $ex = new Connectors_Exception(
                $this->connector,
                'Invalid data value',
                sprintf(
                    'The data key value [%s] must be a string or a number. Complex types like [%s] cannot be used.',
                    $name,
                    gettype($value)    
                ),
                self::ERROR_INVALID_DATA_VALUE
            );
            
            $ex->setRequest($this);
            
            throw $ex;
        }
        
        $this->postData[$name] = strval($value);
        return $this;
    }
    
   /**
    * Retrieves the data to be sent via post, if any.
    * @return array<string,string>
    */
    public function getPostData() : array
    {
        return $this->postData;
    }
    
   /**
    * Sets/adds a GET parameter value.
    * 
    * @param string $name
    * @param mixed $value
    * @return $this
    */
    public function setGETData(string $name, $value)
    {
        if(!is_string($value) && !is_numeric($value)) 
        {
            $ex = new Connectors_Exception(
                $this->connector,
                'Invalid data value',
                sprintf(
                    'The data key value [%s] must be a string or a number. Complex types like [%s] cannot be used.',
                    $name,
                    gettype($value)    
                ),
                self::ERROR_INVALID_DATA_VALUE
            );
            
            $ex->setRequest($this);
            
            throw $ex;
        }
        
        $this->getData[$name] = strval($value);
        return $this;
    }
    
    public function getBody() : string
    {
        if(isset($this->request)) 
        {
            return (string)$this->request->getBody();
        }
        
        return '';
    }

    /**
     * Enables or disabled the caching of the request for
     * the specified duration. If enabled, the request will
     * not be sent but the cached data used instead, as long
     * as its age does not exceed the duration.
     *
     * @param bool $enabled
     * @param int $durationSeconds
     * @return $this
     */
    public function setCacheEnabled(bool $enabled=true, int $durationSeconds)
    {
        $this->cache->setEnabled($enabled, $durationSeconds);
        return $this;
    }

    public function getCacheHash() : string
    {
        return md5(serialize($this->getHashData()));
    }

    /**
     * Collects all internal data relevant to identify
     * individual requests to create a hash for caching
     * purposes.
     *
     * @return mixed[]
     */
    protected function getHashData() : array
    {
        $data = array(
            $this->getPayload(),
            $this->getHTTPMethod(),
            $this->getHeaders(),
            $this->url
        );

        return array_merge($data, $this->_getHashData());
    }

    abstract protected function _getHashData() : array;
}
