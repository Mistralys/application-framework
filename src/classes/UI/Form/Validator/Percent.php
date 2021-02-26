<?php
/**
 * File containing the {@link UI_Form_Validator_Percent} class.
 *
 * @package Application
 * @subpackage Forms
 * @see UI_Form_Validator_Percent
 */

declare(strict_types=1);

/**
 * Specialized validator class used for percentage elements:
 * validating the value according to the min/max settings.
 *
 * @package Application
 * @subpackage Forms
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see UI_Form::addRulePercent()
 */
class UI_Form_Validator_Percent extends UI_Form_Validator_Float
{
    public function getDataType(): string
    {
        return 'percent';
    }
}
