<?php

declare(strict_types=1);

namespace AppFrameworkTests\API;

use Application\API\APIManager;
use Application\API\APIMethodInterface;
use Application\API\ErrorResponsePayload;
use Application\API\ResponsePayload;
use AppUtils\ConvertHelper\JSONConverter;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;
use TestDriver\API\TestErrorResponseMethod;
use TestDriver\API\TestJSON2JSONMethod;
use TestDriver\API\TestUnexpectedThrowableAlwaysMethod;
use TestDriver\API\TestUnexpectedThrowableMethod;

final class ProcessReturnTest extends APITestCase
{
    public function test_processReturnGetData() : void
    {
        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = TestJSON2JSONMethod::METHOD_NAME;

        $JSONData = array('test' => 'foo');

        $method = new TestJSON2JSONMethod(APIManager::getInstance());

        // Simulate the request body, because `php://input` streams are not writable.
        $method->setRequestBody(JSONConverter::var2json($JSONData));

        $data = $method->processReturn();

        $this->assertInstanceOf(ResponsePayload::class, $data);

        $this->assertResultValidWithNoMessages($method->getValidationResults());

        $this->assertSame($JSONData, $data->getData());
    }

    public function test_processReturnErrorResponse() : void
    {
        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = TestErrorResponseMethod::METHOD_NAME;

        $method = new TestErrorResponseMethod(APIManager::getInstance());

        $data = $method->processReturn();

        $this->assertInstanceOf(ErrorResponsePayload::class, $data);

        $this->assertSame(TestErrorResponseMethod::ERROR_CODE_ERROR_RESPONSE, $data->getErrorCode());
        $this->assertSame(TestErrorResponseMethod::ERROR_MESSAGE, $data->getErrorMessage());
    }

    /**
     * A throwable raised before either data collector runs (via
     * {@see TestUnexpectedThrowableMethod::getActiveVersion()}) must still
     * produce a well-formed {@see ErrorResponsePayload} with the stable
     * {@see APIMethodInterface::ERROR_UNEXPECTED_THROWABLE} code — proving
     * the shared execution boundary covers processReturn(), not just the
     * two named collector methods.
     */
    public function test_processReturnUnexpectedThrowable() : void
    {
        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = TestUnexpectedThrowableMethod::METHOD_NAME;

        $method = new TestUnexpectedThrowableMethod(APIManager::getInstance());

        $data = $method->processReturn();

        $this->assertInstanceOf(ErrorResponsePayload::class, $data);
        $this->assertSame(APIMethodInterface::ERROR_UNEXPECTED_THROWABLE, $data->getErrorCode());

        $errorData = $data->getErrorData();
        $this->assertArrayHasKey(APIMethodInterface::RESPONSE_KEY_ERROR_LOG_REFERENCE, $errorData);
        $this->assertNotEmpty($errorData[APIMethodInterface::RESPONSE_KEY_ERROR_LOG_REFERENCE]);

        // The throwable's own message must never leak into the client-facing response.
        $this->assertStringNotContainsString(
            TestUnexpectedThrowableMethod::EXCEPTION_MESSAGE,
            $data->getErrorMessage()
        );
    }

    /**
     * {@see TestUnexpectedThrowableAlwaysMethod::getActiveVersion()} throws on
     * every call, unlike {@see TestUnexpectedThrowableMethod} which throws
     * once — proving `processReturn()` still returns a well-formed error
     * payload rather than propagating the throwable when nothing about the
     * override's behaviour ever changes.
     *
     * NOTE: this test's default (development) environment does not reach
     * the second {@see APIMethodInterface::getActiveVersion()} call inside
     * {@see \Application\API\ErrorResponse::getErrorData()} — that specific
     * escape is covered outside a development environment by
     * {@see \AppFrameworkTests\API\ErrorResponseTest::test_getErrorDataDoesNotEscapeWhenActiveVersionThrowsUnconditionally()}
     * and, for the always-invoked {@see \Application\API\Response\JSONInfoSerializer::toArray()}
     * envelope build, by {@see \AppFrameworkTests\API\ErrorResponseTest::test_infoEnvelopeDoesNotEscapeWhenActiveVersionThrowsUnconditionally()}.
     */
    public function test_processReturnUnexpectedThrowableThatAlwaysThrowsIsHandled() : void
    {
        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = TestUnexpectedThrowableAlwaysMethod::METHOD_NAME;

        $method = new TestUnexpectedThrowableAlwaysMethod(APIManager::getInstance());

        $data = $method->processReturn();

        $this->assertInstanceOf(ErrorResponsePayload::class, $data);
        $this->assertSame(APIMethodInterface::ERROR_UNEXPECTED_THROWABLE, $data->getErrorCode());

        $errorData = $data->getErrorData();
        $this->assertArrayHasKey(APIMethodInterface::RESPONSE_KEY_ERROR_LOG_REFERENCE, $errorData);
        $this->assertNotEmpty($errorData[APIMethodInterface::RESPONSE_KEY_ERROR_LOG_REFERENCE]);

        // The throwable's own message must never leak into the client-facing response.
        $this->assertStringNotContainsString(
            TestUnexpectedThrowableAlwaysMethod::EXCEPTION_MESSAGE,
            $data->getErrorMessage()
        );
    }
}
