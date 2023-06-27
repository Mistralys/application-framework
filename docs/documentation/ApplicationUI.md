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

There are two ways to generate the application's main navigation:

1. Automatic mode - simple, few customization options
2. Manual mode - class-based, all customizations available

For an application with a limited amount of main menu items,
option 1 is sufficient, and needs only a minimal amount of work.

### Automatic mode

#### What it does

The menu items available in the main navigation are determined by the application's
`getAdminAreas()` method. All areas listed there are automatically added to the
navigation, in the order listed.

#### Customizing items

Further configuration of the menu item is done in the admin area class itself:

- **Setting a submenu:** Return a menu name in the `getNavigationGroup()` method.
- **Setting an icon:** Simply return the icon in the area's `getNavigationIcon()` method.

### Manual mode

#### What it does

The menu items are added manually in a specialized class. This allows
more detailed dropdown menus for example, with separators and external
links, which are not possible in the simple mode.

The class must be stored here:

```
/htdocs/assets/classes/DriverName/UI/MainNavigation.php
```

> This is used automatically if it exists, instead of the automatic mode.
> To switch back to automatic mode, the class must be either renamed or
> deleted.

#### Class skeleton

```php
declare(strict_types=1);

namespace DriverName\UI;

use UI\Page\Navigation\NavConfigurator;

class MainNavigation extends NavConfigurator
{
    public function configure() : void
    {
        // configure navigation elements
    }
}
```

### User right checks

User rights for the specified areas are checked, so that only the items
the user is authorized for are shown.

> It is important to have screens included in the navigation that have
> no special right restrictions (like the user settings), which can be
> used as fallback if a user does not have rights for any of the available
> areas.

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
    ->makeLinked('https://appname.lan');
}
```

Any combination of conditions can be set for the button to be shown:

```php
protected function _handleSidebar()
{
    $this->sidebar->addButton('create', t('Create product'))
    ->makePrimary()
    ->requireRight(AppName_User::RIGHT_CREATE_PRODUCTS)
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
        ->link('https://appname.lan')
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

1) Products (Area) - `AppName_Area_Products`
2) Product (Mode) - `AppName_Area_Products_View`
3) Settings (Submode) - `AppName_Area_Products_View_Settings`
4) Edit (Action) - `AppName_Area_Products_View_Settings_Edit`

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
        ->requireRight(AppName_User::RIGHT_CREATE_PRODUCTS)
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
