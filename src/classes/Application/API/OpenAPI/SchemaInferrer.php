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
        $dataExample = $this->extractDataExample($fullExample);

        // Only key descriptions that target data-level paths affect the schema.
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
     * Extracts the data payload from the example array.
     *
     * Supports two formats:
     * - **Envelope format:** `{state: string, data: array, ...}` → returns `$fullExample['data']`.
     * - **Bare payload format:** any other non-empty array → returns `$fullExample` as-is.
     *
     * The heuristic treats the example as an envelope only when it contains both a
     * `state` key (string) AND a `data` key (array). This is reliable because real data
     * payloads never contain both keys with those exact types.
     *
     * @param array<string,mixed> $fullExample
     * @return array<mixed>|null The data payload, or null when the example is empty.
     */
    private function extractDataExample(array $fullExample) : ?array
    {
        if(empty($fullExample))
        {
            return null;
        }

        // Envelope format: contains both `state` (string) and `data` (array).
        if(
            isset($fullExample['state'])
            && is_string($fullExample['state'])
            && array_key_exists('data', $fullExample)
            && is_array($fullExample['data'])
        ) {
            return $fullExample['data'];
        }

        // Bare payload format: treat the entire example as the data payload.
        return $fullExample;
    }

    /**
     * Envelope-level keys that are NOT data properties.
     * Paths targeting these are excluded from the data schema.
     */
    private const array ENVELOPE_KEYS = array('state', 'code', 'message', 'api');

    /**
     * Returns true if any of the given key descriptions target a data-level path.
     *
     * Recognized formats:
     * - `data.fieldName` (legacy, backward-compatible)
     * - `fieldName` or `root.child` (paths relative to the data object)
     *
     * Paths targeting envelope-level keys (`state`, `code`, `message`, `api`) are excluded.
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

            // Legacy format: `data.fieldName`
            if(count($parts) >= 2 && $parts[0] === 'data')
            {
                return true;
            }

            // New format: paths relative to the data object (e.g. `fieldName`, `root.child`).
            // Exclude envelope-level keys.
            if(!empty($parts[0]) && !in_array($parts[0], self::ENVELOPE_KEYS, true))
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
     * Merges key description texts into a schema's `properties`.
     *
     * Supports two path formats:
     * - **Legacy:** `data.fieldName` → extracts `fieldName` as the property name.
     * - **Relative:** `fieldName` or `root.child` → treats `$parts[0]` as the property name.
     *
     * Paths targeting envelope-level keys are skipped.
     * Multi-segment paths (e.g. `comGroups.textSnippet`) navigate into nested object
     * and array-item schemas to place the description on the leaf property.
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

            $propertyName = $this->resolveDataPropertyName($parts);

            if($propertyName === null)
            {
                continue;
            }

            // Compute remaining path segments after the resolved first-level property.
            $remainingParts = $this->getRemainingPathParts($parts);

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

            if(empty($remainingParts))
            {
                // Single-segment path: set description directly on this property.
                $schema['properties'][$propertyName]['description'] = $keyDescription->getDescription();
            }
            else
            {
                // Multi-segment path: navigate into nested schema to place the description.
                $schema['properties'][$propertyName] = $this->setDescriptionAtPath(
                    $schema['properties'][$propertyName],
                    $remainingParts,
                    $keyDescription->getDescription()
                );
            }
        }

        return $schema;
    }

    /**
     * Returns the path segments that follow the resolved first-level property name.
     *
     * - Legacy format `data.fieldName[.sub...]`: skips the `data` prefix and the
     *   property name (two leading segments), returning any deeper segments.
     * - Relative format `fieldName[.sub...]`: skips the property name (first segment)
     *   and returns the rest.
     *
     * @param string[] $parts Full path segments as produced by `explode('.', $path)`.
     * @return string[] The trailing segments, or an empty array for single-segment paths.
     */
    private function getRemainingPathParts(array $parts) : array
    {
        // Legacy format: data.fieldName[.sub...] — skip 'data' and the property name.
        if(count($parts) >= 2 && $parts[0] === 'data')
        {
            return array_values(array_slice($parts, 2));
        }

        // Relative format: fieldName[.sub...] — skip the property name.
        return array_values(array_slice($parts, 1));
    }

    /**
     * Recursively navigates a schema tree and sets `description` at the leaf node
     * identified by `$pathParts`.
     *
     * Navigation rules:
     * - **Array schema** (`type: array` with `items`): descend into `items` without
     *   consuming a path segment (the array's items ARE the next navigable level).
     * - **Object schema** (has `properties`): consume the next path segment and
     *   recurse into the named child property.
     * - **Leaf / unresolvable**: when `$pathParts` is exhausted, attach the
     *   description; when navigation fails (missing property, no items/properties),
     *   return the schema unchanged — never throw.
     *
     * @param array<string, mixed> $schema Current schema node.
     * @param string[] $pathParts Remaining path segments to traverse.
     * @param string $description The description text to set on the leaf.
     * @return array<string, mixed> The (possibly updated) schema node.
     */
    private function setDescriptionAtPath(array $schema, array $pathParts, string $description) : array
    {
        if(empty($pathParts))
        {
            $schema['description'] = $description;
            return $schema;
        }

        // Array type: navigate into items without consuming a path segment.
        if(
            isset($schema['type'])
            && $schema['type'] === 'array'
            && isset($schema['items'])
            && is_array($schema['items'])
        ) {
            $schema['items'] = $this->setDescriptionAtPath($schema['items'], $pathParts, $description);
            return $schema;
        }

        // Object type: consume the next segment and recurse into the named property.
        if(!isset($schema['properties']) || !is_array($schema['properties']))
        {
            // No properties to navigate — skip silently.
            return $schema;
        }

        $nextSegment = $pathParts[0];
        $deeper = array_values(array_slice($pathParts, 1));

        if(!array_key_exists($nextSegment, $schema['properties']))
        {
            // Property does not exist — skip silently.
            return $schema;
        }

        $schema['properties'][$nextSegment] = $this->setDescriptionAtPath(
            $schema['properties'][$nextSegment],
            $deeper,
            $description
        );

        return $schema;
    }

    /**
     * Resolves the first data-level property name from path parts.
     *
     * - `['data', 'fieldName', ...]` → `'fieldName'` (legacy `data.` prefix format)
     * - `['fieldName', ...]` → `'fieldName'` (relative path format)
     * - Envelope-level keys → `null` (skipped)
     *
     * @param string[] $parts Path segments.
     * @return string|null The property name, or null if the path should be skipped.
     */
    private function resolveDataPropertyName(array $parts) : ?string
    {
        if(empty($parts) || $parts[0] === '')
        {
            return null;
        }

        // Legacy format: `data.fieldName` — use $parts[1].
        if(count($parts) >= 2 && $parts[0] === 'data')
        {
            return $parts[1];
        }

        // Skip envelope-level keys.
        if(in_array($parts[0], self::ENVELOPE_KEYS, true))
        {
            return null;
        }

        // Relative format: the first segment is the property name.
        return $parts[0];
    }
}
