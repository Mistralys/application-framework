<?php

declare(strict_types=1);

namespace Application\Renamer;

use Application\Exception\ApplicationException;

class RenamerException extends ApplicationException
{
    public const int ERROR_EXPORT_CANNOT_OPEN_OUTPUT = 187701;
}
