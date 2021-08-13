<?php

declare(strict_types=1);

/**
 *
 * @package Application
 * @subpackage Traits
 * @see Application_Interfaces_Disposable
 */
trait Application_Traits_Disposable
{
    /**
     * @var bool
     */
    private $disposableDisposed = false;

    /**
     * Disposes of the object once it is not needed anymore.
     */
    public function dispose() : void
    {
        if($this->disposableDisposed)
        {
            return;
        }

        $this->_dispose();

        $this->disposableDisposed = true;

        // Let event listeners react to the disposing
        $this->triggerEvent(
            Application_Interfaces_Disposable::EVENT_DISPOSED,
            array($this),
            Application_Traits_Disposable_Event_Disposed::class
        );

        // Disable all further event handling.
        $this->clearAllEventListeners();
        $this->disableEvents();

        $children = $this->getChildDisposables();

        foreach($children as $child)
        {
            if($child instanceof Application_Interfaces_Disposable)
            {
                $child->dispose();
            }
        }
    }

    /**
     * Adds an event listener to the "Disposed" event.
     *
     * @param callable $callback
     * @return Application_EventHandler_EventableListener
     */
    public function onDisposed(callable $callback) : Application_EventHandler_EventableListener
    {
        return $this->addEventListener(Application_Interfaces_Disposable::EVENT_DISPOSED, $callback);
    }

    public function isDisposed() : bool
    {
        return $this->disposableDisposed;
    }

    abstract protected function _dispose() : void;

    /**
     * Throws an exception if the disposable object has been disposed,
     * to avoid using critical methods afterwards.
     *
     * @param string $actionLabel Human-readable label of the action that was started
     * @throws Application_Exception_DisposableDisposed
     */
    protected function requireNotDisposed(string $actionLabel) : void
    {
        if($this->disposableDisposed === false)
        {
            return;
        }

        throw new Application_Exception_DisposableDisposed($this, $actionLabel);
    }
}
