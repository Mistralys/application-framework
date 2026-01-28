<?php

declare(strict_types=1);

namespace Application\Session\Events;

use Application\EventHandler\Eventables\BaseEventableEvent;
use Application_Session;

/**
 * This event is fired when a session has been started.
 *
 * <b>WARNING:</b> At this point, neither the Application
 * nor the Driver has been initialized.
 *
 * @package Application
 * @subpackage Session
 */
class SessionStartedEvent extends BaseEventableEvent
{
    public const string EVENT_NAME = 'SessionStarted';

    public function getName() : string
    {
        return self::EVENT_NAME;
    }

    public function getSession() : Application_Session
    {
        return $this->getArgumentObject(0, Application_Session::class);
    }
}
