# Plan: v7.0.0 Upgrade Documentation and Helper Script

**Date**: 2026-02-09  
**Status**: Ready for Implementation  
**Complexity**: High (Breaking-XXL Release)

## Overview

Create comprehensive upgrade documentation for the v7.0.0 "Breaking-XXL" release, including:
1. Detailed upgrade guide following existing documentation patterns
2. Automated helper script to scan applications for deprecated class usage
3. Complete class mapping reference table
4. Migration examples for all affected systems

## Context

The v7.0.0 release contains systematic breaking changes centered on:
- **Class reorganization**: Classes moved into thematically organized folders with proper namespacing
- **Admin screen system overhaul**: Dynamic loading with sitemap auto-discovery
- **Events system refactoring**: Auto-discovery of event/listener classes
- **MCP/AI integration**: Context-as-Code support for agentic development
- **Type safety improvements**: Extensive type hints and strict typing

Changes are well-documented in `changelog.md` with detailed commit summaries. Backward compatibility is maintained through deprecated class wrappers.

## Breaking Changes Summary

### 1. Class Locations and Namespaces (PRIMARY PATTERN)

Classes moved from root folder into thematically organized subfolders with proper PHP namespacing.

**Key Examples**:
- `Application_Exception` → `Application\Exception\ApplicationException`
- `Application_FilterSettings` → `Application\FilterSettingsInterface`
- `UI` class → Moved to organized location
- Media library classes → `Application\Media\*` namespace
- Session events → `Application\Session\Events\*`

**Pattern**: Old underscore-based pseudo-namespaces replaced with proper PHP namespaces.

### 2. Admin Screen System (MAJOR BREAKING)

**Old Approach**:
- Screens in fixed `Area` folder structure
- Hardcoded screen locations
- Manual sitemap maintenance

**New Approach**:
- Screens can be placed alongside their modules
- Dynamic loading by class name
- `RegisterAdminScreenFolders` offline event to register screen locations
- Automatic sitemap generation with caching
- Admin screens indexed on build

**Affected Screens**:
- Users management
- News central
- Media library
- Time tracker
- UI translation
- Countries management
- Tags management
- Developer screens

### 3. Offline Events System (BREAKING)

**Changes Required**:
- Events must extend `BaseOfflineEvent`
- Listeners must provide event name via `getEventName()`
- No more `wakeUp()` method needed
- Special `OfflineEvents` folder no longer required
- Event/listener classes auto-discovered

**Examples**:
- Session events moved to `Application\Session\Events\`
- Media events moved to `Application\Media\Events\`
- New `RegisterAdminScreenFolders` event

### 4. Database Changes (MINIMAL)

**Required SQL Updates**:
- `2025-12-19-app-sets.sql` - AppSets feature database storage
- This is the ONLY database change for v7.0.0

### 5. Deprecated Classes

Old class names marked as deprecated with `@deprecated` tags pointing to new locations. Backward compatibility maintained but classes will be removed in future version.

## Upgrade Guide Structure

Following the pattern from `docs/upgrade-guides/upgrade-guide-v5.5.0.md`:

```markdown
# Upgrade Guide: v7.0.0

## Overview
- Scope and impact summary
- Version compatibility (upgrading from v6.x)

## Prerequisites
- Minimum PHP version requirements
- Backup recommendations
- Estimated migration time

## Database Updates
### Required SQL Scripts
- 2025-12-19-app-sets.sql
- Import instructions

## Breaking Changes

### 1. Class Locations and Namespaces
#### Overview
- Pattern explanation
- Impact on existing code

#### Class Mapping Reference Table
Complete old → new mappings (50+ entries):
| Old Class | New Class/Namespace | Status |
|-----------|---------------------|--------|
| Application_Exception | Application\Exception\ApplicationException | Deprecated |
| Application_FilterSettings | Application\FilterSettingsInterface | Deprecated |
| [etc.] | [etc.] | [etc.] |

#### Migration Steps
1. Search for deprecated class usage
2. Update use statements
3. Update class references
4. Test affected functionality

#### Code Examples
```php
// OLD
throw new Application_Exception('Error');

// NEW
use Application\Exception\ApplicationException;
throw new ApplicationException('Error');
```

### 2. Admin Screen System Migration
#### Overview
- Architectural change explanation
- Benefits of new approach

#### Migration Steps
1. Identify custom admin screens
2. Move screens to module-adjacent locations (optional)
3. Implement `getAdminScreensFolder()` in module
4. Create `RegisterAdminScreenFolders` event listener
5. Register listener
6. Test screen accessibility

#### Complete Example
```php
// Before: Screen in fixed location
// /Area/Devel/MyCustomScreen.php

// After: Screen alongside module
// /MyModule/Admin/Screens/MyCustomScreen.php

// Module class
class MyModule
{
    public static function getAdminScreensFolder(): string
    {
        return __DIR__ . '/Admin/Screens';
    }
}

// Event listener
class MyScreenFoldersListener extends BaseRegisterAdminScreenFoldersListener
{
    public function handleEvent(RegisterAdminScreenFolders $event): void
    {
        $event->addFolder(MyModule::getAdminScreensFolder());
    }
}
```

### 3. Offline Events System Migration
#### Overview
- Event discovery changes
- Listener base class requirements

#### Migration Steps
1. Update event classes to extend `BaseOfflineEvent`
2. Update listeners to extend appropriate base listener
3. Remove `wakeUp()` methods
4. Implement `getEventName()` in listeners
5. Move events to thematic namespaces (recommended)

#### Code Examples
```php
// OLD
class MyListener
{
    public function wakeUp(Application_EventHandler_OfflineEvents_OfflineEvent $event) 
    {
        // Setup
    }
    
    public function handleEvent(array $data) 
    {
        // Handle
    }
}

// NEW
namespace MyApp\Events;

use Application\OfflineEvents\BaseOfflineEventListener;

class MyListener extends BaseOfflineEventListener
{
    public function getEventName(): string 
    { 
        return MyEvent::class; 
    }
    
    public function handleEvent(MyEvent $event): void
    {
        // Handle - no wakeUp needed
    }
}
```

### 4. Media Library Changes
#### Class Renames
- Collection class namespace updates
- Admin screen relocations

#### Migration Example
```php
// OLD
$media = Application_Media::getInstance();

// NEW
use Application\Media\Collection\MediaCollection;
$media = MediaCollection::getInstance();
```

### 5. Deprecated Screen Base Classes
#### Mapping Table
| Old Base Class | New Base Class | Usage |
|----------------|----------------|-------|
| Application_Admin_Area_Mode_CollectionCreateScreen | DBHelper\Admin\Screens\Mode\BaseRecordCreateMode | Create screens |
| Application_Admin_Area_Mode_CollectionRecordScreen | DBHelper\Admin\Screens\Mode\BaseRecordMode | Edit screens |
| [etc.] | [etc.] | [etc.] |

## Step-by-Step Migration Guide

### Phase 1: Preparation (15-30 minutes)
1. Create full backup of application
2. Review this guide completely
3. Run automated deprecated class scanner
4. Review scanner report
5. Estimate migration scope

### Phase 2: Database Updates (5 minutes)
1. Execute `2025-12-19-app-sets.sql`
2. Verify table creation
3. No data migration required

### Phase 3: Class Reference Updates (1-3 hours)
1. Update namespace imports
2. Replace deprecated class references
3. Update type hints
4. Focus on most-used classes first:
   - Exception classes
   - Media library
   - Filter settings
   - Admin screens

### Phase 4: Admin Screen Migration (30 minutes - 2 hours)
1. Identify custom admin screens
2. Optionally relocate screens
3. Implement `getAdminScreensFolder()` methods
4. Create and register event listeners
5. Clear cache and rebuild

### Phase 5: Event Listener Updates (30 minutes - 1 hour)
1. Update custom offline events
2. Update custom event listeners
3. Remove `wakeUp()` methods
4. Implement `getEventName()`
5. Test event firing

### Phase 6: Testing (1-2 hours)
1. Clear all caches
2. Test admin screen access
3. Test custom event functionality
4. Test media library features
5. Run application test suite
6. Manual smoke testing

### Phase 7: Cleanup (30 minutes)
1. Remove old commented code
2. Update inline documentation
3. Document any deferred changes
4. Commit migration changes

## Automated Helper Script

### Purpose
Scan application codebase for deprecated class usage and generate migration report.

### Location
`tools/upgrade-to-v7.php`

### Features
1. **Scan for deprecated classes**: Search PHP files for old class names
2. **Generate report**: List files, line numbers, and suggested replacements
3. **Priority ranking**: Order by frequency of usage
4. **Export options**: Console output, HTML report, JSON export

### Usage
```bash
# Scan entire application
php tools/upgrade-to-v7.php /path/to/application

# Scan specific folder
php tools/upgrade-to-v7.php /path/to/application/assets/classes

# Generate HTML report
php tools/upgrade-to-v7.php /path/to/application --format=html --output=report.html

# JSON export for automated processing
php tools/upgrade-to-v7.php /path/to/application --format=json --output=report.json
```

### Sample Output
```
=================================================================
 Application Framework v7.0.0 Upgrade Scanner
=================================================================

Scanning: /path/to/application
Found 47 deprecated class usages in 23 files

PRIORITY 1: High-frequency deprecated classes (10+ usages)
-----------------------------------------------------------
Application_Exception (15 usages)
  - /assets/classes/MyApp/Module.php:45, 67, 89
  - /assets/classes/MyApp/Helper.php:23, 156
  Replacement: Use Application\Exception\ApplicationException

PRIORITY 2: Medium-frequency deprecated classes (3-9 usages)
-----------------------------------------------------------
Application_Media (6 usages)
  - /assets/classes/MyApp/MediaHandler.php:12, 34, 56, 78, 90, 102
  Replacement: Use Application\Media\Collection\MediaCollection

[etc.]

Admin Screens Requiring Migration:
-----------------------------------
  - /Area/MyModule/CustomScreen.php
    Action: Implement getAdminScreensFolder() and register via event

Offline Events Requiring Migration:
------------------------------------
  - /assets/classes/MyApp/Events/MyCustomListener.php
    Action: Remove wakeUp(), implement getEventName()

=================================================================
Summary: 47 deprecated usages, 2 screen migrations, 1 event migration
Estimated effort: 2-4 hours
=================================================================
```

### Implementation Details

**Class Mapping Database**: Hardcoded array of deprecated classes with replacements:
```php
$classMap = [
    'Application_Exception' => [
        'new' => 'Application\Exception\ApplicationException',
        'namespace' => 'Application\Exception',
        'type' => 'class',
        'priority' => 'high'
    ],
    'Application_FilterSettings' => [
        'new' => 'Application\FilterSettingsInterface',
        'namespace' => 'Application',
        'type' => 'interface',
        'priority' => 'high'
    ],
    // [50+ more entries]
];
```

**Scanning Logic**:
1. Recursively scan PHP files
2. Parse for class usage (new, extends, implements, type hints, use statements)
3. Match against deprecated class map
4. Collect file locations and line numbers
5. Generate prioritized report

**Special Detections**:
- Admin screen base class usage (triggers migration notice)
- Offline event listener patterns (triggers migration notice)
- `Area` folder structure usage

## Testing Checklist

### Critical Functionality
- [ ] Application boots without errors
- [ ] Database connection successful
- [ ] User authentication works
- [ ] Admin area accessible

### Admin Screens
- [ ] All default admin screens load
- [ ] Custom admin screens accessible
- [ ] Navigation menu complete
- [ ] Screen tabs/modes work
- [ ] Form submissions successful

### Events System
- [ ] Session events fire correctly
- [ ] Media events fire correctly
- [ ] Custom events fire correctly
- [ ] Event listeners execute

### Media Library
- [ ] Media collection loads
- [ ] Media upload works
- [ ] Media editing works
- [ ] Media deletion works

### Application-Specific
- [ ] [Add application-specific tests]
- [ ] [Add application-specific tests]

## Common Issues and Solutions

### Issue: "Class not found" errors
**Cause**: Deprecated class reference not updated  
**Solution**: Use upgrade scanner to find all usages, update namespaces

### Issue: Admin screen not appearing
**Cause**: Screen not registered via `RegisterAdminScreenFolders` event  
**Solution**: Create event listener and register screen folder

### Issue: Events not firing
**Cause**: Listener not implementing `getEventName()` or wrong base class  
**Solution**: Update listener to extend proper base class, implement `getEventName()`

### Issue: "Interface not found" errors
**Cause**: `FilterSettingsInterface` namespace not imported  
**Solution**: Add `use Application\FilterSettingsInterface;`

### Issue: Cache-related errors
**Cause**: Old cached data referencing old class locations  
**Solution**: Clear all caches (storage/cache/*, storage/compiled/*)

## Deprecation Timeline

**v7.0.0**: Deprecated classes available with warnings  
**v7.1.0**: Deprecated classes still available  
**v8.0.0**: Deprecated classes removed (estimated Q3 2026)

**Recommendation**: Migrate immediately to avoid breaking changes in v8.0.0

## Additional Resources

- **Changelog**: `changelog.md` - Detailed commit summaries
- **Changelog History**: `docs/changelog-history/v6-changelog.md` - Historical v6.x changes
- **Framework Documentation**: `docs/` - General framework guides
- **Example Applications**: Contact framework maintainer for example migrations

## Version Compatibility

**Upgrading From**: v6.0.0, v6.1.0, v6.1.1, v6.2.0, v6.3.0  
**Upgrading To**: v7.0.0  
**PHP Requirements**: PHP 8.0+ (PHP 8.5 support planned)  
**Database**: MySQL 5.7+, MariaDB 10.2+

## Support

For migration assistance or issues:
1. Review this guide thoroughly
2. Run automated scanner
3. Check changelog for additional details
4. Contact framework maintainer

---

**Document Version**: 1.0  
**Last Updated**: 2026-02-09  
**Applies To**: Application Framework v7.0.0
```

## Implementation Steps

### Step 1: Create Upgrade Guide Document
**File**: `docs/upgrade-guides/upgrade-guide-v7.0.0.md`

**Tasks**:
1. Copy structure template above
2. Extract complete class mapping from:
   - Git history analysis
   - Changelog commit summaries  
   - Deprecated class files in `_deprecated/` folder
3. Create comprehensive mapping table (target: 50+ entries)
4. Write detailed examples for each section
5. Add application-specific testing checklist
6. Review and refine language

**Estimated Time**: 3-4 hours

### Step 2: Create Automated Helper Script
**File**: `tools/upgrade-to-v7.php`

**Requirements**:
1. Command-line interface
2. Recursive PHP file scanning
3. Pattern matching for deprecated classes
4. Report generation (console, HTML, JSON)
5. Priority ranking
6. Admin screen detection
7. Event listener detection

**Core Components**:

```php
#!/usr/bin/env php
<?php
/**
 * Application Framework v7.0.0 Upgrade Scanner
 * 
 * Scans application code for deprecated class usage and generates
 * migration reports.
 */

class UpgradeScanner
{
    private array $classMap = []; // Deprecated class mappings
    private array $findings = []; // Scan results
    private string $scanPath;
    
    public function __construct(string $scanPath)
    {
        $this->scanPath = $scanPath;
        $this->initClassMap();
    }
    
    private function initClassMap(): void
    {
        // Load all deprecated class mappings
        // Extract from framework's deprecated classes
    }
    
    public function scan(): void
    {
        // Recursively scan PHP files
        // Parse for class usage
        // Match against deprecated map
        // Store findings
    }
    
    public function generateReport(string $format = 'console'): void
    {
        // Generate report in specified format
        // Priority ranking
        // File grouping
        // Actionable recommendations
    }
    
    private function detectAdminScreens(): array
    {
        // Detect admin screens needing migration
    }
    
    private function detectEventListeners(): array
    {
        // Detect event listeners needing updates
    }
}

// CLI execution
$scanner = new UpgradeScanner($argv[1] ?? getcwd());
$scanner->scan();
$scanner->generateReport($options['format'] ?? 'console');
```

**Features to Implement**:
- [ ] Class mapping database (50+ entries)
- [ ] Recursive file scanner
- [ ] Class usage detection (new, extends, implements, type hints, use)
- [ ] Admin screen pattern detection
- [ ] Event listener pattern detection
- [ ] Priority ranking algorithm
- [ ] Console output formatter
- [ ] HTML report generator
- [ ] JSON export
- [ ] Summary statistics
- [ ] Progress indicator for large scans

**Estimated Time**: 4-6 hours

### Step 3: Extract Complete Class Mapping

**Sources**:
1. Git history: `git log --all --oneline --grep="moved" --since="2025-01-01"`
2. Changelog: Parse commit summaries in `changelog.md`
3. Deprecated files: Scan `src/classes/_deprecated/` folder
4. New namespaced classes: Scan organized folders

**Mapping Categories**:
- Core classes (Exception, Interfaces, Utils)
- Media library classes
- Admin screen base classes
- Event classes
- Session classes
- Deployment classes
- UI classes

**Format**:
```
OLD_CLASS | NEW_CLASS | NAMESPACE | TYPE | PRIORITY
```

**Estimated Time**: 2-3 hours

### Step 4: Testing and Validation

**Test on Sample Application**:
1. Create test application using v6.3.0
2. Add various deprecated class usages
3. Run upgrade scanner
4. Follow upgrade guide step-by-step
5. Verify all functionality works
6. Document any issues

**Refine Documentation**:
1. Update guide based on test findings
2. Add common issues encountered
3. Improve examples
4. Clarify ambiguous instructions

**Estimated Time**: 2-3 hours

### Step 5: Review and Finalize

**Review Checklist**:
- [ ] All breaking changes documented
- [ ] Code examples tested and verified
- [ ] Class mapping table complete (50+ entries)
- [ ] Scanner script functional
- [ ] Testing checklist comprehensive
- [ ] Language clear and actionable
- [ ] Formatting consistent
- [ ] Links to related docs valid

**Final Steps**:
1. Proofread entire guide
2. Test scanner on real applications
3. Get feedback from framework maintainer
4. Make final revisions
5. Mark document as complete

**Estimated Time**: 1-2 hours

## Total Estimated Effort

**Documentation**: 6-9 hours  
**Scanner Script**: 4-6 hours  
**Testing**: 2-3 hours  
**Review**: 1-2 hours  

**Total**: 13-20 hours

## Success Criteria

1. ✅ Upgrade guide covers all breaking changes in v7.0.0
2. ✅ Class mapping table includes 50+ deprecated classes
3. ✅ Scanner script successfully detects deprecated usage
4. ✅ Scanner script generates actionable reports
5. ✅ Guide tested on sample application successfully
6. ✅ All code examples tested and verified
7. ✅ Documentation follows existing pattern
8. ✅ Testing checklist is comprehensive

## Notes

- This is a "Breaking-XXL" release - comprehensive documentation is critical
- Backward compatibility via deprecated classes gives users migration runway
- Scanner script provides significant value for large applications
- Template can be reused for future major version upgrades
- Consider creating video walkthrough for complex migrations (future enhancement)

## Related Documents

- `changelog.md` - Detailed v7.0.0 changes
- `docs/changelog-history/v6-changelog.md` - Historical context
- `docs/upgrade-guides/upgrade-guide-v5.5.0.md` - Template reference
- `VERSION` - Current framework version (7.0.0)

---

**Plan Status**: ✅ Ready for Implementation  
**Approval Date**: 2026-02-09  
**Implementation Target**: Q1 2026
