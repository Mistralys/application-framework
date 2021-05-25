<?php

declare(strict_types=1);

class DBHelper_BaseCollection_Keys_Key
{
    const ERROR_CANNOT_GENERATE_VALUE = 87601;

    /**
     * @var DBHelper_BaseCollection_Keys
     */
    private $manager;

    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $required = false;

    /**
     * @var string|NULL
     */
    private $default = NULL;

    /**
     * @var bool
     */
    private $hasDefault = false;

    /**
     * @var callable|null
     */
    private $validation = null;

    /**
     * @var callable|null
     */
    private $generator = null;

    public function __construct(DBHelper_BaseCollection_Keys $manager, string $name)
    {
        $this->manager = $manager;
        $this->name = $name;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function isRequired() : bool
    {
        return $this->required;
    }

    /**
     * Retrieves the default value for the key.
     *
     * IMPORTANT: Also check with `hasDefault()` if
     * this default value should be used at all. Since
     * NULL is a valid default value, that is the only
     * way to check if it's an intentional NULL.
     *
     * @return string|null
     */
    public function getDefault() : ?string
    {
        return $this->default;
    }

    public function makeRequired(bool $required=true) : DBHelper_BaseCollection_Keys_Key
    {
        $this->required = $required;
        return $this;
    }

    /**
     * Sets a callback to validate the key value. This must
     * throw an exception if the value does not match, or it
     * will have no effect.
     *
     * The callback method gets passed the following parameters:
     *
     * 1. The value to validate
     * 2. The full data set being validated (for lookups)
     * 3. The key instance
     *
     * @param callable $callback
     * @return $this
     * @throws Application_Exception
     */
    public function setValidation($callback) : DBHelper_BaseCollection_Keys_Key
    {
        Application::requireCallableValid($callback);

        $this->validation = $callback;
        return $this;
    }

    /**
     * Whether a default value has been specified.
     *
     * @return bool
     */
    public function hasDefault() : bool
    {
        return $this->hasDefault;
    }

    public function setDefault(?string $default) : DBHelper_BaseCollection_Keys_Key
    {
        $this->hasDefault = true;
        $this->default = $default;
        return $this;
    }

    /**
     * Sets a generation callback function that will be used to generate
     * the key's value if none has been specified. Takes precedence before
     * any value set via {@see DBHelper_BaseCollection_Keys_Key::setDefault()}.
     *
     * The callback method gets the following parameters:
     *
     * 1. The key instance, {@see DBHelper_BaseCollection_Keys_Key}
     * 2. The full data set array (to enable lookups)
     *
     * The method must return the generated value.
     *
     * @param callable $callback
     * @return $this
     */
    public function setGenerator(callable $callback) : DBHelper_BaseCollection_Keys_Key
    {
        $this->generator = $callback;
        return $this;
    }

    public function hasGenerator() : bool
    {
        return isset($this->generator);
    }

    /**
     * Generates the key's value according to the generation
     * callback that was set via {@see DBHelper_BaseCollection_Keys_Key::setGenerator()}.
     *
     * @param array $data
     * @return mixed
     * @throws DBHelper_Exception
     *
     * @see DBHelper_BaseCollection_Keys_Key::setGenerator()
     * @see DBHelper_BaseCollection_Keys_Key::ERROR_CANNOT_GENERATE_VALUE
     */
    public function generateValue(array $data)
    {
        if(isset($this->generator))
        {
            return call_user_func($this->generator, $this, $data);
        }

        throw new DBHelper_Exception(
            'Cannot generate value, no generator present',
            'No callback has been set for generating the value.',
            self::ERROR_CANNOT_GENERATE_VALUE
        );
    }

    /**
     * Validates the value using the configured validation.
     *
     * @param mixed $value
     * @param array<string,mixed> $dataSet The full data set being used, for looking up other values for the validation.
     */
    public function validate($value, array $dataSet) : void
    {
        if($this->validation === null)
        {
            return;
        }

        call_user_func($this->validation, $value, $dataSet, $this);
    }
}
