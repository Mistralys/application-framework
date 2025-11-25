<?php

declare(strict_types=1);

namespace Application\API\Clients\Keys;

use Application\API\Clients\APIClientException;

class APIKeyException extends APIClientException
{
    public const int API_KEY_PARAM_CANNOT_BE_OPTIONAL = 187401;
}
