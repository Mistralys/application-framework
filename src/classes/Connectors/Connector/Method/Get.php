<?php
/**
 * File containing the {@see Connectors_Connector_Method_Get} class.
 * 
 * @package Connectors
 * @see Connectors_Connector_Method_Get
 */

declare(strict_types=1);

/**
 * Base class for GET API methods.
 *  
 * @package Connectors
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Connectors_Connector_Method_Get extends Connectors_Connector_Method
{
    public function getHTTPMethod() : string
    {
        return HTTP_Request2::METHOD_GET;
    }
    
    public function getValidResponseCodes()
    {
        return array(200);
    }
}
