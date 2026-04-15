<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\OpenAPI;

use Application\API\APIMethodInterface;
use Application\API\Collection\APIMethodCollection;
use Application\API\Groups\APIGroupInterface;
use Application\API\OpenAPI\OpenAPIGenerator;
use Application\API\OpenAPI\OpenAPISchema;
use Application\API\Parameters\APIParamManager;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use UI\AdminURLs\AdminURLInterface;

/**
 * Unit tests for {@see OpenAPIGenerator}.
 *
 * Uses PHPUnit mocks throughout to keep tests isolated from the running application.
 */
final class OpenAPIGeneratorTest extends TestCase
{
    private string $tempFile = '';

    protected function tearDown(): void
    {
        // Clean up any temp files written by generate() tests.
        if($this->tempFile !== '' && file_exists($this->tempFile))
        {
            unlink($this->tempFile);
            $dir = dirname($this->tempFile);
            if(is_dir($dir) && count(scandir($dir)) === 2) // only . and ..
            {
                rmdir($dir);
            }
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Creates a stub APIMethodCollection that returns the given methods from getAll().
     *
     * @param APIMethodInterface[] $methods
     * @return APIMethodCollection
     */
    private function createCollectionMock(array $methods = array()) : APIMethodCollection
    {
        $collection = $this->createStub(APIMethodCollection::class);
        $collection->method('getAll')->willReturn($methods);
        return $collection;
    }

    /**
     * Creates a stub APIGroupInterface.
     *
     * @param string $label
     * @param string $description
     * @return APIGroupInterface
     */
    private function createGroupMock(string $label = 'Test Group', string $description = 'A test group.') : APIGroupInterface
    {
        $group = $this->createStub(APIGroupInterface::class);
        $group->method('getLabel')->willReturn($label);
        $group->method('getDescription')->willReturn($description);
        return $group;
    }

    /**
     * Creates a stub APIParamManager with no parameters.
     *
     * @return APIParamManager
     */
    private function createEmptyParamManager() : APIParamManager
    {
        $manager = $this->createStub(APIParamManager::class);
        $manager->method('getParams')->willReturn(array());
        return $manager;
    }

    /**
     * Creates a minimal APIMethodInterface stub for generator tests.
     *
     * All parameters are optional — provide only the values that matter for your test;
     * sensible defaults are used for everything else.
     *
     * @param string $methodName
     * @param APIGroupInterface|null $group
     * @param string $docUrl Documentation URL string cast. Empty string → no externalDocs entry.
     * @param RuntimeException|null $descriptionException When set, getDescription() throws this.
     * @return APIMethodInterface
     */
    private function buildMethodStub(
        string $methodName = 'TestMethod',
        ?APIGroupInterface $group = null,
        string $docUrl = '',
        ?RuntimeException $descriptionException = null
    ) : APIMethodInterface {
        $adminUrl = $this->createStub(AdminURLInterface::class);
        $adminUrl->method('__toString')->willReturn($docUrl);

        $method = $this->createStub(APIMethodInterface::class);
        $method->method('getMethodName')->willReturn($methodName);

        if($descriptionException !== null)
        {
            $method->method('getDescription')->willThrowException($descriptionException);
        }
        else
        {
            $method->method('getDescription')->willReturn('Test description.');
        }

        $method->method('getGroup')->willReturn($group ?? $this->createGroupMock());
        $method->method('getDocumentationURL')->willReturn($adminUrl);
        $method->method('getVersions')->willReturn(array('1.0'));
        $method->method('getCurrentVersion')->willReturn('1.0');
        $method->method('getChangelog')->willReturn(array());
        $method->method('getRelatedMethodNames')->willReturn(array());
        $method->method('getResponseMime')->willReturn('application/json');
        $method->method('manageParams')->willReturn($this->createEmptyParamManager());
        return $method;
    }

    /**
     * Creates a fresh OpenAPIGenerator with a minimal setup.
     *
     * @param APIMethodInterface[] $methods
     * @param string $appName
     * @param string $appVersion
     * @return OpenAPIGenerator
     */
    private function createGenerator(
        array $methods = array(),
        string $appName = 'Test App',
        string $appVersion = '1.0.0'
    ) : OpenAPIGenerator {
        return new OpenAPIGenerator(
            $this->createCollectionMock($methods),
            $appName,
            $appVersion
        );
    }

    // -------------------------------------------------------------------------
    // toArray — top-level structure
    // -------------------------------------------------------------------------

    public function test_toArray_hasOpenApiVersion() : void
    {
        $result = $this->createGenerator()->toArray();
        $this->assertSame(OpenAPIGenerator::OPENAPI_VERSION, $result['openapi']);
    }

    public function test_toArray_openapiVersionIs310() : void
    {
        $result = $this->createGenerator()->toArray();
        $this->assertSame('3.1.0', $result['openapi']);
    }

    public function test_toArray_hasRequiredTopLevelKeys() : void
    {
        $result = $this->createGenerator()->toArray();
        $this->assertArrayHasKey('openapi', $result);
        $this->assertArrayHasKey('info', $result);
        $this->assertArrayHasKey('servers', $result);
        $this->assertArrayHasKey('tags', $result);
        $this->assertArrayHasKey('paths', $result);
        $this->assertArrayHasKey('components', $result);
    }

    // -------------------------------------------------------------------------
    // info section
    // -------------------------------------------------------------------------

    public function test_info_hasTitleMatchingAppName() : void
    {
        $result = $this->createGenerator(array(), 'My API App')->toArray();
        $this->assertSame('My API App', $result['info']['title']);
    }

    public function test_info_hasVersionMatchingAppVersion() : void
    {
        $result = $this->createGenerator(array(), 'App', '2.5.1')->toArray();
        $this->assertSame('2.5.1', $result['info']['version']);
    }

    public function test_info_descriptionOmittedWhenEmpty() : void
    {
        $result = $this->createGenerator()->toArray();
        $this->assertArrayNotHasKey('description', $result['info']);
    }

    public function test_info_descriptionIncludedWhenSet() : void
    {
        $generator = new OpenAPIGenerator(
            $this->createCollectionMock(),
            'App',
            '1.0',
            'Full API description.'
        );

        $result = $generator->toArray();
        $this->assertSame('Full API description.', $result['info']['description']);
    }

    // -------------------------------------------------------------------------
    // servers section
    // -------------------------------------------------------------------------

    public function test_servers_emptyWhenNoServerUrlSet() : void
    {
        $result = $this->createGenerator()->toArray();
        $this->assertSame(array(), $result['servers']);
    }

    public function test_servers_includesUrlWhenSet() : void
    {
        $generator = $this->createGenerator();
        $generator->setServerUrl('https://api.example.com');
        $result = $generator->toArray();

        $this->assertCount(1, $result['servers']);
        $this->assertSame('https://api.example.com', $result['servers'][0]['url']);
    }

    // -------------------------------------------------------------------------
    // paths section
    // -------------------------------------------------------------------------

    public function test_paths_emptyWhenNoMethods() : void
    {
        $result = $this->createGenerator()->toArray();
        $this->assertSame(array(), $result['paths']);
    }

    public function test_paths_hasEntryForEachMethod() : void
    {
        $methods = array(
            $this->buildMethodStub('GetComtypes'),
            $this->buildMethodStub('GetMailings'),
        );

        $result = $this->createGenerator($methods)->toArray();

        $this->assertArrayHasKey('/api/GetComtypes', $result['paths']);
        $this->assertArrayHasKey('/api/GetMailings', $result['paths']);
    }

    public function test_paths_eachEntryHasPostOperation() : void
    {
        $result = $this->createGenerator(array($this->buildMethodStub('TestMethod')))->toArray();

        $this->assertArrayHasKey('post', $result['paths']['/api/TestMethod']);
    }

    public function test_paths_externalDocsUrlIsRelativeWhenDocumentationUrlIsSet() : void
    {
        $method = $this->buildMethodStub(
            methodName: 'GetComtypes',
            docUrl: 'http://example.com/api/documentation.php?method=GetComtypes'
        );

        $result = $this->createGenerator(array($method))->toArray();

        $operation = $result['paths']['/api/GetComtypes']['post'];
        $this->assertArrayHasKey('externalDocs', $operation);
        $this->assertSame(
            'documentation.php?'.APIMethodInterface::REQUEST_PARAM_METHOD.'=GetComtypes',
            $operation['externalDocs']['url']
        );
    }

    public function test_paths_externalDocsAbsentWhenDocumentationUrlIsEmpty() : void
    {
        $result = $this->createGenerator(array($this->buildMethodStub('TestMethod')))->toArray();

        $operation = $result['paths']['/api/TestMethod']['post'];
        $this->assertArrayNotHasKey('externalDocs', $operation);
    }

    // -------------------------------------------------------------------------
    // tags section
    // -------------------------------------------------------------------------

    public function test_tags_emptyWhenNoMethods() : void
    {
        $result = $this->createGenerator()->toArray();
        $this->assertSame(array(), $result['tags']);
    }

    public function test_tags_hasOneTagPerUniqueGroup() : void
    {
        $groupA = $this->createGroupMock('Group A', 'Description of A.');
        $groupB = $this->createGroupMock('Group B', 'Description of B.');

        $methods = array(
            $this->buildMethodStub('Method1', $groupA),
            $this->buildMethodStub('Method2', $groupA), // same group
            $this->buildMethodStub('Method3', $groupB),
        );

        $result = $this->createGenerator($methods)->toArray();

        $this->assertCount(2, $result['tags']);
    }

    public function test_tags_containsGroupLabelAndDescription() : void
    {
        $group = $this->createGroupMock('API Group', 'The main API group.');
        $methods = array($this->buildMethodStub('SomeMethod', $group));

        $result = $this->createGenerator($methods)->toArray();

        $tag = $result['tags'][0];
        $this->assertSame('API Group', $tag['name']);
        $this->assertSame('The main API group.', $tag['description']);
    }

    // -------------------------------------------------------------------------
    // components section
    // -------------------------------------------------------------------------

    public function test_components_hasSchemas() : void
    {
        $result = $this->createGenerator()->toArray();
        $this->assertArrayHasKey('schemas', $result['components']);
    }

    public function test_components_schemasHasApiEnvelope() : void
    {
        $result = $this->createGenerator()->toArray();
        $this->assertArrayHasKey(OpenAPISchema::SCHEMA_API_ENVELOPE, $result['components']['schemas']);
    }

    public function test_components_schemasHasApiErrorEnvelope() : void
    {
        $result = $this->createGenerator()->toArray();
        $this->assertArrayHasKey(OpenAPISchema::SCHEMA_API_ERROR_ENVELOPE, $result['components']['schemas']);
    }

    // -------------------------------------------------------------------------
    // Error resilience
    // -------------------------------------------------------------------------

    public function test_failingMethod_isSkipped_notFatal() : void
    {
        $goodMethod = $this->buildMethodStub('GoodMethod');
        $badMethod = $this->buildMethodStub(
            methodName: 'BadMethod',
            descriptionException: new RuntimeException('DB connection failed')
        );

        $generator = $this->createGenerator(array($goodMethod, $badMethod));
        $result = $generator->toArray();

        // Good method should still be in paths.
        $this->assertArrayHasKey('/api/GoodMethod', $result['paths']);
        // Bad method must be absent.
        $this->assertArrayNotHasKey('/api/BadMethod', $result['paths']);
    }

    public function test_failingMethod_recordedInConversionErrors() : void
    {
        $badMethod = $this->buildMethodStub(
            methodName: 'FailingMethod',
            descriptionException: new RuntimeException('Boom')
        );

        $generator = $this->createGenerator(array($badMethod));
        $generator->toArray();

        $errors = $generator->getConversionErrors();
        $this->assertArrayHasKey('FailingMethod', $errors);
        $this->assertStringContainsString('Boom', $errors['FailingMethod']);
    }

    public function test_conversionErrors_clearedBetweenToArrayCalls() : void
    {
        $generator = $this->createGenerator();
        $generator->toArray();
        $generator->toArray();

        $this->assertSame(array(), $generator->getConversionErrors());
    }

    // -------------------------------------------------------------------------
    // generate() — file output
    // -------------------------------------------------------------------------

    public function test_generate_writesJsonFile() : void
    {
        $this->tempFile = sys_get_temp_dir().'/openapi_test_'.uniqid().'/openapi.json';

        $generator = $this->createGenerator();
        $generator->setOutputPath($this->tempFile);
        $path = $generator->generate();

        $this->assertSame($this->tempFile, $path);
        $this->assertFileExists($this->tempFile);
    }

    public function test_generate_writtenFileIsValidJson() : void
    {
        $this->tempFile = sys_get_temp_dir().'/openapi_test_'.uniqid().'/openapi.json';

        $generator = $this->createGenerator(array($this->buildMethodStub()));
        $generator->setOutputPath($this->tempFile);
        $generator->generate();

        $content = file_get_contents($this->tempFile);
        $this->assertIsString($content);
        $decoded = json_decode($content, true);
        $this->assertIsArray($decoded);
        $this->assertSame('3.1.0', $decoded['openapi']);
    }

    // -------------------------------------------------------------------------
    // setters (fluent)
    // -------------------------------------------------------------------------

    public function test_setOutputPath_returnsSelf() : void
    {
        $generator = $this->createGenerator();
        $result = $generator->setOutputPath('/tmp/test.json');
        $this->assertSame($generator, $result);
    }

    public function test_setServerUrl_returnsSelf() : void
    {
        $generator = $this->createGenerator();
        $result = $generator->setServerUrl('https://example.com');
        $this->assertSame($generator, $result);
    }

    public function test_addOutputReplacement_returnsSelf() : void
    {
        $generator = $this->createGenerator();
        $result = $generator->addOutputReplacement('foo', 'bar');
        $this->assertSame($generator, $result);
    }

    public function test_generate_outputReplacementIsApplied() : void
    {
        $this->tempFile = sys_get_temp_dir().'/openapi_test_'.uniqid().'/openapi.json';

        $generator = $this->createGenerator(
            array(),
            'REPLACE_ME App',
            '1.0.0'
        );
        $generator->setOutputPath($this->tempFile);
        $generator->addOutputReplacement('REPLACE_ME', '{PLACEHOLDER}');
        $generator->generate();

        $content = (string)file_get_contents($this->tempFile);
        $this->assertStringContainsString('{PLACEHOLDER}', $content);
        $this->assertStringNotContainsString('REPLACE_ME', $content);
    }

    public function test_generate_multipleOutputReplacementsAreAllApplied() : void
    {
        $this->tempFile = sys_get_temp_dir().'/openapi_test_'.uniqid().'/openapi.json';

        $generator = $this->createGenerator(
            array(),
            'AppName-ALPHA',
            '1.0.0-BETA'
        );
        $generator->setOutputPath($this->tempFile);
        $generator->addOutputReplacement('ALPHA', '{alpha-placeholder}');
        $generator->addOutputReplacement('BETA', '{beta-placeholder}');
        $generator->generate();

        $content = (string)file_get_contents($this->tempFile);
        $this->assertStringContainsString('{alpha-placeholder}', $content);
        $this->assertStringContainsString('{beta-placeholder}', $content);
        $this->assertStringNotContainsString('ALPHA', $content);
        $this->assertStringNotContainsString('BETA', $content);
    }
}
