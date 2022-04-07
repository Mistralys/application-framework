<?php
/**
 * File containing the class {@see \DBHelper\StatementBuilder\ValueDefinition}.
 *
 * @package DBHelper
 * @subpackage StatementBuilder
 * @see \DBHelper\StatementBuilder\ValueDefinition
 */

declare(strict_types=1);

namespace DBHelper\StatementBuilder;

use DBHelper_StatementBuilder_ValuesContainer;

/**
 * Stores information on a single placeholder value
 * in a statement builder.
 *
 * @package DBHelper
 * @subpackage StatementBuilder
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ValueDefinition
{
    private string $name;
    private string $value;
    private int $valueType;

    public function __construct(string $name, string $value, int $valueType)
    {
        $this->name = $name;
        $this->value = $value;
        $this->valueType = $valueType;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getValue() : string
    {
        return $this->value;
    }

    public function isWrapTicks() : bool
    {
        return $this->valueType === DBHelper_StatementBuilder_ValuesContainer::VALUE_TYPE_SYMBOL;
    }

    public function isStringLiteral() : bool
    {
        return $this->valueType === DBHelper_StatementBuilder_ValuesContainer::VALUE_TYPE_STRING_LITERAL;
    }
}
