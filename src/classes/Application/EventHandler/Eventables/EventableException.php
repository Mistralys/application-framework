<?php

declare(strict_types=1);

namespace Application\EventHandler\Eventables;

use EventHandlingException;

class EventableException extends EventHandlingException
{

    public const int ERROR_INVALID_EVENT_CLASS = 84901;
}
