<?php
/**
 * @package API
 * @subpackage OpenAPI
 */

declare(strict_types=1);

namespace Application\API\OpenAPI;

/**
 * Defines shared OpenAPI 3.1 component schemas for the standard API response
 * envelopes used throughout the framework's API system.
 *
 * ## Schemas defined
 *
 * - **`APIEnvelope`** — Standard success response: `state`, `data`, `api`.
 * - **`APIErrorEnvelope`** — Standard error response: `state`, `code`, `message`, `data`, `api`.
 * - **`APIInfo`** — The `api` sub-object: method metadata included in every JSON response.
 *
 * The field names and structure follow the serialization implemented in
 * {@see \Application\API\Response\JSONInfoSerializer}.
 *
 * @package API
 * @subpackage OpenAPI
 */
class OpenAPISchema
{
    public const string SCHEMA_API_ENVELOPE = 'APIEnvelope';
    public const string SCHEMA_API_ERROR_ENVELOPE = 'APIErrorEnvelope';
    public const string SCHEMA_API_INFO = 'APIInfo';

    /**
     * Identifies the `apiKey` security scheme defined in {@see self::getSecuritySchemes()}.
     * Used both in the components definition and when applying per-method security requirements.
     */
    public const string SECURITY_SCHEME_API_KEY = 'apiKey';

    /**
     * Returns the shared `components/schemas` definitions for the standard API
     * response envelopes as a valid OpenAPI 3.1 schema array.
     *
     * @return array<string,array<string,mixed>>
     */
    public function getComponentSchemas() : array
    {
        return array(
            self::SCHEMA_API_INFO => $this->buildAPIInfoSchema(),
            self::SCHEMA_API_ENVELOPE => $this->buildAPIEnvelopeSchema(),
            self::SCHEMA_API_ERROR_ENVELOPE => $this->buildAPIErrorEnvelopeSchema(),
        );
    }

    /**
     * Returns the `components/securitySchemes` definitions for API authentication.
     *
     * The framework uses Bearer-token API keys passed in the `Authorization` header.
     * Methods that require authentication implement {@see \Application\API\Clients\API\APIKeyMethodInterface}.
     *
     * @return array<string,array<string,mixed>>
     */
    public function getSecuritySchemes() : array
    {
        return array(
            self::SECURITY_SCHEME_API_KEY => array(
                'type' => 'http',
                'scheme' => 'bearer',
                'description' => 'API key passed as a Bearer token in the Authorization header.',
            ),
        );
    }

    /**
     * Builds the `APIInfo` schema — the `api` metadata sub-object included in
     * every JSON response, as serialized by {@see \Application\API\Response\JSONInfoSerializer}.
     *
     * @return array<string,mixed>
     */
    private function buildAPIInfoSchema() : array
    {
        return array(
            'type' => 'object',
            'description' => 'Metadata about the API method that handled the request.',
            'properties' => array(
                'methodName' => array(
                    'type' => 'string',
                    'description' => 'The name of the API method that handled the request.',
                ),
                'selectedVersion' => array(
                    'type' => 'string',
                    'description' => 'The API version that was active for this request.',
                ),
                'availableVersions' => array(
                    'type' => 'array',
                    'description' => 'All versions supported by this API method.',
                    'items' => array(
                        'type' => 'string',
                    ),
                ),
                'description' => array(
                    'type' => 'string',
                    'description' => 'The description of the API method.',
                ),
                'requestMime' => array(
                    'type' => 'string',
                    'description' => 'The MIME type of the request body.',
                ),
                'responseMime' => array(
                    'type' => 'string',
                    'description' => 'The MIME type of the response body.',
                ),
                'requestTime' => array(
                    'type' => 'string',
                    'format' => 'date-time',
                    'nullable' => true,
                    'description' => 'ISO 8601 timestamp of when the request was processed.',
                ),
                'documentationURL' => array(
                    'type' => 'string',
                    'format' => 'uri',
                    'description' => 'URL to the human-readable documentation for this method.',
                ),
            ),
        );
    }

    /**
     * Builds the `APIEnvelope` schema — the standard success response wrapper.
     *
     * @return array<string,mixed>
     */
    private function buildAPIEnvelopeSchema() : array
    {
        return array(
            'type' => 'object',
            'description' => 'Standard success response envelope returned by all API methods.',
            'required' => array('state', 'data', 'api'),
            'properties' => array(
                'state' => array(
                    'type' => 'string',
                    'enum' => array('success'),
                    'description' => 'Always "success" for a successful response.',
                ),
                'data' => array(
                    'type' => 'object',
                    'description' => 'The response payload. Structure varies per method.',
                ),
                'api' => array(
                    '$ref' => '#/components/schemas/'.self::SCHEMA_API_INFO,
                ),
            ),
        );
    }

    /**
     * Builds the `APIErrorEnvelope` schema — the standard error response wrapper.
     *
     * @return array<string,mixed>
     */
    private function buildAPIErrorEnvelopeSchema() : array
    {
        return array(
            'type' => 'object',
            'description' => 'Standard error response envelope returned when an API method fails.',
            'required' => array('state', 'code', 'message', 'api'),
            'properties' => array(
                'state' => array(
                    'type' => 'string',
                    'enum' => array('error'),
                    'description' => 'Always "error" for an error response.',
                ),
                'code' => array(
                    'type' => 'integer',
                    'description' => 'Application-level error code.',
                ),
                'message' => array(
                    'type' => 'string',
                    'description' => 'Human-readable error message.',
                ),
                'data' => array(
                    'type' => 'object',
                    'description' => 'Optional additional error context payload.',
                ),
                'api' => array(
                    '$ref' => '#/components/schemas/'.self::SCHEMA_API_INFO,
                ),
            ),
        );
    }
}
