<?php

declare(strict_types=1);

namespace Application\Messaging;

use Application\ApplicationException;

class MessagingException extends ApplicationException
{
    public const int ERROR_TO_AND_FROM_USERS_IDENTICAL = 13402;
    public const int ERROR_INVALID_MESSAGE_PRIORITY = 13401;
    public const int ERROR_INVALID_CUSTOM_DATA = 13403;
}
