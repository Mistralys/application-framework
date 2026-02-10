# Module: Application Sets

## Purpose

Application sets are a way, at configuration level, to control which
administration areas are enabled when running the application.
This allows maintaining multiple instances of the same codebase that
run with different features enabled or disabled.

## Definition of "Areas"

In the administration UI layer, areas are all classes that extend 
the interface `Application\Interfaces\Admin\AdminAreaInterface`.
These are the entry points for the main sections of the admin UI.

Modules typically have their own admin areas when they have settings
to configure or manage.

- **Area interface file**: [AdminAreaInterface.php](/src/classes/Application/Interfaces/Admin/AdminAreaInterface.php)

### Core Areas vs. Regular Areas

Core areas are those that are part of the core application and are
always enabled. Regular areas are those provided by optional modules
(either framework modules or application modules) and can be enabled 
or disabled via application sets.

### Identifying Core Areas

Core areas will return `true` when the method `isCore()` is called.

## The Active Application Set Configuration

The application set to use to run the application is selected
in the configuration constant `APP_APPSET`. This constant is 
defined in the application's configuration files.

The active application set can be retrieved programmatically:

```php
use Application\AppFactory;

$activeSet = AppFactory::createAppSets()->getActive();

$enabledAreas = $activeSet->getEnabledAreas();
```

> NOTE: The active application set cannot be changed at runtime.

### Default Application Set

If the configuration constant `APP_APPSET` is not defined it is
assumed that all areas are enabled. This is equivalent to having
an application set that enables all areas.

## UI Layer Initialization

The driver uses the active application set to determine which
admin areas to initialize in the admin UI layer, namely in the
method: `Application_Driver::prepare()`.

## Administration Screens

The available admin screens for the application sets are located
in the folder:

- **Admin Screens Folder**: [AppSets/Admin](/src/classes/Application/AppSets/Admin)

## Application Sets Collection

Available application sets are managed by the `Application\AppSets\AppSetsCollection` class.
This is a standard [DBHelper collection][], and facilitates retrieving
application sets from the database, as well as managing existing ones.

- **Collection class file**: [AppSetsCollection.php](/src/classes/Application/AppSets/AppSetsCollection.php)
- **Application Set Record class file**: [AppSetRecord.php](/src/classes/Application/AppSets/AppSet.php)
- **Filter Criteria class file**: [AppSetFilterCriteria.php](/src/classes/Application/AppSets/AppSetsFilterCriteria.php)

[DBHelper collection]: /src/classes/DBHelper/README.md
