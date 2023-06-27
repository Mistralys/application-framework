# The UI helper

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
