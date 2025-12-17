<?php

declare(strict_types=1);

namespace Application\Admin\Area\Events;

use Application\Interfaces\Admin\AdminAreaInterface;
use Application_EventHandler_Event;

class UIHandlingCompleteEvent extends Application_EventHandler_Event
{
    public const string EVENT_NAME = 'UIHandlingComplete';

    public function getArea() : AdminAreaInterface
    {
        return $this->getArgumentObject(0, AdminAreaInterface::class);
    }
}
