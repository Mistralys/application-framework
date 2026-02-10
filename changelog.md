# Application Framework Changelog

## v7.0.0 - Screen Loading and Agent Support (Breaking-XXL)

This major release paves the way for agentic coding and a more
flexible way to structure applications. Admin screens can now
be placed alongside their modules, a sitemap generator with
auto-discovery will register them automatically on build.

### Database update

- The SQL file [2025-12-19-app-sets.sql](/docs/sql/2025-12-19-app-sets.sql) must be imported 
  to create the necessary tables for the updated application sets feature.

### Detailed commit summary

#### Admin Screen System
- Added sitemap rendering and developer screen for reviewing the sitemap
- Screen index now stores subscreen classes with getter methods
- Admin screens now support relative folder paths and folder location logging
- Added `RegisterAdminScreenFolders` event listener to register screen folders
- Areas do not have parent screens
- Fixed non-unique screen IDs and added counting for content screens
- Added `getAdminScreensFolder()` and admin URL instances support
- Admin index now uses the `AppFolder` class for media screens
- Updated admin screens for dynamic loading (users, news, media, time tracker, translations)
- Removed obsolete admin screen registry events and legacy screen classes

#### MCP Server & AI Integration
- Added MCP server script and classes for AI integration
- Added CTX (Context as Code) integration with context YAML generation
- Added AI tools for Countries module
- Added HTTP MCP server plan and agent documentation
- Integrated `php-mcp/server` dependency
- Added cache event listener for AI system
- Fixed exception when cache folder not present and `getSize()` throwing exception

#### Events System Refactoring
- Events and listener classes moved thematically
- Added offline event indexing with auto-discovery of event/listener classes
- Events must extend `BaseOfflineEvent`, listeners provide event name
- Added event names where applicable and removed unused event parameters
- Added clearing of event history
- Updated offline event handling in Bootstrap and CacheManager

#### AppSets Feature Enhancement
- Converted to database storage with DBHelper collection structure
- Updated admin screens with improved value display
- Added comprehensive tests and `final` class keywords
- Added `hasFilterSettings()` method
- Fixed value compilation and wrong return values
- Added README documentation

#### Database & DBHelper Improvements
- Added base record settings class with README
- Added PDO primitive type return values
- Added checking parent record functionality
- Fixed WHERE clause with empty primaries
- Moved record data loading to dedicated method
- Added `idExists()` without final flag
- Added `UncachedQuery` attribute for marking uncached queries

#### Code Organization & Structure
- Moved numerous classes into thematically related folders
- Moved `ApplicationException`, `FilterSettingsInterface`, `UI`, `Environments`, `Connectors` classes
- Moved API method classes to dedicated folders
- Added `AppFolder` utility class for path management
- Added `getRootFolder()` and `getRootClassesFolder()` to Driver
- Moved `AppFramework` class file to new location
- Split agent guide into subdocuments

#### UI & User Interface
- Added big selection separators
- Buttons: Added conditional make layout methods
- Added interface for admin URL containers
- Welcome screen refactored with manager class and quick nav event
- Added `adminURL()` and `appConfiguration()` helper methods
- Metanav: Updated read news link

#### Exception Handling & Error Management
- Added static utility methods to Exceptions
- More useful exception rendering
- CLI now shows previous exception output
- Added multiple error codes and constants
- Fixed error check when file does not exist on disk

#### Type Safety & Code Quality
- Added extensive type hints across codebase
- Added PHPDocs throughout
- Added Rector for code quality
- PHPStan fixes and typing enhancements
- Added constant type hints
- Strict typing enabled in multiple classes
- Added return types and fixed return type declarations

#### Dependencies & Build
- Updated dependencies including `Psr\Log` downgrade
- Added framework docs as DEV dependency
- Manually added PHPCAS classmap to Composer
- Fixed tests and added various new test cases
- Added JSON handling details
- Updated project files

#### Media Library
- Upgraded screens to use admin index
- Renamed media library classes
- Added `getAdminScreensFolder()` with listener registration
- Improved image handling in WhatsNew feature

#### Miscellaneous
- FilterSettings: Added type-specific setting methods and `addValue()`, `getGrid()`
- WhatsNew: Improved image handling
- Fixed filter hidden variables
- Fixed and future-proofed paths
- Deepl: Added testing screen and moved helper class
- Countries: Made `collectCountry()` public
- Removed deprecated exception usages and obsolete test cases
- TimeTracker: Fixed collection mixup and updated screens
- Added global folder structure documentation
- Fixed bootstrap path after moving files
- Wording improvements and documentation updates



---
Older changelog entries can be found in the `docs/changelog-history` folder.
