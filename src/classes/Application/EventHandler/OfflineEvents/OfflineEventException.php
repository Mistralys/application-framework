<?php

declare(strict_types=1);

namespace Application\EventHandler\OfflineEvents;

use EventHandlingException;

class OfflineEventException extends EventHandlingException
{
    public const int ERROR_INDEX_FILE_INVALID = 97202;
    public const int ERROR_EVENT_NOT_FOUND_IN_INDEX = 97203;
}
