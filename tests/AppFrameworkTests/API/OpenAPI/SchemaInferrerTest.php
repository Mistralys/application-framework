<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\OpenAPI;

use Application\API\OpenAPI\SchemaInferrer;
use Application\API\Utilities\KeyDescription;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for {@see SchemaInferrer}.
 */
final class SchemaInferrerTest extends TestCase
{
    private SchemaInferrer $inferrer;

    protected function setUp(): void
    {
        $this->inferrer = new SchemaInferrer();
    }

    // -------------------------------------------------------------------------
    // inferSchema — primitives
    // -------------------------------------------------------------------------

    public function test_inferSchema_string() : void
    {
        $this->assertSame(array('type' => 'string'), $this->inferrer->inferSchema('hello'));
    }

    public function test_inferSchema_emptyString() : void
    {
        $this->assertSame(array('type' => 'string'), $this->inferrer->inferSchema(''));
    }

    public function test_inferSchema_int() : void
    {
        $this->assertSame(array('type' => 'integer'), $this->inferrer->inferSchema(42));
    }

    public function test_inferSchema_float() : void
    {
        $this->assertSame(array('type' => 'number'), $this->inferrer->inferSchema(3.14));
    }

    public function test_inferSchema_bool_true() : void
    {
        $this->assertSame(array('type' => 'boolean'), $this->inferrer->inferSchema(true));
    }

    public function test_inferSchema_bool_false() : void
    {
        $this->assertSame(array('type' => 'boolean'), $this->inferrer->inferSchema(false));
    }

    public function test_inferSchema_null() : void
    {
        $this->assertSame(array('nullable' => true), $this->inferrer->inferSchema(null));
    }

    // -------------------------------------------------------------------------
    // inferSchema — empty array
    // -------------------------------------------------------------------------

    public function test_inferSchema_emptyArray() : void
    {
        $schema = $this->inferrer->inferSchema(array());
        $this->assertSame('array', $schema['type']);
        $this->assertArrayNotHasKey('items', $schema);
    }

    // -------------------------------------------------------------------------
    // inferSchema — sequential (list) arrays
    // -------------------------------------------------------------------------

    public function test_inferSchema_listOfStrings() : void
    {
        $schema = $this->inferrer->inferSchema(array('a', 'b', 'c'));
        $this->assertSame('array', $schema['type']);
        $this->assertSame(array('type' => 'string'), $schema['items']);
    }

    public function test_inferSchema_listOfIntegers() : void
    {
        $schema = $this->inferrer->inferSchema(array(1, 2, 3));
        $this->assertSame('array', $schema['type']);
        $this->assertSame(array('type' => 'integer'), $schema['items']);
    }

    public function test_inferSchema_listOfObjects() : void
    {
        $schema = $this->inferrer->inferSchema(array(
            array('id' => 1, 'name' => 'Alice'),
            array('id' => 2, 'name' => 'Bob'),
        ));

        $this->assertSame('array', $schema['type']);
        $this->assertSame('object', $schema['items']['type']);
        $this->assertArrayHasKey('id', $schema['items']['properties']);
        $this->assertArrayHasKey('name', $schema['items']['properties']);
    }

    public function test_inferSchema_listUsesFirstElementForItemsType() : void
    {
        // Mixed types in a list: only the first element determines items type.
        $schema = $this->inferrer->inferSchema(array('hello', 42, null));
        $this->assertSame('array', $schema['type']);
        $this->assertSame(array('type' => 'string'), $schema['items']);
    }

    // -------------------------------------------------------------------------
    // inferSchema — associative (object) arrays
    // -------------------------------------------------------------------------

    public function test_inferSchema_flatObject() : void
    {
        $schema = $this->inferrer->inferSchema(array(
            'id' => 1,
            'name' => 'Alice',
            'active' => true,
        ));

        $this->assertSame('object', $schema['type']);
        $this->assertSame(array('type' => 'integer'), $schema['properties']['id']);
        $this->assertSame(array('type' => 'string'), $schema['properties']['name']);
        $this->assertSame(array('type' => 'boolean'), $schema['properties']['active']);
    }

    public function test_inferSchema_nestedObject() : void
    {
        $schema = $this->inferrer->inferSchema(array(
            'user' => array(
                'id' => 100,
                'email' => 'user@example.com',
            ),
        ));

        $this->assertSame('object', $schema['type']);
        $this->assertSame('object', $schema['properties']['user']['type']);
        $this->assertSame('integer', $schema['properties']['user']['properties']['id']['type']);
        $this->assertSame('string', $schema['properties']['user']['properties']['email']['type']);
    }

    public function test_inferSchema_objectWithNullProperty() : void
    {
        $schema = $this->inferrer->inferSchema(array('value' => null));

        $this->assertSame('object', $schema['type']);
        $this->assertSame(array('nullable' => true), $schema['properties']['value']);
    }

    public function test_inferSchema_objectWithFloatProperty() : void
    {
        $schema = $this->inferrer->inferSchema(array('price' => 9.99));

        $this->assertSame('object', $schema['type']);
        $this->assertSame(array('type' => 'number'), $schema['properties']['price']);
    }

    // -------------------------------------------------------------------------
    // inferSchema — complex/ambiguous (graceful degradation)
    // -------------------------------------------------------------------------

    public function test_inferSchema_objectWithNestedList() : void
    {
        $schema = $this->inferrer->inferSchema(array(
            'tags' => array('php', 'api', 'openapi'),
        ));

        $this->assertSame('object', $schema['type']);
        $this->assertSame('array', $schema['properties']['tags']['type']);
        $this->assertSame(array('type' => 'string'), $schema['properties']['tags']['items']);
    }

    // -------------------------------------------------------------------------
    // inferDataSchema — entry point
    // -------------------------------------------------------------------------

    public function test_inferDataSchema_emptyExampleAndNoDescriptions_returnsEmpty() : void
    {
        $result = $this->inferrer->inferDataSchema(array(), array());
        $this->assertSame(array(), $result);
    }

    public function test_inferDataSchema_withFlatDataExample() : void
    {
        $fullExample = array(
            'state' => 'success',
            'data' => array(
                'companyId' => 42,
                'name' => 'ACME',
            ),
        );

        $result = $this->inferrer->inferDataSchema($fullExample, array());

        $this->assertSame('object', $result['type']);
        $this->assertSame('integer', $result['properties']['companyId']['type']);
        $this->assertSame('string', $result['properties']['name']['type']);
    }

    public function test_inferDataSchema_exampleWithNoDataKey_usesGenericObject() : void
    {
        // When description is provided but no data key in example, should still work.
        $keyDescriptions = array(
            KeyDescription::create('data.userId', 'The user ID.'),
        );

        $result = $this->inferrer->inferDataSchema(array('state' => 'success'), $keyDescriptions);

        $this->assertSame('object', $result['type']);
        $this->assertArrayHasKey('userId', $result['properties']);
    }

    public function test_inferDataSchema_keyDescriptionsMergedIntoInferredSchema() : void
    {
        $fullExample = array(
            'state' => 'success',
            'data' => array(
                'companyId' => 42,
                'name' => 'ACME',
            ),
        );

        $keyDescriptions = array(
            KeyDescription::create('data.companyId', 'The company identifier.'),
            KeyDescription::create('data.name', 'The company name.'),
        );

        $result = $this->inferrer->inferDataSchema($fullExample, $keyDescriptions);

        // Type inferred from example
        $this->assertSame('integer', $result['properties']['companyId']['type']);
        $this->assertSame('string', $result['properties']['name']['type']);

        // Description merged from key descriptions
        $this->assertSame('The company identifier.', $result['properties']['companyId']['description']);
        $this->assertSame('The company name.', $result['properties']['name']['description']);
    }

    public function test_inferDataSchema_keyDescriptionsOnly_producesObjectWithDescriptions() : void
    {
        $keyDescriptions = array(
            KeyDescription::create('data.id', 'The record ID.'),
        );

        $result = $this->inferrer->inferDataSchema(array(), $keyDescriptions);

        $this->assertSame('object', $result['type']);
        $this->assertSame('The record ID.', $result['properties']['id']['description']);
    }

    public function test_inferDataSchema_nonDataKeyDescriptionSkipped() : void
    {
        // Paths targeting envelope-level keys (state, code, message, api) are excluded
        // from data schema inference because they describe the response envelope, not the
        // data payload. Non-envelope paths like `meta.version` are accepted as data properties.
        $keyDescriptions = array(
            KeyDescription::create('state.detail', 'Some envelope field.'),
        );

        $result = $this->inferrer->inferDataSchema(array(), $keyDescriptions);

        // Should return empty because the description targets an envelope-level key.
        $this->assertSame(array(), $result);
    }

    public function test_inferDataSchema_dataIsEmptyArray_returnsArraySchema() : void
    {
        $fullExample = array(
            'state' => 'success',
            'data' => array(),
        );

        $result = $this->inferrer->inferDataSchema($fullExample, array());

        // Empty array infers as 'array' type.
        $this->assertSame('array', $result['type']);
    }

    public function test_inferDataSchema_dataIsListOfObjects() : void
    {
        $fullExample = array(
            'state' => 'success',
            'data' => array(
                array('id' => 1, 'label' => 'First'),
                array('id' => 2, 'label' => 'Second'),
            ),
        );

        $result = $this->inferrer->inferDataSchema($fullExample, array());

        $this->assertSame('array', $result['type']);
        $this->assertSame('object', $result['items']['type']);
        $this->assertArrayHasKey('id', $result['items']['properties']);
        $this->assertArrayHasKey('label', $result['items']['properties']);
    }

    // -------------------------------------------------------------------------
    // inferDataSchema — bare data payload (Bug A fix)
    // -------------------------------------------------------------------------

    public function test_inferDataSchema_bareDataPayload_infersSchema() : void
    {
        // Bare payload without an envelope wrapper — this is the real-world format
        // returned by getExampleJSONResponse() implementations.
        $barePayload = array(
            'companyId' => 42,
            'name' => 'ACME',
            'active' => true,
        );

        $result = $this->inferrer->inferDataSchema($barePayload, array());

        $this->assertSame('object', $result['type']);
        $this->assertSame('integer', $result['properties']['companyId']['type']);
        $this->assertSame('string', $result['properties']['name']['type']);
        $this->assertSame('boolean', $result['properties']['active']['type']);
    }

    public function test_inferDataSchema_barePayload_withKeyDescriptions_mergesDescriptions() : void
    {
        $barePayload = array(
            'companyId' => 42,
            'name' => 'ACME',
        );

        // Descriptions using the non-prefixed path format (as KeyPath produces).
        $keyDescriptions = array(
            KeyDescription::create('companyId', 'The company identifier.'),
            KeyDescription::create('name', 'The company name.'),
        );

        $result = $this->inferrer->inferDataSchema($barePayload, $keyDescriptions);

        // Types inferred from example
        $this->assertSame('integer', $result['properties']['companyId']['type']);
        $this->assertSame('string', $result['properties']['name']['type']);

        // Descriptions merged from non-prefixed key descriptions
        $this->assertSame('The company identifier.', $result['properties']['companyId']['description']);
        $this->assertSame('The company name.', $result['properties']['name']['description']);
    }

    // -------------------------------------------------------------------------
    // inferDataSchema — key description path prefix tolerance (Bug B fix)
    // -------------------------------------------------------------------------

    public function test_inferDataSchema_keyDescription_withoutDataPrefix_isMerged() : void
    {
        // Single-segment path like `/fieldName` (as KeyPath::create('fieldName') produces).
        $keyDescriptions = array(
            KeyDescription::create('userId', 'The user ID.'),
        );

        $result = $this->inferrer->inferDataSchema(array(), $keyDescriptions);

        $this->assertSame('object', $result['type']);
        $this->assertArrayHasKey('userId', $result['properties']);
        $this->assertSame('The user ID.', $result['properties']['userId']['description']);
    }

    public function test_inferDataSchema_keyDescription_multiSegment_withoutDataPrefix_isMerged() : void
    {
        // Multi-segment path into a non-existent nested structure:
        // without a backing inferred schema there is nothing to navigate into,
        // so the description is silently skipped and the leaf is left undescribed.
        $keyDescriptions = array(
            KeyDescription::create('comGroups.textSnippet', 'The text snippet content.'),
        );

        $result = $this->inferrer->inferDataSchema(array(), $keyDescriptions);

        $this->assertSame('object', $result['type']);
        // The first-level placeholder property is created, but no description is
        // attached because the nested path cannot be resolved without a backing schema.
        $this->assertArrayHasKey('comGroups', $result['properties']);
        $this->assertArrayNotHasKey('description', $result['properties']['comGroups']);
    }

    public function test_inferDataSchema_envelopeKeyDescription_isSkipped() : void
    {
        // Paths targeting envelope-level keys should be skipped.
        $keyDescriptions = array(
            KeyDescription::create('state', 'The response state.'),
        );

        $result = $this->inferrer->inferDataSchema(array(), $keyDescriptions);

        // Should return empty — envelope-level key descriptions don't produce a data schema.
        $this->assertSame(array(), $result);
    }

    // -------------------------------------------------------------------------
    // mergeKeyDescriptions — deep path traversal (WP-002)
    // -------------------------------------------------------------------------

    public function test_mergeKeyDescriptions_deepPath_intoArrayItems() : void
    {
        // Path `items.label` where `items` is an array of objects.
        // The description must be placed on `label` inside `items.properties`,
        // skipping the array wrapper without consuming the path segment.
        $barePayload = array(
            'items' => array(
                array('label' => 'First', 'count' => 1),
            ),
        );

        $keyDescriptions = array(
            KeyDescription::create('items.label', 'The item label.'),
        );

        $result = $this->inferrer->inferDataSchema($barePayload, $keyDescriptions);

        $this->assertSame('object', $result['type']);
        $this->assertSame('array',  $result['properties']['items']['type']);
        $this->assertSame(
            'The item label.',
            $result['properties']['items']['items']['properties']['label']['description']
        );
        // Sibling property must remain untouched.
        $this->assertArrayNotHasKey('description', $result['properties']['items']['items']['properties']['count']);
    }

    public function test_mergeKeyDescriptions_deepPath_intoNestedObject() : void
    {
        // Path `user.name` where `user` is a nested object.
        // The description must be placed on `user.properties.name`.
        $barePayload = array(
            'user' => array(
                'id'   => 1,
                'name' => 'Alice',
            ),
        );

        $keyDescriptions = array(
            KeyDescription::create('user.name', 'The user name.'),
        );

        $result = $this->inferrer->inferDataSchema($barePayload, $keyDescriptions);

        $this->assertSame('object', $result['type']);
        $this->assertSame('object', $result['properties']['user']['type']);
        $this->assertSame(
            'The user name.',
            $result['properties']['user']['properties']['name']['description']
        );
        // Root-level `user` must NOT carry any description itself.
        $this->assertArrayNotHasKey('description', $result['properties']['user']);
        // Sibling property `id` must remain untouched.
        $this->assertArrayNotHasKey('description', $result['properties']['user']['properties']['id']);
    }

    public function test_mergeKeyDescriptions_deepPath_missingIntermediate_gracefullySkipped() : void
    {
        // Path `user.nonExistentProp` where `user` exists but `nonExistentProp` does not.
        // The inferrer must not throw and must leave the schema intact.
        $barePayload = array(
            'user' => array(
                'name' => 'Alice',
            ),
        );

        $keyDescriptions = array(
            KeyDescription::create('user.nonExistentProp', 'Should be silently ignored.'),
        );

        $result = $this->inferrer->inferDataSchema($barePayload, $keyDescriptions);

        // `user` still exists with its inferred structure.
        $this->assertSame('object', $result['properties']['user']['type']);
        // The missing property must NOT have been created.
        $this->assertArrayNotHasKey('nonExistentProp', $result['properties']['user']['properties']);
        // `name` must remain untouched.
        $this->assertArrayNotHasKey('description', $result['properties']['user']['properties']['name']);
    }

    public function test_mergeKeyDescriptions_deepPath_threeLevels() : void
    {
        // Path `a.b.c` where `a` is an object containing `b` which is an object containing `c`.
        // The description must be placed on the third-level property `c`.
        $barePayload = array(
            'a' => array(
                'b' => array(
                    'c' => 'leaf',
                ),
            ),
        );

        $keyDescriptions = array(
            KeyDescription::create('a.b.c', 'Three-level description.'),
        );

        $result = $this->inferrer->inferDataSchema($barePayload, $keyDescriptions);

        $this->assertSame('object', $result['type']);
        $this->assertSame('object', $result['properties']['a']['type']);
        $this->assertSame('object', $result['properties']['a']['properties']['b']['type']);
        $this->assertSame(
            'Three-level description.',
            $result['properties']['a']['properties']['b']['properties']['c']['description']
        );
        // Intermediate nodes must NOT carry descriptions of their own.
        $this->assertArrayNotHasKey('description', $result['properties']['a']);
        $this->assertArrayNotHasKey('description', $result['properties']['a']['properties']['b']);
    }

    public function test_mergeKeyDescriptions_legacyDataPrefix_deepPath() : void
    {
        // Path `data.comGroups.textSnippet` — the legacy `data.` prefix must be stripped
        // and the remainder resolved as `comGroups.textSnippet`.
        // `comGroups` is an array of objects, so the array wrapper is transparent and the
        // description lands on `comGroups.items.properties.textSnippet`.
        $barePayload = array(
            'comGroups' => array(
                array('textSnippet' => 'content', 'otherField' => 'x'),
            ),
        );

        $keyDescriptions = array(
            KeyDescription::create('data.comGroups.textSnippet', 'Legacy data-prefix path.'),
        );

        $result = $this->inferrer->inferDataSchema($barePayload, $keyDescriptions);

        $this->assertSame('object', $result['type']);
        $this->assertSame('array',  $result['properties']['comGroups']['type']);
        $this->assertSame(
            'Legacy data-prefix path.',
            $result['properties']['comGroups']['items']['properties']['textSnippet']['description']
        );
        // Sibling property must remain untouched.
        $this->assertArrayNotHasKey(
            'description',
            $result['properties']['comGroups']['items']['properties']['otherField']
        );
    }
}
