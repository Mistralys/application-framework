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
