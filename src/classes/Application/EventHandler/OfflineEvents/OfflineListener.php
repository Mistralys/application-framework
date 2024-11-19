<?php
/**
 * @package Application
 * @subpackage Events
 */

declare(strict_types=1);

use AppUtils\NamedClosure;

/**
 * Abstract base class for offline event listeners.
 *
 * @package Application
 * @subpackage Events
 */
abstract class Application_EventHandler_OfflineEvents_OfflineListener
{
    private ?NamedClosure $callable = null;

    public function getCallable() : NamedClosure
    {
        if(!isset($this->callable))
        {
            $this->callable = $this->wakeUp();
        }

        return $this->callable;
    }

    protected function wakeUp(): NamedClosure
    {
        $callback = array($this, 'handleEvent');

        return NamedClosure::fromClosure(Closure::fromCallable($callback), $callback);
    }

    /**
     * This method is called when the event is triggered.
     *
     * @param Application_EventHandler_Event $event This will be the event class specific to the event that triggered this listener.
     * @param mixed ...$args Any arguments that were added in the original event trigger call.
     * @return void
     */
    abstract protected function handleEvent(Application_EventHandler_Event $event, ...$args): void;
}
