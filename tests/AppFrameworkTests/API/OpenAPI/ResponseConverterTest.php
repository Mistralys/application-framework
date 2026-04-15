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
     * @return APIMethodInterface&\PHPUnit\Framework\MockObject\Stub
     */
    private function createBasicMethodMock(string $responseMime = 'application/json') : APIMethodInterface
    {
        $mock = $this->createStub(APIMethodInterface::class);
        $mock->method('getResponseMime')->willReturn($responseMime);
        return $mock;
    }

    /**
     * Creates a JSONResponseInterface mock.
     *
     * @param array<string,mixed> $example
     * @param KeyDescription[] $keyDescriptions
     * @param string $responseMime
     * @return JSONResponseInterface&\PHPUnit\Framework\MockObject\Stub
     */
    private function createJsonMethodMock(
        array $example = array(),
        array $keyDescriptions = array(),
        string $responseMime = 'application/json'
    ) : JSONResponseInterface {
        $mock = $this->createStub(JSONResponseInterface::class);
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
        $method = $this->createStub(JSONResponseInterface::class);
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

    // -------------------------------------------------------------------------
    // Success response (200) — bare payload & KeyPath-style descriptions
    // -------------------------------------------------------------------------

    public function test_200response_jsonMethod_barePayloadExample_producesAllOfSchema() : void
    {
        // Bare payload (no envelope wrapper) — the real-world format from getExampleJSONResponse().
        $barePayload = array(
            'companyId' => 42,
            'name' => 'ACME',
        );

        $keyDescriptions = array(
            KeyDescription::create('companyId', 'The company identifier.'),
        );

        $method = $this->createJsonMethodMock($barePayload, $keyDescriptions);
        $result = $this->converter->convertResponses($method);
        $content = $result[ResponseConverter::HTTP_200]['content'];
        $mime = array_key_first($content);
        $this->assertIsString($mime);

        $schema = $content[$mime]['schema'];

        // Should use allOf since we have inferred data schema.
        $this->assertArrayHasKey('allOf', $schema);
        $this->assertSame(
            '#/components/schemas/'.OpenAPISchema::SCHEMA_API_ENVELOPE,
            $schema['allOf'][0]['$ref']
        );

        // The data sub-schema should contain the inferred + described properties.
        $dataProperties = $schema['properties']['data']['properties'];
        $this->assertArrayHasKey('companyId', $dataProperties);
        $this->assertSame('integer', $dataProperties['companyId']['type']);
        $this->assertSame('The company identifier.', $dataProperties['companyId']['description']);
        $this->assertArrayHasKey('name', $dataProperties);
        $this->assertSame('string', $dataProperties['name']['type']);
    }

    public function test_200response_jsonMethod_keypathDescriptions_mergedIntoSchema() : void
    {
        // KeyPath-style descriptions (paths relative to data, without `data.` prefix).
        $barePayload = array(
            'grayZoneModes' => array('strict', 'lenient'),
            'mailForgeName' => 'Newsletter Template',
        );

        $keyDescriptions = array(
            KeyDescription::create('grayZoneModes', 'Available gray zone modes.'),
            KeyDescription::create('mailForgeName', 'The MailForge template name.'),
        );

        $method = $this->createJsonMethodMock($barePayload, $keyDescriptions);
        $result = $this->converter->convertResponses($method);
        $content = $result[ResponseConverter::HTTP_200]['content'];
        $mime = array_key_first($content);
        $this->assertIsString($mime);

        $schema = $content[$mime]['schema'];

        // Should use allOf.
        $this->assertArrayHasKey('allOf', $schema);

        // Data properties should have descriptions merged.
        $dataProperties = $schema['properties']['data']['properties'];
        $this->assertSame('Available gray zone modes.', $dataProperties['grayZoneModes']['description']);
        $this->assertSame('The MailForge template name.', $dataProperties['mailForgeName']['description']);
    }
}
