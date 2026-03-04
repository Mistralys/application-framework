<?php
/**
 * @package API
 * @subpackage OpenAPI
 */

declare(strict_types=1);

namespace Application\API\OpenAPI;

/**
 * Maps framework parameter type labels to OpenAPI 3.1 type/format pairs.
 *
 * The mapping covers all types available in {@see \Application\API\Parameters\ParamTypeSelector}.
 * For unknown type labels, the mapper falls back to `string` type.
 *
 * @package API
 * @subpackage OpenAPI
 */
class TypeMapper
{
    public const string TYPE_LABEL_STRING = 'String';
    public const string TYPE_LABEL_INTEGER = 'Integer';
    public const string TYPE_LABEL_BOOLEAN = 'Boolean';
    public const string TYPE_LABEL_ID_LIST = 'ID List';
    public const string TYPE_LABEL_JSON = 'JSON';
    public const string TYPE_LABEL_ALIAS = 'Alias';
    public const string TYPE_LABEL_ALPHABETICAL = 'Alphabetical';
    public const string TYPE_LABEL_ALPHANUMERIC = 'Alphanumeric';
    public const string TYPE_LABEL_DATE = 'Date';
    public const string TYPE_LABEL_EMAIL = 'Email';
    public const string TYPE_LABEL_LABEL = 'Label';
    public const string TYPE_LABEL_MD5 = 'MD5';
    public const string TYPE_LABEL_NAME_OR_TITLE = 'Name or Title';

    /**
     * Maps a framework parameter type label to an OpenAPI 3.1 type/format pair.
     *
     * Returns an associative array with at minimum a `type` key. Where applicable,
     * a `format` key is also present. For the `ID List` type, an `items` key
     * is included describing the array element type.
     *
     * Unknown type labels fall back to `{ "type": "string" }`.
     *
     * @param string $typeLabel The framework parameter type label (e.g. `TypeMapper::TYPE_LABEL_INTEGER`).
     * @return array<string,mixed>
     */
    public static function mapType(string $typeLabel) : array
    {
        switch($typeLabel)
        {
            case self::TYPE_LABEL_INTEGER:
                return array(
                    'type' => 'integer',
                    'format' => 'int64',
                );

            case self::TYPE_LABEL_BOOLEAN:
                return array(
                    'type' => 'boolean',
                );

            case self::TYPE_LABEL_ID_LIST:
                return array(
                    'type' => 'array',
                    'items' => array(
                        'type' => 'integer',
                    ),
                );

            case self::TYPE_LABEL_JSON:
                return array(
                    'type' => 'object',
                );

            case self::TYPE_LABEL_DATE:
                return array(
                    'type' => 'string',
                    'format' => 'date',
                );

            case self::TYPE_LABEL_EMAIL:
                return array(
                    'type' => 'string',
                    'format' => 'email',
                );

            case self::TYPE_LABEL_MD5:
                return array(
                    'type' => 'string',
                    'format' => 'md5',
                );

            case self::TYPE_LABEL_STRING:
            case self::TYPE_LABEL_ALIAS:
            case self::TYPE_LABEL_ALPHABETICAL:
            case self::TYPE_LABEL_ALPHANUMERIC:
            case self::TYPE_LABEL_LABEL:
            case self::TYPE_LABEL_NAME_OR_TITLE:
            default:
                return array(
                    'type' => 'string',
                );
        }
    }
}
