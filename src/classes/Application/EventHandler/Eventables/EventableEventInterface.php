<?php

declare(strict_types=1);

namespace Application\EventHandler\Eventables;

use Application\EventHandler\Event\EventInterface;

interface EventableEventInterface extends EventInterface
{
    public function getSubject(): object;
}

