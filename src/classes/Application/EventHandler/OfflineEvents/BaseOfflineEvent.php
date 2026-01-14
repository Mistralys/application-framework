<?php

declare(strict_types=1);

namespace Application\EventHandler\OfflineEvents;

use Application_EventHandler_Event;

abstract class BaseOfflineEvent extends Application_EventHandler_Event implements OfflineEventInterface
{
    /**
     * @param string $eventName NOTE: Unused, present for signature compatibility.
     * @param array<int,mixed> $args
     */
    public function __construct(string $eventName='', array $args = array())
    {
        parent::__construct($this->_getEventName(), $args);
    }

    abstract protected function _getEventName(): string;
}
