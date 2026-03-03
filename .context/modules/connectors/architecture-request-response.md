# Connectors - Request & Response Architecture
_SOURCE: HTTP request, response, and header class signatures_
# HTTP request, response, and header class signatures
```
// Structure of documents
└── src/
    └── classes/
        └── Connectors/
            └── Headers/
                ├── HTTPHeader.php
                ├── HTTPHeadersBasket.php
            └── Request/
                ├── Cache.php
                ├── Method.php
                ├── RequestSerializer.php
                ├── URL.php
            └── Response/
                └── ResponseEndpointError.php
                └── ResponseError.php
                └── ResponseSerializer.php

```
###  Path: `/src/classes/Connectors/Headers/HTTPHeader.php`

```php
namespace Connectors\Headers;

use AppUtils\Interfaces\StringPrimaryRecordInterface as StringPrimaryRecordInterface;

/**
 * Holds information about a single HTTP header.
 *
 * @package Connectors
 * @subpackage Headers
 */
class HTTPHeader implements StringPrimaryRecordInterface
{
	public function getID(): string
	{
		/* ... */
	}


	public function getName(): string
	{
		/* ... */
	}


	public function getValue(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Connectors/Headers/HTTPHeadersBasket.php`

```php
namespace Connectors\Headers;

use AppUtils\Baskets\GenericStringPrimaryBasket as GenericStringPrimaryBasket;

/**
 * Utility class used to store HTTPHeader objects for use
 * in connector requests.
 *
 * @package Connectors
 * @subpackage Headers
 *
 * @method HTTPHeader[] getAll()
 * @method HTTPHeader getByID(string $id)
 */
class HTTPHeadersBasket extends GenericStringPrimaryBasket
{
	public function addHeader(string $name, string $value): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Connectors/Request/Cache.php`

```php
namespace ;

use AppUtils\FileHelper as FileHelper;
use AppUtils\FileHelper_Exception as FileHelper_Exception;
use Application\Application as Application;

/**
 * Handles caching information for a request.
 *
 * @package Connectors
 * @supackage Request
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Connectors_Request_Cache implements Application_Interfaces_Loggable
{
	use Application_Traits_Loggable;

	public function getLogIdentifier(): string
	{
		/* ... */
	}


	public function isEnabled(): bool
	{
		/* ... */
	}


	public function isValid(): bool
	{
		/* ... */
	}


	public function getCacheFile(): string
	{
		/* ... */
	}


	/**
	 * @param bool $enabled
	 * @param int $durationSeconds
	 * @return $this
	 */
	public function setEnabled(bool $enabled, int $durationSeconds = 0): self
	{
		/* ... */
	}


	/**
	 * Stores the response in the cache.
	 *
	 * @param Connectors_Response $response
	 * @throws FileHelper_Exception
	 * @return $this
	 */
	public function storeResponse(Connectors_Response $response): self
	{
		/* ... */
	}


	public function fetchResponse(): ?Connectors_Response
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Connectors/Request/Method.php`

```php
namespace ;

use Connectors\Connector\ConnectorException as ConnectorException;
use Connectors\Connector\ConnectorInterface as ConnectorInterface;
use Connectors\Headers\HTTPHeadersBasket as HTTPHeadersBasket;

/**
 * Method request: handles requests to a specific
 * API endpoint method.
 *
 * @package Connectors
 * @subpackage Request
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Connectors_Request_Method extends Connectors_Request
{
	/**
	 * @return string
	 */
	public function getMethod(): string
	{
		/* ... */
	}


	/**
	 * @param string $varName
	 * @return $this
	 */
	public function setMethodVar(string $varName): self
	{
		/* ... */
	}


	public function getData(): Connectors_Response
	{
		/* ... */
	}


	public function setHeaders(?HTTPHeadersBasket $headers): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Connectors/Request/RequestSerializer.php`

```php
namespace Connectors\Request;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use AppUtils\ConvertHelper\JSONConverter as JSONConverter;
use AppUtils\ConvertHelper\JSONConverter\JSONConverterException as JSONConverterException;
use Application\AppFactory as AppFactory;
use Application\Exception\UnexpectedInstanceException as UnexpectedInstanceException;
use Application_Exception as Application_Exception;
use Connectors as Connectors;
use Connectors\Connector\ConnectorException as ConnectorException;
use Connectors_Request as Connectors_Request;
use Connectors_Request_Method as Connectors_Request_Method;
use Connectors_Request_URL as Connectors_Request_URL;
use Mistralys\AppFramework\Helpers\JSONUnserializer as JSONUnserializer;

/**
 * Utility class that handles serializing and unserializing
 * a connector request instance.
 *
 * @package Connectors
 * @subpackage Request
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class RequestSerializer
{
	public const ERROR_UNKNOWN_REQUEST_TYPE = 124101;
	public const KEY_CONNECTOR_ID = 'connectorID';
	public const KEY_URL = 'url';
	public const KEY_ID = 'id';
	public const KEY_POST_DATA = 'postData';
	public const KEY_GET_DATA = 'getData';
	public const KEY_HEADERS = 'headers';
	public const KEY_TIMEOUT = 'timeout';
	public const KEY_HTTP_METHOD = 'HTTPMethod';
	public const REQUEST_TYPE_URL = 'URL';
	public const KEY_REQUEST_TYPE = 'requestType';
	public const REQUEST_TYPE_METHOD = 'Method';

	/**
	 * @param Connectors_Request $request
	 * @return string
	 *
	 * @throws ConnectorException
	 * @throws JSONConverterException
	 */
	public static function serialize(Connectors_Request $request): string
	{
		/* ... */
	}


	public static function resolveRequestType(Connectors_Request $request): string
	{
		/* ... */
	}


	/**
	 * @param string $json
	 * @return Connectors_Request|NULL
	 *
	 * @throws JSONConverterException
	 * @throws UnexpectedInstanceException
	 * @throws Application_Exception
	 * @throws ConnectorException
	 */
	public static function unserialize(string $json): ?Connectors_Request
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Connectors/Request/URL.php`

```php
namespace ;

/**
 * Handles a URL-based API request.
 *
 * @package Connectors
 * @supackage Request
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Connectors_Request_URL extends Connectors_Request
{
}


```
###  Path: `/src/classes/Connectors/Response/ResponseEndpointError.php`

```php
namespace Connectors\Response;

use AppUtils\ThrowableInfo as ThrowableInfo;
use Connectors_Response as Connectors_Response;

class ResponseEndpointError extends ResponseError
{
	public function getEndpointError(): ResponseError
	{
		/* ... */
	}


	public function isEndpointError(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Connectors/Response/ResponseError.php`

```php
namespace Connectors\Response;

use AppUtils\ConvertHelper_Exception as ConvertHelper_Exception;
use AppUtils\ThrowableInfo as ThrowableInfo;

/**
 * Information on an error that occurred in a response.
 *
 * @package Connectors
 * @subpackage Response
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ResponseError
{
	public function getMessage(): string
	{
		/* ... */
	}


	/**
	 * @return array<int|string,mixed>
	 */
	public function getData(): array
	{
		/* ... */
	}


	public function getCode(): int
	{
		/* ... */
	}


	public function getDetails(): string
	{
		/* ... */
	}


	public function getException(): ?ThrowableInfo
	{
		/* ... */
	}


	public function isEndpointError(): bool
	{
		/* ... */
	}


	/**
	 * Fetches a list of all error codes available in the error,
	 * from the error itself to any exceptions (and recursively
	 * within their previous exceptions).
	 *
	 * @return string[]
	 * @throws ConvertHelper_Exception
	 */
	public function getAllCodes(): array
	{
		/* ... */
	}


	public function hasErrorCode($code): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Connectors/Response/ResponseSerializer.php`

```php
namespace Connectors\Response;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use AppUtils\ConvertHelper as ConvertHelper;
use Application\Application as Application;
use Connectors_Request as Connectors_Request;
use Connectors_Response as Connectors_Response;
use HTTP_Request2_Response as HTTP_Request2_Response;
use Mistralys\AppFramework\Helpers\JSONUnserializer as JSONUnserializer;

/**
 * Utility class that handles serializing and unserializing
 * a connector response instance.
 *
 * @package Connectors
 * @subpackage Response
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ResponseSerializer
{
	public const KEY_STATUS_CODE = 'statusCode';
	public const KEY_STATUS_MESSAGE = 'statusMessage';
	public const KEY_BODY = 'body';
	public const KEY_REQUEST = 'request';

	public static function serialize(Connectors_Response $response): string
	{
		/* ... */
	}


	public static function unserialize(string $serialized): ?Connectors_Response
	{
		/* ... */
	}
}


```
---
**File Statistics**
- **Size**: 8.96 KB
- **Lines**: 457
File: `modules/connectors/architecture-request-response.md`
