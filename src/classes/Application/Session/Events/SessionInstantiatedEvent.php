<?php
/**
 * @package Application
 * @subpackage Session
 */

declare(strict_types=1);

namespace Application\Session\Events;

use Application\EventHandler\OfflineEvents\BaseOfflineEvent;
use Application_Session;

/**
 * This offline event is triggered when the session object has been instantiated.
 *
 * It is used to be able to add listeners to session events, as some of these
 * are triggered before the driver is fully initialized:
 *
 * - {@see Application_Session::onSessionStarted()}
 * - {@see Application_Session::onUserAuthenticated()}
 * - {@see Application_Session::onUserLoggedOut()}
 *
 * ## Usage
 *
 * 1. Extend the listener class {@see BaseSessionInstantiatedListener}.
 *
 * The listener classes will be auto-discovered on application build.
 *
 * @package Application
 * @subpackage Session
 */
class SessionInstantiatedEvent extends BaseOfflineEvent
{
    public const string EVENT_NAME = 'SessionInstantiated';

    protected function _getEventName(): string
    {
        return self::EVENT_NAME;
    }

    public function getSession() : Application_Session
    {
        return $this->getArgumentObject(0, Application_Session::class);
    }
}
