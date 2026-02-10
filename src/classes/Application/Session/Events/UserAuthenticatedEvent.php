<?php

declare(strict_types=1);

namespace Application\Session\Events;

use Application\EventHandler\Eventables\BaseEventableEvent;
use Application_User;

class UserAuthenticatedEvent extends BaseEventableEvent
{
    public const string EVENT_NAME = 'UserAuthenticated';

    public function getName() : string
    {
        return self::EVENT_NAME;
    }

    public function getUser() : Application_User
    {
        return $this->getArgumentObject(0, Application_User::class);
    }
}
