# API Parameters - Flavors, Handlers, Reserved, Value Lookup (Public API)
_SOURCE: APIHeaderParameterInterface/Trait, RequiredOnlyParamInterface/Trait, ParamHandlerInterface, BaseParamHandler, BaseRuleHandler, RuleHandlerInterface, APIHandlerInterface, BaseAPIHandler, ParamsHandlerContainerInterface, BaseParamsHandlerContainer, APIMethodParameter, APIVersionParameter, SelectableValueParamInterface/Trait, SelectableParamValue_
# APIHeaderParameterInterface/Trait, RequiredOnlyParamInterface/Trait, ParamHandlerInterface, BaseParamHandler, BaseRuleHandler, RuleHandlerInterface, APIHandlerInterface, BaseAPIHandler, ParamsHandlerContainerInterface, BaseParamsHandlerContainer, APIMethodParameter, APIVersionParameter, SelectableValueParamInterface/Trait, SelectableParamValue
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── API/
                └── Parameters/
                    └── Flavors/
                        ├── APIHeaderParameterInterface.php
                        ├── APIHeaderParameterTrait.php
                        ├── RequiredOnlyParamInterface.php
                        ├── RequiredOnlyParamTrait.php
                    └── Handlers/
                        ├── APIHandlerInterface.php
                        ├── BaseAPIHandler.php
                        ├── BaseParamHandler.php
                        ├── BaseParamsHandlerContainer.php
                        ├── BaseRuleHandler.php
                        ├── ParamHandlerInterface.php
                        ├── ParamsHandlerContainerInterface.php
                        ├── RuleHandlerInterface.php
                    └── Reserved/
                        ├── APIMethodParameter.php
                        ├── APIVersionParameter.php
                    └── ValueLookup/
                        └── SelectableParamValue.php
                        └── SelectableValueParamInterface.php
                        └── SelectableValueParamTrait.php

```
###  Path: `/src/classes/Application/API/Parameters/Flavors/APIHeaderParameterInterface.php`

```php
namespace Application\API\Parameters\Flavors;

use Application\API\Parameters\APIParameterInterface as APIParameterInterface;
use Connectors\Headers\HTTPHeadersBasket as HTTPHeadersBasket;

/**
 * Interface for API parameters that are passed via HTTP headers,
 * instead of query parameters or request body.
 *
 * Implementing this interface automatically documents the parameter
 * as a header parameter in the API documentation, and its value will
 * be automatically retrieved using {@see self::getHeaderValue()} instead
 * of {@see BaseAPIParameter::resolveValue()}.
 *
 * The parameter is responsible for the concrete implementation of
 * how to get the header value.
 *
 * @package API
 * @subpackage Parameters
 */
interface APIHeaderParameterInterface extends APIParameterInterface
{
	/**
	 * The name of the HTTP header used to pass this parameter.
	 * Used for documentation purposes only.
	 * @return string
	 */
	public function getHeaderExample(): string;


	/**
	 * Gets the value of the header, if it is present in the request.
	 * @return string|NULL
	 */
	public function getHeaderValue(): ?string;


	/**
	 * Sets the header to the specified value for testing and documentation.
	 * @param HTTPHeadersBasket $headers
	 * @param string $value
	 * @return self
	 */
	public function injectHeaderForValue(HTTPHeadersBasket $headers, string $value): self;
}


```
###  Path: `/src/classes/Application/API/Parameters/Flavors/APIHeaderParameterTrait.php`

```php
namespace Application\API\Parameters\Flavors;

/**
 * Trait used to help implementing API parameters that are
 * passed via HTTP headers.
 *
 * See the interface {@see APIHeaderParameterInterface} for more details.
 *
 * @package API
 * @subpackage Parameters
 *
 * @see APIHeaderParameterInterface
 */
trait APIHeaderParameterTrait
{
	protected function resolveValue(): ?string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Parameters/Flavors/RequiredOnlyParamInterface.php`

```php
namespace Application\API\Parameters\Flavors;

use Application\API\Parameters\APIParameterInterface as APIParameterInterface;

/**
 * Interface for API parameters that are always required
 * and cannot be made optional.
 *
 * In the documentation, this parameter will be marked
 * as required, even if parameters are, on principle,
 * never marked as required.
 *
 * @package API
 * @subpackage Parameters
 */
interface RequiredOnlyParamInterface extends APIParameterInterface
{
}


```
###  Path: `/src/classes/Application/API/Parameters/Flavors/RequiredOnlyParamTrait.php`

```php
namespace Application\API\Parameters\Flavors;

use Application\API\Clients\Keys\APIKeyException as APIKeyException;

/**
 * Trait used to implement parameters that are always required
 * and cannot be made optional.
 *
 * @package API
 * @subpackage Parameters
 */
trait RequiredOnlyParamTrait
{
	/**
	 * @param bool $required
	 * @return $this
	 * @throws APIKeyException
	 */
	public function makeRequired(bool $required = true): self
	{
		/* ... */
	}


	public function isRequired(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Parameters/Handlers/APIHandlerInterface.php`

```php
namespace Application\API\Parameters\Handlers;

use Application\API\APIMethodInterface as APIMethodInterface;
use Application\API\Parameters\APIParameterException as APIParameterException;
use Application\API\Parameters\APIParameterInterface as APIParameterInterface;

interface APIHandlerInterface
{
	public function getMethod(): APIMethodInterface;


	/**
	 * Selects a value directly for this parameter or rule, bypassing normal resolution.
	 *
	 * > NOTE: This should be the final value type returned by the parameter or rule.
	 * > For example: If the parameter is an integer ID, this should select
	 * > the record object.
	 *
	 * @param mixed $value
	 * @return $this
	 */
	public function selectValue(mixed $value): self;


	/**
	 * Resolves and returns the final, resolved value for this parameter or rule.
	 *
	 * > NOTE: This will return the final value type expected from this parameter or rule.
	 * > For example: If the parameter is an integer ID, this should return
	 * > the record object.
	 *
	 * @return mixed|NULL The resolved value, or NULL if not set/available.
	 */
	public function resolveValue(): mixed;


	/**
	 * Like {@see self::resolveValue()} but with a guaranteed non-null return value.
	 * If no value can be resolved, an error response will be sent and execution halted.
	 *
	 * @return string|int|float|bool|array|object
	 * @see APIMethodInterface::ERROR_NO_VALUE_AVAILABLE
	 */
	public function requireValue(): string|int|float|bool|array|object;


	/**
	 * Returns the list of parameters managed by this handler.
	 * @return APIParameterInterface[]
	 */
	public function getParams(): array;


	/**
	 * @return string[]
	 */
	public function getParamNames(): array;
}


```
###  Path: `/src/classes/Application/API/Parameters/Handlers/BaseAPIHandler.php`

```php
namespace Application\API\Parameters\Handlers;

use Application\API\APIMethodInterface as APIMethodInterface;
use Application\API\Parameters\APIParamManager as APIParamManager;
use Application\API\Parameters\APIParameterException as APIParameterException;

abstract class BaseAPIHandler implements APIHandlerInterface
{
	public function getMethod(): APIMethodInterface
	{
		/* ... */
	}


	public function selectValue(mixed $value): self
	{
		/* ... */
	}


	public function resolveValue(): mixed
	{
		/* ... */
	}


	/**
	 * Requires that the handler resolves a value.
	 *
	 * Returns the resolved value when one is available. When no value is
	 * available, `->send()` is called on the error response, which
	 * **terminates PHP request execution** — no code after `requireValue()`
	 * runs in that case.
	 *
	 * The return type is declared as `string|int|float|bool|array|object`
	 * rather than `never` because PHP does not permit `never` on a method that
	 * subclasses may override with a non-`never` return type.
	 *
	 * @return string|int|float|bool|array|object
	 */
	public function requireValue(): string|int|float|bool|array|object
	{
		/* ... */
	}


	/**
	 * @return string[]
	 */
	public function getParamNames(): array
	{
		/* ... */
	}


	/**
	 * This is called when no value has been selected directly.
	 * The value must be resolved from the parameter itself.
	 *
	 * **Null-return contract:** Implementations MUST return `null`
	 * when the handler has no value to contribute (parameter absent,
	 * value empty, or rule not registered). {@see BaseParamsHandlerContainer::resolveValue()}
	 * iterates all registered handlers and uses "first non-null wins"
	 * semantics — returning a non-null value (including an empty array)
	 * will be treated as a successful resolution and prevent subsequent
	 * handlers from being consulted.
	 *
	 * @return mixed The resolved value, or `null` if this handler has no value.
	 */
	abstract protected function resolveValueFromSubject(): mixed;
}


```
###  Path: `/src/classes/Application/API/Parameters/Handlers/BaseParamHandler.php`

```php
namespace Application\API\Parameters\Handlers;

use Application\API\Parameters\APIParameterException as APIParameterException;
use Application\API\Parameters\APIParameterInterface as APIParameterInterface;

/**
 * Abstract base class used to implement API parameter handlers.
 *
 * See the interface {@see ParamHandlerInterface} for more details.
 *
 * @package API
 * @subpackage Parameters
 */
abstract class BaseParamHandler extends BaseAPIHandler implements ParamHandlerInterface
{
	public function register(): APIParameterInterface
	{
		/* ... */
	}


	/**
	 * Create an instance of the parameter this handler manages.
	 * @return APIParameterInterface
	 */
	abstract protected function createParam(): APIParameterInterface;


	public function getParam(): ?APIParameterInterface
	{
		/* ... */
	}


	public function requireParam(): APIParameterInterface
	{
		/* ... */
	}


	public function getParams(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Parameters/Handlers/BaseParamsHandlerContainer.php`

```php
namespace Application\API\Parameters\Handlers;

use Application\API\APIMethodInterface as APIMethodInterface;
use Application\API\Parameters\APIParamManager as APIParamManager;

abstract class BaseParamsHandlerContainer implements ParamsHandlerContainerInterface
{
	public function getMethod(): APIMethodInterface
	{
		/* ... */
	}


	public function getManager(): APIParamManager
	{
		/* ... */
	}


	protected function registerHandler(APIHandlerInterface $handler): void
	{
		/* ... */
	}


	public function resolveValue(): mixed
	{
		/* ... */
	}


	public function getAll(): array
	{
		/* ... */
	}


	/**
	 * @return class-string<APIHandlerInterface>[]
	 */
	public function getIDs(): array
	{
		/* ... */
	}


	/**
	 * @return string[]
	 */
	public function getParamNames(): array
	{
		/* ... */
	}


	abstract protected function isValidValueType(string|int|float|bool|array|object $value): bool;


	/**
	 * Requires that at least one handler resolves a valid value.
	 *
	 * When a value is resolved and passes {@see isValidValueType()}, it is
	 * returned directly. When no value is available, `->send()` is called on
	 * the error response, which **terminates PHP request execution** — no code
	 * after `requireValue()` runs in that case.
	 *
	 * Subclasses override this method to narrow the return type (e.g. to
	 * `Application_Countries_Country` or `Application_Countries_Country[]`).
	 * The subclass body calls `parent::requireValue()` and then applies a
	 * type-narrowing guard. Because the parent return type is declared as
	 * `string|int|float|bool|array|object` (not `never`) — PHP does not permit
	 * `never` on a method that subclasses override with a narrower return —
	 * the guard and its fallback branch are required by PHP's type system even
	 * though they can never be reached at runtime.
	 *
	 * @return string|int|float|bool|array|object
	 */
	public function requireValue(): string|int|float|bool|array|object
	{
		/* ... */
	}


	/**
	 * Selects the given value in all handlers that support value selection.
	 *
	 * > NOTE: This should be the final value type returned by the parameter or rule.
	 *  > For example: If the parameter is an integer ID, this should select
	 *  > the record object.
	 *
	 * @param string|int|float|bool|array<int|string,mixed>|object $value
	 * @return $this
	 */
	public function selectValue(string|int|float|bool|array|object $value): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Parameters/Handlers/BaseRuleHandler.php`

```php
namespace Application\API\Parameters\Handlers;

use Application\API\Parameters\Rules\RuleInterface as RuleInterface;

abstract class BaseRuleHandler extends BaseAPIHandler implements RuleHandlerInterface
{
	public function register(): RuleInterface
	{
		/* ... */
	}


	abstract protected function createRule(): RuleInterface;


	public function getRule(): ?RuleInterface
	{
		/* ... */
	}


	public function getParams(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Parameters/Handlers/ParamHandlerInterface.php`

```php
namespace Application\API\Parameters\Handlers;

use Application\API\Clients\API\Params\APIKeyHandler as APIKeyHandler;
use Application\API\Parameters\APIParameterInterface as APIParameterInterface;

/**
 * Interface for parameter handler classes that manage API parameters:
 * To handle the complex scenarios of parameter registration, value selection,
 * and resolution, parameter handlers provide a consistent interface to manage
 * these tasks.
 *
 * Instead of directly manipulating parameters and implementing intricate
 * logic within the API methods directly, these handlers encapsulate the
 * necessary functionality on a per-parameter basis.
 *
 * ## Usage
 *
 * See the abstract class {@see BaseParamHandler} for a base implementation
 * that can be extended to create specific parameter handlers. As an example,
 * look at {@see APIKeyHandler} on best practices for implementing a parameter
 * handler.
 *
 * The value handling methods are intentionally generic to accommodate a wide
 * variety of parameter types and resolution strategies. Ideally, your handler
 * class should guarantee and document the expected types for selected and
 * resolved values.
 *
 * @package API
 * @subpackage Parameters
 */
interface ParamHandlerInterface extends APIHandlerInterface
{
	/**
	 * Registers the parameter with the API method's parameters collection.
	 * @return APIParameterInterface
	 */
	public function register(): APIParameterInterface;


	/**
	 * Gets the parameter instance managed by this handler.
	 *
	 * > NOTE: The parameter is only returned if it has been registered.
	 *
	 * @return APIParameterInterface|null
	 */
	public function getParam(): ?APIParameterInterface;
}


```
###  Path: `/src/classes/Application/API/Parameters/Handlers/ParamsHandlerContainerInterface.php`

```php
namespace Application\API\Parameters\Handlers;

use Application\API\APIMethodInterface as APIMethodInterface;
use Application\API\Parameters\APIParamManager as APIParamManager;

interface ParamsHandlerContainerInterface
{
	public function getMethod(): APIMethodInterface;


	public function getManager(): APIParamManager;


	/**
	 * Resolves the value by checking each registered handler in order.
	 * The first non-null value found is returned.
	 *
	 * @return mixed
	 */
	public function resolveValue(): mixed;


	/**
	 * Like {@see self::resolveValue()}, but guarantees a non-null return value.
	 * If no value can be resolved, en error response is sent.
	 *
	 * @return string|int|float|bool|array<int|string,mixed>|object
	 * @see APIMethodInterface::ERROR_NO_VALUE_AVAILABLE
	 */
	public function requireValue(): string|int|float|bool|array|object;


	/**
	 * Selects the given value in all handlers that support value selection.
	 * @param string|int|float|bool|array|object $value
	 * @return $this
	 */
	public function selectValue(string|int|float|bool|array|object $value): self;
}


```
###  Path: `/src/classes/Application/API/Parameters/Handlers/RuleHandlerInterface.php`

```php
namespace Application\API\Parameters\Handlers;

use Application\API\Parameters\Rules\RuleInterface as RuleInterface;

interface RuleHandlerInterface extends APIHandlerInterface
{
	public function register(): RuleInterface;


	public function getRule(): ?RuleInterface;
}


```
###  Path: `/src/classes/Application/API/Parameters/Reserved/APIMethodParameter.php`

```php
namespace Application\API\Parameters\Reserved;

use Application\API\APIManager as APIManager;
use Application\API\APIMethodInterface as APIMethodInterface;
use Application\API\Parameters\ReservedParamInterface as ReservedParamInterface;
use Application\API\Parameters\Type\StringParameter as StringParameter;

class APIMethodParameter extends StringParameter implements ReservedParamInterface
{
	public function isEditable(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Parameters/Reserved/APIVersionParameter.php`

```php
namespace Application\API\Parameters\Reserved;

use Application\API\APIMethodInterface as APIMethodInterface;
use Application\API\Parameters\ReservedParamInterface as ReservedParamInterface;
use Application\API\Parameters\Type\StringParameter as StringParameter;
use Application\API\Parameters\ValueLookup\SelectableParamValue as SelectableParamValue;
use Application\API\Parameters\ValueLookup\SelectableValueParamInterface as SelectableValueParamInterface;
use Application\API\Parameters\ValueLookup\SelectableValueParamTrait as SelectableValueParamTrait;

class APIVersionParameter extends StringParameter implements ReservedParamInterface, SelectableValueParamInterface
{
	use SelectableValueParamTrait;

	public function isEditable(): bool
	{
		/* ... */
	}


	protected function _getValues(): array
	{
		/* ... */
	}


	public function getDefaultSelectableValue(): ?SelectableParamValue
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Parameters/ValueLookup/SelectableParamValue.php`

```php
namespace Application\API\Parameters\ValueLookup;

class SelectableParamValue
{
	public string $value;
	public string $label;


	public function getLabel(): string
	{
		/* ... */
	}


	public function getValue(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Parameters/ValueLookup/SelectableValueParamInterface.php`

```php
namespace Application\API\Parameters\ValueLookup;

use Application\API\Parameters\APIParameterInterface as APIParameterInterface;

interface SelectableValueParamInterface extends APIParameterInterface
{
	/**
	 * @return SelectableParamValue[]
	 */
	public function getSelectableValues(): array;


	public function getDefaultSelectableValue(): ?SelectableParamValue;


	/**
	 * @return string[]
	 */
	public function getSelectableValueOptions(): array;


	/**
	 * Checks whether the given value exists in the selectable values.
	 *
	 * @param mixed $value Numeric and boolean values will be converted to string for comparison.
	 * @return bool
	 */
	public function selectableValueExists(mixed $value): bool;
}


```
###  Path: `/src/classes/Application/API/Parameters/ValueLookup/SelectableValueParamTrait.php`

```php
namespace Application\API\Parameters\ValueLookup;

use AppUtils\ConvertHelper as ConvertHelper;

trait SelectableValueParamTrait
{
	/**
	 * @return SelectableParamValue[]
	 */
	public function getSelectableValues(): array
	{
		/* ... */
	}


	/**
	 * @return string[]
	 */
	public function getSelectableValueOptions(): array
	{
		/* ... */
	}


	public function selectableValueExists(mixed $value): bool
	{
		/* ... */
	}


	/**
	 * @return SelectableParamValue[]
	 */
	abstract protected function _getValues(): array;
}


```
---
**File Statistics**
- **Size**: 19.72 KB
- **Lines**: 776
File: `modules/api/parameters/architecture-supporting.md`
