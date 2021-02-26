<?php
/**
 * File containing the {@see Connectors_Connector_Method_Put} class.
 *
 * @package Connectors
 * @see Connectors_Connector_Method_Put
 */

declare(strict_types=1);

/**
 * Base class for POST API methods.
 *
 * @package Connectors
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Connectors_Connector_Method_Put extends Connectors_Connector_Method
{
    public function getHTTPMethod() : string
    {
        return HTTP_Request2::METHOD_PUT;
    }
}
