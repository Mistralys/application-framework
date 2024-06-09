<?php

declare(strict_types=1);

namespace Application\Session\Events;

use Application_EventHandler_EventableEvent;
use Application_User;

class UserAuthenticatedEvent extends Application_EventHandler_EventableEvent
{
    public function getUser() : Application_User
    {
        return $this->getArgumentObject(0, Application_User::class);
    }
}
