<?php
/**
 * File containing the class {@see Application_Formable_RecordSettings_ValueSet}.
 *
 * @package Application
 * @subpackage Forms
 * @see Application_Formable_RecordSettings_ValueSet
 */

declare(strict_types=1);

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
 * @see Application_Formable_RecordSettings::filterForStorage()
 */
class Application_Formable_RecordSettings_ValueSet
{
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
    public function setKey(string $name, $value) : Application_Formable_RecordSettings_ValueSet
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
}
