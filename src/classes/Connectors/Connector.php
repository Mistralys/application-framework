<?php
/**
 * File containing the {@link Connectors_Connector} class.
 *
 * @package Connectors
 * @see Connectors_Connector
 */

/**
 * Base class for connector implementations: offers a number
 * of utility methods that can be used by the individual
 * connectors, and defines the common interface that connectors
 * have to conform to.
 *
 * @package Connectors
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Connectors_Connector implements Application_Interfaces_Simulatable, Application_Interfaces_Loggable
{
    use Application_Traits_Simulatable;
    use Application_Traits_Loggable;

    public const ERROR_NO_ACTIVE_RESPONSE_AVAILABLE = 42401;
    
    protected ?string $cachedID = null;

    /**
     * @var array<string,string>
     */
    protected $params = array();

    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * @var Connectors_Response|null
     */
    protected $activeResponse;

    public function __construct()
    {
        $this->checkRequirements();
        $this->init();
    }

    /**
     * Overridable in child classes.
     */
    protected function init() : void
    {
        
    }
    
   /**
    * Checks if live requests are enabled. They are enabled
    * by default, but turned off in simulation mode. 
    * 
    * With the <code>live-requests</code> boolean request
    * parameter, they can be turned on explicitly. 
    * 
    * @return bool
    */
    public function isLiveRequestsEnabled() : bool
    {
        if(!$this->isSimulationEnabled())
        {
            return true;
        }
        
        return Application_Request::getInstance()->getBool('live-requests');
    }

   /**
    * Retrieves the connector's ID (name). e.g. <code>Editor</code>.
    * This is the name of the connector file without the extension 
    * (case sensitive).
    * 
    * @return string
    */
    public function getID() : string
    {
        if(!isset($this->cachedID)) {
            $this->cachedID = getClassTypeName($this);
        }
        
        return $this->cachedID;
    }

    // region: Abstract methods

    /**
     * Check that all requirements for requests are met.
     *
     * @throws Connectors_Exception
     */
    abstract protected function checkRequirements() : void;

    /**
     * Retrieves the URL to connect to.
     *
     * @return string
     */
    abstract public function getURL() : string;

    // endregion
    
   /**
    * Creates a new request with the possibility to add parameters
    * that are added as a query string. Expects the endpoint to
    * accept a method parameter for the method to call.
    * 
    * @param string $method The endpoint method to call, added as a GET parameter in the request URL
    * @param array $postData The data that will be sent along via POST
    * @param array $getData GET data to append to the URL
    * @return Connectors_Request_Method
    */
    protected function createMethodRequest(string $method, array $postData=array(), array $getData=array())
    {
        $request = new Connectors_Request_Method($this, $this->getURL(), $method, $postData, $getData);
        return $request;
    }
    
   /**
    * Creates a new request to a specific target url path.
    * 
    * @param string $url
    * @param array $postData
    * @param array $getData
    * @return Connectors_Request_URL
    */
    protected function createURLRequest(string $url, array $postData=array(), array $getData=array())
    {
        $request = new Connectors_Request_URL($this, $url, $postData, $getData);
        return $request;
    }
    
   /**
    * Generic utility method to directly retrieve the results of
    * a method request.
    * 
    * @param string $method
    * @param array $postData
    * @throws Application_Exception
    * @return array|bool
    */
    protected function getMethodData(string $method, array $postData=array())
    {
        return $this->fetchResponse($this->createMethodRequest($method, $postData, $this->params));
    }

    /**
     * @param string $url
     * @param array $postData
     * @return array|false
     * @throws Application_Exception
     */
    protected function getURLData(string $url, array $postData=array())
    {
        return $this->fetchResponse($this->createURLRequest($url, $postData, $this->params));
    }

    /**
     * @param Connectors_Request $request
     * @return array|false
     * @throws Application_Exception
     */
    protected function fetchResponse(Connectors_Request $request)
    {
        $this->activeResponse = $request->getData();
        
        if(!$this->activeResponse->isError()) 
        {
            return $this->activeResponse->getData();
        }
        
        return false;
    }
    
   /**
    * Retrieves the response object from the last request.
    * @return Connectors_Response|null
    */
    public function getActiveResponse() : ?Connectors_Response
    {
        return $this->activeResponse;
    }

    public function requireActiveResponse() : Connectors_Response
    {
        if(isset($this->activeResponse))
        {
            return $this->activeResponse;
        }

        throw new Connectors_Exception(
            $this,
            'No active response available.',
           'Cannot get active response, none has been stored.',
           self::ERROR_NO_ACTIVE_RESPONSE_AVAILABLE
        );
    }
    
   /**
    * Adds a parameter to be added to the target URL
    * that the request will call. This is separate
    * from the data array provided to {@link getData()},
    * which is sent via POST.
    * 
    * @param string $name
    * @param string|int|float $value
    * @return Connectors_Connector  
    */
    public function addParam($name, $value)
    {
        $this->params[$name] = strval($value);
        return $this;
    }
    
    /**
     * @param bool $state
     * @return $this
     */
    public function setDebug(bool $state=true)
    {
        $this->debug = $state;
        return $this;
    }
    
    public function getLogIdentifier() : string
    {
        return 'Connector ['.$this->getID().']';
    }
    
   /**
    * Creates a new connector method instance, which is
    * loaded for the current connector type. The class name
    * follows this scheme:
    * 
    * <code>Connectors_Connector_(ConnectorName)_Method_(MethodName)</code>
    * 
    * @param string $name
    * @return Connectors_Connector_Method
    */
    public function createMethod(string $name) : Connectors_Connector_Method
    {
        $class = sprintf(
            'Connectors_Connector_%s_Method_%s',
            $this->getID(),
            $name
        );
        
        return new $class($this);
    }
}
