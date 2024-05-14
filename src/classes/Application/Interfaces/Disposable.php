<?php
/**
 * @package Application
 * @subpackage Disposables
 */

declare(strict_types=1);

/**
 * Interface for objects that can be disposed of, freeing up resources.
 *
 * Usage:
 *
 * 1) Use the trait {@see Application_Traits_Disposable}.
 * 2) Implement this interface.
 * 3) Implement all abstract methods.
 *
 * @package Application
 * @subpackage Disposables
 */
interface Application_Interfaces_Disposable extends Application_Interfaces_Eventable
{
    public const EVENT_DISPOSED = 'Disposed';

    public function dispose() : void;

    public function isDisposed() : bool;

    public function onDisposed(callable $callback) : Application_EventHandler_EventableListener;

    public function getIdentification() : string;

    /**
     * Retrieves a list of all disposable child elements present
     * in the disposable. These automatically get disposed along
     * with the disposable.
     *
     * @return Application_Interfaces_Disposable[]|array Only disposables in the list are used, so this can contain anything to avoid having to do type checks.
     */
    public function getChildDisposables() : array;
}
