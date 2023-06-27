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
class AppName_Area_Example_DataGrid extends Application_Admin_Area_Mode
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

In the application UI, open the developer menu. There is a menu entry called "Icons reference sheet",
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
UI::button(t('Click me'))->link('https://appname.com');

// Open in a new tab
UI::button(t('Click me'))->link('https://appname.com', '_blank');
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
