## v5.10.12 - CAS rollback
- Composer: Rolled `phpCAS` back to the latest public release, as it forces removal of security advisories.
- Composer: `phpCAS` will be replaced with an alternative soon.

## v5.10.11 - CAS temporary fix
- Composer: Locked the `phpCAS` package to a specific commit to fix the PHP8 deprecation warnings.
- Composer: Temporarily removed `roave/security-advisories` to allow the CAS commit to be used.
- Connectors: Added the utility class `ProxyConfiguration` to handle proxy settings.

## v5.10.10 - LDAP DN validation fix
- LDAP: Tweaked the search DN validation to be more lenient with the LDAP server's setup.

## v5.10.9 - LDAP Logging
- LDAP: Added more logging.
- LDAP: Added `toArray()` in the configuration to dump values.

## v5.10.8 - RequestLog logging
- Logging: Added methods to log messages only when the request log is enabled.
- Logging: Known types (e.g. `Microtime`) are now converted into useful log messages.
- RequestLog: Added static `setActive()` and `isActive()`.
- RequestLog: Now caching the enabled status for the duration of the request.
- LDAP: Added more logging, including debug info in request log mode.
- Tests: Now also enabling the request logging in `enableLogging()`.

## v5.10.7 - LDAP PHP7 fix extended
- LDAP: Added a fallback way to connect to LDAP servers in PHP7.
- LDAP: Now handling host connections with or without an LDAP scheme.
- LDAP: The config SSL setting is now inherited from the host if specified with a scheme.

## v5.10.6 - LDAP PHP7 fix
- LDAP: Fixed wrong settings used to connect in PHP7.

## v5.10.5 - LDAP fixes and improvements
- LDAP: Now automatically switching between ldap function resource and class return types.
- LDAP: Fixed deprecated usage of `ldap_connect` in PHP8.4.
- LDAP: Allowing port to be set to `null` to use the default port.
- LDAP: Added integration tests using Mokapi to set up a mock LDAP server.
- LDAP: Added the debug config setting to debug LDAP connections.
- LDAP: Added a configuration option to turn off SSL connections.
- LDAP: The configuration now allows turning on detailed LDAP debugging messages.
- LDAP: Added a search filter failsafe to return only role-matching rights.
- Environments: Added `setLDAPSSLEnabled()` to toggle SSL connections.

## v5.10.4 - Country improvements
- Countries: Added `resolveCountry()` that accepts a range of values.
- Countries: Added `getByLocalizationCountry()`.

## v5.10.3 - Filter Criteria tweak
- FilterCriteria: Fixed replacing query placeholders in `getQueries()`.

## v5.10.2 - Request improvements
- Connectors: Allowing response code `200` for `PUT` requests.
- Connectors: Added the request methods `useSockets()` and `useCURL()` (default).
- Connectors: Added the request method `setAuthorization()`.
- Connectors: Added the request method `setBodyJSON()`.

## v5.10.1 - Language label fix
- Localization: Fixed getting the language label throwing an exception.

## v5.10.0 - Time Tracker Time Spans (Breaking-S)
- TimeTracker: Added the time span management.
- TimeTracker: Added a summary of durations by ticket number.
- TimeTracker: Added a separate form field for the ticket URL.
- UI: Fixed a PHPDoc that broke the method chaining in `UI_Bootstrap::setName()`.

### Breaking changes

This change only affects the Time Tracker. If you do not use it,
no changes are required. Otherwise, the following SQL update script
must be run:

[2025-06-19-time-tracker.sql](/docs/sql/2025-06-19-time-tracker.sql).
