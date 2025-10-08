<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\Rules\Type;

use Application\API\Parameters\APIParameterInterface;
use Application\API\Parameters\Rules\BaseRule;
use UI;
use function AppUtils\parseVariable;

/**
 * Validation rule: Make a parameter required if another parameter equals a specific value
 * (strict typed comparison).
 *
 * @package API
 * @subpackage Parameters
 */
class RequiredIfOtherValueEquals extends BaseRule
{
    public const string RULE_ID = 'REQUIRED_IF_OTHER_VALUE_EQUALS';

    private APIParameterInterface $target;
    private APIParameterInterface $other;
    private mixed $expectedValue;

    public function __construct(APIParameterInterface $target, APIParameterInterface $other, mixed $expectedValue)
    {
        $this->target = $target;
        $this->other = $other;
        $this->expectedValue = $expectedValue;

        parent::__construct();
    }

    public function getID(): string
    {
        return self::RULE_ID;
    }

    protected function _validate(): void
    {
        if($this->other->isInvalidated() || $this->target->isInvalidated()) {
            // One of the parameters has already been invalidated, nothing to do here.
            return;
        }

        if($this->other->getValue() === $this->expectedValue) {
            $this->target->makeRequired();
        }
    }

    public function preValidate(): void
    {
        // Initial state must be that the required parameter is not required,
        // only if the other parameter is set will it become required.
        $this->target->makeRequired(false);
    }

    public function getTypeLabel(): string
    {
        return t('If other equals');
    }

    public function getTypeDescription(): string
    {
        return t('A parameter will be required if another parameter equals a specific value (strict typed comparison).');
    }

    public function renderDocumentation(UI $ui): string
    {
        return t(
            'The parameter %1$s will be required if the parameter %2$s is set to "%3$s".',
            sb()->mono($this->target->getName()),
            sb()->mono($this->other->getName()),
            sb()->mono(parseVariable($this->expectedValue)->enableType()->toString())
        );
    }
}
