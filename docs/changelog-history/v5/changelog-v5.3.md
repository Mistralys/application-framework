## v5.3.4 - Upgraded localization library
- Countries: Updated return types to avoid using deprecated AppLocalization types.
- Dependencies: Updated AppLocalization to [v1.5.0](https://github.com/Mistralys/application-localization/releases/tag/1.5.0).

## v5.3.3 - AppSets fix
- AppSets: Fixed not properly recognizing areas, now using the Driver's `areaExists()`.

## v5.3.2 - AJAX exception fix.
- AJAX: Fixed a type issue in the AJAX error logger.

## v5.3.1 - Record Setting Properties
- Record Settings: Added possibility to set runtime properties on record settings.

## v5.3.0 - Admin Screen event handling (Deprecation)
- Admin Screens: Added events and listener methods for screen initialization and rendering.
- Admin Screens: Added possibility to replace a screen's content via events.
- Admin Screens: Added the possibility to disable the action handling via events.
- Testing: Added some test screens for the screen event handling.

### Deprecations

- `Application_Admin_ScreenInterface` has been replaced by `AdminScreenInterface`.
