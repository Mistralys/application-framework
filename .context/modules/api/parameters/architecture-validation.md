# API Parameters - Validation (Public API)
_SOURCE: ParamValidationInterface, BaseParamValidation, RequiredValidation, EnumValidation, RegexValidation, CallbackValidation, ValueExistsCallbackValidation, ParamValidationResults_
# ParamValidationInterface, BaseParamValidation, RequiredValidation, EnumValidation, RegexValidation, CallbackValidation, ValueExistsCallbackValidation, ParamValidationResults
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── API/
                └── Parameters/
                    └── Validation/
                        └── BaseParamValidation.php
                        └── ParamValidationInterface.php
                        └── ParamValidationResults.php
                        └── Type/
                            └── CallbackValidation.php
                            └── EnumValidation.php
                            └── RegexValidation.php
                            └── RequiredValidation.php
                            └── ValueExistsCallbackValidation.php

```
###  Path: `/src/classes/Application/API/Parameters/Validation/BaseParamValidation.php`

```php
namespace Application\API\Parameters\Validation;

abstract class BaseParamValidation implements ParamValidationInterface
{
}


```
###  Path: `/src/classes/Application/API/Parameters/Validation/ParamValidationInterface.php`

```php
namespace Application\API\Parameters\Validation;

use AppUtils\OperationResult as OperationResult;
use Application\API\Parameters\APIParameterInterface as APIParameterInterface;

interface ParamValidationInterface
{
	public const VALIDATION_NON_NUMERIC_ID = 183501;
	public const VALIDATION_INVALID_VALUE_TYPE = 183502;
	public const VALIDATION_INVALID_JSON_DATA = 183503;
	public const VALIDATION_INVALID_FORMAT_BY_REGEX = 183504;
	public const VALIDATION_WARNING_FLOAT_TO_INT = 183505;
	public const VALIDATION_EMPTY_REQUIRED_PARAM = 183506;

	public function validate(
		int|float|bool|string|array|null $value,
		OperationResult $result,
		APIParameterInterface $param,
	): void;
}


```
###  Path: `/src/classes/Application/API/Parameters/Validation/ParamValidationResults.php`

```php
namespace Application\API\Parameters\Validation;

use Application\Validation\ValidationResults as ValidationResults;

class ParamValidationResults extends ValidationResults
{
	public function serializeErrors(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Parameters/Validation/Type/CallbackValidation.php`

```php
namespace Application\API\Parameters\Validation\Type;

use AppUtils\OperationResult as OperationResult;
use Application\API\Parameters\APIParameterInterface as APIParameterInterface;
use Application\API\Parameters\Validation\BaseParamValidation as BaseParamValidation;

class CallbackValidation extends BaseParamValidation
{
	public function validate(
		float|int|bool|array|string|null $value,
		OperationResult $result,
		APIParameterInterface $param,
	): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Parameters/Validation/Type/EnumValidation.php`

```php
namespace Application\API\Parameters\Validation\Type;

use AppUtils\OperationResult as OperationResult;
use Application\API\Parameters\APIParameterException as APIParameterException;
use Application\API\Parameters\APIParameterInterface as APIParameterInterface;
use Application\API\Parameters\Validation\BaseParamValidation as BaseParamValidation;

class EnumValidation extends BaseParamValidation
{
	public const VALIDATION_INVALID_VALUE = 183201;

	public function validate(
		float|int|bool|array|string|null $value,
		OperationResult $result,
		APIParameterInterface $param,
	): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Parameters/Validation/Type/RegexValidation.php`

```php
namespace Application\API\Parameters\Validation\Type;

use AppUtils\OperationResult as OperationResult;
use Application\API\Parameters\APIParameterInterface as APIParameterInterface;
use Application\API\Parameters\Validation\BaseParamValidation as BaseParamValidation;
use Application\API\Parameters\Validation\ParamValidationInterface as ParamValidationInterface;

class RegexValidation extends BaseParamValidation
{
	public function validate(
		int|float|bool|string|array|null $value,
		OperationResult $result,
		APIParameterInterface $param,
	): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Parameters/Validation/Type/RequiredValidation.php`

```php
namespace Application\API\Parameters\Validation\Type;

use AppUtils\OperationResult as OperationResult;
use Application\API\Parameters\APIParameterInterface as APIParameterInterface;
use Application\API\Parameters\Validation\BaseParamValidation as BaseParamValidation;
use Application\API\Parameters\Validation\ParamValidationInterface as ParamValidationInterface;

class RequiredValidation extends BaseParamValidation
{
	public function validate(
		float|int|bool|array|string|null $value,
		OperationResult $result,
		APIParameterInterface $param,
	): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Parameters/Validation/Type/ValueExistsCallbackValidation.php`

```php
namespace Application\API\Parameters\Validation\Type;

use AppUtils\OperationResult as OperationResult;
use Application\API\Parameters\APIParameterInterface as APIParameterInterface;
use Application\API\Parameters\Validation\BaseParamValidation as BaseParamValidation;

/**
 * Validating a parameter value by using a callback function
 * to check if the value exists.
 *
 * @package API
 * @subpackage Parameters
 */
class ValueExistsCallbackValidation extends BaseParamValidation
{
	public const VALIDATION_VALUE_NOT_EXISTS = 183401;

	public function validate(
		float|int|bool|array|string|null $value,
		OperationResult $result,
		APIParameterInterface $param,
	): void
	{
		/* ... */
	}
}


```
---
**File Statistics**
- **Size**: 5.96 KB
- **Lines**: 211
File: `modules/api/parameters/architecture-validation.md`
