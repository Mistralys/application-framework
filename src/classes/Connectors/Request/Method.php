<?php
/**
 * File containing the {@see Connectors_Request_Method} class.
 *
 * @package Connectors
 * @see Connectors_Request_Method
 */

/**
 * Method request: handles requests to a specific
 * API endpoint method.
 *
 * @package Connectors
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Connectors_Request_Method extends Connectors_Request
{
    /**
     * @var string
     */
    protected $method;

    /**
     * @var string
     */
    protected $methodVar = 'method';

    /**
     * @param Connectors_Connector $connector
     * @param string $url The URL to the API endpoint
     * @param string $method The API method to call
     * @param array $postData POST parameters to send with the request
     * @param array $getData GET parameters to append to the request URL
     */
    public function __construct(Connectors_Connector $connector, string $url, string $method, array $postData = array(), array $getData = array())
    {
        parent::__construct($connector, $url, $postData, $getData);

        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getMethod() : string
    {
        return $this->method;
    }

    /**
     * @param string $varName
     * @return $this
     */
    public function setMethodVar(string $varName)
    {
        $this->methodVar = $varName;
        return $this;
    }
    
    public function getData() : Connectors_Response
    {
        $this->log(sprintf('Method: [%s]', $this->method));
        
        $this->setGETData($this->methodVar, $this->method);
        $this->setGETData(Application_Localization::REQUEST_PARAM_CONTENT_LOCALE, 'de_DE');
        $this->setGETData(Application_Localization::REQUEST_PARAM_APPLICATION_LOCALE, 'de_DE');
        
        return parent::getData();
    }

    protected function _getHashData() : array
    {
        return array(
            'method' => $this->method,
            'methodVar' => $this->methodVar
        );
    }
}
