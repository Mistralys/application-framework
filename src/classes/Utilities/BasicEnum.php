<?php

declare(strict_types=1);

use function AppUtils\parseVariable;

// Error constants are outside the class
// on purpose, as it uses reflection to
// fetch the list of available constants.
// This would pollute the enum's values
// with the error constants.

const ENUM_ERROR_NAME_DOES_NOT_EXIST = 87001;
const ENUM_ERROR_INVALID_VALUE = 87002;

abstract class BasicEnum
{
    /**
     * @var array<string,array<string,mixed>>
     */
    private static $values = array();

    /**
     * Retrieves the names of all available enum values.
     * @return string[]
     */
    public static function getNames() : array
    {
        return array_keys(self::resolveEnumValues(static::class));
    }

    /**
     * Retrieves all constants for the specified enum class.
     *
     * @param string $enumClass
     * @return array<string,mixed>
     */
    private static function resolveEnumValues(string $enumClass) : array
    {
        self::initEnumValues($enumClass);

        return self::$values[$enumClass];
    }

    /**
     * Initializes the list of available constant names,
     * once per enum class. Stores the names both in the
     * original case, and in lowercase for a non-strict
     * check.
     *
     * @param string $enumClass
     */
    private static function initEnumValues(string $enumClass) : void
    {
        if(isset(self::$values[$enumClass])) {
            return;
        }

        $reflectionClass = new ReflectionClass(static::class);
        self::$values[$enumClass] = $reflectionClass->getConstants();

        ksort(self::$values[$enumClass]);
    }

    /**
     * Retrieves all available values in the enum, as
     * constant name => value pairs.
     *
     * @return array<string,string|int|float|bool|NULL>
     */
    public static function getValues() : array
    {
        return self::resolveEnumValues(static::class);
    }

    public static function isValidName(string $name) : bool
    {
        $values = self::resolveEnumValues(static::class);

        return isset($values[$name]);
    }

    public static function isValidValue($value) : bool
    {
        $values = array_values(self::resolveEnumValues(static::class));
        return in_array($value, $values, true);
    }

    public static function requireValidValue($value) : void
    {
        if(self::isValidValue($value))
        {
            return;
        }

        throw new Application_Exception(
            'Invalid enum value.',
            sprintf(
                'The enum [%s] does not have the value [%s].',
                static::class,
                parseVariable($value)->enableType()->toString()
            ),
            ENUM_ERROR_INVALID_VALUE
        );
    }

    /**
     * Attempts to retrieve the name of an enum's constant
     * by its value.
     *
     * @param string|int|float|bool $value
     * @return string
     * @throws Application_Exception
     *
     * @see BasicEnum::_ERROR_VALUE_DOES_NOT_EXIST
     * @see BasicEnum::isValidValue()
     */
    public static function getNameByValue($value) : string
    {
        $values = self::resolveEnumValues(static::class);

        foreach($values as $name => $checkValue) {
            if($value === $checkValue) {
                return $name;
            }
        }

        throw new Application_Exception(
            'Unknown enum constant name',
            sprintf(
                'Tried getting name for value [%s].',
                parseVariable($value)->enableType()->toString()
            ),
            ENUM_ERROR_NAME_DOES_NOT_EXIST
        );
    }
}
