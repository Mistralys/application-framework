<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Parameters;

use Application\API\APIManager;
use Application\API\APIMethodInterface;
use Application\API\Clients\API\Params\APIKeyParam;
use Application\API\ErrorResponsePayload;
use Application\API\Parameters\BaseAPIParameter;
use Application\API\Parameters\Type\BooleanParameter;
use Application\API\Parameters\Type\IDListParameter;
use Application\API\Parameters\Type\IntegerParameter;
use Application\API\Parameters\Type\JSONParameter;
use Application\API\Parameters\Type\StringListParameter;
use Application\API\Parameters\Type\StringParameter;
use Application\API\Parameters\ValueSource\JSONBodyValueSource;
use Application\API\Parameters\ValueSource\RequestValueSource;
use Application\API\ResponsePayload;
use Application\API\Traits\JSONRequestInterface;
use Application\Application;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;
use ReflectionProperty;
use TestDriver\API\TestJSONBodyParamsMethod;
use TestDriver\API\TestRemovedParamMethod;

/**
 * Covers AC-01, AC-02 and AC-05: JSON-body parameter resolution for every
 * non-header, non-reserved parameter type, the unparseable-body rejection
 * and its re-entry guard, and dev-mode echo truncation.
 */
final class JSONBodyParamResolutionTest extends APITestCase
{
    // region: Helpers

    private function createMethod(string $body='{}') : TestJSONBodyParamsMethod
    {
        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = TestJSONBodyParamsMethod::METHOD_NAME;

        $method = new TestJSONBodyParamsMethod(APIManager::getInstance());
        $method->setRequestBody($body);

        return $method;
    }

    /**
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

    // region: AC-01 — each type resolves from the body, with request fallback

    public function test_allSixTypesResolveFromBody() : void
    {
        $method = $this->createMethod(json_encode(array(
            'aString' => 'body-string',
            'anInt' => 42,
            'aBool' => true,
            'aJSON' => array('nested' => 'value'),
            'aStringList' => array('one', 'two'),
            'anIDList' => array(1, 2, 3),
        ), JSON_THROW_ON_ERROR));

        $source = new JSONBodyValueSource($method, new RequestValueSource());

        $string = new StringParameter('aString', 'A String');
        $string->setValueSource($source);
        $this->assertSame('body-string', $string->getValue());

        $int = new IntegerParameter('anInt', 'An Int');
        $int->setValueSource($source);
        $this->assertSame(42, $int->getValue());

        $bool = new BooleanParameter('aBool', 'A Bool');
        $bool->setValueSource($source);
        $this->assertTrue($bool->getValue());

        $json = new JSONParameter('aJSON', 'A JSON');
        $json->setValueSource($source);
        $this->assertSame(array('nested' => 'value'), $json->getValue());

        $stringList = new StringListParameter('aStringList', 'A String List');
        $stringList->setValueSource($source);
        $this->assertSame(array('one', 'two'), $stringList->getValue());

        $idList = new IDListParameter('anIDList', 'An ID List');
        $idList->setValueSource($source);
        $this->assertSame(array(1, 2, 3), $idList->getValue());
    }

    public function test_fallsBackToRequestWhenKeyAbsentFromBody() : void
    {
        $_REQUEST['aString'] = 'from-request';

        $method = $this->createMethod('{}');
        $source = new JSONBodyValueSource($method, new RequestValueSource());

        $string = new StringParameter('aString', 'A String');
        $string->setValueSource($source);

        $this->assertSame('from-request', $string->getValue());
    }

    public function test_bodyValueWinsWhenBothArePresent() : void
    {
        $_REQUEST['aString'] = 'from-request';

        $method = $this->createMethod('{"aString":"from-body"}');
        $source = new JSONBodyValueSource($method, new RequestValueSource());

        $string = new StringParameter('aString', 'A String');
        $string->setValueSource($source);

        $this->assertSame('from-body', $string->getValue());
    }

    public function test_reservedParamsStayRequestOnly() : void
    {
        $_REQUEST[APIMethodInterface::REQUEST_PARAM_API_VERSION] = '1.0';

        // The body supplies a value that is not among the method's allowed
        // versions. If the reserved parameter consulted the body at all,
        // its own enum validation would fail and getValue() would return
        // null (the default) instead of the request-submitted '1.0'.
        $method = $this->createMethod('{"apiVersion":"not-a-valid-version"}');

        $apiVersionParam = null;
        foreach($method->manageParams()->getParams() as $param) {
            if($param->getName() === APIMethodInterface::REQUEST_PARAM_API_VERSION) {
                $apiVersionParam = $param;
            }
        }

        $this->assertNotNull($apiVersionParam);
        $this->assertSame('1.0', $apiVersionParam->getValue());
    }

    public function test_headerParamsStayRequestOnly() : void
    {
        $method = $this->createMethod('{"apiKey":"from-body"}');

        $headerParam = new APIKeyParam();
        $method->manageParams()->registerParam($headerParam);

        $property = new ReflectionProperty(BaseAPIParameter::class, 'valueSource');

        $this->assertNull($property->getValue($headerParam), 'A header parameter must never receive an injected value source.');
    }

    public function test_nonJSONMethodsAreUnchanged() : void
    {
        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = TestRemovedParamMethod::METHOD_NAME;
        $_REQUEST[TestRemovedParamMethod::PARAM_NEW_NAME] = 'value';

        $method = new TestRemovedParamMethod(APIManager::getInstance());
        $data = $method->processReturn();

        $this->assertInstanceOf(ResponsePayload::class, $data);
        $this->assertSame('value', $data->getData()[TestRemovedParamMethod::KEY_NEW_NAME]);
    }

    // endregion

    // region: AC-02 — unparseable body rejection and re-entry guard

    public function test_invalidJSONBodyIsRejected() : void
    {
        $method = $this->createMethod('not valid json');

        $data = $method->processReturn();

        $this->assertInstanceOf(ErrorResponsePayload::class, $data);
        $this->assertSame(JSONRequestInterface::ERROR_FAILED_TO_READ_INPUT, $data->getErrorCode());
    }

    public function test_nonObjectTopLevelBodyIsRejected() : void
    {
        $method = $this->createMethod('[1,2]');

        $data = $method->processReturn();

        $this->assertInstanceOf(ErrorResponsePayload::class, $data);
        $this->assertSame(JSONRequestInterface::ERROR_FAILED_TO_READ_INPUT, $data->getErrorCode());
    }

    public function test_emptyBodyIsAnEmptyObject() : void
    {
        $method = $this->createMethod('');

        $data = $method->processReturn();

        $this->assertInstanceOf(ResponsePayload::class, $data);
    }

    public function test_reentryGuardAvoidsRecursionAndEchoesTruncatedUnparseableBody() : void
    {
        $longInvalidBody = str_repeat('x', 513);
        $method = $this->createMethod($longInvalidBody);

        $data = $method->processReturn();

        $this->assertInstanceOf(ErrorResponsePayload::class, $data, 'The call must complete (no infinite recursion) and return an error.');
        $this->assertSame(JSONRequestInterface::ERROR_FAILED_TO_READ_INPUT, $data->getErrorCode());

        $errorData = $data->getErrorData();
        $jsonRequest = $errorData[JSONRequestInterface::RESPONSE_KEY_ERROR_JSON_REQUEST_DATA] ?? null;

        $this->assertIsArray($jsonRequest);
        $this->assertSame(
            '[513 characters truncated]',
            $jsonRequest[JSONRequestInterface::RESPONSE_KEY_ERROR_UNPARSEABLE_BODY] ?? null
        );
    }

    public function test_unparseableBodyEchoIsVerbatimWhenShort() : void
    {
        $method = $this->createMethod('not valid json');

        $data = $method->processReturn();
        $errorData = $data->getErrorData();

        $this->assertSame(
            'not valid json',
            $errorData[JSONRequestInterface::RESPONSE_KEY_ERROR_JSON_REQUEST_DATA][JSONRequestInterface::RESPONSE_KEY_ERROR_UNPARSEABLE_BODY] ?? null
        );
    }

    public function test_getRequestDataAfterFailedParseReturnsEmptyCollectionWithoutParsingAgain() : void
    {
        $method = $this->createMethod('not valid json');

        // Trigger the failed parse (process in return mode).
        $method->processReturn();

        $this->assertSame(array(), $method->getRequestData()->getData());
    }

    public function test_unparseableBodyAbsentOutsideDevelEnvironment() : void
    {
        $method = $this->createMethod('not valid json');

        $errorData = $this->withDevelEnvironment(false, function() use ($method) {
            return $method->processReturn()->getErrorData();
        });

        $this->assertArrayNotHasKey(JSONRequestInterface::RESPONSE_KEY_ERROR_JSON_REQUEST_DATA, $errorData);
    }

    // endregion

    // region: AC-05 — dev-mode echo truncation

    /**
     * `meta` (a {@see JSONParameter}) given a string that is itself not
     * valid JSON produces a real `VALIDATION_INVALID_JSON_DATA` error
     * (not merely a warning), which is what drives `validate()` into
     * `errorResponseBadRequest()` and therefore `collectRequestErrorData()` —
     * a parsed, truthful body that still ends in an error response, letting
     * these tests inspect the echoed body without needing a dedicated
     * always-failing fixture.
     */
    public function test_truncatesLongStringUnderRegisteredKey() : void
    {
        $longValue = str_repeat('a', 513);

        $method = $this->createMethod(json_encode(array(
            'meta' => 'not json', // forces a real validation error (see docblock above)
            'label' => $longValue,
        ), JSON_THROW_ON_ERROR));

        $data = $method->processReturn();
        $this->assertInstanceOf(ErrorResponsePayload::class, $data);

        $errorData = $data->getErrorData();
        $jsonRequest = $errorData[JSONRequestInterface::RESPONSE_KEY_ERROR_JSON_REQUEST_DATA] ?? array();

        $this->assertSame('[513 characters truncated]', $jsonRequest['label'] ?? null);
    }

    public function test_truncatesLongStringUnderUnregisteredKeyAndNestedObject() : void
    {
        $longValue = str_repeat('b', 513);

        $method = $this->createMethod(json_encode(array(
            'meta' => 'not json', // forces a real validation error (see docblock above)
            'mistypedFileContent' => $longValue,
            'nested' => array('deep' => $longValue),
        ), JSON_THROW_ON_ERROR));

        $data = $method->processReturn();
        $this->assertInstanceOf(ErrorResponsePayload::class, $data);

        $errorData = $data->getErrorData();
        $jsonRequest = $errorData[JSONRequestInterface::RESPONSE_KEY_ERROR_JSON_REQUEST_DATA] ?? array();

        $this->assertSame('[513 characters truncated]', $jsonRequest['mistypedFileContent'] ?? null);
        $this->assertSame('[513 characters truncated]', $jsonRequest['nested']['deep'] ?? null);
    }

    public function test_doesNotTruncateStringAtExactlyTheLimit() : void
    {
        $exactValue = str_repeat('c', 512);

        $method = $this->createMethod(json_encode(array(
            'meta' => 'not json', // forces a real validation error (see docblock above)
            'label' => $exactValue,
        ), JSON_THROW_ON_ERROR));

        $data = $method->processReturn();
        $this->assertInstanceOf(ErrorResponsePayload::class, $data);

        $errorData = $data->getErrorData();
        $jsonRequest = $errorData[JSONRequestInterface::RESPONSE_KEY_ERROR_JSON_REQUEST_DATA] ?? array();

        $this->assertSame($exactValue, $jsonRequest['label'] ?? null);
    }

    // endregion
}
