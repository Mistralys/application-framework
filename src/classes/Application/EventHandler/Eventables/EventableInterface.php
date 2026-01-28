<?php
/**
 * @package Application
 * @subpackage EventHandler
 */

declare(strict_types=1);

namespace Application\EventHandler\Eventables;

use Application_Interfaces_Loggable;

/**
 * Interface for classes that support handling events. Used in
 * tandem with the trait {@see EventableTrait}.
 *
 * @package Application
 * @subpackage EventHandler
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see EventableTrait
 */
interface EventableInterface extends Application_Interfaces_Loggable
{
    /**
     * Adds a listener for the specified event.
     *
     * NOTE: This does NOT validate whether the event
     * actually exists.
     *
     * @param string $eventName
     * @param callable $callback
     * @return EventableListener
     */
    public function addEventListener(string $eventName, callable $callback): EventableListener;


    /**
     * Retrieves all listener instances that were added for
     * the specified event, if any.
     *
     * @param string $eventName
     * @return array
     */
    public function getEventListeners(string $eventName): array;

    /**
     * Whether any listeners have been added for the specified event.
     *
     * @param string $eventName
     * @return bool
     */
    public function hasEventListeners(string $eventName): bool;

    /**
     * Removes a previously added event listener. Note that this
     * will have no effect if the listener does not exist anymore,
     * or is not a listener in this object.
     *
     * @param EventableListener $listener
     */
    public function removeEventListener(EventableListener $listener): void;

    public function countEventListeners(string $eventName): int;

    public function clearEventListeners(string $eventName): void;

    public function clearAllEventListeners(): void;

    public function isEventIgnored(string $eventName): bool;

    /**
     * Sets an event to be ignored: If triggered, it will not
     * be processed by the event handler.
     *
     * @param string $eventName
     * @return $this
     */
    public function ignoreEvent(string $eventName): self;

    /**
     * Removes an event from the list of ignored events.
     *
     * NOTE: Has no effect if the event is not currently ignored.
     *
     * @param string $eventName
     * @return $this
     */
    public function unIgnoreEvent(string $eventName): self;

    /**
     * @return string[]
     */
    public function getIgnoredEvents(): array;
}
