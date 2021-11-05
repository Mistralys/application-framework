<?php

declare(strict_types=1);

class DBHelper_StatementBuilder_ValuesContainer
{
    public const ERROR_UNKNOWN_PLACEHOLDER_NAME = 95501;

    /**
     * @var array<string,string>
     */
    protected $values = array();

    /**
     * @var DBHelper_StatementBuilder_ValuesContainer|NULL
     */
    protected $container = null;

    private function wrapTicks(string $value) : string
    {
        return '`'.trim($value, '`').'`';
    }

    /**
     * @param string $tableName
     * @param string $value
     * @return $this
     */
    public function table(string $tableName, string $value)
    {
        return $this->add($tableName, $this->wrapTicks($value));
    }

    /**
     * @param string $alias
     * @param string $value
     * @return $this
     */
    public function alias(string $alias, string $value)
    {
        return $this->add($alias, $this->wrapTicks($value));
    }

    /**
     * @param string $name
     * @param int $value
     * @return $this
     */
    public function int(string $name, int $value)
    {
        return $this->add($name, (string)$value);
    }

    /**
     * @param string $fieldName
     * @param string $value
     * @return $this
     */
    public function field(string $fieldName, string $value)
    {
        return $this->add($fieldName, $this->wrapTicks($value));
    }

    /**
     * @param string $placeholderName
     * @param string $value
     * @return $this
     */
    protected function add(string $placeholderName, string $value)
    {
        $placeholderName = trim($placeholderName, '{}');

        $this->values[$placeholderName] = $value;

        return $this;
    }

    public function hasValue(string $placeholderName) : bool
    {
        return array_key_exists($placeholderName, $this->values) || (isset($this->container) && $this->container->hasValue($placeholderName));
    }

    public function getValue(string $placeholderName) : string
    {
        if(array_key_exists($placeholderName, $this->values))
        {
            return $this->values[$placeholderName];
        }

        if(isset($this->container) && $this->container->hasValue($placeholderName))
        {
            return $this->container->getValue($placeholderName);
        }

        throw new DBHelper_Exception(
            'Unknown placeholder name.',
            sprintf(
                'The placeholder [%s] is not known.',
                $placeholderName
            ),
            self::ERROR_UNKNOWN_PLACEHOLDER_NAME
        );
    }

    /**
     * @param DBHelper_StatementBuilder_ValuesContainer $container
     * @return $this
     */
    public function setContainer(DBHelper_StatementBuilder_ValuesContainer $container)
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
}
