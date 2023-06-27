# Administration screens system

## Structure

Administration screens in the applications are individual PHP classes. They follow the
framework’s admin structure, and have a fixed maximum logical depth of 4:

1. Area – Main administration area (Products, Countries...)
2. Mode – Sub-navigation within an area (Products list)
3. Submode – Sub-navigation within a mode (View product => Status, Settings, Changelog...)
4. Action – Specific actions within a sub-mode (Publish product, delete product...)

Each of these administration levels has specific base classes that must be extended:

1. Area: `Application_Admin_Area`
2. Mode: `Application_Admin_Area_Mode`
3. Submode: `Application_Admin_Area_Mode_Submode`
4. Action: `Application_Admin_Area_Mode_Submode_Action`

## Hierarchy when handling a request

From top to bottom, each screen checks if it should pass on the handling of the current
request to a sub-level. For example, an area will check if one of its modes should
handle it, which in turn checks all its sub-modes, etc.

This means that all screens in the chain can do their part of the processing as needed.
To illustrate, consider a Product settings screen, which is the following class:

`AppName_Area_Products_View_Settings_Edit`

The screens in this case are, in order:

1. Products (Area)
2. View (Mode)
3. Settings (Submode)
4. Edit (Action).

## Handler methods

Each admin screen has several preset methods for handling its tasks, which can be implemented
as needed. They are called automatically by the system, in the following order:

1. `_handleBeforeActions()`
2. `_handleActions()`
3. `_handleSidebar()`
4. `_handleSubnavigation()`
5. `_handleBreadcrumb()`
6. `_handleHelp()`
7. `_handleContextMenu()`
8. `_handleTabs()`

## Handler chains

### Inheritance

Even if the active screen is an _Action_ at the end of the hierarchy, the handler methods of
all its parents are called nonetheless. This is practical to avoid duplicating tasks.

For example, consider viewing a product property, handled by the class `AppName_Area_Properties_Edit`:
The subnavigation is created here in the `_handleSubnavigation()` method, so that the _Sub-mode_
and _Action_ screens do not have to worry about it.

### Stacking

A side effect of the inheritance is that the handler methods stack together. This is best
illustrated by the `_handleBreadcrumb()` handler: Each screen in the hierarchy can add items
to the breadcrumb, which are automatically appended to the items the parent screen added.

### Canceling

To stop a handler from being propagated down to child screens, it only needs to return
a boolean `false`.

## Accessing information

### Authenticated user

The current user is available in `$this->user`. Anywhere else, it can be fetched via
`AppName::getInstance()->getUser()`. This class instance allows accessing all relevant
user data as well as the user's rights.

The names of all rights in the system are accessible as constants in the user class, for
example: `AppName_User::RIGHT_CREATE_PRODUCTS`. Each of these is also accessible easily
via the user instance itself, for example:

```php
if($this->user->canCreateProducts()) {
    // User can create new products.
}
```

### Request variables

To access any variables from the current request, be they POST or GET, you may use the
request class, which is available in `$this->request`. For details on the request class,
see the "Accessing request data" chapter.

### Links and URLs

Generating links to admin screens should be delegated to the according data types. For example,
to get the URL to edit a prpduct's setting, one must ask the product. The same goes for all
known data types in the application.

- Collections: Fetch the URL to the overview, or other collection-level screens.
- Records: Fetch URLs to the available screens.

Examples:

- `AppName_Products::getAdminListURL()` - URL to the products overview.
- `AppName_Proucts_Product::getAdminEditStatusURL()` - URL to the status screen of the product.
