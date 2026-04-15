# API - Core Architecture (Public API)
_SOURCE: APIManager, APIMethodInterface, APIException, APIFoldersManager, APIUrls, ResponsePayload, ErrorResponsePayload, ErrorResponse, APIResponseDataException_
# APIManager, APIMethodInterface, APIException, APIFoldersManager, APIUrls, ResponsePayload, ErrorResponsePayload, ErrorResponse, APIResponseDataException
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── API/
                └── APIException.php
                └── APIFoldersManager.php
                └── APIManager.php
                └── APIMethodInterface.php
                └── APIResponseDataException.php
                └── APIUrls.php
                └── BaseMethods/
                    ├── BaseAPIMethod.php
                └── Collection/
                    ├── APICacheLocation.php
                    ├── APIMethodCollection.php
                    ├── APIMethodIndex.php
                └── Connector/
                    ├── AppAPIConnector.php
                    ├── AppAPIMethod.php
                └── Documentation/
                    ├── APIDocumentation.php
                    ├── BaseAPIDocumentation.php
                    ├── Examples/
                    │   ├── JSONMethodExample.php
                    ├── MethodDocumentation.php
                └── ErrorResponse.php
                └── ErrorResponsePayload.php
                └── Events/
                    ├── RegisterAPIIndexCacheListener.php
                    ├── RegisterAPIResponseCacheListener.php
                └── Groups/
                    ├── APIGroupInterface.php
                    ├── FrameworkAPIGroup.php
                    ├── GenericAPIGroup.php
                └── Response/
                    ├── JSONInfoSerializer.php
                    ├── ResponseInterface.php
                └── ResponsePayload.php
                └── Traits/
                    ├── DryRun/
                    │   ├── DryRunAPIParam.php
                    ├── DryRunAPIInterface.php
                    ├── DryRunAPITrait.php
                    ├── JSONRequestInterface.php
                    ├── JSONRequestTrait.php
                    ├── JSONResponseInterface.php
                    ├── JSONResponseTrait.php
                    ├── JSONResponseWithExampleInterface.php
                    ├── JSONResponseWithExampleTrait.php
                    ├── RequestRequestInterface.php
                    ├── RequestRequestTrait.php
                └── User/
                    ├── APIRightsInterface.php
                    ├── APIRightsTrait.php
                └── Utilities/
                    ├── KeyDescription.php
                    ├── KeyPath.php
                    ├── KeyPathInterface.php
                    ├── KeyReplacement.php
                └── Versioning/
                    └── APIVersionInterface.php
                    └── BaseAPIVersion.php
                    └── VersionCollection.php
                    └── VersionedAPIInterface.php
                    └── VersionedAPITrait.php

```
###  Path: `/src/classes/Application/API/APIException.php`

```php
namespace Application\API;

use Application_Exception as Application_Exception;

/**
 * Exception class for API-related errors.
 *
 * @package API
 * @subpackage Core
 */
class APIException extends Application_Exception
{
	public const ERROR_METHOD_NOT_IN_INDEX = 59213005;
	public const ERROR_INTERNAL = 59213006;
	public const ERROR_CANNOT_MODIFY_AFTER_VALIDATION = 59213007;
	public const ERROR_INVALID_API_VERSION = 59213008;
}


```
###  Path: `/src/classes/Application/API/APIFoldersManager.php`

```php
namespace Application\API;

use Application\API\OpenAPI\GetOpenAPISpec as GetOpenAPISpec;
use Application\Admin\Index\AdminScreenIndex as AdminScreenIndex;
use Application\Countries\CountriesCollection as CountriesCollection;
use Application\Locales as Locales;
use Application\SourceFolders\Sources\APISourceFolders as APISourceFolders;
use DBHelper as DBHelper;

/**
 * Utility class used to register framework-internal API method folders.
 *
 * @package API
 * @subpackage Core
 */
class APIFoldersManager
{
	public function register(): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/APIManager.php`

```php
namespace Application\API;

use AppUtils\ClassHelper as ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException as BaseClassHelperException;
use Application\API\Collection\APIMethodCollection as APIMethodCollection;
use Application\API\Collection\APIMethodIndex as APIMethodIndex;
use Application\API\OpenAPI\HtaccessGenerator as HtaccessGenerator;
use Application\API\OpenAPI\OpenAPIGenerator as OpenAPIGenerator;
use Application_Driver as Application_Driver;
use Application_Request as Application_Request;

/**
 * Main API manager class that is used as endpoint to
 * process API requests.
 *
 * ## Usage
 *
 * - To process an API request, call the {@see self::process()} method.
 * - To load a specific API method class, use {@see self::loadMethod()}.
 * - To access available API methods, use {@see self::getMethodCollection()}.
 *
 * @package API
 * @subpackage Core
 */
class APIManager
{
	public const ERROR_NO_METHOD_SPECIFIED = 112547002;
	public const ERROR_INVALID_METHOD_CLASS = 112547003;

	/**
	 * Returns the global instance of the API manager,
	 * creating the instance as needed.
	 *
	 * @return APIManager
	 */
	public static function getInstance(): APIManager
	{
		/* ... */
	}


	public function adminURL(): APIUrls
	{
		/* ... */
	}


	public function getMethodCollection(): APIMethodCollection
	{
		/* ... */
	}


	public function getMethodIndex(): APIMethodIndex
	{
		/* ... */
	}


	public function process(?string $methodName = null): void
	{
		/* ... */
	}


	public function getRequestedMethodName(): ?string
	{
		/* ... */
	}


	public function getMethodByName(string $methodName): APIMethodInterface
	{
		/* ... */
	}


	/**
	 * @param class-string<APIMethodInterface> $class
	 * @return APIMethodInterface
	 * @throws APIException
	 */
	public function loadMethod(string $class): APIMethodInterface
	{
		/* ... */
	}


	/**
	 * Replaces all known method names in the given text with Markdown links to their documentation.
	 * @param string $text
	 * @return string
	 */
	public function markdownifyMethodNames(string $text): string
	{
		/* ... */
	}


	/**
	 * Generates the OpenAPI 3.1 specification JSON file for all registered API methods.
	 *
	 * Instantiates {@see OpenAPIGenerator} with the current method collection, the
	 * application name and version from the running driver, and delegates to
	 * {@see OpenAPIGenerator::generate()}.
	 *
	 * @param string $outputPath Optional absolute path for the generated JSON file.
	 *                           Defaults to `APP_INSTALL_FOLDER/api/openapi.json`
	 *                           when the constant is defined.
	 * @return string The absolute path to the generated file.
	 */
	public function generateOpenAPISpec(string $outputPath = ''): string
	{
		/* ... */
	}


	/**
	 * Generates the API `.htaccess` file that enables RESTful URL rewriting.
	 *
	 * @param string $outputDirectory Optional absolute path to the directory where the
	 *                                `.htaccess` file will be written. Defaults to
	 *                                `APP_INSTALL_FOLDER/api` when the constant is defined.
	 * @return string The absolute path to the generated `.htaccess` file.
	 */
	public function generateHtaccess(string $outputDirectory = ''): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/APIMethodInterface.php`

```php
namespace Application\API;

use AppUtils\Interfaces\StringPrimaryRecordInterface as StringPrimaryRecordInterface;
use AppUtils\Microtime as Microtime;
use Application\API\Groups\APIGroupInterface as APIGroupInterface;
use Application\API\Parameters\APIParamManager as APIParamManager;
use Application\API\Parameters\Validation\ParamValidationResults as ParamValidationResults;
use Application\API\Response\JSONInfoSerializer as JSONInfoSerializer;
use Application_CORS as Application_CORS;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

/**
 * Interface for all API methods.
 *
 * @package API
 * @subpackage Core
 */
interface APIMethodInterface extends StringPrimaryRecordInterface
{
	public const ERROR_REQUEST_DATA_EXCEPTION = 183001;
	public const ERROR_RESPONSE_DATA_EXCEPTION = 183002;
	public const ERROR_INVALID_REQUEST_PARAMS = 183003;
	public const ERROR_NO_VALUE_AVAILABLE = 183004;
	public const REQUEST_PARAM_API_VERSION = 'apiVersion';
	public const REQUEST_PARAM_METHOD = 'method';
	public const RESPONSE_KEY_ERROR_REQUEST_DATA = 'requestData';

	public function getInfo(): JSONInfoSerializer;


	public function getMethodName(): string;


	public function getDescription(): string;


	/**
	 * The group that this method belongs to. This is used for
	 * documentation purposes, but also for organizing methods
	 * and selecting them in the API key administration.
	 *
	 * @return APIGroupInterface
	 */
	public function getGroup(): APIGroupInterface;


	public function getRequestMime(): string;


	public function getResponseMime(): string;


	public function getDocumentationURL(): AdminURLInterface;


	/**
	 * @return array<string, string> An associative array containing changelog entries with version numbers as keys and descriptions as values.
	 */
	public function getChangelog(): array;


	/**
	 * @return string[] An array of method names that are related to this method.
	 */
	public function getRelatedMethodNames(): array;


	/**
	 * @return APIMethodInterface[] An array of method instances that are related to this method.
	 */
	public function getRelatedMethods(): array;


	public function getRequestTime(): ?Microtime;


	/**
	 * Retrieves an indexed array containing available API
	 * version numbers that can be specified to work with.
	 *
	 * @return string[]
	 */
	public function getVersions(): array;


	/**
	 * Retrieves the current version of the API endpoint.
	 *
	 * @return string
	 */
	public function getCurrentVersion(): string;


	/**
	 * Manually selects the version to work with, when working
	 * outside a request context.
	 *
	 * @param string $version
	 * @return $this
	 */
	public function selectVersion(string $version): self;


	public function manageParams(): APIParamManager;


	/**
	 * Gets the version that the method is currently
	 * working with. If a valid version has been specified
	 * in the request, that version is returned. Otherwise,
	 * the current version is returned.
	 *
	 * @return string
	 */
	public function getActiveVersion(): string;


	/**
	 * Adds a domain name to the list of allowed cross-origin
	 * request sources. Adding one of these enables CORS for
	 * this API endpoint.
	 *
	 * > Note: use the wildcard <code>*</code> as domain to enable
	 * > all cross-origin sources.
	 *
	 * @param string $domain
	 * @return $this
	 */
	public function allowCORSDomain(string $domain): self;


	public function getCORS(): Application_CORS;


	/**
	 * Processes the API request and sends the response.
	 * @return never
	 */
	public function process(): never;


	/**
	 * Processes the method as usual, but instead of sending
	 * the response to the client, it returns the response data
	 * as an object.
	 *
	 * > This is mostly used for unit testing API methods, but
	 * > can potentially allow re-using API methods outside
	 * > the API endpoint context.
	 *
	 * @return ResponsePayload|ErrorResponsePayload The data that was fetched, or an error response payload.
	 * @throws APIException
	 */
	public function processReturn(): ResponsePayload|ErrorResponsePayload;


	/**
	 * Renders an example response for this API method.
	 *
	 * This is used in the documentation to show an example
	 * response for the method.
	 *
	 * @return string|null The HTML representation of the example response, or null if no example is available.
	 */
	public function renderExample(): ?string;


	/**
	 * When using {@see self::processReturn()}, this method can be used
	 * to retrieve the validation results of the parameters.
	 *
	 * @return ParamValidationResults
	 */
	public function getValidationResults(): ParamValidationResults;


	/**
	 * Sets the request body to use instead of reading from `php://input`,
	 * used for testing purposes.
	 *
	 * @param string $body
	 * @return $this
	 */
	public function setRequestBody(string $body): self;


	/**
	 * Gets a single-line text representation of the method to use in the
	 * UI when filtering the method list by keywords.
	 *
	 * @return string
	 */
	public function getFilterText(): string;


	public function errorResponse(int $errorCode): ErrorResponse;
}


```
###  Path: `/src/classes/Application/API/APIResponseDataException.php`

```php
namespace Application\API;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use Exception as Exception;

/**
 * Special exception used when processing an API method using the
 * {@see APIMethodInterface::processReturn()} method. It is used
 * to halt the processing and return the response data through
 * the exception.
 *
 * **WARNING**: This exception should not be used for any other purpose.
 *
 * @package API
 * @subpackage Core
 */
class APIResponseDataException extends Exception
{
	public const CODE_API_METHOD_RETURN_EXCEPTION = 182901;

	public function getMethod(): APIMethodInterface
	{
		/* ... */
	}


	public function getResponseData(): ResponsePayload|ErrorResponsePayload
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/APIUrls.php`

```php
namespace Application\API;

use Application\Bootstrap\Screen\APIDocumentationBootstrap as APIDocumentationBootstrap;
use UI\AdminURLs\AdminURL as AdminURL;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

class APIUrls
{
	public function documentationOverview(): AdminURLInterface
	{
		/* ... */
	}


	public function methodDocumentation(APIMethodInterface|string $method): AdminURLInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/BaseMethods/BaseAPIMethod.php`

```php
namespace Application\API\BaseMethods;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use AppUtils\Interfaces\StringableInterface as StringableInterface;
use AppUtils\Microtime as Microtime;
use Application\API\APIException as APIException;
use Application\API\APIManager as APIManager;
use Application\API\APIMethodInterface as APIMethodInterface;
use Application\API\APIResponseDataException as APIResponseDataException;
use Application\API\Cache\CacheableAPIMethodInterface as CacheableAPIMethodInterface;
use Application\API\Clients\API\APIKeyMethodInterface as APIKeyMethodInterface;
use Application\API\ErrorResponse as ErrorResponse;
use Application\API\ErrorResponsePayload as ErrorResponsePayload;
use Application\API\Parameters\APIParamManager as APIParamManager;
use Application\API\Parameters\ParamTypeSelector as ParamTypeSelector;
use Application\API\Parameters\Reserved\APIMethodParameter as APIMethodParameter;
use Application\API\Parameters\Reserved\APIVersionParameter as APIVersionParameter;
use Application\API\Parameters\Validation\ParamValidationResults as ParamValidationResults;
use Application\API\Response\JSONInfoSerializer as JSONInfoSerializer;
use Application\API\ResponsePayload as ResponsePayload;
use Application\API\Traits\JSONRequestInterface as JSONRequestInterface;
use Application\Application as Application;
use Application_CORS as Application_CORS;
use Application_Driver as Application_Driver;
use Application_Interfaces_Loggable as Application_Interfaces_Loggable;
use Application_Request as Application_Request;
use Application_Traits_Loggable as Application_Traits_Loggable;
use Throwable as Throwable;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

abstract class BaseAPIMethod implements APIMethodInterface, Application_Interfaces_Loggable
{
	use Application_Traits_Loggable;

	final public function getID(): string
	{
		/* ... */
	}


	public function getLogIdentifier(): string
	{
		/* ... */
	}


	final public function getRequestTime(): ?Microtime
	{
		/* ... */
	}


	final public function getDocumentationURL(): AdminURLInterface
	{
		/* ... */
	}


	final public function process(): never
	{
		/* ... */
	}


	final public function processReturn(): ResponsePayload|ErrorResponsePayload
	{
		/* ... */
	}


	final public function manageParams(): APIParamManager
	{
		/* ... */
	}


	public function selectVersion(string $version): self
	{
		/* ... */
	}


	public function getValidationResults(): ParamValidationResults
	{
		/* ... */
	}


	public function setRequestBody(string $body): self
	{
		/* ... */
	}


	public function getFilterText(): string
	{
		/* ... */
	}


	final public function allowCORSDomain(string $domain): self
	{
		/* ... */
	}


	final public function getCORS(): Application_CORS
	{
		/* ... */
	}


	public function getActiveVersion(): string
	{
		/* ... */
	}


	public function errorResponse(int $errorCode): ErrorResponse
	{
		/* ... */
	}


	final public function getInfo(): JSONInfoSerializer
	{
		/* ... */
	}


	final public function getRelatedMethods(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Collection/APICacheLocation.php`

```php
namespace Application\AppFactory;

use AppUtils\FileHelper\JSONFile as JSONFile;
use Application\API\Collection\APIMethodIndex as APIMethodIndex;
use Application\CacheControl\BaseCacheLocation as BaseCacheLocation;

/**
 * Cache location description class for the API method index,
 * so it can be handled via the cache control system.
 *
 * @package API
 * @subpackage Method Collection
 */
class APICacheLocation extends BaseCacheLocation
{
	public const CACHE_ID = 'APIMethodIndex';

	public function getID(): string
	{
		/* ... */
	}


	public function getByteSize(): int
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	public function clear(): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Collection/APIMethodCollection.php`

```php
namespace Application\API\Collection;

use AppUtils\Collections\BaseClassLoaderCollectionMulti as BaseClassLoaderCollectionMulti;
use AppUtils\FileHelper\FolderInfo as FolderInfo;
use AppUtils\Interfaces\StringPrimaryRecordInterface as StringPrimaryRecordInterface;
use Application\API\APIManager as APIManager;
use Application\API\APIMethodInterface as APIMethodInterface;
use Application\AppFactory as AppFactory;

/**
 * Collection of all available API methods in the application.
 * This includes APIs from the application framework as well
 * as those provided by the application itself.
 *
 * @package API
 * @subpackage Method Collection
 *
 * @method APIMethodInterface[] getAll()
 * @method APIMethodInterface getByID(string $id)
 */
class APIMethodCollection extends BaseClassLoaderCollectionMulti
{
	public function getInstanceOfClassName(): ?string
	{
		/* ... */
	}


	public function getClassFolders(): array
	{
		/* ... */
	}


	public function isRecursive(): bool
	{
		/* ... */
	}


	public function getDefaultID(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Collection/APIMethodIndex.php`

```php
namespace Application\API\Collection;

use AppUtils\FileHelper as FileHelper;
use AppUtils\FileHelper\JSONFile as JSONFile;
use Application\API\APIException as APIException;
use Application\API\APIManager as APIManager;
use Application\API\APIMethodInterface as APIMethodInterface;
use Application\AppFactory\APICacheLocation as APICacheLocation;
use Application\Application as Application;
use Application_Interfaces_Loggable as Application_Interfaces_Loggable;
use Application_Traits_Loggable as Application_Traits_Loggable;

/**
 * API method indexing module: Creates a cache file on disk
 * that is used at runtime to look up whether a method exists,
 * and to fetch its class name without having to use the
 * {@see APIMethodCollection} to find it.
 *
 * ## Usage
 *
 * Use {@see APIManager::getMethodIndex} to get an instance
 * of this class, and then call {@see methodExists()} to check
 * if a method exists, or {@see getMethodClass()} to get the
 * class name of a method.
 *
 * @package API
 * @subpackage Method Collection
 */
class APIMethodIndex implements Application_Interfaces_Loggable
{
	use Application_Traits_Loggable;

	public function getLogIdentifier(): string
	{
		/* ... */
	}


	/**
	 * @return string[]
	 */
	public function getMethodNames(): array
	{
		/* ... */
	}


	public function methodExists(string $methodName): bool
	{
		/* ... */
	}


	/**
	 * @param class-string<APIMethodInterface> $methodName
	 * @return string
	 * @throws APIException
	 */
	public function getMethodClass(string $methodName): string
	{
		/* ... */
	}


	public function build(): self
	{
		/* ... */
	}


	public function getDataFile(): JSONFile
	{
		/* ... */
	}


	public function getCacheLocation(): APICacheLocation
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Connector/AppAPIConnector.php`

```php
namespace Application\API\Connector;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use AppUtils\ClassHelper as ClassHelper;
use Application\Bootstrap\Screen\APIBootstrap as APIBootstrap;
use Connectors as Connectors;
use Connectors\Connector\BaseConnector as BaseConnector;
use Connectors\Connector\ConnectorException as ConnectorException;
use Connectors\Headers\HTTPHeadersBasket as HTTPHeadersBasket;

class AppAPIConnector extends BaseConnector
{
	public static function create(string $appURL): self
	{
		/* ... */
	}


	public function getURL(): string
	{
		/* ... */
	}


	/**
	 * @param string $methodName
	 * @param array<string,mixed>|ArrayDataCollection $params
	 * @param HTTPHeadersBasket|null $headers Optional headers to include in the request
	 * @return ArrayDataCollection
	 * @throws ConnectorException
	 */
	public function fetchMethodData(
		string $methodName,
		array|ArrayDataCollection $params = [],
		?HTTPHeadersBasket $headers = null,
	): ArrayDataCollection
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Connector/AppAPIMethod.php`

```php
namespace Application\API\Connector;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use AppUtils\ConvertHelper\JSONConverter as JSONConverter;
use Application\API\APIMethodInterface as APIMethodInterface;
use Connectors\Connector\ConnectorException as ConnectorException;
use Connectors\Headers\HTTPHeadersBasket as HTTPHeadersBasket;
use Connectors_Connector_Method_Post as Connectors_Connector_Method_Post;
use Throwable as Throwable;

class AppAPIMethod extends Connectors_Connector_Method_Post
{
	/**
	 * @param string $methodName
	 * @param ArrayDataCollection $params
	 * @param HTTPHeadersBasket|NULL $headers
	 * @return ArrayDataCollection
	 * @throws ConnectorException
	 */
	public function fetchJSON(
		string $methodName,
		ArrayDataCollection $params,
		?HTTPHeadersBasket $headers = null,
	): ArrayDataCollection
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Documentation/APIDocumentation.php`

```php
namespace Application\API\Documentation;

use Application\API\APIManager as APIManager;
use Application\Themes\DefaultTemplate\API\APIMethodsOverviewTmpl as APIMethodsOverviewTmpl;
use UI_Page_Template as UI_Page_Template;

class APIDocumentation extends BaseAPIDocumentation
{
}


```
###  Path: `/src/classes/Application/API/Documentation/BaseAPIDocumentation.php`

```php
namespace Application\API\Documentation;

use UI_Page as UI_Page;
use UI_Page_Template as UI_Page_Template;
use UI_Renderable_Interface as UI_Renderable_Interface;
use UI_Traits_RenderableGeneric as UI_Traits_RenderableGeneric;

abstract class BaseAPIDocumentation implements UI_Renderable_Interface
{
	use UI_Traits_RenderableGeneric;

	public function render(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Documentation/Examples/JSONMethodExample.php`

```php
namespace Application\API\Documentation\Examples;

use AppUtils\ConvertHelper\JSONConverter as JSONConverter;
use AppUtils\Highlighter as Highlighter;
use Application\API\Traits\JSONResponseInterface as JSONResponseInterface;
use Application\API\Traits\JSONResponseTrait as JSONResponseTrait;
use Application\API\Utilities\KeyDescription as KeyDescription;
use UI_Renderable as UI_Renderable;

/**
 * Renders an example JSON response for a given API method implementing {@see JSONResponseInterface},
 * for use in API documentation. It is used by {@see JSONResponseTrait::renderExample()} to fetch
 * the JSON to use.
 *
 * @package API
 * @subpackage Documentation
 */
class JSONMethodExample extends UI_Renderable
{
}


```
###  Path: `/src/classes/Application/API/Documentation/MethodDocumentation.php`

```php
namespace Application\API\Documentation;

use Application\API\APIMethodInterface as APIMethodInterface;
use Application\Themes\DefaultTemplate\API\APIMethodDetailTmpl as APIMethodDetailTmpl;
use UI_Page_Template as UI_Page_Template;

class MethodDocumentation extends BaseAPIDocumentation
{
}


```
###  Path: `/src/classes/Application/API/ErrorResponse.php`

```php
namespace Application\API;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use Application\Application as Application;
use Connectors_ResponseCode as Connectors_ResponseCode;

/**
 * Utility class used to configure and send error responses.
 * This is returned by {@see Application\API\BaseMethods\BaseAPIMethod::errorResponse()}.
 *
 * @package API
 * @subpackage Core
 */
class ErrorResponse
{
	public function toPayload(): ErrorResponsePayload
	{
		/* ... */
	}


	public function getMethod(): APIMethodInterface
	{
		/* ... */
	}


	/**
	 * @param string $message
	 * @param mixed ...$args
	 * @return $this
	 */
	public function setErrorMessage(string $message, ...$args): self
	{
		/* ... */
	}


	public function getErrorMessage(): string
	{
		/* ... */
	}


	/**
	 * @param string $message
	 * @param mixed ...$args Arguments for `sprintf`.
	 * @return void
	 */
	public function appendErrorMessage(string $message, ...$args): void
	{
		/* ... */
	}


	public function getErrorCode(): int
	{
		/* ... */
	}


	/**
	 * @return array<string, mixed>
	 */
	public function getErrorData(): array
	{
		/* ... */
	}


	public function getHttpStatusCode(): int
	{
		/* ... */
	}


	/**
	 * @param array<string, mixed>|ArrayDataCollection|null $data
	 * @return $this
	 */
	public function addData(array|ArrayDataCollection|null $data): self
	{
		/* ... */
	}


	public function setHTTPStatusCode(int $statusCode): self
	{
		/* ... */
	}


	public function makeBadRequest(): self
	{
		/* ... */
	}


	public function makeInternalServerError(): self
	{
		/* ... */
	}


	public function send(): never
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/ErrorResponsePayload.php`

```php
namespace Application\API;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use AppUtils\ConvertHelper\JSONConverter as JSONConverter;
use AppUtils\Interfaces\StringableInterface as StringableInterface;
use Application\API\Response\ResponseInterface as ResponseInterface;

class ErrorResponsePayload extends ArrayDataCollection implements ResponseInterface, StringableInterface
{
	public const KEY_ERROR_CODE = 'errorCode';
	public const KEY_ERROR_MESSAGE = 'errorMessage';
	public const KEY_ERROR_DATA = 'errorData';

	public function getMethod(): APIMethodInterface
	{
		/* ... */
	}


	public function getErrorCode(): int
	{
		/* ... */
	}


	public function getErrorMessage(): string
	{
		/* ... */
	}


	/**
	 * @return array<string, mixed>
	 */
	public function getErrorData(): array
	{
		/* ... */
	}


	public function getAsString(): string
	{
		/* ... */
	}


	public function __toString(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Events/RegisterAPIIndexCacheListener.php`

```php
namespace Application\API\Events;

use Application\API\APIManager as APIManager;
use Application\API\Collection\APIMethodIndex as APIMethodIndex;
use Application\CacheControl\Events\BaseRegisterCacheLocationsListener as BaseRegisterCacheLocationsListener;

/**
 * Registers the API method index cache location.
 *
 * @package Application
 * @subpackage CacheControl
 *
 * @see APIMethodIndex::getCacheLocation()
 */
class RegisterAPIIndexCacheListener extends BaseRegisterCacheLocationsListener
{
}


```
###  Path: `/src/classes/Application/API/Events/RegisterAPIResponseCacheListener.php`

```php
namespace Application\API\Events;

use Application\API\Cache\APIResponseCacheLocation as APIResponseCacheLocation;
use Application\CacheControl\Events\BaseRegisterCacheLocationsListener as BaseRegisterCacheLocationsListener;

/**
 * Registers the API response cache location with the CacheControl system.
 * This listener is discovered automatically — no manual registration required.
 *
 * @package Application
 * @subpackage CacheControl
 * @see APIResponseCacheLocation
 */
class RegisterAPIResponseCacheListener extends BaseRegisterCacheLocationsListener
{
}


```
###  Path: `/src/classes/Application/API/Groups/APIGroupInterface.php`

```php
namespace Application\API\Groups;

interface APIGroupInterface
{
	public function getID(): string;


	public function getLabel(): string;


	public function getDescription(): string;
}


```
###  Path: `/src/classes/Application/API/Groups/FrameworkAPIGroup.php`

```php
namespace Application\API\Groups;

class FrameworkAPIGroup extends GenericAPIGroup
{
	public static function create(): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Groups/GenericAPIGroup.php`

```php
namespace Application\API\Groups;

class GenericAPIGroup implements APIGroupInterface
{
	public function getID(): string
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	public function getDescription(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Response/JSONInfoSerializer.php`

```php
namespace Application\API\Response;

use Application\API\APIMethodInterface as APIMethodInterface;
use Application\API\Traits\JSONResponseTrait as JSONResponseTrait;

/**
 * Helper class that serializes the information on an API method
 * to an array that is included in JSON responses.
 *
 * @package API
 * @subpackage Response
 * @see JSONResponseTrait::_sendJSONData()
 */
class JSONInfoSerializer
{
	public const KEY_REQUEST_MIME = 'requestMime';
	public const KEY_SELECTED_VERSION = 'selectedVersion';
	public const KEY_METHOD_NAME = 'methodName';
	public const KEY_REQUEST_TIME = 'requestTime';
	public const KEY_RESPONSE_MIME = 'responseMime';
	public const KEY_DESCRIPTION = 'description';
	public const KEY_AVAILABLE_VERSIONS = 'availableVersions';
	public const KEY_DOCUMENTATION_URL = 'documentationURL';

	public function toArray(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Response/ResponseInterface.php`

```php
namespace Application\API\Response;

use Application\API\APIMethodInterface as APIMethodInterface;

/**
 * Base interface for all API responses.
 *
 * @package API
 * @subpackage Response
 */
interface ResponseInterface
{
	public function getMethod(): APIMethodInterface;
}


```
###  Path: `/src/classes/Application/API/ResponsePayload.php`

```php
namespace Application\API;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use Application\API\Response\ResponseInterface as ResponseInterface;

class ResponsePayload extends ArrayDataCollection implements ResponseInterface
{
	public function getMethod(): APIMethodInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Traits/DryRun/DryRunAPIParam.php`

```php
namespace Application\API\Traits\DryRun;

use Application\API\Parameters\Type\BooleanParameter as BooleanParameter;

class DryRunAPIParam extends BooleanParameter
{
}


```
###  Path: `/src/classes/Application/API/Traits/DryRunAPIInterface.php`

```php
namespace Application\API\Traits;

use Application\API\APIMethodInterface as APIMethodInterface;
use Application\API\Traits\DryRun\DryRunAPIParam as DryRunAPIParam;

/**
 * Interface for APIs that support dry run functionality.
 * A dry run allows the API to simulate an operation without
 * making any actual changes.
 *
 * Use the trait {@see DryRunAPITrait} to implement this interface.
 *
 * @package API
 * @subpackage Traits
 * @see DryRunAPITrait
 */
interface DryRunAPIInterface extends APIMethodInterface
{
	public const PARAM_DRY_RUN = 'dryRun';
	public const KEY_DRY_RUN = 'dryRun';

	public function selectDryRun(bool $dryRun): self;


	public function getDryRunParam(): ?DryRunAPIParam;


	public function registerDryRunParam(): DryRunAPIParam;


	public function isDryRun(): bool;
}


```
###  Path: `/src/classes/Application/API/Traits/DryRunAPITrait.php`

```php
namespace Application\API\Traits;

use Application\API\Traits\DryRun\DryRunAPIParam as DryRunAPIParam;

/**
 * Trait used to implement the interface {@see DryRunAPIInterface}.
 *
 * @package API
 * @subpackage Traits
 * @see DryRunAPIInterface
 */
trait DryRunAPITrait
{
	public function getDryRunParam(): ?DryRunAPIParam
	{
		/* ... */
	}


	public function registerDryRunParam(): DryRunAPIParam
	{
		/* ... */
	}


	public function selectDryRun(bool $dryRun): self
	{
		/* ... */
	}


	public function isDryRun(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Traits/JSONRequestInterface.php`

```php
namespace Application\API\Traits;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use Application\API\APIMethodInterface as APIMethodInterface;

/**
 * @package API
 * @subpackage Traits
 * @see JSONRequestTrait
 */
interface JSONRequestInterface extends APIMethodInterface
{
	public const ERROR_FAILED_TO_READ_INPUT = 182801;
	public const RESPONSE_KEY_ERROR_JSON_REQUEST_DATA = 'JSONRequest';

	public function getRequestData(): ArrayDataCollection;
}


```
###  Path: `/src/classes/Application/API/Traits/JSONRequestTrait.php`

```php
namespace Application\API\Traits;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use AppUtils\ConvertHelper\JSONConverter\JSONConverterException as JSONConverterException;

/**
 * @package API
 * @subpackage Traits
 * @see JSONRequestInterface
 */
trait JSONRequestTrait
{
	public function getRequestData(): ArrayDataCollection
	{
		/* ... */
	}


	public function getRequestMime(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Traits/JSONResponseInterface.php`

```php
namespace Application\API\Traits;

use Application\API\APIMethodInterface as APIMethodInterface;
use Application\API\Utilities\KeyDescription as KeyDescription;

/**
 * @package API
 * @subpackage Traits
 * @see JSONResponseTrait
 */
interface JSONResponseInterface extends APIMethodInterface
{
	public const RESPONSE_KEY_API = 'api';
	public const RESPONSE_KEY_STATE = 'state';
	public const RESPONSE_KEY_CODE = 'code';
	public const RESPONSE_KEY_DATA = 'data';
	public const RESPONSE_KEY_MESSAGE = 'message';
	public const RESPONSE_STATE_ERROR = 'error';
	public const RESPONSE_STATE_SUCCESS = 'success';

	/**
	 * @return array<string, mixed>
	 */
	public function getExampleJSONResponse(): array;


	/**
	 * @return KeyDescription[]
	 */
	public function getReponseKeyDescriptions(): array;
}


```
###  Path: `/src/classes/Application/API/Traits/JSONResponseTrait.php`

```php
namespace Application\API\Traits;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use AppUtils\ConvertHelper\JSONConverter as JSONConverter;
use Application\API\Documentation\Examples\JSONMethodExample as JSONMethodExample;
use Application\API\ErrorResponse as ErrorResponse;
use Application\API\Parameters\Validation\ParamValidationResults as ParamValidationResults;

/**
 * Trait used to implement JSON response handling in API methods,
 * by implementing the interface {@see JSONResponseInterface}.
 *
 * @package API
 * @subpackage Traits
 *
 * @see JSONResponseInterface
 */
trait JSONResponseTrait
{
	public function getResponseMime(): string
	{
		/* ... */
	}


	public function renderExample(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Traits/JSONResponseWithExampleInterface.php`

```php
namespace Application\API\Traits;

interface JSONResponseWithExampleInterface extends JSONResponseInterface
{
	public function isExampleResponse(): bool;
}


```
###  Path: `/src/classes/Application/API/Traits/JSONResponseWithExampleTrait.php`

```php
namespace Application\API\Traits;

use AppUtils\ArrayDataCollection as ArrayDataCollection;

trait JSONResponseWithExampleTrait
{
	public function isExampleResponse(): bool
	{
		/* ... */
	}


	public function getExampleJSONResponse(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Traits/RequestRequestInterface.php`

```php
namespace Application\API\Traits;

interface RequestRequestInterface
{
}


```
###  Path: `/src/classes/Application/API/Traits/RequestRequestTrait.php`

```php
namespace Application\API\Traits;

trait RequestRequestTrait
{
	public function getRequestMime(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/User/APIRightsInterface.php`

```php
namespace Application\API\User;

interface APIRightsInterface
{
	public const GROUP_API = 'API';
	public const RIGHT_VIEW_API_CLIENTS = 'ViewAPIClients';
	public const RIGHT_EDIT_API_CLIENTS = 'EditAPIClients';
	public const RIGHT_DELETE_API_CLIENTS = 'DeleteAPIClients';
	public const RIGHT_CREATE_API_CLIENTS = 'CreateAPIClients';
}


```
###  Path: `/src/classes/Application/API/User/APIRightsTrait.php`

```php
namespace Application\API\User;

use Application_User_Rights as Application_User_Rights;
use Application_User_Rights_Group as Application_User_Rights_Group;

/**
 * Trait used to implement the rights for the API clients module.
 *
 * @package API
 * @subpackage User
 *
 * @see APIRightsInterface
 */
trait APIRightsTrait
{
	public function canEditAPIClients(): bool
	{
		/* ... */
	}


	public function canViewAPIClients(): bool
	{
		/* ... */
	}


	public function canDeleteAPIClients(): bool
	{
		/* ... */
	}


	public function canCreateAPIClients(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Utilities/KeyDescription.php`

```php
namespace Application\API\Utilities;

use Application\API\APIManager as APIManager;
use Application\MarkdownRenderer\MarkdownRenderer as MarkdownRenderer;

/**
 * Utility class used to describe an API response key with its path and a description.
 *
 * @package API
 * @subpackage Utilities
 */
class KeyDescription implements KeyPathInterface
{
	public KeyPath $path;
	public string $description;


	/**
	 * @param string|KeyPath $path
	 * @param string $description Markdown-enabled description of the key.
	 * @param mixed ...$args Optional arguments to be used with sprintf to format the description.
	 * @return self
	 */
	public static function create(string|KeyPath $path, string $description, ...$args): self
	{
		/* ... */
	}


	public function getPath(): string
	{
		/* ... */
	}


	public function getDescription(): string
	{
		/* ... */
	}


	public function renderDescription(): string
	{
		/* ... */
	}


	public function __toString(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Utilities/KeyPath.php`

```php
namespace Application\API\Utilities;

class KeyPath implements KeyPathInterface
{
	public static function create(string|KeyPath $component): self
	{
		/* ... */
	}


	public function add(string $component): self
	{
		/* ... */
	}


	public function getPath(): string
	{
		/* ... */
	}


	public function __toString(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Utilities/KeyPathInterface.php`

```php
namespace Application\API\Utilities;

use AppUtils\Interfaces\StringableInterface as StringableInterface;

interface KeyPathInterface extends StringableInterface
{
	public function getPath(): string;
}


```
###  Path: `/src/classes/Application/API/Utilities/KeyReplacement.php`

```php
namespace Application\API\Utilities;

class KeyReplacement implements KeyPathInterface
{
	public function getPath(): string
	{
		/* ... */
	}


	public function getOldKey(): string
	{
		/* ... */
	}


	public function getNewKey(): string
	{
		/* ... */
	}


	public static function create(string|KeyPath $oldKey, string|KeyPath $newKey): self
	{
		/* ... */
	}


	public function __toString(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Versioning/APIVersionInterface.php`

```php
namespace Application\API\Versioning;

use AppUtils\Interfaces\StringPrimaryRecordInterface as StringPrimaryRecordInterface;
use Application\API\APIMethodInterface as APIMethodInterface;
use Application\API\Utilities\KeyPath as KeyPath;
use Application\API\Utilities\KeyReplacement as KeyReplacement;

/**
 * Interface for a specific version of an API method.
 * A base implementation is provided by {@see BaseAPIVersion}.
 *
 * @package API
 * @subpackage Versioning
 */
interface APIVersionInterface extends StringPrimaryRecordInterface
{
	public function getMethod(): APIMethodInterface;


	public function getVersion(): string;


	/**
	 * Markdown-formatted changelog of this version.
	 * @return string
	 */
	public function getChangelog(): string;


	/**
	 * List of keys (use dot notation for paths) that are deprecated in this version,
	 * with replacement key or NULL if no replacement exists.
	 *
	 * @return array<int,KeyPath|KeyReplacement>
	 */
	public function getDeprecatedKeys(): array;


	/**
	 * List of keys (use dot notation for paths) that are removed in this version.
	 *
	 * > NOTE: These keys should have been marked as deprecated in a previous version,
	 * > which is why there is no replacement key here.
	 *
	 * @return array<int,KeyPath|KeyReplacement>
	 */
	public function getRemovedKeys(): array;
}


```
###  Path: `/src/classes/Application/API/Versioning/BaseAPIVersion.php`

```php
namespace Application\API\Versioning;

use Application\API\APIMethodInterface as APIMethodInterface;
use Application\API\Utilities\KeyPath as KeyPath;
use Application\API\Utilities\KeyPathInterface as KeyPathInterface;
use Application\API\Utilities\KeyReplacement as KeyReplacement;

/**
 * Abstract base class for API versions. Used by API methods that
 * use {@see VersionedAPIInterface} to implement their versioning.
 *
 * @package API
 * @subpackage Versioning
 */
abstract class BaseAPIVersion implements APIVersionInterface
{
	public function getID(): string
	{
		/* ... */
	}


	public function getMethod(): APIMethodInterface
	{
		/* ... */
	}


	public function getChangelog(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Versioning/VersionCollection.php`

```php
namespace Application\API\Versioning;

use AppUtils\ClassHelper as ClassHelper;
use AppUtils\Collections\BaseClassLoaderCollection as BaseClassLoaderCollection;
use AppUtils\FileHelper\FolderInfo as FolderInfo;
use AppUtils\Interfaces\StringPrimaryRecordInterface as StringPrimaryRecordInterface;

/**
 * Collection of all API versions available for a specific API method
 * that uses versioning via {@see VersionedAPITrait}. Uses ClassHelper
 * class loading to dynamically load all version classes in the
 * version folder of the method.
 *
 * @package API
 * @subpackage Versioning
 *
 * @method APIVersionInterface[] getAll()
 * @method APIVersionInterface getByID(string $id)
 * @method APIVersionInterface getDefault()
 */
class VersionCollection extends BaseClassLoaderCollection
{
	public function getInstanceOfClassName(): string
	{
		/* ... */
	}


	public function isRecursive(): bool
	{
		/* ... */
	}


	public function getClassesFolder(): FolderInfo
	{
		/* ... */
	}


	public function getDefaultID(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Versioning/VersionedAPIInterface.php`

```php
namespace Application\API\Versioning;

use AppUtils\FileHelper\FolderInfo as FolderInfo;
use Application\API\APIMethodInterface as APIMethodInterface;

/**
 * Interface for an API method that uses version handling using
 * separate version classes. This allows more granular control
 * over the response data for each version.
 *
 * Can be added to an existing API method with the trait {@see VersionedAPITrait}.
 *
 * ## Usage
 *
 * 1. Implement this interface in your API method class.
 * 2. Use the {@see VersionedAPITrait} trait in your API method class.
 * 3. Create a folder for the API's version classes.
 * 4. Return the folder in {@see self::getVersionFolder()}.
 * 5. Create a class for each version, extending {@see BaseAPIVersion}.
 *
 * > Typically, you would create an abstract base class for your API's versions,
 * > which builds the base response, and then extend that class for each version,
 * > adding or removing fields as needed.
 *
 * @package API
 * @subpackage Versioning
 * @see VersionedAPITrait
 */
interface VersionedAPIInterface extends APIMethodInterface
{
	public function getVersionFolder(): FolderInfo;


	public function getVersionCollection(): VersionCollection;
}


```
###  Path: `/src/classes/Application/API/Versioning/VersionedAPITrait.php`

```php
namespace Application\API\Versioning;

use AppUtils\ArrayDataCollection as ArrayDataCollection;

/**
 * Trait used to implement {@see VersionedAPIInterface} in an API method,
 * and add version handling using separate version classes.
 *
 * ----------------------------------------------------------------
 * For more documentation, see {@see VersionedAPIInterface}.
 * ----------------------------------------------------------------
 *
 * @package API
 * @subpackage Versioning
 * @see VersionedAPIInterface
 */
trait VersionedAPITrait
{
	final public function getVersionCollection(): VersionCollection
	{
		/* ... */
	}


	final public function getVersions(): array
	{
		/* ... */
	}


	final public function getChangelog(): array
	{
		/* ... */
	}
}


```
---
**File Statistics**
- **Size**: 45.38 KB
- **Lines**: 2072
File: `modules/api/architecture-core.md`
