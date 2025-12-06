<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\Rules;

use Application\API\Parameters\APIParameterInterface;
use Application\Validation\ValidationLoggableInterface;
use Application\Validation\ValidationResults;
use AppUtils\Interfaces\StringableInterface;
use UI;

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
    public const int VALIDATION_NO_PARAM_SET_MATCHED = 183601;

    public function getID() : string;
    public function getLabel() : string;
    public function getDescription(): string;
    public function getTypeLabel() : string;
    public function getTypeDescription() : string;

    /**
     * @param string|StringableInterface $description
     * @return $this
     */
    public function setDescription(string|StringableInterface $description) : self;

    /**
     * Applies the rule. This must be called after {@see self::preValidate()}.
     *
     * > Note: This is done only once, subsequent calls will have no effect.
     *
     * @return $this
     */
    public function apply() : self;
    public function isValid() : bool;

    /**
     * NOTE: Rules are required by default.
     * @return bool
     */
    public function isRequired() : bool;
    public function setRequired(bool $required) : self;
    public function getValidationResults() : ValidationResults;

    /**
     * Runs all preparations that the rule needs to do before validation.
     * This should be called for all rules before any validation is done.
     *
     * @return void
     */
    public function preValidate() : void;
    public function renderDocumentation(UI $ui) : string;

    /**
     * @return APIParameterInterface[]
     */
    public function getParams() : array;
}
