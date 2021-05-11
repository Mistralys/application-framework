<?php

declare(strict_types=1);

class DBHelper_BaseCollection_Keys_Key
{
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
