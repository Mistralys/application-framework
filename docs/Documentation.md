# Introduction

The framework provides the underlying application structure,
from the user authentication and administration screens to the
UI theming. It is a custom framework that is not based on any
known frameworks: it grew from the original development of the
SPIN system. It was still fully integrated in SPIN and the
Maileditor until 2019, when it was separated into its own project.

# Responsibilities

The framework is responsible for many of the underlying tasks that
a web or console application may need. This includes, but is not 
limited to:

- Creating the working environment of a request.
- Providing the base skeleton of end user screens.
- Providing finished, common end user screens.  
- Providing a unified UI and templating layer.
- Providing a localization layer.
- Providing structural elements for handling data.
- Giving access to common development tools.

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

# Administration screens system

## Structure

Administration screens in the Maileditor are individual PHP classes. They follow the
framework’s admin structure, and have a fixed maximum logical depth of 4:

1. Area – Main administration area (Mails, Comtypes...)
2. Mode – Sub-navigation within an area (Mails list)
3. Submode – Sub-navigation within a mode (View mail => Status, Upload, Changelog...)
4. Action – Specific actions within a sub-mode (Publish mail, delete mail...)

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
To illustrate, consider the Mail builder screen, which is the following class:

`Maileditor_Area_Mails_View_Audiences_Edit`

The screens in this case are, in order:

1. Mails (Area)
2. View (Mode)
3. Audiences (Submode)
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

For example, consider viewing a communication type, handled by the class `Maileditor_Area_Comtypes_Edit`:
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
`Maileditor::getInstance()->getUser()`. This class instance allows accessing all relevant
user data as well as the user's rights.

The names of all rights in the system are accessible as constants in the user class, for
example: `Maileditor_User::RIGHT_CREATE_MAILS`. Each of these is also accessible easily
via the user instance itself, for example:

```php
if($this->user->canCreateMails()) {
    // User can create new mails.
}
```

### Request variables

To access any variables from the current request, be they POST or GET, you may use the
request class, which is available in `$this->request`. For details on the request class,
see the "Accessing request data" chapter.

### Links and URLs

Generating links to admin screens should be delegated to the according data types. For example,
to get the URL to edit a mailing's setting, one must ask the mailing. The same goes for all
known data types in the Maileditor.

- Collections: Fetch the URL to the overview, or other collection-level screens.
- Records: Fetch URLs to the available screens.

Examples:

- `Maileditor_Mails::getAdminListURL()` - URL to the mailings overview.
- `Maileditor_Mails_Mail::getAdminEditStatusURL()` - URL to the status screen of the mailing.

# Application UI

The UI in the application framework is largely abstracted through PHP classes representing
the different UI elements. The aim is to be able to focus more on the actual functionality
than layout details.

## Page structure

All pages have the following elements (items marked with * are optional):

- Main navigation
- Breadcrumb*
- Sidebar*
- Subnavigation*
- Tabs*
- Title*
- Subtitle*
- Content area
- Footer _(automated)_

## The theme renderer

The content area of the page including the title and/or subtitle is handled by the theme's
renderer class, which is available in admin screens under `$this->renderer`. It also
controls whether the page has a sidebar.

A typical use for the renderer is as result of the screen's render handler:

```php
protected function _renderContent()
{
    return $this->renderer
        ->setTitle(t('Page title'))
        ->setAbstract(t('Page abstract text to explain its uses.'))
        ->setContent('<p>(page content)</p>')
        ->setWithSidebar(); // Enable the sidebar for the screen
}
```

## The main navigation

The menu items available in the main navigation are determined by the Maileditor's
`getAdminAreas()` method.

### Customizing navigation items

Further configuration of the menu item is done in the admin area class itself:

- **Setting a submenu:** Return a menu name in the `getNavigationGroup()` method.
- **Setting an icon:** Simply return the icon in the area's `getNavigationIcon()` method.

## The sidebar

The sidebar is an object, available under `$this->sidebar` in administration screens.
It offers a number of methods to add elements to the sidebar.

### Adding buttons

The sidebar uses a custom button implementation, specialized for the sidebar to allow
defining the visibility of buttons based on user rights for example.

A simple linked button:

```php
protected function _handleSidebar()
{
    $this->sidebar->addButton('homepage', t('Homepage'))
    ->setIcon(UI::icon()->home())
    ->makePrimary()
    ->makeLinked('https://maileditor.server.lan');
}
```

Any combination of conditions can be set for the button to be shown:

```php
protected function _handleSidebar()
{
    $this->sidebar->addButton('create', t('Create mail'))
    ->makePrimary()
    ->requireRight(Maileditor_User::RIGHT_CREATE_MAILS)
    ->requireTrue($trueCondition)
    ->requireFalse($falseCondition);
}
```

Disabling a button:

```php
protected function _handleSidebar()
{
    $this->sidebar->addButton('download', t('Download'))
    ->disable(t('Not possible at this time'));
}
```

### Adding separators

Separators are intelligent: there cannot be any duplicate separators,
and the sidebar cannot end with a separator. This way they can be
added anywhere, and will adjust to the visibility of the buttons.

```php
protected function _handleSidebar()
{
    $this->sidebar->addSeparator();
}
```

### Adding messages

```php
protected function _handleSidebar()
{
    $this->sidebar->addErrorMessage(t('Message text'));
    $this->sidebar->addWarningMessage(t('Message text'));
    $this->sidebar->addSuccessMessage(t('Message text'));
    $this->sidebar->addInfoMessage(t('Message text'));
}
```

### The developer panel

A special sidebar content section for developers is available,
which can be used to add developer specific functionality. Typically,
this is used to simulate form submits or other operations for testing
purposes.

The panel is of course only shown to developers.

Example:

```php
protected function _handleSidebar()
{
    $this->sidebar->addSeparator();
            
    $dev = $this->sidebar->addDeveloperPanel();
    
    $dev->addButton(
        UI::button(t('Homepage'))
        ->setIcon(UI::icon()->home())
        ->link('https://maileditor.server.lan')
    );
}
```

#### Converting sidebar buttons

For many sidebar button variants, a developer variant can be created
simply by copying an existing sidebar button. Only buttons with custom
javascript click handlers cannot be converted.

```php
protected function _handleSidebar()
{
    // A regular form submit button
    $this->sidebar->addButton('save', t('Save now'))
    ->setIcon(UI::icon()->save())
    ->makeClickableSubmit($this);
    
    $this->sidebar->addSeparator();
            
    $dev = $this->sidebar->addDeveloperPanel();
    
    // Add a simulation mode copy of the save button
    $dev->addConvertedButton('save');
}
```

## The breadcrumb

The breadcrumb is handled in the screen method `_handleBreadcrumb()`, and
accessible via the property `$this->breadcrumb`.

### Following the screen hierarchy

The breadcrumb is present in all screens. The optimal way to handle it is
to add elements sequentially, following the screen hierarchy. Consider
the screen to edit an address book contact, for example:

1) Address book (Area) - `Maileditor_Area_Recipients`
2) Contact editor (Mode) - `Maileditor_Area_Recipients_Edit`
3) Contact settings form (Submode) - `Maileditor_Area_Recipients_Edit_Settings`

Each of these can add its own level to the breadcrumb, so it does not need
to be built from the root in each screen.

### Adding an item

```php
protected function _handleBreadcrumb()
{
    // Add an item with link
    $this->breadcrumb->appendItem(t('Label'))->makeLinked('https://somewhere');

    // The last item can be added without link
    $this->breadcrumb->appendItem(t('Last item'));
}
```

  > NOTE: Items may be added without link, but it is recommended to always set
    a link, as new subscreens can always be added in the future, which would
    then leave a step linkless in the breadcrumb.

## The subnavigation

By default, no subnavigation is shown. It is enabled automatically as soon
as any items are added to it. It is handled in the screen method `_handleSubnavigation`,
and is accessible via the property `$this->subnav`.

### Adding items

The are several flavors of items that can be added to a subnavigation.
The most commonly used is simple URLs to subscreens of the current screen.

#### Subscreen URLs

This simply adds a navigation item with a link to a subscreen, or any other URL.

```php
protected function _handleSubnavigation() 
{
    $item1 = $this->subnav->addURL(t('Item 1'), '(url)');
    $item2 = $this->subnav->addURL(t('Item 2'), '(url)');
}
```

#### Dropdown menus

  > Note: dropdown menus cannot be automatically marked as active. You will have
    to manually set it as active (see "Marking as active").

```php
protected function _handleSubnavigation()
{
    $menu = $this->subnav->addDropdownMenu(t('Menu'));

    $menu->addLink(t('Item 1'), '(url)');
    $menu->addSeparator();
    $menu->addLink(t('Item 2'), '(url)');
}
```

#### Search box

A search box can be added, which will automatically trigger the specified callback
function when the user has entered search terms, to adjust the screen's contents
accordingly.

The following example adds a search box on the right, with a minimum search term
length of 3 characters. More configuration options can be found in the search
item class itself: `UI_Page_Navigation_Item_Search`.

```php
protected function _handleSubnavigation() 
{
    $this->subnav->addSearch(array($this, 'handleSearch'))
        ->setMinSearchLength(3)
        ->makeRightAligned();
}

/**
 * Called when the user entered search terms in the search box. 
 * @param UI_Page_Navigation_Item_Search $search
 * @param string[] $terms List of search terms
 * @param string $scope The scope of the search (if scopes were added)
 */
public function handleSearch(UI_Page_Navigation_Item_Search $search, array $terms, string $scope) : void
{
    // Do something with the search details,
    // like for example filtering a data grid.
}
```

### Configuring visibility

Navigation items implement the conditional interface, which means they can be
tied to specific user rights, and any additional freeform conditions.

```php
protected function _handleSubnavigation() 
{
    $this->subnav->addURL(t('Nav item'), '(url)')
        ->requireRight(Maileditor_User::RIGHT_CREATE_MAILS)
        ->requireTrue($trueCondition)
        ->requireFalse($falseCondition);
}
```

### Marking as active

The subnavigation intelligently checks whether an item must be marked as
the active one, by comparing the request variables from its own URL with
those of the current request.

If this system is not sufficient for your use case, the active item can
be selected manually:

```php
protected function _handleSubnavigation() 
{
    $item1 = $this->subnav->addURL(t('Item 1'), '(url)');
    $item2 = $this->subnav->addURL(t('Item 2'), '(url)');
        
    $this->subnav->forceActiveItem($item2);
}
```

## The inline help

Documentation can be added to a screen to make it self-documenting. This
is done in the `_handleHelp()` method, using the property `$this->help`.

This object offers a number of methods to add help texts, which are
displayed in the UI.

```php
protected function _handleHelp()
{

}
```

## Templates

### Introduction

For more detailed layouts or cases in which custom HTML code is needed,
templates may be used. This is possible for chunks of layout, as well as
entire pages.

Historically, templates are procedural PHP files that simply have to
generate content. Recently, this was upgraded to allow class-based templates,
to facilitate code validation with tools like PHPStan, and make working
with the template methods easier.

Going forward, new templates should all be class-based. Older templates are
being upgraded piecemeal.

### Creating template files

Template files are stored in the folder:

`themes/default/templates`

They can be freely categorized in subfolders.

The classes must follow the naming scheme:

`driver_template_default_xxx`

Where `xxx` is the name of the template, with underscores replacing folder
slashes. Example for the template file `comtypes/status.php`:

`driver_template_default_comtypes_status`

### Overriding a framework template

When overriding a legacy, non class-based framework template, a file with
the same name must be added in the application's templates folder.

In case of a class-based template, a file of the same name needs to be
created, and the template class must extend the original template class.

An example is the user logged out template `logged-out.php`:

```php
class driver_template_default_logged_out extends template_default_logged_out
{
}
```

### Creating a template instance

A template can be created by the `UI::createTemplate()` method. It can be
given any number of variables to work with, and since it implements the
renderable interface, it can be cast to string and offers the methods
`render()` and `display()`.

In an admin screen, it can be done this way for example:

```php
protected function _renderContent()
{
    $tmpl = $this->ui->createTemplate('template_name');
    $tmpl->setVar('customerName', 'Max Mustermann');
    
    return $this->renderer
        ->appendContent($tmpl)
        ->makeWithoutSidebar();
}
```

### Structure of a template class

The following example shows the structure of a typical template class.

```php
class driver_template_default_example extends UI_Page_Template_Custom
{
    /**
     * Generates the template's output, sending it directly to 
     * standard output. The templating system catches this output
     * automatically.  
     */
    protected function generateOutput(): void
    {
        ?>
            <p>
                <?php pt('Welcome, dear customer %1$s', $this->name) ?>
            </p>
        <?php
    }
    
    /**
     * @var string
     */
    private $name;
    
    /**
     * Called before the output is generated. This is where the
     * required information from variables should be fetched and
     * verified. 
     */ 
    protected function preRender(): void
    {
        $this->name = $this->getStringVar('customerName');
    }
}
```

### Accessing variables

The template class has type-flavored methods to avoid having to do type casting
all the time, and to make the code analysis easier for tools like PHPStan.

These methods include:

- `getArrayVar('varname')`
- `getBoolVar('varname')`
- `getObjectVar('varname', 'Expected_Class_Name')`
- `getStringVar('varname')`

# UI

## Adding clientside includes

### JavaScript includes

JavaScript include files must be stored in the application's theme under:

```
htdocs/themes/default/js
```

They can be organized into subfolders as necessary.

#### Application-Internal

To load an include in any page, use the `addJavascript()` method:

```php
UI::getInstance()->addJavascript('filename.js');
```

This automatically gets enqueued in the page depending on when it is called.
Per default, includes are added in the page's `<head>` section. If they are
added after the head has been rendered, or the `defer` parameter has been set
to `true`, the script is added at the bottom of the `<body>` tag.

  > NOTE: The same file can be safely added several times - each one only gets
    added once in the page.

#### External

Any file name that contains an absolute URL is considered an external include.

```php
UI::getInstance()->addJavascript('https://domain/filename.js');
```

#### From a composer package

Stylesheets can also be loaded directly from a composer dependency.

```php
UI::getInstance()->addVendorJavascript(
    'mistralys/html_quickform2', // Package name 
    'js/src/rules.js' // Relative path to the file
);
```

### Stylesheet includes

CSS include files must be stored in the application's themes folder, under:

```
htdocs/themes/default/css
```

They can be organized into subfolders as necessary.

#### Application-internal

To load an include in any page, use the `addJavascript()` method:

```php
// Add an include for all (screen & print)
UI::getInstance()->addStylesheet('filename.css');

// Add an include for screen only
UI::getInstance()->addStylesheet('filename.css', 'screen');
```

This automatically gets enqueued in the page depending on when it is called.
Per default, includes are added in the page's `<head>` section. If they are
added after the head has been rendered the stylesheet is added at the bottom 
of the `<body>` tag.

  > NOTE: The same file can be safely added several times - each one only gets
    added once in the page.

#### External

Any file name that contains an absolute URL is considered an external include.

```php
UI::getInstance()->addStylesheet('https://domain/filename.css');
```

#### From a composer package

Stylesheets can also be loaded directly from a composer dependency. 

```php
UI::getInstance()->addVendorStylesheet(
    'mistralys/application-utils', // Package name 
    'css/urlinfo-highlight.css' // Relative path to the file
);
```

## Adding JavaScript statements

Like adding JavaScript includes, actual statements can be added to the page,
either to the `<head>` for the page initialization, or specifically to be
executed on page load.

```php
$ui = UI::getInstance();

// To execute in the <head>, in the order statements are added
$ui->addJavascriptHead("console.log('I have been called.')");

// To execute on page load
$ui->addJavascriptOnload("alert('The page has been loaded.')");
```

For adding complex function or method calls, they can be added with native PHP
variable values, and converted into a javascript statement, including associative
arrays.

```php
$ui = UI::getInstance();

$ui->addJavascriptHeadStatement(
    'functionName',
    true,
    42,
    'String\'s the way to go',
    array(
        'key' => 'value'
    )
);
```

This creates the following JavaScript statement:

```js
functionName(true, 42, 'String\'s the way to go', {'key': 'value'});
```

To do the same on page load, use `addJavascriptOnloadStatement()`.

# UI Elements

## Frontend CSS framework

The framework uses Bootstrap v2.3.2:

https://getbootstrap.com/2.3.2

## Data grids / tables

### Introduction

Grids are handled by a specialized class, `UI_DataGrid`. It offers a fully object-oriented
way to create lists of items, with many configurable options. An instance can be created
using the `UI::createDataGrid()` method. In admin screens, it is available via
`$this->ui->createDataGrid()`.

### Creating a simple list

```php
$ui = UI::getInstance();

$grid = $ui->createDataGrid('grid_name');
$grid->addColumn('name', t('Name'));
$grid->addColumn('lastname', t('Last name'));

// Entries are specified as an array with the
// specified column names for each entry.
$entries = array(
    array(
        'name' => 'Max',
        'lastname' => 'Mustermann'
    )
);

$html = $grid->render($entries);
```

### Customizing individual entries

#### Using entry objects

Instead of creating a list of associative arrays, it is possible to use entry objects,
which allow further customization of the individual entries. To create an entry instance, 
use the `createEntry()` method. This still requires specifying the entry data, but 
enables easy access to the entry methods.

The following example creates a list with two entries, that are styled as a successful
and a failed operation respectively.

```php
$ui = UI::getInstance();

$grid = $ui->createDataGrid('grid_name');
$grid->addColumn('name', t('Name'));
$grid->addColumn('lastname', t('Last name'));

// Create an entry instance for each list entry
$entries = array(
    $grid->createEntry(array(
        'name' => 'Max',
        'lastname' => 'Mustermann'
    ))
    ->makeSuccess(),
    $grid->createEntry(array(
        'name' => 'Otto',
        'lastname' => 'Mustermann'
    ))
    ->makeWarning()
);

$html = $grid->render($entries);
```

  > NOTE: Both entry styles can be freely mixed, so you may use array 
    and object entries interchangeably.

#### Styling options

Beyond the `makeSuccess()` and `makeWarning()` methods, rows can be customized
by adding custom classes using `addClass()`. The entries have all usual class
related methods. These classes are added to the `<tr>` tag.

#### Adding a merged row

Merged rows may be added using a specialized entry object, which is created
using the grid's `createMergedEntry()` method.

The following example adds a regular entry row, and a merged row below it.

```php
$ui = UI::getInstance();

$grid = $ui->createDataGrid('grid_name');
$grid->addColumn('name', t('Name'));
$grid->addColumn('lastname', t('Last name'));

$entries = array(
    array(
        'name' => 'Max',
        'lastname' => 'Mustermann'
    ),
    // The merged row requires a single string parameter 
    // which is used as the cell's content. This may contain
    // HTML, so allows the use of complex elements.
    $grid->createMergedEntry('(content here)')
);

$html = $grid->render($entries);
```

The merged entry has the same styling options as the regular entry class, but
cannot use checkboxes to select them.

#### Adding a header row

A header row can be used to visually separate groups of entries within a grid.
Not to mistake with the grid's column headers: this is a merged row with a single
heading text.

The following example adds a heading at the beginning of the table:

```php
$ui = UI::getInstance();

$grid = $ui->createDataGrid('grid_name');
$grid->addColumn('name', t('Name'));
$grid->addColumn('lastname', t('Last name'));

$entries = array(
    $grid->createHeadingEntry(t('Users list')),
    array(
        'name' => 'Max',
        'lastname' => 'Mustermann'
    )
);

$html = $grid->render($entries);
```

#### Excluding entries from the count

When using entries in a grid for layout only purposes, like merged cells, it 
is possible to mark them as excluded from the items count shown in the grid's 
footer.

```php
$ui = UI::getInstance();

$grid = $ui->createDataGrid('grid_name');
$grid->addColumn('name', t('Name'));
$grid->addColumn('lastname', t('Last name'));

$entries = array(
    $grid->createEntry(array(
        'name' => 'Otto',
        'lastname' => 'Beispielmann'
    ))
    ->makeNonCountable(),
    array(
        'name' => 'Max',
        'lastname' => 'Mustermann'
    )
);

$html = $grid->render($entries);
```

  > NOTE: Headings are always excluded from the count.

### Using a grid as screen content

In an admin screen, the principle is to create the grid in the `_handleActions()` method
using a separate method, and to append it to the page content in `_renderContent`.

Example skeleton for a data grid screen:

```php
class Maileditor_Area_Example_DataGrid extends Application_Admin_Area_Mode
{
    /**
     * @var UI_DataGrid 
     */
    protected $grid;
    
    protected function _handleActions()
    {
        $this->createGrid();
    }
    
    protected function _renderContent()
    {
        // Create the entries array 
        $entries = array(
            array(
                'name' => 'Max',
                'lastname' => 'Mustermann'
                
            )
        );
        
        return $this->renderer
            ->setTitle(t('A mighty fine grid'))
            ->appendDataGrid($this->grid, $entries)
            ->makeWithoutSidebar();
    }
    
    protected function createGrid() : void
    {
        // Configure the data grid
        $grid = $this->ui->createDataGrid('grid_name');
        $grid->addColumn('name', t('Name'));
        $grid->addColumn('lastname', t('Last name'));
        
        // Store the grid in a property to access it anywhere
        $this->grid = $grid;
    }
}
```

### Enabling the column navigator

The column navigator can be used to control the amount of columns shown in the UI, when
there are many columns. It adds buttons to choose the amount of columns clientside, as
well as other useful tools.

```php
$ui = UI::getInstance();

$grid = $ui->createDataGrid('grid_name');
$grid->addColumn('name', t('Name'));
$grid->addColumn('lastname', t('Last name'));

// Enable the navigator, and display a minimum of 4 columns
$grid->enableColumnControls(4);
```

### Enabling multiple selection checkboxes

When the checkboxes are enabled, users can select entries and have actions applied to
them. The available actions can be configured separately.

```php
class Documentation_DataGrid_MultiSelect extends Application_Admin_Area_Mode
{
    protected $grid;

    protected function _handleActions()
    {
        $this->createDataGrid();
    }
    
    protected function createDataGrid() : void
    {
        $grid = $this->ui->createDataGrid('grid_name');
        $grid->addColumn('name', t('Name'));
        $grid->addColumn('lastname', t('Last name'));
        
        // Enable the multiple selection, based on the 
        // primary key 'id'. This must be present in the
        // entries. The name and type of the primary ID
        // can be freely chosen.
        $grid->enableMultiSelect('id');

        // Configure a multiselect action, which will execute a 
        // callback when chosen by a user.
        $grid->addAction('delete', t('Delete...'))
            ->setIcon(UI::icon()->delete())
            ->makeDangerous()
            ->makeConfirm(t('Are you sure you wish to delete this user?'))
            ->setCallback(array($this, 'callback_multiActionDelete'));
            
        // Store the grid
        $this->grid = $grid;
    }

    /**
     * The callback function is called automatically when the user has 
     * selected items, and chosen the action in the menu.
     * @param UI_DataGrid_Action $action
     */
    public function callback_multiActionDelete(UI_DataGrid_Action $action) 
    {
        $ids = $action->getSelectedValues();
        
        // Do something with the IDs
        
        // When done, redirect the user with a message.
        $this->redirectWithSuccessMessage(
            t('The selected items have been deleted successfully at %1$s.', sb()->time()),
            '(redirect url)'
        );
    }

    protected function _renderContent()
    {
        // Entries must contain the primary key column, which
        // does not need to have a matching column in the grid.
        $entries = array(
            array(
                'id' => 42, 
                'name' => 'Max',
                'lastname' => 'Mustermann'
            )
        );

        $this->renderer
            ->setTitle(t('List of users'))
            ->appendDataGrid($this->grid, $entries)
            ->makeWithoutSidebar();
    }
}
```

### Handling pagination

#### Introduction

By default, a grid will display all items passed on to it. The pagination controls have to be 
expressly activated, and the actual pagination mechanism handled - either manually, or automatically
by using a FilterCriteria instance. 

  > NOTE: The grid does not double-check that the amount of items
    per page matches the amount of items given to it. You have to
    ensure the offset and limit are used correctly when fetching
    the matching items.

#### Custom pagination example

This example illustrates how to handle the offset and limit manually, given a list of items.

```php
$grid = $this->ui->createDataGrid('grid_name');
$grid->addColumn('name', t('Name'));
$grid->addColumn('lastname', t('Last name'));

$grid->enableLimitOptionsDefault();

// The total amount of items in the grid
$total = 45;

$grid->setTotal($total);

// The current offset and limit selected in the grid
$offset = $grid->getOffset();
$limit = $grid->getLimit();

$entries = array();

// Generate dummy entries matching the selected range
for($i=$offset; $i <= $offset + ($limit-1); $i++)
{
    $entries[] = array(
        'name' => 'Otto '.($i+1),
        'lastname' => 'Mustermann'
    );
}

echo $grid->render($entries);
```

#### Example with filter criteria

The filter criteria are made to work in tandem with data grids, to automate the fetching
of records from the database according to the grid's offset and limit.

```php
$grid = $this->ui->createDataGrid('countries_list');
$grid->addColumn('iso', t('ISO code'));
$grid->addColumn('label', t('Name'));

$grid->enableLimitOptionsDefault();

// Get a filter criteria instance
$filters = Application_Countries::getInstance()->getFilterCriteria();

// Let the filters configure the grid (and vice versa)
$filters->configure($grid);

// Fetch the items: This is automatically limited using the grid's
// current offset and items per page limit.
$items = $filters->getItemsObjects();

// Build the entries
$entries = array();
foreach($items as $item)
{
    $entries[] = array(
        'iso' => $item->getISO(),
        'label' => $item->getLocalizedLabel()
    );
}

echo $grid->render($entries);
```

### Enabling compact mode

In some cases, a more compact table layout may be needed. This can easily be enabled with the
`enableCompactMode()` method, which will cause all cells to use less padding to reduce the 
overall size of the grid.

This can be further combined with turning off the footer line if it is not needed.

```php
$ui = UI::getInstance();

$grid = $ui->createDataGrid('grid_name');
$grid->addColumn('name', t('Name'));
$grid->addColumn('lastname', t('Last name'));

$grid->enableCompactMode();
$grid->disableFooter();
```

To make the grid even easier to integrate into specific layout cases, its margins can be 
turned off (it will have no top or bottom margins anymore):

```php
$ui = UI::getInstance();

$grid = $ui->createDataGrid('grid_name');
$grid->addColumn('name', t('Name'));
$grid->addColumn('lastname', t('Last name'));

$grid->disableMargins();
```

## Icons

Icons can be easily inserted using the static `UI::icon()` method. The IDE should then show
all available icon styles. Icons extend the renderable interface, so they can be cast to
string, and have the `render()` and `display()` methods.

Examples:

```php
// Fetching the rendered icon HTML
UI::icon()->delete()->render();

// Echoing the icon
UI::icon()->delete()->display();

// Changing the visual style to "Dangerous" (usually red)
UI::icon()->delete()->makeDangerous();

// Adding a tooltip
UI::icon()->delete()
    ->makeDangerous()
    ->setTooltip(t('This will delete the record.'));
```

### Icons reference sheet

In the maileditor UI, open the developer menu. There is a menu entry called "Icons reference sheet",
which shows all icons available for the application.

## Buttons

Buttons can be created using the static `UI::button()` method. Use the IDE to navigate all possible
methods to configure the button. Buttons extend the renderable interface, so they can be cast to
string, and have the `render()` and `display()` methods.

### Choosing a visual style

The `makeXXX()` methods allows choosing a visual style for the button, for example:

```php
// A primary button
UI::button(t('Primary button'))->makePrimary();

// A dangerous action button
UI::button(t('Very dangerous'))->makeDangerous();
```

### Linking to an URL

```php
// Set an URL to link to
UI::button(t('Click me'))->link('https://ionos.com');

// Open in a new tab
UI::button(t('Click me'))->link('https://ionos.com', '_blank');
```

### Choosing a size

The buttons come in several sizes, which come with dedicated methods:

```php
UI::button(t('Large'))->makeLarge();
UI::button(t('Small'))->makeSmall();
UI::button(t('Mini'))->makeMini();
```

## Page sections

Sections allow adding collapsible content blocks with a title bar. Instances can be
created using the UI instance. In an admin screen, this is accessible via `$this->ui`.

The following example creates a section that is initially collapsed:

```php
$this->ui->createSection()
  ->setTitle(t('Section title'))
  ->setAbstract(t('Here you can see things'))
  ->setContent('<p>(Content)</p>')
  ->collapse();
```

### Rendering and displaying

Sections implement the renderable interface, so they can be cast to string, and offer
the `render()` and `display()` methods. When working in an administration screen, the
optimal way is to append the section to the renderer, like this:

```php
$section = $this->ui->createSection()
  ->setTitle(t('Section title'))
  ->setAbstract(t('Here you can see things'))
  ->setContent('<p>(Content)</p>')
  ->collapse();

$this->renderer->appendContent($section);
```

### Collapsible sections and grouping

When several collapsible sections are displayed in a page, controls are automatically
shown to expand or collapse them all. It is possible to create section groups which
can be collapsed and expanded separately, by setting a group name.

```php
$this->ui->createSection()
  ->setTitle(t('Section title'))
  ->setContent('<p>(Content)</p>')
  ->setGroup('group1')
  ->collapse();
```

# Accessing request data

## The request class

To access any variables from the current request, be they POST or GET, you may use the
request class, which is available anywhere via `Application_Request::getInstance()`.
It allows validating the request variable before getting it. 

The request class is based on the request class included in the [Application Utils][] 
GitHub package.

## Fetching values

The simplest way to fetch a request variable is to use the `getParam()` method.

```php
$request = Application_Request::getInstance();

$value = $request->getParam('name');
```

However, this does not include any validation at all. It is recommended to always
filter the values using the available validation methods.

### Boolean values

Treated as boolean values are: `yes`, `no`, `true`, `false`, `1`, `0`.

```php
$request = Application_Request::getInstance();

if($request->getBool('boolean_variable')) {
    // Is true
}
```

  > NOTE: If the value is not a boolean, it will return `false` by default.

### Integers

Fetching an integer. Note that the cast to int is necessary even though
the `get()` method already ensures that an int is returned, to keep the
PHP code analysis tools happy.

```php
$request = Application_Request::getInstance();

$value = (int)$request
  ->registerParam('integer_var')
  ->setInteger()
  ->get();
```

### Callback

The callback validation allows a method or function to be used to validate
the request value, if at all present. The callback gets the value as first
parameter, as well as any additional (optional) arguments that may have been
specified.

```php
$request = Application_Request::getInstance();

$value = (string)$request
    ->registerParam('variable')
    ->setCallback('callback_function', array('optionalArgument'))
    ->get();

/**
 * @param mixed $value
 * @param string $optional Optional parameter to the callback
 * @return bool
 */
function callback_function($value, string $optional) : bool
{
    return strval($value);
}
```

This callback function simply returns the value converted to string. 

### Multiple choice

Fetching a single value from a variable that allows a list of values: will
return a value if it is present in the specified list of values.

```php
$request = Application_Request::getInstance();

$value = (string)$request
  ->registerParam('variable')
  ->setEnum('value1', 'value2', 'value3')
  ->get();
```

This will allow the `variable` parameter to be set to any of the three 
specified values.

It is also possible to specify the list of values as an array:

```php
$request = Application_Request::getInstance();

$value = (string)$request
  ->registerParam('variable')
  ->setEnum(array('value1', 'value2', 'value3'))
  ->get();
```

### Regex check

This allows specifying a regular expression to check the value against.
This for example, expects an uppercase string with 4 letters.

  > NOTE: You will typically want to add the beginning `\A` and end anchors `\z`
    to match the whole value.

```php
$request = Application_Request::getInstance();

$value = (string)$request
  ->registerParam('variable')
  ->setRegex('/\A[A-Z]{4}\z/')
  ->get();
```

### Comma-separated IDs

This allows a list of IDs to be specified as a comma-separated string,
like for example `45,14,8,147`. The request automatically parses this
and returns an array of integers.

```php
$request = Application_Request::getInstance();

$ids = (array)$request
    ->registerParam('ids')
    ->setIDList()
    ->get();
```

  > NOTE: Whitespace is automatically stripped, so spaces after the commas
    are allowed.

### Additional validations

This list is not exhaustive - there are more validation methods that you can
see with the IDE when registering a parameter.

# Data collections

## Countries

### Introduction

Collection class: `Application_Countries`  
Factory method: `Application_Countries::getInstance()`  

The countries management handles all available countries for the application.
It offers a number of methods around accessing countries, and make creating
country selection use cases easy to handle.

### Fetching countries

```php
$collection = Application_Countries::getInstance();

// Getting all countries 
$collection->getAll();
```

### The countries selector

The `injectCountrySelector` can be used to add a country selection element 
to a `UI_Form` instance. This means it can be used with a traditional form
instance as well as a Formable.

Inject from within a formable:

```php
class Documentation_CountrySelector extends Application_Formable
{
    public function inject_countries() : void
    {
        $selectElement = Application_Countries::getInstance()->injectCountrySelector(
            $this->getFormInstance(),
            'countries', // field name
            t('Countries'), // field label
            true, // required?
            true, // Add the "Please select" entry?
            false // Include the invariant country?
        );
    }
}
```

### The invariant country

#### Introduction

The invariant country can be used in cases where a country must be selected,
but the data can be valid for all countries. Its details are always the same:

- ID: `9999` - See `Application_Countries_Country::COUNTRY_INDEPENDENT_ID`
- ISO: `zz` - See `Application_Countries_Country::COUNTRY_INDEPENDENT_ISO`

#### Fetching the country

```php
Application_Countries::getInstance()->getInvariantCountry();
```

#### Excluding from results

By default, the invariant country is included in all results, but can be 
excluded manually.

When using the `getAll()` method:

```php
$collection = Application_Countries::getInstance();
$countries = $collection->getAll(false);
```

When using the filter criteria:

```php
$criteria = Application_Countries::getInstance()->getFilterCriteria();
$criteria->excludeInvariant();
```

## Data handling classes

### Array-based records

The base record class is a counterpart to the DBHelper's record. Here the target
data can be loaded from an arbitrary source, and the getter methods make working
with the data array easier.

The class offers a number of type hinted methods to access keys in the data array,
like for example:

- `getDataKeyInt()`
- `getDataKeyBool()`
- `getDataKeyArray()`
- `getDataKeyDate()`

These check if the target key is present in the data array, and guarantee returning
the correct data type, to avoid doing this manually.

#### For integer-based primary keys

Extend the class `Application_Collection_BaseRecord_IntegerPrimary`.

This is the base skeleton for a record class:

```php
<?php

declare(strict_types=1);

class ExampleIntegerBaseRecord extends Application_Collection_BaseRecord_IntegerPrimary
{
    /**
     * A label for the type of record, e.g. "Product", which
     * is used in log messages to identify the record.
     *
     * @return string
     */
    protected function getRecordTypeLabel(): string
    {
        return 'Example record';
    }
    
    /**
     * The name of the record's primary key in the data set,
     * e.g. "product_id". Ensures that its value cannot be
     * overwritten or set.
     *
     * @return string
     */
    protected function getRecordPrimaryName(): string
    {
        return 'primaryKey';
    }

    /**
     * Fetches the record's data set as an associative array. 
     * @return array<string,mixed>
     */
    protected function loadData(): array
    {
        // Implement the logic to fetch the data here
        return array();
    }

    /**
     * Called at the end of the constructor, after the data has been loaded. 
     */
    protected function init(): void
    {
    }
}
```

# Forms

## Introduction

The application framework uses the package `HTML_QuickForm2` to handle forms. This in turn
is wrapped in what is called a _Formable_, a utility class that handles all the application's
specifics around the forms package.

  > Note: The fork https://github.com/Mistralys/HTML_QuickForm2 is used in the framework, as it 
    has a number of improvements regarding performance when using many forms in parallel, as well 
    as several quality of life improvements.

No layouting work needs to be done: all forms are standardized for a streamlined user 
experience. The framework offers methods for all relevant aspects:

- The layout.
- All commonly used data types.
- Custom data types can be added in the application itself.
- Inline documentation.  
- Input validation.
 
## Formables

The framework has a "Formable" interface and base class, which bring together all aspects of
form building. This includes methods for creating input elements, as well as for adding 
validation. This system has the `HTML_QuickForm2` at its core, but simplified for use in the
framework.

Administration screens implement the formable interface, so they can be used to handle a 
single form. If this is not sufficient, any number of forms can be created on the fly.
All examples here in the documentation assume that you are working with a single form in
an admin screen.

## Typical admin screen form

The following example shows the typical structure for handling a form in a screen:

```php
class Documentation_Form_TypicalStructure extends Application_Admin_Area_Mode
{
    protected function _handleActions()
    {
        // First off, create the form instance.
        $this->createSettingsForm();
        
        // The form is considered valid if it has been submitted,
        // and all elements passed validation.
        if($this->isFormValid())
        {
            $this->handleFormSubmitted($this->getFormValues());
        }
    }
    
    protected function _renderContent()
    {
        // Append the form to the renderer: this will use
        // the form as content of the page.
        return $this->renderer
            ->setTitle(t('Example form'))
            ->appendFormable($this)
            ->makeWithSidebar();
    }
    
    protected function _handleSidebar()
    {
        // A clickable submit button is configured to 
        // submit the formable when it is, well, clicked.
        $this->sidebar->addButton('save', t('Submit now'))
        ->setIcon(UI::icon()->save())
        ->makeClickableSubmit($this);
        
        $this->sidebar->addButton('cancel', t('Cancel'))
        ->makeLinked('(relevant cancel url)');
    }
    
    /**
     * Called when the form has been submitted and is valid.
     * @param array<string,string> $formValues
     */
    protected function handleFormSubmitted(array $formValues) : void
    {
        // do something with the values
        
        // Redirect to a relevant page after the operation, 
        // with a success message.
        $this->redirectWithSuccessMessage(
            t('The form was submitted successfully.'),
            '(redirect url)'
        );
    }
    
    /**
     * Creates and configures the form. 
     */
    protected function createSettingsForm() : void
    {
        $defaultValues = array( 
            'name' => ''
        );
        
        $this->createFormableForm('form_name', $defaultValues);
        
        $el = $this->addElementText('name', t('Name'));
        $el->addFilterTrim();
    }
}
```

## Configuring validation

The Quickform package works with "rules": A form element can have any number of validation
rules attached to it, which are verified in the order they are added to the element. If 
all rules return true, the element is considered valid.

### Adding rules

The framework comes with a range of predefined validation methods, which can be freely 
combined to get the required result.

```php
class Documentation_Form_AddingRules extends Application_Admin_Area_Mode
{
    protected function createSettingsForm() : void
    {
        $this->createFormableForm('form_name');
        
        $el = $this->addElementText('name', t('Name'));
        
        $this->addRuleLabel($el); // Only allow characters for item labels
        $this->makeLengthLimited($el, 0, 80); // Limit length to a maximum of 80 characters
    }
}
```

### Rules and empty elements

Rules must always return true if the element's value is empty: the required rule is 
responsible for validating this case. Empty non-required elements must be considered
valid.

### Available validation rules

The following format-specific methods are available for formables:

- `addRuleNameOrTitle()` - Alphanumerical, with a limited set of punctuation allowed. 
- `addRuleAlias()` - Element alias; lowercase alphanumerical without spaces.
- `addRuleEmail()` - Validate an email address.
- `addRuleFilename()` - Rule for specifying a file name, with common restrictions.
- `addRuleFloat()` - Number with dot or comma for decimals.
- `addRuleInteger()` - Integer number.
- `addRuleISODate()` - Date in the format 2021-02-25.
- `addRuleNoHTML()` - Disallow the use of HTML tags.
- `addRulePhone()` - For entering phone numbers.

Custom validation methods:

- `addRuleCallback()` - Use a callback function/method for validation.
- `addRuleRegex()` - Validate using a regular expression.

## Marking elements as structural

This is typically used in conjunction with data types that have states, like mailings:
It means that changing the value will change the state of the record. These elements
receive a visual distinction in the UI to identify them as structural.

```php
class Documentation_Form_StructuralElement extends Application_Admin_Area_Mode
{
    protected function createSettingsForm() : void
    {
        $this->createFormableForm('form_name');
        
        $el = $this->addElementText('name', t('Name'));
        
        $this->makeStructural($el);
    }
}
```

## Adding inline documentation

As shown in the "Configuring validation" chapter, validation rules automatically add
documentation on the expected input format. In addition to this, every form element
can be described further with the `setComments()` method.

It is recommended to always add background information on what the form element's data
is used for, if this is not entirely obvious. Examples are also useful if applicable,
so that new users do not have to guess what's expected.

```php
class Documentation_Form_InlineDocumentation extends Application_Admin_Area_Mode
{
    protected function createSettingsForm() : void
    {
        $this->createFormableForm('form_name');
        
        $el = $this->addElementText('name', t('Name'));
        $el->setComment((string)sb()
            ->t('With the string builder, it is easy to add detailed information.')
            ->nl()
            ->icon(UI::icon()->information()->makeInformation())
            ->info(t('Some important tips here.'))
        );
    }
}
```

## Element size classes

To give elements a specific size, you may use the element's `addClass()` method
with one of the available size classes:

- `input-mini`
- `input-small`
- `input-medium`
- `input-large`
- `input-xlarge`
- `input-xxlarge`

## Marking elements as required

Marking elements as required is done via the formable's `makeRequired()` method.

```php
class Documentation_Form_RequiredElements extends Application_Admin_Area_Mode
{
    protected function createSettingsForm() : void
    {
        $this->createFormableForm('form_name');
        
        $el = $this->addElementText('name', t('Name'));
        
        $this->makeRequired($el);
    }
}
```

## Form elements

### Text input

```php
class Documentation_Form_Element_TextInput extends Application_Admin_Area_Mode
{
    protected function createSettingsForm() : void
    {
        $this->createFormableForm('form_name');
        
        $el = $this->addElementText('name', t('Name'));
        $el->addClass('input-xxlarge');
        $el->addFilterTrim();
        $el->setComment(t('Some comments here.'));
    }
}
```

### Select

This adds a traditional select element, with or without grouping.

```php
class Documentation_Form_Element_Select extends Application_Admin_Area_Mode
{
    protected function createSettingsForm() : void
    {
        $this->createFormableForm('form_name');
        
        // Add a simple flat select without option groups
        $ungrouped = $this->addElementSelect('name', t('Name'));
        $ungrouped->addOption(t('Please select...'), '');
        $ungrouped->addOption(t('Option 1'), '1');
        $ungrouped->addOption(t('Option 2'), '2');
        
        // Add a select with option groups
        $grouped = $this->addElementSelect('name', t('Name'));
        $grouped->addOption(t('Please select...'), '');
        
        $groupA = $grouped->addOptgroup(t('Group A'));
        $groupA->addOption(t('Option 1'), '1');
        $groupA->addOption(t('Option 2'), '2');

        $groupB = $grouped->addOptgroup(t('Group B'));
        $groupB->addOption(t('Option 3'), '3');
        $groupB->addOption(t('Option 4'), '4');
    }
}
```

# Filter criteria

The filter criteria classes are used to fetch records according to a set
of criteria, like an extended search.

## Flavor: Database

### Adding JOIN statements

#### Adding a mandatory JOIN

```php
class Documentation_FilterCriteria extends Application_FilterCriteria_Database
{
    const JOIN_TARGET = 'join_target';

    protected function _registerJoins() : void
    {
        // If there are no JOIN statements that depend
        // on this one, it can be added directly.
        $this->addJoinStatement(
            "JOIN
                {table_target} AS {target}
            ON
                {target}.{primary_name}={source}.{primary_name}"
        );
        
        // If there are dependent JOINs, it must be
        // registered instead:
        $this->registerJoinStatement(
            self::JOIN_TARGET,
            "JOIN
                {table_target} AS {target}
            ON
                {target}.{primary_name}={source}.{primary_name}"
        );
        
        // To mark it as mandatory, require it by default.
        $this->requireJoin(self::JOIN_TARGET);
    }
}
```

# Debugging and Logging

## Logging

### The logger class

The application's logger class is the central hub to access the logging system:

```php
$logger = Application::getLogger();
```

#### Log modes

The log modes define what becomes of the log messages. By default, they are stored
in memory, and discarded at the end of the request. The log is saved to disk only if
an exception occurrs, in which case it is stored along with the exception details to
view it in the error log.

The log mode can be changed on the fly:

```php
$logger = Application::getLogger();

// Direct all log messages to the logs/trace.log file
$logger->logModeFile();

// Echo all log messages to standard output
$logger->logModeEcho();

// Do not store any log messages at all
$logger->logModeNone();
```

### Displaying the log in the UI

When in UI mode, appending the parameter `&simulate_only=yes` will cause all
log messages to be sent to the browser immediately. Alternatively, the log can 
be printed in one block with the following call:

```php
// Print the log as plain text
Application::getLogger()->printLog();

// Print the log with HTML styling enabled
Application::getLogger()->printLog(true);
```

### The loggable interface and trait

Any class can implement the loggable interface, and use the corresponding trait
to avoid having to implement all the methods:

```php
class Documentation_LoggableExample implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;
    
    public function getLogIdentifier() : string
    {
        return 'Unique Identifier';
    }
    
    public function addSomeLogs() : void
    {
        // Adding a simple log message
        $this->log('Regular log message');
        
        // Logging data sets
        $this->logData(array('data' => 'value'));
        
        // Visual header to separate major log sections
        $this->logHeader('Header title');
        
        // Marking a log message as being related to an event
        $this->logEvent('EventName', 'Message');
        
        // Add a log message marked as an error
        $this->logError('An error message');
    }
}
```

  > NOTE: The log identifier will be used as prefix for all log messages.

## Debugging

The primary way to debug in the framework is logging: Log messages can be used to 
track important events and changes, and can stay in the code indefinitely. Since 
exceptions retain the log messages up to the error, they are a valuable source of 
information.

# Global utility methods & functions

## Global functions

- `compileAttributes()` - Renders an associative array to an HTML attributes string.
- `compileStyles()` - Renders an associative array of CSS styles to a style string.
- `displayException()` - Can display a pretty error screen given an exception.
- `ensureType()` - PHPStan-friendly method to check an object type.
- `getClassTypeName()` - Returns the type name given a class name.
- `getUI()` - Returns the active `UI` instance.
- `imageURL()` - Get the URL to an image stored in the application's themes folder.
- `isDevelMode()` - Whether developer mode is enabled.
- `isOSWindows()` - Whether the current OS is Windows.  
- `nextJSID()` - Generate a unique element ID, tied to the user's session.
- `toString()` - Convert a variable or renderable to string.
- `var_dump_get()` - Fetch the output of a `var_dump()` call.

## The Application Utils

Originally included in the framework, this is a collection of utility classes that
have been moved to a separate open source GitHub package: https://github.com/Mistralys/application-utils.

Here's a short overview of some of the most commonly used tools:

### Converting booleans

In the framework, booleans also include the strings `yes` and `no`. To simplify
working with these values, they can be easily converted.

```php
// Convert a string to a boolean value
$bool = \AppUtils\ConvertHelper::string2bool('yes');

// Converting a boolean value to string
$string = \AppUtils\ConvertHelper::bool2string(true);
```

### Searching for files

Example: Finding all `txt` files, including in subfolders, and get them with
their relative paths from the source folder.

```php
$textFiles = \AppUtils\FileHelper::createFileFinder('/path/to/folder')
->includeExtension('txt')
->makeRecursive()
->setPathmodeRelative()
->getAll();
```

### Parsing URLs

The `parseURL()` method can parse URLs to easily access information on the URL.
It fixes some issues with parsing query strings that are inherent to the some
of the native PHP functions, so it is recommended to use this anytime you need
to work with URLs.

```php
$url = 'https://domain.extension/path/?param=value';

$info = \AppUtils\parseURL($url);
$host = $info->getHost();
$highlighted = $info->getHighlighted();
```

### Operation results tracking

The `OperationResult` and `OprationResult_Collection` classes can be used to keep track 
of what happens during the processing of any kind of operation, to allow the process that
started a task to check if the operation completed successfully, and to access information
on errors that occurred.

It is meant to be used for errors and warnings that are not critical enough for throwing 
an exception.

- `OperationResult` - Made to hold a single result message.
- `OperationResult_Collection` - Made to hold several result messages.

#### Single result example

Instead of returning true or false, a method returns an operation result instance. This 
allows specifying a human readable message when errors occur, as well as a machine readable 
error code.

```php
use \AppUtils\OperationResult;

class Documentation_OperationResult
{
    public const ERROR_COULD_NOT_SAVE_FILE = 0000;

    public function doSomething() : OperationResult
    {
        $result = new OperationResult($this);
        
        if(!file_put_contents('/path/to/file.txt', 'Content')) 
        {
            return $result->makeError(
                t('The content could not be saved to file %1$s.', 'file.txt'),
                self::ERROR_COULD_NOT_SAVE_FILE
            );
        } 
        
        return $result;
    }
}

$instance = new Documentation_OperationResult();

$result = $instance->doSomething();

// The isValid methods checks if there are any error messages present.
if(!$result->isValid())
{
    die(t('An error occurred:').' '.$result->getErrorMessage());
}
```

#### Multiple results

The `OperationResult_Collection` works exactly like the `OperationResult` class, except 
that it can store multiple messages. Each call to `makeError()` adds an error message to
the collection. This is very handy when a process works through several items, to keep 
track of items that failed without stopping the whole process.

## The StringBuilder

The make working with blocks of texts easier, it is possible to use the `UI_StringBuilder`
class, via the global `sb()` function. This has a number of utility methods to translate
and format texts, as well as canned messages.

The string builder extends the renderable interface, so it can be cast to string, and it
offers the methods `render()` and `display()`. Many of the framework methods accept these
natively.

### The principle

All methods of the string builder are chainable, and by default a space is added between
all bits of text that are added. This makes it possible to append them for a natural text
flow, avoiding tedious string concatenation via `$text .= ' '.t('Text here')`.

Consider doing this:

```php
echo 
    UI::icon()->information().' '.
    t('First sentence').' '.
    t('Second sentence').' '.
    '<strong>'.t('Some bold text').'</strong>';
```

Using the string builder, this becomes:

```php
sb()
    ->icon(UI::icon()->information())
    ->t('First sentence.')
    ->t('Second sentence.')
    ->bold(t('Some bold text'))
    ->display();
```

### Methods overview

- `add()` - Adds a freeform bit of text or HTML.
- `t()` - Appends a translated text. Works like the `t()` function.
- `sf()` - Appends a bit of text formatted using `sprintf()`.
- `bold()` - Appends a bold text.
- `mono()` - Appends a text styled with a monospace font.
- `code()` - Appends a text styled as an inline bit of code.
- `nl()` - Appends a newline.
- `para()` - Appends a double newline.
- `nospace()` - Appends a bit of text, without automatic space after it.
- `html()` - Appends some HTML code, without automatic space after it.

*Layout elements*

- `icon()` - Appends an icon.
- `button()` - Append a button.
- `link()` - Appends a linked text.
- `linkRight()` - Appends a text that is linked only if the user has the specified right.
- `ol()` - Appends an ordered list.
- `ul()` - Appends an unordered list.

*Visual styling*

- `danger()` - Append a text visually styled as dangerous.
- `info()` - Append a text visually styled as informational.
- `warning()` - Append a text visually styled as warning.
- `muted()` - Append a text visually styled as muted/grayed out text.

*Canned messages*

- `hint()` - Appends the text "Hint:".
- `note()` - Appends the text "Note:".
- `noteBold()` - Appends the text "Note:" as bold text.
- `time()` - Appends the current time.
- `cannotBeUndone()` - Appends the canned "This cannot be undone, are you sure?" text.

# Querying the database

## Prerequisites

The database methods in the framework are transaction based, so it is recommended
to work with INNODB tables, with relations to handle deletions and changes automatically.

## The DBHelper

The main hub for running queries is the DBHelper static class, which offers a range
of static methods for most use cases. It is configured automatically for the database
defined in the application's configuration files.

  > NOTE: For backwards compatibility, the DBHelper still offers methods that have been
    superseded by newer, more efficient alternatives. This documentation will focus on
    the newer implementations.

## Examples

### Fetching all rows

To fetch rows from a table, use the "fetch many" helper, which can be 
configured with any number of conditions to filter the results.

```php
$records = DBHelper::createFetchMany('table_name')
->whereValue('column1', 'value')
->whereNotNull('column2')
->orderBy('column1', 'DESC')
->fetch();
```

### Fetching values from a single column

Selecting many allows fetching all values from a 
single column in the table:

```php
$names = DBHelper::createFetchMany('table_name')
->orderBy('name')
->groupBy('name')
->fetchColumn('name');
```

For integer values, there is a specific method:

```php
$ids = DBHelper::createFetchMany('table_name')
->groupBy('record_id')
->fetchColumnInt('record_id');
```

### Fetching a single record

```php
$record = DBHelper::createFetchOne('table_name')
->selectColumns('firstname', 'lastname', 'email')
->whereValue('record_id', 1)
->fetch();
```

### Fetching a single column from a record

```php
$firstname = DBHelper::createFetchKey('firstname', 'table_name')
->whereValue('record_id', 1)
->fetchString();
```

This also has type flavored methods to avoid type casting:

```php
$amountLogins = DBHelper::createFetchKey('amount_logins', 'table_name')
->whereValue('record_id', 1)
->fetchInt();
```

### Inserting records

```php
DBHelper::insertDynamic(
    'table_name',
    array(
        'firstname' => 'Max',
        'lastname' => 'Mustermann'
    )
);
```

To run an automatic check if a record already exists, and turn the
insert into an update, use the `insertOrUpdate` method:

```php
DBHelper::insertOrUpdate(
    'table_name',
    array(
        'firstname' => 'Max',
        'lastname' => 'Mustermann',
        'email' => 'max@mustermann.void'
    ),
    array(
        'firstname',
        'lastname'
    )
);
```

This will check if the combination of `firstname` + `lastname` already
exists, and if it does, executes an `UDPATE` statement instead.

### Deleting records

Records can be deleted simply by specifying the column values to use to
identify the records to delete.

```php
DBHelper::deleteRecords(
    'table_name',
    array(
        'record_id' => 42
    )
);
```

### Transactions

Running statements should always be wrapped in a transaction, to allow rolling the
operation back if something goes wrong:

```php
DBHelper::startTransaction();

// Run some database operations

DBHelper::commitTransaction();
```

An exception will be thrown if an exception has already been started, or if none
is active when calling `commitTransaction()`. This can be avoided by checking first
with `DBHelper::isTransactionStarted()`. There is a method that does this automatically
for you, to keep the code clean:

```php
DBHelper::startConditional();

// Run some database operations

DBHelper::commitTransaction();
```

Rolling back a transaction:

```php
DBHelper::rollbackTransaction();
```

### Debugging

It is not always easy to see the final SQL used in queries, since the values are
injected at runtime using placeholders to avoid SQL injection. The debug mode
comes in handy here, as it can display simulated queries and data sets.

```php
DBHelper::enableDebugging();

// All queries after this call are echoed to standard output.

DBHelper::disableDebugging();
```

### Utility methods

Checking if a table exists in the database:

```php
if(DBHelper::tableExists('table_name'))
{
    // Do something.
}
```

Checking if a column exists in a table:

```php
if(DBHelper::columnExists('table_name', 'column_name'))
{
    // Do something.
}
```

Dropping all tables in the database:

```php
DBHelper::dropTables();
```

...to use with extreme caution, of course.

# Connectors for external services

## Introduction

Connectors are a set of classes used to connect to external services: they offer a 
flexible base feature set to connect using a range of service types.

## Structure

For each service to connect to, a connector class should be added in the application's
`Connectors` folder:

`assets/classes/Connectors/ServiceName.php`

Individual service methods can then be added here:

`assets/classes/Connectors/ServiceName/Method/XXX.php`  

### Method classes

The connector has abstract classes for the following methods:

- DELETE - `Connectors_Connector_Method_Delete`
- GET - `Connectors_Connector_Method_Get`
- POST - `Connectors_Connector_Method_Post`
- PUT - `Connectors_Connector_Method_Put`

These can be extended to communicate with services based on HTTP methods.

  > NOTE: All these expect the data format to be JSON. See "Custom methods"
    below to see how to handle other cases.       

### Custom methods

If the method classes do not fit your use case, create your methods from the base class,
`Connectors_Connector_Method`, and add your own implementation. If needed, it is even
possible to use third party packages to handle the actual communication.

### Exceptions

It is recommended to create an exception class for each service, so these exceptions
can easily be identified. This should be added in the service's folder, i.e.:

```
assets/classes/Connectors/ServiceName/Exception.php
```

The class must extend the base connector exception, `Connectors_Exception`.

## Adding a service method

The simplified steps for adding an API method to a connector can be the following:

1) Add a matching method in the connector class, e.g. `getProducts()`.
2) Define the required parameters for the method.
3) Create the method class, e.g. `Connectors_ServiceName_Method_GetProducts`.
4) Add the method `getProducts()` in the method class with the parameters.
4) Instantiate the method in `getProducts()`.
5) Call `getProducts()` on the method instance, passing the parameters.
6) Implement the connection in the method class
7) Return the data

### Creating classes for data types

To keep things easy to understand, it is recommended to create a separate data storage
class for each data type fetched via the connector methods. For example, if retrieving
data for products, the `getProducts()` method should ideally return an array with 
product instances (for example of type `Connectors_ServiceName_Product`), with the 
relevant getter methods for maximum transparency.

This makes it easier down the line to see what data is available, and also to allow for
future adjustments to the data sets.

Also, the product class from this example is liable to be used in other methods of the 
remote service. If there is a method to add a new product, it will be handy to be able
to give the method class a product instance, which already has all the information the
API method needs.

### Example connector class and method

To illustrate the examples mentioned above with a remote service handling products, 
here is some PHP code to go with it, with the following classes:

- `Connectors_Connector_Products` - The remote service connector.
- `Connectors_Connector_Products_Method_GetProducts` - The method class for the GET products endpoint.
- `Connectors_Connector_Products_DataType_Product` - The data type container for a single product.

```php
/**
 * The connector class that is used to connected to the "Products" remote service. 
 */
class Connectors_Connector_Products extends Connectors_Connector
{
    public function getURL() : string
    {
        return 'https://products.service/api/';
    }
    
    public function checkRequirements() : void
    {
        // check if all requirements to connect to the remote service
        // are met; throw an exception otherwise.
    }
    
    /**
     * Fetches all products from the API, for the specified country.
     * 
     * @param string $countryCode
     * @return Connectors_Connector_Products_DataType_Product[]
     */
    public function getProducts(string $countryCode) : array
    {
        $this->createGetProducts()->getProducts($countryCode);
    }
    
    /**
     * Create the method instance to get the products. 
     * @return Connectors_Connector_Products_Method_GetProducts
     */
    private function createGetProducts() : Connectors_Connector_Products_Method_GetProducts
    {
        return new Connectors_Connector_Products_Method_GetProducts($this);
    }
}

/**
 * The connector method class, which handles the actual communication with
 * the remote API, sending the request and processing the result.
 */
class Connectors_Connector_Products_Method_GetProducts extends Connectors_Connector_Method_Get
{
    public const ERROR_COULD_NOT_FETCH_PRODUCTS = 80601;

    /**
     * @param string $countryCode
     * @return Connectors_Connector_Products_DataType_Product[]
     */
    public function getProducts(string $countryCode) : array
    {
        // 'products' is the API endpoint to call: this is appended
        // to the connector's service URL.
        $request = $this->createRequest('products');
        
        // Add some GET data. The same can be done for POST data
        // as needed.
        $request->setGETData(array('country' => $countryCode));
        
        // The request automatically decodes the JSON, so a successful
        // request directly returns the actual data set.
        $response = $this->executeRequest($request);
        
        // Check if the response had errors 
        if($response->isError())
        {
            $response->throwException(
                'Could not fetch products.',
                self::ERROR_COULD_NOT_FETCH_PRODUCTS
            );
        }
        
        return $this->processResponse($response);
    }
    
    /**
     * Processes the response when it is valid,, and returns the
     * available products.
     * 
     * @param Connectors_Response $response
     * @return Connectors_Connector_Products_DataType_Product[]
     */
    private function processResponse(Connectors_Response $response) : array
    {
        $data = $response->getData();
    
        $result = array();
        foreach($data as $productData)
        {
            $result[] = new Connectors_Connector_Products_DataType_Product(
                intval($productData['productID']),
                strval($productData['name'])
            );
        }
        
        return $result;
    }
}

/**
 * The "Product" data type used to make the exchange of data
 * more transparent for all sides that handle product data. 
 */
class Connectors_Connector_Products_DataType_Product
{
   /**
    * @var int 
    */
    private $id;
    
    /**
     * @var string 
     */
    private $label;
    
    public function __construct(int $id, string $label)
    {
        $this->id = $id;
        $this->label = $label;
    }
    
    public function getID() : int
    {
        return $this->id;
    }
    
    public function getLabel() : string
    {
        return $this->label;
    }
}
```

# Localization

The localization is handled by the [Application Localization] package.

## The principle

All texts are written in native english in the code, using one of the available
translation methods. The localization package then allows translating these using
a separate UI.

## Translation methods

- `t()` Translate a string and return the localized text.
- `pt()` Same as `t()`, but echo the text (for use in templates).
- `pts()` Same as `pt()`, but adds a space at the end for HTML.

All these methods accept an english string as input, and an arbitrary amount of
parameters which are inserted into the text using the `sprintf` function.

Example with a placeholder:

```php
$text = t('The record was saved at %1$s.', sb()->time());
```

Example in an HTML tag:

```html
<p><?php pt('Paragraph text here') ?></p>
<p>
    <?php
        pts('First sentence');
        pts('Second sentence');
    ?>
</p>
```

## Clientside translation

All javascript files can use the `t()` function. These are treated exactly like the
server side texts, including inserting values with placeholders. A sprintf shim
guarantees that the exact same syntax can be used.

Translations are loaded automatically clientside by the localization system. In
essence, nothing more needs to be done than to enclose english texts in `t()`.

## String concatenation

To concatenate strings, translated strings, icons and the like, the `UI_StringBuilder` class can
be used, which supports method chaining. Many of the framework UI methods accept string builder
instances natively. The shorthand method `sb()` can be used to create a new instance.

For example, displaying an icon with a text:

```php
sb()
    ->icon(UI::icon()->information())
    ->t('This is a translated text.');
```

## Translation UI

### Application texts

Developers can access the translation UI under _Manage > Translation_. This finds all instances
of the translation methods, and allows adding the translations for all locales defined
for the application.

  > NOTE: All translatable texts will be shown, including Composer libraries like the framework
    or [Application Utils][]. Avoid translating those, since they will be overwritten when those
    libraries are updated.

### Framework texts

A translation UI is available for the translatable text within the framework itself. 
Run a `composer install` in a local clone of the framework, then point your browser to the 
`localization` subfolder. 

## Releases and SATIS repository

### Create a development release

A development release is simply linking the composer.lock to a specific commit in the GitHub
package of the application framework. 

1) Push all changes in the framework
2) Update the SATIS repository
3) Run `composer update` in the application
4) Commit the `composer.lock` file
5) Run `composer install` in the local working copies

  > NOTE: This assumes that the framework version is set to `dev-main` in the application's
    `composer.json` file for development purposes.

### Create a production release

A production release is tied to a version tag in GitHub.

01) Run PHPStan checks for coding issues
02) Run the unit tests
03) Add changes to the `changelog.txt` file
04) Enter the matching version number in the `VERSION` file
05) Push all changes in the framework
06) Create a release on GitHub for the version number
07) Update the SATIS repository
08) Change the application to the version number of the release
09) Run `composer update` in the application
10) Commit the composer files
11) Run `composer install` in the local working copies 

### Updating the SATIS repository

The SATIS repository is a private composer packages repository like packagist.org.
It is hosted by Mistralys and is available here:

[Mistralys composer SATIS][]

The access credentials can be found in the application's `auth.json` file.

The updater script to refresh the data for all available packages is here:

[Mistralys SATIS updater][]

## The WHATSNEW.xml and VERSION files

### WHATSNEW.xml

#### Formatting 

It is possible to use Markdown for formatting the texts.

Variables may also be used in texts, to insert application specific data:

- `$appURL` - URL to the application's htdocs folder.

  > NOTE: The available variables are defined in 
    `Application_Whatsnew_Version_Language_Category_Item::renderVariables()`.

#### Inserting images

Images specific to the WHATSNEW.xml file can be stored in the theme's `whatsnew`
subfolder, and can be added with the command:

```
{image:filename.jpg}
```

### VERSION

This contains the application's current version string. It must be generated
automatically from the WHATSNEW.xml file on the production server. In local
development copies, it is done automatically by the [Framework Manager][].


[Framework Manager]: https://github.com/Mistralys/appframework-manager
[Application Utils]: https://github.com/Mistralys/application-utils
[Application Localization]: https://github.com/Mistralys/application-localization
[Mistralys composer SATIS]: https://composer.mistralys-eva.systems
[Mistalys SATIS updater]: https://composer.mistralys-eva.systems/updater.php