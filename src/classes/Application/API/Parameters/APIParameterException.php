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
    public const int ERROR_REMOVED_PARAM_IS_REGISTERED = 183107;
    public const int ERROR_REMOVED_PARAM_RESERVED_NAME = 183108;
    public const int ERROR_REMOVED_PARAM_ALREADY_DECLARED = 183109;
    public const int ERROR_INVALID_REMOVED_PARAM_CONFIG = 183110;
}
