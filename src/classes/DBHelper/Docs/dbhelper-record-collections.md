# Record collections

## Purpose

The DBHelper collection classes have been created to facilitate accessing
records stored in the database. They automate most of the usual boilerplate
code, and almost eliminate the need for manual database queries.

Use them for:

- Loading a list of records from the database.
- Accessing individual record data in an object-oriented way.
- Checking if records exist by their ID.
- Counting the total amount of records.
- Getting records matching complex filter criteria.
- Generating forms to create new records or to edit existing records.
- Generating forms to let users select criteria to filter record lists with.

## Business Logic

In addition to the database layer abstraction, the collection and record 
classes are the ideal location for implementing record-related business
and application logic:

- Getter and setter methods for record properties
- Methods to handle record-specific tasks
- Factory method to access record-related task processing and access

## Integer Primary Keys

Record collections exclusively work with integer-based record IDs.
This is typically implemented in the database with an `auto_increment`
key, but a separate ID table can be configured as well (used if
the collection's `$recordIDTable` is set).

## Instantiating Collections

Collections are instantiated using the static `DBHelper::createCollection()` 
factory method, which accepts a collection class string.

```php
use \TestDriver\TestDBRecords\TestDBCollection;

$collection = DBHelper::createCollection(TestDBCollection::class);
```

## Record Collection Structure

### The Collection

- Patterns: Repository Pattern, Specification Pattern
- Abstract base class: [BaseCollection.php](../BaseCollection.php).
- Interface: [DBHelperCollectionInterface](../BaseCollection/DBHelperCollectionInterface.php)

#### Roles

The collection class has several roles:

1. **Database and collection configuration.** It stores the database
   table name, primary key name, data columns and the like.
2. **Accessing known records.** It has methods to get all records,
   acts as a factory to create records, to check if an ID exists and more.
3. **Factory for modules.** It has methods to create instances of the
   filter criteria and filter settings, and optionally for other related
   utility classes.
4. **Validation of record data.** The columns available in the database
   schema can be described in the collection, including validation rules.
   The collection enforces the use of valid data when creating records.

#### Established conventions

A number of class constants are defined:

- `COLLECTION_ID` (string) - A unique identifier for the collection, e.g. `propducts`.
- `TABLE_NAME` (string) - The main record database table name.
- `PRIMARY_NAME` (string) - The name of the integer primary key column.
- `REQUEST_PRIMARY_NAME` (string) - The name of the record's primary when used in an HTTP request context (typically different for obfuscation reasons).
- `RECORD_TYPE` (string) - A unique identifier for the type of record, e.g. `product`.
- `COL_{NAME}` (string) - All column names from the main record database table. E.g. `COL_PRICE`.

Additional tables and columns are typically also added to the collection
class, because it is the authority regarding the database details. Additional
table constants and columns are named after these principles:

- `TABLE_{IDENTIFIER}` - For example, `TABLE_PRODUCT_PROPERTIES`.
- `COL_{IDENTIFIER}_{NAME}` - For example, `COL_PROPERTIES_NAME`.

### The Record

- Patterns: Active Record Pattern
- Interface: [DBHelperRecordInterface](../Interfaces/DBHelperRecordInterface.php)
- Abstract base class: [BaseRecord.php](../BaseRecord.php)

The record class models a single record and allows accessing and
modifying the record's data. It automatically loads the record's
data from the database by its integer ID.

#### Record hydration

Records act entirely autonomously for accessing their data. The collection
acts as factory (the `getByID()` method), but the record instance is only
given the record ID. The record then hydrates itself by fetching the required
data from the database.

While the root data (all columns in the record's main data) is fetched on
instantiation, any additional data (from additional database tables, for example)
can be fetched on demand. It's up to the record to implement this, however.

### The Filter Criteria 

- Patterns: Query Object Pattern
- Interface: [DBHelperFilterCriteriaInterface.php](../DBHelperFilterCriteriaInterface.php).
- Abstract base class: [BaseFilterCriteria.php](../BaseFilterCriteria.php).

The filter criteria class is used to fetch records from the database
with freely configurable criteria, based on an intelligent query builder
system.

Each record collection has its own filter criteria class, which is used
to implement record-specific filters. For example, a product available in
specific countries will typically have a method to select one or more
countries to filter the list with.

### The Filter Settings

- Patterns: Form Model Pattern
- Interface: [DBHelperFilterSettingsInterface](../DBHelperFilterSettingsInterface.php)
- Abstract base class: [BaseFilterSettings.php](../BaseFilterSettings.php)

The filter settings class is closely tied to the filter criteria:
It creates the form that users select the available filters with.
It is typically used in the sidebar of record list admin screens.

### The Record Settings 

- Patterns: Form Model Pattern
- Interface: __To Be Created__
- Abstract base class: [BaseRecordSettings.php](../BaseRecordSettings.php).

The record settings class is used to manage the input form used to
create or edit DBHelper records. Switching between create and edit
modes depends on whether a record instance is passed to it.

#### Create Mode

When no existing record instance is passed to the record settings
instance, it runs in create mode. This is used in the "Create record"
administration screens to render and manage the form.

The collection class handles the data flow in the background to create
the record using validated form data. The internal data column validation
is also applied here.

#### Edit Mode

When an existing record instance is passed to the record settings 
instance, it runs in edit mode. This is used in the "Record settings" 
admin screens to render and manage the form. The record class handles 
the data flow in the background to update its properties using validated
form data.

### Parent & Child Collections

Records can have a parent record. For example, a shopping cart item record
would likely have a shopping cart record as its parent. This relation is
defined by the child collection: The shopping cart item record collection
defines the shopping cart record collection as its parent.

As a result, each shopping cart item collection instance will require a
shopping cart record instance. This instance is then passed on to each
shopping cart item record instance so it can access it.

- Patterns:
    - Parent collection: Aggregate root
    - Child collection: Entity collection
- Interface: [ChildCollectionInterface.php](../BaseCollection/ChildCollectionInterface.php)
- Abstract base class: [BaseChildCollection.php](../BaseCollection/BaseChildCollection.php)

> NOTE: Parent collections have no official awareness of dependencies to
> child collections, beyond what the concrete implementation may provide.
> There is currently no API to determine if a collection has children.

#### Enforcing The Connection

The `DBHelper::createCollection()` static factory method to create collection
instances expects the parent record to be specified when creating an instance,
of a child collection, i.e. collections that implement the interface 
`ChildCollectionInterface`. If the parent record is not specified, an exception
is thrown.

There is no need to instantiate the child collection manually: Instead, the 
parent record typically implements a method to get the child collection class 
instance. This handles setting the parent record.

Pseudocode as example:

```php
class ParentRecord extends DBHelper_BaseRecord
{
    public function createChildCollection() : ChildCollection
    {
        // Using the class helper to check the return type,
        // as DBHelper::createCollection only returns
        // a collection interface instance. 
        return \AppUtils\ClassHelper::requireObjectInstanceOf(
            ChildCollection::class,
            DBHelper::createCollection(
                ChildCollection::class, 
                $this // myself as parent record
            )
        );
    }
}
```

## Key / Value data tables

In addition to the record table, a generic key/value table can also
be connected using the `DataTable` class. This must be configured
manually by creating an instance as needed.

## Example collection

In the example application implementation, there is an example collection
that illustrates a typical collection use and internal setup.

- [TestDBCollection.php](/tests/application/assets/classes/TestDriver/TestDBRecords/TestDBCollection.php) - The collection class
- [TestDBRecord.php](/tests/application/assets/classes/TestDriver/TestDBRecords/TestDBRecord.php) - The record class
- [TestDBFilterCriteria.php](/tests/application/assets/classes/TestDriver/TestDBRecords/TestDBFilterCriteria.php) - The filter criteria class
- [TestDBFilterSettings.php](/tests/application/assets/classes/TestDriver/TestDBRecords/TestDBFilterSettings.php) - The filter settings class
- [TestDBRecordSettingsManager](/tests/application/assets/classes/TestDriver/TestDBRecords/TestDBRecordSettingsManager.php) - The record settings manager class

