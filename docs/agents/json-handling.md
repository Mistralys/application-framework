# JSON Handling

## Converting variables to JSON

Use the `JSONConverter::var2json` method to convert variables to JSON format.
This throws an exception on failure, which contains useful information. 
This makes it a much better alternative to PHP's built-in `json_encode()` function.

```php
use AppUtils\ConvertHelper\JSONConverter;

$var = [
    'name' => 'John Doe',
    'age' => 30,
    'is_member' => true,
    'preferences' => [
        'colors' => ['red', 'green', 'blue'],
        'notifications' => false
    ]
];

$json = JSONConverter::var2json($var);

// JSON parameters can be passed as the second argument
$jsonPretty = JSONConverter::var2json($var, JSON_PRETTY_PRINT);

// Variant that fails silently and returns an empty string on error
$jsonSilent = JSONConverter::var2jsonSilent($var, JSON_THROW_ON_ERROR);
```

## Converting JSON to Array

Using associative arrays when decoding JSON is the standard in the
Application Framework, and must be preferred over objects.

```php
use AppUtils\ConvertHelper\JSONConverter;

$jsonAssoc = '{"name":"John Doe","age":30,"is_member":true,"preferences":{"colors":["red","green","blue"],"notifications":false}}';

$assoc = JSONConverter::json2array($jsonAssoc);

// Variant that fails silently and returns an empty array on error
$assocSilent = JSONConverter::json2arraySilent($jsonAssoc);
```

## Converting JSON to Mixed

Use the `JSONConverter::json2var` method to convert JSON strings back to PHP variables.

**Note:** Valid JSON can be objects, arrays, or primitive values (numbers, strings, booleans, null).
When decoding primitive JSON values, the method returns the corresponding PHP type directly.

```php
use AppUtils\ConvertHelper\JSONConverter;

// Convert to an object (default behavior)
$object = JSONConverter::json2var('{"name":"John Doe","age":30,"is_member":true}');

// JSON can also be primitive values
$number = JSONConverter::json2var('10'); // Returns integer: 10
$float = JSONConverter::json2var('10.5'); // Returns float: 10.5
$string = JSONConverter::json2var('"hello"'); // Returns string: "hello"
$boolean = JSONConverter::json2var('true'); // Returns boolean: true
$null = JSONConverter::json2var('null'); // Returns NULL
```

