# Connectors for remote services

## Introduction

Connectors are a set of classes used to connect to external services: they offer a
flexible base feature set to connect using a range of service types.

## Structure

For each service to connect to, a connector class should be added in the application's
`Connectors` folder:

`assets/classes/Connectors/ServiceName.php`

Individual service methods can then be added here:

`assets/classes/Connectors/ServiceName/Method/XXX.php`

### Method classes

The connector has abstract classes for the following methods:

- DELETE - `Connectors_Connector_Method_Delete`
- GET - `Connectors_Connector_Method_Get`
- POST - `Connectors_Connector_Method_Post`
- PUT - `Connectors_Connector_Method_Put`

These can be extended to communicate with services based on HTTP methods.

> NOTE: All these expect the data format to be JSON. See "Custom methods"
below to see how to handle other cases.

### Custom methods

If the method classes do not fit your use case, create your methods from the base class,
`Connectors_Connector_Method`, and add your own implementation. If needed, it is even
possible to use third party packages to handle the actual communication.

### Exceptions

It is recommended to create an exception class for each service, so these exceptions
can easily be identified. This should be added in the service's folder, i.e.:

```
assets/classes/Connectors/ServiceName/Exception.php
```

The class must extend the base connector exception, `Connectors_Exception`.

## Adding a service method

The simplified steps for adding an API method to a connector can be the following:

1) Add a matching method in the connector class, e.g. `getProducts()`.
2) Define the required parameters for the method.
3) Create the method class, e.g. `Connectors_ServiceName_Method_GetProducts`.
4) Add the method `getProducts()` in the method class with the parameters.
4) Instantiate the method in `getProducts()`.
5) Call `getProducts()` on the method instance, passing the parameters.
6) Implement the connection in the method class
7) Return the data

### Creating classes for data types

To keep things easy to understand, it is recommended to create a separate data storage
class for each data type fetched via the connector methods. For example, if retrieving
data for products, the `getProducts()` method should ideally return an array with
product instances (for example of type `Connectors_ServiceName_Product`), with the
relevant getter methods for maximum transparency.

This makes it easier down the line to see what data is available, and also to allow for
future adjustments to the data sets.

Also, the product class from this example is liable to be used in other methods of the
remote service. If there is a method to add a new product, it will be handy to be able
to give the method class a product instance, which already has all the information the
API method needs.

### Example connector class and method

To illustrate the examples mentioned above with a remote service handling products,
here is some PHP code to go with it, with the following classes:

- `Connectors_Connector_Products` - The remote service connector.
- `Connectors_Connector_Products_Method_GetProducts` - The method class for the GET products endpoint.
- `Connectors_Connector_Products_DataType_Product` - The data type container for a single product.

```php
/**
 * The connector class that is used to connected to the "Products" remote service. 
 */
class Connectors_Connector_Products extends Connectors_Connector
{
    public function getURL() : string
    {
        return 'https://products.service/api/';
    }
    
    public function checkRequirements() : void
    {
        // check if all requirements to connect to the remote service
        // are met; throw an exception otherwise.
    }
    
    /**
     * Fetches all products from the API, for the specified country.
     * 
     * @param string $countryCode
     * @return Connectors_Connector_Products_DataType_Product[]
     */
    public function getProducts(string $countryCode) : array
    {
        $this->createGetProducts()->getProducts($countryCode);
    }
    
    /**
     * Create the method instance to get the products. 
     * @return Connectors_Connector_Products_Method_GetProducts
     */
    private function createGetProducts() : Connectors_Connector_Products_Method_GetProducts
    {
        return new Connectors_Connector_Products_Method_GetProducts($this);
    }
}

/**
 * The connector method class, which handles the actual communication with
 * the remote API, sending the request and processing the result.
 */
class Connectors_Connector_Products_Method_GetProducts extends Connectors_Connector_Method_Get
{
    public const ERROR_COULD_NOT_FETCH_PRODUCTS = 80601;

    /**
     * @param string $countryCode
     * @return Connectors_Connector_Products_DataType_Product[]
     */
    public function getProducts(string $countryCode) : array
    {
        // 'products' is the API endpoint to call: this is appended
        // to the connector's service URL.
        $request = $this->createRequest('products');
        
        // Add some GET data. The same can be done for POST data
        // as needed.
        $request->setGETData(array('country' => $countryCode));
        
        // The request automatically decodes the JSON, so a successful
        // request directly returns the actual data set.
        $response = $this->executeRequest($request);
        
        // Check if the response had errors 
        if($response->isError())
        {
            $response->throwException(
                'Could not fetch products.',
                self::ERROR_COULD_NOT_FETCH_PRODUCTS
            );
        }
        
        return $this->processResponse($response);
    }
    
    /**
     * Processes the response when it is valid,, and returns the
     * available products.
     * 
     * @param Connectors_Response $response
     * @return Connectors_Connector_Products_DataType_Product[]
     */
    private function processResponse(Connectors_Response $response) : array
    {
        $data = $response->getData();
    
        $result = array();
        foreach($data as $productData)
        {
            $result[] = new Connectors_Connector_Products_DataType_Product(
                intval($productData['productID']),
                strval($productData['name'])
            );
        }
        
        return $result;
    }
}

/**
 * The "Product" data type used to make the exchange of data
 * more transparent for all sides that handle product data. 
 */
class Connectors_Connector_Products_DataType_Product
{
   /**
    * @var int 
    */
    private $id;
    
    /**
     * @var string 
     */
    private $label;
    
    public function __construct(int $id, string $label)
    {
        $this->id = $id;
        $this->label = $label;
    }
    
    public function getID() : int
    {
        return $this->id;
    }
    
    public function getLabel() : string
    {
        return $this->label;
    }
}
```
