<?php

declare(strict_types=1);

namespace Application\API\Parameters;

use Application\API\Parameters\Flavors\APIHeaderParameterInterface;
use Application\API\Parameters\Validation\ParamValidationInterface;
use Application\API\Parameters\Validation\Type\CallbackValidation;
use Application\API\Parameters\Validation\Type\EnumValidation;
use Application\API\Parameters\Validation\Type\RequiredValidation;
use Application\API\Parameters\Validation\Type\ValueExistsCallbackValidation;
use Application\API\Parameters\ValueLookup\SelectableValueParamInterface;
use Application\AppFactory;
use Application\Validation\ValidationLoggableTrait;
use Application\Validation\ValidationResults;
use Application_Request;
use Application_Traits_Loggable;
use AppUtils\Interfaces\StringableInterface;
use AppUtils\OperationResult;
use AppUtils\Request\RequestParam;

abstract class BaseAPIParameter implements APIParameterInterface
{
    use Application_Traits_Loggable;
    use ValidationLoggableTrait;

    protected ?RequestParam $param = null;
    protected string $label;
    protected bool $required = false;
    protected string $description = '';
    private string $name;
    private static ?Application_Request $request = null;
    protected ValidationResults $result;
    private bool $invalidated = false;
    private string $validatorLabel;

    public function __construct(string $name, string $label)
    {
        $this->name = $name;
        $this->label = $label;
        $this->validatorLabel = sprintf('API Parameter [%s]', $this->name);
        $this->result = new ValidationResults($this);

        $this->_init();
    }

    protected function _init() : void
    {

    }

    public function getLogIdentifier(): string
    {
        return $this->validatorLabel;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function makeRequired(bool $required=true) : self
    {
        $this->required = $required;
        return $this;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getRequestParam(): RequestParam
    {
        if(isset($this->param)) {
            return $this->param;
        }

        $this->param = $this
            ->getRequest()
            ->registerParam($this->getName());

        return $this->param;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function hasDescription(): bool
    {
        return !empty($this->description);
    }

    /**
     * @param string|StringableInterface $description
     * @param mixed ...$args Optional parameters for `sprintf`.
     * @return $this
     */
    public function setDescription(string|StringableInterface $description, ...$args): self
    {
        $this->description = sprintf(toString($description), ...$args);
        return $this;
    }

    protected function getRequest() : Application_Request
    {
        if(!isset(self::$request)) {
            self::$request = AppFactory::createRequest();
        }

        return self::$request;
    }

    /**
     * @var int|float|bool|string|array<int|string,mixed>|null
     */
    private int|float|bool|string|array|null $value = null;

    /**
     * @param (callable(int|float|bool|string|array<int|string,mixed>, OperationResult, APIParameterInterface, mixed...) : void) $callback
     * @param mixed ...$args
     * @return $this
     */
    public function validateByCallback(callable $callback, ...$args) : self
    {
        return $this->validateBy(new CallbackValidation($callback));
    }

    /**
     * @param (callable(int|float|bool|string|array<int|string,mixed>|null) : bool) $callback
     * @return $this
     */
    public function validateByValueExistsCallback(callable $callback) : self
    {
        return $this->validateBy(new ValueExistsCallbackValidation($callback));
    }

    /**
     * @param array<int|string,int|float|string|bool> $values
     * @return $this
     */
    public function validateByEnum(array $values) : self
    {
        return $this->validateBy(new EnumValidation(array_values($values)));
    }

    /**
     * @var int|float|bool|string|array<int|string,mixed>|null
     */
    private int|float|bool|string|array|null $selectedValue = null;

    public function selectValue(int|float|bool|string|array|null $value) : self
    {
        $this->requireValidSelectableValue($value);

        $this->selectedValue = $value;
        return $this;
    }

    /**
     * @var int|float|bool|string|array<int|string,mixed>|null
     */
    protected int|float|bool|string|array|null $defaultValue = null;

    /**
     * @param int|float|bool|string|array<int|string,mixed>|null $default
     * @return $this
     */
    public function setDefaultValue(int|float|bool|string|array|null $default) : self
    {
        $this->requireValidSelectableValue($default);

        $this->defaultValue = $default;
        return $this;
    }

    /**
     * @param int|float|bool|string|array<int|string,mixed>|null $value
     * @return void
     * @throws APIParameterException
     */
    private function requireValidSelectableValue(int|float|bool|string|array|null $value) : void
    {
        if($value === null) {
            return;
        }

        if($this instanceof SelectableValueParamInterface && !$this->selectableValueExists($value)) {
            throw new APIParameterException(
                'Invalid selected value.',
                sprintf(
                    'The given value is not among the selectable options for parameter [%s].',
                    $this->getName()
                ),
                APIParameterException::ERROR_INVALID_PARAM_VALUE
            );
        }
    }

    public function hasValue() : bool
    {
        return $this->getValue() !== null;
    }

    private bool $valueResolved = false;

    public function getValue() : int|float|bool|string|array|null
    {
        if($this->isInvalidated()) {
            return null;
        }

        if($this->selectedValue !== null) {
            return $this->selectedValue;
        }

        if($this->valueResolved) {
            return $this->value;
        }

        $this->valueResolved = true;

        if($this instanceof APIHeaderParameterInterface) {
            $value = $this->getHeaderValue();
        } else {
            $value = $this->resolveValue();
        }

        if($this->validate($value)) {
            $this->value = $value;
        } else {
            $this->value = $this->getDefaultValue();
        }

        return $this->value;
    }

    /**
     * @param int|float|bool|string|array<int|string,mixed>|null $value
     * @return bool
     */
    private function validate(int|float|bool|string|array|null $value) : bool
    {
        // The result may already contain errors from value resolution.
        if(!$this->result->isValid()) {
            return false;
        }

        // If the parameter is required, prepend the required validation.
        if($this->isRequired()) {
            array_unshift($this->validations, new RequiredValidation());
        }

        // Run through all validations
        foreach($this->validations as $validation)
        {
            $validation->validate($value, $this->result, $this);

            if(!$this->result->isValid()) {
                // Stop processing on first error
                return false;
            }
        }

        return true;
    }

    public function getValidationResults(): ValidationResults
    {
        // Ensure value is resolved and validations are run
        $this->getValue();

        return $this->result;
    }

    public function isValid() : bool
    {
        return $this->getValidationResults()->isValid();
    }

    /**
     * @var array<int, ParamValidationInterface>
     */
    private array $validations = array();

    public function validateBy(ParamValidationInterface $validation) : self
    {
        $this->validations[] = $validation;
        return $this;
    }

    /**
     * @return int|float|bool|string|array<int|string,mixed>|null
     */
    abstract protected function resolveValue(): int|float|bool|string|array|null;

    public function invalidate() : self
    {
        $this->invalidated = true;
        return $this;
    }

    public function isInvalidated() : bool
    {
        return $this->invalidated;
    }
}
