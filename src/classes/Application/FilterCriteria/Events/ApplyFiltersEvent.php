<?php

declare(strict_types=1);

namespace Application\FilterCriteria\Events;

use Application_EventHandler_EventableEvent;

class ApplyFiltersEvent extends Application_EventHandler_EventableEvent
{
    public function getFilterCriteria(): array
    {
        return $this->getArgument(0);
    }
}
