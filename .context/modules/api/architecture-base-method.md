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
                    └── PaginatedAPIInterface.php
                    └── PaginatedAPITrait.php
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
use Application\API\Parameters\Removed\RemovedParameter as RemovedParameter;
use Application\API\Parameters\Reserved\APIMethodParameter as APIMethodParameter;
use Application\API\Parameters\Reserved\APIVersionParameter as APIVersionParameter;
use Application\API\Parameters\Validation\ParamValidationResults as ParamValidationResults;
use Application\API\Request\RequestBodyException as RequestBodyException;
use Application\API\Request\RequestBodyReader as RequestBodyReader;
use Application\API\Response\JSONInfoSerializer as JSONInfoSerializer;
use Application\API\ResponsePayload as ResponsePayload;
use Application\API\Traits\JSONRequestInterface as JSONRequestInterface;
use Application\Application as Application;
use Application\ConfigSettings\AppConfig as AppConfig;
use Application_CORS as Application_CORS;
use Application_Driver as Application_Driver;
use Application_ErrorLog_Log_Entry_Exception as Application_ErrorLog_Log_Entry_Exception;
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
	 * Override this method to register method-specific parameters and
	 * perform any other one-time setup required by the method.
	 *
	 * Use {@see self::addParam()} to add parameters.
	 *
	 * NOTE: Reserved parameters (API method name, version, and — when
	 * {@see APIKeyMethodInterface} is implemented — the API key parameter)
	 * are already registered by {@see self::initReservedParams()} before
	 * this method is called. Do NOT re-register them here.
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
	 * Declares a request parameter name that this method used to accept,
	 * but has since removed: a request still sending it is rejected with
	 * {@see APIMethodInterface::ERROR_REMOVED_PARAMETER_USED} by
	 * {@see self::rejectRemovedParams()}, instead of the method having to
	 * hand-write a guard for it in {@see self::collectRequestData()}.
	 *
	 * @param string $name
	 * @return RemovedParameter
	 */
	final protected function addRemovedParam(string $name): RemovedParameter
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
	 *
	 * A {@see self::setRequestBody()} preset bypasses {@see self::createRequestBodyReader()}
	 * entirely — it is already in memory, under test control, and was never
	 * subject to the ceiling a real request body is read under. Otherwise,
	 * the body is read through the factory's {@see RequestBodyReader}, which
	 * enforces the configured byte ceiling and detects a `post_max_size`
	 * discard; a {@see RequestBodyException} it throws is converted here into
	 * the matching client-facing error response.
	 *
	 * @return string
	 */
	final protected function getRequestBody(): string
	{
		/* ... */
	}


	/**
	 * Creates the reader {@see self::getRequestBody()} reads the raw request
	 * body through. Defaults to a reader over the real request
	 * (`php://input`, `$_SERVER['CONTENT_LENGTH']`) under the effective
	 * ceiling: the lower of the configured ceiling
	 * ({@see AppConfig::getAPIMaxRequestBodySize()}) and the server's
	 * `post_max_size` ({@see RequestBodyReader::resolveEffectiveCeiling()}) —
	 * so the ceiling reported to a client via `maxRequestBodySize` is one it
	 * can actually reach.
	 *
	 * Overridable so an in-process test can lower the effective ceiling to
	 * exercise {@see RequestBodyReader}'s `read()` path at a tiny size —
	 * the booted ceiling cannot be lowered from a test. A
	 * {@see self::setRequestBody()} preset bypasses this factory entirely;
	 * see {@see self::getRequestBody()}.
	 *
	 * @return RequestBodyReader
	 */
	protected function createRequestBodyReader(): RequestBodyReader
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
	 * @inheritDoc
	 */
	final public function getSafeActiveVersion(): string
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
	 * Can be used to collect additional request-derived data to be added
	 * to the error response, e.g. the parsed request body. By default,
	 * returns an empty array.
	 *
	 * SECURITY: Routed through {@see ErrorResponse::addRequestData()} — the
	 * dedicated, redaction-subject channel — not {@see ErrorResponse::addData()}.
	 * An override returning request-derived content (see {@see \Application\API\Traits\JSONRequestTrait}
	 * for the framework's own example) is therefore automatically dropped
	 * from a production response by {@see ErrorResponse::getErrorData()},
	 * regardless of what this method returns. An API key itself can never
	 * appear here: it is a header-only parameter (see the contract comment
	 * on {@see \Application\API\Clients\API\Params\APIKeyParam::getHeaderValue()})
	 * and is never part of the request body or `$_REQUEST`.
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

	/**
	 * Thrown by {@see \Application\API\BaseMethods\BaseAPIMethod::getRequestBody()}
	 * when the request body exceeds the configured ceiling
	 * ({@see \Application\ConfigSettings\AppConfig::getAPIMaxRequestBodySize()}),
	 * whether the body was rejected outright (declared or actual length over
	 * the ceiling) or PHP silently discarded it (`post_max_size`). Both
	 * causes map to this single code — and HTTP 413 via
	 * {@see \Application\API\ErrorResponse::makePayloadTooLarge()} — since the
	 * client action is identical in either case: send a smaller body.
	 */
	public const ERROR_REQUEST_BODY_TOO_LARGE = 182802;

	/**
	 * Error-data key carrying the configured byte ceiling
	 * ({@see \Application\ConfigSettings\AppConfig::getAPIMaxRequestBodySize()})
	 * alongside {@see self::ERROR_REQUEST_BODY_TOO_LARGE}, so a client can
	 * tell by how much its request needs to shrink without parsing the
	 * error message.
	 */
	public const RESPONSE_KEY_ERROR_MAX_REQUEST_BODY_SIZE = 'maxRequestBodySize';
	public const RESPONSE_KEY_ERROR_JSON_REQUEST_DATA = 'JSONRequest';

	/**
	 * Sub-key of {@see self::RESPONSE_KEY_ERROR_JSON_REQUEST_DATA} used when
	 * the request body could not be parsed (invalid JSON, or a top level
	 * that is not an object): carries the echo form of the raw body
	 * recorded once by {@see JSONRequestTrait::getRequestData()} — the raw
	 * body verbatim when it is at most `ECHO_MAX_STRING_LENGTH` characters,
	 * otherwise `[N characters truncated]`. Never re-reads or re-parses
	 * the body, so it cannot re-enter the parse on a repeated error response.
	 */
	public const RESPONSE_KEY_ERROR_UNPARSEABLE_BODY = 'unparseableBody';

	public function getRequestData(): ArrayDataCollection;
}


```
###  Path: `/src/classes/Application/API/Traits/JSONRequestTrait.php`

```php
namespace Application\API\Traits;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use AppUtils\ConvertHelper\JSONConverter as JSONConverter;
use AppUtils\ConvertHelper\JSONConverter\JSONConverterException as JSONConverterException;
use Application\Application as Application;

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


	/**
	 * Lazily parses and returns the JSON request body, once per request.
	 *
	 * Re-entry guard (audit cycle 3, Major 1): {@see \Application\API\BaseMethods\BaseAPIMethod::errorResponse()}
	 * calls {@see self::collectRequestErrorData()} while building every
	 * error response, which in a development environment reads this very
	 * method. Without a guard, an unparseable body would recurse without
	 * end: the first call assigns a provisional empty collection to
	 * `$requestData` *before* reading or parsing the body, so a reentrant
	 * call triggered while building the resulting error response sees that
	 * already non-null collection and returns it immediately, instead of
	 * reading or parsing the body a second time.
	 *
	 * @return ArrayDataCollection
	 */
	public function getRequestData(): ArrayDataCollection
	{
		/* ... */
	}


	public function getRequestMime(): string
	{
		/* ... */
	}


	/**
	 * SECURITY: This echoes the parsed request body verbatim, which is
	 * genuinely useful for local debugging but must never reach a client
	 * outside a development environment — a request body can legitimately
	 * contain sensitive domain data. Gated on {@see Application::isDevelEnvironment()}
	 * here at the trait level (an empty array short-circuits the contribution
	 * entirely), and independently gated again by {@see \Application\API\ErrorResponse::getErrorData()}
	 * on the same check, since this method's return value is routed through
	 * {@see \Application\API\ErrorResponse::addRequestData()} — the
	 * dedicated, redaction-subject channel — not {@see \Application\API\ErrorResponse::addData()}.
	 * An API key never appears here regardless: it is a header-only
	 * parameter (see the contract comment on
	 * {@see \Application\API\Clients\API\Params\APIKeyParam::getHeaderValue()})
	 * and is never part of the request body this trait parses.
	 *
	 * Every string value echoed here, at any nesting depth, is truncated to
	 * {@see self::ECHO_MAX_STRING_LENGTH} characters — covering registered
	 * and unregistered keys alike (e.g. a mistyped `fileContent`), with no
	 * parameter lookup involved.
	 *
	 * @return array<string,mixed>
	 */
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
	public function getResponseKeyDescriptions(): array;
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
###  Path: `/src/classes/Application/API/Traits/PaginatedAPIInterface.php`

```php
namespace Application\API\Traits;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use Application\API\APIMethodInterface as APIMethodInterface;
use Application\API\Utilities\KeyDescription as KeyDescription;
use Application_FilterCriteria as Application_FilterCriteria;

/**
 * Interface for API methods that support paging a list result through a
 * `limit`/`offset` pair, with a `totalCount` response key independent of
 * paging — matching the existing HCP Editor response shape of
 * `GetMailingsAPI`/`GetComtypesAPI`, so those can adopt this piece later
 * without a breaking response change.
 *
 * An out-of-range `limit` or a negative `offset` is **rejected**, never
 * silently clamped — a client must never mistake a truncated page for a
 * complete one.
 *
 * > **Note:** This rejection only covers numeric out-of-range values. A
 * > non-numeric `limit`/`offset` (e.g. `abc`) is not rejected with
 * > {@see self::VALIDATION_INVALID_LIMIT}/{@see self::VALIDATION_INVALID_OFFSET} —
 * > it is treated as an `IntegerParameter` warning and silently falls back
 * > to the configured default via null-coalescing, inherited from
 * > `IntegerParameter`'s own non-numeric-value handling.
 *
 * Use the trait {@see PaginatedAPITrait} to implement this interface.
 *
 * @package API
 * @subpackage Traits
 * @see PaginatedAPITrait
 */
interface PaginatedAPIInterface extends APIMethodInterface
{
	public const PARAM_LIMIT = 'limit';
	public const PARAM_OFFSET = 'offset';
	public const KEY_LIMIT = 'limit';
	public const KEY_OFFSET = 'offset';
	public const KEY_TOTAL_COUNT = 'totalCount';

	/**
	 * The submitted `limit` is outside the `1`-`$maxLimit` range configured
	 * via {@see self::registerPaginationParams()}.
	 */
	public const VALIDATION_INVALID_LIMIT = 183701;

	/** The submitted `offset` is negative. */
	public const VALIDATION_INVALID_OFFSET = 183702;

	/**
	 * Registers the `limit` and `offset` parameters.
	 *
	 * @param int $defaultLimit Used for {@see self::getPaginationLimit()} when `limit` is not submitted.
	 * @param int $maxLimit The maximum value accepted for `limit`; a higher value is rejected with {@see self::VALIDATION_INVALID_LIMIT}.
	 * @return $this
	 */
	public function registerPaginationParams(int $defaultLimit, int $maxLimit): self;


	/**
	 * The resolved `limit` value: the submitted value, or the configured
	 * default when `limit` was not submitted.
	 *
	 * @return int
	 */
	public function getPaginationLimit(): int;


	/**
	 * The resolved `offset` value: the submitted value, or `0` when
	 * `offset` was not submitted.
	 *
	 * @return int
	 */
	public function getPaginationOffset(): int;


	/**
	 * Applies the resolved `limit`/`offset` to the given filter criteria,
	 * through its public {@see Application_FilterCriteria::setLimit()} signature only.
	 *
	 * @param Application_FilterCriteria $criteria
	 * @return $this
	 */
	public function applyPagination(Application_FilterCriteria $criteria): self;


	/**
	 * Writes the `limit`, `offset` and `totalCount` response keys.
	 *
	 * @param ArrayDataCollection $response
	 * @param int $totalCount Total number of matching results, independent of paging.
	 * @return $this
	 */
	public function addPaginationResponseKeys(ArrayDataCollection $response, int $totalCount): self;


	/**
	 * Key descriptions for the `limit`, `offset` and `totalCount` response
	 * keys, for a consuming method to merge into its own
	 * `getResponseKeyDescriptions()`.
	 *
	 * @return KeyDescription[]
	 */
	public function getPaginationKeyDescriptions(): array;
}


```
###  Path: `/src/classes/Application/API/Traits/PaginatedAPITrait.php`

```php
namespace Application\API\Traits;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use AppUtils\OperationResult as OperationResult;
use Application\API\Parameters\APIParameterInterface as APIParameterInterface;
use Application\API\Parameters\Type\IntegerParameter as IntegerParameter;
use Application\API\Utilities\KeyDescription as KeyDescription;
use Application_FilterCriteria as Application_FilterCriteria;

/**
 * Trait used to implement the interface {@see PaginatedAPIInterface}.
 *
 * @package API
 * @subpackage Traits
 * @see PaginatedAPIInterface
 */
trait PaginatedAPITrait
{
	public function registerPaginationParams(int $defaultLimit, int $maxLimit): self
	{
		/* ... */
	}


	/**
	 * NOTE: {@see IntegerParameter::resolveValue()} resolves an absent
	 * parameter to `null` regardless of any configured default value (its
	 * own `setDefaultValue()` only ever takes effect through
	 * {@see \Application\API\Parameters\BaseAPIParameter::getDefaultValue()},
	 * which is reached only when validation itself fails — never merely
	 * because the value is absent). The configured default is therefore
	 * applied here explicitly, rather than relying on the parameter's own
	 * default-value plumbing.
	 */
	public function getPaginationLimit(): int
	{
		/* ... */
	}


	public function getPaginationOffset(): int
	{
		/* ... */
	}


	public function applyPagination(Application_FilterCriteria $criteria): self
	{
		/* ... */
	}


	public function addPaginationResponseKeys(ArrayDataCollection $response, int $totalCount): self
	{
		/* ... */
	}


	public function getPaginationKeyDescriptions(): array
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