## Exception Usage

## Exception Code Flow

As a general rule:

- Return success values (including null) as early as possible.
- Throw exceptions at the end of the function.

### Error Code Constants

#### Unique Error Codes Service

All error codes are defined as constants in the relevant exception class.
The integer values are created using a dedicated error code service to ensure
that they are unique across the entire framework and all applications.

The service is available at:

```
https://mistralys.eu/ErrorcodesService?pw={APPLICATION_IDENTIFIER}&counter=appframework-manager
```

Where `{APPLICATION_IDENTIFIER}` is a unique identifier for the application
that has to be requested from Mistralys.

The service returns a single unique integer per request.

> NOTE: The service enforces a rate limit. New codes can be generated after
> a short delay (historically around 1 code per minute).

#### Error Code Format

The code always has the following structure: **Unique number + error number**.

Example: `505001`. 

In this number, `505` is the unique error identifier, and `001` is the error 
number. The error number can be incremented for multiple error codes within 
the same exception class.

The size of the error number portion is always three digits, with leading zeros
if necessary. This gives a maximum of 999 error codes per exception class,
which is sufficient for all practical purposes.

### Exception arguments

Exceptions in the framework all take four parameters:

- user-facing message - Does not contain any technical details.
- developer details - Contains all context needed to debug the issue, ideally with all relevant data included.
- code - Numeric error code for programmatic handling.
- previous exception

The message is designed to be shown to the user, and must not contain and system
information. Developer details are for logging and debugging purposes, and can
contain system information.

Example:

```php
use Application\Exception\ApplicationException;

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
