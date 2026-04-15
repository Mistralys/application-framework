<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\OpenAPI;

use Application\API\OpenAPI\TypeMapper;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for {@see TypeMapper}.
 *
 * Verifies that all 13 framework parameter type labels are mapped to the
 * correct OpenAPI 3.1 type/format pairs, and that unknown labels fall back
 * to the `string` type.
 */
final class TypeMapperTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Simple string-like types
    // -------------------------------------------------------------------------

    public function test_stringType() : void
    {
        $result = TypeMapper::mapType(TypeMapper::TYPE_LABEL_STRING);

        $this->assertSame('string', $result['type']);
        $this->assertArrayNotHasKey('format', $result);
    }

    public function test_aliasType() : void
    {
        $result = TypeMapper::mapType(TypeMapper::TYPE_LABEL_ALIAS);

        $this->assertSame('string', $result['type']);
        $this->assertArrayNotHasKey('format', $result);
    }

    public function test_alphabeticalType() : void
    {
        $result = TypeMapper::mapType(TypeMapper::TYPE_LABEL_ALPHABETICAL);

        $this->assertSame('string', $result['type']);
        $this->assertArrayNotHasKey('format', $result);
    }

    public function test_alphanumericType() : void
    {
        $result = TypeMapper::mapType(TypeMapper::TYPE_LABEL_ALPHANUMERIC);

        $this->assertSame('string', $result['type']);
        $this->assertArrayNotHasKey('format', $result);
    }

    public function test_labelType() : void
    {
        $result = TypeMapper::mapType(TypeMapper::TYPE_LABEL_LABEL);

        $this->assertSame('string', $result['type']);
        $this->assertArrayNotHasKey('format', $result);
    }

    public function test_nameOrTitleType() : void
    {
        $result = TypeMapper::mapType(TypeMapper::TYPE_LABEL_NAME_OR_TITLE);

        $this->assertSame('string', $result['type']);
        $this->assertArrayNotHasKey('format', $result);
    }

    // -------------------------------------------------------------------------
    // String types with format
    // -------------------------------------------------------------------------

    public function test_dateType() : void
    {
        $result = TypeMapper::mapType(TypeMapper::TYPE_LABEL_DATE);

        $this->assertSame('string', $result['type']);
        $this->assertSame('date', $result['format']);
    }

    public function test_emailType() : void
    {
        $result = TypeMapper::mapType(TypeMapper::TYPE_LABEL_EMAIL);

        $this->assertSame('string', $result['type']);
        $this->assertSame('email', $result['format']);
    }

    public function test_md5Type() : void
    {
        $result = TypeMapper::mapType(TypeMapper::TYPE_LABEL_MD5);

        $this->assertSame('string', $result['type']);
        $this->assertArrayNotHasKey('format', $result, 'MD5 type must not use the standard format key');
        $this->assertSame('md5', $result['x-format']);
    }

    // -------------------------------------------------------------------------
    // Numeric / boolean types
    // -------------------------------------------------------------------------

    public function test_integerType() : void
    {
        $result = TypeMapper::mapType(TypeMapper::TYPE_LABEL_INTEGER);

        $this->assertSame('integer', $result['type']);
        $this->assertSame('int64', $result['format']);
    }

    public function test_booleanType() : void
    {
        $result = TypeMapper::mapType(TypeMapper::TYPE_LABEL_BOOLEAN);

        $this->assertSame('boolean', $result['type']);
        $this->assertArrayNotHasKey('format', $result);
    }

    // -------------------------------------------------------------------------
    // Complex types
    // -------------------------------------------------------------------------

    public function test_idListType() : void
    {
        $result = TypeMapper::mapType(TypeMapper::TYPE_LABEL_ID_LIST);

        $this->assertSame('array', $result['type']);
        $this->assertArrayHasKey('items', $result);
        $this->assertSame('integer', $result['items']['type']);
        $this->assertArrayNotHasKey('format', $result);
    }

    public function test_jsonType() : void
    {
        $result = TypeMapper::mapType(TypeMapper::TYPE_LABEL_JSON);

        $this->assertSame('object', $result['type']);
        $this->assertArrayNotHasKey('format', $result);
    }

    // -------------------------------------------------------------------------
    // Unknown / fallback
    // -------------------------------------------------------------------------

    public function test_unknownTypeFallsBackToString() : void
    {
        $result = TypeMapper::mapType('SomeUnknownTypeLabel');

        $this->assertSame('string', $result['type']);
    }

    public function test_emptyStringFallsBackToString() : void
    {
        $result = TypeMapper::mapType('');

        $this->assertSame('string', $result['type']);
    }

    // -------------------------------------------------------------------------
    // Coverage: all 13 types mapped
    // -------------------------------------------------------------------------

    public function test_allThirteenTypesReturnArray() : void
    {
        $labels = array(
            TypeMapper::TYPE_LABEL_STRING,
            TypeMapper::TYPE_LABEL_INTEGER,
            TypeMapper::TYPE_LABEL_BOOLEAN,
            TypeMapper::TYPE_LABEL_ID_LIST,
            TypeMapper::TYPE_LABEL_JSON,
            TypeMapper::TYPE_LABEL_ALIAS,
            TypeMapper::TYPE_LABEL_ALPHABETICAL,
            TypeMapper::TYPE_LABEL_ALPHANUMERIC,
            TypeMapper::TYPE_LABEL_DATE,
            TypeMapper::TYPE_LABEL_EMAIL,
            TypeMapper::TYPE_LABEL_LABEL,
            TypeMapper::TYPE_LABEL_MD5,
            TypeMapper::TYPE_LABEL_NAME_OR_TITLE,
        );

        $this->assertCount(13, $labels, 'Exactly 13 framework type labels must be defined.');

        foreach($labels as $label)
        {
            $result = TypeMapper::mapType($label);
            $this->assertArrayHasKey('type', $result, sprintf(
                'Type label "%s" did not produce a result with a "type" key.',
                $label
            ));
        }
    }
}
