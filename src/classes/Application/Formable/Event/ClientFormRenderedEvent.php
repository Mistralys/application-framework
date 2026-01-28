<?php

declare(strict_types=1);

namespace Application\Formable\Event;

use Application\EventHandler\Traits\HTMLProcessingEventTrait;

class ClientFormRenderedEvent extends BaseFormableEvent implements HTMLProcessingEventInterface
{
    use HTMLProcessingEventTrait;

    public const string EVENT_NAME = 'ClientFormRendered';

    public function getName() : string
    {
        return self::EVENT_NAME;
    }

    protected function getHTMLArgumentIndex(): int
    {
        return 1;
    }
}
