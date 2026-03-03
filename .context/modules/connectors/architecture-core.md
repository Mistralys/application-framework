# Connectors - Core Architecture
_SOURCE: Connector base classes and method signatures_
# Connector base classes and method signatures
```
// Structure of documents
└── src/
    └── classes/
        └── Connectors/
            └── Connector/
                ├── BaseConnector.php
                ├── BaseConnectorMethod.php
                ├── ConnectorException.php
                ├── ConnectorInterface.php
                ├── Method/
                │   ├── Delete.php
                │   ├── Get.php
                │   ├── Post.php
                │   ├── Put.php
                ├── Stub/
                │   ├── Method/
                │   │   └── StubFailureMethod.php
                ├── StubConnector.php
            └── Connectors.php
            └── ConnectorsException.php
            └── ProxyConfiguration.php
            └── Request.php
            └── Response.php
            └── ResponseCode.php

```
###  Path: `/src/classes/Connectors/Connector/BaseConnector.php`

```php
namespace Connectors\Connector;

use AppUtils\ClassHelper as ClassHelper;
use AppUtils\ConvertHelper as ConvertHelper;
use Application_Request as Application_Request;
use Application_Traits_Loggable as Application_Traits_Loggable;
use Application_Traits_Simulatable as Application_Traits_Simulatable;
use Connectors_Request as Connectors_Request;
use Connectors_Request_Method as Connectors_Request_Method;
use Connectors_Request_URL as Connectors_Request_URL;
use Connectors_Response as Connectors_Response;

abstract class BaseConnector implements ConnectorInterface
{
	use Application_Traits_Simulatable;
	use Application_Traits_Loggable;

	public function isLiveRequestsEnabled(): bool
	{
		/* ... */
	}


	public function getID(): string
	{
		/* ... */
	}


	abstract public function getURL(): string;


	public function getActiveResponse(): ?Connectors_Response
	{
		/* ... */
	}


	public function requireActiveResponse(): Connectors_Response
	{
		/* ... */
	}


	public function addParam(string $name, string|int|float|bool $value): ConnectorInterface
	{
		/* ... */
	}


	public function setDebug(bool $state = true): ConnectorInterface
	{
		/* ... */
	}


	public function getLogIdentifier(): string
	{
		/* ... */
	}


	public function createMethod(string $nameOrClass, ...$constructorArgs): BaseConnectorMethod
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Connectors/Connector/BaseConnectorMethod.php`

```php
namespace Connectors\Connector;

use Application_Interfaces_Loggable as Application_Interfaces_Loggable;
use Application_Traits_Loggable as Application_Traits_Loggable;
use Connectors_Request_Method as Connectors_Request_Method;
use Connectors_Request_URL as Connectors_Request_URL;
use Connectors_Response as Connectors_Response;

/**
 * Base class for connector methods.
 *
 * @package Connectors
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class BaseConnectorMethod implements Application_Interfaces_Loggable
{
	use Application_Traits_Loggable;

	/**
	 * Retrieves the ID of the method.
	 * @return string
	 */
	public function getID(): string
	{
		/* ... */
	}


	/**
	 * Retrieves the type of HTTP method used to communicate with the server.
	 * @return string
	 */
	abstract public function getHTTPMethod(): string;


	public function getLogIdentifier(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Connectors/Connector/ConnectorException.php`

```php
namespace Connectors\Connector;

use AppUtils\ConvertHelper as ConvertHelper;
use Connectors\ConnectorsException as ConnectorsException;
use Connectors_Request as Connectors_Request;
use Connectors_Response as Connectors_Response;
use HTTP_Request2_Response as HTTP_Request2_Response;
use JsonException as JsonException;
use Throwable as Throwable;

/**
 * Connector-specific exception, which gives access to all
 * available information, from the request to the response.
 *
 * @package Connectors
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ConnectorException extends ConnectorsException
{
	public const ERROR_NO_ACTIVE_RESPONSE_AVAILABLE = 42401;

	public function getConnector(): ConnectorInterface
	{
		/* ... */
	}


	public function setRequest(Connectors_Request $request): self
	{
		/* ... */
	}


	public function hasRequest(): bool
	{
		/* ... */
	}


	public function getRequest(): ?Connectors_Request
	{
		/* ... */
	}


	/**
	 * @param HTTP_Request2_Response $response
	 * @return $this
	 */
	public function setResponse(HTTP_Request2_Response $response): self
	{
		/* ... */
	}


	public function hasResponse(): bool
	{
		/* ... */
	}


	public function getResponse(): ?HTTP_Request2_Response
	{
		/* ... */
	}


	public function setConnectorResponse(Connectors_Response $response): self
	{
		/* ... */
	}


	public function getConnectorResponse(): ?Connectors_Response
	{
		/* ... */
	}


	public function hasConnectorResponse(): bool
	{
		/* ... */
	}


	public function getDeveloperInfo(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Connectors/Connector/ConnectorInterface.php`

```php
namespace Connectors\Connector;

use AppUtils\ClassHelper\BaseClassHelperException as BaseClassHelperException;
use Application_Interfaces_Loggable as Application_Interfaces_Loggable;
use Application_Interfaces_Simulatable as Application_Interfaces_Simulatable;
use Connectors_Response as Connectors_Response;

/**
 * Base class for connector implementations: offers a number
 * of utility methods that can be used by the individual
 * connectors and defines the common interface that connectors
 * have to conform to.
 *
 * @package Connectors
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
interface ConnectorInterface extends Application_Interfaces_Simulatable, Application_Interfaces_Loggable
{
	/**
	 * Checks if live requests are enabled. They are enabled
	 * by default but turned off in simulation mode.
	 *
	 * With the <code>live-requests</code> boolean request
	 * parameter, they can be turned on explicitly.
	 *
	 * @return bool
	 */
	public function isLiveRequestsEnabled(): bool;


	/**
	 * Retrieves the connector's ID (name). e.g. <code>Editor</code>.
	 * This is the name of the connector file without the extension
	 * (case-sensitive).
	 *
	 * @return string
	 */
	public function getID(): string;


	/**
	 * Retrieves the URL to connect to.
	 *
	 * @return string
	 */
	public function getURL(): string;


	/**
	 * Retrieves the response object from the last request.
	 * @return Connectors_Response|null
	 */
	public function getActiveResponse(): ?Connectors_Response;


	public function requireActiveResponse(): Connectors_Response;


	/**
	 * Adds a parameter to be added to the target URL
	 * that the request will call. This is separate
	 * from the data array provided to {@link getData()},
	 * which is sent via POST.
	 *
	 * @param string $name
	 * @param string|int|float|bool $value
	 * @return ConnectorInterface
	 */
	public function addParam(string $name, string|int|float|bool $value): ConnectorInterface;


	/**
	 * @param bool $state
	 * @return $this
	 */
	public function setDebug(bool $state = true): ConnectorInterface;


	public function getLogIdentifier(): string;


	/**
	 * Creates a new connector method instance, which is
	 * loaded for the current connector type.
	 *
	 * ## Legacy class names
	 *
	 * For legacy methods, the class name follows this scheme:
	 *
	 * ```
	 * Connectors_Connector_(ConnectorName)_Method_(MethodName)
	 * ```
	 *
	 * @param string|class-string<BaseConnectorMethod> $nameOrClass
	 * @param mixed ...$constructorArgs Additional arguments to pass to the method constructor.
	 *                                  Note: The connector instance is always passed as the
	 *                                  first argument.
	 * @return BaseConnectorMethod
	 * @throws BaseClassHelperException
	 */
	public function createMethod(string $nameOrClass, ...$constructorArgs): BaseConnectorMethod;
}


```
###  Path: `/src/classes/Connectors/Connector/Method/Delete.php`

```php
namespace ;

use Connectors\Connector\BaseConnectorMethod as BaseConnectorMethod;

/**
 * Base class for POST API methods.
 *
 * @package Connectors
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Connectors_Connector_Method_Delete extends BaseConnectorMethod
{
	public function getHTTPMethod(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Connectors/Connector/Method/Get.php`

```php
namespace ;

use Connectors\Connector\BaseConnectorMethod as BaseConnectorMethod;

/**
 * Base class for GET API methods.
 *
 * @package Connectors
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Connectors_Connector_Method_Get extends BaseConnectorMethod
{
	public function getHTTPMethod(): string
	{
		/* ... */
	}


	public function getValidResponseCodes()
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Connectors/Connector/Method/Post.php`

```php
namespace ;

use Connectors\Connector\BaseConnectorMethod as BaseConnectorMethod;

/**
 * Base class for POST API methods.
 *
 * @package Connectors
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Connectors_Connector_Method_Post extends BaseConnectorMethod
{
	public function getHTTPMethod(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Connectors/Connector/Method/Put.php`

```php
namespace ;

use Connectors\Connector\BaseConnectorMethod as BaseConnectorMethod;

/**
 * Base class for POST API methods.
 *
 * @package Connectors
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Connectors_Connector_Method_Put extends BaseConnectorMethod
{
	public function getHTTPMethod(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Connectors/Connector/Stub/Method/StubFailureMethod.php`

```php
namespace Connectors\Connector\Stub\Method;

use Connectors\Connector\ConnectorException as ConnectorException;
use Connectors_Connector_Method_Get as Connectors_Connector_Method_Get;

/**
 * Pigeon API method: Retrieves all words available in the
 * dictionary (placeholders for global information, like
 * phone numbers).
 *
 * @package Connectors
 * @subpackage Stub
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class StubFailureMethod extends Connectors_Connector_Method_Get
{
	public const ERROR_CONNECTION_DID_NOT_FAIL = 70101;
	public const ERROR_CONNECTION_FAILED = 70102;

	/**
	 * @return never
	 * @throws ConnectorException
	 */
	public function failFetchData()
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Connectors/Connector/StubConnector.php`

```php
namespace Connectors\Connector;

use AppUtils\ClassHelper as ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException as BaseClassHelperException;
use Connectors\Connector\Stub\Method\StubFailureMethod as StubFailureMethod;

/**
 * @package Connectors
 * @subpackage Stub
 */
class StubConnector extends BaseConnector
{
	public function getURL(): string
	{
		/* ... */
	}


	/**
	 * @return never
	 * @throws BaseClassHelperException
	 * @throws ConnectorException
	 */
	public function executeFailRequest()
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Connectors/Connectors.php`

```php
namespace ;

use AppUtils\ClassHelper as ClassHelper;
use AppUtils\ClassHelper\ClassNotExistsException as ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException as ClassNotImplementsException;
use Connectors\Connector\ConnectorInterface as ConnectorInterface;
use Connectors\Connector\StubConnector as StubConnector;
use Mistralys\VariableHasher\VariableHasher as VariableHasher;

/**
 * External API connectors manager: handles access to
 * connector classes for the available connections to
 * external applications.
 *
 * @package Connectors
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Connectors
{
	public const ERROR_INVALID_CONNECTOR_TYPE = 100701;

	/**
	 * Creates/gets the connector of the specified type. The type is
	 * the name of the connector file, case-sensitive. Will throw
	 * an exception if the type does not exist.
	 *
	 * Connector instances are cached, so multiple calls with the same
	 * arguments will return the same instance.
	 *
	 * @param string|class-string<ConnectorInterface> $typeOrClass Connector type ID or class name.
	 * @param mixed ...$constructorArguments Optional arguments to pass to the connector's constructor.
	 * @return ConnectorInterface
	 *
	 * @throws ClassNotExistsException
	 * @throws ClassNotImplementsException
	 * @see Connectors::ERROR_INVALID_CONNECTOR_TYPE
	 */
	public static function createConnector(string $typeOrClass, ...$constructorArguments): ConnectorInterface
	{
		/* ... */
	}


	public static function createStubConnector(): StubConnector
	{
		/* ... */
	}


	/**
	 * @param string|class-string<ConnectorInterface> $connectorID
	 * @return bool
	 */
	public static function connectorExists(string $connectorID): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Connectors/ConnectorsException.php`

```php
namespace Connectors;

use Application\Exception\ApplicationException as ApplicationException;

class ConnectorsException extends ApplicationException
{
}


```
###  Path: `/src/classes/Connectors/ProxyConfiguration.php`

```php
namespace Connectors;

/**
 * Utility class to hold proxy configuration details.
 *
 * @package Connectors
 * @subpackage Configuration
 */
class ProxyConfiguration
{
	public function getHost(): string
	{
		/* ... */
	}


	public function getPort(): int
	{
		/* ... */
	}


	public function getUsername(): ?string
	{
		/* ... */
	}


	public function getPassword(): ?string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Connectors/Request.php`

```php
namespace ;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use AppUtils\ConvertHelper as ConvertHelper;
use AppUtils\ConvertHelper\JSONConverter as JSONConverter;
use AppUtils\ConvertHelper\JSONConverter\JSONConverterException as JSONConverterException;
use AppUtils\FileHelper_Exception as FileHelper_Exception;
use Application\Application as Application;
use Application\Exception\UnexpectedInstanceException as UnexpectedInstanceException;
use Connectors\Connector\ConnectorException as ConnectorException;
use Connectors\Connector\ConnectorInterface as ConnectorInterface;
use Connectors\Request\RequestSerializer as RequestSerializer;

/**
 * Handles all information for a request to send to a
 * remote API service.
 *
 * @package Connectors
 * @subpackage Request
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Connectors_Request implements Application_Interfaces_Loggable
{
	use Application_Traits_Loggable;

	public const ERROR_INVALID_AUTH_SCHEME = 339001;
	public const ERROR_REQUEST_FAILED = 339002;
	public const ERROR_NO_REQUEST_SENT_YET = 339005;
	public const ERROR_INVALID_DATA_VALUE = 339006;
	public const ERROR_INVALID_METHOD = 339008;
	public const AUTH_SCHEME_BASIC = 'Basic';
	public const AUTH_SCHEME_DIGEST = 'Digest';
	public const ADAPTER_CURL = 'curl';
	public const ADAPTER_SOCKETS = 'socket';

	/** @var array<string,string> */
	public const ADAPTER_CLASSES = array(
	        self::ADAPTER_CURL => HTTP_Request2_Adapter_Curl::class,
	        self::ADAPTER_SOCKETS => HTTP_Request2_Adapter_Socket::class
	    );

	/**
	 * @return string
	 *
	 * @throws ConnectorException
	 * @throws JSONConverterException
	 */
	public function serialize(): string
	{
		/* ... */
	}


	/**
	 * Restores a request instance from a serialized package
	 * created with {@see Connectors_Request::serialize()}.
	 *
	 * LIMITATIONS: Proxy and authentication data are not
	 * persisted, so if used, the request object will not be
	 * directly usable.
	 *
	 * @param string $json
	 * @return Connectors_Request|NULL Can be null if the data is invalid, or obsolete.
	 *
	 * @throws Application_Exception
	 * @throws ConnectorException
	 * @throws JSONConverterException
	 * @throws UnexpectedInstanceException
	 */
	public static function unserialize(string $json): ?Connectors_Request
	{
		/* ... */
	}


	/**
	 * Unique request ID.
	 *
	 * @return string
	 */
	public function getID(): string
	{
		/* ... */
	}


	/**
	 * Tells the request to use authentication in the requested URL.
	 *
	 * @param string $user
	 * @param string $password
	 * @return $this
	 */
	public function useAuth(string $user, string $password): self
	{
		/* ... */
	}


	/**
	 * Tells the request to use a proxy server.
	 *
	 * @param string $host
	 * @param string $port
	 * @param string $user
	 * @param string $password
	 * @param string $authScheme
	 * @return $this
	 * @throws ConnectorException
	 */
	public function useProxy(
		string $host,
		string $port,
		string $user,
		string $password,
		string $authScheme = HTTP_Request2::AUTH_DIGEST,
	): self
	{
		/* ... */
	}


	/**
	 * The base URL as specified as target URL for the request. Can contain
	 * parameters if they were included.
	 *
	 * @return string
	 */
	public function getBaseURL(): string
	{
		/* ... */
	}


	/**
	 * Retrieves the full request URL including all GET request parameters.
	 *
	 * @return string
	 * @throws ConnectorException
	 */
	public function getRequestURL(): string
	{
		/* ... */
	}


	/**
	 * Gets the request instance (available after sending a request).
	 * @return HTTP_Request2|NULL
	 */
	public function getRequest(): ?HTTP_Request2
	{
		/* ... */
	}


	/**
	 * @return float
	 * @throws ConnectorException
	 */
	public function getTimeTaken(): float
	{
		/* ... */
	}


	/**
	 * Sets the method to use to send the request: either
	 * POST or GET.
	 *
	 * @param string $method
	 * @return $this
	 * @throws ConnectorException
	 */
	public function setHTTPMethod(string $method): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 * @throws Application_Exception
	 */
	public function makeGET(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 * @throws ConnectorException
	 */
	public function makePOST(): self
	{
		/* ... */
	}


	public function isPOST(): bool
	{
		/* ... */
	}


	public function isPUT(): bool
	{
		/* ... */
	}


	public function isGET(): bool
	{
		/* ... */
	}


	public function isHTTPMethod(string $method): bool
	{
		/* ... */
	}


	public function getPayload(): array
	{
		/* ... */
	}


	public function getHTTPMethod(): string
	{
		/* ... */
	}


	/**
	 * Sets the timeout for the request in seconds.
	 * Set to 0 for no limit.
	 *
	 * @param int $seconds
	 * @return $this
	 */
	public function setTimeout(int $seconds): self
	{
		/* ... */
	}


	/**
	 * Sets the body of the request to send.
	 *
	 * @param string $content
	 * @return $this
	 */
	public function setBody(string $content): self
	{
		/* ... */
	}


	/**
	 * Sets the body of the request from a JSON string or a data array.
	 * Automatically sets the matching content type.
	 *
	 * @param string|array<string|int,mixed>|ArrayDataCollection $json
	 * @return $this
	 */
	public function setBodyJSON(string|array|ArrayDataCollection $json): self
	{
		/* ... */
	}


	/**
	 * @param string $mime
	 * @return $this
	 */
	public function setContentType(string $mime): self
	{
		/* ... */
	}


	/**
	 * Sets the `Authorization` request header.
	 *
	 * @param string $scheme The auth scheme to use, e.g. {@see self::AUTH_SCHEME_BASIC} or {@see self::AUTH_SCHEME_DIGEST}.
	 * @param string $value The value matching the selected auth scheme.
	 * @return $this
	 */
	public function setAuthorization(string $scheme, string $value): self
	{
		/* ... */
	}


	/**
	 * Gives the request the <code>multipart/form-data</code>
	 * content type for file uploads.
	 *
	 * @return $this
	 */
	public function makeMultipart(): self
	{
		/* ... */
	}


	/**
	 * @return array<string,string>
	 */
	public function getHeaders(): array
	{
		/* ... */
	}


	/**
	 * Retrieves the string that is used to separate mime boundaries.
	 *
	 * @return string
	 */
	public function getBoundary(): string
	{
		/* ... */
	}


	/**
	 * Sets an HTTP header to send with the request. Overwrites
	 * existing headers.
	 *
	 * @param string $name
	 * @param string $value
	 * @return $this
	 */
	public function setHeader(string $name, string $value): self
	{
		/* ... */
	}


	public function getCache(): Connectors_Request_Cache
	{
		/* ... */
	}


	/**
	 * Requests data from a SPIN API method.
	 *
	 * @return Connectors_Response
	 *
	 * @throws ConnectorException
	 * @throws HTTP_Request2_Exception
	 * @throws HTTP_Request2_LogicException
	 * @throws FileHelper_Exception
	 */
	public function getData(): Connectors_Response
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function useCURL(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function useSockets(): self
	{
		/* ... */
	}


	/**
	 * @param string $adapter
	 * @return $this
	 */
	public function setAdapter(string $adapter): self
	{
		/* ... */
	}


	/**
	 * Checks whether the specified response code is
	 * valid for the currently configured HTTP request
	 * method.
	 *
	 * @param int $code
	 * @return boolean
	 */
	public function isValidResponseCode(int $code): bool
	{
		/* ... */
	}


	/**
	 * @param string $method
	 * @return int[]
	 *
	 * @see HTTP_Request2::METHOD_GET
	 * @see HTTP_Request2::METHOD_DELETE
	 * @see HTTP_Request2::METHOD_POST
	 * @see HTTP_Request2::METHOD_PUT
	 */
	public function getCodesByMethod(string $method): array
	{
		/* ... */
	}


	public function getConnector(): ConnectorInterface
	{
		/* ... */
	}


	public function getLogIdentifier(): string
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @param mixed $value
	 * @return $this
	 * @throws ConnectorException
	 */
	public function setPOSTData(string $name, mixed $value): self
	{
		/* ... */
	}


	/**
	 * @param array<int|string,mixed>|ArrayDataCollection $params
	 * @return $this
	 * @throws ConnectorException
	 */
	public function setPOSTParams(array|ArrayDataCollection $params): self
	{
		/* ... */
	}


	/**
	 * @return array<string,string>
	 */
	public function getGetData(): array
	{
		/* ... */
	}


	/**
	 * Retrieves the data to be sent via post, if any.
	 * @return array<string,string>
	 */
	public function getPostData(): array
	{
		/* ... */
	}


	/**
	 * Sets/adds a GET parameter value.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return $this
	 * @throws ConnectorException
	 */
	public function setGETData(string $name, mixed $value): self
	{
		/* ... */
	}


	public function getBody(): string
	{
		/* ... */
	}


	/**
	 * Enables or disabled the caching of the request for
	 * the specified duration. If enabled, the request will
	 * not be sent but the cached data used instead, as long
	 * as its age does not exceed the duration.
	 *
	 * @param bool $enabled
	 * @param int $durationSeconds
	 * @return $this
	 */
	public function setCacheEnabled(bool $enabled = true, int $durationSeconds = 0): self
	{
		/* ... */
	}


	public function getCacheHash(): string
	{
		/* ... */
	}


	/**
	 * @return int Timeout duration in seconds.
	 */
	public function getTimeout(): int
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Connectors/Response.php`

```php
namespace ;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use AppUtils\ThrowableInfo as ThrowableInfo;
use Connectors\Connector\ConnectorException as ConnectorException;
use Connectors\Connector\ConnectorInterface as ConnectorInterface;
use Connectors\Response\ResponseEndpointError as ResponseEndpointError;
use Connectors\Response\ResponseError as ResponseError;
use Connectors\Response\ResponseSerializer as ResponseSerializer;

/**
 * Information on a single response of a connector request.
 * Allows accessing information on the state of the request,
 * including detailed failure information, if any.
 *
 * @package Connectors
 * @subpackage Response
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Connectors_Response implements Application_Interfaces_Loggable
{
	use Application_Traits_Loggable;

	public const ERROR_INVALID_SERIALIZED_DATA = 80001;
	public const ERROR_DATA_SET_IS_MISSING = 80002;
	public const STATE_ERROR = 'error';
	public const STATE_SUCCESS = 'success';
	public const JSON_PLACEHOLDER_START = '{RESPONSE}';
	public const JSON_PLACEHOLDER_END = '{/RESPONSE}';
	public const KEY_STATE = 'state';
	public const KEY_DETAILS = 'details';
	public const KEY_MESSAGE = 'message';
	public const KEY_DATA = 'data';
	public const KEY_CODE = 'code';
	public const KEY_EXCEPTION = 'exception';
	public const RETURNCODE_JSON_NOT_PARSEABLE = 400001;
	public const RETURNCODE_UNEXPECTED_FORMAT = 400002;
	public const RETURNCODE_ERROR_RESPONSE = 400003;
	public const RETURNCODE_WRONG_STATUSCODE = 400004;

	/**
	 * If the endpoint sent exception details in the response payload,
	 * it can be accessed here.
	 *
	 * NOTE: This is available even if the response is considered valid,
	 * and exception details are present.
	 *
	 * @return ThrowableInfo|null
	 * @deprecated Use {@see Connectors_Response::getError()} and {@see ResponseEndpointError::getEndpointError()} instead.
	 */
	public function getEndpointException(): ?ThrowableInfo
	{
		/* ... */
	}


	public function getResponseState(): string
	{
		/* ... */
	}


	/**
	 * @return ResponseError|null
	 * @deprecated Use {@see Connectors_Response::getError()} and {@see ResponseEndpointError::getEndpointError()} instead.
	 */
	public function getEndpointError(): ?ResponseError
	{
		/* ... */
	}


	public function getRequest(): Connectors_Request
	{
		/* ... */
	}


	public function getStatusCode(): int
	{
		/* ... */
	}


	public function getStatusMessage(): string
	{
		/* ... */
	}


	/**
	 * Retrieves the raw JSON from the remote API request.
	 * @return string
	 */
	public function getRawJSON(): string
	{
		/* ... */
	}


	public function getTimeTaken(): float
	{
		/* ... */
	}


	public function isError(): bool
	{
		/* ... */
	}


	public function isLooseData(): bool
	{
		/* ... */
	}


	public function getHeader(string $name): string
	{
		/* ... */
	}


	/**
	 * There are two types of errors:
	 *
	 * 1. The response does not match the expectations. This can be an
	 *    HTTP response code not in the expected list, or the format
	 *    could not be recognized for example. The error will be an
	 *    instance of {@see ResponseError}.
	 *
	 * 2. The endpoint explicitly returned an error message. The error
	 *    object will be an instance of {@see ResponseEndpointError},
	 *    which offers additional error details as provided by the
	 *    endpoint. This can include exception details.
	 *
	 * @return ResponseError|null
	 */
	public function getError(): ?ResponseError
	{
		/* ... */
	}


	/**
	 * @return string
	 * @deprecated Use getError()->getMessage() instead.
	 */
	public function getErrorMessage(): string
	{
		/* ... */
	}


	/**
	 * @return int
	 * @deprecated Use getError()->getCode() instead.
	 */
	public function getErrorCode(): int
	{
		/* ... */
	}


	/**
	 * @return string
	 * @deprecated Use getError()->getDetails() instead.
	 */
	public function getErrorDetails(): string
	{
		/* ... */
	}


	/**
	 * @return array<int|string,mixed>
	 * @deprecated Use getError()->getCode() instead.
	 */
	public function getErrorData(): array
	{
		/* ... */
	}


	/**
	 * Retrieves the full URL that has been requested. If any parameters
	 * were sent via GET or POST, they are included as well. Recommended
	 * to be used for verification purposes only.
	 *
	 * @return string
	 * @see Connectors_Request::getBaseURL()
	 */
	public function getURL(): string
	{
		/* ... */
	}


	public function getResult(): HTTP_Request2_Response
	{
		/* ... */
	}


	public function getMethod(): string
	{
		/* ... */
	}


	/**
	 * Retrieves the decoded JSON data returned
	 * by the endpoint, if any.
	 *
	 * @return array<int|string,mixed>
	 */
	public function getData(): array
	{
		/* ... */
	}


	public function requireData(): array
	{
		/* ... */
	}


	/**
	 * Throws an exception for the response error.
	 *
	 * @param string $message
	 * @param int $code
	 * @throws ConnectorException
	 */
	public function throwException(string $message = '', int $code = 0): void
	{
		/* ... */
	}


	public function createException(string $message = '', int $code = 0): ConnectorException
	{
		/* ... */
	}


	public function getBody(): string
	{
		/* ... */
	}


	public function serialize(): string
	{
		/* ... */
	}


	public static function unserialize(string $serialized): ?Connectors_Response
	{
		/* ... */
	}


	public function getLogIdentifier(): string
	{
		/* ... */
	}


	/**
	 * @param string|int $code
	 * @return bool
	 */
	public function hasErrorCode($code): bool
	{
		/* ... */
	}


	/**
	 * Retrieves all error codes available in the response,
	 * including exception codes.
	 *
	 * @return string[]
	 */
	public function getErrorCodes(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Connectors/ResponseCode.php`

```php
namespace ;

/**
 * List of known HTTP status codes.
 *
 * @package Connectors
 * @supackage Response
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Connectors_ResponseCode extends BasicEnum
{
	public const HTTP_CONTINUE = 100;
	public const HTTP_SWITCHING_PROTOCOLS = 101;
	public const HTTP_PROCESSING = 102;
	public const HTTP_EARLY_HINTS = 103;
	public const HTTP_OK = 200;
	public const HTTP_CREATED = 201;
	public const HTTP_ACCEPTED = 202;
	public const HTTP_NON_AUTHORITATIVE_INFORMATION = 203;
	public const HTTP_NO_CONTENT = 204;
	public const HTTP_RESET_CONTENT = 205;
	public const HTTP_PARTIAL_CONTENT = 206;
	public const HTTP_MULTI_STATUS = 207;
	public const HTTP_ALREADY_REPORTED = 208;
	public const HTTP_IM_USED = 226;
	public const HTTP_MULTIPLE_CHOICES = 300;
	public const HTTP_MOVED_PERMANENTLY = 301;
	public const HTTP_FOUND = 302;
	public const HTTP_SEE_OTHER = 303;
	public const HTTP_NOT_MODIFIED = 304;
	public const HTTP_USE_PROXY = 305;
	public const HTTP_RESERVED = 306;
	public const HTTP_TEMPORARY_REDIRECT = 307;
	public const HTTP_PERMANENTLY_REDIRECT = 308;
	public const HTTP_BAD_REQUEST = 400;
	public const HTTP_UNAUTHORIZED = 401;
	public const HTTP_PAYMENT_REQUIRED = 402;
	public const HTTP_FORBIDDEN = 403;
	public const HTTP_NOT_FOUND = 404;
	public const HTTP_METHOD_NOT_ALLOWED = 405;
	public const HTTP_NOT_ACCEPTABLE = 406;
	public const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
	public const HTTP_REQUEST_TIMEOUT = 408;
	public const HTTP_CONFLICT = 409;
	public const HTTP_GONE = 410;
	public const HTTP_LENGTH_REQUIRED = 411;
	public const HTTP_PRECONDITION_FAILED = 412;
	public const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
	public const HTTP_REQUEST_URI_TOO_LONG = 414;
	public const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
	public const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
	public const HTTP_EXPECTATION_FAILED = 417;
	public const HTTP_I_AM_A_TEAPOT = 418;
	public const HTTP_MISDIRECTED_REQUEST = 421;
	public const HTTP_UNPROCESSABLE_ENTITY = 422;
	public const HTTP_LOCKED = 423;
	public const HTTP_FAILED_DEPENDENCY = 424;
	public const HTTP_TOO_EARLY = 425;
	public const HTTP_UPGRADE_REQUIRED = 426;
	public const HTTP_PRECONDITION_REQUIRED = 428;
	public const HTTP_TOO_MANY_REQUESTS = 429;
	public const HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
	public const HTTP_UNAVAILABLE_FOR_LEGAL_REASONS = 451;
	public const HTTP_INTERNAL_SERVER_ERROR = 500;
	public const HTTP_NOT_IMPLEMENTED = 501;
	public const HTTP_BAD_GATEWAY = 502;
	public const HTTP_SERVICE_UNAVAILABLE = 503;
	public const HTTP_GATEWAY_TIMEOUT = 504;
	public const HTTP_VERSION_NOT_SUPPORTED = 505;
	public const HTTP_VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL = 506;
	public const HTTP_INSUFFICIENT_STORAGE = 507;
	public const HTTP_LOOP_DETECTED = 508;
	public const HTTP_NOT_EXTENDED = 510;
	public const HTTP_NETWORK_AUTHENTICATION_REQUIRED = 511;
}


```
---
**File Statistics**
- **Size**: 31.25 KB
- **Lines**: 1553
File: `modules/connectors/architecture-core.md`
