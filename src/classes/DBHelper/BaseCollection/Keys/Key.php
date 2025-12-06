<?php

declare(strict_types=1);

use Application\AppFactory;
use AppUtils\Microtime;
use DBHelper\BaseRecord\BaseRecordException;

class DBHelper_BaseCollection_Keys_Key
{
    private string $name;

    private bool $required = false;
    private ?string $default = NULL;

    private bool $hasDefault = false;

    /**
     * @var callable|null
     */
    private $validation = null;

    /**
     * @var callable|null
     */
    private $generator = null;

    public function __construct(string $name)
    {
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
     * If the value is not valid, the method must throw an exception.
     *
     * @param callable(mixed, array<string,mixed>, DBHelper_BaseCollection_Keys_Key) : void $callback
     * @return $this
     */
    public function setValidation(callable $callback) : self
    {
        $this->validation = $callback;
        return $this;
    }

    /**
     * Sets a regular expression validation for the key.
     *
     * The regex must be a full regex, including delimiters.
     *
     * @param string $regex
     * @return $this
     */
    public function setRegexValidation(string $regex) : self
    {
        return $this->setValidation(function(mixed $value) use ($regex) : void {
            if(is_string($value) && preg_match($regex, $value)) {
                return;
            }

            throw new DBHelper_Exception(
                'Validation failed',
                sprintf('Value "%s" does not match regex %s', $value, $regex),
                DBHelper_Exception::ERROR_KEY_VALIDATION_FAILED
            );
        });
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
     * @param callable(DBHelper_BaseCollection_Keys_Key, array<string,mixed>): mixed $callback
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
     * @see BaseRecordException::ERROR_CANNOT_GENERATE_KEY_VALUE
     */
    public function generateValue(array $data) : mixed
    {
        if(isset($this->generator))
        {
            return call_user_func($this->generator, $this, $data);
        }

        throw new BaseRecordException(
            'Cannot generate value, no generator present',
            'No callback has been set for generating the value.',
            BaseRecordException::ERROR_CANNOT_GENERATE_KEY_VALUE
        );
    }

    /**
     * Validates the value using the configured validation.
     *
     * @param mixed $value
     * @param array<string,mixed> $dataSet The full data set being used, for looking up other values for the validation.
     */
    public function validate(mixed $value, array $dataSet) : void
    {
        if($this->validation === null)
        {
            return;
        }

        call_user_func($this->validation, $value, $dataSet, $this);
    }

    public function setMicrotimeGenerator() : self
    {
        $this->setMicrotimeValidation();

        return $this->setGenerator(function() : Microtime {
            return Microtime::createNow();
        });
    }

    public function setMicrotimeValidation() : self
    {
        return $this->setValidation(function(mixed $value) : void
        {
            if($value instanceof Microtime) {
                return;
            }

            if(is_string($value)) {
                try{Microtime::createFromString($value);} catch (Throwable $e) {
                    throw new DBHelper_Exception(
                        'Validation failed',
                        sprintf('Value "%s" is not a valid Microtime string: %s', $value, $e->getMessage()),
                        DBHelper_Exception::ERROR_KEY_VALIDATION_FAILED,
                        $e
                    );
                }

                return;
            }

            throw new DBHelper_Exception(
                'Validation failed',
                sprintf('Value "%s" is not a valid Microtime instance or string', $value),
                DBHelper_Exception::ERROR_KEY_VALIDATION_FAILED
            );
        });
    }

    public function setUserValidation() : self
    {
        return $this->setValidation(function(mixed $value) : void
        {
            if(is_string($value) && is_numeric($value)) {
                $value = (int)$value;
            }

            if(is_int($value) && $value > 0 && AppFactory::createUsers()->idExists($value)) {
                return;
            }

            throw new DBHelper_Exception(
                'Validation failed',
                sprintf('Value "%s" is not a valid user ID', $value),
                DBHelper_Exception::ERROR_KEY_VALIDATION_FAILED
            );
        });
    }

    /**
     * @param string[] $allowedValues
     * @return $this
     */
    public function setEnumValidation(array $allowedValues) : self
    {
        return $this->setValidation(function(mixed $value) use ($allowedValues) : void
        {
            if(is_numeric($value)) {
                $value = (string)$value;
            }

            if(in_array($value, $allowedValues, true)) {
                return;
            }

            throw new DBHelper_Exception(
                'Validation failed',
                sprintf(
                    'Key [%1$s] value [%2$s] is not in the allowed set of values: '.PHP_EOL.
                    '- %3$s',
                    $this->getName(),
                    $value,
                    implode(PHP_EOL.'- ', $allowedValues)
                ),
                DBHelper_Exception::ERROR_KEY_VALIDATION_FAILED
            );
        });
    }

    public function setCurrentUserGenerator() : self
    {
        $this->setUserValidation();

        return $this->setGenerator(function() : int {
            return AppFactory::createUser()->getID();
        });
    }
}
