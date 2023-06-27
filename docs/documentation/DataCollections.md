# Data collections

## Countries

### Introduction

Collection class: `Application_Countries`  
Factory method: `Application_Countries::getInstance()`

The countries management handles all available countries for the application.
It offers a number of methods around accessing countries, and make creating
country selection use cases easy to handle.

### Fetching countries

```php
$collection = Application_Countries::getInstance();

// Getting all countries 
$collection->getAll();
```

### The countries selector

The `injectCountrySelector` can be used to add a country selection element
to a `UI_Form` instance. This means it can be used with a traditional form
instance as well as a Formable.

Inject from within a formable:

```php
class Documentation_CountrySelector extends Application_Formable
{
    public function inject_countries() : void
    {
        $selectElement = Application_Countries::getInstance()->injectCountrySelector(
            $this->getFormInstance(),
            'countries', // field name
            t('Countries'), // field label
            true, // required?
            true, // Add the "Please select" entry?
            false // Include the invariant country?
        );
    }
}
```

### The invariant country

#### Introduction

The invariant country can be used in cases where a country must be selected,
but the data can be valid for all countries. Its details are always the same:

- ID: `9999` - See `Application_Countries_Country::COUNTRY_INDEPENDENT_ID`
- ISO: `zz` - See `Application_Countries_Country::COUNTRY_INDEPENDENT_ISO`

#### Fetching the country

```php
Application_Countries::getInstance()->getInvariantCountry();
```

#### Excluding from results

By default, the invariant country is included in all results, but can be
excluded manually.

When using the `getAll()` method:

```php
$collection = Application_Countries::getInstance();
$countries = $collection->getAll(false);
```

When using the filter criteria:

```php
$criteria = Application_Countries::getInstance()->getFilterCriteria();
$criteria->excludeInvariant();
```

## Data handling classes

### Array-based records

The base record class is a counterpart to the DBHelper's record. Here the target
data can be loaded from an arbitrary source, and the getter methods make working
with the data array easier.

The class offers a number of type hinted methods to access keys in the data array,
like for example:

- `getDataKeyInt()`
- `getDataKeyBool()`
- `getDataKeyArray()`
- `getDataKeyDate()`

These check if the target key is present in the data array, and guarantee returning
the correct data type, to avoid doing this manually.

#### For integer-based primary keys

Extend the class `Application_Collection_BaseRecord_IntegerPrimary`.

This is the base skeleton for a record class:

```php
<?php

declare(strict_types=1);

class ExampleIntegerBaseRecord extends Application_Collection_BaseRecord_IntegerPrimary
{
    /**
     * A label for the type of record, e.g. "Product", which
     * is used in log messages to identify the record.
     *
     * @return string
     */
    protected function getRecordTypeLabel(): string
    {
        return 'Example record';
    }
    
    /**
     * The name of the record's primary key in the data set,
     * e.g. "product_id". Ensures that its value cannot be
     * overwritten or set.
     *
     * @return string
     */
    protected function getRecordPrimaryName(): string
    {
        return 'primaryKey';
    }

    /**
     * Fetches the record's data set as an associative array. 
     * @return array<string,mixed>
     */
    protected function loadData(): array
    {
        // Implement the logic to fetch the data here
        return array();
    }

    /**
     * Called at the end of the constructor, after the data has been loaded. 
     */
    protected function init(): void
    {
    }
}
```
