<?php

declare(strict_types=1);

namespace Application\FilterCriteria\Events;

use Application\EventHandler\Eventables\BaseEventableEvent;

class ApplyFiltersEvent extends BaseEventableEvent
{
    public const string EVENT_NAME = 'ApplyFilters';

    public function getName() : string
    {
        return self::EVENT_NAME;
    }

    public function getFilterCriteria(): array
    {
        return $this->getArgument(0);
    }
}
