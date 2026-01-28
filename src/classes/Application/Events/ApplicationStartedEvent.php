<?php

declare(strict_types=1);

namespace Application\Events;

use Application\Application;
use Application\EventHandler\Event\BaseEvent;
use Application_Driver;

class ApplicationStartedEvent extends BaseEvent
{
    public const string EVENT_NAME = 'ApplicationStarted';

    public function getName(): string
    {
        return self::EVENT_NAME;
    }

    public function getApplication(): Application
    {
        return $this->getArgumentObject(0, Application::class);
    }

    public function getDriver(): Application_Driver
    {
        return $this->getArgumentObject(1, Application_Driver::class);
    }
}
