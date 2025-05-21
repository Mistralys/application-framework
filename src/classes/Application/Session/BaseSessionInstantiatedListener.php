<?php
/**
 * @package Application
 * @subpackage Session
 */

declare(strict_types=1);

namespace Application\Session;

use Application\OfflineEvents\SessionInstantiatedEvent;
use Application_EventHandler_Event;
use Application_EventHandler_OfflineEvents_OfflineListener;
use AppUtils\ClassHelper;

/**
 * Abstract base class for offline event listeners that handle session instantiation.
 *
 * @package Application
 * @subpackage Session
 */
abstract class BaseSessionInstantiatedListener extends Application_EventHandler_OfflineEvents_OfflineListener
{
    protected function handleEvent(Application_EventHandler_Event $event, ...$args): void
    {
        $this->handleSessionInstantiated(
            ClassHelper::requireObjectInstanceOf(
                SessionInstantiatedEvent::class,
                $event
            )
        );
    }

    abstract protected function handleSessionInstantiated(SessionInstantiatedEvent $event) : void;
}
