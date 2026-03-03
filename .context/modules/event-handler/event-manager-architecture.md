# Event Handler - Event Manager Architecture
_SOURCE: Public APIs_
# Public APIs
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── EventHandler/
                └── EventManager.php
                └── Traits/
                    └── HTMLProcessingEventInterface.php
                    └── HTMLProcessingEventTrait.php

```
###  Path: `/src/classes/Application/EventHandler/EventManager.php`

```php
namespace Application\EventHandler;

use AppUtils\ClassHelper as ClassHelper;
use Application\Application as Application;
use Application\EventHandler\Event\EventInterface as EventInterface;
use Application\EventHandler\Event\EventListener as EventListener;
use Application\EventHandler\Event\StandardEvent as StandardEvent;
use Application\EventHandler\OfflineEvents\OfflineEventsManager as OfflineEventsManager;
use EventHandlingException as EventHandlingException;

/**
 * Event management class: handles registering and triggering events
 * and any listeners. This is used for all events, so event names
 * should be prefixed to ensure that the naming is unique.
 *
 * @package Application
 * @subpackage EventHandler
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class EventManager
{
	public const ERROR_INVALID_EVENT_CLASS = 13801;
	public const ERROR_MISSING_EVENT_CLASS = 13802;
	public const ERROR_UNKNOWN_LISTENER = 13804;

	/**
	 * Adds a callback to the specified event.
	 *
	 * @param string $eventName
	 * @param callable $callback
	 * @param string $source A human-readable label for the listener.
	 * @return EventListener
	 */
	public static function addListener(string $eventName, callable $callback, string $source = ''): EventListener
	{
		/* ... */
	}


	/**
	 * Checks whether any listeners have been added for the specified event.
	 * @param string $eventName
	 * @return boolean
	 */
	public static function hasListener(string $eventName): bool
	{
		/* ... */
	}


	/**
	 * Triggers the specified event, calling all registered listeners.
	 *
	 * @param string $eventName
	 * @param mixed|array<int,mixed>|NULL $args Indexed array of arguments or a single argument to pass to the event.
	 * @param class-string<EventInterface> $class The name of the event class to use. Allows specifying a custom class for this event, which must extend the base event class.
	 * @return EventInterface
	 * @throws EventHandlingException
	 *
	 * @see EventManager::ERROR_MISSING_EVENT_CLASS
	 * @see EventManager::ERROR_INVALID_EVENT_CLASS
	 */
	public static function trigger(
		string $eventName,
		mixed $args = null,
		string $class = StandardEvent::class,
	): EventInterface
	{
		/* ... */
	}


	public static function removeListener(int $listenerID): void
	{
		/* ... */
	}


	public static function listenerExists(int $listenerID): bool
	{
		/* ... */
	}


	/**
	 * @param int $listenerID
	 * @return EventListener
	 * @throws EventHandlingException
	 *
	 * @see EventManager::ERROR_UNKNOWN_LISTENER
	 */
	public static function getListenerByID(int $listenerID): EventListener
	{
		/* ... */
	}


	public static function createOfflineEvents(): OfflineEventsManager
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/EventHandler/Traits/HTMLProcessingEventInterface.php`

```php
namespace Application\Formable\Event;

use Application\EventHandler\Traits\HTMLProcessingEventTrait as HTMLProcessingEventTrait;

/**
 * Interface for the trait {@see HTMLProcessingEventTrait}.
 *
 * @package Event Handling
 * @subpackage Traits
 * @see HTMLProcessingEventTrait
 */
interface HTMLProcessingEventInterface
{
	public function getHTML(): string;


	public function setHTML(string $html);


	/**
	 * @param string $needle
	 * @param string $replacement
	 * @return $this
	 */
	public function replace(string $needle, string $replacement): self;
}


```
###  Path: `/src/classes/Application/EventHandler/Traits/HTMLProcessingEventTrait.php`

```php
namespace Application\EventHandler\Traits;

use Application\Formable\Event\HTMLProcessingEventInterface as HTMLProcessingEventInterface;

/**
 * Trait used to implement an event that allows HTML code
 * to be accessed and modified.
 *
 * ## Usage
 *
 * 1. Extend the interface {@see HTMLProcessingEventInterface}.
 * 2. Use this trait.
 * 3. Implement the {@see self::getHTMLArgumentIndex()} method.
 *
 * @package Event Handling
 * @subpackage Traits
 */
trait HTMLProcessingEventTrait
{
	public function getHTML(): string
	{
		/* ... */
	}


	/**
	 * @param string $html
	 * @return $this
	 */
	public function setHTML(string $html): self
	{
		/* ... */
	}


	/**
	 * @param string $needle
	 * @param string $replacement
	 * @return $this
	 */
	public function replace(string $needle, string $replacement): self
	{
		/* ... */
	}
}


```
---
**File Statistics**
- **Size**: 4.82 KB
- **Lines**: 212
File: `modules/event-handler/event-manager-architecture.md`
