<?php

declare(strict_types=1);

namespace Application\Countries\AITools;

use Application\AI\AIToolException;

class CountryAIException extends AIToolException
{
    public const int ERROR_INVALID_COUNTRY = 189101;
}
