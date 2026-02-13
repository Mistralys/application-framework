# Agent Guide: XML Changelog Maintenance

## Overview

The Application Framework project maintains a version changelog in XML format. These files document changes for end users across multiple languages. This guide explains the structure, constraints, and best practices for maintaining these changelog files.

## File Organization

### The Primary Changelog

The changelog is application-specific, and has a fixed location where it is
expected to be saved by the WhatsNew handling system:

- **File location:** `/WHATSNEW.xml`

### Changelog History Folder

To keep the size of the main `WHATSNEW.xml` file in check, previous versions
are archived in the changelog history folder, where a file is created for each
minor version within a folder for the main version.

Here is an example:

```
docs/changelog-history/
└── v19/              # Major version 19 (split by minor versions)
    ├── v19.0.xml
    ├── v19.1.xml
    ├── v19.2.xml
    ├── v19.3.xml
    ├── v19.4.xml
    ├── v19.5.xml
    ├── v19.6.xml
    └── v19.7.xml
```

### Organizational Rules

1. **Subfolders for each major version:** Each major version gets its own subfolder in the changelog history folder.
2. **Splitting by minor version**: Each minor version gets its own XML file within the major version folder.

## XML Structure

### Root Element

```xml
<?xml version="1.0" encoding="UTF-8"?>
<whatsnew>
    <version id="19.7.9">
        <!-- content -->
    </version>
</whatsnew>
```

### Version Entry Structure

Each version entry follows this structure:

```xml
<version id="MAJOR.MINOR.PATCH">
    <de>
        <!-- German language entries -->
    </de>
    <en>
        <!-- English language entries -->
    </en>
</version>
```

### Item Structure

Within each language block:

```xml
<de>
    <item category="Category Name">
        Description text that can span multiple lines
        and include formatting.
    </item>
    <item category="Another Category">
        Another change description.
    </item>
</de>
```

## Formatting Rules

### 1. XML Declaration

Always start with:
```xml
<?xml version="1.0" encoding="UTF-8"?>
```

### 2. Version Ordering

**Critical**: Versions must be ordered from newest to oldest (descending):

```xml
<whatsnew>
    <version id="19.7.9">...</version>  <!-- Newest first -->
    <version id="19.7.8">...</version>
    <version id="19.7.5">...</version>
    <version id="19.7.4">...</version>  <!-- Oldest last -->
</whatsnew>
```

### 3. Text Content

- Text content can span multiple lines
- Whitespace and indentation are preserved for readability
- Line breaks within text are natural and expected

### 4. Special Characters

XML special characters must be escaped:

| Character | Escaped Form | Example Context |
|-----------|--------------|-----------------|
| `<` | `&lt;` | `Menü Mail &gt; Erweitert` |
| `>` | `&gt;` | `Menü Mail &gt; Erweitert` |
| `&` | `&amp;` | `[Link](url?key=value&amp;other=123)` |
| `"` | `&quot;` | Used in attributes |

### 5. Markdown Links

Markdown-style links are supported within text:

```xml
<item category="API">
    Moe information in the [Online Guide](https://mistralys.eu/guide?paramA=A&amp;paramB=B).
</item>
```

**Important**: The `&` in URLs must be escaped as `&amp;`.

### 6. Code References

Use backticks for code/API references:

```xml
<item category="APIs">
    The `GetProducts`-API now contains all connected countries.
</item>
```

**Note**: Categories should be consistent within a language but may differ between German and English translations.

## Language Blocks

### Required Language Blocks

- `<de>`: German (primary language)
- `<en>`: English (required for all user-facing changes)

### Rules

1. **All user-facing changes must have both German and English entries**
2. **Content should match semantically between languages** (not necessarily word-for-word)
3. **Number of items should match** between `<de>` and `<en>` unless there's a good reason
4. **End-user facing changes only**, as developer-centric changes are handled separately

## Adding New Changelog Entries

### Step 1: Determine the Target File

New entries always go into the main `WHATSNEW.xml` file. 

> NOTE: The changelog version archiving is a separate process.

### Step 2: Create the Version Entry

```xml
<version id="19.7.10">
    <de>
        <item category="Appropriate Category">
            Deutsche Beschreibung der Änderung.
        </item>
    </de>
    <en>
        <item category="Appropriate Category">
            English description of the change.
        </item>
    </en>
</version>
```

### Step 3: Position the Entry

Insert the new version entry **at the top** of the file (after the root opening tag), maintaining descending version order.

**Before:**
```xml
<?xml version="1.0" encoding="UTF-8"?>
<whatsnew>
    <version id="19.7.9">
        ...
    </version>
```

**After:**
```xml
<?xml version="1.0" encoding="UTF-8"?>
<whatsnew>
    <version id="19.7.10">
        <de>
            <item category="System">
                Neue Funktion hinzugefügt.
            </item>
        </de>
        <en>
            <item category="System">
                Added new feature.
            </item>
        </en>
    </version>
    <version id="19.7.9">
        ...
    </version>
```

### Step 4: Multiple Changes in One Version

If a version has multiple changes, add multiple `<item>` elements within each language block:

```xml
<version id="19.7.10">
    <de>
        <item category="Mailings">
            Erste Änderung in Mailings.
        </item>
        <item category="API">
            Zweite Änderung in der API.
        </item>
        <item category="System">
            Dritte Änderung im System.
        </item>
    </de>
    <en>
        <item category="Mailings">
            First change in mailings.
        </item>
        <item category="API">
            Second change in the API.
        </item>
        <item category="System">
            Third change in the system.
        </item>
    </en>
</version>
```

## Complete Examples

### Example 1: Simple Bug Fix

```xml
<version id="19.7.10">
    <de>
        <item category="Mailings">
            Ein Fehler wurde behoben, der dazu führen konnte, dass
            Mailings nicht korrekt generiert wurden.
        </item>
    </de>
    <en>
        <item category="Mailings">
            A bug has been fixed that could cause mailings to not be
            generated correctly.
        </item>
    </en>
</version>
```

### Example 2: New Feature with API Reference

```xml
<version id="19.8.0">
    <de>
        <item category="API">
            Eine neue API wurde hinzugefügt: `CreateNotification`.
            Diese ermöglicht es, Push-Benachrichtigungen zu erstellen.
        </item>
        <item category="Benachrichtigungen">
            Push-Benachrichtigungen können jetzt über die Benutzeroberfläche
            verwaltet werden.
        </item>
    </de>
    <en>
        <item category="API">
            A new API has been added: `CreateNotification`.
            This allows creating push notifications.
        </item>
        <item category="Notifications">
            Push notifications can now be managed through the user interface.
        </item>
    </en>
</version>
```

### Example 3: With Links and Special Characters

```xml
<version id="19.8.2">
    <de>
        <item category="Dokumentation">
            Die API-Dokumentation wurde aktualisiert und ist jetzt
            unter [API Docs](https://maileditor.example.com/api?section=docs&amp;version=19.8)
            verfügbar.
        </item>
        <item category="Mailings">
            Das Menü wurde umstrukturiert: Mail &gt; Erweitert &gt; Einstellungen.
        </item>
    </de>
    <en>
        <item category="Documentation">
            The API documentation has been updated and is now available
            at [API Docs](https://maileditor.example.com/api?section=docs&amp;version=19.8).
        </item>
        <item category="Mailings">
            The menu has been restructured: Mail &gt; Advanced &gt; Settings.
        </item>
    </en>
</version>
```

## Validation Checklist

Before finalizing changelog entries, verify:

- [ ] XML declaration is present and correct
- [ ] Root element matches the file's existing convention
- [ ] Version ID is in `MAJOR.MINOR.PATCH` format
- [ ] New entry is positioned at the top (newest first)
- [ ] Both `<de>` and `<en>` blocks are present (for user-facing changes)
- [ ] Number of items matches between German and English
- [ ] Categories are consistent and follow established patterns
- [ ] Special characters (`<`, `>`, `&`) are properly escaped
- [ ] URLs in links use `&amp;` instead of `&`
- [ ] Code/API names are wrapped in backticks
- [ ] Text content is clear and concise
- [ ] File is properly indented (4 spaces per level)
- [ ] File ends with a closing `</whatsnew>` tag

## Tips and Best Practices

### 1. Writing Effective Changelog Entries

**Good:**
```xml
<item category="Mailings">
    Ein Fehler wurde behoben, der dazu führen konnte, dass Mailings
    mit bestimmten Inhalten nicht geöffnet werden konnten.
</item>
```

**Avoid (too vague):**
```xml
<item category="Mailings">
    Bug Fix.
</item>
```

### 2. Grouping Related Changes

Group related changes under the same version, but use separate items:

```xml
<version id="19.8.3">
    <de>
        <item category="Tenants">
            Neue Tenants wurden hinzugefügt: Brand A und Brand B.
        </item>
        <item category="Tenants">
            Die Tenant-Übersicht zeigt jetzt die Anzahl der Mailings
            pro Tenant an.
        </item>
    </de>
```

### 3. Version Number Gaps

It's normal to have gaps in patch versions (e.g., 19.7.5 followed by 19.7.8). 
This reflects internal development versioning, which is handled in a separate
developer-centric changelog.

### 4. Consistent Terminology

Maintain consistent terminology across versions:
- Use established category names
- Follow existing patterns for similar changes
- Review recent entries for style guidance

## Common Pitfalls

### ❌ Incorrect: Missing Translation

```xml
<version id="19.8.5">
    <de>
        <item category="API">
            Neue API hinzugefügt.
        </item>
    </de>
    <!-- Missing <en> block -->
</version>
```

### ❌ Incorrect: Unescaped Special Characters

```xml
<item category="Mailings">
    Menu: Mail > Advanced > Settings  <!-- Should be &gt; -->
    Link: https://example.com?key=value&other=123  <!-- Should be &amp; -->
</item>
```

### ❌ Incorrect: Wrong Version Order

```xml
<whatsnew>
    <version id="19.7.5">...</version>
    <version id="19.7.9">...</version>  <!-- Should be first -->
</whatsnew>
```

### ✅ Correct: Complete and Proper Entry

```xml
<version id="19.8.5">
    <de>
        <item category="API">
            Eine neue API wurde hinzugefügt: `GetStatistics`.
            Siehe [Dokumentation](https://example.com/api?method=GetStatistics&amp;v=19.8).
        </item>
    </de>
    <en>
        <item category="API">
            A new API has been added: `GetStatistics`.
            See [Documentation](https://example.com/api?method=GetStatistics&amp;v=19.8).
        </item>
    </en>
</version>
```

## Creating a New Major Version

When starting a new major version (e.g., v20):

1. **Create a subdirectory to archive the previous version**: `v19/`
2. **Create the minor version files**: `v19/v19.9.xml` (for each minor version in the WHATSNEW.xml)

### Creating a New Minor Version File

When creating a new minor version file (e.g., `v19.8.xml`):

1. **Create the file in the appropriate subdirectory** (e.g., `v19/`)
2. **Follow the naming convention**: `v{MAJOR}.{MINOR}.xml`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<whatsnew>
    <version id="19.8.0">
        <!-- First entry for this minor version -->
    </version>
</whatsnew>
```

## Integration with WHATSNEW.xml

The main `WHATSNEW.xml` file in the project root typically contains the most recent changes and may be periodically archived into the `docs/changelog-history/` structure. When archiving:

1. Copy relevant entries to the appropriate history file
2. Maintain version ordering (newest first)
3. Ensure all formatting and escaping is preserved
4. Update both XML and markdown versions if applicable

---

## Quick Reference

**Version Format**: `MAJOR.MINOR.PATCH` (e.g., `19.7.9`)

**File Location**:
- v13-v18: `docs/changelog-history/v{MAJOR}.xml`
- v19+: `docs/changelog-history/v{MAJOR}/v{MAJOR}.{MINOR}.xml`

**Required Blocks**: `<de>` and `<en>` for user-facing changes

**Entry Position**: Always at the top (newest first)

**Escape These**:
- `<` → `&lt;`
- `>` → `&gt;`
- `&` → `&amp;`

**Root Element**: Use `<whatsnew>`.

---

This guide should be updated as conventions evolve. When in doubt, examine recent entries in the appropriate version file for guidance.
