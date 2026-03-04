<?php
/**
 * @package API
 * @subpackage OpenAPI
 */

declare(strict_types=1);

namespace Application\API\OpenAPI;

use Application\API\APIMethodInterface;
use Application\API\Traits\JSONResponseInterface;

/**
 * Converts an API method's response metadata into OpenAPI 3.1 response objects.
 *
 * ## Success response (200)
 *
 * All methods get a 200 response referencing the shared `APIEnvelope` schema.
 * Methods implementing {@see JSONResponseInterface} additionally receive:
 * - An `example` value from `getExampleJSONResponse()` (when available; exceptions cause a silent skip).
 * - A richer `data` sub-schema inferred by {@see SchemaInferrer} from the example payload, augmented with
 *   property descriptions from `getReponseKeyDescriptions()`.
 *
 * ## Error responses (400, 500)
 *
 * Both error responses reference the shared `APIErrorEnvelope` schema via `$ref`.
 *
 * @package API
 * @subpackage OpenAPI
 */
class ResponseConverter
{
    public const string HTTP_200 = '200';
    public const string HTTP_400 = '400';
    public const string HTTP_500 = '500';

    /**
     * Converts a method's response metadata into a map of HTTP status codes → OpenAPI response objects.
     *
     * @param APIMethodInterface $method
     * @return array{'200': array<string,mixed>, '400': array<string,mixed>, '500': array<string,mixed>}
     */
    public function convertResponses(APIMethodInterface $method) : array
    {
        return array(
            self::HTTP_200 => $this->buildSuccessResponse($method),
            self::HTTP_400 => $this->buildErrorResponse('Validation error or invalid parameters.'),
            self::HTTP_500 => $this->buildErrorResponse('Internal server error.'),
        );
    }

    /**
     * Builds the 200 success response object for the given method.
     *
     * For methods implementing {@see JSONResponseInterface}, example data and
     * an inferred data schema are added when available. {@see SchemaInferrer} handles
     * both type inference from the example and merging of key descriptions.
     * Exceptions from `getExampleJSONResponse()` are silently ignored.
     *
     * @param APIMethodInterface $method
     * @return array<string,mixed>
     */
    private function buildSuccessResponse(APIMethodInterface $method) : array
    {
        $responseMime = $method->getResponseMime();

        $contentEntry = array(
            'schema' => array(
                '$ref' => '#/components/schemas/'.OpenAPISchema::SCHEMA_API_ENVELOPE,
            ),
        );

        if($method instanceof JSONResponseInterface)
        {
            $example = null;

            // Gracefully attempt to include the example response.
            try {
                $raw = $method->getExampleJSONResponse();
                if(!empty($raw)) {
                    $contentEntry['example'] = $raw;
                    $example = $raw;
                }
            } catch(\Throwable $e) {
                // Silently omit — method may require database/env state.
            }

            // Use SchemaInferrer to produce a richer data sub-schema, combining type inference
            // from the example with property descriptions from key descriptions.
            $keyDescriptions = $method->getReponseKeyDescriptions();
            $dataSchema = (new SchemaInferrer())->inferDataSchema($example ?? array(), $keyDescriptions);

            if(!empty($dataSchema)) {
                // Use allOf to extend the referenced schema with the enriched data sub-schema.
                $contentEntry['schema'] = array(
                    'allOf' => array(
                        array('$ref' => '#/components/schemas/'.OpenAPISchema::SCHEMA_API_ENVELOPE),
                    ),
                    'properties' => array(
                        'data' => $dataSchema,
                    ),
                );
            }
        }

        return array(
            'description' => 'Successful response.',
            'content' => array(
                $responseMime => $contentEntry,
            ),
        );
    }

    /**
     * Builds a standard error response object referencing the `APIErrorEnvelope` schema.
     *
     * @param string $description Human-readable description for the HTTP status.
     * @return array<string,mixed>
     */
    private function buildErrorResponse(string $description) : array
    {
        return array(
            'description' => $description,
            'content' => array(
                'application/json' => array(
                    'schema' => array(
                        '$ref' => '#/components/schemas/'.OpenAPISchema::SCHEMA_API_ERROR_ENVELOPE,
                    ),
                ),
            ),
        );
    }
}
