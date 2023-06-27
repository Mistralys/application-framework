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
