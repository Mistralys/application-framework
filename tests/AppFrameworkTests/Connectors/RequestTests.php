<?php

declare(strict_types=1);

namespace testsuites\Connectors;

use Application_Exception;
use Connectors\Response\ResponseEndpointError;
use Connectors_Connector_Dummy;
use Connectors_Request_Method;
use Connectors_Request_URL;
use Connectors_Response;
use HTTP_Request2;
use HTTP_Request2_Response;
use AppFrameworkTestClasses\ApplicationTestCase;
use function AppUtils\parseThrowable;

class RequestTests extends ApplicationTestCase
{
    // region: _Tests

    public const BASE_ENDPOINT_URL = 'https://endpoint';

    public function test_disableCache() : void
    {
        $request = $this->createTestMethodRequest();
        $cache = $request->getCache();

        $cache->setEnabled(true, 1);
        $this->assertTrue($cache->isEnabled());

        $response = $this->createTestResponse(
            $this->successJSONResponse
        );

        $cache->storeResponse($response);

        $this->assertTrue($cache->isValid());

        $request->setCacheEnabled(false);

        $this->assertFalse($cache->isEnabled());
        $this->assertFalse($cache->isValid());
    }

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
        $this->assertEmpty($response->getData());

        $error = $response->getError();

        $this->assertNotNull($error);
        $this->assertInstanceOf(ResponseEndpointError::class, $error);
        $this->assertSame(Connectors_Response::RETURNCODE_ERROR_RESPONSE, $error->getCode());

        $endpointError = $error->getEndpointError();

        $this->assertSame(42, $endpointError->getCode());
        $this->assertSame('An error occurred.', $endpointError->getMessage());
        $this->assertSame('Developer details.', $endpointError->getDetails());
        $this->assertSame(array('foo' => 'Error bar'), $endpointError->getData());

        $exception = $endpointError->getException();

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
            $this->successJSONResponse
        ));

        $this->assertFalse($response->isError());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(array('foo' => 'bar'), $response->getData());
    }

    /**
     * Ensures that the URLs are handled correctly regarding
     * the GET parameters. Any parameters specified in the
     * URL must be removed and added to the collection of GET
     * parameters.
     *
     * NOTE: This is important because any GET parameters
     * included in the URL are automatically stripped by the
     * backend library.
     */
    public function test_URLHandling_serializeWithGETParams() : void
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

        $baseURL = self::BASE_ENDPOINT_URL.'?argh=yes';

        $response = $this->createTestResponse(
            $body,
            200,
            null,
            $baseURL,
            array(
                'foo' => 'bar'
            )
        );

        $this->assertSame(self::BASE_ENDPOINT_URL, $response->getRequest()->getBaseURL());
        $this->assertSame($baseURL.'&foo=bar', $response->getRequest()->getRequestURL());
        $this->assertSame($baseURL.'&foo=bar', $response->getURL());

        $serialized = $response->serialize();
        $restored = Connectors_Response::unserialize($serialized);

        $this->assertNotNull($restored);
        $this->assertSame(self::BASE_ENDPOINT_URL, $restored->getRequest()->getBaseURL());
        $this->assertSame($baseURL.'&foo=bar', $restored->getRequest()->getRequestURL());
        $this->assertSame($baseURL.'&foo=bar', $restored->getURL());
    }

    public function test_URLHandling() : void
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

        $baseURL = self::BASE_ENDPOINT_URL;

        $response = $this->createTestResponse(
            $body,
            200,
            null,
            $baseURL,
            array(
                'foo' => 'bar'
            )
        );

        $this->assertSame($baseURL, $response->getRequest()->getBaseURL());
        $this->assertSame($baseURL.'?foo=bar', $response->getRequest()->getRequestURL());
        $this->assertSame($baseURL.'?foo=bar', $response->getURL());
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
            $body
        ));

        $this->assertEmpty($response->getData());

        $serialized = $response->serialize();
        $error = $response->getError();

        $this->assertNotNull($error);
        $this->assertInstanceOf(ResponseEndpointError::class, $error);

        $endpointError = $error->getEndpointError();
        $exception = $endpointError->getException();

        $this->assertNotNull($endpointError);
        $this->assertNotNull($exception);
        $this->assertSame(42, $endpointError->getCode());
        $this->assertSame(452, $exception->getCode());

        $restored = Connectors_Response::unserialize($serialized);

        $this->assertNotNull($restored);
        $this->assertSame($response->getStatusCode(), $restored->getStatusCode());
        $this->assertSame($response->getRawJSON(), $restored->getRawJSON());
        $this->assertSame($response->getStatusMessage(), $restored->getStatusMessage());
        $this->assertSame($response->getURL(), $restored->getURL());
        $this->assertSame($response->isLooseData(), $restored->isLooseData());
        $this->assertSame($response->getData(), $restored->getData());

        $restoredError = $restored->getError();
        $this->assertInstanceOf(ResponseEndpointError::class, $restoredError);

        $restoredEndpointError = $restoredError->getEndpointError();
        $restoredException = $restoredEndpointError->getException();

        $this->assertNotNull($restoredException);
        $this->assertSame($error->getCode(), $restoredError->getCode());
        $this->assertSame($error->getDetails(), $restoredError->getDetails());
        $this->assertSame($error->getData(), $restoredError->getData());
        $this->assertSame($endpointError->getCode(), $restoredEndpointError->getCode());
        $this->assertSame($exception->getCode(), $restoredException->getCode());
    }

    public function test_nonJSONResponse() : void
    {
        $response = $this->createTestResponse('Invalid JSON response');
        $error = $response->getError();

        $this->assertNotNull($error);
        $this->assertTrue($response->isError());
        $this->assertEmpty($response->getResponseState());
        $this->assertNotInstanceOf(ResponseEndpointError::class, $error);
        $this->assertEmpty($response->getData());
        $this->assertSame(Connectors_Response::RETURNCODE_JSON_NOT_PARSEABLE, $error->getCode());
    }

    public function test_invalidJSONResponse() : void
    {
        $response = $this->createTestResponse('{"foo":"bar"}');
        $error = $response->getError();

        $this->assertNotNull($error);
        $this->assertSame(Connectors_Response::RETURNCODE_UNEXPECTED_FORMAT, $error->getCode());
    }

    public function test_invalidJSONStrayOutputResponse() : void
    {
        $response = $this->createTestResponse(sprintf(
            $this->strayOutputResponse,
            '{"foo":"bar"}'
        ));

        $error = $response->getError();

        $this->assertNotNull($error);
        $this->assertSame(Connectors_Response::RETURNCODE_UNEXPECTED_FORMAT, $error->getCode());
    }

    public function test_invalidFormatJSONStrayOutput() : void
    {
        $response = $this->createTestResponse(sprintf(
            $this->strayOutputResponse,
            'true'
        ));

        $error = $response->getError();

        $this->assertNotNull($error);
        $this->assertSame(Connectors_Response::RETURNCODE_JSON_NOT_PARSEABLE, $error->getCode());
    }

    /**
     * <code>true</code> is a valid JSON value, but must not be accepted
     * here, as we expect an array.
     *
     * @return void
     */
    public function test_invalidResponse() : void
    {
        $response = $this->createTestResponse('true');
        $error = $response->getError();

        $this->assertNotNull($error);
        $this->assertSame(Connectors_Response::RETURNCODE_JSON_NOT_PARSEABLE, $error->getCode());
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
The JSON is included here: {RESPONSE}%s{/RESPONSE}.
There is content all around it.
EOT;


    public function createTestResponse(string $body, int $statusCode = 200, ?string $method = null, ?string $url = null, ?array $getData=null) : Connectors_Response
    {
        $request = $this->createTestMethodRequest($method, $url);

        $HTTPResponse = new HTTP_Request2_Response(
            'HTTP/1.1 ' . $statusCode . ' OK',
            false,
            self::BASE_ENDPOINT_URL
        );

        $HTTPResponse->appendBody($body);

        if($getData !== null)
        {
            foreach($getData as $name => $value) {
                $request->setGETData($name, $value);
            }
        }

        return new Connectors_Response($request, $HTTPResponse);
    }

    public function createTestMethodRequest(?string $method = null, ?string $url = null) : Connectors_Request_Method
    {
        return new Connectors_Request_Method(
            new Connectors_Connector_Dummy(),
            $url ?? self::BASE_ENDPOINT_URL,
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
