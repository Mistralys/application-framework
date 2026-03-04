<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\OpenAPI;

use Application\API\Groups\FrameworkAPIGroup;
use Application\API\OpenAPI\GetOpenAPISpec;
use Application\API\OpenAPI\OpenAPIGenerator;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the built-in {@see GetOpenAPISpec} API method.
 *
 * These tests do not require a running application instance and focus solely
 * on the class contract — constants, return-type guarantees, and the static URL helper.
 *
 * @package AppFrameworkTests\API\OpenAPI
 */
final class GetOpenAPISpecTest extends TestCase
{
    /**
     * The method name constant must equal the literal string consumed by the API index
     * and the .htaccess rewrite rule.
     */
    public function test_methodNameConstant_isGetOpenAPISpec(): void
    {
        $this->assertSame('GetOpenAPISpec', GetOpenAPISpec::METHOD_NAME);
    }

    /**
     * When `APP_URL` is defined, `getSpecURL()` must produce a URL that ends with
     * `/api/GetOpenAPISpec`.
     */
    public function test_getSpecURL_withAppURLDefined_returnsSuffix(): void
    {
        if(!defined('APP_URL')) {
            $this->markTestSkipped('APP_URL constant is not defined in this test environment.');
        }

        $url = GetOpenAPISpec::getSpecURL();

        $this->assertNotEmpty($url);
        $this->assertStringEndsWith('/api/'.GetOpenAPISpec::METHOD_NAME, $url);
    }

    /**
     * When `APP_URL` is not defined, `getSpecURL()` must return an empty string rather
     * than throwing an error.
     */
    public function test_getSpecURL_withoutAppURL_returnsEmptyString(): void
    {
        if(defined('APP_URL')) {
            $this->markTestSkipped('APP_URL is defined; cannot test the fallback branch.');
        }

        $this->assertSame('', GetOpenAPISpec::getSpecURL());
    }

    /**
     * `getExampleJSONResponse()` returns an empty array for raw-spec endpoints.
     */
    public function test_getExampleJSONResponse_isEmptyArray(): void
    {
        $instance = $this->createGetOpenAPISpec();
        $this->assertSame(array(), $instance->getExampleJSONResponse());
    }

    /**
     * `getReponseKeyDescriptions()` returns an empty array for raw-spec endpoints.
     * Note: using the misspelled method name matching the interface definition.
     */
    public function test_getReponseKeyDescriptions_isEmptyArray(): void
    {
        $instance = $this->createGetOpenAPISpec();
        $this->assertSame(array(), $instance->getReponseKeyDescriptions());
    }

    /**
     * The method must be grouped under `FrameworkAPIGroup`.
     */
    public function test_getGroup_isFrameworkAPIGroup(): void
    {
        $instance = $this->createGetOpenAPISpec();
        $this->assertInstanceOf(FrameworkAPIGroup::class, $instance->getGroup());
    }

    /**
     * `getChangelog()` must return an empty array (no versioned history).
     */
    public function test_getChangelog_isEmptyArray(): void
    {
        $instance = $this->createGetOpenAPISpec();
        $this->assertSame(array(), $instance->getChangelog());
    }

    /**
     * `getRelatedMethodNames()` returns an empty array.
     */
    public function test_getRelatedMethodNames_isEmptyArray(): void
    {
        $instance = $this->createGetOpenAPISpec();
        $this->assertSame(array(), $instance->getRelatedMethodNames());
    }

    /**
     * `getVersions()` must include the `VERSION_1_0` version.
     */
    public function test_getVersions_containsVersion1(): void
    {
        $instance = $this->createGetOpenAPISpec();
        $this->assertContains(GetOpenAPISpec::VERSION_1_0, $instance->getVersions());
    }

    /**
     * `getCurrentVersion()` must return the `CURRENT_VERSION` constant value.
     */
    public function test_getCurrentVersion_matchesCurrentVersionConstant(): void
    {
        $instance = $this->createGetOpenAPISpec();
        $this->assertSame(GetOpenAPISpec::CURRENT_VERSION, $instance->getCurrentVersion());
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Creates a `GetOpenAPISpec` instance without going through the application
     * bootstrapper by using a mock `APIManager`.
     */
    private function createGetOpenAPISpec(): GetOpenAPISpec
    {
        $api = $this->createMock(\Application\API\APIManager::class);
        return new GetOpenAPISpec($api);
    }
}
