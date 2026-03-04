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
        $keyDescriptions = array(
            KeyDescription::create('meta.version', 'Some meta field.'),
        );

        $result = $this->inferrer->inferDataSchema(array(), $keyDescriptions);

        // Should return empty because the description doesn't apply to the data payload.
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
}
