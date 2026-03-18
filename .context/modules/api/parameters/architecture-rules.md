# API Parameters - Rules (Public API)
_SOURCE: RuleInterface, BaseRule, OrRule, RequiredIfOtherIsSetRule, RequiredIfOtherValueEquals, RuleTypeSelector, ParamSet, ParamSetInterface, CustomParamSetInterface, BaseCustomParamSet_
# RuleInterface, BaseRule, OrRule, RequiredIfOtherIsSetRule, RequiredIfOtherValueEquals, RuleTypeSelector, ParamSet, ParamSetInterface, CustomParamSetInterface, BaseCustomParamSet
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── API/
                └── Parameters/
                    └── Rules/
                        └── BaseCustomParamSet.php
                        └── BaseRule.php
                        └── CustomParamSetInterface.php
                        └── ParamSet.php
                        └── ParamSetInterface.php
                        └── RuleInterface.php
                        └── RuleTypeSelector.php
                        └── Type/
                            └── OrRule.php
                            └── RequiredIfOtherIsSetRule.php
                            └── RequiredIfOtherValueEquals.php

```
###  Path: `/src/classes/Application/API/Parameters/Rules/BaseCustomParamSet.php`

```php
namespace Application\API\Parameters\Rules;

use Application\API\APIMethodInterface as APIMethodInterface;
use Application\API\Parameters\APIParameterInterface as APIParameterInterface;
use Application\API\Parameters\ParamSet as ParamSet;
use Application\API\Parameters\Rules\Type\OrRule as OrRule;

/**
 * Helper abstract class to create custom parameter sets:
 * Instead of instantiating {@see ParamSet} directly, extend
 * this class to work in a more structured way.
 *
 * This is especially useful when working with the {@see OrRule},
 * for example: Getting the valid parameter set with {@see OrRule::getValidSet()}
 * then returns an instance of the custom parameter set class,
 * allowing to add custom methods to retrieve values in a type-safe way.
 *
 * @package API
 * @subpackage Parameters
 */
abstract class BaseCustomParamSet extends ParamSet
{
	/**
	 * Initialize parameters for the custom parameter set.
	 * Register them using {@see self::registerParam()}.
	 *
	 * @return void
	 */
	abstract protected function initParams(): void;


	abstract protected function _getID(): string;


	protected function registerParam(APIParameterInterface $param): void
	{
		/* ... */
	}


	final public function getMethod(): APIMethodInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Parameters/Rules/BaseRule.php`

```php
namespace Application\API\Parameters\Rules;

use AppUtils\Interfaces\StringableInterface as StringableInterface;
use AppUtils\OperationResult_Collection as OperationResult_Collection;
use Application\Validation\ValidationLoggableTrait as ValidationLoggableTrait;
use Application\Validation\ValidationResults as ValidationResults;
use Application_Traits_Loggable as Application_Traits_Loggable;

/**
 * Abstract base class to implement validation rules.
 *
 * @package API
 * @subpackage Parameters
 */
abstract class BaseRule implements RuleInterface
{
	use Application_Traits_Loggable;
	use ValidationLoggableTrait;

	public function getLabel(): string
	{
		/* ... */
	}


	public function isRequired(): bool
	{
		/* ... */
	}


	public function setRequired(bool $required): self
	{
		/* ... */
	}


	public function setDescription(string|StringableInterface $description): self
	{
		/* ... */
	}


	public function getDescription(): string
	{
		/* ... */
	}


	public function getLogIdentifier(): string
	{
		/* ... */
	}


	public function apply(): self
	{
		/* ... */
	}


	public function isValid(): bool
	{
		/* ... */
	}


	public function getValidationResults(): ValidationResults
	{
		/* ... */
	}


	abstract protected function _validate(): void;
}


```
###  Path: `/src/classes/Application/API/Parameters/Rules/CustomParamSetInterface.php`

```php
namespace Application\API\Parameters\Rules;

use Application\API\APIMethodInterface as APIMethodInterface;
use Application\API\Parameters\ParamSetInterface as ParamSetInterface;

/**
 * Interface for custom parameter sets that need access to their API method.
 * A base implementation is provided by {@see BaseCustomParamSet}.
 *
 * @package API
 * @subpackage Parameters
 */
interface CustomParamSetInterface extends ParamSetInterface
{
	public function getMethod(): APIMethodInterface;
}


```
###  Path: `/src/classes/Application/API/Parameters/Rules/ParamSet.php`

```php
namespace Application\API\Parameters;

use Application\API\Parameters\Rules\Type\OrRule as OrRule;
use Application_Interfaces_Loggable as Application_Interfaces_Loggable;
use Application_Traits_Loggable as Application_Traits_Loggable;

/**
 * Class used to hold a set of parameters for use in rules.
 * For example, the `OR`rule: {@see OrRule::addSet()}.
 *
 * @package API
 * @subpackage Parameters
 */
class ParamSet implements ParamSetInterface, Application_Interfaces_Loggable
{
	use Application_Traits_Loggable;

	public function getLabel(): string
	{
		/* ... */
	}


	protected function generateLabel(): string
	{
		/* ... */
	}


	public function setLabel(?string $label): self
	{
		/* ... */
	}


	public function getLogIdentifier(): string
	{
		/* ... */
	}


	public function getID(): string
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


	public function isValid(): bool
	{
		/* ... */
	}


	public function apply(): self
	{
		/* ... */
	}


	public function __toString()
	{
		/* ... */
	}


	public function resetRequiredState(): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Parameters/Rules/ParamSetInterface.php`

```php
namespace Application\API\Parameters;

use AppUtils\Interfaces\StringableInterface as StringableInterface;

/**
 * Interface for sets of parameters, used in rules.
 * The implementation is provided by {@see ParamSet}.
 *
 * @package API
 * @subpackage Parameters
 */
interface ParamSetInterface extends StringableInterface
{
	public function getLabel(): string;


	public function setLabel(?string $label): self;


	public function getID(): string;


	/**
	 * @return APIParameterInterface[]
	 */
	public function getParams(): array;


	public function isValid(): bool;


	public function apply(): self;


	/**
	 * Mark all parameters as not required.
	 * @return self
	 */
	public function resetRequiredState(): self;
}


```
###  Path: `/src/classes/Application/API/Parameters/Rules/RuleInterface.php`

```php
namespace Application\API\Parameters\Rules;

use AppUtils\Interfaces\StringableInterface as StringableInterface;
use Application\API\Parameters\APIParameterInterface as APIParameterInterface;
use Application\Validation\ValidationLoggableInterface as ValidationLoggableInterface;
use Application\Validation\ValidationResults as ValidationResults;
use UI as UI;

/**
 * Interface for validation rules.
 *
 * A base implementation is available in {@see BaseRule}.
 *
 * @package API
 * @subpackage Parameters
 */
interface RuleInterface extends ValidationLoggableInterface
{
	public const VALIDATION_NO_PARAM_SET_MATCHED = 183601;

	public function getID(): string;


	public function getLabel(): string;


	public function getDescription(): string;


	public function getTypeLabel(): string;


	public function getTypeDescription(): string;


	/**
	 * @param string|StringableInterface $description
	 * @return $this
	 */
	public function setDescription(string|StringableInterface $description): self;


	/**
	 * Applies the rule. This must be called after {@see self::preValidate()}.
	 *
	 * > Note: This is done only once, subsequent calls will have no effect.
	 *
	 * @return $this
	 */
	public function apply(): self;


	public function isValid(): bool;


	/**
	 * NOTE: Rules are required by default.
	 * @return bool
	 */
	public function isRequired(): bool;


	public function setRequired(bool $required): self;


	public function getValidationResults(): ValidationResults;


	/**
	 * Runs all preparations that the rule needs to do before validation.
	 * This should be called for all rules before any validation is done.
	 *
	 * @return void
	 */
	public function preValidate(): void;


	public function renderDocumentation(UI $ui): string;


	/**
	 * @return APIParameterInterface[]
	 */
	public function getParams(): array;
}


```
###  Path: `/src/classes/Application/API/Parameters/Rules/RuleTypeSelector.php`

```php
namespace Application\API\Parameters\Rules;

use Application\API\Parameters\APIParamManager as APIParamManager;
use Application\API\Parameters\APIParameterInterface as APIParameterInterface;
use Application\API\Parameters\Rules\Type\OrRule as OrRule;
use Application\API\Parameters\Rules\Type\RequiredIfOtherIsSetRule as RequiredIfOtherIsSetRule;
use Application\API\Parameters\Rules\Type\RequiredIfOtherValueEquals as RequiredIfOtherValueEquals;

/**
 * Utility selector class for different types of validation rules
 * to add to an API method's parameters.
 *
 * @package API
 * @subpackage Parameters
 */
class RuleTypeSelector
{
	public function or(string $label): OrRule
	{
		/* ... */
	}


	public function requiredIfOtherIsSet(
		string $label,
		APIParameterInterface $target,
		APIParameterInterface $other,
	): RequiredIfOtherIsSetRule
	{
		/* ... */
	}


	public function requiredIfOtherValueEquals(
		string $label,
		APIParameterInterface $target,
		APIParameterInterface $other,
		mixed $expectedValue,
	): RequiredIfOtherValueEquals
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Parameters/Rules/Type/OrRule.php`

```php
namespace Application\API\Parameters\Rules\Type;

use Application\API\APIException as APIException;
use Application\API\Parameters\ParamSetInterface as ParamSetInterface;
use Application\API\Parameters\Rules\BaseRule as BaseRule;
use Application\API\Parameters\Rules\RuleInterface as RuleInterface;
use UI as UI;

/**
 * Handles switching between different sets of parameters,
 *
 * Validates from top to bottom: The first set of parameters that
 * are all present and valid will be accepted, all others ignored.
 *
 * > **NOTE**: It is best to add this rule before all other rules,
 * > so that the invalidation of parameters is handled correctly.
 *
 * @package API
 * @subpackage Parameters
 */
class OrRule extends BaseRule
{
	public const RULE_ID = 'OR';

	public function getID(): string
	{
		/* ... */
	}


	public function getTypeLabel(): string
	{
		/* ... */
	}


	public function getTypeDescription(): string
	{
		/* ... */
	}


	/**
	 * Add a set of parameters, where at least one set must be complete and valid.
	 *
	 * @param ParamSetInterface $set
	 * @return $this
	 */
	public function addSet(ParamSetInterface $set): self
	{
		/* ... */
	}


	protected function _validate(): void
	{
		/* ... */
	}


	public function getValidSet(): ?ParamSetInterface
	{
		/* ... */
	}


	/**
	 * Get the valid parameter set after validation (non-null-safe).
	 *
	 * > NOTE: This is safe to use after the validation has run.
	 * > If no valid set was found, an error response will have been sent,
	 * > and this exception will not be thrown.
	 *
	 * @return ParamSetInterface
	 * @throws APIException
	 */
	public function requireValidSet(): ParamSetInterface
	{
		/* ... */
	}


	public function preValidate(): void
	{
		/* ... */
	}


	public function renderDocumentation(UI $ui): string
	{
		/* ... */
	}


	public function getParams(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Parameters/Rules/Type/RequiredIfOtherIsSetRule.php`

```php
namespace Application\API\Parameters\Rules\Type;

use Application\API\Parameters\APIParameterInterface as APIParameterInterface;
use Application\API\Parameters\Rules\BaseRule as BaseRule;
use UI as UI;

/**
 * Validation rule: Make a parameter required if another parameter is set (not null).
 *
 * @package API
 * @subpackage Parameters
 */
class RequiredIfOtherIsSetRule extends BaseRule
{
	public const RULE_ID = 'REQUIRED_IF_OTHER_IS_SET';

	public function getID(): string
	{
		/* ... */
	}


	public function getParams(): array
	{
		/* ... */
	}


	protected function _validate(): void
	{
		/* ... */
	}


	public function preValidate(): void
	{
		/* ... */
	}


	public function getTypeLabel(): string
	{
		/* ... */
	}


	public function getTypeDescription(): string
	{
		/* ... */
	}


	public function renderDocumentation(UI $ui): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Parameters/Rules/Type/RequiredIfOtherValueEquals.php`

```php
namespace Application\API\Parameters\Rules\Type;

use Application\API\Parameters\APIParameterInterface as APIParameterInterface;
use Application\API\Parameters\Rules\BaseRule as BaseRule;
use UI as UI;

/**
 * Validation rule: Make a parameter required if another parameter equals a specific value
 * (strict typed comparison).
 *
 * @package API
 * @subpackage Parameters
 */
class RequiredIfOtherValueEquals extends BaseRule
{
	public const RULE_ID = 'REQUIRED_IF_OTHER_VALUE_EQUALS';

	public function getID(): string
	{
		/* ... */
	}


	public function getParams(): array
	{
		/* ... */
	}


	protected function _validate(): void
	{
		/* ... */
	}


	public function preValidate(): void
	{
		/* ... */
	}


	public function getTypeLabel(): string
	{
		/* ... */
	}


	public function getTypeDescription(): string
	{
		/* ... */
	}


	public function renderDocumentation(UI $ui): string
	{
		/* ... */
	}
}


```