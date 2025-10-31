# Application Framework Changelog

## v5.13.2 - API validation helpers
- API: Added `validateAs()` to the string parameter class to choose common validations.
- API: Added a series of common validation types for API parameters, like email, URL, date, etc.
- API: Added parameter classes for common types, like email, URL, date, etc.
- Users: Fixed the missing MD5 email column when setting up system user entries.
- UI: Fixed the generic button trait missing some button interface methods.
- Sessions: Fixed some errors in the OAuth session handling trait.
- Tests: Added several stubs to enable static analysis of traits that were not analyzed before.

## v5.13.1 - Small improvements
- API: Added the `dryRun` API parameter trait to handle triggering dry-run operations.
- API: Exceptions are now logged to the error log even if they are non-framework exceptions.
- Exceptions: Exceptions now return their log ID with `getLogID()`.

## v5.13.0 - User management (Breaking-L)
- Users: Added a user interface to manage known users.
- Users: Upgraded the user table for additional information, like the foreign nickname.
- Users: Added user rights to manage known users.
- Users: Added hashing of email addresses for faster database queries (partially implemented).
- Changelog: Moved v5 entries into multiple files to avoid overly cluttering this file.
- API: Added `selectAppCountry()` to the app country API trait for manual selection.
- LDAP: Removed the deprecated PHP7-style connection code, now that we require PHP8.4.
- Users: Fixed the user rights list sorting breaking the associative array.
- Users: Added a maintenance script to hash existing email addresses in the database.

### Breaking changes (L)

- The user management requires an update script to be run on the database. 
  This can be done on production, as the script does not create conflicts with 
  existing data.
- The updater classes have been restructured. If you have custom updater scripts, 
  update them to use the new namespaced classes.

### Update guide

1. Import the SQL file [2025-10-29-users.sql](/docs/sql/2025-10-29-users.sql).
2. Refactor your updater classes to use the new classes, if applicable.
3. Deploy the application.
4. Run the email hashing maintenance script via the maintenance UI. 
5. Import the SQL file [2025-10-29-users-post-update.sql](/docs/sql/2025-10-29-users-post-update.sql).

---
Older changelog entries can be found in the `docs/changelog-history` folder.
