<?php

declare(strict_types=1);

namespace Application\Admin\Area\Events;

use Application_Admin_Area;
use Application_EventHandler_Event;

class UIHandlingCompleteEvent extends Application_EventHandler_Event
{
    public function getArea() : Application_Admin_Area
    {
        return $this->getArgumentObject(0, Application_Admin_Area::class);
    }
}
