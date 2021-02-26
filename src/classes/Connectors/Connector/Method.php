<?php
/**
 * File containing the {@see Connectors_Connector_Method} class.
 *
 * @package Connectors
 * @see Connectors_Connector_Method
 */

declare(strict_types=1);

/**
 * Base class for connector methods.
 *
 * @package Connectors
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Connectors_Connector_Method implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

   /**
    * @var Connectors_Connector
    */
    protected $connector;

   /**
    * @var Connectors_Request_URL|NULL
    */
    protected $activeRequest;
    
   /**
    * @var Connectors_Response|NULL
    */
    protected $activeResponse;
    
    public function __construct(Connectors_Connector $connector)
    {
        $this->connector = $connector;
    }
    
   /**
    * Retrieves the ID of the method.
    * @return string
    */
    public function getID() : string
    {
        $tokens = explode('_', get_class($this));
        return array_pop($tokens);
    }
    
   /**
    * Retrieves the type of HTTP method used to communicate with the server.
    * @return string
    */
    abstract public function getHTTPMethod() : string;
    
   /**
    * Creates a request instance configured for the type
    * of method.
    * 
    * @param string $endpoint The endpoint to call: this is appended to the API URL.
    * @return Connectors_Request_URL
    */
    protected function createRequest(string $endpoint='') : Connectors_Request_URL
    {
        $request = new Connectors_Request_URL(
            $this->connector,
            $this->connector->getURL().'/'.$endpoint
        );
        
        $request->setHTTPMethod($this->getHTTPMethod());
        
        return $request;
    }
    
   /**
    * Executes the specified request, and returns the response.
    * Stores the request and response internally to be able to
    * access them after the fact.
    * 
    * @param Connectors_Request_URL $request
    * @return Connectors_Response
    */
    protected function executeRequest(Connectors_Request_URL $request) : Connectors_Response
    {
        $this->activeRequest = $request;
        $this->activeResponse = $request->getData();
        
        return $this->activeResponse;
    }
    
   /**
    * Executes a request for the specified endpoint, creating the 
    * request instance automatically. The request cannot be configured
    * further before it is sent - this is meant to be used for requests
    * that have no parameters.
    * 
    * @param string $endpoint
    * @return Connectors_Response
    */
    protected function executeRequestByName(string $endpoint) : Connectors_Response
    {
        $request = $this->createRequest($endpoint);
        
        return $this->executeRequest($request);
    }

    public function getLogIdentifier(): string
    {
        return sprintf(
            '%s | Method [%s:%s]',
            $this->connector->getLogIdentifier(),
            $this->getHTTPMethod(),
            $this->getID()
        );
    }
}
