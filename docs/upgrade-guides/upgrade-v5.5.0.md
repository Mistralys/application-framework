# Upgrade Guide: v5.5.0

## Offline Event Folder Structure

Offline event listener folders are now named after the event name instead
of the event class.

1. Rename the listener folders to the event names.
2. Update the namespace in the listener classes.
3. Use the new base listener classes, e.g. `BaseRegisterCacheLocationsListener`.

## Offline Event Listener Classes

The need to implement the `wakeUp()` method has been removed. Now only the
`handleEvent()` method is required.

1. Remove the `wakeUp()` method.
2. Implement the `handleEvent()` method.

For the framework's offline event listeners, there are now new base classes
that simplify the event handling even further:

- `BaseRegisterCacheLocationsListener`
- `BaseRegisterTagCollectionsListener`
- `BaseSessionInstantiatedListener`

> NOTE: For custom offline event listeners, we recommend creating your own 
> base class to reduce the amount of boilerplate code.

## Taggable Additions

1. Any record collections that are taggable must now implement an offline
   event listener for the `RegisterTagCollections` event, and return the
   class name in `getCollectionRegistrationClass()`.
2. Taggable collections must implement `getTaggableByID()`.

## Application Version

The driver's version-related methods are now all final. The system has been
fixed to an implementation based on extracting the current version from the
`dev-changelog.md` file.

1. Remove all version methods from the driver class.
