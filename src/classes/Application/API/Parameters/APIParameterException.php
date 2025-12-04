<?php

declare(strict_types=1);

namespace Application\API\Parameters;

use Application\API\APIException;

class APIParameterException extends APIException
{
    public const int ERROR_PARAM_ALREADY_REGISTERED = 183101;
    public const int ERROR_RESERVED_PARAM_NAME = 183102;
    public const int ERROR_INVALID_PARAM_CONFIG = 183103;
    public const int ERROR_INVALID_PARAM_VALUE = 183104;
    public const int ERROR_PARAM_NOT_REGISTERED = 183106;
}
