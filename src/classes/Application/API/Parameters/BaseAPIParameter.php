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
use Application\API\Parameters\ValueSource\ParamValueSourceInterface;
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

    private ?ParamValueSourceInterface $valueSource = null;

    /**
     * Injects the raw-value backing store this parameter resolves itself
     * against (see {@see \Application\API\Parameters\APIParamManager::registerParam()}).
     * Not called for header or reserved parameters, which keep their
     * existing resolution paths.
     *
     * @param ParamValueSourceInterface $source
     * @return $this
     */
    public function setValueSource(ParamValueSourceInterface $source) : self
    {
        $this->valueSource = $source;
        return $this;
    }

    /**
     * Retrieves the parameter's raw, unvalidated value, used by the
     * concrete type classes in {@see resolveValue()}. Delegates to
     * {@see self::getRawRequestValueUnprocessed()} when a {@see ParamValueSourceInterface}
     * has been injected (the normal case for a parameter registered through
     * {@see \Application\API\Parameters\APIParamManager}), so both methods
     * read from the same source and agree on the result in that case. The
     * only remaining difference between the two methods is their no-source
     * fallback: this one falls back to {@see self::getRequestParam()} (which
     * discards empty strings) when a parameter is instantiated standalone,
     * as in unit tests.
     *
     * @return mixed
     */
    protected function getRawRequestValue() : mixed
    {
        if(isset($this->valueSource)) {
            return $this->getRawRequestValueUnprocessed();
        }

        return $this->getRequestParam()->get();
    }

    /**
     * Whether a value is present for this parameter in its backing store,
     * as opposed to the key being entirely absent. Used by
     * {@see \Application\API\Parameters\Type\ClearableStringParameter}'s
     * three-state (absent / empty / value) resolution, which needs to tell
     * "not sent" from "sent as an empty string" — a distinction
     * {@see self::getRawRequestValue()} alone cannot make, since it returns
     * `null` for both.
     *
     * Uses the injected {@see ParamValueSourceInterface} when one has been
     * set (the normal case for a parameter registered through
     * {@see \Application\API\Parameters\APIParamManager}), so a JSON-body
     * method's clearable parameters resolve from the body exactly like every
     * other parameter type (see {@see \Application\API\Parameters\ValueSource\JSONBodyValueSource}).
     * Falls back to `$_REQUEST` directly otherwise — e.g. when a parameter is
     * instantiated standalone, as in unit tests.
     *
     * @return bool
     */
    protected function hasRawRequestValue() : bool
    {
        if(isset($this->valueSource)) {
            return $this->valueSource->hasKey($this->getName());
        }

        return array_key_exists($this->getName(), $_REQUEST);
    }

    /**
     * Retrieves this parameter's raw value exactly as stored in its backing
     * store, with no intermediate processing. Used exclusively by
     * {@see \Application\API\Parameters\Type\ClearableStringParameter}, which
     * needs the untouched value to preserve its absent/empty/value
     * distinction. {@see self::getRawRequestValue()} delegates to this method
     * when a value source is injected, so the two agree in that case; the
     * only remaining difference is the no-source fallback — this method
     * falls back to `$_REQUEST` directly (preserving empty strings), while
     * {@see self::getRawRequestValue()} falls back to
     * {@see self::getRequestParam()} (which discards them), for a parameter
     * instantiated standalone, as in unit tests.
     *
     * Only meaningful together with a preceding {@see self::hasRawRequestValue()}
     * check.
     *
     * @return mixed
     */
    protected function getRawRequestValueUnprocessed() : mixed
    {
        if(isset($this->valueSource)) {
            return $this->valueSource->getRawValue($this->getName());
        }

        return $_REQUEST[$this->getName()] ?? null;
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
     * @param mixed ...$args Forwarded to `$callback` after the value, result and param arguments.
     * @return $this
     */
    public function validateByCallback(callable $callback, ...$args) : self
    {
        return $this->validateBy(new CallbackValidation($callback, ...$args));
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

    /**
     * Whether the parameter has a resolved value.
     *
     * Returns `true` when `getValue()` is not `null`, and `false` when the
     * parameter is absent or invalid. Note that an **empty string** (`''`) is
     * a valid non-null value — `hasValue()` returns `true` for it.
     *
     * This distinction is the core contract of {@see ClearableStringParameter}:
     * callers must use `hasValue()` to detect whether the parameter was submitted
     * at all, and `getValue() === ''` to detect an explicit clear intent. Treating
     * an empty-string result as "no value" would incorrectly suppress the clear
     * operation.
     *
     * @return bool
     */
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
     * Determines whether the resolved value should be passed through the
     * validation pipeline. Subclasses may override this to skip format
     * validators for specific sentinel values.
     *
     * The base implementation returns `true` for all values, preserving
     * existing behavior.
     *
     * @param int|float|bool|string|array<int|string,mixed>|null $value The resolved value from {@see resolveValue()}.
     * @return bool `true` to run validators, `false` to skip them.
     */
    protected function isValueValidatable(int|float|bool|string|array|null $value) : bool
    {
        return true;
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

        // Allow subclasses to skip validation for specific sentinel values.
        if(!$this->isValueValidatable($value)) {
            return true;
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
