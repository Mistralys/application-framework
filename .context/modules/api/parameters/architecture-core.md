# API Parameters - Core Architecture (Public API)
_SOURCE: APIParamManager, APIParameterInterface, BaseAPIParameter, ParamTypeSelector, APIParameterException, ReservedParamInterface_
# APIParamManager, APIParameterInterface, BaseAPIParameter, ParamTypeSelector, APIParameterException, ReservedParamInterface
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── API/
                └── Parameters/
                    └── APIParamManager.php
                    └── APIParameterException.php
                    └── APIParameterInterface.php
                    └── BaseAPIParameter.php
                    └── ParamTypeSelector.php
                    └── ReservedParamInterface.php

```
###  Path: `/src/classes/Application/API/Parameters/APIParamManager.php`

```php
namespace Application\API\Parameters;

use Application\API\APIException as APIException;
use Application\API\APIMethodInterface as APIMethodInterface;
use Application\API\Parameters\Flavors\APIHeaderParameterInterface as APIHeaderParameterInterface;
use Application\API\Parameters\Rules\RuleInterface as RuleInterface;
use Application\API\Parameters\Rules\RuleTypeSelector as RuleTypeSelector;
use Application\API\Parameters\Validation\ParamValidationResults as ParamValidationResults;
use Application\Validation\ValidationResultInterface as ValidationResultInterface;

/**
 * Main API parameter manager for an API method: Handles registering parameters and rules,
 * and validating them all.
 *
 * ## Using rules
 *
 * You may add as many rules as you need with {@see APIParamManager::addRule()}.
 * The rules are executed in the order they were added. They are able to modify
 * the parameters by switching their required/optional status, or invalidating them.
 *
 * @package API
 * @subpackage Parameters
 */
class APIParamManager implements ValidationResultInterface
{
	public function getValidatorLabel(): string
	{
		/* ... */
	}


	public function addParam(string $name, string $label): ParamTypeSelector
	{
		/* ... */
	}


	public function paramExists(string $name): bool
	{
		/* ... */
	}


	public function registerParam(APIParameterInterface $param): self
	{
		/* ... */
	}


	/**
	 * @return APIParameterInterface[]
	 */
	public function getParams(): array
	{
		/* ... */
	}


	/**
	 * @return APIHeaderParameterInterface[]
	 */
	public function getHeaderParams(): array
	{
		/* ... */
	}


	/**
	 * Returns the rule type selector to add a new validation rule.
	 * @return RuleTypeSelector
	 */
	public function addRule(): RuleTypeSelector
	{
		/* ... */
	}


	public function registerRule(RuleInterface $rule): void
	{
		/* ... */
	}


	public function getValidationResults(): ParamValidationResults
	{
		/* ... */
	}


	public function getLogIdentifier(): string
	{
		/* ... */
	}


	/**
	 * @return RuleInterface[]
	 */
	public function getRules(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Parameters/APIParameterException.php`

```php
namespace Application\API\Parameters;

use Application\API\APIException as APIException;

class APIParameterException extends APIException
{
	public const ERROR_PARAM_ALREADY_REGISTERED = 183101;
	public const ERROR_RESERVED_PARAM_NAME = 183102;
	public const ERROR_INVALID_PARAM_CONFIG = 183103;
	public const ERROR_INVALID_PARAM_VALUE = 183104;
	public const ERROR_PARAM_NOT_REGISTERED = 183106;
}


```
###  Path: `/src/classes/Application/API/Parameters/APIParameterInterface.php`

```php
namespace Application\API\Parameters;

use AppUtils\Interfaces\StringableInterface as StringableInterface;
use AppUtils\OperationResult as OperationResult;
use Application\API\APIMethodInterface as APIMethodInterface;
use Application\API\Parameters\Validation\ParamValidationInterface as ParamValidationInterface;
use Application\Validation\ValidationLoggableInterface as ValidationLoggableInterface;
use Application\Validation\ValidationResults as ValidationResults;

/**
 * Interface for API method parameters.
 *
 * A base implementation is provided by {@see BaseAPIParameter}.
 *
 * @package API
 * @subpackage Parameters
 */
interface APIParameterInterface extends ValidationLoggableInterface
{
	public const RESERVED_PARAM_NAMES = [
		APIMethodInterface::REQUEST_PARAM_METHOD,
		APIMethodInterface::REQUEST_PARAM_API_VERSION,
	];

	public function getName(): string;


	public function getTypeLabel(): string;


	/**
	 * @param bool $required
	 * @return $this
	 */
	public function makeRequired(bool $required = true): self;


	public function isRequired(): bool;


	public function getLabel(): string;


	/**
	 * @return int|string|float|bool|array<int|string,mixed>|null
	 */
	public function getDefaultValue(): int|string|float|bool|array|null;


	/**
	 * @return int|float|bool|string|array<int|string,mixed>|null
	 */
	public function getValue(): int|float|bool|string|array|null;


	public function hasValue(): bool;


	/**
	 * Manually selects the value to use for the parameter.
	 * The value will be used instead of any value provided
	 * in the request.
	 *
	 * If the parameter implements {@see SelectableValueParamInterface},
	 * this will also check that the value exists in the selectable values.
	 *
	 * @param int|float|bool|string|array<int|string,mixed>|null $value Note: Set to `NULL` to clear any selected value.
	 * @return $this
	 */
	public function selectValue(int|float|bool|string|array|null $value): self;


	/**
	 * @param int|float|bool|string|array<int|string,mixed>|null $default
	 * @return $this
	 */
	public function setDefaultValue(int|float|bool|string|array|null $default): self;


	public function getDescription(): string;


	public function hasDescription(): bool;


	public function setDescription(string|StringableInterface $description): self;


	public function getValidationResults(): ValidationResults;


	public function isValid(): bool;


	/**
	 * @param ParamValidationInterface $validation
	 * @return $this
	 */
	public function validateBy(ParamValidationInterface $validation): self;


	/**
	 * Call a custom callback to validate the parameter value.
	 *
	 * If the value is invalid, the callback must call {@see OperationResult::makeError()}
	 * on the provided result object.
	 *
	 * @param (callable(int|float|bool|string|array<int|string,mixed>, OperationResult, mixed...) : void) $callback
	 * @param mixed ...$args Additional arguments to pass to the callback after the value and OperationResult.
	 * @return self
	 */
	public function validateByCallback(callable $callback, ...$args): self;


	/**
	 * @param array<int,int|float|string|bool|array<int|string,mixed>> $values
	 * @return $this
	 */
	public function validateByEnum(array $values): self;


	/**
	 * Invalidate this parameter. This causes it to be excluded from the final output
	 * when validating the required parameters for a request using validation rules.
	 *
	 * @return $this
	 */
	public function invalidate(): self;


	/**
	 * Whether this parameter is enabled. Disabled parameters are ignored for value
	 * resolution (they are disabled automatically when they have been invalidated,
	 * for example).
	 *
	 * @return bool
	 */
	public function isInvalidated(): bool;
}


```
###  Path: `/src/classes/Application/API/Parameters/BaseAPIParameter.php`

```php
namespace Application\API\Parameters;

use AppUtils\Interfaces\StringableInterface as StringableInterface;
use AppUtils\OperationResult as OperationResult;
use AppUtils\Request\RequestParam as RequestParam;
use Application\API\Parameters\Flavors\APIHeaderParameterInterface as APIHeaderParameterInterface;
use Application\API\Parameters\Validation\ParamValidationInterface as ParamValidationInterface;
use Application\API\Parameters\Validation\Type\CallbackValidation as CallbackValidation;
use Application\API\Parameters\Validation\Type\EnumValidation as EnumValidation;
use Application\API\Parameters\Validation\Type\RequiredValidation as RequiredValidation;
use Application\API\Parameters\Validation\Type\ValueExistsCallbackValidation as ValueExistsCallbackValidation;
use Application\API\Parameters\ValueLookup\SelectableValueParamInterface as SelectableValueParamInterface;
use Application\AppFactory as AppFactory;
use Application\Validation\ValidationLoggableTrait as ValidationLoggableTrait;
use Application\Validation\ValidationResults as ValidationResults;
use Application_Request as Application_Request;
use Application_Traits_Loggable as Application_Traits_Loggable;

abstract class BaseAPIParameter implements APIParameterInterface
{
	use Application_Traits_Loggable;
	use ValidationLoggableTrait;

	public function getLogIdentifier(): string
	{
		/* ... */
	}


	public function getName(): string
	{
		/* ... */
	}


	public function makeRequired(bool $required = true): self
	{
		/* ... */
	}


	public function isRequired(): bool
	{
		/* ... */
	}


	public function getRequestParam(): RequestParam
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	public function getDescription(): string
	{
		/* ... */
	}


	public function hasDescription(): bool
	{
		/* ... */
	}


	/**
	 * @param string|StringableInterface $description
	 * @param mixed ...$args Optional parameters for `sprintf`.
	 * @return $this
	 */
	public function setDescription(string|StringableInterface $description, ...$args): self
	{
		/* ... */
	}


	/**
	 * @param (callable(int|float|bool|string|array<int|string,mixed>, OperationResult, APIParameterInterface, mixed...) : void) $callback
	 * @param mixed ...$args
	 * @return $this
	 */
	public function validateByCallback(callable $callback, ...$args): self
	{
		/* ... */
	}


	/**
	 * @param (callable(int|float|bool|string|array<int|string,mixed>|null) : bool) $callback
	 * @return $this
	 */
	public function validateByValueExistsCallback(callable $callback): self
	{
		/* ... */
	}


	/**
	 * @param array<int|string,int|float|string|bool> $values
	 * @return $this
	 */
	public function validateByEnum(array $values): self
	{
		/* ... */
	}


	public function selectValue(int|float|bool|string|array|null $value): self
	{
		/* ... */
	}


	/**
	 * @param int|float|bool|string|array<int|string,mixed>|null $default
	 * @return $this
	 */
	public function setDefaultValue(int|float|bool|string|array|null $default): self
	{
		/* ... */
	}


	public function hasValue(): bool
	{
		/* ... */
	}


	public function getValue(): int|float|bool|string|array|null
	{
		/* ... */
	}


	public function getValidationResults(): ValidationResults
	{
		/* ... */
	}


	public function isValid(): bool
	{
		/* ... */
	}


	public function validateBy(ParamValidationInterface $validation): self
	{
		/* ... */
	}


	public function invalidate(): self
	{
		/* ... */
	}


	public function isInvalidated(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Parameters/ParamTypeSelector.php`

```php
namespace Application\API\Parameters;

use Application\API\Parameters\CommonTypes\AliasParameter as AliasParameter;
use Application\API\Parameters\CommonTypes\AlphabeticalParameter as AlphabeticalParameter;
use Application\API\Parameters\CommonTypes\AlphanumericParameter as AlphanumericParameter;
use Application\API\Parameters\CommonTypes\DateParameter as DateParameter;
use Application\API\Parameters\CommonTypes\EmailParameter as EmailParameter;
use Application\API\Parameters\CommonTypes\LabelParameter as LabelParameter;
use Application\API\Parameters\CommonTypes\MD5Parameter as MD5Parameter;
use Application\API\Parameters\CommonTypes\NameOrTitleParameter as NameOrTitleParameter;
use Application\API\Parameters\Type\BooleanParameter as BooleanParameter;
use Application\API\Parameters\Type\IDListParameter as IDListParameter;
use Application\API\Parameters\Type\IntegerParameter as IntegerParameter;
use Application\API\Parameters\Type\JSONParameter as JSONParameter;
use Application\API\Parameters\Type\StringListParameter as StringListParameter;
use Application\API\Parameters\Type\StringParameter as StringParameter;

class ParamTypeSelector
{
	public function boolean(): BooleanParameter
	{
		/* ... */
	}


	public function integer(): IntegerParameter
	{
		/* ... */
	}


	public function string(): StringParameter
	{
		/* ... */
	}


	/**
	 * List of strings as an array.
	 *
	 * Accepts a comma-separated string or an array of strings.
	 * Each item is whitespace-trimmed; empty strings are filtered out.
	 * Null and all-empty input resolves to null.
	 *
	 * @return StringListParameter
	 */
	public function stringList(): StringListParameter
	{
		/* ... */
	}


	/**
	 * List of integer IDs as an array.
	 *
	 * @return IDListParameter
	 * @throws APIParameterException
	 */
	public function idList(): IDListParameter
	{
		/* ... */
	}


	public function JSON(): JSONParameter
	{
		/* ... */
	}


	public function alias(bool $allowCapitalLetters): AliasParameter
	{
		/* ... */
	}


	public function alphabetical(): AlphabeticalParameter
	{
		/* ... */
	}


	public function alphanumeric(): AlphanumericParameter
	{
		/* ... */
	}


	public function date(): DateParameter
	{
		/* ... */
	}


	public function email(): EmailParameter
	{
		/* ... */
	}


	public function label(): LabelParameter
	{
		/* ... */
	}


	public function md5(): MD5Parameter
	{
		/* ... */
	}


	public function nameOrTitle(): NameOrTitleParameter
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Parameters/ReservedParamInterface.php`

```php
namespace Application\API\Parameters;

/**
 * Interface for reserved API parameters.
 *
 * @package API
 * @subpackage Parameters
 */
interface ReservedParamInterface extends APIParameterInterface
{
	public function isEditable(): bool;
}


```
---
**File Statistics**
- **Size**: 13.52 KB
- **Lines**: 613
File: `modules/api/parameters/architecture-core.md`
