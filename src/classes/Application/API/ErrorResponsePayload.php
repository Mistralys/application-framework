<?php

declare(strict_types=1);

namespace Application\API;

use Application\API\Response\ResponseInterface;
use AppUtils\ArrayDataCollection;
use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\Interfaces\StringableInterface;

class ErrorResponsePayload extends ArrayDataCollection implements ResponseInterface, StringableInterface
{
    public const string KEY_ERROR_CODE = 'errorCode';
    public const string KEY_ERROR_MESSAGE = 'errorMessage';
    public const string KEY_ERROR_DATA = 'errorData';

    private APIMethodInterface $method;
    private int $httpStatusCode;

    public function __construct(ErrorResponse $response)
    {
        $this->method = $response->getMethod();
        $this->httpStatusCode = $response->getHttpStatusCode();

        parent::__construct(array(
            self::KEY_ERROR_CODE => $response->getErrorCode(),
            self::KEY_ERROR_MESSAGE => $response->getErrorMessage(),
            self::KEY_ERROR_DATA => $response->getErrorData(),
        ));
    }

    public function getMethod() : APIMethodInterface
    {
        return $this->method;
    }

    /**
     * The HTTP status code this error response was (or would be) sent
     * with, e.g. `400` for a bad request or `500` for an internal server
     * error — see {@see ErrorResponse::getHttpStatusCode()}. Not part of
     * the serialized array data: this is a testing/introspection accessor
     * for code using {@see \Application\API\BaseMethods\BaseAPIMethod::processReturn()},
     * where the actual HTTP header is never sent.
     *
     * @return int
     */
    public function getHttpStatusCode() : int
    {
        return $this->httpStatusCode;
    }

    public function getErrorCode() : int
    {
        return $this->getInt(self::KEY_ERROR_CODE);
    }

    public function getErrorMessage() : string
    {
        return $this->getString(self::KEY_ERROR_MESSAGE);
    }

    /**
     * @return array<string, mixed>
     */
    public function getErrorData() : array
    {
        return $this->getArray(self::KEY_ERROR_DATA);
    }

    public function getAsString() : string
    {
        return (string)$this;
    }

    public function __toString() : string
    {
        return 'Erroneous API response'.PHP_EOL.
        'Code: #'.$this->getErrorCode().' '.PHP_EOL.
        'Message: '.$this->getErrorMessage().PHP_EOL.
        'Data: '.PHP_EOL.
        JSONConverter::var2json($this->getErrorData(), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
    }
}
