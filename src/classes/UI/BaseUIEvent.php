<?php

declare(strict_types=1);

namespace UI;

use Application\EventHandler\Event\BaseEvent;
use UI;

abstract class BaseUIEvent extends BaseEvent
{
    final public function getUI(): UI
    {
        return $this->getArgumentObject(0, UI::class);
    }
}
