<?php
/**
 * File containing the {@see Connectors_Connector_Method_Get} class.
 * 
 * @package Connectors
 * @see Connectors_Connector_Method_Get
 */

declare(strict_types=1);

use Connectors\Connector\BaseConnectorMethod;

/**
 * Base class for GET API methods.
 *  
 * @package Connectors
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Connectors_Connector_Method_Get extends BaseConnectorMethod
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
