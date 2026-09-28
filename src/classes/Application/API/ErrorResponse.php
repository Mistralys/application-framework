<?php
/**
 * @package API
 * @subpackage Core
 */

declare(strict_types=1);

namespace Application\API;

use Application\API\Parameters\APIParameterInterface;
use Application\Application;
use AppUtils\ArrayDataCollection;
use AppUtils\OperationResult;
use Connectors_ResponseCode;

/**
 * Utility class used to configure and send error responses.
 * This is returned by {@see Application\API\BaseMethods\BaseAPIMethod::errorResponse()}.
 *
 * @package API
 * @subpackage Core
 */
class ErrorResponse
{
    private int $httpStatusCode = Connectors_ResponseCode::HTTP_BAD_REQUEST;
    private int $errorCode;
    /**
     * @var callable
     */
    private $sendCallback;

    /**
     * @var array<string, mixed> $errorData Additional data to include in the error response
     */
    private array $errorData = array();

    /**
     * @var array<string, mixed> $requestData Request-derived data, kept in a
     *      dedicated channel so {@see self::getErrorData()} can redact it
     *      independently of {@see self::$errorData} — see {@see self::addRequestData()}.
     */
    private array $requestData = array();
    private string $message = '';
    private APIMethodInterface $method;

    /**
     * @param APIMethodInterface $method
     * @param int $errorCode
     * @param callable $sendCallback {@see Application\API\BaseMethods\BaseAPIMethod::sendErrorResponse()}
     */
    public function __construct(APIMethodInterface $method, int $errorCode, callable $sendCallback)
    {
        $this->method = $method;
        $this->errorCode = $errorCode;
        $this->sendCallback = $sendCallback;
    }

    public function toPayload() : ErrorResponsePayload
    {
        return new ErrorResponsePayload($this);
    }

    public function getMethod(): APIMethodInterface
    {
        return $this->method;
    }

    /**
     * @param string $message
     * @param mixed ...$args
     * @return $this
     */
    public function setErrorMessage(string $message, ...$args) : self
    {
        $this->message = sprintf($message, ...$args);
        return $this;
    }

    public function getErrorMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @param mixed ...$args Arguments for `sprintf`.
     * @return void
     */
    public function appendErrorMessage(string $message, ...$args) : void
    {
        if($this->message !== '') {
            $this->message .= ' ';
        }

        $this->message .= ltrim(sprintf($message, ...$args));
    }

    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * The single chokepoint both response paths ({@see self::send()} via
     * {@see \Application\API\ErrorResponsePayload} over live HTTP, and
     * {@see \Application\API\BaseMethods\BaseAPIMethod::processReturn()}
     * via the same payload) read the error body through.
     *
     * SECURITY: This is where request-derived content is redacted before
     * it can reach a client. {@see self::$errorData} (populated only via
     * {@see self::addData()}) is method-owned domain data and is returned
     * unfiltered. {@see self::$requestData} (populated only via
     * {@see self::addRequestData()} — e.g. the raw `$_REQUEST` under
     * {@see APIMethodInterface::RESPONSE_KEY_ERROR_REQUEST_DATA}, or the
     * parsed JSON request body under {@see \Application\API\Traits\JSONRequestInterface::RESPONSE_KEY_ERROR_JSON_REQUEST_DATA})
     * is request-derived: outside a development environment
     * ({@see Application::isDevelEnvironment()}), every key it contributed
     * is dropped and replaced with a single minimal, non-sensitive
     * `requestData` allowlist (method name + API version only). In a
     * development environment, its content passes through unchanged. An
     * API key can never appear in either channel: it is a header-only
     * parameter (see the contract comment on
     * {@see \Application\API\Clients\API\Params\APIKeyParam::getHeaderValue()})
     * and is never part of `$_REQUEST` or a JSON request body.
     *
     * @return array<string, mixed>
     */
    public function getErrorData(): array
    {
        $this->errorData['validationMessages'] = array();
        $this->errorData['validationErrors'] = array();

        foreach($this->method->getValidationResults()->getResults() as $result) {
            $this->errorData['validationMessages'][] = (string)$result;

            if($result->isError()) {
                $this->errorData['validationErrors'][] = $this->serializeValidationError($result);
            }
        }

        if(Application::isDevelEnvironment()) {
            // Devel environment: pass every request-derived key through as
            // collected — e.g. both 'requestData' ($_REQUEST) and
            // 'JSONRequest' (the parsed body) when both were contributed.
            $this->errorData = array_merge($this->errorData, $this->requestData);
        } else {
            // Production: replace the request-derived channel's content
            // wholesale with a minimal, non-sensitive allowlist. Any other
            // key it contributed (e.g. 'JSONRequest') is dropped entirely
            // by simply never being merged into $this->errorData.
            $this->errorData[APIMethodInterface::RESPONSE_KEY_ERROR_REQUEST_DATA] = array(
                APIMethodInterface::REQUEST_PARAM_METHOD => $this->method->getMethodName(),
                // Throw-safe accessor: this is the error-response path itself, so a
                // second failure here must never escape unhandled.
                APIMethodInterface::REQUEST_PARAM_API_VERSION => $this->method->getSafeActiveVersion(),
            );
        }

        return $this->errorData;
    }

    /**
     * @param OperationResult $result
     * @return array{param: string|null, code: int, message: string}
     */
    private function serializeValidationError(OperationResult $result) : array
    {
        $subject = $result->getSubject();
        $param = null;

        if($subject instanceof APIParameterInterface) {
            $param = $subject->getName();
        }

        return array(
            'param' => $param,
            'code' => $result->getCode(),
            'message' => $result->getErrorMessage()
        );
    }

    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }

    /**
     * Adds method-owned domain data to the error response (e.g.
     * `FinalizeMailingAPI`'s validation `isValid` flag) — returned to the
     * client via {@see self::getErrorData()} unfiltered and unredacted.
     *
     * SECURITY: Never route request-derived content (raw `$_REQUEST`, a
     * parsed request body, or anything else sourced from the incoming
     * request rather than authored by the method itself) through this
     * channel — use {@see self::addRequestData()} instead, which is
     * subject to redaction in production. An API key can never legitimately
     * end up here either way: it is a header-only parameter (see the
     * contract comment on {@see \Application\API\Clients\API\Params\APIKeyParam::getHeaderValue()}).
     *
     * @param array<string, mixed>|ArrayDataCollection|null $data
     * @return $this
     */
    public function addData(array|ArrayDataCollection|null $data) : self
    {
        if($data instanceof ArrayDataCollection) {
            $data = $data->getData();
        } elseif($data === null) {
            $data = array();
        }

        $this->errorData = array_merge($this->errorData, $data);

        return $this;
    }

    /**
     * Adds request-derived data to the error response (e.g. raw `$_REQUEST`
     * contents, or a parsed request body — see {@see \Application\API\Traits\JSONRequestTrait::collectRequestErrorData()}).
     *
     * SECURITY: Content added here is kept separate from {@see self::$errorData}
     * and is rebuilt by {@see self::getErrorData()} into a minimal
     * method/API-version allowlist for any client outside a development
     * environment — regardless of what is added here. This is the
     * channel-scoped redaction boundary: use {@see self::addData()} instead
     * for method-owned domain data that must always reach the client
     * unfiltered.
     *
     * @param array<string, mixed>|ArrayDataCollection|null $data
     * @return $this
     */
    public function addRequestData(array|ArrayDataCollection|null $data) : self
    {
        if($data instanceof ArrayDataCollection) {
            $data = $data->getData();
        } elseif($data === null) {
            $data = array();
        }

        $this->requestData = array_merge($this->requestData, $data);

        return $this;
    }

    public function setHTTPStatusCode(int $statusCode) : self
    {
        $this->httpStatusCode = $statusCode;
        return $this;
    }

    public function makeBadRequest() : self
    {
        return $this->setHTTPStatusCode(Connectors_ResponseCode::HTTP_BAD_REQUEST);
    }

    public function makeInternalServerError() : self
    {
        return $this->setHTTPStatusCode(Connectors_ResponseCode::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Sets the HTTP status code to 401 Unauthorized.
     * Use for authentication failures: the submitted API key does not match
     * any known key ({@see APIMethodInterface::ERROR_API_KEY_INVALID}).
     */
    public function makeUnauthorized() : self
    {
        return $this->setHTTPStatusCode(Connectors_ResponseCode::HTTP_UNAUTHORIZED);
    }

    /**
     * Sets the HTTP status code to 403 Forbidden.
     * Use for authorization failures: method not granted to the API key
     * ({@see APIMethodInterface::ERROR_METHOD_NOT_GRANTED}) or insufficient user
     * rights ({@see APIMethodInterface::ERROR_INSUFFICIENT_RIGHTS}).
     */
    public function makeForbidden() : self
    {
        return $this->setHTTPStatusCode(Connectors_ResponseCode::HTTP_FORBIDDEN);
    }

    public function send() : never
    {
        $this->addRequestData(array(
            APIMethodInterface::RESPONSE_KEY_ERROR_REQUEST_DATA => $_REQUEST,
        ));

        $send = $this->sendCallback;
        $send($this);

        // Failsafe - this typically never gets reached because the send callback should exit.
        Application::exit('API Error response exit fallback');
    }
}