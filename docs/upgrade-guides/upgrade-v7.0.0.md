# Upgrade Guide: v7.0.0

> **Migration Complexity**: Breaking-XXL  
> **Estimated Time**: 2-6 hours depending on application size  
> **Last Updated**: 2026-02-09

## Overview

Application Framework v7.0.0 represents a major architectural evolution designed to support modern agentic coding practices and improve code organization. This release introduces systematic namespace adoption, reorganizes core classes into thematic folders, and completely overhauls the admin screen loading system.

**This is a breaking release** that requires manual migration in most applications. The framework now uses dynamic screen loading, allowing admin screens to be placed alongside their modules for better organization and AI-assisted development.

### Major Changes

- **Class reorganization with namespacing**: 50+ core classes moved into thematically organized folders with proper PHP namespaces
- **Admin screen system overhaul**: Screens now dynamically loaded via `RegisterAdminScreenFolders` event instead of hardcoded registry
- **Offline events refactoring**: Event/listener auto-discovery with standardized base classes
- **MCP/AI integration support**: Context-as-Code generation, MCP server functionality, and agent-friendly documentation
- **Type safety improvements**: Extensive type hints, PHPDoc annotations, and Rector integration
- **Database schema change**: New AppSets feature with database storage (one SQL script required)

While this may seem like a large migration effort, the v7.0.0 release maintains **backward compatibility** for all deprecated classes through wrapper classes. This gives you time to migrate gradually, but be aware that these compatibility wrappers will be removed in v8.0.0 (estimated Q3 2026).

See [changelog.md](../../changelog.md) for complete technical details.

## Prerequisites

### System Requirements

- **PHP**: 8.4 or higher (8.4+ required as of v7.0.0)
- **MySQL**: 5.7+ or MariaDB 10.2+
- **Composer**: 2.0+
- **Git**: For tracking changes during migration

### Before You Begin

1. **Create full backup** of both application codebase and database
2. **Set up testing environment** - do NOT upgrade production directly
3. **Review this guide completely** before starting
4. **Review the class mappings**: See [v7.0.0-class-mappings.md](v7.0.0-class-mappings.md) for all deprecated class references
5. **Allocate sufficient time**:
   - Small applications (< 10k LOC): 2-3 hours
   - Medium applications (10-50k LOC): 3-5 hours  
   - Large applications (> 50k LOC): 5-6+ hours
6. **Ensure team communication** if working in a team environment

### Tools You'll Need

- Terminal/command-line access
- Text editor with search/replace capabilities (PHPStorm, VS Code recommended)
- Database management tool (phpMyAdmin, MySQL Workbench, or command-line)
- Optional: Automated upgrade scanner script (see Work Package 5)

## Database Updates

### Required SQL Scripts

Application Framework v7.0.0 requires **one database migration** to support the enhanced AppSets feature.

**File**: `docs/sql/2025-12-19-app-sets.sql`  
**Purpose**: Creates AppSets database tables for storing application configuration sets  
**Location**: `/vendor/mistralys/application_framework/docs/sql/2025-12-19-app-sets.sql`

#### Import via Command Line

```bash
# Navigate to framework directory
cd /path/to/vendor/mistralys/application_framework

# Import the SQL file
mysql -u your_username -p your_database_name < docs/sql/2025-12-19-app-sets.sql
```

#### Import via GUI (phpMyAdmin)

1. Open phpMyAdmin and select your database
2. Click the "Import" tab
3. Click "Choose File" and select `docs/sql/2025-12-19-app-sets.sql`
4. Click "Go" to execute

#### Verification

After importing, verify the tables were created:

```sql
SHOW TABLES LIKE '%appsets%';
```

You should see several new `appsets_*` tables.

**⚠️ Note**: This is the **ONLY** database change required for v7.0.0. No other schema modifications are necessary.

## Breaking Changes

v7.0.0 introduces major structural changes to the application framework. This section details each breaking change, why it was made, and how to migrate your code.

### 1. Class Locations and Namespaces (CRITICAL)

**Impact**: HIGH - Most applications will need changes

All core framework classes have been reorganized into thematically-related folders with proper PHP namespaces. This improves code organization, enables better IDE support, and prepares for AI-assisted development.

#### What Changed

- **50+ classes** moved from root `src/classes/` to organized subfolders
- **PHP namespaces** now used for all classes (no more backslash-prefixed class names)
- **Backward compatibility** maintained through deprecated wrapper classes in `src/classes/_deprecated/`

#### Examples of Relocated Classes

| Old Name (v6.x) | New Location (v7.0.0) | PHP Namespace |
|---|---|---|
| `Application_Exception` | `src/classes/Application/Exception/ApplicationException.php` | `Application\Exception` |
| `Application_Admin_Area` | `src/classes/Application/Admin/BaseArea.php` | `Application\Admin` |
| `Application_Admin_Area_Mode` | `src/classes/Application/Admin/Area/BaseMode.php` | `Application\Admin\Area` |
| `Application_FilterSettings` | `src/classes/Application/FilterSettings.php` | `Application` |

#### Migration Steps

1. **Review the class mappings**: See [v7.0.0-class-mappings.md](v7.0.0-class-mappings.md) for the complete reference
2. **Add `use` statements** to each file using the new classes:
   ```php
   // v6.x
   throw new Application_Exception('Error');
   
   // v7.0.0
   use Application\Exception\ApplicationException;
   throw new ApplicationException('Error');
   ```

3. **Update class inheritance**:
   ```php
   // v6.x
   class MyArea extends Application_Admin_Area { }
   
   // v7.0.0
   use Application\Admin\BaseArea;
   class MyArea extends BaseArea { }
   ```

4. **Update interface implementations**:
   ```php
   // v6.x
   class MyScreen implements Application_Interfaces_Admin_CollectionCreate { }
   
   // v7.0.0
   use DBHelper\Admin\Traits\RecordCreateScreenInterface;
   class MyScreen implements RecordCreateScreenInterface { }
   ```

#### Deprecated Wrapper Classes

All deprecated classes are available in `src/classes/_deprecated/` for backward compatibility:

- Extend their new counterparts
- Will be removed in v8.0.0
- Marked with `@deprecated` PHPDoc tags

#### IDE Integration

Modern IDEs now better support the namespaced structure:

- Auto-completion works correctly
- "Go to Definition" navigates to the correct file
- Refactoring is safer with proper namespaces
- Type hints work as expected

### 2. Admin Screen Loading System (CRITICAL)

**Impact**: HIGH - Significant changes to admin screen organization

The admin screen system has been completely redesigned to support dynamic loading via offline events. Admin screens can now be placed alongside their modules instead of in a central location.

#### What Changed

**Before (v6.x)**: Hardcoded screen registry
```php
// In central location (e.g., Application_Admin_ScreenRegistry)
public function registerScreens() {
    $this->registerScreen($screenClass);
    // ... hardcoded for all screens
}
```

**After (v7.0.0)**: Dynamic registration via event listeners
```php
// In the module's offline event listener
class RegisterMyModuleScreensListener extends BaseRegisterScreenFoldersListener {
    protected function getAdminScreenFolders(): array {
        return [MyModule::getAdminScreensFolder()];
    }
}
```

#### Migration Requirements

1. **Create offline event listeners** for your admin screens:
   ```php
   // src/classes/MyApp/OfflineEvents/RegisterAdminScreenFolders/RegisterMyScreensListener.php
   namespace MyApp\OfflineEvents\RegisterAdminScreenFolders;
   
   use Application\CacheControl\BaseRegisterScreenFoldersListener;
   use MyApp\MyFeature;
   
   class RegisterMyScreensListener extends BaseRegisterScreenFoldersListener {
       protected function getAdminScreenFolders(): array {
           return [MyFeature::getAdminScreensFolder()];
       }
   }
   ```

2. **Place screens in module folders** (recommended structure):
   ```
   src/classes/MyApp/MyFeature/
   ├── MyFeature.php
   ├── Collection/
   │   ├── MyFeatureCollection.php
   │   └── Admin/
   │       ├── ListScreen.php
   │       ├── CreateScreen.php
   │       └── EditScreen.php
   ```

3. **Update screen class names** - use new base class terminology:
   - `MyFeatureCollectionCreateScreen` → `BaseRecordCreateSubmode`
   - `MyFeatureCollectionEditScreen` → `BaseRecordSettingsSubmode`
   - `MyFeatureCollectionListScreen` → `BaseRecordListSubmode`

4. **Implement `getAdminScreensFolder()` method** in your collection class:
   ```php
   public static function getAdminScreensFolder(): FolderInfo {
       return AppFolder::getFolder('assets/classes/MyApp/MyFeature/Collection/Admin');
   }
   ```

#### Benefits of New System

- **Modular**: Screens live with their modules
- **Scalable**: Easy to add new screens without modifying core files
- **AI-friendly**: Auto-discovery supports agent-assisted development
- **Flexible**: Supports runtime folder registration

### 3. Offline Events System (HIGH IMPACT)

**Impact**: MEDIUM - Affects custom event listeners

The offline events system has been refactored to improve consistency and support auto-discovery of events and listeners.

#### What Changed

1. **Events must extend `BaseOfflineEvent`**:
   ```php
   // v7.0.0
   use Application\EventHandler\OfflineEvents\BaseOfflineEvent;
   
   class MyCustomEvent extends BaseOfflineEvent {
       public const string EVENT_NAME = 'MyCustomEvent';
   }
   ```

2. **Listeners now provide event name** instead of inheriting from specific listeners:
   ```php
   // v7.0.0
   use Application\EventHandler\OfflineEvents\BaseOfflineListener;
   
   class MyCustomListener extends BaseOfflineListener implements OfflineEventListenerInterface {
       public function getEventName(): string {
           return 'MyCustomEvent';
       }
       
       public function handleEvent(EventInterface $event): void {
           // Handle the event
       }
   }
   ```

3. **Folder structure for listeners** must match event name:
   ```
   src/classes/Application/OfflineEvents/MyCustomEvent/
   └── MyCustomEventListener.php
   ```

4. **The `wakeUp()` method is no longer required** - lifecycle simplified

#### Migration Steps for Custom Events

1. **Identify all custom event classes** in your application
2. **Make them extend `BaseOfflineEvent`**:
   ```php
   // Before
   class MyEvent extends Application_EventHandler_Event { }
   
   // After
   use Application\EventHandler\OfflineEvents\BaseOfflineEvent;
   class MyEvent extends BaseOfflineEvent { }
   ```

3. **Update all listener classes** to extend `BaseOfflineListener`:
   ```php
   // Before
   class MyEventListener extends Application_EventHandler_OfflineEvents_OfflineListener { }
   
   // After
   use Application\EventHandler\OfflineEvents\BaseOfflineListener;
   class MyEventListener extends BaseOfflineListener { }
   ```

4. **Add event name provider** method if not inheriting from a base listener:
   ```php
   public function getEventName(): string {
       return 'MyCustomEvent';
   }
   ```

5. **Test event firing** thoroughly in your environment

#### Event Name Convention

Event names should be **PascalCase** and match the class name:

- Event class: `MyCustomEvent`
- Constant: `public const string EVENT_NAME = 'MyCustomEvent'`
- Listener folder: `OfflineEvents/MyCustomEvent/`

### 4. Exception Handling (MEDIUM IMPACT)

**Impact**: MEDIUM - Affects exception throwing and catching

Exception classes have been reorganized into a dedicated namespace structure.

#### What Changed

1. **All exceptions moved to `Application\Exception` namespace**:
   - `Application_Exception` → `\Application\Exception\ApplicationException`
   - Custom exceptions should follow the pattern

2. **Exception base classes relocated**:
   - Standard exceptions now extend framework base exceptions
   - Proper exception hierarchy established

#### Migration Steps

1. **Update exception throws**:
   ```php
   // Before
   throw new Application_Exception('Error message');
   
   // After
   use Application\Exception\ApplicationException;
   throw new ApplicationException('Error message');
   ```

2. **Update exception catches**:
   ```php
   // Before
   catch(Application_Exception $e) { }
   
   // After
   use Application\Exception\ApplicationException;
   catch(ApplicationException $e) { }
   ```

3. **Create custom exceptions** in the proper location:
   ```php
   namespace MyApp\Exception;
   use Application\Exception\ApplicationException;
   
   class MyCustomException extends ApplicationException {
       // Implementation
   }
   ```

### 5. Base Classes and Inheritance (MEDIUM IMPACT)

**Impact**: MEDIUM - Affects custom admin screens

The naming and organization of base classes for admin screens has changed for consistency.

#### Collection → Record Terminology

All references to "Collection" in class names have been changed to "Record":

| Old Name | New Name | Purpose |
|----------|----------|---------|
| `ApplicationCollectionCreate` | `BaseRecordCreateSubmode` | Creating a new record |
| `ApplicationCollectionEdit` | `BaseRecordSettingsSubmode` | Editing record settings |
| `ApplicationCollectionDelete` | `BaseRecordDeleteSubmode` | Deleting a record |
| `ApplicationCollectionList` | `BaseRecordListSubmode` | Listing records |
| `ApplicationCollectionRecord` | `BaseRecordSubmode` | Viewing a single record |

#### Migration Example

```php
// Before (v6.x)
class UserCollectionCreateScreen extends Application_Admin_Area_Mode_Submode_CollectionCreate {
    // Implementation
}

// After (v7.0.0)
use Application\Admin\Area\Mode\BaseRecordCreateSubmode;

class UserCreateScreen extends BaseRecordCreateSubmode {
    // Implementation
}
```

### 6. Type Hints and Return Types (MEDIUM IMPACT)

**Impact**: LOW (mostly improvement) - May affect type-unsafe code

The framework now includes extensive type hints and return types throughout.

#### What This Means

- **IDE support improves** - auto-completion is better
- **Type errors caught** earlier
- **PHPStan compliance** required
- **Strict types** enabled in more files

#### Migration Recommendations

1. **Enable `declare(strict_types=1)`** in all your classes
2. **Add return type hints** to methods
3. **Add parameter type hints** to methods
4. **Run PHPStan** to identify type issues
5. **Update any type-unsafe calls** to methods

### Summary of Breaking Changes by Severity

| Severity | Category | Files Affected | Time to Fix |
|----------|----------|---|---|
| **CRITICAL** | Class namespaces | Most admin screen files | 2-4 hours |
| **CRITICAL** | Admin screen loading | All screen implementations | 1-3 hours |
| **HIGH** | Offline events | Custom event listeners | 30 mins - 1 hour |
| **MEDIUM** | Exception handling | Error handling code | 30 mins |
| **MEDIUM** | Base class names | Admin screen extends clauses | 30 mins |
| **LOW** | Type hints | General code quality | 1-2 hours |

### Migration Path Recommendation

1. **Phase 1**: Update class namespaces (extends/implements/throw/catch)
2. **Phase 2**: Create offline event listeners for screens
3. **Phase 3**: Update custom event implementations
4. **Phase 4**: Add type hints and return types
5. **Phase 5**: Test thoroughly in isolation
6. **Phase 6**: Integration testing with full application

## Step-by-Step Migration Guide

This guide walks you through upgrading your application from v6.x to v7.0.0 in manageable phases. Work through each phase carefully, testing as you go.

### Phase 1: Preparation (Estimated: 30 minutes)

#### Step 1.1: Create a Feature Branch

```bash
cd /path/to/your/application
git checkout -b feature/upgrade-to-v7.0.0
```

#### Step 1.2: Create Environment Backup

```bash
# Backup database
mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql

# Backup files (if using git, just ensure clean working directory)
git status  # Should show no uncommitted changes
```

#### Step 1.3: Review Breaking Changes

1. Read the [Breaking Changes](#breaking-changes) section above
2. Review [v7.0.0-class-mappings.md](v7.0.0-class-mappings.md) for all class changes
3. Identify which categories affect your application:
   - Admin screens (HIGH priority)
   - Exception handling (MEDIUM priority)
   - Custom event listeners (MEDIUM priority)
   - Type hints (LOW priority)

#### Step 1.4: Identify HIGH Priority Files

Search your codebase for deprecated classes to prioritize:

```bash
# Find all extends of deprecated classes
grep -r "extends Application_Admin_Area\|extends Application_Admin_Area_Mode\|extends Application_Admin_Area_Mode_Submode\|extends Application_Exception" assets/classes/ --include="*.php"

# Count results
grep -r "extends Application" assets/classes/ --include="*.php" | wc -l

# Find exception throws
grep -r "new Application_Exception" assets/classes/ --include="*.php" | wc -l
```

### Phase 2: Update Dependencies (Estimated: 15 minutes)

#### Step 2.1: Update Composer

```bash
# Update framework dependency
composer update mistralys/application_framework

# The Composer lock file will update with v7.0.0
```

#### Step 2.2: Verify PHP Version

```bash
php -v
# Should show PHP 8.4 or higher
```

If PHP version is lower than 8.4:
1. Contact your hosting provider to upgrade PHP
2. Update your local development environment
3. Ensure all CI/CD environments are updated

#### Step 2.3: Clear Application Cache

```bash
# Clear all cached files (if your app has cache directory)
rm -rf storage/cache/*
rm -rf logs/*

# Or use application-specific cache clearing:
# Via admin UI: Settings > Cache > Clear All
```

#### Step 2.4: Commit Changes

```bash
git add composer.json composer.lock
git commit -m "chore(v7): update framework to v7.0.0"
```

### Phase 3: Database Migration (Estimated: 15 minutes)

#### Step 3.1: Run SQL Migration Script

```bash
# Method 1: Command line
cd /path/to/application
mysql -u your_username -p your_database < vendor/mistralys/application_framework/docs/sql/2025-12-19-app-sets.sql

# Method 2: If using a wrapper script
# Run your app's database migration command if available
```

#### Step 3.2: Verify Migration

```sql
-- Connect to your database
-- Check for new tables
SHOW TABLES LIKE '%appsets%';

-- Should show several tables:
-- - appsets
-- - appsets_values
-- - etc.
```

#### Step 3.3: Commit Database Changes

```bash
# If you're tracking migrations in version control
git add docs/migrations/
git commit -m "chore(v7): apply database migration for AppSets feature"
```

### Phase 4: Code Migration - Admin Screens (Estimated: 2-4 hours)

This is the most time-intensive phase for most applications.

#### Step 4.1: Update Admin Screen Class Names

For each admin screen class, update class names and inheritance:

```php
// Before: assets/classes/MyApp/Admin/MyFeatureArea.php
class MyFeatureArea extends Application_Admin_Area {
    // ...
}

// After: Keep same file location, update class
use Application\Admin\BaseArea;

class MyFeatureArea extends BaseArea {
    // ...
}
```

**Files to update**: All files extending:
- `Application_Admin_Area` → `Application\Admin\BaseArea`
- `Application_Admin_Area_Mode` → `Application\Admin\Area\BaseMode`
- `Application_Admin_Area_Mode_Submode` → `Application\Admin\Area\Mode\BaseSubmode`
- `Application_Admin_Area_Mode_Submode_Action` → `Application\Admin\Area\Mode\Submode\BaseAction`

#### Step 4.2: Create Offline Event Listeners

Create listeners to register admin screen folders:

```php
// New file: src/classes/MyApp/OfflineEvents/RegisterAdminScreenFolders/RegisterMyFeatureScreensListener.php
<?php
declare(strict_types=1);

namespace MyApp\OfflineEvents\RegisterAdminScreenFolders;

use Application\CacheControl\BaseRegisterScreenFoldersListener;
use AppUtils\FileHelper\FolderInfo;
use MyApp\MyFeature;

class RegisterMyFeatureScreensListener extends BaseRegisterScreenFoldersListener {
    protected function getAdminScreenFolders(): array {
        return [MyFeature::getAdminScreensFolder()];
    }
}
```

**For each module with admin screens**:
1. Create listener file in correct location
2. Return `FolderInfo` objects pointing to screen folders
3. Test that listener is auto-discovered

#### Step 4.3: Update Collection → Record Naming

Rename admin screen base classes:

```php
// Before
class UserCreateScreen extends Application_Admin_Area_Mode_Submode_CollectionCreate {

// After  
use Application\Admin\Area\Mode\BaseRecordCreateSubmode;

class UserCreateScreen extends BaseRecordCreateSubmode {
```

**Common replacements**:
- `CollectionCreate` → `BaseRecordCreateSubmode`
- `CollectionEdit` → `BaseRecordSettingsSubmode`
- `CollectionDelete` → `BaseRecordDeleteSubmode`
- `CollectionList` → `BaseRecordListSubmode`
- `CollectionRecord` → `BaseRecordSubmode`

#### Step 4.4: Add `getAdminScreensFolder()` Method

Each module needs this static method:

```php
// In your feature/collection class
public static function getAdminScreensFolder(): FolderInfo {
    return AppFolder::getFolder(__DIR__ . '/Admin');
}
```

#### Step 4.5: Verify Screen Loading

1. Clear cache completely
2. Load application
3. Navigate to admin area
4. Verify screens load without errors
5. Check browser console for errors

#### Step 4.6: Commit Screen Updates

```bash
git add assets/classes/
git add src/classes/MyApp/OfflineEvents/
git commit -m "refactor(v7): migrate admin screens to new base classes and event listeners"
```

### Phase 5: Code Migration - Other Classes (Estimated: 1-2 hours)

#### Step 5.1: Update Exception Classes

Search and replace exception usage:

```bash
# Find all exception usage
grep -r "new Application_Exception\|catch(Application_Exception\|extends Application_Exception" assets/classes/ --include="*.php"
```

Update each file:

```php
// Before
throw new Application_Exception('Error message');

// After
use Application\Exception\ApplicationException;

throw new ApplicationException('Error message');
```

#### Step 5.2: Update Interface Implementations

Find files implementing deprecated interfaces:

```bash
grep -r "implements Application_Interfaces_Admin\|implements CollectionSettings" assets/classes/ --include="*.php"
```

Update implementations:

```php
// Before
class MyScreen implements Application_Interfaces_Admin_CollectionCreate { }

// After
use DBHelper\Admin\Traits\RecordCreateScreenInterface;

class MyScreen implements RecordCreateScreenInterface { }
```

#### Step 5.3: Update Custom Event Listeners

If you have custom event listeners:

```php
// Before
class MyEventListener extends Application_EventHandler_OfflineEvents_OfflineListener {
    public function wakeUp() {
        return function(Application_EventHandler_Event $event) {
            // ...
        };
    }
}

// After
use Application\EventHandler\OfflineEvents\BaseOfflineListener;
use Application\EventHandler\Event\EventInterface;

class MyEventListener extends BaseOfflineListener {
    public function getEventName(): string {
        return 'MyCustomEvent';
    }
    
    public function handleEvent(EventInterface $event): void {
        // ...
    }
}
```

#### Step 5.4: Verify No PHP Deprecation Warnings

Check PHP error logs for deprecation notices:

```bash
# If using PHP CLI
php -l assets/classes/MyFile.php

# In error logs (tail)
tail -f logs/error.log | grep -i "deprecated"
```

#### Step 5.5: Commit Exception and Event Updates

```bash
git add assets/classes/
git commit -m "refactor(v7): update exception classes and event listeners to v7.0.0 structure"
```

### Phase 6: Type Hints and Code Quality (Estimated: 1-2 hours)

#### Step 6.1: Add Strict Types Declaration

Add this to the top of each PHP file (after opening tag):

```php
<?php
declare(strict_types=1);

namespace MyApp\...;
```

#### Step 6.2: Add Method Type Hints

```php
// Before
public function process($data) {
    return $result;
}

// After
public function process(array $data): array {
    return $result;
}
```

#### Step 6.3: Run PHPStan

```bash
# If PHPStan is configured in framework
vendor/bin/phpstan analyse assets/classes/ --level max

# Or use Psalm if configured
vendor/bin/psalm assets/classes/
```

#### Step 6.4: Fix Type Errors

Address any type errors reported. Common issues:

- Missing null checks: `if ($value !== null)`
- Wrong parameter types: Check method signatures
- Missing return types: Add explicit return types
- Array type hints: Use `array<string, mixed>` syntax

#### Step 6.5: Commit Type Improvements

```bash
git add assets/classes/
git commit -m "refactor(v7): add strict types and comprehensive type hints"
```

### Phase 7: Testing and Validation (Estimated: 1-2 hours)

#### Step 7.1: Unit Tests

```bash
# Run your application's unit tests
vendor/bin/phpunit

# Or if using your own test runner
npm test  # or your test command
```

**Expected**: All tests pass without errors

#### Step 7.2: Functional Testing

1. **Start application**: `php -S localhost:8000` or use your server
2. **Test user login**: Verify authentication works
3. **Test admin screens**:
   - Navigate to each admin area
   - Verify screens load correctly
   - Test CRUD operations
4. **Test file uploads**: If applicable
5. **Test API endpoints**: If applicable
6. **Check logs**: Verify no errors in error logs

#### Step 7.3: Browser Console Check

1. Open browser Developer Tools (F12)
2. Go to Console tab
3. Clear console
4. Navigate through application
5. Look for JavaScript errors or warnings

#### Step 7.4: Deprecated Class Warnings

These warnings are expected during transition (from v6 compatibility layer):

```
Deprecated: [Class] is deprecated, use [NewClass] instead
```

This is normal for v7.0.0. If you see these, it means:
- Deprecated classes are still working
- You haven't updated all code yet
- Focus on HIGH priority files first

#### Step 7.5: Database Integrity Check

```sql
-- Verify data integrity
SELECT COUNT(*) FROM appsets;
SELECT COUNT(*) FROM appsets_values;

-- Check for errors
SHOW ENGINE INNODB STATUS\G  -- for InnoDB
```

#### Step 7.6: Commit Testing Results

```bash
git add tests/
git commit -m "test(v7): verify upgrade compatibility and functionality"
```

### Phase 8: Merge and Deploy (Estimated: 30 minutes)

#### Step 8.1: Final Testing in Feature Branch

```bash
# Run full test suite one final time
vendor/bin/phpunit --testdox

# Review all commits
git log --oneline origin/main..HEAD

# Check for any uncommitted changes
git status
```

#### Step 8.2: Merge to Main

```bash
# Switch to main branch
git checkout main

# Ensure main is up to date
git pull origin main

# Merge feature branch
git merge feature/upgrade-to-v7.0.0

# Or use pull request if in team environment
```

#### Step 8.3: Deploy to Production

**⚠️ Important**: Test on staging first!

```bash
# Deploy to staging
# 1. Pull latest code
# 2. Run composer install
# 3. Run database migration
# 4. Test thoroughly

# After staging verification, deploy to production
# 1. Create backup
# 2. Pull latest code
# 3. Run composer install  
# 4. Run database migration
# 5. Clear cache
# 6. Monitor error logs
```

### Troubleshooting Common Issues

If you encounter issues during migration, see the [Common Issues and Solutions](#common-issues-and-solutions) section below.

## Testing Checklist

Use this checklist to verify your upgrade is complete and working correctly. Check each item off as you complete it.

### Pre-Migration Checklist

- [ ] Created backup of database
- [ ] Created backup of application files
- [ ] Created feature branch for upgrade
- [ ] Reviewed breaking changes section
- [ ] Identified all HIGH priority files
- [ ] Notified team of upgrade in progress
- [ ] Set up testing environment separate from production

### Dependency Updates

- [ ] Updated `composer.json` and `composer.lock`
- [ ] Ran `composer update mistralys/application_framework`
- [ ] Verified PHP version is 8.4+
- [ ] All vendor dependencies installed correctly

### Database Migration

- [ ] Applied SQL migration: `2025-12-19-app-sets.sql`
- [ ] Verified new tables exist: `appsets`, `appsets_values`
- [ ] Backed up new database state
- [ ] No migration errors in logs

### Admin Screens Update

- [ ] Updated all `extends Application_Admin_Area`
- [ ] Updated all `extends Application_Admin_Area_Mode`
- [ ] Updated all `extends Application_Admin_Area_Mode_Submode`
- [ ] Updated all `extends Application_Admin_Area_Mode_Submode_Action`
- [ ] Created offline event listeners for screen folders
- [ ] Renamed `CollectionCreate` → `BaseRecordCreateSubmode`
- [ ] Renamed `CollectionEdit` → `BaseRecordSettingsSubmode`
- [ ] Renamed `CollectionDelete` → `BaseRecordDeleteSubmode`
- [ ] Renamed `CollectionList` → `BaseRecordListSubmode`
- [ ] Renamed `CollectionRecord` → `BaseRecordSubmode`
- [ ] Added `getAdminScreensFolder()` methods where needed
- [ ] Verified no errors in admin screens on load

### Exception Handling Update

- [ ] Updated all `new Application_Exception(` calls
- [ ] Updated all `catch(Application_Exception` statements
- [ ] Updated `extends Application_Exception` declarations
- [ ] Created custom exceptions in proper namespace
- [ ] Verified exception handling in error scenarios

### Event Listeners Update

- [ ] Updated custom offline event classes
- [ ] Updated custom offline event listeners
- [ ] Removed `wakeUp()` methods from listeners
- [ ] Added `getEventName()` methods where needed
- [ ] Organized listener files in correct folders
- [ ] Verified offline events fire correctly

### Type Hints and Code Quality

- [ ] Added `declare(strict_types=1)` to files
- [ ] Added method return type hints
- [ ] Added method parameter type hints
- [ ] Ran PHPStan with max level (if available)
- [ ] Fixed type-related issues
- [ ] Verified no type warnings in logs

### Cache and Compilation

- [ ] Cleared all application caches
- [ ] Cleared compiled files (if applicable)
- [ ] Cleared logs directory
- [ ] Reloaded admin screen index

### Unit Tests

- [ ] All unit tests pass
- [ ] No test failures related to class names
- [ ] No test failures related to namespaces
- [ ] All assertions passing

### Functional Testing - Core Features

- [ ] Application loads without errors
- [ ] Admin interface renders correctly
- [ ] User authentication works
- [ ] Session management works
- [ ] User logout works

### Functional Testing - Admin Screens

- [ ] Main admin area loads
- [ ] All admin screens accessible
- [ ] No "Class not found" errors
- [ ] No "Method not found" errors
- [ ] Screen actions work (Create, Edit, Delete)
- [ ] Filters work on list screens
- [ ] Search functionality works
- [ ] Pagination works

### Functional Testing - Features

- [ ] Database operations (Create/Read/Update/Delete)
- [ ] File uploads (if applicable)
- [ ] File downloads (if applicable)
- [ ] Reports generation (if applicable)
- [ ] Export functionality (if applicable)
- [ ] API endpoints (if applicable)
- [ ] Email sending (if applicable)

### Logging and Errors

- [ ] No PHP fatal errors in error logs
- [ ] No PHP warnings in error logs
- [ ] No PHP deprecation warnings in error logs
- [ ] No SQL errors in logs
- [ ] No JavaScript errors in browser console
- [ ] Application logs are clean

### Performance

- [ ] Admin screens load within reasonable time (< 2 seconds)
- [ ] List screens render without lag
- [ ] Search/filter operations are responsive
- [ ] No obvious memory leaks
- [ ] No excessive database queries

### Security

- [ ] CSRF tokens working
- [ ] XSRF protection functional
- [ ] User permissions respected
- [ ] Admin screens require authentication
- [ ] SQL injection prevention intact

### Documentation and Commits

- [ ] All changes properly committed with descriptive messages
- [ ] No uncommitted code in repository
- [ ] Updated internal documentation where needed
- [ ] Team members informed of changes
- [ ] Created deployment notes (if in team environment)

### Production Readiness

- [ ] Tested in staging environment
- [ ] All stakeholders signed off on changes
- [ ] Rollback plan documented
- [ ] Maintenance window scheduled (if needed)
- [ ] Team trained on changes (if applicable)
- [ ] Monitoring alerts configured

### Post-Deployment

- [ ] Deployed to production successfully
- [ ] Production database migrated
- [ ] Production application loads correctly
- [ ] Admin screens work in production
- [ ] No errors in production logs
- [ ] Monitored for 24 hours for issues
- [ ] Notified team of successful deployment

---

**Completion**: When all items are checked, your upgrade to v7.0.0 is complete!

## Common Issues and Solutions

This section addresses issues commonly encountered during the v7.0.0 upgrade.

### Issue 1: "Class not found" Errors

**Error Message**:
```
Fatal error: Uncaught Error: Class "Application_Admin_Area" not found in /path/to/file.php:10
```

**Cause**: File still using old class names instead of new namespaces

**Solution**:
1. Locate the file mentioned in error
2. Check if the class extends deprecated class name
3. Update to new base class:
   ```php
   // Change from:
   class MyArea extends Application_Admin_Area { }
   
   // To:
   use Application\Admin\BaseArea;
   class MyArea extends BaseArea { }
   ```
4. Add appropriate `use` statement at top of file
5. Clear cache and reload

**Prevention**: Use IDE search/replace to systematically update all class references

---

### Issue 2: Admin Screens Not Displaying

**Symptoms**: 
- Admin area loads but screens list is empty
- Screens don't appear in navigation
- 404 errors when accessing screen URLs

**Cause**: Offline event listeners not registered or screen folders not found

**Solution**:
1. Verify offline event listener exists:
   ```php
   // Should exist: src/classes/YourApp/OfflineEvents/RegisterAdminScreenFolders/RegisterXyzScreensListener.php
   namespace YourApp\OfflineEvents\RegisterAdminScreenFolders;
   
   use Application\CacheControl\BaseRegisterScreenFoldersListener;
   
   class RegisterXyzScreensListener extends BaseRegisterScreenFoldersListener {
       protected function getAdminScreenFolders(): array {
           // Return folder containing screens
       }
   }
   ```

2. Verify `getAdminScreensFolder()` method exists in your feature class:
   ```php
   public static function getAdminScreensFolder(): FolderInfo {
       return AppFolder::getFolder(__DIR__ . '/Admin');
   }
   ```

3. Check folder structure exists:
   ```
   assets/classes/YourApp/Feature/
   └── Admin/
       ├── ListScreen.php
       ├── CreateScreen.php
       └── EditScreen.php
   ```

4. Clear cache:
   ```bash
   rm -rf storage/cache/*
   # Or via admin UI: Settings > Cache > Clear All
   ```

5. Reload admin area and verify screens appear

**Prevention**: Run automated scanner (WP5) to verify screen registration

---

### Issue 3: "Undefined method" or "Unknown method" Errors

**Error Message**:
```
Fatal error: Uncaught Error: Call to undefined method Application_Admin_Area::getScreensFolder()
```

**Cause**: Using method on deprecated class instead of new base class

**Solution**:
1. Update class to extend new base class (see Issue 1)
2. Verify method name is correct in new class
3. Check method signature hasn't changed:
   ```php
   // Old signature
   public function getScreenFolder() { }
   
   // New signature (possibly different)
   public static function getAdminScreensFolder(): FolderInfo { }
   ```
4. Update method calls to match new signature

---

### Issue 4: Exception Errors - "Application_Exception Not Found"

**Error Message**:
```
Fatal error: Uncaught TypeError: Argument 1 passed to handler() must be of type Application_Exception
```

**Cause**: Code catching/throwing wrong exception class

**Solution**:
1. Identify all exception usage in your code:
   ```bash
   grep -r "Application_Exception" assets/classes/ --include="*.php"
   ```

2. Update each file:
   ```php
   // From:
   try {
       // Code
   } catch(Application_Exception $e) {
       // Handle
   }
   
   // To:
   use Application\Exception\ApplicationException;
   
   try {
       // Code
   } catch(ApplicationException $e) {
       // Handle
   }
   ```

3. Clear cache and test exception handling

---

### Issue 5: Offline Event Listeners Not Firing

**Symptoms**:
- Custom event listeners not executing
- Event-dependent code not running
- Cache not clearing on save operations

**Cause**: Listener not properly named or located, or `wakeUp()` method still present

**Solution**:
1. Verify listener class location:
   ```
   src/classes/YourApp/OfflineEvents/YourEventName/
   └── YourEventNameListener.php
   ```

2. Verify listener implements correctly:
   ```php
   use Application\EventHandler\OfflineEvents\BaseOfflineListener;
   
   class YourEventNameListener extends BaseOfflineListener {
       public function getEventName(): string {
           return 'YourEventName';  // Match your event
       }
       
       public function handleEvent(EventInterface $event): void {
           // Implementation
       }
   }
   ```

3. Remove old `wakeUp()` method if present:
   ```php
   // Delete this entirely:
   protected function wakeUp() {
       return function() { ... };
   }
   ```

4. Clear cache completely:
   ```bash
   rm -rf storage/cache/offline_events*
   ```

5. Test event firing

---

### Issue 6: Database Migration Fails

**Error Message**:
```
ERROR 1050 (42S01) at line 5: Table 'appsets' already exists
```

**Cause**: Database migration script already run, or tables partially created

**Solution**:
1. **If tables already exist** (successful migration):
   - No action needed, migration already applied
   - Verify tables have data: `SELECT * FROM appsets LIMIT 1;`

2. **If migration partially failed**:
   - Check which tables exist: `SHOW TABLES LIKE '%appsets%';`
   - Review error logs for specifics
   - If safe, manually clean up partial tables:
     ```sql
     DROP TABLE IF EXISTS appsets;
     DROP TABLE IF EXISTS appsets_values;
     -- Etc. for all appsets tables
     ```
   - Re-run migration script

3. **If running script multiple times**:
   - Modify migration script to use `CREATE TABLE IF NOT EXISTS`
   - Or skip already-created tables manually

---

### Issue 7: Type Hint Errors

**Error Message**:
```
TypeError: Argument 1 passed to MyClass::process() must be of type array, string given
```

**Cause**: Code passing wrong type to method with type hints

**Solution**:
1. Identify method with type error
2. Check method signature:
   ```php
   public function process(array $data): array {
       // $data must be array, not string
   }
   ```

3. Fix calling code:
   ```php
   // Before - passing string:
   $result = $object->process('data');
   
   // After - pass array:
   $result = $object->process(['data']);
   ```

4. Test the corrected code

---

### Issue 8: Namespace Errors - "Use of undefined constant"

**Error Message**:
```
Fatal error: Uncaught Error: Undefined constant "Application\Admin\BaseArea"
```

**Cause**: Missing `use` statement for class

**Solution**:
1. Add `use` statement at top of file:
   ```php
   <?php
   declare(strict_types=1);
   
   namespace MyApp\Admin;
   
   use Application\Admin\BaseArea;  // Add this
   
   class MyArea extends BaseArea {
   ```

2. Verify class namespace is correct:
   - Check file location: `src/classes/Application/Admin/BaseArea.php`
   - Check `namespace` declaration in that file

3. Use full namespace path if needed:
   ```php
   class MyArea extends \Application\Admin\BaseArea {
   ```

---

### Issue 9: Screen Actions Not Working

**Symptoms**:
- Create/Edit/Delete buttons not functioning
- Form submission fails
- Action screens show errors

**Cause**: Incorrect base class for action screens

**Solution**:
1. Identify action screen class:
   ```php
   // Should extend one of:
   // - BaseRecordCreateAction (for create)
   // - BaseRecordDeleteAction (for delete)
   // - BaseRecordSettingsAction (for edit)
   // - BaseRecordListAction (for list operations)
   // - BaseRecordAction (for custom actions)
   ```

2. Update class:
   ```php
   // From:
   class EditUserAction extends Application_Admin_Area_Mode_Submode_Action_CollectionEdit {
   
   // To:
   use Application\Admin\Area\Mode\Submode\BaseRecordSettingsAction;
   
   class EditUserAction extends BaseRecordSettingsAction {
   ```

3. Verify all methods are compatible with new base class
4. Test action in browser

---

### Issue 10: PHP 8.4 Compatibility Issues

**Error Message**:
```
Parse error: syntax error, unexpected '[', expecting ';' on line 10
```

**Cause**: Using PHP < 8.4 features or syntax not compatible with 8.4

**Solution**:
1. Verify PHP version:
   ```bash
   php -v
   # Must show 8.4.x
   ```

2. If version is too old:
   - Update PHP on development/hosting
   - Set CI/CD to test with PHP 8.4+

3. Update code to PHP 8.4 syntax:
   - Use named arguments: `method(named: value)`
   - Use union types: `string|int`
   - Use match expressions instead of switch
   - Use nullsafe operator: `$object?->method()`

---

### Issue 11: Cache Issues After Upgrade

**Symptoms**:
- Old code still running despite updates
- Screens not showing changes
- Stale data being displayed

**Cause**: Application cache not cleared properly

**Solution**:
1. **Manual cache clearing**:
   ```bash
   # Delete all cache files
   rm -rf storage/cache/*
   rm -rf logs/*
   
   # Or if cache is in different location
   find . -name "cache" -type d -exec rm -rf {} +
   ```

2. **Via admin UI** (if accessible):
   - Navigate to Settings > Cache Management
   - Click "Clear All Caches"
   - Click "Rebuild Indexes"

3. **Force browser cache clear**:
   - Hard refresh: Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac)
   - Or clear browser cache entirely

4. **Verify cache cleared**:
   ```bash
   ls -la storage/cache/  # Should be empty or minimal
   ```

---

### Issue 12: "Permission Denied" on Admin Screens

**Symptoms**:
- Admin screens load but show "Access Denied"
- "Insufficient permissions" message
- User logout on screen access

**Cause**: Screen class not registered in screen rights

**Solution**:
1. Find screen registry/rights class:
   ```bash
   grep -r "ScreenRights\|registerScreens" assets/classes/ --include="*.php" | head -5
   ```

2. Verify screen is registered:
   ```php
   // In your ScreenRights class
   protected function _registerRights(): void {
       // Add registration for screen
       $this->register(MyFeatureListScreen::class, 'feature.view');
       $this->register(MyFeatureCreateScreen::class, 'feature.create');
   }
   ```

3. Verify user has required permission
4. Clear cache and test

---

## Getting Help

If you encounter an issue not listed here:

1. **Check the error message carefully**:
   - Read the full stack trace
   - Note the file and line number
   - Understand what operation failed

2. **Search project documentation**:
   - Check [v7.0.0-class-mappings.md](v7.0.0-class-mappings.md)
   - Review [changelog.md](../../changelog.md)
   - Check framework documentation

3. **Review similar issues** in this section
4. **Check logs**:
   ```bash
   tail -f logs/error.log
   tail -f logs/php-error.log
   ```

5. **Test in isolation**:
   - Create minimal test file to reproduce issue
   - Verify assumptions about class/method behavior

6. **Contact framework maintainer** with:
   - Full error message and stack trace
   - Steps to reproduce
   - Code example showing issue
   - Framework version you're upgrading from/to

## Deprecation Timeline

Understanding the deprecation timeline helps you plan your migration strategy:

| Version | Status | Timeline | Action Required |
|---------|--------|----------|-----------------|
| **v7.0.0** | Deprecated classes available with `@deprecated` warnings | Current (2026-02-09) | Begin migration planning |
| **v7.1.0** | Deprecated classes still available | Q2 2026 | Complete migration recommended |
| **v8.0.0** | **Deprecated classes REMOVED** | Q3 2026 (estimated) | **All migration must be complete** |

**⚠️ Critical Recommendation**: Do not delay migration. While backward compatibility is maintained in v7.x, the deprecated classes **will be completely removed** in v8.0.0 (estimated 6-12 months from now). Applications that haven't migrated will experience fatal errors when upgrading to v8.0.0.

### Migration Urgency by Priority

Based on the [class mappings reference](v7.0.0-class-mappings.md):

- **HIGH Priority** (30+ classes): Migrate immediately - these are core classes used in most applications
- **MEDIUM Priority** (10+ classes): Migrate within 1-2 months during normal development
- **LOW Priority** (5+ classes): Migrate when encountered or during refactoring

## Version Compatibility

### Upgrading From

This upgrade guide applies when upgrading from any of these v6.x versions:

- v6.0.0
- v6.1.0
- v6.1.1
- v6.2.0
- v6.3.0

### Upgrading To

- **Target Version**: v7.0.0
- **PHP Requirements**: PHP 8.4+ (strict requirement)
- **Database**: MySQL 5.7+, MariaDB 10.2+
- **Composer**: 2.0+

### Breaking Changes from v6.x

All v6.x applications will require code changes when upgrading to v7.0.0. The extent of changes depends on:

1. **Admin screen usage**: Heavy use of admin screens requires more changes
2. **Exception handling**: Applications with custom exception handling need updates
3. **Event listeners**: Offline event listeners require refactoring
4. **Class inheritance**: Classes extending framework classes need namespace updates

## Additional Resources

### Documentation

- **Class Mappings**: [v7.0.0-class-mappings.md](v7.0.0-class-mappings.md) - Complete reference of all deprecated class mappings
- **Detailed Changelog**: [changelog.md](../../changelog.md) - Full technical changelog with commit details
- **v6.x Changelog**: [v6-changelog.md](../changelog-history/v6-changelog.md) - Historical context
- **Framework Documentation**: [docs/](../) - General framework documentation
- **Agent Documentation**: [agents/](../agents/readme.md) - AI/agent integration guides

### Tools

- **Automated Scanner**: `/tools/upgrade-to-v7.php` (Work Package 5 - in development)
- **Rector**: Code quality and refactoring tool (integrated in framework)
- **PHPStan**: Static analysis tool for type checking

### Getting Help

If you encounter issues during migration:

1. **Review this guide thoroughly** - most common issues are documented
2. **Check the changelog** for additional technical details
3. **Run the automated scanner** (when available) to identify issues
4. **Review your error logs** for specific error messages
5. **Contact framework maintainer** for migration assistance

### Version Control Recommendations

We strongly recommend:

1. **Create a feature branch** for the upgrade: `git checkout -b upgrade-to-v7.0.0`
2. **Commit incrementally** as you complete each migration phase
3. **Use descriptive commit messages** referencing this guide
4. **Test thoroughly** before merging to main/production

Example commit workflow:

```bash
git add composer.json composer.lock
git commit -m "chore: upgrade to framework v7.0.0"

git add database/migrations/
git commit -m "chore(v7): apply database migration 2025-12-19-app-sets.sql"

git add assets/classes/
git commit -m "refactor(v7): update exception classes to use new namespaces"

git add assets/classes/Admin/
git commit -m "refactor(v7): migrate admin screens to new base classes"
```

## Support

### Migration Assistance

For complex applications or if you need hands-on assistance:

1. Review this entire guide first
2. Run the automated scanner to identify issues
3. Attempt the migration in a test environment
4. Document specific errors or blockers
5. Contact framework maintainer with:
   - Framework version currently running
   - Target version (v7.0.0)
   - Specific error messages or issues
   - Steps already attempted

### Reporting Issues

If you discover issues with this guide or the migration process:

1. Document the issue thoroughly
2. Include error messages and logs
3. Specify your environment (PHP version, database, etc.)
4. Report to framework maintainer

### Community Resources

- Framework repository: [GitHub](https://github.com/Mistralys/application-framework)
- Issue tracker: Report bugs and request features
- Agent documentation: AI-assisted development patterns

---

## Document Information

**Document Version**: 1.0 (Complete)  
**Created**: 2026-02-09  
**Last Updated**: 2026-02-09  
**Applies To**: Application Framework v7.0.0  
**Status**: Complete - All sections implemented

**Contributing**: This is a living document. If you discover additional issues or have suggestions during your migration, please contribute updates. See the [AGENTS.md](../../AGENTS.md) for contribution guidelines.
