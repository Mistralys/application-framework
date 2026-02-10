<?php

declare(strict_types=1);

namespace Application\EventHandler\OfflineEvents;

use Application\EventHandler\Event\BaseEvent;

abstract class BaseOfflineEvent extends BaseEvent implements OfflineEventInterface
{
}
