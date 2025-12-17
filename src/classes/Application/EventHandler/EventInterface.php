<?php

declare(strict_types=1);

namespace Application\EventHandler;

use Application_EventHandler_Exception;
use Application_EventHandler_Listener;
use AppUtils\Interfaces\StringPrimaryRecordInterface;

interface EventInterface extends StringPrimaryRecordInterface
{
    public function getName() : string;

    /**
     * Specifies that the event should be cancelled. This is only
     * possible if the event is callable.
     *
     * @param string $reason The reason for which the event was cancelled
     * @return $this
     *@throws Application_EventHandler_Exception {@see Application_EventHandler_Exception::ERROR_EVENT_NOT_CANCELLABLE}
     */
    public function cancel(string $reason) : self;

    /**
     * Retrieves all arguments of the event as an array.
     *
     * @return array<int,mixed>
     */
    public function getArguments() : array;

    /**
     * Retrieves the argument at the specified index, or null
     * if it does not exist.
     *
     * @param int $index Zero-based index of the argument.
     * @return NULL|mixed
     */
    public function getArgument(int $index) : mixed;

    /**
     * @param int $index Zero-based index of the argument.
     * @return string
     */
    public function getArgumentString(int $index) : string;

    /**
     * @param int $index Zero-based index of the argument.
     * @return array<int|string,mixed>
     */
    public function getArgumentArray(int $index) : array;

    /**
     * @param int $index Zero-based index of the argument.
     * @return int
     */
    public function getArgumentInt(int $index) : int;

    /**
     * @param int $index Zero-based index of the argument.
     * @return bool
     */
    public function getArgumentBool(int $index) : bool;

    /**
     * Checks whether the event should be cancelled.
     * @return boolean
     */
    public function isCancelled() : bool;

    /**
     * @return string
     */
    public function getCancelReason() : string;

    /**
     * Whether this event can be cancelled.
     * @return boolean
     */
    public function isCancellable() : bool;

    /**
     * Called automatically when a listener for this event is called,
     * to provide information about the listener.
     *
     * @param Application_EventHandler_Listener $listener
     * @return $this
     */
    public function selectListener(Application_EventHandler_Listener $listener) : self;

    /**
     * Retrieves the source of the listener that handled this event.
     * This is an optional string that can be specified when adding
     * an event listener. It can be empty.
     *
     * @return string
     */
    public function getSource() : string;

    public function startTrigger() : void;

    public function stopTrigger() : void;
}
