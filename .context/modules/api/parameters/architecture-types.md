# API Parameters - Parameter Types (Public API)
_SOURCE: StringParameter, ClearableStringParameter, IntegerParameter, BooleanParameter, JSONParameter, IDListParameter, StringListParameter, ListParameterTrait_
# StringParameter, ClearableStringParameter, IntegerParameter, BooleanParameter, JSONParameter, IDListParameter, StringListParameter, ListParameterTrait
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── API/
                └── Parameters/
                    └── Type/
                        └── BooleanParameter.php
                        └── ClearableStringParameter.php
                        └── IDListParameter.php
                        └── IntegerParameter.php
                        └── JSONParameter.php
                        └── ListParameterTrait.php
                        └── StringListParameter.php
                        └── StringParam/
                            ├── StringValidations.php
                        └── StringParameter.php

```
###  Path: `/src/classes/Application/API/Parameters/Type/BooleanParameter.php`

```php
namespace Application\API\Parameters\Type;

use AppUtils\ConvertHelper as ConvertHelper;
use Application\API\Parameters\APIParameterException as APIParameterException;
use Application\API\Parameters\APIParameterInterface as APIParameterInterface;
use Application\API\Parameters\BaseAPIParameter as BaseAPIParameter;
use Application\API\Parameters\Validation\ParamValidationInterface as ParamValidationInterface;
use Application\API\Parameters\ValueLookup\SelectableParamValue as SelectableParamValue;
use Application\API\Parameters\ValueLookup\SelectableValueParamInterface as SelectableValueParamInterface;
use Application\API\Parameters\ValueLookup\SelectableValueParamTrait as SelectableValueParamTrait;

/**
 * Boolean parameter type. Also accepts string values that can be converted to boolean,
 * as supported by {@see ConvertHelper::string2bool()}.
 *
 * @package API
 * @subpackage Parameters
 *
 * @property bool|NULL $defaultValue
 */
class BooleanParameter extends BaseAPIParameter implements SelectableValueParamInterface
{
	use SelectableValueParamTrait;

	public function getTypeLabel(): string
	{
		/* ... */
	}


	public function getDefaultValue(): ?bool
	{
		/* ... */
	}


	/**
	 * @param bool|string|int|null $default A boolean value, or a string that can be converted to boolean by {@see ConvertHelper::string2bool()}. Other value types are rejected.
	 * @return $this
	 * @throws APIParameterException {@see APIParameterException::ERROR_INVALID_PARAM_VALUE}
	 */
	public function setDefaultValue(int|float|bool|string|array|null $default): self
	{
		/* ... */
	}


	/**
	 * @param bool|string|int|null $value A boolean value, or a string that can be converted to boolean by {@see ConvertHelper::string2bool()}. Other value types are rejected.
	 * @return $this
	 * @throws APIParameterException {@see APIParameterException::ERROR_INVALID_PARAM_VALUE}
	 */
	public function selectValue(float|int|bool|array|string|null $value): self
	{
		/* ... */
	}


	public function getDefaultSelectableValue(): ?SelectableParamValue
	{
		/* ... */
	}


	public function getValue(): ?bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Parameters/Type/ClearableStringParameter.php`

```php
namespace Application\API\Parameters\Type;

use Application\API\Parameters\Validation\ParamValidationInterface as ParamValidationInterface;

/**
 * Clearable string API parameter with three-state resolution semantics.
 *
 * Unlike {@see StringParameter}, this type distinguishes between an absent
 * parameter and a present-but-empty parameter, enabling Update-style API
 * methods to explicitly clear optional metadata fields:
 *
 * - **Absent** (key not in `$_REQUEST`) → `null`
 * - **Present but empty** (empty string or whitespace-only after trim) → `''`
 * - **Present with value** (non-empty after trim) → trimmed string
 *
 * Reading `$_REQUEST` directly via `array_key_exists()` is intentional:
 * the framework's `RequestParam::get()` discards empty strings before the
 * parameter type ever sees them, which would collapse the absent/empty
 * distinction that this type relies on.
 *
 * @package API
 * @subpackage Parameters
 */
class ClearableStringParameter extends StringParameter
{
	public function getTypeLabel(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Parameters/Type/IDListParameter.php`

```php
namespace Application\API\Parameters\Type;

use AppUtils\ConvertHelper as ConvertHelper;
use Application\API\Parameters\APIParameterException as APIParameterException;
use Application\API\Parameters\BaseAPIParameter as BaseAPIParameter;
use Application\API\Parameters\Validation\ParamValidationInterface as ParamValidationInterface;

/**
 * API Parameter: List of integer IDs as an array.
 *
 * @package API
 * @subpackage Parameters
 *
 * @property int[]|null $defaultValue
 */
class IDListParameter extends BaseAPIParameter
{
	use ListParameterTrait;

	public function getTypeLabel(): string
	{
		/* ... */
	}


	/**
	 * @return int[]
	 */
	public function getDefaultValue(): array
	{
		/* ... */
	}


	/**
	 * @param array<int|string,int|float|string>|string|NULL $default An array of IDs or a comma-separated string of IDs. Set to `NULL` to reset to an empty array. Other value types are ignored.
	 * @return $this
	 */
	public function setDefaultValue(int|float|bool|string|array|null $default): self
	{
		/* ... */
	}


	/**
	 * @param array<int|string,int|float|string>|string|null $value
	 * @return $this
	 * @throws APIParameterException
	 */
	public function selectValue(float|int|bool|array|string|null $value): self
	{
		/* ... */
	}


	/**
	 * @return int[]|null
	 */
	public function getValue(): ?array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Parameters/Type/IntegerParameter.php`

```php
namespace Application\API\Parameters\Type;

use Application\API\Parameters\APIParameterException as APIParameterException;
use Application\API\Parameters\BaseAPIParameter as BaseAPIParameter;
use Application\API\Parameters\Validation\ParamValidationInterface as ParamValidationInterface;

/**
 * Integer API Parameter.
 *
 * > NOTE: Will convert float values to integers, with a warning.
 *
 * @package API
 * @subpackage Parameters
 *
 * @property int|NULL $defaultValue
 */
class IntegerParameter extends BaseAPIParameter
{
	public function getTypeLabel(): string
	{
		/* ... */
	}


	public function getDefaultValue(): ?int
	{
		/* ... */
	}


	/**
	 * @param int|float|string|null $default The default value. Must be numeric or `NULL`, all other types are rejected.
	 * @return $this
	 */
	public function setDefaultValue(int|float|bool|string|array|null $default): self
	{
		/* ... */
	}


	/**
	 * @param int|float|string|null $value String and float values will be converted to integer.
	 * @return $this
	 * @throws APIParameterException
	 */
	public function selectValue(float|int|bool|array|string|null $value): self
	{
		/* ... */
	}


	public function getValue(): ?int
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Parameters/Type/JSONParameter.php`

```php
namespace Application\API\Parameters\Type;

use AppUtils\ConvertHelper\JSONConverter as JSONConverter;
use AppUtils\ConvertHelper\JSONConverter\JSONConverterException as JSONConverterException;
use Application\API\Parameters\APIParameterException as APIParameterException;
use Application\API\Parameters\BaseAPIParameter as BaseAPIParameter;
use Application\API\Parameters\Validation\ParamValidationInterface as ParamValidationInterface;

/**
 * JSON API Parameter: Accepts a JSON string and converts it to an array.
 *
 * > NOTE: If the value is already an array, it will be used as-is.
 *
 * @package API
 * @subpackage Parameters
 *
 * @property array<int|string,mixed>|null $defaultValue
 */
class JSONParameter extends BaseAPIParameter
{
	public function getTypeLabel(): string
	{
		/* ... */
	}


	public function getDefaultValue(): ?array
	{
		/* ... */
	}


	/**
	 * @param array<int|string,mixed>|string|null $value String values will be parsed as JSON.
	 * @return $this
	 * @throws APIParameterException {@see APIParameterException::ERROR_INVALID_PARAM_VALUE}
	 */
	public function selectValue(float|int|bool|array|string|null $value): self
	{
		/* ... */
	}


	/**
	 * @param string|array<int|string,mixed>|null $default String values will be parsed as JSON.
	 * @return $this
	 * @throws APIParameterException
	 */
	public function setDefaultValue(int|float|bool|string|array|null $default): self
	{
		/* ... */
	}


	/**
	 * @return array<int|string,mixed>|null
	 */
	public function getValue(): ?array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Parameters/Type/ListParameterTrait.php`

```php
namespace Application\API\Parameters\Type;

use AppUtils\ConvertHelper as ConvertHelper;
use Application\API\Parameters\APIParameterException as APIParameterException;

/**
 * Shared input normalisation logic for list-type API parameters.
 *
 * Used by {@see IDListParameter} and {@see StringListParameter} to
 * convert raw API input values into arrays.
 *
 * @package API
 * @subpackage Parameters
 */
trait ListParameterTrait
{
}


```
###  Path: `/src/classes/Application/API/Parameters/Type/StringListParameter.php`

```php
namespace Application\API\Parameters\Type;

use AppUtils\ConvertHelper as ConvertHelper;
use Application\API\Parameters\APIParameterException as APIParameterException;
use Application\API\Parameters\BaseAPIParameter as BaseAPIParameter;
use Application\API\Parameters\Validation\ParamValidationInterface as ParamValidationInterface;

/**
 * API Parameter: List of strings as an array.
 *
 * Accepts a comma-separated string or an array of strings. Each item is
 * whitespace-trimmed, and empty strings (including items that become empty
 * after trimming) are filtered out.
 *
 * **Null and empty resolution:**
 * - A `null` request value resolves to `null` (parameter absent).
 * - An empty string `""` resolves to `null`.
 * - An array or comma-separated string where all items are empty after trimming
 *   resolves to `null`.
 *
 * **Usage example:**
 * ```php
 * // Register the parameter on an API method
 * $param = $this->manageParams()
 *     ->addParam('tags', t('Tags'))
 *     ->stringList();
 *
 * // The request value "foo, bar, baz" resolves to ['foo', 'bar', 'baz']
 * // The request value "  , , " resolves to null (all-empty after trim)
 * // The request value null resolves to null (parameter absent)
 *
 * // Set a default value
 * $param->setDefaultValue('foo, bar');        // ['foo', 'bar']
 * $param->setDefaultValue(['foo', 'bar']);     // ['foo', 'bar']
 * $param->setDefaultValue(null);              // [] (empty array, no default)
 *
 * // Force a specific value regardless of request or default
 * $param->selectValue('foo, bar');            // ['foo', 'bar']
 * $param->selectValue(null);                  // [] (empty array)
 * ```
 *
 * **`@property` note:** The `@property string[] $defaultValue` annotation
 * overrides the parent's `mixed`-typed `$defaultValue` property for IDE
 * type-narrowing purposes. It is not a promoted property.
 *
 * @package API
 * @subpackage Parameters
 *
 * @property string[]|null $defaultValue
 */
class StringListParameter extends BaseAPIParameter
{
	use ListParameterTrait;

	public function getTypeLabel(): string
	{
		/* ... */
	}


	/**
	 * @return string[]
	 */
	public function getDefaultValue(): array
	{
		/* ... */
	}


	/**
	 * @param array<int|string,mixed>|string|null $default A comma-separated string or an array of strings. Set to `NULL` to reset to an empty array. Other value types are rejected.
	 * @return $this
	 * @throws APIParameterException {@see APIParameterException::ERROR_INVALID_PARAM_VALUE}
	 */
	public function setDefaultValue(int|float|bool|string|array|null $default): self
	{
		/* ... */
	}


	/**
	 * @param array<int|string,mixed>|string|null $value A comma-separated string or an array of strings. Set to `NULL` to reset to an empty array. Other value types are rejected.
	 * @return $this
	 * @throws APIParameterException {@see APIParameterException::ERROR_INVALID_PARAM_VALUE}
	 */
	public function selectValue(float|int|bool|array|string|null $value): self
	{
		/* ... */
	}


	/**
	 * @return string[]|null
	 */
	public function getValue(): ?array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Parameters/Type/StringParam/StringValidations.php`

```php
namespace Application\API\Parameters\Type\StringParam;

use AppUtils\Microtime as Microtime;
use AppUtils\OperationResult as OperationResult;
use AppUtils\RegexHelper as RegexHelper;
use Application\API\Parameters\Type\StringParameter as StringParameter;
use Application\API\Parameters\Validation\Type\RegexValidation as RegexValidation;
use Throwable as Throwable;

class StringValidations
{
	public const REGEX_ALPHA = '/^[a-zA-Z]+$/';
	public const REGEX_ALNUM = '/^[a-zA-Z0-9]+$/';

	/**
	 * @return StringParameter
	 * @see self::REGEX_ALNUM
	 */
	public function alphanumeric(): StringParameter
	{
		/* ... */
	}


	/**
	 * @return StringParameter
	 * @see self::REGEX_ALPHA
	 */
	public function alphabetical(): StringParameter
	{
		/* ... */
	}


	/**
	 * @param bool $allowCapitalLetters Whether to allow capital letters in the alias.
	 * @return StringParameter
	 * @see RegexHelper::REGEX_ALIAS
	 * @see RegexHelper::REGEX_ALIAS_CAPITALS
	 */
	public function alias(bool $allowCapitalLetters): StringParameter
	{
		/* ... */
	}


	/**
	 * @return StringParameter
	 * @see RegexHelper::REGEX_LABEL
	 */
	public function label(): StringParameter
	{
		/* ... */
	}


	/**
	 * @return StringParameter
	 * @see RegexHelper::REGEX_NAME_OR_TITLE
	 */
	public function nameOrTitle(): StringParameter
	{
		/* ... */
	}


	public function md5(): StringParameter
	{
		/* ... */
	}


	/**
	 * @return StringParameter
	 * @see RegexHelper::REGEX_EMAIL
	 */
	public function email(): StringParameter
	{
		/* ... */
	}


	/**
	 * @return StringParameter
	 * @see RegexHelper::REGEX_URL
	 */
	public function url(): StringParameter
	{
		/* ... */
	}


	/**
	 * @return StringParameter
	 * @see RegexHelper::REGEX_FILENAME
	 */
	public function filename(): StringParameter
	{
		/* ... */
	}


	/**
	 * @return StringParameter
	 * @see Microtime::createFromString()
	 */
	public function date(): StringParameter
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Parameters/Type/StringParameter.php`

```php
namespace Application\API\Parameters\Type;

use AppUtils\RegexHelper as RegexHelper;
use Application\API\Parameters\APIParameterException as APIParameterException;
use Application\API\Parameters\BaseAPIParameter as BaseAPIParameter;
use Application\API\Parameters\Type\StringParam\StringValidations as StringValidations;
use Application\API\Parameters\Validation\ParamValidationInterface as ParamValidationInterface;
use Application\API\Parameters\Validation\Type\MaxLengthValidation as MaxLengthValidation;
use Application\API\Parameters\Validation\Type\RegexValidation as RegexValidation;

/**
 * String API Parameter.
 *
 * - Accepts any string and numeric values.
 * - Numeric values will be converted to strings.
 * - Empty strings will be treated as null values.
 * - Null values will be treated as null values.
 * - Other value types will be ignored, and a warning will be issued.
 *
 * ## Validator helper naming conventions
 *
 * This class exposes two styles of validator-registering helpers that coexist
 * for historical reasons:
 *
 * - **`validateBy*` prefix** — procedural style; e.g. `validateByRegex()`.
 *   Used for validators that apply a validation rule without altering a named
 *   property of the parameter.
 * - **`set*` prefix** — property-setter style; e.g. `setMaxLength()`.
 *   Used for validators that correspond to a named, configurable attribute of
 *   the parameter (the max length is a property of the string, not just a
 *   validation rule).
 *
 * Both styles call `validateBy()` internally and return `$this` for fluent
 * chaining. New helpers should follow the `set*` convention when they model a
 * named parameter attribute, and `validateBy*` when they apply a standalone
 * rule with no corresponding attribute.
 *
 * @package API
 * @subpackage Parameters
 *
 * @property string|null $defaultValue
 */
class StringParameter extends BaseAPIParameter
{
	public function getTypeLabel(): string
	{
		/* ... */
	}


	public function getDefaultValue(): ?string
	{
		/* ... */
	}


	/**
	 * @param string|int|float|null $default Numeric values will be converted to strings. All other types are rejected.
	 * @return $this
	 * @throws APIParameterException {@see APIParameterException::ERROR_INVALID_PARAM_VALUE}
	 */
	public function setDefaultValue(int|float|bool|string|array|null $default): self
	{
		/* ... */
	}


	/**
	 * @param string|int|float|null $value Numeric values will be converted to strings. All other types are rejected.
	 * @return $this
	 * @throws APIParameterException {@see APIParameterException::ERROR_INVALID_PARAM_VALUE}
	 */
	public function selectValue(float|int|bool|array|string|null $value): self
	{
		/* ... */
	}


	/**
	 * Returns a helper to choose among predefined string validations.
	 * @return StringValidations
	 */
	public function validateAs(): StringValidations
	{
		/* ... */
	}


	/**
	 * Registers a regex validation that requires the string value to match
	 * the given PCRE pattern.
	 *
	 * @param string $regex A valid PCRE regex pattern (e.g. `/^[a-z]+$/i`).
	 * @return $this
	 */
	public function validateByRegex(string $regex): self
	{
		/* ... */
	}


	/**
	 * Registers a max length validation that ensures the string value
	 * does not exceed the specified number of characters (multibyte-safe).
	 *
	 * @param int $maxLength Maximum allowed character count.
	 * @return $this
	 */
	public function setMaxLength(int $maxLength): self
	{
		/* ... */
	}


	public function getValue(): ?string
	{
		/* ... */
	}
}


```