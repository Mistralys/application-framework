<?php

declare(strict_types=1);

namespace Application\Countries\Event;

use Application\EventHandler\Eventables\BaseEventableEvent;

class IgnoredCountriesUpdatedEvent extends BaseEventableEvent
{
    public const string EVENT_NAME = 'IgnoredCountriesUpdated';

    public function getName() : string
    {
        return self::EVENT_NAME;
    }
}
