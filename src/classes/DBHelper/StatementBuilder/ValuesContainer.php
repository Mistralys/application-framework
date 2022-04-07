<?php
/**
 * File containing the class {@see DBHelper_StatementBuilder_ValuesContainer}.
 *
 * @package DBHelper
 * @subpackage StatementBuilder
 * @see DBHelper_StatementBuilder_ValuesContainer
 */

declare(strict_types=1);

use DBHelper\StatementBuilder\ValueDefinition;

/**
 * Companion class to the statement builder, used to
 * store placeholder names for fields, tables and more.
 *
 * @package DBHelper
 * @subpackage StatementBuilder
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DBHelper_StatementBuilder_ValuesContainer
{
    public const ERROR_UNKNOWN_PLACEHOLDER_NAME = 95501;

    public const VALUE_TYPE_SYMBOL = 1;
    public const VALUE_TYPE_INTEGER = 2;
    public const VALUE_TYPE_STRING_LITERAL = 3;

    /**
     * @var array<string,ValueDefinition>
     */
    protected $values = array();

    protected ?DBHelper_StatementBuilder_ValuesContainer $container = null;

    private function wrapTicks(string $value) : string
    {
        return '`'.trim($value, '`').'`';
    }

    private function wrapQuotes(string $value) : string
    {
        return "'".trim($value, "'")."'";
    }

    /**
     * @param string $tableName
     * @param string $value
     * @return $this
     */
    public function table(string $tableName, string $value) : self
    {
        return $this->add($tableName, $value, self::VALUE_TYPE_SYMBOL);
    }

    /**
     * @param string $alias
     * @param string $value
     * @return $this
     */
    public function alias(string $alias, string $value) : self
    {
        return $this->add($alias, $value, self::VALUE_TYPE_SYMBOL);
    }

    /**
     * @param string $name
     * @param int $value
     * @return $this
     */
    public function int(string $name, int $value) : self
    {
        return $this->add($name, (string)$value, self::VALUE_TYPE_INTEGER);
    }

    public function text(string $name, string $value) : self
    {
        return $this->add($name, $value,self::VALUE_TYPE_STRING_LITERAL);
    }

    /**
     * @param string $fieldName
     * @param string $value
     * @return $this
     */
    public function field(string $fieldName, string $value) : self
    {
        return $this->add($fieldName, $value, self::VALUE_TYPE_SYMBOL);
    }

    /**
     * @param string $placeholderName
     * @param string $value
     * @param int $valueType
     * @return $this
     */
    protected function add(string $placeholderName, string $value, int $valueType) : self
    {
        $placeholderName = $this->trimPlaceholderName($placeholderName);

        $this->values[$placeholderName] = new ValueDefinition(
            $placeholderName,
            $value,
            $valueType
        );

        return $this;
    }

    public function hasValue(string $placeholderName) : bool
    {
        return isset($this->values[$placeholderName]) || (isset($this->container) && $this->container->hasValue($placeholderName));
    }

    public function getValueDef(string $placeholderName) : ValueDefinition
    {
        if(isset($this->values[$placeholderName]))
        {
            return $this->values[$placeholderName];
        }

        if(isset($this->container) && $this->container->hasValue($placeholderName))
        {
            return $this->container->getValueDef($placeholderName);
        }

        throw new DBHelper_Exception(
            'Placeholder does not exist.',
            sprintf(
                'Could not find placeholder [%s]. Available placeholders are [%s].',
                $placeholderName,
                implode(', ', array_keys($this->values))
            ),
            self::ERROR_UNKNOWN_PLACEHOLDER_NAME
        );
    }

    public function getValue(string $placeholderName) : string
    {
        $def = $this->getValueDef($placeholderName);

        if($def->isWrapTicks())
        {
            return $this->wrapTicks($def->getValue());
        }

        if($def->isStringLiteral())
        {
            return $this->wrapQuotes($def->getValue());
        }

        return $def->getValue();
    }

    /**
     * @param DBHelper_StatementBuilder_ValuesContainer $container
     * @return $this
     */
    public function setContainer(DBHelper_StatementBuilder_ValuesContainer $container) : self
    {
        $this->container = $container;
        return $this;
    }

    /**
     * Creates a new statement builder that inherits
     * this container's placeholder values.
     *
     * @param string $template
     * @return DBHelper_StatementBuilder
     */
    public function statement(string $template) : DBHelper_StatementBuilder
    {
        return statementBuilder($template, $this);
    }

    public function getField(string $name) : ValueDefinition
    {
        return $this->getValueDef($name);
    }

    public function getTable(string $name) : ValueDefinition
    {
        return $this->getValueDef($name);
    }

    public function getAlias(string $name) : ValueDefinition
    {
        return $this->getValueDef($name);
    }

    private function trimPlaceholderName(string $placeholderName) : string
    {
        return trim($placeholderName, '{}');
    }
}
