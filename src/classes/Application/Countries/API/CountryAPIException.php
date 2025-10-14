<?php

declare(strict_types=1);

namespace Application\Countries\API;

use Application\Countries\CountryException;

class CountryAPIException extends CountryException
{
    public const int INVALID_PARAM_SET = 184701;
}
