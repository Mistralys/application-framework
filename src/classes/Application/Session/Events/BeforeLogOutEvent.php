<?php

declare(strict_types=1);

namespace Application\Session\Events;

use Application\EventHandler\Eventables\BaseEventableEvent;
use Application_User;

class BeforeLogOutEvent extends BaseEventableEvent
{
    public const string EVENT_NAME = 'BeforeLogOut';

    public function getName() : string
    {
        return self::EVENT_NAME;
    }

    public function getUser() : Application_User
    {
        return $this->getArgumentObject(0, Application_User::class);
    }

    public function getReasonID() : string
    {
        return $this->getArgumentString(1);
    }
}
