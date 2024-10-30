<?php

declare(strict_types=1);

namespace AppFrameworkTests\Ajax;

use AppFrameworkTestClasses\ApplicationTestCase;
use AppFrameworkTestClasses\Traits\ConnectorTestInterface;
use Connectors;
use AppFrameworkTestClasses\Traits\ConnectorTestTrait;
use TestDriver\AjaxMethods\AjaxGetTestJSON;
use TestDriver\Connectors\InternalAjax\GetTestJSONMethod;
use TestDriver\Connectors\InternalAjaxConnector;

final class AjaxRequestTests
    extends ApplicationTestCase
    implements ConnectorTestInterface
{
    use ConnectorTestTrait;

    public function test_unknownMethodError(): void
    {
        $response = $this->createTestMethod()->unknownMethod();

        $this->assertResponseIsError($response);

        $this->assertEmpty($response->getData());
    }

    public function testGetJSONDataCall(): void
    {
        $response = $this->createTestMethod()->knownMethod();

        $this->assertResponseIsSuccess($response);

        $this->assertNotEmpty($response->getData());
        $this->assertEquals(AjaxGetTestJSON::RESPONSE_PAYLOAD, $response->getData());
    }

    public function createTestMethod() : GetTestJSONMethod
    {
        $connector = Connectors::createConnector(InternalAjaxConnector::class);

        $this->assertTrue($connector->isLiveRequestsEnabled());

        $method = $connector->createMethod(GetTestJSONMethod::class);

        $this->assertInstanceOf(GetTestJSONMethod::class, $method);

        return $method;
    }
}
