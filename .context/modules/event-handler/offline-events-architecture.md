# Event Handler - Offline Events Architecture
_SOURCE: Public Classes and APIs_
# Public Classes and APIs
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── EventHandler/
                └── OfflineEvents/
                    └── BaseOfflineEvent.php
                    └── BaseOfflineListener.php
                    └── Index/
                        ├── EventClassFinder.php
                        ├── EventIndex.php
                        ├── EventIndexCacheLocation.php
                        ├── EventIndexer.php
                        ├── Events/
                        │   ├── RegisterEventIndexCacheListener.php
                        ├── ListenerClassFinder.php
                    └── OfflineEventContainer.php
                    └── OfflineEventException.php
                    └── OfflineEventInterface.php
                    └── OfflineEventListenerInterface.php
                    └── OfflineEventsManager.php

```
###  Path: `/src/classes/Application/EventHandler/OfflineEvents/BaseOfflineEvent.php`

```php
namespace Application\EventHandler\OfflineEvents;

use Application\EventHandler\Event\BaseEvent as BaseEvent;

abstract class BaseOfflineEvent extends BaseEvent implements OfflineEventInterface
{
}


```
###  Path: `/src/classes/Application/EventHandler/OfflineEvents/BaseOfflineListener.php`

```php
namespace Application\EventHandler\OfflineEvents;

use AppUtils\NamedClosure as NamedClosure;
use Application\EventHandler\Event\EventInterface as EventInterface;
use Application\EventHandler\Event\StandardEvent as StandardEvent;

/**
 * Abstract base class for offline event listeners.
 *
 * @package Application
 * @subpackage Events
 */
abstract class BaseOfflineListener implements OfflineEventListenerInterface
{
	final public function getID(): string
	{
		/* ... */
	}


	public function getCallable(): NamedClosure
	{
		/* ... */
	}


	public function getPriority(): int
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/EventHandler/OfflineEvents/Index/EventClassFinder.php`

```php
namespace Application\EventHandler\OfflineEvents\Index;

use AppUtils\ClassHelper as ClassHelper;
use AppUtils\Collections\BaseClassLoaderCollectionMulti as BaseClassLoaderCollectionMulti;
use Application\EventHandler\OfflineEvents\OfflineEventInterface as OfflineEventInterface;
use Application_Interfaces_Loggable as Application_Interfaces_Loggable;
use Application_Traits_Loggable as Application_Traits_Loggable;
use Mistralys\AppFramework\AppFramework as AppFramework;
use ReflectionClass as ReflectionClass;

/**
 * @method OfflineEventInterface[] getAll()
 */
class EventClassFinder extends BaseClassLoaderCollectionMulti implements Application_Interfaces_Loggable
{
	use Application_Traits_Loggable;

	public function getLogIdentifier(): string
	{
		/* ... */
	}


	public function serialize(): array
	{
		/* ... */
	}


	public function getByID(string $id): OfflineEventInterface
	{
		/* ... */
	}


	public function getInstanceOfClassName(): string
	{
		/* ... */
	}


	public function getClassFolders(): array
	{
		/* ... */
	}


	public function isRecursive(): bool
	{
		/* ... */
	}


	public function getDefaultID(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/EventHandler/OfflineEvents/Index/EventIndex.php`

```php
namespace Application\EventHandler\OfflineEvents\Index;

use AppUtils\FileHelper\PHPFile as PHPFile;
use Application\Application as Application;
use Application\EventHandler\OfflineEvents\OfflineEventException as OfflineEventException;
use Application\EventHandler\OfflineEvents\OfflineEventInterface as OfflineEventInterface;
use Application\EventHandler\OfflineEvents\OfflineEventListenerInterface as OfflineEventListenerInterface;
use Application_Interfaces_Loggable as Application_Interfaces_Loggable;
use Application_Traits_Loggable as Application_Traits_Loggable;

class EventIndex implements Application_Interfaces_Loggable
{
	use Application_Traits_Loggable;

	public const KEY_EVENTS = 'events';
	public const KEY_LISTENERS = 'listeners';

	public static function getInstance(): EventIndex
	{
		/* ... */
	}


	public static function getIndexFile(): PHPFile
	{
		/* ... */
	}


	public function getLogIdentifier(): string
	{
		/* ... */
	}


	/**
	 * @param string $eventName
	 * @return class-string<OfflineEventInterface>|NULL
	 */
	public function getEventClass(string $eventName): ?string
	{
		/* ... */
	}


	public function getAllEventClasses(): array
	{
		/* ... */
	}


	public function getAllListenerClasses(): array
	{
		/* ... */
	}


	/**
	 * @param string|OfflineEventInterface $event
	 * @return class-string<OfflineEventListenerInterface>[]
	 */
	public function getListenerClasses(string|OfflineEventInterface $event): array
	{
		/* ... */
	}


	public function eventClassExists(string $class): bool
	{
		/* ... */
	}


	public function listenerClassExists(string $class): bool
	{
		/* ... */
	}


	/**
	 * Resets the in-memory index so the next read re-reads the
	 * data file from disk, refreshing this singleton instance in
	 * place instead of leaving it serving stale data.
	 */
	public function clearIndexCache(): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/EventHandler/OfflineEvents/Index/EventIndexCacheLocation.php`

```php
namespace Application\EventHandler\OfflineEvents\Index;

use AppUtils\FileHelper\PHPFile as PHPFile;
use Application\CacheControl\BaseCacheLocation as BaseCacheLocation;
use Application\CacheControl\RegeneratingCacheLocationInterface as RegeneratingCacheLocationInterface;

/**
 * Cache location description class for the offline events index,
 * so it can be handled via the cache control system.
 *
 * The index is a runtime-required, committed artefact: clearing
 * it would leave the application without an offline event/listener
 * index until the next explicit build, so it implements
 * {@see RegeneratingCacheLocationInterface} to be rebuilt in place
 * by the cache control system instead of being deleted.
 *
 * @package Application
 * @subpackage EventHandler
 */
class EventIndexCacheLocation extends BaseCacheLocation implements RegeneratingCacheLocationInterface
{
	public const CACHE_ID = 'OfflineEventIndex';

	public function getID(): string
	{
		/* ... */
	}


	public function getByteSize(): int
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	/**
	 * Deletes the index file, without rebuilding it.
	 *
	 * > NOTE: This is only called directly by consumers that bypass
	 * > the cache manager. {@see \Application\CacheControl\CacheManager}
	 * > never calls this method on this class, using {@see self::regenerate()}
	 * > instead, since this is a {@see RegeneratingCacheLocationInterface}.
	 */
	public function clear(): void
	{
		/* ... */
	}


	/**
	 * Rebuilds the index file in place via {@see EventIndexer::reindex()},
	 * bypassing its once-per-process guard, and resets the {@see EventIndex}
	 * singleton's in-memory cache so the next read picks up the freshly
	 * built data.
	 *
	 * @throws \Throwable On build failures.
	 */
	public function regenerate(): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/EventHandler/OfflineEvents/Index/EventIndexer.php`

```php
namespace Application\EventHandler\OfflineEvents\Index;

use Application_Interfaces_Loggable as Application_Interfaces_Loggable;
use Application_Traits_Loggable as Application_Traits_Loggable;

class EventIndexer implements Application_Interfaces_Loggable
{
	use Application_Traits_Loggable;

	public static function getInstance(): EventIndexer
	{
		/* ... */
	}


	public function getLogIdentifier(): string
	{
		/* ... */
	}


	public function index(): void
	{
		/* ... */
	}


	/**
	 * Rewrites the index file unconditionally, regardless of whether
	 * {@see self::index()} has already run in the current process.
	 *
	 * Used by {@see EventIndexCacheLocation::regenerate()} to refresh
	 * the index in place, since {@see self::index()}'s once-per-process
	 * guard must stay in place for its other callers (e.g. the Composer
	 * build script) to remain idempotent.
	 */
	public function reindex(): void
	{
		/* ... */
	}


	public function countEvents(): int
	{
		/* ... */
	}


	public function countListeners(): int
	{
		/* ... */
	}


	/**
	 * @return array<string|int, mixed>
	 */
	public function serialize(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/EventHandler/OfflineEvents/Index/Events/RegisterEventIndexCacheListener.php`

```php
namespace Application\EventHandler\OfflineEvents\Index\Events;

use Application\CacheControl\Events\BaseRegisterCacheLocationsListener as BaseRegisterCacheLocationsListener;
use Application\EventHandler\OfflineEvents\Index\EventIndexCacheLocation as EventIndexCacheLocation;

/**
 * Registers the offline event index cache location.
 *
 * @package Application
 * @subpackage EventHandler
 *
 * @see EventIndexCacheLocation
 */
class RegisterEventIndexCacheListener extends BaseRegisterCacheLocationsListener
{
}


```
###  Path: `/src/classes/Application/EventHandler/OfflineEvents/Index/ListenerClassFinder.php`

```php
namespace Application\EventHandler\OfflineEvents\Index;

use AppUtils\ClassHelper as ClassHelper;
use AppUtils\Collections\BaseClassLoaderCollectionMulti as BaseClassLoaderCollectionMulti;
use Application\EventHandler\OfflineEvents\OfflineEventListenerInterface as OfflineEventListenerInterface;
use Application_Interfaces_Loggable as Application_Interfaces_Loggable;
use Application_Traits_Loggable as Application_Traits_Loggable;
use Mistralys\AppFramework\AppFramework as AppFramework;
use ReflectionClass as ReflectionClass;

/**
 * @method OfflineEventListenerInterface[] getAll()
 */
class ListenerClassFinder extends BaseClassLoaderCollectionMulti implements Application_Interfaces_Loggable
{
	use Application_Traits_Loggable;

	public function getLogIdentifier(): string
	{
		/* ... */
	}


	public function serialize(): array
	{
		/* ... */
	}


	public function getByID(string $id): OfflineEventListenerInterface
	{
		/* ... */
	}


	public function getInstanceOfClassName(): string
	{
		/* ... */
	}


	public function getClassFolders(): array
	{
		/* ... */
	}


	public function isRecursive(): bool
	{
		/* ... */
	}


	public function getDefaultID(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/EventHandler/OfflineEvents/OfflineEventContainer.php`

```php
namespace Application\EventHandler\OfflineEvents;

use AppUtils\ClassHelper as ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException as BaseClassHelperException;
use Application\EventHandler\EventManager as EventManager;
use Application\EventHandler\OfflineEvents\Index\EventIndex as EventIndex;
use Throwable as Throwable;

/**
 * Specialized offline event class used to handle an offline
 * event that can wake up listeners and trigger them.
 *
 * @package Application
 * @subpackage Events
 */
class OfflineEventContainer
{
	public function getEventName(): string
	{
		/* ... */
	}


	/**
	 * @return array<int,mixed>
	 */
	public function getArgs(): array
	{
		/* ... */
	}


	/**
	 * @return OfflineEventListenerInterface[]
	 */
	public function getListeners(): array
	{
		/* ... */
	}


	public function getListenerClasses(): array
	{
		/* ... */
	}


	public function hasListeners(): bool
	{
		/* ... */
	}


	public function getTriggeredEvent(): ?OfflineEventInterface
	{
		/* ... */
	}


	public function trigger(): ?OfflineEventInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/EventHandler/OfflineEvents/OfflineEventException.php`

```php
namespace Application\EventHandler\OfflineEvents;

use EventHandlingException as EventHandlingException;

class OfflineEventException extends EventHandlingException
{
	public const ERROR_INDEX_FILE_INVALID = 97202;
	public const ERROR_EVENT_NOT_FOUND_IN_INDEX = 97203;
}


```
###  Path: `/src/classes/Application/EventHandler/OfflineEvents/OfflineEventInterface.php`

```php
namespace Application\EventHandler\OfflineEvents;

use Application\EventHandler\Event\EventInterface as EventInterface;

interface OfflineEventInterface extends EventInterface
{
}


```
###  Path: `/src/classes/Application/EventHandler/OfflineEvents/OfflineEventListenerInterface.php`

```php
namespace Application\EventHandler\OfflineEvents;

use AppUtils\Interfaces\StringPrimaryRecordInterface as StringPrimaryRecordInterface;
use AppUtils\NamedClosure as NamedClosure;

interface OfflineEventListenerInterface extends StringPrimaryRecordInterface
{
	/**
	 * Unique identifier for this listener.
	 *
	 * > NOTE: This is typically derived from the class name.
	 *
	 * @return string
	 */
	public function getID(): string;


	/**
	 * The name of the offline event this listener is associated with.
	 *
	 * @return string
	 */
	public function getEventName(): string;


	/**
	 * Returns the callable that will be invoked when the event is fired.
	 *
	 * @return NamedClosure
	 */
	public function getCallable(): NamedClosure;


	/**
	 * Higher priority listeners are called first, giving the
	 * possibility to influence the order in which they are
	 * executed.
	 *
	 * @return int
	 */
	public function getPriority(): int;
}


```
###  Path: `/src/classes/Application/EventHandler/OfflineEvents/OfflineEventsManager.php`

```php
namespace Application\EventHandler\OfflineEvents;

use Application\EventHandler\Event\StandardEvent as StandardEvent;
use Mistralys\AppFrameworkDocs\DocumentationPages as DocumentationPages;

/**
 * Class handling the management of offline events: These
 * are events that are stored on the disk instead of living
 * in memory.
 *
 * ## What are offline events used for?
 *
 * They allow for classes to listen to events even if the
 * class instance is not loaded at the time the event is
 * triggered: the event listener includes everything needed
 * to load the matching class instance, and let it process
 * the event.
 *
 * ## How do the offline events work?
 *
 * When an offline event is triggered, it is converted to
 * a regular event. Listeners are equally converted to
 * regular listeners by "waking" the listening classes, and
 * adding them as listeners.
 *
 * ## Event discovery
 *
 * Offline event classes and listener classes can be placed anywhere
 * in the codebase. The event indexer automatically discovers them by
 * scanning for classes that implement `OfflineEventInterface` and
 * `OfflineEventListenerInterface`.
 *
 * Listeners are linked to events by matching the event name returned
 * by the listener's `getEventName()` method with the event's `getName()`
 * method.
 *
 * ## Class inheritance
 *
 * - Offline events must extend the regular event class, {@see StandardEvent}.
 * - Offline listeners must extend the class {@see BaseOfflineListener}.
 *
 * @package Application
 * @subpackage EventHandler
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see DocumentationPages::OFFLINE_EVENTS
 */
class OfflineEventsManager
{
	/**
	 * @param string $eventName
	 * @param array<int,mixed> $args
	 * @return OfflineEventContainer
	 */
	public function triggerEvent(string $eventName, array $args = []): OfflineEventContainer
	{
		/* ... */
	}


	public function wasEventTriggered(string $eventName): bool
	{
		/* ... */
	}


	/**
	 * @return array<string,OfflineEventContainer[]>
	 */
	public function getTriggeredEvents(): array
	{
		/* ... */
	}


	/**
	 * @param string $eventName
	 * @return OfflineEventContainer[]
	 */
	public function getEventsByName(string $eventName): array
	{
		/* ... */
	}


	/**
	 * Clears the history of triggered events.
	 * @return $this
	 */
	public function clearEventHistory(): self
	{
		/* ... */
	}
}


```