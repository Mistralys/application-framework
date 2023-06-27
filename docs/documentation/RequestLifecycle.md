# Lifecycle of a request

## Boot steps

The following shows the different steps that an application goes through
in a single request. Items marked with "_(UI mode)_" are only executed when
the target page serves an administration screen.

01) The entry point is always a "dispatcher" like `index.php`.
02) The application is started using `Application_Bootstrap::boot('ScreenID')`.
03) The according bootstrap class is loaded, e.g. `Application_Bootstrap_Screen_Main`.
04) The class's `_boot()` method is called
05) The class can choose startup options (see "Startup Options").
06) The environment is set up using the `createEnvironment()` method.
07) The database layer is initialized.
08) The session is initialized, and user authenticated if authentication is enabled.
09) The `Application` object is instantiated.
10) The localization layer is initialized.
11) The application's driver class is instantiated.
12) The application's `start()` method is called.
13) The request object is instantiated.
14) The event `DriverInstantiated` is triggered.
15) The available admin areas are determined.
16) _(UI mode)_ The `UI_Page` object is instantiated.
17) The driver's `start()` method is called.
18) _(UI mode)_ If in maintenance mode, the maintenance screen is displayed.
19) _(UI mode)_ The selected admin area is instantiated.
20) _(UI mode)_ The area's `handleActions()` handler is executed.
21) _(UI mode)_ The driver's `setUpUI` method is called.
22) _(UI mode)_ If the user is not allowed to log in, redirect to the logout screen.
23) _(UI mode)_ Build the main navigation structure.
24) _(UI mode)_ Instantiate the UI element objects (subnavigation, sidebar, tabs, ...).
25) _(UI mode)_ Execute the admin screen's UI related handlers, in this order:
    1) `handleSidebar()`
    2) `handleSubnavigation()`
    3) `handleBreadcrumb()`
    4) `handleHelp()`
    5) `handleContextMenu()`
    6) `handleTabs()`
26) The event `ApplicationStarted` is triggered.
27) _(UI mode)_ The application's `display()` method is called.
28) _(UI mode)_ The active administration screen's `renderContent` method is called.
29) _(UI mode)_ The page's HTML is rendered using the frame template.
30) _(UI mode)_ The HTML is sent to the browser.

## UI mode vs SCRIPT mode

When in script mode (see "Startup Options"), the tasks of the request are the responsibility
of the boot screen class itself. An example of this is the AJAX method handling
screen: `Application_Bootstrap_Screen_Ajax`. Otherwise, the active admin screens chain handles
everything.

## Startup Options

Before calling the boot screen's `createEnvironment()` method, the screen can configure
several options that will determine how the application is run.

- `enableScriptMode()` - Run the application in script mode, disabling the admin screen selection.
- `disableAuthentication()` - Turns off user authentication.
- `disallowDBWriteOperations()` - Guarantees that no database write operations are executed.
