<?php

declare(strict_types=1);

namespace AppFrameworkTests\Ajax;

use AppFrameworkTestClasses\ApplicationTestCase;
use AppFrameworkTestClasses\Traits\ConnectorTestInterface;
use AppFrameworkTestClasses\Traits\ConnectorTestTrait;
use Connectors;
use PHPUnit\Framework\Attributes\Group;
use TestDriver\AjaxMethods\AjaxGetTestJSON;
use TestDriver\Connectors\InternalAjax\GetTestJSONMethod;
use TestDriver\Connectors\InternalAjaxConnector;

/**
 * Tests for the internal AJAX connector that make live HTTP requests to APP_URL.
 * Excluded from the default suite — requires a running web server at APP_URL.
 */
#[Group('live-http')]
final class AjaxRequestTest
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
