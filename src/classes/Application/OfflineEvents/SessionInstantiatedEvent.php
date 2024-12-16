<?php
/**
 * @package Application
 * @subpackage Session
 */

declare(strict_types=1);

namespace Application\OfflineEvents;

use Application_EventHandler_Event;
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
 * 1. Add listeners in the folder {@see self::EVENT_NAME} in the offline event folder.
 * 2. Extend the base class {@see BaseSessionInstantiatedListener}.
 *
 * @package Application
 * @subpackage Session
 */
class SessionInstantiatedEvent extends Application_EventHandler_Event
{
    public const EVENT_NAME = 'SessionInstantiated';

    public function getSession() : Application_Session
    {
        return $this->getArgumentObject(0, Application_Session::class);
    }
}
