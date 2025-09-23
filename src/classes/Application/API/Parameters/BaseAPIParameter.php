<?php

declare(strict_types=1);

namespace Application\API\Parameters;

use Application\API\Parameters\Validation\ParamValidationInterface;
use Application\API\Parameters\Validation\Type\CallbackValidation;
use Application\API\Parameters\Validation\Type\EnumValidation;
use Application\API\Parameters\Validation\Type\RequiredValidation;
use Application\AppFactory;
use Application_Request;
use AppUtils\Interfaces\StringableInterface;
use AppUtils\OperationResult;
use AppUtils\Request\RequestParam;

abstract class BaseAPIParameter implements APIParameterInterface
{
    protected ?RequestParam $param = null;
    protected string $label;
    protected bool $required = false;
    protected string $description = '';
    private string $name;
    private static ?Application_Request $request = null;
    protected OperationResult $result;

    public function __construct(string $name, string $label)
    {
        $this->name = $name;
        $this->label = $label;
        $this->result = new OperationResult($this);
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

    private int|float|bool|string|array|null $value = null;

    public function addValidationCallback(callable $callback, ...$args) : self
    {
        return $this->addValidation(new CallbackValidation($callback));
    }

    /**
     * @param array<int|string,int|float|string|bool> $values
     * @return $this
     */
    public function addValidationEnum(array $values) : self
    {
        return $this->addValidation(new EnumValidation(array_values($values)));
    }

    public function getValue() : int|float|bool|string|array|null
    {
        if(isset($this->value)) {
            return $this->value;
        }

        $value = $this->resolveValue();

        if($this->validate($value)) {
            $this->value = $value;
        } else {
            $this->value = $this->getDefaultValue();
        }

        return $this->value;
    }

    private function validate(int|float|bool|string|array $value) : bool
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
            $validation->validate($value, $this->result);

            if(!$this->result->isValid()) {
                // Stop processing on first error
                return false;
            }
        }

        return true;
    }

    public function getValidationResult(): OperationResult
    {
        // Ensure value is resolved and validations are run
        $this->getValue();

        return $this->result;
    }

    public function isValid() : bool
    {
        return $this->getValidationResult()->isValid();
    }

    /**
     * @var array<int, ParamValidationInterface>
     */
    private array $validations = array();

    public function addValidation(ParamValidationInterface $validation) : self
    {
        $this->validations[] = $validation;
        return $this;
    }

    abstract protected function resolveValue(): int|float|bool|string|array|null;
}
