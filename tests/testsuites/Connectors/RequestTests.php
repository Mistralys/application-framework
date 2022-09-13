<?php

declare(strict_types=1);

namespace testsuites\Connectors;

use Application_Exception;
use Connectors_Connector_Dummy;
use Connectors_Request_Method;
use Connectors_Request_URL;
use Connectors_Response;
use HTTP_Request2;
use HTTP_Request2_Response;
use PHPUnit\Framework\TestCase;
use function AppUtils\parseThrowable;

class RequestTests extends TestCase
{
    // region: _Tests

    public function test_isValidResponseCode_Valid() : void
    {
        // Method: GET Response Code:200
        $this->requestClass->setHTTPMethod(HTTP_Request2::METHOD_GET);
        $isValid = $this->requestClass->isValidResponseCode(200);
        $this->assertTrue($isValid, 'Method: GET Response Code:200');

        // Method: DELETE Response Code:200
        $this->requestClass->setHTTPMethod(HTTP_Request2::METHOD_DELETE);
        $isValid = $this->requestClass->isValidResponseCode(200);
        $this->assertTrue($isValid, 'Method: DELETE Response Code:200');

        // Method: DELETE Response Code:204
        $this->requestClass->setHTTPMethod(HTTP_Request2::METHOD_DELETE);
        $isValid = $this->requestClass->isValidResponseCode(204);
        $this->assertTrue($isValid, 'Method: DELETE Response Code:204');

        // Method: PUT Response Code:201
        $this->requestClass->setHTTPMethod(HTTP_Request2::METHOD_PUT);
        $isValid = $this->requestClass->isValidResponseCode(201);
        $this->assertTrue($isValid, 'Method: PUT Response Code:201');

        // Method: PUT Response Code:202
        $this->requestClass->setHTTPMethod(HTTP_Request2::METHOD_PUT);
        $isValid = $this->requestClass->isValidResponseCode(202);
        $this->assertTrue($isValid, 'Method: PUT Response Code:202');

        // Method: PUT Response Code:200
        $this->requestClass->setHTTPMethod(HTTP_Request2::METHOD_POST);
        $isValid = $this->requestClass->isValidResponseCode(200);
        $this->assertTrue($isValid, 'Method: PUT Response Code:200');

        // Method: PUT Response Code:202
        $this->requestClass->setHTTPMethod(HTTP_Request2::METHOD_POST);
        $isValid = $this->requestClass->isValidResponseCode(202);
        $this->assertTrue($isValid, 'Method: PUT Response Code:202');
    }

    public function test_isValidResponseCode_Invalid() : void
    {
        // Method: GET Response Code:300
        $this->requestClass->setHTTPMethod(HTTP_Request2::METHOD_GET);
        $isValid = $this->requestClass->isValidResponseCode(300);
        $this->assertFalse($isValid, 'Method: GET Response Code:300');

        // Method: DELETE Response Code:300
        $this->requestClass->setHTTPMethod(HTTP_Request2::METHOD_DELETE);
        $isValid = $this->requestClass->isValidResponseCode(300);
        $this->assertFalse($isValid, 'Method: DELETE Response Code:300');

        // Method: PUT Response Code:300
        $this->requestClass->setHTTPMethod(HTTP_Request2::METHOD_PUT);
        $isValid = $this->requestClass->isValidResponseCode(300);
        $this->assertFalse($isValid, 'Method: PUT Response Code:300');

        // Method: POST Response Code:300
        $this->requestClass->setHTTPMethod(HTTP_Request2::METHOD_POST);
        $isValid = $this->requestClass->isValidResponseCode(300);
        $this->assertFalse($isValid, 'Method: POST Response Code:300');
    }

    public function test_endpointSuccess() : void
    {
        $response = $this->createTestResponse($this->successJSONResponse);

        $this->assertFalse($response->isError());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(array('foo' => 'bar'), $response->getData());
    }

    public function test_endpointFailure() : void
    {
        $body = sprintf(
            $this->errorJSONResponse,
            json_encode(parseThrowable(
                new Application_Exception(
                    'Endpoint exception',
                    'Endpoint details',
                    452
                )
            )->serialize(), JSON_THROW_ON_ERROR)
        );

        $response = $this->createTestResponse($body);

        $this->assertTrue($response->isError());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(Connectors_Response::RETURNCODE_ERROR_RESPONSE, $response->getErrorCode());
        $this->assertSame(array('foo' => 'Error bar'), $response->getData());

        $error = $response->getEndpointError();
        $this->assertNotNull($error);
        $this->assertSame(42, $error->getCode());
        $this->assertSame('An error occurred.', $error->getMessage());
        $this->assertSame('Developer details.', $error->getDetails());

        $exception = $error->getException();

        $this->assertNotNull($exception);
        $this->assertSame(452, $exception->getCode());
        // String search, because throwable info appends the
        // developer details in the test suite environment.
        $this->assertStringContainsString('Endpoint exception', $exception->getMessage());
        $this->assertSame('Endpoint details', $exception->getDetails());
    }

    public function test_responsePlaceholders() : void
    {
        $response = $this->createTestResponse(sprintf(
            $this->strayOutputResponse,
            Connectors_Response::JSON_PLACEHOLDER_START.
            $this->successJSONResponse.
            Connectors_Response::JSON_PLACEHOLDER_END
        ));

        $this->assertFalse($response->isError());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(array('foo' => 'bar'), $response->getData());
    }

    public function test_serialization() : void
    {
        $body = sprintf(
            $this->errorJSONResponse,
            json_encode(parseThrowable(
                new Application_Exception(
                    'Endpoint exception',
                    'Endpoint details',
                    452
                )
            )->serialize(), JSON_THROW_ON_ERROR)
        );

        // Using the response nested in stray output, so we
        // can also test that the serialized data does not
        // contain the extraneous content, but only the actual
        // JSON relevant for the response.
        $response = $this->createTestResponse(sprintf(
            $this->strayOutputResponse,
            Connectors_Response::JSON_PLACEHOLDER_START.
            $body.
            Connectors_Response::JSON_PLACEHOLDER_END
        ));

        $serialized = $response->serialize();
        $error = $response->getEndpointError();
        $exception = $response->getEndpointException();

        $this->assertNotNull($error);
        $this->assertNotNull($exception);
        $this->assertSame(42, $error->getCode());
        $this->assertSame(452, $exception->getCode());

        $restored = Connectors_Response::unserialize($response->getRequest(), $serialized);

        $this->assertSame($response->getStatusCode(), $restored->getStatusCode());
        $this->assertSame($response->getRawJSON(), $restored->getRawJSON());
        $this->assertSame($response->getStatusMessage(), $restored->getStatusMessage());
        $this->assertSame($response->getURL(), $restored->getURL());
        $this->assertSame($response->getData(), $restored->getData());
        $this->assertSame($response->getErrorCode(), $restored->getErrorCode());

        $restoredError = $restored->getEndpointError();
        $restoredException = $restored->getEndpointException();

        $this->assertNotNull($restoredError);
        $this->assertNotNull($restoredException);
        $this->assertSame($error->getCode(), $restoredError->getCode());
        $this->assertSame($exception->getCode(), $restoredException->getCode());
    }

    // endregion

    // region: Support methods

    private string $successJSONResponse = <<<'EOT'
{
    "state": "success",
    "data": {
        "foo": "bar"
    }
}
EOT;

    private string $errorJSONResponse = <<<'EOT'
{
    "state": "error",
    "code": 42,
    "message": "An error occurred.",
    "details": "Developer details.",
    "exception": %s,
    "data": {
        "foo": "Error bar"
    }
}
EOT;

    private string $strayOutputResponse = <<<'EOT'
This is some log message.
The JSON is included here: %s.
There is content all around it.
EOT;


    public function createTestResponse(string $body, int $statusCode = 200, ?string $method = null, ?string $url = null) : Connectors_Response
    {
        $request = $this->createTestMethodRequest($method, $url);

        $HTTPResponse = new HTTP_Request2_Response(
            'HTTP/1.1 ' . $statusCode . ' OK',
            false,
            'https://endpoint'
        );

        $HTTPResponse->appendBody($body);

        return new Connectors_Response($request, $HTTPResponse);
    }

    public function createTestMethodRequest(?string $method = null, ?string $url = null) : Connectors_Request_Method
    {
        return new Connectors_Request_Method(
            new Connectors_Connector_Dummy(),
            $url ?? 'https://endpoint',
            $method ?? 'TestMethod'
        );
    }

    private Connectors_Request_URL $requestClass;

    public function setUp() : void
    {
        $connector = new Connectors_Connector_Dummy();
        $this->requestClass = new Connectors_Request_URL($connector, '');
    }

    // endregion
}
