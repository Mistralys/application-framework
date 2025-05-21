<?php

declare(strict_types=1);

namespace Application\Formable\Event;

use Application\EventHandler\Traits\HTMLProcessingEventTrait;

class ClientFormRenderedEvent extends BaseFormableEvent implements HTMLProcessingEventInterface
{
    use HTMLProcessingEventTrait;

    public const EVENT_NAME = 'ClientFormRendered';

    protected function getHTMLArgumentIndex(): int
    {
        return 1;
    }
}
