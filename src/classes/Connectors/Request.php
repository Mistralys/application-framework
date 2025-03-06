<?php
/**
 * @package Connectors
 * @see Connectors_Request
 */

declare(strict_types=1);

use Application\Exception\UnexpectedInstanceException;
use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper\JSONConverter\JSONConverterException;
use AppUtils\FileHelper_Exception;
use Connectors\Request\RequestSerializer;
use function AppUtils\parseURL;

/**
 * Handles all information for a request to send to a
 * remote API service.
 *
 * @package Connectors
 * @subpackage Request
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Connectors_Request implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    public const ERROR_INVALID_AUTH_SCHEME = 339001;
    public const ERROR_REQUEST_FAILED = 339002;
    public const ERROR_NO_REQUEST_SENT_YET = 339005;
    public const ERROR_INVALID_DATA_VALUE = 339006;
    public const ERROR_INVALID_METHOD = 339008;

    protected string $url;
    protected int $timeoutSeconds = 0;
    protected Connectors_Connector $connector;
    protected string $body = '';
    protected string $HTTPMethod = HTTP_Request2::METHOD_POST;
    protected ?HTTP_Request2 $request = null;
    private string $id;
    protected Connectors_Request_Cache $cache;
    protected Connectors_Response $response;
    protected float $timeStart = 0.0;
    protected float $timeTaken = 0.0;

    /**
     * @var array<string,string>
     */
    protected array $postData = array();

    /**
     * @var array<string,string>
     */
    protected array $getData = array();

    /**
     * @var array<string,string>
     */
    protected array $headers = array();

    /**
     * @var array<string,string>|NULL
     */
    protected ?array $auth = null;

    /**
     * @var array<string,string>|NULL
     */
    protected ?array $proxyConfig = null;

    /**
     * @var string[]
     */
    private static array $validHTTPMethods = array(
        HTTP_Request2::METHOD_POST,
        HTTP_Request2::METHOD_GET,
        HTTP_Request2::METHOD_DELETE,
        HTTP_Request2::METHOD_PUT
    );

    /**
     * @param Connectors_Connector $connector
     * @param string $url The URL to the API endpoint
     * @param array<string,mixed> $postData POST data to send with the request
     * @param array<string,mixed> $getData GET data to append the URL
     * @param string|null $id Request ID, used when restoring from serialized data.
     *
     * @throws Application_Exception
     * @throws Connectors_Exception
     */
    public function __construct(Connectors_Connector $connector, string $url, array $postData=array(), array $getData=array(), ?string $id=null)
    {
        // Remove any GET parameters from the target URL, and
        // merge them into the get data collection. We use only
        // the base, parameterless URL for the endpoint.
        $info = parseURL($url);
        $getData = array_merge($info->getParams(), $getData);

        $names = array_keys($getData);
        foreach($names as $name)
        {
            $info->removeParam($name);
        }

        $this->url = $info->getNormalized();
        $this->connector = $connector;
        $this->cache = new Connectors_Request_Cache($this);
        $this->id = $id ?? $this->createID();

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
     * @return string
     *
     * @throws Connectors_Exception
     * @throws JSONConverterException
     */
    public function serialize() : string
    {
        return RequestSerializer::serialize($this);
    }

    /**
     * Restores a request instance from a serialized package
     * created with {@see Connectors_Request::serialize()}.
     *
     * LIMITATIONS: Proxy and authentication data are not
     * persisted, so if used, the request object will not be
     * directly usable.
     *
     * @param string $json
     * @return Connectors_Request|NULL Can be null if the data is invalid, or obsolete.
     *
     * @throws Application_Exception
     * @throws Connectors_Exception
     * @throws JSONConverterException
     * @throws UnexpectedInstanceException
     */
    public static function unserialize(string $json) : ?Connectors_Request
    {
        return RequestSerializer::unserialize($json);
    }

    /**
     * Unique request ID.
     *
     * @return string
     */
    public function getID() : string
    {
        return $this->id;
    }

    /**
     * Creates a unique request ID.
     *
     * @return string
     * @throws Application_Exception
     */
    private function createID() : string
    {
        $user = Application::getUser();

        return $user->getID().'-'.microtime(true);
    }

   /**
    * Tells the request to use authentication in the requested URL.
    * 
    * @param string $user
    * @param string $password
    * @return $this
    */
    public function useAuth(string $user, string $password) : self
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
    * @throws Connectors_Exception
    * @return $this
    */
    public function useProxy(string $host, string $port, string $user, string $password, string $authScheme=HTTP_Request2::AUTH_DIGEST) : self
    {
        $this->log(sprintf('Enabling use of the proxy [%s].', $host));
        
        $validAuths = array(HTTP_Request2::AUTH_BASIC, HTTP_Request2::AUTH_DIGEST);
        
        if(!in_array($authScheme, $validAuths, true))
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

    /**
     * The base URL as specified as target URL for the request. Can contain
     * parameters if they were included.
     *
     * @return string
     */
    public function getBaseURL() : string
    {
        return $this->url;
    }

    /**
     * Retrieves the full request URL including all GET request parameters.
     *
     * @return string
     * @throws Connectors_Exception
     */
    public function getRequestURL() : string
    {
        return $this->buildURL();
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
    * @throws Connectors_Exception
    * @return $this
    */
    public function setHTTPMethod(string $method) : self
    {

        if(!in_array($method, self::$validHTTPMethods, true))
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
    public function makeGET() : self
    {
        return $this->setHTTPMethod(HTTP_Request2::METHOD_GET);
    }

    /**
     * @return $this
     * @throws Connectors_Exception
     */
    public function makePOST() : self
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
    * @return $this
    */
    public function setTimeout(int $seconds) : self
    {
        $this->timeoutSeconds = $seconds;
        return $this;
    }
    
   /**
    * Sets the body of the request to send.
    * 
    * @param string $content
    * @return $this
    */
    public function setBody(string $content) : self
    {
        $this->body = $content;
        
        return $this;
    }

    /**
     * @param string $mime
     * @return $this
     */
    public function setContentType(string $mime) : self
    {
        return $this->setHeader('Content-Type', $mime);
    }
    
   /**
    * Gives the request the <code>multipart/form-data</code>
    * content type for file uploads.
    * 
    * @return $this
    */
    public function makeMultipart() : self
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
    * Sets an HTTP header to send with the request. Overwrites
    * existing headers.
    * 
    * @param string $name
    * @param string $value
    * @return $this
    */
    public function setHeader(string $name, string $value) : self
    {
        $this->headers[$name] = $value;
        
        return $this;
    }

    public function getCache() : Connectors_Request_Cache
    {
        return $this->cache;
    }

    /**
     * Requests data from a SPIN API method.
     *
     * @return Connectors_Response
     *
     * @throws Connectors_Exception
     * @throws HTTP_Request2_Exception
     * @throws HTTP_Request2_LogicException
     * @throws FileHelper_Exception
     */
    public function getData() : Connectors_Response
    {
        $url = $this->buildURL();
        
        $this->log(sprintf('Fetching data for URL: [%s].', $url));

        $this->handleRequestStarted();

        // Bypass the request entirely if the cache is valid.
        if($this->cache->isValid())
        {
            $this->log('Cache | Cache is valid, fetching cached response.');

            $response = $this->cache->fetchResponse();
            if($response !== null) {
                return $response;
            }

            $this->log('Cache | No response returned, must have been invalid.');
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
     * @throws HTTP_Request2_MessageException
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

        $info = parseURL($url);

        foreach($this->getData as $name => $value) {
            $info->setParam($name, $value);
        }

        return $info->getNormalized();
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
                ConvertHelper::time2string($this->timeoutSeconds)
            ));
            
            $req->setConfig('timeout', $this->timeoutSeconds);
        }
        
        if($this->isPUT() || $this->isPOST())
        {
            $totalSize = 0;

            if(!empty($this->postData))
            {
                foreach($this->postData as $param => $value)
                {
                    $req->addPostParameter($param, $value);
                }

                $dataSize = strlen(serialize($this->postData));
                $totalSize += $dataSize;
                
                $this->log(sprintf('Also sending data keys via POST: [%s].', implode(', ', array_keys($this->postData))));
                $this->log(sprintf('POST data length: [%s] characters (approximate).', number_format($dataSize, 0, '.', ' ')));
            }
            
            if(!empty($this->body))
            {
                $req->setBody($this->body);

                $bodySize = strlen($this->body);
                $totalSize += $bodySize;

                $this->log('Also sending a body via POST.');
                $this->log(sprintf('POST body length: [%s] characters.', number_format($bodySize, 0, '.', ' ')));
            }

            $this->log(sprintf(
                'POST total size: [%s] characters (~%s) (approximate)',
                number_format($totalSize, 0, '.', ' '),
                ConvertHelper::bytes2readable($totalSize)
            ));

            $this->log('POST maximum size: [%s]', ini_get('post_max_size'));
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
    * @var array<string,array<int,int>>
    */
    protected array $codesByMethod = array(
        HTTP_Request2::METHOD_GET => array(200),
        HTTP_Request2::METHOD_DELETE => array(200, 204),
        HTTP_Request2::METHOD_PUT => array(201, 202),
        HTTP_Request2::METHOD_POST => array(200, 201, 202)
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
        if(isset($this->codesByMethod[$method]))
        {
            return $this->codesByMethod[$method];
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
    public function setPOSTData(string $name, $value) : self
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
        
        $this->postData[$name] = (string)$value;
        return $this;
    }

    /**
     * @return array<string,string>
     */
    public function getGetData() : array
    {
        return $this->getData;
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
     * @throws Connectors_Exception
     */
    public function setGETData(string $name, $value) : self
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
        
        $this->getData[$name] = (string)$value;
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
    public function setCacheEnabled(bool $enabled=true, int $durationSeconds = 0) : self
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
     * @return array<mixed>
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


    /**
     * @return int Timeout duration in seconds.
     */
    public function getTimeout() : int
    {
        return $this->timeoutSeconds;
    }
}
