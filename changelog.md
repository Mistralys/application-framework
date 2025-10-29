# Application Framework Changelog

## v5.13.0 - User management (Breaking-DB-L)
- Users: Added a user interface to manage known users.
- Users: Upgraded the user table for additional information, like the foreign nickname.
- Users: Added user rights to manage known users.
- Users: Added hashing of email addresses for faster database queries (partially implemented).
- Changelog: Moved v5 entries into multiple files to avoid overly cluttering this file.

### Breaking changes (DB-L)

- The user management requires an update script to be run on the database. 
  Please ensure that the file [/docs/sql/2025-10-29-users.sql] is imported.
  This can be done on production, as it does not create conflicts with existing 
  data.

---
Older changelog entries can be found in the `docs/changelog-history` folder.
