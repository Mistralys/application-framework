<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\OpenAPI;

use Application\API\OpenAPI\OpenAPISchema;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for {@see OpenAPISchema}.
 *
 * Verifies that `getComponentSchemas()` returns valid OpenAPI 3.1 schema
 * definitions for `APIEnvelope`, `APIErrorEnvelope`, and `APIInfo`.
 */
final class OpenAPISchemaTest extends TestCase
{
    private OpenAPISchema $schema;

    protected function setUp(): void
    {
        $this->schema = new OpenAPISchema();
    }

    // -------------------------------------------------------------------------
    // Top-level structure
    // -------------------------------------------------------------------------

    public function test_getComponentSchemas_returnsAllThreeSchemas() : void
    {
        $schemas = $this->schema->getComponentSchemas();

        $this->assertArrayHasKey(OpenAPISchema::SCHEMA_API_ENVELOPE, $schemas);
        $this->assertArrayHasKey(OpenAPISchema::SCHEMA_API_ERROR_ENVELOPE, $schemas);
        $this->assertArrayHasKey(OpenAPISchema::SCHEMA_API_INFO, $schemas);
    }

    public function test_getComponentSchemas_returnsExactlyThreeEntries() : void
    {
        $schemas = $this->schema->getComponentSchemas();

        $this->assertCount(3, $schemas);
    }

    // -------------------------------------------------------------------------
    // APIInfo schema
    // -------------------------------------------------------------------------

    public function test_apiInfoSchema_isObject() : void
    {
        $schemas = $this->schema->getComponentSchemas();
        $info = $schemas[OpenAPISchema::SCHEMA_API_INFO];

        $this->assertSame('object', $info['type']);
    }

    public function test_apiInfoSchema_hasExpectedProperties() : void
    {
        $schemas = $this->schema->getComponentSchemas();
        $info = $schemas[OpenAPISchema::SCHEMA_API_INFO];

        $this->assertArrayHasKey('properties', $info);

        $expectedProperties = array(
            'methodName',
            'selectedVersion',
            'availableVersions',
            'description',
            'requestMime',
            'responseMime',
            'requestTime',
            'documentationURL',
        );

        foreach($expectedProperties as $property)
        {
            $this->assertArrayHasKey(
                $property,
                $info['properties'],
                sprintf('APIInfo schema is missing expected property "%s".', $property)
            );
        }
    }

    public function test_apiInfoSchema_methodNameIsString() : void
    {
        $schemas = $this->schema->getComponentSchemas();
        $this->assertSame('string', $schemas[OpenAPISchema::SCHEMA_API_INFO]['properties']['methodName']['type']);
    }

    public function test_apiInfoSchema_availableVersionsIsArray() : void
    {
        $schemas = $this->schema->getComponentSchemas();
        $prop = $schemas[OpenAPISchema::SCHEMA_API_INFO]['properties']['availableVersions'];

        $this->assertSame('array', $prop['type']);
        $this->assertArrayHasKey('items', $prop);
        $this->assertSame('string', $prop['items']['type']);
    }

    public function test_apiInfoSchema_requestTimeIsNullable() : void
    {
        $schemas = $this->schema->getComponentSchemas();
        $prop = $schemas[OpenAPISchema::SCHEMA_API_INFO]['properties']['requestTime'];

        $this->assertSame('string', $prop['type']);
        $this->assertSame('date-time', $prop['format']);
        $this->assertTrue($prop['nullable']);
    }

    // -------------------------------------------------------------------------
    // APIEnvelope schema
    // -------------------------------------------------------------------------

    public function test_apiEnvelopeSchema_isObject() : void
    {
        $schemas = $this->schema->getComponentSchemas();
        $envelope = $schemas[OpenAPISchema::SCHEMA_API_ENVELOPE];

        $this->assertSame('object', $envelope['type']);
    }

    public function test_apiEnvelopeSchema_stateEnumContainsSuccess() : void
    {
        $schemas = $this->schema->getComponentSchemas();
        $envelope = $schemas[OpenAPISchema::SCHEMA_API_ENVELOPE];

        $this->assertArrayHasKey('properties', $envelope);
        $this->assertArrayHasKey('state', $envelope['properties']);
        $this->assertContains('success', $envelope['properties']['state']['enum']);
    }

    public function test_apiEnvelopeSchema_hasDataProperty() : void
    {
        $schemas = $this->schema->getComponentSchemas();
        $envelope = $schemas[OpenAPISchema::SCHEMA_API_ENVELOPE];

        $this->assertArrayHasKey('data', $envelope['properties']);
        $this->assertSame('object', $envelope['properties']['data']['type']);
    }

    public function test_apiEnvelopeSchema_apiPropertyReferencesAPIInfo() : void
    {
        $schemas = $this->schema->getComponentSchemas();
        $envelope = $schemas[OpenAPISchema::SCHEMA_API_ENVELOPE];

        $this->assertArrayHasKey('api', $envelope['properties']);
        $this->assertArrayHasKey('$ref', $envelope['properties']['api']);
        $this->assertStringContainsString(OpenAPISchema::SCHEMA_API_INFO, $envelope['properties']['api']['$ref']);
    }

    public function test_apiEnvelopeSchema_requiredContainsStateDataApi() : void
    {
        $schemas = $this->schema->getComponentSchemas();
        $envelope = $schemas[OpenAPISchema::SCHEMA_API_ENVELOPE];

        $this->assertArrayHasKey('required', $envelope);
        $this->assertContains('state', $envelope['required']);
        $this->assertContains('data', $envelope['required']);
        $this->assertContains('api', $envelope['required']);
    }

    // -------------------------------------------------------------------------
    // APIErrorEnvelope schema
    // -------------------------------------------------------------------------

    public function test_apiErrorEnvelopeSchema_isObject() : void
    {
        $schemas = $this->schema->getComponentSchemas();
        $envelope = $schemas[OpenAPISchema::SCHEMA_API_ERROR_ENVELOPE];

        $this->assertSame('object', $envelope['type']);
    }

    public function test_apiErrorEnvelopeSchema_stateEnumContainsError() : void
    {
        $schemas = $this->schema->getComponentSchemas();
        $envelope = $schemas[OpenAPISchema::SCHEMA_API_ERROR_ENVELOPE];

        $this->assertArrayHasKey('properties', $envelope);
        $this->assertArrayHasKey('state', $envelope['properties']);
        $this->assertContains('error', $envelope['properties']['state']['enum']);
    }

    public function test_apiErrorEnvelopeSchema_hasCodeAsInteger() : void
    {
        $schemas = $this->schema->getComponentSchemas();
        $envelope = $schemas[OpenAPISchema::SCHEMA_API_ERROR_ENVELOPE];

        $this->assertArrayHasKey('code', $envelope['properties']);
        $this->assertSame('integer', $envelope['properties']['code']['type']);
    }

    public function test_apiErrorEnvelopeSchema_hasMessageAsString() : void
    {
        $schemas = $this->schema->getComponentSchemas();
        $envelope = $schemas[OpenAPISchema::SCHEMA_API_ERROR_ENVELOPE];

        $this->assertArrayHasKey('message', $envelope['properties']);
        $this->assertSame('string', $envelope['properties']['message']['type']);
    }

    public function test_apiErrorEnvelopeSchema_apiPropertyReferencesAPIInfo() : void
    {
        $schemas = $this->schema->getComponentSchemas();
        $envelope = $schemas[OpenAPISchema::SCHEMA_API_ERROR_ENVELOPE];

        $this->assertArrayHasKey('api', $envelope['properties']);
        $this->assertArrayHasKey('$ref', $envelope['properties']['api']);
        $this->assertStringContainsString(OpenAPISchema::SCHEMA_API_INFO, $envelope['properties']['api']['$ref']);
    }

    public function test_apiErrorEnvelopeSchema_requiredContainsStateCodeMessageApi() : void
    {
        $schemas = $this->schema->getComponentSchemas();
        $envelope = $schemas[OpenAPISchema::SCHEMA_API_ERROR_ENVELOPE];

        $this->assertArrayHasKey('required', $envelope);
        $this->assertContains('state', $envelope['required']);
        $this->assertContains('code', $envelope['required']);
        $this->assertContains('message', $envelope['required']);
        $this->assertContains('api', $envelope['required']);
    }

    // -------------------------------------------------------------------------
    // Constant values
    // -------------------------------------------------------------------------

    public function test_schemaConstants_matchKeys() : void
    {
        $this->assertSame('APIEnvelope', OpenAPISchema::SCHEMA_API_ENVELOPE);
        $this->assertSame('APIErrorEnvelope', OpenAPISchema::SCHEMA_API_ERROR_ENVELOPE);
        $this->assertSame('APIInfo', OpenAPISchema::SCHEMA_API_INFO);
    }
}
