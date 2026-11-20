<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\Rules\Type;

use Application\API\Parameters\APIParameterInterface;
use Application\API\Parameters\Rules\BaseRule;
use Application\API\Parameters\Rules\RuleInterface;
use UI;
use function AppUtils\parseVariable;

/**
 * Validation rule: A target parameter is only accepted when another
 * parameter's value is one of a given set of allowed values.
 *
 * Unlike {@see RequiredIfOtherValueEquals}, which only ever makes the
 * target *required*, this rule *rejects* the target outright with
 * {@see RuleInterface::VALIDATION_PARAM_NOT_APPLICABLE} when the
 * condition is not met.
 *
 * ## Fallback value resolution
 *
 * In PATCH-style update methods, the "other" parameter is frequently
 * omitted from the request because the client is not changing it -
 * the condition value then has to be read from the stored record
 * instead. {@see self::setFallbackValueResolver()} covers this case:
 * it is only ever consulted when the other parameter has no value.
 *
 * @package API
 * @subpackage Parameters
 */
class OnlyIfOtherValueInRule extends BaseRule
{
    public const string RULE_ID = 'ONLY_IF_OTHER_VALUE_IN';

    private APIParameterInterface $target;
    private APIParameterInterface $other;

    /**
     * @var array<int,int|float|string|bool>
     */
    private array $allowedValues;

    /**
     * @var (callable():(int|float|string|bool|null))|null
     */
    private $fallbackValueResolver = null;

    /**
     * @param string $label
     * @param APIParameterInterface $target The parameter that is only accepted conditionally.
     * @param APIParameterInterface $other The parameter whose value decides whether the target is accepted.
     * @param array<int,int|float|string|bool> $allowedValues The values of `$other` for which `$target` is accepted.
     */
    public function __construct(string $label, APIParameterInterface $target, APIParameterInterface $other, array $allowedValues)
    {
        $this->target = $target;
        $this->other = $other;
        $this->allowedValues = $allowedValues;

        parent::__construct($label);
    }

    /**
     * Registers a callback used to resolve the condition value when the
     * "other" parameter has no value of its own (e.g. not sent in an
     * update request). Typically used to read the current value from
     * the stored record being updated.
     *
     * @param callable():(int|float|string|bool|null) $resolver
     * @return $this
     */
    public function setFallbackValueResolver(callable $resolver) : self
    {
        $this->fallbackValueResolver = $resolver;
        return $this;
    }

    public function getID(): string
    {
        return self::RULE_ID;
    }

    /**
     * @return array<int,int|float|string|bool>
     */
    public function getAllowedValues() : array
    {
        return $this->allowedValues;
    }

    public function getParams(): array
    {
        return array(
            $this->target,
            $this->other
        );
    }

    public function preValidate(): void
    {
        // Nothing to prepare: unlike RequiredIfOtherValueEquals/RequiredIfOtherIsSetRule,
        // this rule never changes the target's required state - it either
        // accepts or rejects whatever value was (or was not) submitted.
    }

    protected function _validate(): void
    {
        if($this->other->isInvalidated() || $this->target->isInvalidated()) {
            // One of the parameters has already been invalidated, nothing to do here.
            return;
        }

        if(!$this->target->hasValue()) {
            // The target was not sent at all, so there is nothing to reject.
            return;
        }

        $conditionValue = $this->resolveConditionValue();

        if(in_array($conditionValue, $this->allowedValues, true)) {
            return;
        }

        $this->result->makeError(
            sprintf(
                'The parameter [%1$s] is only accepted when [%2$s] is one of: %3$s.',
                $this->target->getName(),
                $this->other->getName(),
                implode(', ', array_map(static fn($value) => parseVariable($value)->enableType()->toString(), $this->allowedValues))
            ),
            RuleInterface::VALIDATION_PARAM_NOT_APPLICABLE
        );
    }

    /**
     * Resolves the value to compare against {@see self::$allowedValues}:
     * the "other" parameter's own value when it has one, otherwise the
     * fallback resolver's value (or `null` if none is registered).
     *
     * @return int|float|string|bool|null
     */
    private function resolveConditionValue() : int|float|string|bool|null
    {
        if($this->other->hasValue()) {
            /** @var int|float|string|bool $value */
            $value = $this->other->getValue();
            return $value;
        }

        if($this->fallbackValueResolver !== null) {
            return ($this->fallbackValueResolver)();
        }

        return null;
    }

    public function getTypeLabel(): string
    {
        return t('Only if other value in');
    }

    public function getTypeDescription(): string
    {
        return t('A parameter is only accepted when another parameter\'s value is one of a given set of values.');
    }

    public function renderDocumentation(UI $ui): string
    {
        $values = array();
        foreach($this->allowedValues as $value) {
            $values[] = sb()->mono(parseVariable($value)->enableType()->toString());
        }

        return t(
            'The parameter %1$s is only accepted when %2$s is set to one of: %3$s.',
            sb()->mono($this->target->getName()),
            sb()->mono($this->other->getName()),
            implode(', ', $values)
        );
    }
}
