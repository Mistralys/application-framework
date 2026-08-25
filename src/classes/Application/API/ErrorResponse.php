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
        $this->addData(array(
            APIMethodInterface::RESPONSE_KEY_ERROR_REQUEST_DATA => $_REQUEST,
        ));

        $send = $this->sendCallback;
        $send($this);

        // Failsafe - this typically never gets reached because the send callback should exit.
        Application::exit('API Error response exit fallback');
    }
}