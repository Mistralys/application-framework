## v5.2.0 - Developer changelog handling
- Driver: Added missing implementation for the `areaExists()`.
- Driver: Made the use of the `dev-changelog.md` file official.
- Driver: Added the helper class `DevChangelog` to parse the developer changelog file.
- Driver: The `version` file can now optionally be automatically generated.
- Tagging: Fixed a hardcoded media collection reference in the tag collection trait.
- Tagging: Added `_handleHiddenFormVars()` in the record's tagging screen trait.

### Update guide

To make use of the new version file generation mechanism, use the following code
for the driver's `getExtendedVersion()` method:

```php
public function getExtendedVersion() : string
{
    return AppFactory::createDevChangelog()->getCurrentVersion()->getTagVersion();
}
```
