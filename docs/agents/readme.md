# AGENT GUIDES

## Purpose

Canonical coding paradigms and patterns used across this project. Agents and contributors
should follow these rules when implementing interfaces, traits, and classes.

## General Conventions

- Null checks: prefer `isset($this->property)` over strict `null` comparisons.
- Exceptions: return success values first; throw exceptions at the end as the failure path.
- Keep It Left: minimize nesting by returning early from functions.
- Avoid long functions: break down complex logic into smaller private methods.
- Consistent naming: snake_case for DB fields; camelCase for PHP properties/methods.
- Short acronyms like "ID": Always keep uppercase, e.g., `getID()`,  `getUserID()`.
- Always define arrays with the verbose `array()` syntax.

### Exception arguments

Exceptions in the framework all take four parameters:

- message
- developer details
- code
- previous exception

The message is designed to be shown to the user, and must not contain and system
information. Developer details are for logging and debugging purposes, and can
contain system information.

Example:

```php
use Application\ApplicationException;

throw new ApplicationException(
    'User-facing error message.',
    sprintf(
        'Detailed developer message with context: %s',
        $contextInfo
    ),
    1001,
    $previousException
);
```

### Throwing exceptions

In function paths that can fail, return success values first, and throw exceptions
at the end. This keeps the "happy path" less nested and easier to read.

### Working with arrays

Use the `ArrayDataCollection` class for associative arrays when possible. This offers
type-safety and utility methods.

Example:

```php
use AppUtils\ArrayDataCollection;

$data = ArrayDataCollection::create(array(
    'name' => 'John Doe',
    'integer' => 45,
    'booleanString' => 'true', // can also use `yes` and `no`,
    'float' => 3.14,
));

$name = $data->getString('name');
$age = $data->getInt('integer');
$isActive = $data->getBool('booleanString');
$piValue = $data->getFloat('float');
```

### Working with JSON files

Use the `JsonFile` class to read and write JSON files.

```php
use AppUtils\FileHelper\JSONFile;

$jsonFile = JSONFile::factory('/path/to/file.json');

// Load the data from the file into an associative array
$data = $jsonFile->getData();

// Save the data back to the file
$data['newKey'] = 'newValue';

$jsonFile->putData($data);
```

### Translations

- Wrap user-facing strings with `t()` from the `AppLocalize` package.
- Use placeholders for dynamic content with numbered placeholders, e.g. `%1$s`.
- Placeholders in translation functions work exactly like `sprintf`.
- Systematically split sentences into multiple calls to `t()` for maximum reusability of texts.

Example:

```php
function getWelcomeMessage($username) {
    return t('Welcome, %1$s!', $username);
}
```

Providing context for translators:

When there are more than two placeholders in a string, provide context by using the `tex()` function:

```php
function getOrderSummary($orderNumber, $itemCount, $totalPrice) 
{
    return tex(
        'Your order %1$s contains %2$s items totaling %3$s.',
        'Order summary message with 1) order number, 2) item count, and 3) total price.',
        $orderNumber,
        $itemCount,
        $totalPrice
    );
}
```

## Patterns

### Checking instance types

Use the `ClassHelper` static methods to check for class types. This will throw
an exception if the type does not match, and guarantees that static analyzers
like PHPStan can infer the correct type after the check.

```php
<?php
use AppUtils\ClassHelper;

$campaign = ClassHelper::requireObjectInstanceOf(
    CampaignRecord::class,
    $record
);
```

### Creating objects lazily

Use `isset()` for the property to initialize it as needed.

```php
<?php

class CampaignService 
{
    private ?CampaignManager $campaignManager = null;

    public function getCampaignManager() : CampaignManager 
    {
        if (!isset($this->campaignManager)) {
            $this->object = new CampaignManager();
        }
        
        return $this->campaignManager;
    }
}
```
