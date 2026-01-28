<?php
/**
 * @package Application
 * @subpackage Events
 */

declare(strict_types=1);

namespace Application\EventHandler\OfflineEvents;

use Application\EventHandler\Event\EventInterface;
use Application\EventHandler\Event\StandardEvent;
use AppUtils\NamedClosure;

/**
 * Abstract base class for offline event listeners.
 *
 * @package Application
 * @subpackage Events
 */
abstract class BaseOfflineListener implements OfflineEventListenerInterface
{
    private ?NamedClosure $callable = null;

    private ?string $id = null;

    final public function getID(): string
    {
        if (!isset($this->id)) {
            $this->id = md5(get_class($this));
        }

        return $this->id;
    }

    public function getCallable(): NamedClosure
    {
        if (!isset($this->callable)) {
            $this->callable = $this->wakeUp();
        }

        return $this->callable;
    }

    protected function wakeUp(): NamedClosure
    {
        $callback = array($this, 'handleEvent');

        return NamedClosure::fromClosure($callback(...), $callback);
    }

    public function getPriority(): int
    {
        return 0;
    }

    /**
     * This method is called when the event is triggered.
     *
     * @param EventInterface $event This will be the event class specific to the event that triggered this listener.
     * @param mixed ...$args Any arguments that were added in the original event trigger call.
     * @return void
     */
    abstract protected function handleEvent(EventInterface $event, ...$args): void;
}
