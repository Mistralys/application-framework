<?php
/**
 * @package API
 * @subpackage Core
 */

declare(strict_types=1);

namespace Application\API;

use Application;
use AppUtils\ArrayDataCollection;
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
    private array $errorData = array();
    private string $message = '';
    private APIMethodInterface $method;

    /**
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

    public function getErrorData(): array
    {
        return $this->errorData;
    }

    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }

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