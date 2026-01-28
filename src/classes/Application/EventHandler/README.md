# Module: Event Handling

## Overview

This module provides a comprehensive event handling system for the application.
It allows developers to create, manage, and respond to various events within 
the application lifecycle.

## Global Event Handling

The event handling system is built around the concept of events and listeners.
The static `EventHandler` class manages the triggering of global events and the 
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
use Application\EventHandler\Event\EventInterface;use Application\EventHandler\EventManager;

EventManager::addListener(
    'EventName', 
    function(EventInterface $event) {
        // Handle the event
    },
    'Human-readable Listener Name'
);
```

## Eventables: Event-Aware Objects

Eventable objects are those that can trigger events, and allow listeners
to be registered for those events. They use the event handling system
internally to manage event triggering and listener notification.

### Related classes




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

### Indexing Offline Events

To avoid resource-intensive file I/O operations every time an offline event is triggered,
the framework includes an indexing utility that scans the codebase for event and event
listener classes and creates an index. This index is then used to quickly look up 
listeners for a given event type.

### Related Classes

**Offline Event Handling:**
- [OfflineEventHandler](/src/classes/Application/EventHandler/OfflineEvents/OfflineEventsManager.php): Main manager for offline events. Manages triggering events.
- [BaseOfflineEvent](/src/classes/Application/EventHandler/OfflineEvents/BaseOfflineEvent.php): Base class for all offline events.
- [BaseOfflineListener](/src/classes/Application/EventHandler/OfflineEvents/BaseOfflineListener.php): Base class for all offline event listeners.
- [OfflineEventContainer](/src/classes/Application/EventHandler/OfflineEvents/OfflineEventContainer.php): Main handler for a single offline event. Instantiates and notifies listeners.

**Event indexing and discovery:**
- [EventIndexer](/src/classes/Application/EventHandler/OfflineEvents/Index/EventIndexer.php): Indexing utility for offline events. Discovers listeners throughout the codebase.
- [EventIndex](/src/classes/Application/EventHandler/OfflineEvents/Index/EventIndex.php): Utility class used to query the event index.
- [EventClassFinder](/src/classes/Application/EventHandler/OfflineEvents/Index/EventClassFinder.php): Utility class to find event classes in the codebase.
- [ListenerClassFinder](/src/classes/Application/EventHandler/OfflineEvents/Index/ListenerClassFinder.php): Utility class to find listener classes in the codebase.
