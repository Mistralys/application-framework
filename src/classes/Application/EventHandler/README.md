# Module: Event Handling

## Overview

This module provides a comprehensive event handling system for the application.
It allows developers to create, manage, and respond to various events within 
the application lifecycle.

The module supports three distinct patterns:

1. **Global Event Handling** - Static event manager for application-wide events
2. **Eventables** - Instance-based events for objects that need their own event lifecycle
3. **Offline Events** - Just-in-time listener instantiation for decoupled architectures

## Global Event Handling

The event handling system is built around the concept of events and listeners.
The static `EventManager` class manages the triggering of global events and the 
notification of registered listeners.

### Example Events

- [DriverInstantiated](/src/classes/Application/Driver/Events/DriverInstantiatedEvent.php): Triggered when the application driver is instantiated.
- [ApplicationStarted](/src/classes/Application/Events/ApplicationStartedEvent.php): Triggered when the application has started.
- [SystemShutDown](/src/classes/Application/Events/SystemShutDownEvent.php): Triggered when the application is shutting down.

### Triggering Events

**Triggering a global event:**

```php
use Application\EventHandler\EventManager;EventManager::trigger(
    'EventName', 
    array('param1' => $value1, 'param2' => $value2)
);
```

**Triggering an event with a custom event class:**

```php
use Application\EventHandler\EventManager;EventManager::trigger(
    'EventName', 
    array('param1' => $value1, 'param2' => $value2),
    MyCustomEventClass::class
);
```

### Registering Listeners

Listeners can be freely registered to respond to specific events
identified by their event name.

```php
use Application\EventHandler\Event\EventInterface;
use Application\EventHandler\EventManager;

EventManager::addListener(
    'EventName', 
    function(EventInterface $event) {
        // Handle the event
    },
    'Human-readable Listener Name'
);
```

### Related Classes

**Event Management:**
- [EventManager](EventManager.php): Static class handling global event registration and triggering. Manages listeners and dispatches events to all registered callbacks.

**Event Classes:**
- [EventInterface](Event/EventInterface.php): Interface for all event classes. Defines the contract for accessing event data, cancellation, and arguments.
- [BaseEvent](Event/BaseEvent.php): Abstract base class for events. Provides common functionality for argument handling and cancellation logic.
- [StandardEvent](Event/StandardEvent.php): Default event implementation used when no custom event class is specified.
- [EventListener](Event/EventListener.php): Listener wrapper class that stores callback information and metadata for registered listeners.

**Exceptions:**
- [EventHandlingException](EventHandlingException.php): Base exception for event handling errors.


## Eventables: Event-Aware Objects

Eventable objects are those that can trigger events, and allow listeners
to be registered for those events. They use the event handling system
internally to manage event triggering and listener notification.

Unlike global events, eventable events are scoped to a specific object instance,
allowing for finer control over event handling. This is useful for objects that
have their own lifecycle and need to notify listeners about their internal state changes.

### Key Features

- **Instance-scoped events**: Listeners are attached to specific object instances.
- **Event namespacing**: Events can be namespaced to avoid conflicts.
- **Event ignoring**: Ability to temporarily ignore specific events.
- **Subject access**: Listeners can access the object that triggered the event.

### Related Classes

**Interfaces and Traits:**
- [EventableInterface](Eventables/EventableInterface.php): Interface for classes that support instance-based event handling. Defines methods for adding, removing, and managing listeners.
- [EventableTrait](Eventables/EventableTrait.php): Trait implementing the `EventableInterface`. Use this trait in any class that needs to trigger events.

**Event Classes:**
- [EventableEventInterface](Eventables/EventableEventInterface.php): Interface for eventable events, extending the base `EventInterface` with access to the subject (the object that triggered the event).
- [BaseEventableEvent](Eventables/BaseEventableEvent.php): Abstract base class for eventable events. Stores the subject instance and provides `getSubject()`.
- [StandardEventableEvent](Eventables/StandardEventableEvent.php): Default event implementation for eventables when no custom event class is specified.
- [EventableListener](Eventables/EventableListener.php): Listener class specific to eventables, storing reference to the subject and supporting namespaced event names.

**Exceptions:**
- [EventableException](Eventables/EventableException.php): Exception class for eventable-specific errors.


## Offline Event Handling, aka "Just-In-Time" Event Handling

Called "Offline Events" in the framework, this event handling mechanism allows 
event listeners to be woken up when the events are triggered. This makes it
possible to define listeners for events where a listener cannot be attached
directly to an object instance.

### How It Works

1. When an offline event is triggered, the system checks for any registered listeners
   for that event type. The listeners are class-based and are dynamically instantiated
   when the event occurs. If no listeners are found, the event is simply ignored.
2. The event handler runs the necessary logic to process the event and notifies
   all registered listeners.

### Event Discovery and Organization

Offline event classes and listener classes can be freely placed anywhere in the codebase.
The event indexer automatically discovers them by scanning for classes that implement
the appropriate interfaces (`OfflineEventInterface` and `OfflineEventListenerInterface`).

**Example organization:**

```php
// Event class - can be placed anywhere
class CriticalEvent extends BaseOfflineEvent
{
    public function getName(): string
    {
        return 'CriticalEvent';
    }
}

// Listener class - can be placed anywhere
class LogHandler extends BaseOfflineListener
{
    public function getEventName(): string
    {
        return 'CriticalEvent';
    }
    
    protected function handleEvent(EventInterface $event, ...$args): void
    {
        // Handle the event
    }
}
```

The indexer links listeners to events by matching the event name returned by
`getEventName()` with the event's `getName()` method.

### Listener Priorities

Listeners can define a priority via `getPriority()`. Higher priority listeners
are executed first. Listeners with the same priority are sorted by their ID.

### Indexing Offline Events

The framework includes an indexing utility that scans the codebase for event and event
listener classes and creates an index. This index is then used to quickly look up 
listeners for a given event type, avoiding resource-intensive discovery on every request.

### Related Classes

**Interfaces:**
- [OfflineEventInterface](OfflineEvents/OfflineEventInterface.php): Interface for offline events, extending `EventInterface`.
- [OfflineEventListenerInterface](OfflineEvents/OfflineEventListenerInterface.php): Interface for offline listeners. Defines methods for ID, event name, callable retrieval, and priority.

**Base Classes:**
- [BaseOfflineEvent](OfflineEvents/BaseOfflineEvent.php): Abstract base class for offline events. Extend this to create custom offline events.
- [BaseOfflineListener](OfflineEvents/BaseOfflineListener.php): Abstract base class for offline listeners. Extend this and implement `handleEvent()` to handle events.

**Event Management:**
- [OfflineEventsManager](OfflineEvents/OfflineEventsManager.php): Main manager for offline events. Handles triggering events and tracking triggered events.
- [OfflineEventContainer](OfflineEvents/OfflineEventContainer.php): Container for a single offline event instance. Loads listeners, sorts them by priority, and triggers them.

**Event Indexing and Discovery:**
- [EventIndexer](OfflineEvents/Index/EventIndexer.php): Indexing utility that scans the codebase and generates the event index file.
- [EventIndex](OfflineEvents/Index/EventIndex.php): Utility class to query the generated event index. Provides methods to look up event classes and their listeners.
- [EventClassFinder](OfflineEvents/Index/EventClassFinder.php): Scans the codebase to discover all classes implementing `OfflineEventInterface`.
- [ListenerClassFinder](OfflineEvents/Index/ListenerClassFinder.php): Scans the codebase to discover all classes implementing `OfflineEventListenerInterface`.

**Exceptions:**
- [OfflineEventException](OfflineEvents/OfflineEventException.php): Exception class for offline event errors.


## Event Traits

The module provides traits for common event patterns that can be reused
across different event types.

### HTML Processing Events

These traits enable events that allow HTML content to be accessed and modified
by listeners.

**Related Classes:**
- [HTMLProcessingEventInterface](Traits/HTMLProcessingEventInterface.php): Interface for events that process HTML content. Provides `getHTML()`, `setHTML()`, and `replace()` methods.
- [HTMLProcessingEventTrait](Traits/HTMLProcessingEventTrait.php): Trait implementing the interface. Requires implementing `getHTMLArgumentIndex()` to specify which argument contains the HTML.
