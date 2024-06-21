<?php
/**
 * @package Application
 * @subpackage Disposables
 */

declare(strict_types=1);

use Application\Exception\DisposableDisposedException;

/**
 * Trait used to implement the {@see Application_Interfaces_Disposable} interface.
 *
 * @package Application
 * @subpackage Disposables
 * @see Application_Interfaces_Disposable
 */
trait Application_Traits_Disposable
{
    private bool $disposableDisposed = false;

    /**
     * Disposes of the object once it is not needed anymore.
     */
    public function dispose() : void
    {
        if($this->disposableDisposed) {
            return;
        }

        $this->log('Dispose | Disposing of the object.');

        $this->disposableDisposed = true;

        $this->log('Dispose | Disposing of child disposables.');

        $children = $this->getChildDisposables();

        foreach($children as $child)
        {
            if($child instanceof Application_Interfaces_Disposable)
            {
                $child->dispose();
            }
        }

        $this->log('Dispose | Clearing the object\'s properties.');

        $this->_dispose();

        // Let event listeners react to the disposing
        $this->triggerEvent(
            Application_Interfaces_Disposable::EVENT_DISPOSED,
            array($this),
            Application_Traits_Disposable_Event_Disposed::class
        );

        // Disable all further event handlings.
        $this->clearAllEventListeners();
        $this->disableEvents();
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

    /**
     * Disposes of the object's resources: Clear all
     * properties and references to other objects.
     *
     * 1. Use `unset()` for object references.
     * 2. Set nullable properties to `NULL`.
     * 3. Set scalar properties to their default values.
     *
     * NOTE: Scalar values are known to be garbage collected
     * better when not cleared with `unset()`.
     *
     * @return void
     */
    abstract protected function _dispose() : void;

    /**
     * Throws an exception if the disposable object has been disposed,
     * to avoid using critical methods afterwards.
     *
     * @param string|NULL $actionLabel Human-readable label of the action that was started
     * @throws DisposableDisposedException
     */
    protected function requireNotDisposed(?string $actionLabel=null) : void
    {
        if($this->disposableDisposed === false) {
            return;
        }

        throw new DisposableDisposedException($this, $actionLabel);
    }
}
