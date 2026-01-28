<?php

declare(strict_types=1);

namespace Application\Admin\Area\Events;

use Application\EventHandler\Event\BaseEvent;
use Application\Interfaces\Admin\AdminAreaInterface;

class UIHandlingCompleteEvent extends BaseEvent
{
    public const string EVENT_NAME = 'UIHandlingComplete';

    public function getName() : string
    {
        return self::EVENT_NAME;
    }

    public function getArea() : AdminAreaInterface
    {
        return $this->getArgumentObject(0, AdminAreaInterface::class);
    }
}
