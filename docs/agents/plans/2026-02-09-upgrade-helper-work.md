# Work Packages: v7.0.0 Upgrade Documentation & Helper Script

**Plan Reference**: [2026-02-09-upgrade-helper.md](2026-02-09-upgrade-helper.md)  
**Date Created**: 2026-02-09  
**Status**: Ready for Implementation  
**Total Estimated Effort**: 13-20 hours

## Overview

This document breaks down the v7.0.0 upgrade documentation project into distinct, incrementally implementable work packages. Each package is self-contained with all necessary context for implementation, even if picked up weeks or months later.

## Context Summary

**What**: Create comprehensive upgrade guide and automated scanner for Application Framework v7.0.0 "Breaking-XXL" release.

**Why**: v7.0.0 contains systematic breaking changes (class relocations, namespace additions, admin screen system overhaul, events refactoring) requiring detailed migration documentation.

**Key Changes in v7.0.0**:
- Classes moved into thematically organized folders with PHP namespaces
- Admin screens now dynamically loaded via `RegisterAdminScreenFolders` event
- Offline events refactored with auto-discovery
- Deprecated classes maintain backward compatibility temporarily
- One database change: `2025-12-19-app-sets.sql`

**Key Files**:
- Source: `/Users/smordziol/Webserver/libraries/application-framework/changelog.md`
- Source: `/Users/smordziol/Webserver/libraries/application-framework/src/classes/_deprecated/`
- Template: `/Users/smordziol/Webserver/libraries/application-framework/docs/upgrade-guides/upgrade-guide-v5.5.0.md`
- Output: `/Users/smordziol/Webserver/libraries/application-framework/docs/upgrade-guides/upgrade-guide-v7.0.0.md`
- Script: `/Users/smordziol/Webserver/libraries/application-framework/tools/upgrade-to-v7.php`

---

## Work Package 1: Extract Complete Class Mapping Database

**Objective**: Create a comprehensive reference table of all deprecated class mappings (old → new) for use in documentation and scanner script.

**Status**: 🔲 Not Started  
**Estimated Effort**: 2-3 hours  
**Dependencies**: None  
**Priority**: HIGH (Required for all other packages)

### Context

v7.0.0 moved 50+ classes from root folder into organized subfolders with namespacing. Deprecated wrapper classes maintain backward compatibility. Need complete mapping for:
1. Upgrade guide reference table
2. Scanner script's detection database
3. Migration example generation

### Information Sources

1. **Changelog commits**: `/Users/smordziol/Webserver/libraries/application-framework/changelog.md`
   - Search for: "Moved", "Renamed", "Relocated"
   - Lines approximately 1-500 contain v7.0.0 changes

2. **Deprecated classes folder**: `/Users/smordziol/Webserver/libraries/application-framework/src/classes/_deprecated/`
   - Each file contains `@deprecated` tag pointing to new location
   - Example: `Application_Exception` → `Application\Exception\ApplicationException`

3. **Git history** (if available):
   ```bash
   cd /Users/smordziol/Webserver/libraries/application-framework
   git log --oneline --all --grep="Moved" --since="2025-01-01"
   git log --oneline --all --grep="Renamed" --since="2025-01-01"
   git diff HEAD~100..HEAD --name-status | grep "^R"
   ```

### Tasks

1. **Scan deprecated classes folder**
   - Read all PHP files in `src/classes/_deprecated/`
   - Extract class name from `class ClassName`
   - Extract new location from `@deprecated Use {@see \New\Namespace\ClassName}`
   - Record namespace, type (class/interface/trait)

2. **Parse changelog entries**
   - Extract "Moved X to Y" entries from `changelog.md`
   - Cross-reference with deprecated folder findings
   - Note any classes mentioned but not deprecated

3. **Categorize mappings**
   - **Core classes**: Exceptions, Interfaces, Utils, AppFolder
   - **Media library**: Media, MediaCollection, events
   - **Admin screens**: Base classes, area classes
   - **Session**: Session classes, events
   - **Events**: Offline events, listeners
   - **UI**: UI, renderable classes
   - **Other**: Miscellaneous utilities

4. **Create structured data file**
   - Format: Markdown table or JSON
   - Columns: Old Class | New Class | Namespace | Type | Category | Priority
   - Priority: HIGH (commonly used), MEDIUM, LOW
   - Save to: `docs/upgrade-guides/v7.0.0-class-mappings.md` or `.json`

### Example Entry Format

**Markdown**:
```markdown
| Old Class | New Class | Namespace | Type | Category | Priority |
|-----------|-----------|-----------|------|----------|----------|
| Application_Exception | ApplicationException | Application\Exception | class | Core | HIGH |
| Application_FilterSettings | FilterSettingsInterface | Application | interface | Core | HIGH |
```

**JSON**:
```json
{
  "Application_Exception": {
    "newClass": "ApplicationException",
    "fullNamespace": "Application\\Exception\\ApplicationException",
    "namespace": "Application\\Exception",
    "type": "class",
    "category": "Core",
    "priority": "HIGH",
    "usage": ["throw new", "extends"],
    "notes": "Primary exception class"
  }
}
```

### Acceptance Criteria

- [ ] At least 50 deprecated classes documented
- [ ] All entries include: old name, new name, full namespace, type
- [ ] Classes categorized by module/functionality
- [ ] Priority assigned to each (HIGH/MEDIUM/LOW based on usage frequency)
- [ ] Data saved in both human-readable (Markdown) and machine-readable (JSON) formats
- [ ] Cross-referenced with changelog entries
- [ ] Validated by checking that new classes actually exist in codebase

### Output Files

- `docs/upgrade-guides/v7.0.0-class-mappings.md` (human-readable reference)
- `docs/upgrade-guides/v7.0.0-class-mappings.json` (scanner script data)

### Notes

- Use `grep -r "@deprecated" src/classes/_deprecated/` to quickly find all deprecated classes
- Common patterns in deprecated files:
  ```php
  /**
   * @deprecated Use {@see \New\Namespace\ClassName} instead.
   */
  class Old_ClassName extends \New\Namespace\ClassName
  ```
- If a class is mentioned in changelog but not in `_deprecated/`, it may be a complete removal (not just relocation)

---

## Work Package 2: Create Upgrade Guide Structure & Overview

**Objective**: Create the upgrade guide document with complete structure, overview sections, and prerequisites.

**Status**: 🔲 Not Started  
**Estimated Effort**: 1-2 hours  
**Dependencies**: None (can run parallel to WP1)  
**Priority**: HIGH

### Context

Create the main upgrade guide document following the established pattern from previous upgrade guides. Sets foundation for all detailed content.

### Reference Documents

**Template**: `/Users/smordziol/Webserver/libraries/application-framework/docs/upgrade-guides/upgrade-guide-v5.5.0.md`

**Read sections**:
- Document structure (headers, sections)
- Overview format and tone
- Prerequisites format
- Database updates format
- Step-by-step guide structure

### Tasks

1. **Create document file**
   - Path: `/Users/smordziol/Webserver/libraries/application-framework/docs/upgrade-guides/upgrade-guide-v7.0.0.md`
   - Copy header structure from v5.5.0 guide
   - Update version references to v7.0.0

2. **Write Overview section**
   - Brief summary of v7.0.0 scope
   - Major changes summary (3-5 bullet points)
   - Version compatibility (upgrading from v6.x)
   - Reference to "Breaking-XXL" nature
   - Link to changelog for full details

3. **Write Prerequisites section**
   - PHP version requirements (check `composer.json`)
   - Database requirements (MySQL/MariaDB versions)
   - Required tools (Git, Composer, etc.)
   - Backup recommendations
   - Estimated migration time ranges (by application size)
   - Testing environment recommendation

4. **Write Database Updates section**
   - List required SQL script: `2025-12-19-app-sets.sql`
   - Location: `docs/sql/2025-12-19-app-sets.sql`
   - Import instructions (command-line and GUI)
   - Verification steps
   - Backup recommendations

5. **Create section placeholders**
   - Breaking Changes (to be filled in WP3)
   - Step-by-Step Migration Guide (to be filled in WP4)
   - Testing Checklist (to be filled in WP4)
   - Common Issues and Solutions (to be filled in WP4)
   - Deprecation Timeline
   - Additional Resources
   - Version Compatibility
   - Support

6. **Write Deprecation Timeline section**
   - v7.0.0: Deprecated classes available with warnings
   - v7.1.0: Deprecated classes still available
   - v8.0.0: Deprecated classes will be removed (estimated Q3 2026)
   - Recommendation to migrate immediately

7. **Write Version Compatibility section**
   - Upgrading from: v6.0.0, v6.1.0, v6.1.1, v6.2.0, v6.3.0
   - Upgrading to: v7.0.0
   - PHP requirements from `composer.json`
   - Database version requirements

8. **Write Additional Resources section**
   - Link to `changelog.md`
   - Link to `docs/changelog-history/v6-changelog.md`
   - Framework documentation links
   - Contact information for support

9. **Write Support section**
   - Migration assistance process
   - Issue reporting
   - Where to get help

### Template Structure

```markdown
# Upgrade Guide: v7.0.0

> **Migration Complexity**: Breaking-XXL  
> **Estimated Time**: 2-6 hours depending on application size  
> **Last Updated**: 2026-02-09

## Overview

[2-3 paragraphs about v7.0.0 scope and changes]

**Major Changes**:
- Class reorganization with namespacing
- Admin screen system overhaul
- Offline events refactoring
- MCP/AI integration support
- Type safety improvements

See [changelog.md](../../changelog.md) for complete details.

## Prerequisites

### System Requirements
- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.2+
- Composer 2.0+

### Before You Begin
1. **Create full backup** of application and database
2. **Set up testing environment** - do NOT upgrade production first
3. **Review this guide completely** before starting
4. **Allocate time**:
   - Small applications (< 10k LOC): 2-3 hours
   - Medium applications (10-50k LOC): 3-5 hours
   - Large applications (> 50k LOC): 5-6+ hours

## Database Updates

### Required SQL Scripts

Execute the following SQL script on your database:

**File**: `docs/sql/2025-12-19-app-sets.sql`  
**Purpose**: Creates AppSets feature database storage

**Command-line import**:
```bash
mysql -u username -p database_name < docs/sql/2025-12-19-app-sets.sql
```

**Verification**:
```sql
SHOW TABLES LIKE '%appsets%';
```

**Note**: This is the ONLY database change required for v7.0.0.

## Breaking Changes

[Placeholder - filled in WP3]

## Step-by-Step Migration Guide

[Placeholder - filled in WP4]

## Testing Checklist

[Placeholder - filled in WP4]

## Common Issues and Solutions

[Placeholder - filled in WP4]

## Deprecation Timeline

| Version | Status | Timeline |
|---------|--------|----------|
| v7.0.0 | Deprecated classes available with `@deprecated` warnings | Current |
| v7.1.0 | Deprecated classes still available | Q2 2026 |
| v8.0.0 | **Deprecated classes REMOVED** | Q3 2026 (estimated) |

**⚠️ Recommendation**: Migrate immediately to avoid breaking changes in v8.0.0 (6-12 months).

## Version Compatibility

**Upgrading From**: v6.0.0, v6.1.0, v6.1.1, v6.2.0, v6.3.0  
**Upgrading To**: v7.0.0  
**PHP Requirements**: PHP 8.0+ (PHP 8.1+ recommended)  
**Database**: MySQL 5.7+, MariaDB 10.2+

## Additional Resources

- **Detailed Changes**: [changelog.md](../../changelog.md)
- **v6 History**: [v6-changelog.md](../changelog-history/v6-changelog.md)
- **Framework Documentation**: [docs/](../README.md)
- **Agent Documentation**: [agents/](../agents/readme.md)

## Support

For migration assistance:
1. Review this guide thoroughly
2. Run automated scanner (see Work Package 5)
3. Check changelog for additional details
4. Contact framework maintainer

---

**Document Version**: 1.0  
**Created**: 2026-02-09  
**Applies To**: Application Framework v7.0.0
```

### Acceptance Criteria

- [ ] Document created at correct path
- [ ] Overview section complete and accurate
- [ ] Prerequisites clearly stated with version numbers
- [ ] Database update instructions complete and tested
- [ ] All major section placeholders created
- [ ] Deprecation timeline included
- [ ] Version compatibility table complete
- [ ] Follows format and tone of existing upgrade guides
- [ ] Markdown formatting valid

### Output Files

- `docs/upgrade-guides/upgrade-guide-v7.0.0.md` (partial - structure only)

---

## Work Package 3: Document Breaking Changes in Detail

**Objective**: Complete the "Breaking Changes" section of the upgrade guide with comprehensive details, examples, and migration instructions for each category.

**Status**: 🔲 Not Started  
**Estimated Effort**: 3-4 hours  
**Dependencies**: WP1 (class mappings), WP2 (document structure)  
**Priority**: HIGH

### Context

This is the core content of the upgrade guide. Documents each breaking change category with examples and migration paths. Uses class mapping data from WP1.

### Input Files

- Class mapping data: `docs/upgrade-guides/v7.0.0-class-mappings.md` (from WP1)
- Document structure: `docs/upgrade-guides/upgrade-guide-v7.0.0.md` (from WP2)
- Changelog reference: `changelog.md` (for detailed change descriptions)

### Tasks

#### Task 3.1: Class Locations and Namespaces Section

1. **Write overview**
   - Explain the reorganization pattern
   - Why namespaces were added
   - Impact on existing code

2. **Insert class mapping reference table**
   - Use top 30-40 most important classes from WP1 data
   - Format as markdown table
   - Group by category (Core, Media, Admin, Events, etc.)
   - Include status column (Deprecated, Relocated, etc.)

3. **Write migration steps**
   - How to update use statements
   - How to update class references
   - How to update type hints
   - IDE find/replace patterns

4. **Create code examples**
   - Minimum 5 before/after examples
   - Cover: Exceptions, Media library, Filter settings, UI classes
   - Show both old and new patterns
   - Include namespace import examples

**Example Structure**:
```markdown
### 1. Class Locations and Namespaces

#### Overview

The v7.0.0 release reorganizes classes from the root folder into thematically organized subfolders with proper PHP namespacing. This improves code organization, enables autoloading, and follows modern PHP standards.

**Pattern**: Old underscore-based pseudo-namespaces → Proper PHP namespaces

**Backward Compatibility**: All old class names remain available as deprecated wrappers but will be removed in v8.0.0.

#### Class Mapping Reference

##### Core Classes
| Old Class | New Class | Full Namespace | Status |
|-----------|-----------|----------------|--------|
| Application_Exception | ApplicationException | Application\Exception\ApplicationException | Deprecated |
| ... | ... | ... | ... |

##### Media Library
[Table of media classes]

##### Admin Screens
[Table of screen classes]

#### Migration Steps

1. **Search for deprecated class usage**
   - Use automated scanner (see Scanner Tool section)
   - Or manually search: `grep -r "Application_Exception" assets/classes/`

2. **Update namespace imports**
   ```php
   // Add at top of file
   use Application\Exception\ApplicationException;
   ```

3. **Update class references**
   - Replace old names with new names
   - Remove underscores from class names

4. **Update type hints**
   ```php
   // OLD
   public function handle(Application_Exception $e) {}
   
   // NEW
   public function handle(ApplicationException $e) {}
   ```

#### Code Examples

**Example 1: Exception Handling**
```php
// OLD
try {
    // code
} catch (Application_Exception $e) {
    throw new Application_Exception('Error: ' . $e->getMessage());
}

// NEW
use Application\Exception\ApplicationException;

try {
    // code
} catch (ApplicationException $e) {
    throw new ApplicationException('Error: ' . $e->getMessage());
}
```

[4+ more examples]
```

#### Task 3.2: Admin Screen System Migration Section

1. **Write overview**
   - Explain architectural change
   - Old: Fixed `Area` folder structure
   - New: Dynamic loading with event registration
   - Benefits of new approach

2. **Write migration steps**
   - Numbered steps for migrating screens
   - How to implement `getAdminScreensFolder()`
   - How to create event listener
   - How to register listener

3. **Create complete example**
   - Show old structure
   - Show new structure
   - Module class with `getAdminScreensFolder()`
   - Event listener implementation
   - Registration code

4. **List affected screens**
   - Which built-in screens moved
   - What application developers need to check

**Example Structure**:
```markdown
### 2. Admin Screen System Migration

#### Overview

Admin screens are now **dynamically loaded** instead of being tied to the fixed `Area` folder structure. Screens can now be placed alongside their modules for better organization.

**Old Approach**:
- Screens in fixed `/Area/` folder structure
- Hardcoded screen locations
- Manual sitemap maintenance

**New Approach**:
- Screens can be anywhere in codebase
- Dynamic loading by class name
- Register locations via `RegisterAdminScreenFolders` event
- Automatic sitemap generation

**Benefits**: Better code organization, screens next to their modules, easier maintenance.

#### Affected Screens

The following built-in screens were relocated:
- Users management → `Application\Admin\Screens\Users\`
- Media library → `Application\Media\Admin\Screens\`
- News central → `Application\Admin\Screens\News\`
- Developer screens → `Application\Admin\Screens\Developer\`

**Action Required**: If you have custom admin screens, follow migration steps below.

#### Migration Steps

[Detailed numbered steps]

#### Complete Example

[Full before/after code example]
```

#### Task 3.3: Offline Events System Section

1. **Write overview**
2. **Document required changes**
   - Extend `BaseOfflineEvent`
   - Implement `getEventName()`
   - Remove `wakeUp()` method
3. **Create before/after examples**
4. **List moved events**

#### Task 3.4: Media Library Changes Section

1. **Document class renames**
2. **Show migration examples**
3. **Note admin screen changes**

#### Task 3.5: Deprecated Screen Base Classes Section

1. **Create mapping table**
2. **Show migration examples**

### Acceptance Criteria

- [ ] All 5 breaking change categories documented
- [ ] Each category has clear overview
- [ ] Migration steps are detailed and actionable
- [ ] Minimum 10 total code examples (before/after)
- [ ] Class mapping reference table complete (30-40 entries)
- [ ] Examples are tested and accurate
- [ ] Language is clear and non-technical where possible
- [ ] Cross-references to other sections where appropriate

### Output Files

- `docs/upgrade-guides/upgrade-guide-v7.0.0.md` (updated - breaking changes section complete)

---

## Work Package 4: Write Step-by-Step Migration Guide & Testing

**Objective**: Complete the practical migration guide with phased approach, testing checklist, and common issues section.

**Status**: 🔲 Not Started  
**Estimated Effort**: 2-3 hours  
**Dependencies**: WP3 (breaking changes documented)  
**Priority**: HIGH

### Context

Provides actionable step-by-step process for upgrading, organized by phase with time estimates. Includes comprehensive testing checklist and troubleshooting guide.

### Tasks

#### Task 4.1: Step-by-Step Migration Guide

Create 7 phases with detailed tasks:

1. **Phase 1: Preparation**
   - Backup procedures
   - Scanner script download/setup
   - Report review
   - Scope estimation

2. **Phase 2: Database Updates**
   - SQL script execution
   - Verification
   - Rollback plan

3. **Phase 3: Class Reference Updates**
   - Priority-based approach (high-frequency classes first)
   - Find/replace patterns
   - Namespace imports
   - Testing after each batch

4. **Phase 4: Admin Screen Migration**
   - Screen identification
   - Optional relocation
   - Event listener creation
   - Registration
   - Cache clearing

5. **Phase 5: Event Listener Updates**
   - Custom event updates
   - Listener updates
   - `wakeUp()` removal
   - `getEventName()` implementation

6. **Phase 6: Testing**
   - Cache clearing
   - Functionality testing
   - Test suite execution
   - Smoke testing

7. **Phase 7: Cleanup**
   - Code cleanup
   - Documentation updates
   - Commit changes

**Format Each Phase**:
```markdown
### Phase X: Phase Name (Time Estimate)

**Goal**: [What this phase accomplishes]

**Steps**:
1. [Detailed step with commands/examples]
2. [Detailed step]
   ```bash
   # Example commands
   ```
3. [Detailed step]

**Verification**:
- [ ] Checkpoint 1
- [ ] Checkpoint 2

**Common Issues**: See [Common Issues](#common-issues-and-solutions)
```

#### Task 4.2: Testing Checklist

Create comprehensive checklist organized by:

1. **Critical Functionality**
   - Application boots
   - Database connection
   - Authentication
   - Admin access

2. **Admin Screens**
   - Default screens load
   - Custom screens accessible
   - Navigation complete
   - Forms work
   - Modes/tabs work

3. **Events System**
   - Session events fire
   - Media events fire
   - Custom events fire
   - Listeners execute

4. **Media Library**
   - Collection loads
   - Upload works
   - Editing works
   - Deletion works

5. **Application-Specific**
   - Template for custom tests

**Format**:
```markdown
## Testing Checklist

### Critical Functionality
- [ ] Application boots without errors
- [ ] Database connection successful
- [ ] User authentication works
- [ ] Admin area accessible
- [ ] No PHP warnings/notices in logs

### Admin Screens
- [ ] Dashboard loads
- [ ] Users screen accessible
- [ ] [etc.]

[Continue for all categories]

### Application-Specific Tests
Add your application-specific tests:
- [ ] _________________
- [ ] _________________
```

#### Task 4.3: Common Issues and Solutions

Document 8-10 common issues with solutions:

1. **"Class not found" errors**
   - Cause
   - Solution
   - Example

2. **Admin screen not appearing**
   - Cause
   - Solution
   - Example

3. **Events not firing**
4. **Interface not found errors**
5. **Cache-related errors**
6. **Namespace import errors**
7. **Type hint errors**
8. **Deprecated warning floods**

**Format**:
```markdown
## Common Issues and Solutions

### Issue: "Class 'Application_Exception' not found"

**Cause**: Deprecated class reference not updated and old class file missing.

**Solution**: 
1. Use upgrade scanner to find all usages
2. Update namespace imports
3. Update class references

**Example**:
```php
// Update from:
throw new Application_Exception('Error');

// To:
use Application\Exception\ApplicationException;
throw new ApplicationException('Error');
```

**See Also**: [Class Locations and Namespaces](#class-locations-and-namespaces)

[Continue for all issues]
```

### Acceptance Criteria

- [ ] 7 migration phases documented
- [ ] Each phase has clear goals, steps, verification
- [ ] Time estimates provided for each phase
- [ ] Testing checklist covers all critical areas
- [ ] Minimum 8 common issues documented
- [ ] Each issue has cause, solution, example
- [ ] Cross-references between sections
- [ ] Practical and actionable instructions

### Output Files

- `docs/upgrade-guides/upgrade-guide-v7.0.0.md` (updated - guide sections complete)

---

## Work Package 5: Create Automated Scanner Script

**Objective**: Build a CLI tool that scans application code for deprecated class usage and generates actionable migration reports.

**Status**: 🔲 Not Started  
**Estimated Effort**: 4-6 hours  
**Dependencies**: WP1 (class mappings - provides data source)  
**Priority**: MEDIUM (valuable but guide can exist without it)

### Context

Create PHP CLI script that helps developers identify deprecated class usage in their applications. Makes migration significantly easier, especially for large codebases.

### Script Location

`/Users/smordziol/Webserver/libraries/application-framework/tools/upgrade-to-v7.php`

### Features

1. **Recursive file scanning**: Scan all PHP files in target directory
2. **Class usage detection**: Find deprecated classes in various contexts (new, extends, implements, type hints, use statements)
3. **Admin screen detection**: Identify screens needing migration
4. **Event listener detection**: Identify event listeners needing updates
5. **Report generation**: Console, HTML, and JSON output formats
6. **Priority ranking**: Order findings by frequency/importance
7. **Progress indicator**: Show progress for large scans

### Script Structure

```php
#!/usr/bin/env php
<?php
/**
 * Application Framework v7.0.0 Upgrade Scanner
 * 
 * Scans application code for deprecated class usage and generates
 * migration reports.
 * 
 * Usage:
 *   php upgrade-to-v7.php /path/to/application
 *   php upgrade-to-v7.php /path/to/app --format=html --output=report.html
 *   php upgrade-to-v7.php /path/to/app --format=json --output=report.json
 */

// 1. Argument parsing
// 2. Class definitions
// 3. Main execution
// 4. Output formatting

class UpgradeScanner
{
    private array $classMap = [];
    private array $findings = [];
    private string $scanPath;
    private int $filesScanned = 0;
    
    // Methods:
    // - __construct()
    // - loadClassMap()
    // - scan()
    // - scanFile()
    // - detectClassUsage()
    // - detectAdminScreens()
    // - detectEventListeners()
    // - generateReport()
    // - formatConsole()
    // - formatHTML()
    // - formatJSON()
    // - prioritize()
}
```

### Tasks

#### Task 5.1: Core Scanner Implementation

1. **Create file with shebang and docblock**
2. **Implement argument parsing**
   - Path (required)
   - --format=console|html|json (optional, default: console)
   - --output=filename (optional)
   - --help flag

3. **Load class mapping data**
   - Read JSON file from WP1
   - Parse into lookup array
   - Include metadata (namespace, type, category, priority)

4. **Implement recursive file scanner**
   - Use `RecursiveDirectoryIterator`
   - Filter for `.php` files
   - Skip common ignore patterns (vendor/, node_modules/, cache/, etc.)
   - Show progress (files scanned / total)

5. **Implement class usage detector**
   - Regex patterns for:
     - `new ClassName(`
     - `extends ClassName`
     - `implements ClassName`
     - `use Full\ClassName;`
     - Type hints: `function foo(ClassName $var)`
     - Static calls: `ClassName::method()`
   - Capture file path and line number
   - Store in findings array

6. **Implement admin screen detector**
   - Look for patterns:
     - `extends Application_Admin_Area`
     - Files in `/Area/` folders
     - Classes extending deprecated screen base classes
   - Flag for migration

7. **Implement event listener detector**
   - Look for patterns:
     - `public function wakeUp(` (old pattern)
     - Files in `OfflineEvents/` folders
     - Classes without `getEventName()` method
   - Flag for migration

#### Task 5.2: Report Generation

1. **Console formatter**
   - ASCII table formatting
   - Color coding (if terminal supports)
   - Priority sections (High/Medium/Low)
   - File grouping
   - Summary statistics

2. **HTML formatter**
   - Bootstrap-based styling
   - Collapsible sections
   - Syntax highlighting
   - Searchable/filterable table
   - Export functionality

3. **JSON formatter**
   - Structured data output
   - Machine-readable
   - For CI/CD integration

4. **Priority ranking**
   - HIGH: 10+ usages or critical classes
   - MEDIUM: 3-9 usages
   - LOW: 1-2 usages
   - Sort by frequency within priority

#### Task 5.3: Testing and Refinement

1. **Create test fixtures**
   - Small PHP files with various deprecated usage patterns
   - Test all detection patterns
   - Verify accurate line numbers

2. **Test on real application**
   - Run on sample application using framework
   - Verify findings are accurate
   - Check for false positives
   - Refine patterns as needed

3. **Performance optimization**
   - Test on large codebases
   - Optimize file reading
   - Add caching if needed

4. **Error handling**
   - Handle invalid paths
   - Handle permission errors
   - Handle malformed PHP files
   - Graceful degradation

### Example Output

**Console Format**:
```
=================================================================
 Application Framework v7.0.0 Upgrade Scanner
=================================================================

Scanning: /path/to/application
Files scanned: 145/145 [========================================] 100%

Found 47 deprecated class usages in 23 files

PRIORITY 1: High-frequency deprecated classes (10+ usages)
-----------------------------------------------------------
Application_Exception (15 usages)
  /assets/classes/MyApp/Module.php:45
  /assets/classes/MyApp/Module.php:67
  /assets/classes/MyApp/Helper.php:23
  ...
→ Replacement: Use Application\Exception\ApplicationException
→ See: docs/upgrade-guides/upgrade-guide-v7.0.0.md#class-locations

Application_Media (12 usages)
  /assets/classes/MyApp/MediaHandler.php:12, 34, 56, 78
  ...
→ Replacement: Use Application\Media\Collection\MediaCollection

PRIORITY 2: Medium-frequency (3-9 usages)
------------------------------------------
[...]

PRIORITY 3: Low-frequency (1-2 usages)
---------------------------------------
[...]

Admin Screens Requiring Migration
----------------------------------
  /Area/MyModule/CustomScreen.php
    → Extends deprecated Application_Admin_Area_Mode_CollectionCreateScreen
    → Action: Update to DBHelper\Admin\Screens\Mode\BaseRecordCreateMode
    → See: docs/upgrade-guides/upgrade-guide-v7.0.0.md#admin-screens

Offline Events Requiring Migration
-----------------------------------
  /assets/classes/MyApp/Events/MyListener.php:45
    → Has wakeUp() method (deprecated pattern)
    → Action: Remove wakeUp(), implement getEventName()
    → See: docs/upgrade-guides/upgrade-guide-v7.0.0.md#offline-events

=================================================================
Summary
=================================================================
Deprecated classes found:    15 distinct classes
Total usages:                47 locations
Files affected:              23 files
Admin screens to migrate:     1 screen
Event listeners to update:    1 listener

Estimated migration effort:   3-4 hours

Next Steps:
1. Review this report carefully
2. Read: docs/upgrade-guides/upgrade-guide-v7.0.0.md
3. Start with HIGH priority items
4. Test after each batch of changes
=================================================================
```

### Acceptance Criteria

- [ ] Script executable from command line
- [ ] Scans directory recursively for PHP files
- [ ] Detects all deprecated class usage patterns (new, extends, implements, type hints, use)
- [ ] Detects admin screens needing migration
- [ ] Detects event listeners needing updates
- [ ] Generates console report with priorities
- [ ] Generates HTML report (optional)
- [ ] Generates JSON export (optional)
- [ ] Shows progress indicator for large scans
- [ ] Handles errors gracefully
- [ ] Performance acceptable (< 1 second per 100 files)
- [ ] Tested on real application
- [ ] Documented with --help flag
- [ ] No false positives in test cases

### Output Files

- `tools/upgrade-to-v7.php` (executable PHP script)
- `tools/upgrade-to-v7-README.md` (usage documentation)

### Testing Data

Create test directory: `tests/upgrade-scanner/fixtures/`

Sample files to test all patterns:
```php
// fixture-exceptions.php
class TestClass {
    public function test() {
        throw new Application_Exception('Test'); // Should detect
        try {
        } catch (Application_Exception $e) { // Should detect
        }
    }
}

// fixture-extends.php
class TestScreen extends Application_Admin_Area_Mode_CollectionCreateScreen { // Should detect
}

// fixture-typehints.php
class TestClass {
    public function handle(Application_Exception $e) { // Should detect
    }
}

// fixture-use.php
use Application_Exception; // Should detect

// fixture-events.php
class TestListener {
    public function wakeUp() { // Should detect (needs migration)
    }
}
```

---

## Work Package 6: Integration and Final Testing

**Objective**: Integrate all components, test on real application, refine based on findings.

**Status**: 🔲 Not Started  
**Estimated Effort**: 2-3 hours  
**Dependencies**: WP2, WP3, WP4, WP5 (all components complete)  
**Priority**: HIGH

### Context

Validate that the complete upgrade guide and scanner tool work together effectively on a real application upgrade.

### Tasks

#### Task 6.1: Test Application Setup

1. **Create or select test application**
   - Use existing application on framework v6.3.0
   - Or create minimal test application with:
     - Custom admin screens
     - Custom event listeners
     - Media library usage
     - Exception handling
     - FilterSettings implementation

2. **Establish baseline**
   - Document current functionality
   - Create automated tests if possible
   - Take full backup
   - Note all custom features

#### Task 6.2: Execute Upgrade Following Guide

1. **Run scanner script**
   - Execute on test application
   - Review report
   - Document findings
   - Note any unexpected results

2. **Follow upgrade guide step-by-step**
   - Execute each phase precisely as documented
   - Note any unclear instructions
   - Track actual time vs estimated time
   - Document any issues encountered

3. **Record all changes**
   - Keep log of modifications
   - Note helpful patterns
   - Identify pain points

#### Task 6.3: Validate Results

1. **Functional testing**
   - All features work as before upgrade
   - No new errors
   - No warnings in logs
   - Performance unchanged

2. **Code quality**
   - No deprecated class warnings
   - All namespaces correct
   - Admin screens accessible
   - Events firing correctly

3. **Scanner validation**
   - Re-run scanner on upgraded application
   - Should find zero deprecated usages
   - Verify accuracy

#### Task 6.4: Refine Documentation

1. **Update upgrade guide based on findings**
   - Clarify ambiguous instructions
   - Add missing steps
   - Improve examples
   - Adjust time estimates
   - Add discovered gotchas to Common Issues

2. **Update scanner script**
   - Fix any false positives
   - Add missing detection patterns
   - Improve error messages
   - Refine output formatting

3. **Update class mappings**
   - Add any missing classes discovered
   - Correct any errors
   - Improve categorization

### Acceptance Criteria

- [ ] Test application successfully upgraded from v6.3.0 to v7.0.0
- [ ] All functionality works post-upgrade
- [ ] Scanner tool identified all actual deprecated usages
- [ ] Zero false positives in scanner results
- [ ] Upgrade guide instructions are accurate and complete
- [ ] Time estimates are realistic
- [ ] Common issues section covers actual encountered problems
- [ ] Documentation refined based on real-world testing

### Output Files

- Updated `docs/upgrade-guides/upgrade-guide-v7.0.0.md`
- Updated `tools/upgrade-to-v7.php`
- Updated class mapping files
- Test application upgrade log (for reference)

---

## Work Package 7: Review, Polish, and Finalize

**Objective**: Final review, proofreading, consistency check, and formal release preparation.

**Status**: 🔲 Not Started  
**Estimated Effort**: 1-2 hours  
**Dependencies**: WP6 (testing complete)  
**Priority**: MEDIUM

### Context

Ensure documentation is production-ready with consistent formatting, accurate cross-references, and professional quality.

### Tasks

#### Task 7.1: Content Review

1. **Accuracy check**
   - Verify all version numbers (v6.x → v7.0.0)
   - Verify all file paths
   - Verify all commands work
   - Verify all code examples syntax
   - Test all cross-reference links

2. **Completeness check**
   - All breaking changes covered
   - All migration paths documented
   - All common issues addressed
   - All acceptance criteria from WP1-6 met

3. **Consistency check**
   - Terminology consistent throughout
   - Formatting consistent
   - Tone consistent
   - Example structure consistent

#### Task 7.2: Formatting and Polish

1. **Markdown validation**
   - Valid markdown syntax
   - Proper heading hierarchy
   - Code blocks properly formatted
   - Lists properly structured
   - Tables properly formatted

2. **Visual formatting**
   - Consistent use of bold, italic, code
   - Proper use of callouts/notes
   - Adequate spacing between sections
   - Syntax highlighting correct

3. **Grammar and clarity**
   - Proofread all text
   - Remove jargon where possible
   - Simplify complex sentences
   - Fix typos and grammar errors

#### Task 7.3: Cross-References and Links

1. **Internal links**
   - Verify all anchor links work
   - Add missing cross-references
   - Consistent link formatting

2. **External references**
   - Verify file paths exist
   - Verify referenced documentation exists
   - Update if files moved

3. **Navigation aids**
   - Table of contents (if needed)
   - "See also" references
   - Back-to-top links (if long document)

#### Task 7.4: Final Checklist

- [ ] All work packages 1-6 complete
- [ ] Document tested on real upgrade
- [ ] Scanner script tested and functional
- [ ] All code examples are valid PHP
- [ ] All file paths are accurate
- [ ] All cross-references work
- [ ] Markdown validates
- [ ] Grammar and spelling checked
- [ ] Formatting consistent
- [ ] No TODOs or placeholders remaining
- [ ] Version and date correct
- [ ] Contact/support information accurate

#### Task 7.5: Create Summary Document

Create `docs/upgrade-guides/README.md` or update existing:
- List all available upgrade guides
- Add v7.0.0 guide to list
- Link to scanner tool
- Note complexity/estimated time

### Acceptance Criteria

- [ ] All checklist items complete
- [ ] Document is professional quality
- [ ] No errors or inconsistencies
- [ ] Ready for public release
- [ ] Summary/index updated

### Output Files

- Final `docs/upgrade-guides/upgrade-guide-v7.0.0.md`
- Final `tools/upgrade-to-v7.php`
- Final class mapping files
- Updated `docs/upgrade-guides/README.md`

---

## Implementation Strategy

### Recommended Order

1. **Start with WP1 and WP2 in parallel** (both are foundational, no dependencies)
2. **Complete WP3** (needs class mappings from WP1, document structure from WP2)
3. **Complete WP4** (needs breaking changes from WP3)
4. **Complete WP5** (needs class mappings from WP1; can be parallel with WP3/WP4)
5. **Complete WP6** (needs all components)
6. **Complete WP7** (final polish)

### Incremental Delivery

Each work package produces usable output:
- **WP1**: Class mapping reference (useful immediately)
- **WP2**: Document structure (shows scope and outline)
- **WP3**: Breaking changes details (primary content)
- **WP4**: Migration guide (practical instructions)
- **WP5**: Scanner tool (high-value utility)
- **WP6**: Validated documentation
- **WP7**: Publication-ready materials

### Time Distribution

| Work Package | Hours | Percentage |
|--------------|-------|------------|
| WP1: Class Mappings | 2-3 | 15% |
| WP2: Structure | 1-2 | 10% |
| WP3: Breaking Changes | 3-4 | 25% |
| WP4: Migration Guide | 2-3 | 18% |
| WP5: Scanner Script | 4-6 | 32% |
| WP6: Testing | 2-3 | 18% |
| WP7: Polish | 1-2 | 10% |
| **Total** | **15-23** | **100%** |

### Parallelization Opportunities

- **WP1 + WP2**: Can run completely in parallel
- **WP5**: Can start after WP1, parallel to WP3/WP4
- **WP3 + WP4**: Sequential but can start WP4 sections that don't depend on WP3

### Session Planning

**Session 1** (3-4 hours):
- Complete WP1 (class mappings)
- Complete WP2 (document structure)
- Start WP3 (first 2 breaking change sections)

**Session 2** (3-4 hours):
- Complete WP3 (remaining breaking changes)
- Complete WP4 (migration guide)

**Session 3** (4-6 hours):
- Complete WP5 (scanner script)

**Session 4** (2-3 hours):
- Complete WP6 (testing)
- Complete WP7 (polish)

---

## Success Metrics

- [ ] Upgrade guide covers 100% of v7.0.0 breaking changes
- [ ] Class mapping includes 50+ classes
- [ ] Scanner detects 95%+ of deprecated usages (validated by testing)
- [ ] Scanner has < 5% false positive rate
- [ ] Upgrade guide tested on real application successfully
- [ ] Average upgrade time for medium application: 3-5 hours (validated)
- [ ] Zero blockers encountered in test upgrade
- [ ] Documentation clarity rated 4+/5 by test users

---

## Notes for Future Implementers

### Key Files Quick Reference

**Framework Root**: `/Users/smordziol/Webserver/libraries/application-framework/`

- Changelog: `changelog.md`
- Deprecated classes: `src/classes/_deprecated/`
- Upgrade guides: `docs/upgrade-guides/`
- Tools: `tools/`
- Class mapping output: `docs/upgrade-guides/v7.0.0-class-mappings.*`
- Guide output: `docs/upgrade-guides/upgrade-guide-v7.0.0.md`
- Scanner output: `tools/upgrade-to-v7.php`

### Common Patterns in Deprecated Files

```php
/**
 * @deprecated Use {@see \Full\Namespace\NewClass} instead.
 */
class Old_Class_Name extends \Full\Namespace\NewClass
{
    // Usually empty - just extends new class
}
```

### Git Commands for Research

```bash
cd /Users/smordziol/Webserver/libraries/application-framework

# Find moved classes
git log --all --oneline --grep="Moved" --since="2025-01-01"

# Find renames
git diff HEAD~100..HEAD --name-status | grep "^R"

# Find all deprecated tags
grep -r "@deprecated" src/classes/_deprecated/
```

### Changelog Parsing Tips

v7.0.0 section starts around line 1 in `changelog.md`. Look for commit summaries like:
- "Moved X to Y"
- "Renamed X to Y"
- "Relocated X"

### Scanner Detection Patterns

Common PHP patterns to detect:
```regex
new\s+([A-Z][a-zA-Z_]*)
extends\s+([A-Z][a-zA-Z_]*)
implements\s+([A-Z][a-zA-Z_,\s]*)
use\s+([A-Z][a-zA-Z_\\]*);
function\s+\w+\([^)]*([A-Z][a-zA-Z_]*)\s+\$
```

---

## Document Status Tracking

| Work Package | Status | Assignee | Started | Completed | Notes |
|--------------|--------|----------|---------|-----------|-------|
| WP1: Class Mappings | 🔲 Not Started | - | - | - | - |
| WP2: Structure | 🔲 Not Started | - | - | - | - |
| WP3: Breaking Changes | 🔲 Not Started | - | - | - | - |
| WP4: Migration Guide | 🔲 Not Started | - | - | - | - |
| WP5: Scanner Script | 🔲 Not Started | - | - | - | - |
| WP6: Testing | 🔲 Not Started | - | - | - | - |
| WP7: Polish | 🔲 Not Started | - | - | - | - |

**Legend**: 🔲 Not Started | 🔄 In Progress | ✅ Complete | ⚠️ Blocked

---

**Last Updated**: 2026-02-09  
**Document Version**: 1.0  
**Estimated Total Effort**: 15-23 hours
