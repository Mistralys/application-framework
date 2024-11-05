# Upgrade guide: v5.4.0

## Cache Control

To make use of the new cache control screen, do the following:

1. Create a screen class under `{DriverName}/Area/Devel/CacheControlScreen.php`
   that implements the base class `CacheControlScreenInterface`.
2. Call `registerCacheControl()` in your developer admin screen.

## Application configuration

To use the new configuration summary screen in the developer administration,
do the following:

1. Create a screen class under `{DriverName}/Area/Devel/AppConfigScreen.php`
   that implements the base class `BaseAppConfigScreen`.
2. Call `registerAppConfig()` in your developer admin screen.

## Recent items

1. Open the class `{DriverName}_User_Recent`.
2. Call `registerNews()` to add news entries to the categories.
3. Call `registerMedia()` to add media documents to the categories.

## Database Update

This update is straightforward and non-destructive. Import the provided SQL script:

[docs/sql/2024-10-28-user-email-index.sql](/docs/sql/2024-10-28-user-email-index.sql)

## Breaking changes

1. All custom Ajax methods of the application must now implement the `getMethodName()`
   method to return their method name. This makes it possible to use namespaces and
   arbitrary class names for the methods.
2. Removed the global constant `APP_FRAMEWORK_DOCUMENTATION_URL`.
   Use the `DocumentationHub` class instead.
