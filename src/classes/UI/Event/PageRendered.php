<?php

declare(strict_types=1);

namespace UI\Event;

use Application\EventHandler\Event\BaseEvent;
use Application\EventHandler\Traits\HTMLProcessingEventTrait;
use Application\Formable\Event\HTMLProcessingEventInterface;
use UI_Page;

class PageRendered extends BaseEvent implements HTMLProcessingEventInterface
{
    use HTMLProcessingEventTrait;

    public const string EVENT_NAME = 'PageRendered';

    public function getName() : string
    {
        return self::EVENT_NAME;
    }

    public function getPage() : UI_Page
    {
        return $this->getArgumentObject(0, UI_Page::class);
    }

    protected function getHTMLArgumentIndex(): int
    {
        return 1;
    }
}
