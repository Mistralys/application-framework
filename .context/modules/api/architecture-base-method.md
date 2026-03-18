# API - Base Method & Traits (Public API)
_SOURCE: BaseAPIMethod lifecycle, JSONResponseInterface/Trait, JSONRequestInterface/Trait, DryRunAPIInterface/Trait, JSONResponseWithExampleInterface/Trait, RequestRequestInterface/Trait_
# BaseAPIMethod lifecycle, JSONResponseInterface/Trait, JSONRequestInterface/Trait, DryRunAPIInterface/Trait, JSONResponseWithExampleInterface/Trait, RequestRequestInterface/Trait
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── API/
                └── BaseMethods/
                    ├── BaseAPIMethod.php
                └── Traits/
                    └── DryRun/
                        ├── DryRunAPIParam.php
                    └── DryRunAPIInterface.php
                    └── DryRunAPITrait.php
                    └── JSONRequestInterface.php
                    └── JSONRequestTrait.php
                    └── JSONResponseInterface.php
                    └── JSONResponseTrait.php
                    └── JSONResponseWithExampleInterface.php
                    └── JSONResponseWithExampleTrait.php
                    └── RequestRequestInterface.php
                    └── RequestRequestTrait.php

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


	/**
	 * @return class-string<ResponsePayload>
	 */
	protected function getResponseClass(): string
	{
		/* ... */
	}


	/**
	 * Used to give a method the opportunity to configure request
	 * parameters before it is processed. Note: use the
	 * {@see self::addParam()} method to add parameters.
	 */
	abstract protected function init(): void;


	final public function manageParams(): APIParamManager
	{
		/* ... */
	}


	public function selectVersion(string $version): self
	{
		/* ... */
	}


	final protected function addParam(string $name, string|StringableInterface $label): ParamTypeSelector
	{
		/* ... */
	}


	/**
	 * Retrieves the value of a request parameter. Alias for the
	 * request's getParam Method.
	 *
	 * @param string $name
	 * @param string|array<int|string,mixed>|int|float|bool|NULL $default
	 * @return mixed|NULL
	 */
	final protected function getParam(string $name, $default = null): mixed
	{
		/* ... */
	}


	/**
	 * Validates the parameters of the method to make sure all required
	 * request parameters are present and valid.
	 */
	final protected function validate(): void
	{
		/* ... */
	}


	public function getValidationResults(): ParamValidationResults
	{
		/* ... */
	}


	abstract protected function configureValidationErrorResponse(
		ErrorResponse $response,
		ParamValidationResults $results,
	): void;


	protected function isSimulation(): bool
	{
		/* ... */
	}


	/**
	 * Utility method: Fetches the raw request body.
	 * @return string
	 */
	final protected function getRequestBody(): string
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


	/**
	 * Fetch all required data from the request before
	 * building the response, to ensure everything is
	 * present.
	 *
	 * Store all needed data internally in the method for
	 * use in the {@see self::collectResponseData()} method.
	 *
	 * In case of an error, use {@see self::errorResponse()}.
	 *
	 * @param string $version The API version for which to collect data.
	 * @return void
	 */
	abstract protected function collectRequestData(string $version): void;


	/**
	 * Add the method's custom response data to the response object,
	 * if relevant for the response format of this method (an HTML-only
	 * method does not need to do anything here, for example).
	 *
	 * @param ArrayDataCollection $response
	 * @param string $version The API version for which to generate the response.
	 * @return void
	 */
	abstract protected function collectResponseData(ArrayDataCollection $response, string $version): void;


	/**
	 * Implementation for sending an error response for the specific
	 * data format.
	 *
	 * > NOTE: The HTTP status code has already been sent in the header
	 * > at this point. This method is only responsible for sending
	 * > the body of the response, if any.
	 *
	 * @param ErrorResponse $response
	 * @return void
	 */
	abstract protected function _sendErrorResponse(ErrorResponse $response): void;


	public function errorResponse(int $errorCode): ErrorResponse
	{
		/* ... */
	}


	protected function errorResponseBadRequest(): ErrorResponse
	{
		/* ... */
	}


	/**
	 * Can be used to collect additional data to be added
	 * to the error response. By default, returns an empty
	 * array.
	 *
	 * @return array<int|string,mixed>
	 */
	protected function collectRequestErrorData(): array
	{
		/* ... */
	}


	/**
	 * @param ArrayDataCollection $data
	 * @return void
	 */
	abstract protected function _sendSuccessResponse(ArrayDataCollection $data): void;


	final public function getInfo(): JSONInfoSerializer
	{
		/* ... */
	}


	final protected function processExit(): never
	{
		/* ... */
	}


	final public function getRelatedMethods(): array
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
	protected function collectRequestData(string $version): void
	{
		/* ... */
	}


	public function getRequestData(): ArrayDataCollection
	{
		/* ... */
	}


	public function getRequestMime(): string
	{
		/* ... */
	}


	protected function collectRequestErrorData(): array
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


	protected function _sendSuccessResponse(ArrayDataCollection $data): void
	{
		/* ... */
	}


	protected function _sendErrorResponse(ErrorResponse $response): void
	{
		/* ... */
	}


	protected function configureValidationErrorResponse(ErrorResponse $response, ParamValidationResults $results): void
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