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
