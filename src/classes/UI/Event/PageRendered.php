<?php

declare(strict_types=1);

namespace UI\Event;

use Application\EventHandler\Traits\HTMLProcessingEventTrait;
use Application\Formable\Event\HTMLProcessingEventInterface;
use Application_EventHandler_Event;
use UI_Page;

class PageRendered extends Application_EventHandler_Event implements HTMLProcessingEventInterface
{
    use HTMLProcessingEventTrait;

    public function getPage() : UI_Page
    {
        return $this->getArgumentObject(0, UI_Page::class);
    }

    protected function getHTMLArgumentIndex(): int
    {
        return 1;
    }
}
