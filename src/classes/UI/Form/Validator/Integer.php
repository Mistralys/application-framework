<?php
/**
 * File containing the {@link UI_Form_Validator_Integer} class.
 * @package Application
 * @subpackage Forms
 * @see UI_Form_Validator_Integer
 */

declare(strict_types=1);

use AppUtils\ClassHelper\BaseClassHelperException;
use AppUtils\RegexHelper;

/**
 * Specialized validator class used for integer form elements:
 * used to validate values according to the format requirements
 * as well as minimum/maximum values if any.
 * 
 * Also sets the data type so the type hints are displayed in
 * the UI for the field type.
 * 
 * @package Application
 * @subpackage Forms
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see UI_Form::addRuleInteger()
 */
class UI_Form_Validator_Integer extends UI_Form_Validator
{
    public const ERROR_INVALID_CONFIGURATION = 74901;

    protected int $min = 0;
    protected int $max = 0;

    /**
     * @param UI_Form $form
     * @param HTML_QuickForm2_Node $element
     * @param int $min
     * @param int $max
     *
     * @throws HTML_QuickForm2_Exception
     * @throws BaseClassHelperException
     */
    public function __construct(UI_Form $form, HTML_QuickForm2_Node $element, int $min=0, int $max=0)
    {
        parent::__construct($form, $element);

        $this->min = $min;
        $this->max = $max;

        $this->element->setAttribute('data-min', (string)$this->min);
        $this->element->setAttribute('data-max', (string)$this->max);
    }

    protected function checkConfig(): void
    {
        if($this->min >= 0 && $this->min <= $this->max)
        {
            return;
        }

        throw new Application_Exception(
            'Invalid integer configuration.',
            sprintf(
                'Values out of bounds: min [%s] max [%s]',
                $this->min,
                $this->max
            ),
            self::ERROR_INVALID_CONFIGURATION
        );
    }

    public function getDataType(): string
    {
        return 'integer';
    }

    public function getDefaultValue(): string
    {
        return '0';
    }

    protected function applyFilters($value) : string
    {
        return trim((string)$value);
    }

    protected function _validate() : bool
    {
        if(!preg_match(RegexHelper::REGEX_INTEGER, $this->value))
        {
            return $this->makeError(t('Not a valid integer value.'));
        }

        $number = (int)$this->value;

        if($number < $this->min)
        {
            return $this->makeError(t('Must be equal to or bigger than %1$s.', $this->min));
        }
        
        if($this->max > 0 && $number > $this->max)
        {
            return $this->makeError(t('Must be equal to or smaller than %1$s.', $this->max));
        }
        
        return true;
    }
}
