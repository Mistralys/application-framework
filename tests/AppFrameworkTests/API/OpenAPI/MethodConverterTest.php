<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\OpenAPI;

use Application\API\APIMethodInterface;
use Application\API\Groups\APIGroupInterface;
use Application\API\OpenAPI\MethodConverter;
use Application\API\Parameters\APIParamManager;
use Application\API\Traits\JSONRequestInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use UI\AdminURLs\AdminURLInterface;

/**
 * Unit tests for {@see MethodConverter}.
 *
 * Uses PHPUnit mocks to isolate the converter from the application bootstrap.
 */
final class MethodConverterTest extends TestCase
{
    private MethodConverter $converter;

    protected function setUp(): void
    {
        $this->converter = new MethodConverter();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Creates a mock APIGroupInterface with the given label.
     *
     * @param string $label
     * @return APIGroupInterface&MockObject
     */
    private function createGroupMock(string $label) : APIGroupInterface
    {
        $group = $this->createMock(APIGroupInterface::class);
        $group->method('getLabel')->willReturn($label);
        return $group;
    }

    /**
     * Creates a mock AdminURLInterface whose string cast returns the given URL.
     *
     * @param string $url
     * @return AdminURLInterface&MockObject
     */
    private function createAdminURLMock(string $url) : AdminURLInterface
    {
        $adminUrl = $this->createMock(AdminURLInterface::class);
        $adminUrl->method('__toString')->willReturn($url);
        return $adminUrl;
    }

    /**
     * Creates a mock APIParamManager with no parameters.
     *
     * @return APIParamManager&MockObject
     */
    private function createEmptyParamManager() : APIParamManager
    {
        $manager = $this->createMock(APIParamManager::class);
        $manager->method('getParams')->willReturn(array());
        return $manager;
    }

    /**
     * Builds a full APIMethodInterface mock with configurable properties.
     *
     * @param string $methodName
     * @param string $description
     * @param string $groupLabel
     * @param string $docUrl
     * @param string[] $versions
     * @param string $currentVersion
     * @param array<string,string> $changelog
     * @param string[] $relatedMethodNames
     * @return APIMethodInterface&MockObject
     */
    private function createMethodMock(
        string $methodName = 'TestMethod',
        string $description = 'Single-line description.',
        string $groupLabel = 'Test Group',
        string $docUrl = '',
        array $versions = array('1.0'),
        string $currentVersion = '1.0',
        array $changelog = array(),
        array $relatedMethodNames = array()
    ) : APIMethodInterface {
        $method = $this->createMock(APIMethodInterface::class);
        $method->method('getMethodName')->willReturn($methodName);
        $method->method('getDescription')->willReturn($description);
        $method->method('getGroup')->willReturn($this->createGroupMock($groupLabel));
        $method->method('getDocumentationURL')->willReturn($this->createAdminURLMock($docUrl));
        $method->method('getVersions')->willReturn($versions);
        $method->method('getCurrentVersion')->willReturn($currentVersion);
        $method->method('getChangelog')->willReturn($changelog);
        $method->method('getRelatedMethodNames')->willReturn($relatedMethodNames);
        $method->method('getResponseMime')->willReturn('application/json');
        $method->method('manageParams')->willReturn($this->createEmptyParamManager());
        return $method;
    }

    /**
     * Builds a mock that implements both APIMethodInterface and JSONRequestInterface.
     *
     * @param string $methodName
     * @param string $requestMime
     * @return JSONRequestInterface&MockObject
     */
    private function createJsonMethodMock(
        string $methodName = 'JsonMethod',
        string $requestMime = 'application/json'
    ) : JSONRequestInterface {
        $method = $this->createMock(JSONRequestInterface::class);
        $method->method('getMethodName')->willReturn($methodName);
        $method->method('getDescription')->willReturn('JSON method description.');
        $method->method('getGroup')->willReturn($this->createGroupMock('JSON Group'));
        $method->method('getDocumentationURL')->willReturn($this->createAdminURLMock(''));
        $method->method('getVersions')->willReturn(array('1.0'));
        $method->method('getCurrentVersion')->willReturn('1.0');
        $method->method('getChangelog')->willReturn(array());
        $method->method('getRelatedMethodNames')->willReturn(array());
        $method->method('getResponseMime')->willReturn('application/json');
        $method->method('getRequestMime')->willReturn($requestMime);
        $method->method('manageParams')->willReturn($this->createEmptyParamManager());
        return $method;
    }

    // -------------------------------------------------------------------------
    // Path and top-level structure
    // -------------------------------------------------------------------------

    public function test_convertMethod_pathFormatIsCorrect() : void
    {
        $method = $this->createMethodMock('GetComtypes');
        $result = $this->converter->convertMethod($method);

        $this->assertArrayHasKey('/api/GetComtypes', $result);
    }

    public function test_convertMethod_pathUsesMethodName() : void
    {
        $method = $this->createMethodMock('SomeOtherMethod');
        $result = $this->converter->convertMethod($method);

        $this->assertArrayHasKey('/api/SomeOtherMethod', $result);
    }

    public function test_convertMethod_pathItemHasPostOperation() : void
    {
        $method = $this->createMethodMock();
        $result = $this->converter->convertMethod($method);

        $path = array_key_first($result);
        $this->assertIsString($path);
        $this->assertArrayHasKey('post', $result[$path]);
    }

    public function test_convertMethod_onlyPostOperationPresent() : void
    {
        $method = $this->createMethodMock();
        $result = $this->converter->convertMethod($method);

        $path = array_key_first($result);
        $this->assertIsString($path);
        $pathItem = $result[$path];

        // Only 'post' should be present — no get, put, delete, etc.
        $this->assertSame(array('post'), array_keys($pathItem));
    }

    // -------------------------------------------------------------------------
    // Operation object — required fields
    // -------------------------------------------------------------------------

    private function getOperation(APIMethodInterface $method) : array
    {
        $result = $this->converter->convertMethod($method);
        $path = array_key_first($result);
        $this->assertIsString($path);
        return $result[$path]['post'];
    }

    public function test_operation_operationIdMatchesMethodName() : void
    {
        $method = $this->createMethodMock('GetComtypes');
        $operation = $this->getOperation($method);

        $this->assertSame('GetComtypes', $operation['operationId']);
    }

    public function test_operation_descriptionIsFullDescription() : void
    {
        $method = $this->createMethodMock('TestMethod', "First line.\nSecond line.");
        $operation = $this->getOperation($method);

        $this->assertSame("First line.\nSecond line.", $operation['description']);
    }

    public function test_operation_summaryIsFirstLine() : void
    {
        $method = $this->createMethodMock('TestMethod', "First line.\nSecond line.");
        $operation = $this->getOperation($method);

        $this->assertSame('First line.', $operation['summary']);
    }

    public function test_operation_summaryFromSingleLineDescription() : void
    {
        $method = $this->createMethodMock('TestMethod', 'Single line only.');
        $operation = $this->getOperation($method);

        $this->assertSame('Single line only.', $operation['summary']);
    }

    public function test_operation_summarySkipsLeadingEmptyLines() : void
    {
        $method = $this->createMethodMock('TestMethod', "\n\nActual first line.\nSecond line.");
        $operation = $this->getOperation($method);

        $this->assertSame('Actual first line.', $operation['summary']);
    }

    public function test_operation_tagsContainsGroupLabel() : void
    {
        $method = $this->createMethodMock('TestMethod', 'Desc', 'My Group');
        $operation = $this->getOperation($method);

        $this->assertArrayHasKey('tags', $operation);
        $this->assertContains('My Group', $operation['tags']);
    }

    public function test_operation_tagsHasOnlyGroupLabel() : void
    {
        $method = $this->createMethodMock('TestMethod', 'Desc', 'Only Group');
        $operation = $this->getOperation($method);

        $this->assertCount(1, $operation['tags']);
    }

    // -------------------------------------------------------------------------
    // apiVersion parameter
    // -------------------------------------------------------------------------

    public function test_operation_hasParametersKey() : void
    {
        $method = $this->createMethodMock();
        $operation = $this->getOperation($method);

        $this->assertArrayHasKey('parameters', $operation);
    }

    public function test_operation_firstParameterIsApiVersion() : void
    {
        $method = $this->createMethodMock();
        $operation = $this->getOperation($method);

        $this->assertNotEmpty($operation['parameters']);
        $this->assertSame('apiVersion', $operation['parameters'][0]['name']);
    }

    public function test_apiVersionParam_isInQuery() : void
    {
        $method = $this->createMethodMock();
        $operation = $this->getOperation($method);

        $this->assertSame('query', $operation['parameters'][0]['in']);
    }

    public function test_apiVersionParam_isNotRequired() : void
    {
        $method = $this->createMethodMock();
        $operation = $this->getOperation($method);

        $this->assertFalse($operation['parameters'][0]['required']);
    }

    public function test_apiVersionParam_schemaContainsEnumFromVersions() : void
    {
        $method = $this->createMethodMock('TestMethod', 'Desc', 'Group', '', array('1.0', '2.0', '3.0'));
        $operation = $this->getOperation($method);

        $schema = $operation['parameters'][0]['schema'];
        $this->assertSame(array('1.0', '2.0', '3.0'), $schema['enum']);
    }

    public function test_apiVersionParam_schemaDefaultIsCurrentVersion() : void
    {
        $method = $this->createMethodMock('TestMethod', 'Desc', 'Group', '', array('1.0', '2.0'), '2.0');
        $operation = $this->getOperation($method);

        $schema = $operation['parameters'][0]['schema'];
        $this->assertSame('2.0', $schema['default']);
    }

    // -------------------------------------------------------------------------
    // Responses
    // -------------------------------------------------------------------------

    public function test_operation_hasResponsesKey() : void
    {
        $method = $this->createMethodMock();
        $operation = $this->getOperation($method);

        $this->assertArrayHasKey('responses', $operation);
    }

    public function test_operation_responsesHas200And400And500() : void
    {
        $method = $this->createMethodMock();
        $operation = $this->getOperation($method);

        $this->assertArrayHasKey('200', $operation['responses']);
        $this->assertArrayHasKey('400', $operation['responses']);
        $this->assertArrayHasKey('500', $operation['responses']);
    }

    // -------------------------------------------------------------------------
    // Request body — JSON method
    // -------------------------------------------------------------------------

    public function test_jsonMethod_hasNoRequestBody_whenNoBodyParams() : void
    {
        // Without actual body params, requestBody should be omitted (empty params).
        $method = $this->createJsonMethodMock();
        $operation = $this->getOperation($method);

        $this->assertArrayNotHasKey('requestBody', $operation);
    }

    public function test_formEncodedMethod_hasNoRequestBody() : void
    {
        // Standard APIMethodInterface (not JSON) → no request body.
        $method = $this->createMethodMock();
        $operation = $this->getOperation($method);

        $this->assertArrayNotHasKey('requestBody', $operation);
    }

    // -------------------------------------------------------------------------
    // Optional extensions — x-changelog
    // -------------------------------------------------------------------------

    public function test_xChangelog_includedWhenNonEmpty() : void
    {
        $changelog = array('1.0' => 'Initial release.', '2.0' => 'Added feature X.');
        $method = $this->createMethodMock('TestMethod', 'Desc', 'Group', '', array('1.0'), '1.0', $changelog);
        $operation = $this->getOperation($method);

        $this->assertArrayHasKey('x-changelog', $operation);
        $this->assertSame($changelog, $operation['x-changelog']);
    }

    public function test_xChangelog_omittedWhenEmpty() : void
    {
        $method = $this->createMethodMock('TestMethod', 'Desc', 'Group', '', array('1.0'), '1.0', array());
        $operation = $this->getOperation($method);

        $this->assertArrayNotHasKey('x-changelog', $operation);
    }

    // -------------------------------------------------------------------------
    // Optional extensions — x-related-methods
    // -------------------------------------------------------------------------

    public function test_xRelatedMethods_includedWhenNonEmpty() : void
    {
        $related = array('GetComtypes', 'GetComGroup');
        $method = $this->createMethodMock('TestMethod', 'Desc', 'Group', '', array('1.0'), '1.0', array(), $related);
        $operation = $this->getOperation($method);

        $this->assertArrayHasKey('x-related-methods', $operation);
        $this->assertSame($related, $operation['x-related-methods']);
    }

    public function test_xRelatedMethods_omittedWhenEmpty() : void
    {
        $method = $this->createMethodMock();
        $operation = $this->getOperation($method);

        $this->assertArrayNotHasKey('x-related-methods', $operation);
    }

    // -------------------------------------------------------------------------
    // Optional — externalDocs
    // -------------------------------------------------------------------------

    public function test_externalDocs_includedWhenDocUrlNonEmpty() : void
    {
        $method = $this->createMethodMock('TestMethod', 'Desc', 'Group', 'https://example.com/api/TestMethod');
        $operation = $this->getOperation($method);

        $this->assertArrayHasKey('externalDocs', $operation);
        $this->assertSame('https://example.com/api/TestMethod', $operation['externalDocs']['url']);
    }

    public function test_externalDocs_omittedWhenDocUrlEmpty() : void
    {
        $method = $this->createMethodMock('TestMethod', 'Desc', 'Group', '');
        $operation = $this->getOperation($method);

        $this->assertArrayNotHasKey('externalDocs', $operation);
    }
}
