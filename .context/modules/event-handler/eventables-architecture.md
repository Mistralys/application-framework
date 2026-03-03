# Event Handler - Eventables Architecture
_SOURCE: Public Classes and APIs_
# Public Classes and APIs
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── EventHandler/
                └── Eventables/
                    └── BaseEventableEvent.php
                    └── EventableEventInterface.php
                    └── EventableException.php
                    └── EventableInterface.php
                    └── EventableListener.php
                    └── EventableTrait.php
                    └── StandardEventableEvent.php

```
###  Path: `/src/classes/Application/EventHandler/Eventables/BaseEventableEvent.php`

```php
namespace Application\EventHandler\Eventables;

use Application\EventHandler\Event\BaseEvent as BaseEvent;

/**
 * Eventable-specific event class which extends the base event class:
 * it stores an instance of the owner object and adds the `getSubject()`
 * method to retrieve it.
 *
 * @package Application
 * @subpackage EventHandler
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see EventableTrait::createEvent()
 */
abstract class BaseEventableEvent extends BaseEvent implements EventableEventInterface
{
	public function getSubject(): object
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/EventHandler/Eventables/EventableEventInterface.php`

```php
namespace Application\EventHandler\Eventables;

use Application\EventHandler\Event\EventInterface as EventInterface;

interface EventableEventInterface extends EventInterface
{
	public function getSubject(): object;
}


```
###  Path: `/src/classes/Application/EventHandler/Eventables/EventableException.php`

```php
namespace Application\EventHandler\Eventables;

use EventHandlingException as EventHandlingException;

class EventableException extends EventHandlingException
{
	public const ERROR_INVALID_EVENT_CLASS = 84901;
}


```
###  Path: `/src/classes/Application/EventHandler/Eventables/EventableInterface.php`

```php
namespace Application\EventHandler\Eventables;

use Application_Interfaces_Loggable as Application_Interfaces_Loggable;

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


```
###  Path: `/src/classes/Application/EventHandler/Eventables/EventableListener.php`

```php
namespace Application\EventHandler\Eventables;

use Application\EventHandler\Event\EventListener as EventListener;

/**
 * Eventable-specific listener class which extends the base listener class:
 * it stores an instance of the owner object and adds the `getSubject()`
 * method to retrieve it.
 *
 * @package Application
 * @subpackage EventHandler
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see EventableTrait::addEventListener()
 */
class EventableListener extends EventListener
{
	public function getEventNameNS(): string
	{
		/* ... */
	}


	/**
	 * @return object
	 */
	public function getSubject(): object
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/EventHandler/Eventables/EventableTrait.php`

```php
namespace Application\EventHandler\Eventables;

use AppUtils\ConvertHelper as ConvertHelper;
use Application_Exception as Application_Exception;

/**
 * Trait used to enable any class to use event handling.
 *
 * Usage:
 *
 * 1) Use this trait
 * 2) Implement the interface {@see EventableInterface}.
 *
 * Optional:
 *
 * - Override {@see self::getEventNamespace()} to handle event namespaces.
 *
 * @package Application
 * @subpackage EventHandler
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see EventableInterface
 */
trait EventableTrait
{
	/**
	 * @param string $eventName
	 * @param callable $callback
	 * @return EventableListener
	 */
	public function addEventListener(string $eventName, callable $callback): EventableListener
	{
		/* ... */
	}


	/**
	 * Removes a previously added listener.
	 *
	 * NOTE: This will fail silently if the listener does not exist.
	 *
	 * @param EventableListener $listener
	 */
	public function removeEventListener(EventableListener $listener): void
	{
		/* ... */
	}


	/**
	 * @param string $eventName
	 * @return string|null
	 */
	public function getEventNamespace(string $eventName): ?string
	{
		/* ... */
	}


	public function getIgnoredEvents(): array
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function ignoreEvent(string $eventName): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function unIgnoreEvent(string $eventName): self
	{
		/* ... */
	}


	public function isEventIgnored(string $eventName): bool
	{
		/* ... */
	}


	public function hasEventListeners(string $eventName): bool
	{
		/* ... */
	}


	public function countEventListeners(string $eventName): int
	{
		/* ... */
	}


	/**
	 * @param string $eventName
	 * @return EventableListener[]
	 */
	public function getEventListeners(string $eventName): array
	{
		/* ... */
	}


	public function clearEventListeners(string $eventName): void
	{
		/* ... */
	}


	public function clearAllEventListeners(): void
	{
		/* ... */
	}


	public function areEventsDisabled(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/EventHandler/Eventables/StandardEventableEvent.php`

```php
namespace Application\EventHandler\Eventables;

/**
 * Standard implementation of an eventable event, which uses
 * the specified event name. This is the default event class
 * and cannot be extended further.
 *
 * Use the {@see BaseEventableEvent} class to create custom
 * eventable event classes.
 *
 * @package Application
 * @subpackage EventHandler
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see EventableTrait::createEvent()
 */
final class StandardEventableEvent extends BaseEventableEvent
{
	public function getName(): string
	{
		/* ... */
	}
}


```
---
**File Statistics**
- **Size**: 7.81 KB
- **Lines**: 383
File: `modules/event-handler/eventables-architecture.md`
