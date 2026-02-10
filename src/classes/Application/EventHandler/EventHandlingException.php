<?php

declare(strict_types=1);

use Application\Exception\ApplicationException;

class EventHandlingException extends ApplicationException
{
    public const int ERROR_EVENT_NOT_CANCELLABLE = 13701;
}
