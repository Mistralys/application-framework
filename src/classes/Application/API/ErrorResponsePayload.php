<?php

declare(strict_types=1);

namespace Application\API;

use Application\API\Response\ResponseInterface;
use AppUtils\ArrayDataCollection;

class ErrorResponsePayload extends ArrayDataCollection implements ResponseInterface
{
    public const string KEY_ERROR_CODE = 'errorCode';
    public const string KEY_ERROR_MESSAGE = 'errorMessage';
    public const string KEY_ERROR_DATA = 'errorData';

    private APIMethodInterface $method;

    public function __construct(ErrorResponse $response)
    {
        $this->method = $response->getMethod();

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

    public function getErrorCode() : int
    {
        return $this->getInt(self::KEY_ERROR_CODE);
    }

    public function getErrorMessage() : string
    {
        return $this->getString(self::KEY_ERROR_MESSAGE);
    }

    public function getErrorData() : array
    {
        return $this->getArray(self::KEY_ERROR_DATA);
    }
}
