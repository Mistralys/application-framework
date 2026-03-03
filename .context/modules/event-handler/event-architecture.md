# Event Handler - Event Architecture
_SOURCE: Public Classes and APIs_
# Public Classes and APIs
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── EventHandler/
                └── Event/
                    └── BaseEvent.php
                    └── EventInterface.php
                    └── EventListener.php
                    └── StandardEvent.php

```
###  Path: `/src/classes/Application/EventHandler/Event/BaseEvent.php`

```php
namespace Application\EventHandler\Event;

use AppUtils\ClassHelper as ClassHelper;
use AppUtils\ClassHelper\ClassNotExistsException as ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException as ClassNotImplementsException;
use AppUtils\ConvertHelper as ConvertHelper;
use EventHandlingException as EventHandlingException;

/**
 * Abstract base class for individual events: an instance of this is
 * given as an argument to event listener callbacks. May be extended
 * to provide a more specialized API depending on the event.
 *
 * @package Application
 * @subpackage Core
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class BaseEvent implements EventInterface
{
	final public function getID(): string
	{
		/* ... */
	}


	public function cancel(string $reason): self
	{
		/* ... */
	}


	final public function getArguments(): array
	{
		/* ... */
	}


	final public function getArgument(int $index): mixed
	{
		/* ... */
	}


	final public function getArgumentString(int $index): string
	{
		/* ... */
	}


	final public function getArgumentArray(int $index): array
	{
		/* ... */
	}


	final public function getArgumentInt(int $index): int
	{
		/* ... */
	}


	final public function getArgumentBool(int $index): bool
	{
		/* ... */
	}


	public function isCancelled(): bool
	{
		/* ... */
	}


	public function getCancelReason(): string
	{
		/* ... */
	}


	/**
	 * Whether this event can be cancelled.
	 * @return boolean
	 */
	public function isCancellable(): bool
	{
		/* ... */
	}


	public function selectListener(EventListener $listener): self
	{
		/* ... */
	}


	public function getSource(): string
	{
		/* ... */
	}


	public function startTrigger(): void
	{
		/* ... */
	}


	public function stopTrigger(): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/EventHandler/Event/EventInterface.php`

```php
namespace Application\EventHandler\Event;

use AppUtils\Interfaces\StringPrimaryRecordInterface as StringPrimaryRecordInterface;
use EventHandlingException as EventHandlingException;

interface EventInterface extends StringPrimaryRecordInterface
{
	public function getName(): string;


	/**
	 * Specifies that the event should be cancelled. This is only
	 * possible if the event is callable.
	 *
	 * @param string $reason The reason for which the event was cancelled
	 * @return $this
	 * @throws EventHandlingException {@see EventHandlingException::ERROR_EVENT_NOT_CANCELLABLE}
	 */
	public function cancel(string $reason): self;


	/**
	 * Retrieves all arguments of the event as an array.
	 *
	 * @return array<int,mixed>
	 */
	public function getArguments(): array;


	/**
	 * Retrieves the argument at the specified index, or null
	 * if it does not exist.
	 *
	 * @param int $index Zero-based index of the argument.
	 * @return NULL|mixed
	 */
	public function getArgument(int $index): mixed;


	/**
	 * @param int $index Zero-based index of the argument.
	 * @return string
	 */
	public function getArgumentString(int $index): string;


	/**
	 * @param int $index Zero-based index of the argument.
	 * @return array<int|string,mixed>
	 */
	public function getArgumentArray(int $index): array;


	/**
	 * @param int $index Zero-based index of the argument.
	 * @return int
	 */
	public function getArgumentInt(int $index): int;


	/**
	 * @param int $index Zero-based index of the argument.
	 * @return bool
	 */
	public function getArgumentBool(int $index): bool;


	/**
	 * Checks whether the event should be cancelled.
	 * @return boolean
	 */
	public function isCancelled(): bool;


	/**
	 * @return string
	 */
	public function getCancelReason(): string;


	/**
	 * Whether this event can be cancelled.
	 * @return boolean
	 */
	public function isCancellable(): bool;


	/**
	 * Called automatically when a listener for this event is called,
	 * to provide information about the listener.
	 *
	 * @param EventListener $listener
	 * @return $this
	 */
	public function selectListener(EventListener $listener): self;


	/**
	 * Retrieves the source of the listener that handled this event.
	 * This is an optional string that can be specified when adding
	 * an event listener. It can be empty.
	 *
	 * @return string
	 */
	public function getSource(): string;


	public function startTrigger(): void;


	public function stopTrigger(): void;
}


```
###  Path: `/src/classes/Application/EventHandler/Event/EventListener.php`

```php
namespace Application\EventHandler\Event;

use AppUtils\ConvertHelper as ConvertHelper;
use Application\EventHandler\Eventables\EventableInterface as EventableInterface;

/**
 * Listener class for a specific event: Used to store information
 * on the listener. It also allows removing specific listeners by
 * their instance.
 *
 * @package Application
 * @subpackage EventHandler
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see EventManager::addListener()
 * @see EventableInterface::addEventListener()
 */
class EventListener
{
	/**
	 * Unique ID of the listener, within the same request.
	 *
	 * @return int
	 */
	public function getID(): int
	{
		/* ... */
	}


	/**
	 * The name of the event the listener listens to.
	 *
	 * @return string
	 */
	public function getEventName(): string
	{
		/* ... */
	}


	/**
	 * Human-readable label of where the listener comes from.
	 *
	 * @return string
	 */
	public function getSource(): string
	{
		/* ... */
	}


	/**
	 * @return callable
	 */
	public function getCallback(): callable
	{
		/* ... */
	}


	public function getCallbackAsString(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/EventHandler/Event/StandardEvent.php`

```php
namespace Application\EventHandler\Event;

/**
 * This is the default event class used when no specific
 * event class is defined. It uses the event name as provided
 * and cannot be extended with additional functionality.
 *
 * To create a custom event, extend the {@see BaseEvent} class.
 *
 * @package Application
 * @subpackage Core
 */
final class StandardEvent extends BaseEvent
{
	public function getName(): string
	{
		/* ... */
	}
}


```
---
**File Statistics**
- **Size**: 6.59 KB
- **Lines**: 355
File: `modules/event-handler/event-architecture.md`
