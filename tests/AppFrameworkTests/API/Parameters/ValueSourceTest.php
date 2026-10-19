<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Parameters;

use Application\API\APIManager;
use Application\API\APIMethodInterface;
use Application\API\Parameters\ValueSource\JSONBodyValueSource;
use Application\API\Parameters\ValueSource\RequestValueSource;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;
use TestDriver\API\TestJSONBodyParamsMethod;

/**
 * Unit-level tests for the {@see RequestValueSource} and
 * {@see JSONBodyValueSource} value sources (see
 * {@see \Application\API\Parameters\ValueSource\ParamValueSourceInterface}),
 * proven here with synthetic arrays and a stub JSON method rather than
 * through a full API method call. — AC-01
 */
final class ValueSourceTest extends APITestCase
{
    // region: RequestValueSource

    public function test_requestValueSourceHasKey() : void
    {
        $_REQUEST['foo'] = 'bar';

        $source = new RequestValueSource();

        $this->assertTrue($source->hasKey('foo'));
        $this->assertFalse($source->hasKey('doesNotExist'));
    }

    public function test_requestValueSourceGetRawValueMatchesRequestParamGet() : void
    {
        $_REQUEST['foo'] = 'bar';

        $source = new RequestValueSource();

        $this->assertSame('bar', $source->getRawValue('foo'));
        $this->assertNull($source->getRawValue('doesNotExist'));
    }

    public function test_requestValueSourceGetKeys() : void
    {
        $_REQUEST = array('foo' => 'bar', 'baz' => '42');

        $source = new RequestValueSource();

        $this->assertSame(array('foo', 'baz'), $source->getKeys());
    }

    // endregion

    // region: JSONBodyValueSource

    private function createMethod(string $body='{}') : TestJSONBodyParamsMethod
    {
        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = TestJSONBodyParamsMethod::METHOD_NAME;

        $method = new TestJSONBodyParamsMethod(APIManager::getInstance());
        $method->setRequestBody($body);

        return $method;
    }

    public function test_jsonBodyValueSourceBodyWinsOverFallback() : void
    {
        $_REQUEST['label'] = 'from-request';

        $method = $this->createMethod('{"label":"from-body"}');
        $source = new JSONBodyValueSource($method, new RequestValueSource());

        $this->assertSame('from-body', $source->getRawValue('label'));
    }

    public function test_jsonBodyValueSourceFallsBackToRequest() : void
    {
        $_REQUEST['label'] = 'from-request';

        $method = $this->createMethod('{}');
        $source = new JSONBodyValueSource($method, new RequestValueSource());

        $this->assertSame('from-request', $source->getRawValue('label'));
    }

    public function test_jsonBodyValueSourceHasKeyWithNullBodyValue() : void
    {
        // A key present in the body with an explicit null value must be
        // reported as present (array_key_exists semantics), distinct from
        // a wholly absent key.
        $method = $this->createMethod('{"label":null}');
        $source = new JSONBodyValueSource($method, new RequestValueSource());

        $this->assertTrue($source->hasKey('label'));
        $this->assertNull($source->getRawValue('label'));
        $this->assertFalse($source->hasKey('doesNotExist'));
    }

    public function test_jsonBodyValueSourceGetKeysIsUnionOfBodyAndRequest() : void
    {
        $_REQUEST['onlyInRequest'] = 'value';

        $method = $this->createMethod('{"onlyInBody":"value","shared":"body-value"}');
        $_REQUEST['shared'] = 'request-value';

        $source = new JSONBodyValueSource($method, new RequestValueSource());

        $keys = $source->getKeys();

        // Not an exact count: $_REQUEST also carries the reserved 'method'
        // key set by createMethod(). The union must contain every body and
        // request key at least once (no duplicates for 'shared', present
        // in both), which is what matters for removed-parameter detection.
        $this->assertContains('onlyInRequest', $keys);
        $this->assertContains('onlyInBody', $keys);
        $this->assertContains('shared', $keys);
        $this->assertSame(1, count(array_keys($keys, 'shared', true)), '"shared" must appear exactly once in the union.');
    }

    // endregion
}
