<?php

declare(strict_types=1);

namespace UI\Event;

use Application_EventHandler_Event;
use UI_Page;

class PageRendered extends Application_EventHandler_Event
{
    public function getPage() : UI_Page
    {
        return $this->getArgumentObject(0, UI_Page::class);
    }

    public function getHTML() : string
    {
        return $this->getArgumentString(1);
    }

    public function setHTML(string $html) : self
    {
        $this->args[1] = $html;
        return $this;
    }

    public function replace(string $needle, string $replacement) : self
    {
        return $this->setHTML(
            str_replace(
                $needle,
                $replacement,
                $this->getHTML()
            )
        );
    }
}
