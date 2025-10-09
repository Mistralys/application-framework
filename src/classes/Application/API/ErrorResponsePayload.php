<?php

declare(strict_types=1);

namespace Application\API;

use AppUtils\ArrayDataCollection;

class ErrorResponsePayload extends ArrayDataCollection
{
    public const string KEY_ERROR_CODE = 'errorCode';
    public const string KEY_ERROR_MESSAGE = 'errorMessage';
    public const string KEY_ERROR_DATA = 'errorData';

    public function __construct(ErrorResponse $response)
    {
        parent::__construct(array(
            self::KEY_ERROR_CODE => $response->getErrorCode(),
            self::KEY_ERROR_MESSAGE => $response->getErrorMessage(),
            self::KEY_ERROR_DATA => $response->getErrorData(),
        ));
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
