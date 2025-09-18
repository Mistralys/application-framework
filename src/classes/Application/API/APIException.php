<?php

declare(strict_types=1);

namespace Application\API;

use Application_Exception;

class APIException extends Application_Exception
{
    public const int ERROR_INVALID_OUTPUT_FORMAT = 59213003;
    public const int ERROR_NO_OUTPUT_METHODS = 59213001;
    public const int ERROR_INVALID_INPUT_FORMAT = 59213002;
    public const int ERROR_NO_INPUT_METHODS = 59213004;
    public const int ERROR_METHOD_NOT_IN_INDEX = 59213005;
}
