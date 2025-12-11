<?php

declare(strict_types=1);

namespace Application\WhatsNew;

use Application\ApplicationException;

class WhatsNewException extends ApplicationException
{
    public const int ERROR_VERSION_NUMBER_NOT_FOUND = 30003;
    public const int ERROR_COULD_NOT_PARSE_XML = 30002;
    public const int ERROR_WHATS_NEW_FILE_NOT_FOUND = 30001;
}
