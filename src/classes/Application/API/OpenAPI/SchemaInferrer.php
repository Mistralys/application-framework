<?php
/**
 * @package API
 * @subpackage OpenAPI
 */

declare(strict_types=1);

namespace Application\API\OpenAPI;

use Application\API\Utilities\KeyDescription;

/**
 * Infers a JSON Schema (OpenAPI 3.1 compatible) from a PHP example response array.
 *
 * ## Type mapping
 *
 * ```
 * PHP string      → { "type": "string" }
 * PHP int         → { "type": "integer" }
 * PHP float       → { "type": "number" }
 * PHP bool        → { "type": "boolean" }
 * PHP null        → { "nullable": true }
 * PHP list array  → { "type": "array", "items": <schema of first element> }
 * PHP assoc array → { "type": "object", "properties": { … } }
 * ```
 *
 * ## Graceful degradation
 *
 * - Empty sequential arrays produce `{ "type": "array" }` without `items`.
 * - Unknown or complex types fall back to `{ "type": "object" }`.
 * - All failures are silently contained — inference never throws.
 *
 * ## Usage with key descriptions
 *
 * Use {@see inferDataSchema()} to infer the schema for the `data` payload of a full
 * API response array and simultaneously merge property descriptions from
 * {@see KeyDescription} objects.
 *
 * @package API
 * @subpackage OpenAPI
 */
class SchemaInferrer
{
    /**
     * Infers the JSON Schema for the `data` key of a full API example response,
     * and merges property descriptions from key description objects.
     *
     * Returns an empty array when both `$fullExample` and `$keyDescriptions` are empty,
     * so the caller can safely detect "nothing to infer."
     *
     * @param array<string, mixed> $fullExample The complete response array from `getExampleJSONResponse()`.
     * @param KeyDescription[] $keyDescriptions Descriptions from `getReponseKeyDescriptions()`.
     * @return array<string, mixed> An OpenAPI schema object for the data payload, or `[]` when empty.
     */
    public function inferDataSchema(array $fullExample, array $keyDescriptions) : array
    {
        $dataExample = isset($fullExample['data']) && is_array($fullExample['data'])
            ? $fullExample['data']
            : null;

        // Only key descriptions that target the `data.*` namespace affect the schema.
        $hasDataDescriptions = $this->hasDataPathDescriptions($keyDescriptions);

        // Nothing to work with — caller should use the plain $ref schema.
        if($dataExample === null && !$hasDataDescriptions)
        {
            return array();
        }

        if($dataExample !== null)
        {
            $schema = $this->inferSchema($dataExample);
        }
        else
        {
            // No example data: build a generic object as the base for key description overlays.
            $schema = array('type' => 'object');
        }

        return $this->mergeKeyDescriptions($schema, $keyDescriptions);
    }

    /**
     * Returns true if any of the given key descriptions target a `data.*` path.
     *
     * @param KeyDescription[] $keyDescriptions
     * @return bool
     */
    private function hasDataPathDescriptions(array $keyDescriptions) : bool
    {
        foreach($keyDescriptions as $kd)
        {
            $path = ltrim($kd->getPath(), '/');
            $parts = explode('.', $path);

            if(count($parts) >= 2 && $parts[0] === 'data')
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Recursively infers a JSON Schema from a PHP value.
     *
     * @param mixed $value Any PHP value.
     * @return array<string, mixed> An OpenAPI-compatible schema object.
     */
    public function inferSchema(mixed $value) : array
    {
        if(is_string($value))
        {
            return array('type' => 'string');
        }

        if(is_int($value))
        {
            return array('type' => 'integer');
        }

        if(is_float($value))
        {
            return array('type' => 'number');
        }

        if(is_bool($value))
        {
            return array('type' => 'boolean');
        }

        if($value === null)
        {
            // Using OpenAPI 3.0-compatible nullable flag for consistency with the rest of this module.
            return array('nullable' => true);
        }

        if(is_array($value))
        {
            return $this->inferArraySchema($value);
        }

        // Unknown type (e.g. objects, resources) — fall back to a generic object.
        return array('type' => 'object');
    }

    /**
     * Infers a schema for a PHP array, distinguishing between sequential (list) and associative arrays.
     *
     * @param array<mixed, mixed> $array
     * @return array<string, mixed>
     */
    private function inferArraySchema(array $array) : array
    {
        if(empty($array))
        {
            return array('type' => 'array');
        }

        if(array_is_list($array))
        {
            // Sequential (list) array → OpenAPI array with items inferred from the first element.
            return array(
                'type' => 'array',
                'items' => $this->inferSchema($array[0]),
            );
        }

        // Associative (map) array → OpenAPI object with per-key property schemas.
        $properties = array();
        foreach($array as $key => $val)
        {
            $properties[(string)$key] = $this->inferSchema($val);
        }

        return array(
            'type' => 'object',
            'properties' => $properties,
        );
    }

    /**
     * Merges key description texts into a schema's `properties`, following `data.*` paths.
     *
     * Only paths with the prefix `data.` are processed. Paths deeper than one level below
     * `data` (e.g. `data.nested.field`) are currently not merged (graceful skip).
     *
     * @param array<string, mixed> $schema The base schema to merge into.
     * @param KeyDescription[] $keyDescriptions
     * @return array<string, mixed> The schema with descriptions added.
     */
    private function mergeKeyDescriptions(array $schema, array $keyDescriptions) : array
    {
        if(empty($keyDescriptions))
        {
            return $schema;
        }

        foreach($keyDescriptions as $keyDescription)
        {
            $path = ltrim($keyDescription->getPath(), '/');
            $parts = explode('.', $path);

            // Only handle paths under `data` with at least one sub-key.
            if(count($parts) < 2 || $parts[0] !== 'data')
            {
                continue;
            }

            $propertyName = $parts[1];

            // Ensure the schema has a properties container.
            if(!isset($schema['properties']))
            {
                $schema['type'] = 'object';
                $schema['properties'] = array();
            }

            if(!isset($schema['properties'][$propertyName]))
            {
                $schema['properties'][$propertyName] = array();
            }

            $schema['properties'][$propertyName]['description'] = $keyDescription->getDescription();
        }

        return $schema;
    }
}
