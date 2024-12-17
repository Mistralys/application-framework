<?php

declare(strict_types=1);

namespace TestDriver\OfflineEvents;

use Application_EventHandler_Event;

class PriorityTestEvent extends Application_EventHandler_Event
{
    public const EVENT_NAME = 'PriorityTest';
}
