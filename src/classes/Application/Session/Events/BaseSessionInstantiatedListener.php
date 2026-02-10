<?php
/**
 * @package Application
 * @subpackage Session
 */

declare(strict_types=1);

namespace Application\Session\Events;

use Application\EventHandler\Event\EventInterface;
use Application\EventHandler\Event\StandardEvent;
use Application\EventHandler\OfflineEvents\BaseOfflineListener;
use AppUtils\ClassHelper;

/**
 * Abstract base class for offline event listeners that handle session instantiation.
 *
 * @package Application
 * @subpackage Session
 */
abstract class BaseSessionInstantiatedListener extends BaseOfflineListener
{
    public function getEventName(): string
    {
        return SessionInstantiatedEvent::EVENT_NAME;
    }

    protected function handleEvent(EventInterface $event, ...$args): void
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
