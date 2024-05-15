<?php

declare(strict_types=1);

namespace TestDriver\OfflineEvents;

use Application_EventHandler_Event;

class TestEvent extends Application_EventHandler_Event
{
    public const EVENT_NAME = 'Test';
}
