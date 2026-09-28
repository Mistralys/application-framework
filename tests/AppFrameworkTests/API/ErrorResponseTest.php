<?php

declare(strict_types=1);

namespace AppFrameworkTests\API;

use Application\API\APIManager;
use Application\API\APIMethodInterface;
use Application\API\Clients\API\APIKeyMethodInterface;
use Application\API\Traits\JSONRequestInterface;
use Application\Application;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;
use ReflectionProperty;
use TestDriver\API\TestAPIKeyMethod;
use TestDriver\API\TestErrorResponseMethod;
use TestDriver\API\TestGetCountryBySetAPI;
use TestDriver\API\TestJSONRequestRedactionMethod;
use TestDriver\API\TestUnexpectedThrowableAlwaysMethod;

final class ErrorResponseTest extends APITestCase
{
    public function test_errorDataIncludesValidationErrors() : void
    {
        $method = new TestAPIKeyMethod(APIManager::getInstance());

        $response = $this->assertErrorResponse($method);

        $data = $response->getErrorData();

        $this->assertArrayHasKey('validationErrors', $data);
        $this->assertIsArray($data['validationErrors']);
        $this->assertNotEmpty($data['validationErrors']);

        foreach($data['validationErrors'] as $error) {
            $this->assertArrayHasKey('param', $error);
            $this->assertArrayHasKey('code', $error);
            $this->assertArrayHasKey('message', $error);
            $this->assertIsInt($error['code']);
            $this->assertIsString($error['message']);
            $this->assertStringNotContainsString('ERROR #', $error['message']);
        }
    }

    public function test_errorDataStillIncludesValidationMessages() : void
    {
        $method = new TestAPIKeyMethod(APIManager::getInstance());

        $response = $this->assertErrorResponse($method);

        $data = $response->getErrorData();

        $this->assertArrayHasKey('validationMessages', $data);
        $this->assertIsArray($data['validationMessages']);
        $this->assertNotEmpty($data['validationMessages']);

        foreach($data['validationMessages'] as $message) {
            $this->assertIsString($message);
        }
    }

    public function test_validationErrorParamMatchesParameterName() : void
    {
        $method = new TestAPIKeyMethod(APIManager::getInstance());

        $response = $this->assertErrorResponse($method);

        $data = $response->getErrorData();

        $paramNames = array_column($data['validationErrors'], 'param');

        $this->assertContains(APIKeyMethodInterface::API_KEY_PARAM_NAME, $paramNames);
    }

    public function test_ruleLevelValidationErrorHasNullParam() : void
    {
        $method = new TestGetCountryBySetAPI(APIManager::getInstance());

        $response = $this->assertErrorResponse($method);

        $data = $response->getErrorData();

        $this->assertArrayHasKey('validationErrors', $data);
        $this->assertNotEmpty($data['validationErrors']);
        $this->assertNull($data['validationErrors'][0]['param']);
    }

    /**
     * In a development environment (the default for this test suite — see
     * {@see \TestDriver\Environments\LocalEnvironment::getType()}),
     * `requestData` reflects the raw `$_REQUEST` content unredacted.
     */
    public function test_requestDataIsUnredactedInDevelEnvironment() : void
    {
        $_REQUEST['someExtraParam'] = 'some-value';

        $method = new TestAPIKeyMethod(APIManager::getInstance());

        $response = $this->assertErrorResponse($method);

        $data = $response->getErrorData();

        $this->assertArrayHasKey(APIMethodInterface::RESPONSE_KEY_ERROR_REQUEST_DATA, $data);
        $this->assertSame(
            'some-value',
            $data[APIMethodInterface::RESPONSE_KEY_ERROR_REQUEST_DATA]['someExtraParam'] ?? null,
            'requestData must reflect $_REQUEST verbatim in a development environment.'
        );
    }

    /**
     * Outside a development environment, `requestData` is rebuilt into a
     * minimal method/API-version allowlist regardless of what was added
     * via {@see \Application\API\ErrorResponse::addRequestData()} —
     * verifying AC3: no arbitrary request value reaches a production
     * client through this channel.
     */
    public function test_requestDataIsRedactedOutsideDevelEnvironment() : void
    {
        $_REQUEST['someExtraParam'] = 'some-value';

        $method = new TestAPIKeyMethod(APIManager::getInstance());

        $data = $this->withDevelEnvironment(false, function () use ($method) {
            $response = $this->assertErrorResponse($method);
            return $response->getErrorData();
        });

        $this->assertArrayHasKey(APIMethodInterface::RESPONSE_KEY_ERROR_REQUEST_DATA, $data);

        $requestData = $data[APIMethodInterface::RESPONSE_KEY_ERROR_REQUEST_DATA];

        $this->assertSame(
            array(APIMethodInterface::REQUEST_PARAM_METHOD, APIMethodInterface::REQUEST_PARAM_API_VERSION),
            array_keys($requestData),
            'requestData must contain only the method/API-version allowlist in production.'
        );
        $this->assertSame($method->getMethodName(), $requestData[APIMethodInterface::REQUEST_PARAM_METHOD]);
        $this->assertArrayNotHasKey('someExtraParam', $requestData);
    }

    /**
     * The parsed JSON request body is present under `JSONRequest` in a
     * development environment, and entirely absent outside one — proving
     * both the trait-level guard and the {@see \Application\API\ErrorResponse::getErrorData()}
     * chokepoint redact it independently (AC5).
     */
    public function test_jsonRequestBodyPresentInDevelEnvironment() : void
    {
        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = TestJSONRequestRedactionMethod::METHOD_NAME;

        $method = new TestJSONRequestRedactionMethod(APIManager::getInstance());
        $method->setRequestBody('{"secretField":"sensitive-value"}');

        $response = $this->assertErrorResponse($method);

        $data = $response->getErrorData();

        $this->assertArrayHasKey(JSONRequestInterface::RESPONSE_KEY_ERROR_JSON_REQUEST_DATA, $data);
    }

    public function test_jsonRequestBodyAbsentOutsideDevelEnvironment() : void
    {
        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = TestJSONRequestRedactionMethod::METHOD_NAME;

        $method = new TestJSONRequestRedactionMethod(APIManager::getInstance());
        $method->setRequestBody('{"secretField":"sensitive-value"}');

        $data = $this->withDevelEnvironment(false, function () use ($method) {
            $response = $this->assertErrorResponse($method);
            return $response->getErrorData();
        });

        $this->assertArrayNotHasKey(JSONRequestInterface::RESPONSE_KEY_ERROR_JSON_REQUEST_DATA, $data);
        $this->assertStringNotContainsString('sensitive-value', (string)json_encode($data));
    }

    /**
     * Data added via {@see \Application\API\ErrorResponse::addData()} —
     * method-owned domain data, not request-derived — must reach the
     * client unchanged regardless of environment, for both a plain array
     * and an {@see \AppUtils\ArrayDataCollection} (AC4).
     */
    public function test_addDataSurvivesRedactionArray() : void
    {
        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = TestErrorResponseMethod::METHOD_NAME;

        $method = new TestErrorResponseMethod(APIManager::getInstance());

        $data = $this->withDevelEnvironment(false, function () use ($method) {
            $response = $method->processReturn();
            return $response->getErrorData();
        });

        // TestErrorResponseMethod itself adds no addData() content, but its
        // validation arrays are populated via addData()-equivalent internal
        // state in getErrorData() and must always be present.
        $this->assertArrayHasKey('validationMessages', $data);
        $this->assertArrayHasKey('validationErrors', $data);
    }

    /**
     * Outside a development environment, {@see \Application\API\ErrorResponse::getErrorData()}
     * resolves the API version again for the `requestData` allowlist — the
     * envelope build itself calling {@see APIMethodInterface::getActiveVersion()}
     * a second time. {@see TestUnexpectedThrowableAlwaysMethod::getActiveVersion()}
     * throws on every call (unlike {@see TestErrorResponseMethod}), proving
     * `getErrorData()` must use the throw-safe
     * {@see APIMethodInterface::getSafeActiveVersion()} accessor instead —
     * otherwise this second throw would escape unhandled rather than
     * producing a well-formed error response.
     */
    public function test_getErrorDataDoesNotEscapeWhenActiveVersionThrowsUnconditionally() : void
    {
        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = TestUnexpectedThrowableAlwaysMethod::METHOD_NAME;

        $method = new TestUnexpectedThrowableAlwaysMethod(APIManager::getInstance());

        $data = $this->withDevelEnvironment(false, function () use ($method) {
            $response = $this->assertErrorResponseCode($method, APIMethodInterface::ERROR_UNEXPECTED_THROWABLE);
            return $response->getErrorData();
        });

        $requestData = $data[APIMethodInterface::RESPONSE_KEY_ERROR_REQUEST_DATA] ?? null;

        $this->assertIsArray($requestData);
        $this->assertArrayHasKey(APIMethodInterface::REQUEST_PARAM_API_VERSION, $requestData);
        $this->assertNotEmpty($requestData[APIMethodInterface::REQUEST_PARAM_API_VERSION]);
    }

    /**
     * {@see \Application\API\Response\JSONInfoSerializer::toArray()} resolves
     * the API version for the response envelope unconditionally, regardless
     * of environment. This must also use {@see APIMethodInterface::getSafeActiveVersion()},
     * not {@see APIMethodInterface::getActiveVersion()} directly, so a
     * method whose override throws on every call cannot make the envelope
     * build itself fail.
     */
    public function test_infoEnvelopeDoesNotEscapeWhenActiveVersionThrowsUnconditionally() : void
    {
        $method = new TestUnexpectedThrowableAlwaysMethod(APIManager::getInstance());

        $info = $method->getInfo()->toArray();

        $this->assertArrayHasKey('selectedVersion', $info);
        $this->assertNotEmpty($info['selectedVersion']);
    }

    // region: Support methods

    /**
     * Forces {@see Application::isDevelEnvironment()} to a specific value
     * for the duration of the given callback, via reflection on its
     * private static cache — the framework provides no public setter, and
     * this test suite's own environment is permanently "dev" (see
     * {@see \TestDriver\Environments\LocalEnvironment::getType()}), so this
     * is the only way to exercise the production ("not devel") branch.
     *
     * @template T
     * @param bool $devel
     * @param callable():T $callback
     * @return T
     */
    private function withDevelEnvironment(bool $devel, callable $callback)
    {
        $property = new ReflectionProperty(Application::class, 'develEnvironment');
        $original = $property->getValue();

        $property->setValue(null, $devel);

        try {
            return $callback();
        } finally {
            $property->setValue(null, $original);
        }
    }

    // endregion
}
