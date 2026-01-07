<?php

declare(strict_types=1);

namespace Application\API\Clients;

use Application\Exception\ApplicationException;

class APIClientException extends ApplicationException
{
    public const int ERROR_API_KEY_MISSING_OR_INVALID = 187501;
}
