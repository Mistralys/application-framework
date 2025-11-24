<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\Flavors;

use Application\API\Parameters\APIParameterInterface;
use Connectors\Headers\HTTPHeadersBasket;

/**
 * Interface for API parameters that are passed via HTTP headers,
 * instead of query parameters or request body.
 *
 * Implementing this interface automatically documents the parameter
 * as a header parameter in the API documentation, and its value will
 * be automatically retrieved using {@see self::getHeaderValue()} instead
 * of {@see BaseAPIParameter::resolveValue()}.
 *
 * The parameter is responsible for the concrete implementation of
 * how to get the header value.
 *
 * @package API
 * @subpackage Parameters
 */
interface APIHeaderParameterInterface extends APIParameterInterface
{
    /**
     * The name of the HTTP header used to pass this parameter.
     * Used for documentation purposes only.
     * @return string
     */
    public function getHeaderExample() : string;

    /**
     * Gets the value of the header, if it is present in the request.
     * @return string|NULL
     */
    public function getHeaderValue() : ?string;

    /**
     * Sets the header to the specified value for testing and documentation.
     * @param HTTPHeadersBasket $headers
     * @param string $value
     * @return self
     */
    public function injectHeaderForValue(HTTPHeadersBasket $headers, string $value) : self;
}
