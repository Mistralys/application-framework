# Connectors: Connect to External APIs over HTTP

## Purpose

The Connectors module contains classes that can be used to access
external systems over HTTP. They support the typical HTTP methods
like GET, POST, PUT and DELETE methods.

## Working Principle

The system is designed as a scaffold to create a connector class
for each external system to connect to. For example, if the 
application has a `Countries` service and a `Products` service
that it connects to, each of these gets its own connector class.

## Class Naming Convention

The connector implementations can be stored within the application 
in the corresponding module folders. The convention is to call
this folder `Connectors`. 

The connector class should use namespaces and be called 
`{SERVICE}Connector`, so for example, `ProductsConnector`.

### Extending the Connector class

All connectors must extend the abstract base connector class:

```php
class MyConnector extends Connectors_Connector
{
    public const string SERVICE_URL = 'https://mistralys.eu/example-service';

    protected function init() 
    {
    }

    protected function checkRequirements() : void
    {
        
    }

    public function getURL() : string
    {
        return self::SERVICE_URL; 
    }
}
```

### Manual Requests

Within a connector class, JSON endpoints can be called manually by
configuring a request instance.

**When to use:** Ideal for very simple requests, but the method-based
requests should be preferred because they encapsulate each method's logic.

```php
class MyConnector extends Connectors_Connector
{
    public const string SERVICE_URL = 'https://mistralys.eu/example-service';
    public const int ERROR_FAILED_TO_LOAD_DATA = 42;

    public function getData() : array 
    {
        $data = $this->fetchResponse($this->createURLRequest(self::SERVICE_URL));
        if(is_array($data)) {
            return $data;
        }
        
        throw new Connectors_Exception(
            $this,
            'Failed to load data.',
            'Developer information',
            self::ERROR_FAILED_TO_LOAD_DATA
        );
    }
}
```

### Method-Based Requests

Each service endpoint gets its own connector method class, encapsulating
the logic, which has multiple advantages, including a more readable file
structure, better code maintenance and more. This is the standard way
of setting up connectors.

There are abstract base classes or each HTTP method:

- [DELETE](/src/classes/Connectors/Connector/Method/Delete.php)
- [GET](/src/classes/Connectors/Connector/Method/Get.php)
- [POST](/src/classes/Connectors/Connector/Method/Post.php)
- [PUT](/src/classes/Connectors/Connector/Method/Put.php)

```php
class GetProductsMethod extends Connectors_Connector_Method_Get
{
    public const string REQUEST_METHOD_NAME = 'GetProducts';

    public function getID(): string
    {
        return self::REQUEST_METHOD_NAME;
    }

    public function fetchProducts() : Connectors_Response
    {
        return $this->createMethodRequest('products/get-all')->getData();
    }
}
```


## Connector Factory

The `Connectors` class acts as a factory for connector classes.

```php
$connector = Connectors::createConnector(MyConnector::class);

// Constructor arguments can be passed as an indexed array
$connectorWithArgs = Connectors::createConnector(MyConnector::class, array('argument1', 42));
```

### Checking if a connector exists

```php
Connectors::connectorExists()
```

## Configuring a Proxy Server

All requests can optionally use a proxy server, which can be 
configured using a dedicated proxy configuration class.

```php
$proxy = new \Connectors\ProxyConfiguration(
    'host',
    8000,
    'username',
    'secret'
);
```

