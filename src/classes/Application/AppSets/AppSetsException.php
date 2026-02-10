<?php

declare(strict_types=1);

namespace Application\Sets;

use Application\Exception\ApplicationException;

class AppSetsException extends ApplicationException
{

    public const int ERROR_ALIAS_ALREADY_EXISTS = 12701;
    public const int ERROR_CANNOT_RENAME_TO_EXISTING_NAME = 12705;
    public const int ERROR_CANNOT_RENAME_INEXISTANT_SET = 12704;
    public const int ERROR_UNKNOWN_SET = 12702;
    public const int ERROR_CANNOT_SAVE_CONFIGURATION = 12703;
    public const int ERROR_FORMABLE_NOT_VALID = 12801;
    public const int ERROR_INVALID_DEFAULT_AREA = 12802;
}
