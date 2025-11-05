## v5.11.3 - Connector cache fix redux
- Connectors: Fixed unhandled cases of loading broken JSON from the cache.
- Error handling: Exceptions now also add a message in the application log.
- Helpers: Added the specialized `JSONUnserializer` class with integrated logging.

## v5.11.2 - Connector cache fix
- Connectors: Fixed connector IDs stored in cache causing an exception.

## v5.11.1 - Connector improvements
- Connectors: Constructor arguments can now be specified for connector methods.

## v5.11.0 - PHP8 and Connector improvements (Breaking-S)
- Connectors: Added constructor arguments to `createConnector()`.
- Connectors: Fixed `createConnector()` creating a new instance with every call.
- Dependencies: Now requiring PHP8.4.

### Breaking changes (S)

The framework has been officially switched to PHP8.4. It has been updated to run for a while,
and this change only makes it official. No other breaking changes are included in this version.
