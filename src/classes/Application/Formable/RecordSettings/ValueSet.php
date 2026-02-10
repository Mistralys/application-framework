<?php
/**
 * File containing the class {@see Application_Formable_RecordSettings_ValueSet}.
 *
 * @package Application
 * @subpackage Forms
 * @see Application_Formable_RecordSettings_ValueSet
 */

declare(strict_types=1);

use function AppUtils\parseVariable;

/**
 * Stores form values for the record settings, used when
 * filtering the values for storage. The settings can use
 * callbacks to filter the values, and this is used so they
 * can affect the whole value set as well.
 *
 * @package Application
 * @subpackage Forms
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Formable_RecordSettings::collectStorageValues()
 */
class Application_Formable_RecordSettings_ValueSet
{
    public const int ERROR_EMPTY_KEY_VALUE = 146601;
    public const int ERROR_KEY_VALUE_MISMATCH = 146602;

    /**
     * @var array<string,mixed>
     */
    private $values;

    /**
     * @param array<string,mixed> $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function setKey(string $name, $value) : self
    {
        $this->values[$name] = $value;
        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function keyExists(string $name) : bool
    {
        return array_key_exists($name, $this->values);
    }

    /**
     * @param string $name
     * @return $this
     */
    public function removeKey(string $name)
    {
        if(array_key_exists($name, $this->values))
        {
            unset($this->values[$name]);
        }

        return $this;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getKey(string $name)
    {
        if(isset($this->values[$name]))
        {
            return $this->values[$name];
        }

        return null;
    }

    /**
     * @return array<string,mixed>
     */
    public function getValues() : array
    {
        return $this->values;
    }

    public function setKeys(array $keyValues) : self
    {
        foreach($keyValues as $name => $value)
        {
            $this->setKey($name, $value);
        }

        return $this;
    }

    public function requireNotEmpty(string $name) : self
    {
        $value = $this->getKey($name);
        if($value !== null && $value !== '') {
            return $this;
        }

        throw new Application_Exception(
            'Empty key value',
            sprintf(
                'Required key is [%s]. Values provided: %s',
                $name,
                '<pre>'.print_r($this->getValues(), true).'</pre>'
            ),
            self::ERROR_EMPTY_KEY_VALUE
        );
    }

    public function requireSame(string $name, $value) : self
    {
        if($this->getKey($name) === $value) {
            return $this;
        }

        throw new Application_Exception(
            'Key value mismatch',
            sprintf(
                'Required key is [%s], value must be [%s]. Given: [%s]',
                $name,
                parseVariable($value)->enableType(),
                parseVariable($this->getKey($name))->enableType()
            ),
            self::ERROR_KEY_VALUE_MISMATCH
        );
    }
}
