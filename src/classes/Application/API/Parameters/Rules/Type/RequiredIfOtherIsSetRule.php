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

/**
 * Validation rule: Make a parameter required if another parameter is set (not null).
 *
 * @package API
 * @subpackage Parameters
 */
class RequiredIfOtherIsSetRule extends BaseRule
{
    public const string RULE_ID = 'REQUIRED_IF_OTHER_IS_SET';

    private APIParameterInterface $target;
    private APIParameterInterface $other;

    public function __construct(APIParameterInterface $target, APIParameterInterface $other)
    {
        $this->target = $target;
        $this->other = $other;

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

        if($this->other->getValue() !== null) {
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
        return t('If other is set');
    }

    public function getTypeDescription(): string
    {
        return t('A parameter will be required if another parameter is set (not empty).');
    }

    public function renderDocumentation(UI $ui): string
    {
        return t(
            'If %1$s is set, %2$s is required.',
            sb()->mono($this->other->getName()),
            sb()->mono($this->target->getName())
        );
    }
}
