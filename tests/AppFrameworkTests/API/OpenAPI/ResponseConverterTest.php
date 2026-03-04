<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\OpenAPI;

use Application\API\APIMethodInterface;
use Application\API\OpenAPI\OpenAPISchema;
use Application\API\OpenAPI\ResponseConverter;
use Application\API\Traits\JSONResponseInterface;
use Application\API\Utilities\KeyDescription;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Unit tests for {@see ResponseConverter}.
 *
 * Uses PHPUnit mocks for all framework objects to keep tests isolated
 * from the application bootstrap.
 */
final class ResponseConverterTest extends TestCase
{
    private ResponseConverter $converter;

    protected function setUp(): void
    {
        $this->converter = new ResponseConverter();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Creates a basic APIMethodInterface mock with a configurable response MIME.
     *
     * @param string $responseMime
     * @return APIMethodInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createBasicMethodMock(string $responseMime = 'application/json') : APIMethodInterface
    {
        $mock = $this->createMock(APIMethodInterface::class);
        $mock->method('getResponseMime')->willReturn($responseMime);
        return $mock;
    }

    /**
     * Creates a JSONResponseInterface mock.
     *
     * @param array<string,mixed> $example
     * @param KeyDescription[] $keyDescriptions
     * @param string $responseMime
     * @return JSONResponseInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createJsonMethodMock(
        array $example = array(),
        array $keyDescriptions = array(),
        string $responseMime = 'application/json'
    ) : JSONResponseInterface {
        $mock = $this->createMock(JSONResponseInterface::class);
        $mock->method('getResponseMime')->willReturn($responseMime);
        $mock->method('getExampleJSONResponse')->willReturn($example);
        $mock->method('getReponseKeyDescriptions')->willReturn($keyDescriptions);
        return $mock;
    }

    // -------------------------------------------------------------------------
    // Top-level structure
    // -------------------------------------------------------------------------

    public function test_convertResponses_returnsAllThreeStatusCodes() : void
    {
        $result = $this->converter->convertResponses($this->createBasicMethodMock());

        $this->assertArrayHasKey(ResponseConverter::HTTP_200, $result);
        $this->assertArrayHasKey(ResponseConverter::HTTP_400, $result);
        $this->assertArrayHasKey(ResponseConverter::HTTP_500, $result);
    }

    public function test_httpConstants_haveExpectedValues() : void
    {
        $this->assertSame('200', ResponseConverter::HTTP_200);
        $this->assertSame('400', ResponseConverter::HTTP_400);
        $this->assertSame('500', ResponseConverter::HTTP_500);
    }

    // -------------------------------------------------------------------------
    // Success response (200) — basic method
    // -------------------------------------------------------------------------

    public function test_200response_hasDescription() : void
    {
        $result = $this->converter->convertResponses($this->createBasicMethodMock());

        $this->assertArrayHasKey('description', $result[ResponseConverter::HTTP_200]);
        $this->assertNotEmpty($result[ResponseConverter::HTTP_200]['description']);
    }

    public function test_200response_hasContent() : void
    {
        $result = $this->converter->convertResponses($this->createBasicMethodMock());

        $this->assertArrayHasKey('content', $result[ResponseConverter::HTTP_200]);
    }

    public function test_200response_contentUsesResponseMime() : void
    {
        $result = $this->converter->convertResponses($this->createBasicMethodMock('application/json'));

        $content = $result[ResponseConverter::HTTP_200]['content'];
        $this->assertArrayHasKey('application/json', $content);
    }

    public function test_200response_schemaReferencesApiEnvelope() : void
    {
        $result = $this->converter->convertResponses($this->createBasicMethodMock());

        $content = $result[ResponseConverter::HTTP_200]['content'];
        $mime = array_key_first($content);
        $this->assertIsString($mime);
        $schema = $content[$mime]['schema'];
        $this->assertArrayHasKey('$ref', $schema);
        $this->assertStringContainsString(OpenAPISchema::SCHEMA_API_ENVELOPE, $schema['$ref']);
    }

    // -------------------------------------------------------------------------
    // Success response (200) — JSONResponseInterface with example
    // -------------------------------------------------------------------------

    public function test_200response_jsonMethod_withExample_includesExample() : void
    {
        $example = array('state' => 'success', 'data' => array('id' => 1));
        $method = $this->createJsonMethodMock($example);

        $result = $this->converter->convertResponses($method);
        $content = $result[ResponseConverter::HTTP_200]['content'];
        $mime = array_key_first($content);
        $this->assertIsString($mime);

        $this->assertArrayHasKey('example', $content[$mime]);
        $this->assertSame($example, $content[$mime]['example']);
    }

    public function test_200response_jsonMethod_withEmptyExample_noExampleKey() : void
    {
        $method = $this->createJsonMethodMock(array()); // empty example

        $result = $this->converter->convertResponses($method);
        $content = $result[ResponseConverter::HTTP_200]['content'];
        $mime = array_key_first($content);
        $this->assertIsString($mime);

        $this->assertArrayNotHasKey('example', $content[$mime]);
    }

    public function test_200response_jsonMethod_exceptionInGetExample_noExampleKey() : void
    {
        $method = $this->createMock(JSONResponseInterface::class);
        $method->method('getResponseMime')->willReturn('application/json');
        $method->method('getExampleJSONResponse')->willThrowException(new RuntimeException('DB not connected'));
        $method->method('getReponseKeyDescriptions')->willReturn(array());

        $result = $this->converter->convertResponses($method);
        $content = $result[ResponseConverter::HTTP_200]['content'];
        $mime = array_key_first($content);
        $this->assertIsString($mime);

        // The example must be omitted — no exception should propagate
        $this->assertArrayNotHasKey('example', $content[$mime]);
    }

    // -------------------------------------------------------------------------
    // Success response (200) — key descriptions
    // -------------------------------------------------------------------------

    public function test_200response_keyDescriptions_schemaUsesAllOf() : void
    {
        $keyDescriptions = array(
            KeyDescription::create('data.companyId', 'The company identifier.'),
        );
        $method = $this->createJsonMethodMock(array(), $keyDescriptions);

        $result = $this->converter->convertResponses($method);
        $content = $result[ResponseConverter::HTTP_200]['content'];
        $mime = array_key_first($content);
        $this->assertIsString($mime);

        $schema = $content[$mime]['schema'];
        $this->assertArrayHasKey('allOf', $schema);
    }

    public function test_200response_keyDescriptions_allOfReferencesEnvelope() : void
    {
        $keyDescriptions = array(
            KeyDescription::create('data.name', 'The entity name.'),
        );
        $method = $this->createJsonMethodMock(array(), $keyDescriptions);

        $result = $this->converter->convertResponses($method);
        $content = $result[ResponseConverter::HTTP_200]['content'];
        $mime = array_key_first($content);
        $this->assertIsString($mime);

        $schema = $content[$mime]['schema'];
        $this->assertSame('#/components/schemas/'.OpenAPISchema::SCHEMA_API_ENVELOPE, $schema['allOf'][0]['$ref']);
    }

    public function test_200response_keyDescriptions_dataPropertiesHaveDescriptions() : void
    {
        $keyDescriptions = array(
            KeyDescription::create('data.companyId', 'The company identifier.'),
            KeyDescription::create('data.name', 'The company name.'),
        );
        $method = $this->createJsonMethodMock(array(), $keyDescriptions);

        $result = $this->converter->convertResponses($method);
        $content = $result[ResponseConverter::HTTP_200]['content'];
        $mime = array_key_first($content);
        $this->assertIsString($mime);

        $schema = $content[$mime]['schema'];
        $dataProperties = $schema['properties']['data']['properties'];

        $this->assertArrayHasKey('companyId', $dataProperties);
        $this->assertSame('The company identifier.', $dataProperties['companyId']['description']);
        $this->assertArrayHasKey('name', $dataProperties);
        $this->assertSame('The company name.', $dataProperties['name']['description']);
    }

    public function test_200response_noKeyDescriptions_schemaIsSimpleRef() : void
    {
        $method = $this->createJsonMethodMock(array(), array());

        $result = $this->converter->convertResponses($method);
        $content = $result[ResponseConverter::HTTP_200]['content'];
        $mime = array_key_first($content);
        $this->assertIsString($mime);

        $schema = $content[$mime]['schema'];
        // Without key descriptions, should be a simple $ref, not allOf
        $this->assertArrayHasKey('$ref', $schema);
        $this->assertArrayNotHasKey('allOf', $schema);
    }

    // -------------------------------------------------------------------------
    // Error responses (400 and 500)
    // -------------------------------------------------------------------------

    public function test_400response_hasDescription() : void
    {
        $result = $this->converter->convertResponses($this->createBasicMethodMock());
        $response = $result[ResponseConverter::HTTP_400];

        $this->assertArrayHasKey('description', $response);
        $this->assertNotEmpty($response['description']);
    }

    public function test_400response_schemaReferencesApiErrorEnvelope() : void
    {
        $result = $this->converter->convertResponses($this->createBasicMethodMock());
        $schema = $result[ResponseConverter::HTTP_400]['content']['application/json']['schema'];

        $this->assertArrayHasKey('$ref', $schema);
        $this->assertStringContainsString(OpenAPISchema::SCHEMA_API_ERROR_ENVELOPE, $schema['$ref']);
    }

    public function test_500response_hasDescription() : void
    {
        $result = $this->converter->convertResponses($this->createBasicMethodMock());
        $response = $result[ResponseConverter::HTTP_500];

        $this->assertArrayHasKey('description', $response);
        $this->assertNotEmpty($response['description']);
    }

    public function test_500response_schemaReferencesApiErrorEnvelope() : void
    {
        $result = $this->converter->convertResponses($this->createBasicMethodMock());
        $schema = $result[ResponseConverter::HTTP_500]['content']['application/json']['schema'];

        $this->assertArrayHasKey('$ref', $schema);
        $this->assertStringContainsString(OpenAPISchema::SCHEMA_API_ERROR_ENVELOPE, $schema['$ref']);
    }

    public function test_400and500_referenceErrorEnvelope_not_successEnvelope() : void
    {
        $result = $this->converter->convertResponses($this->createBasicMethodMock());

        $schema400 = $result[ResponseConverter::HTTP_400]['content']['application/json']['schema'];
        $schema500 = $result[ResponseConverter::HTTP_500]['content']['application/json']['schema'];

        $this->assertStringNotContainsString(OpenAPISchema::SCHEMA_API_ENVELOPE.'_', $schema400['$ref']);
        $this->assertStringContainsString(OpenAPISchema::SCHEMA_API_ERROR_ENVELOPE, $schema400['$ref']);
        $this->assertStringContainsString(OpenAPISchema::SCHEMA_API_ERROR_ENVELOPE, $schema500['$ref']);
    }
}
