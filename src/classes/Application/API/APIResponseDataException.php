<?php
/**
 * @package API
 * @subpackage Core
 */

declare(strict_types=1);

namespace Application\API;

use AppUtils\ArrayDataCollection;
use Exception;

/**
 * Special exception used when processing an API method using the
 * {@see APIMethodInterface::processReturn()} method. It is used
 * to halt the processing and return the response data through
 * the exception.
 *
 * **WARNING**: This exception should not be used for any other purpose.
 *
 * @package API
 * @subpackage Core
 */
class APIResponseDataException extends Exception
{
    public const int CODE_API_METHOD_RETURN_EXCEPTION = 182901;
    private APIMethodInterface $method;
    private ResponsePayload|ErrorResponsePayload $responseData;

    public function __construct(APIMethodInterface $method, ResponsePayload|ErrorResponsePayload $responseData)
    {
        $this->method = $method;
        $this->responseData = $responseData;

        parent::__construct(
            'API method return exception',
            self::CODE_API_METHOD_RETURN_EXCEPTION
        );
    }

    public function getMethod(): APIMethodInterface
    {
        return $this->method;
    }

    public function getResponseData(): ResponsePayload|ErrorResponsePayload
    {
        return $this->responseData;
    }
}
