# Debugging and Logging

## Logging

### The logger class

The application's logger class is the central hub to access the logging system:

```php
$logger = \Application\AppFactory::createLogger();
```

#### Log modes

The log modes define what becomes of the log messages. By default, they are stored
in memory, and discarded at the end of the request. The log is saved to disk only if
an exception occurrs, in which case it is stored along with the exception details to
view it in the error log.

The log mode can be changed on the fly:

```php
$logger = \Application\AppFactory::createLogger();

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
\Application\AppFactory::createLogger()->printLog();

// Print the log with HTML styling enabled
\Application\AppFactory::createLogger()->printLog(true);
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

### Writing request logs

When the constant `APP_WRITE_LOG` is set to true, the entire application log is
written to disk on every request. This allows debugging things from the very
beginning of a request, including the user's authentication request chain.

The logs are written into the following folder:

```
htdocs/storage/logs/request
```

The logs are then further organized into year, month, day and hour subfolders
to make it easy to browse and find them.

#### Accessing request logs programmatically

These files can be easily accessed
using the dedicated classes.

The following example shows how to get a list of all years for which there are
request logs stored:

```php
$log = \Application\AppFactory::createRequestLog();

$availableYears = $log->getYears();
```

The object-oriented interface makes it easy to progress in the folders.

#### Accessing request logs via UI

If the application has enabled the `htdocs/requestlog.php` dispatcher file,
navigating to it with the browser allows navigating and viewing the log files
in a simple UI.

The UI will only be available if the `APP_WRITE_LOG` is set to `true`, as it
is only meant to be used for debugging.

> NOTE: This dispatcher is not protected. We recommend enabling it only when
> necessary, since the application log can give critical insight into the
> application, and even display privileged information.
