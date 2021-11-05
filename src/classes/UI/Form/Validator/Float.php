<?php
/**
 * File containing the {@link UI_Form_Validator_Float} class.
 *
 * @package Application
 * @subpackage Forms
 * @see UI_Form_Validator_Float
 */

declare(strict_types=1);

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
 * @see UI_Form::addRuleFloat()
 */
class UI_Form_Validator_Float extends UI_Form_Validator
{
    public const ERROR_INVALID_FLOAT_CONFIGURATION = 74801;

    /**
     * @var float
     */
    protected $min = 0;

    /**
     * @var float
     */
    protected $max = 0;

    public function __construct(UI_Form $form, HTML_QuickForm2_Element $element, float $min=0, float $max=0)
    {
        parent::__construct($form, $element);

        $this->min = $min;
        $this->max = $max;

        $this->element->setAttribute('data-min', (string)$this->min);
        $this->element->setAttribute('data-max', (string)$this->max);
    }

    protected function applyFilters($value) : string
    {
        $value = trim(strval($value));
        $value = $this->form->callback_convertComma($value);

        return $value;
    }

    public function getDefaultValue(): string
    {
        return '0';
    }

    protected function checkConfig() : void
    {
        if($this->min >= 0 && $this->min <= $this->max)
        {
            return;
        }

        throw new Application_Exception(
            'Invalid float configuration.',
            sprintf(
                'Values out of bounds: min [%s] max [%s]',
                $this->min,
                $this->max
            ),
            self::ERROR_INVALID_FLOAT_CONFIGURATION
        );
    }

    public function getDataType(): string
    {
        return 'float';
    }

    protected function _validate() : bool
    {
        if(!preg_match(AppUtils\RegexHelper::REGEX_FLOAT, $this->value))
        {
            return $this->makeError(t('Must be a valid floating point value.'));
        }

        $number = floatval($this->value);

        if($number < $this->min)
        {
            return $this->makeError(t('The minimum value is %1$s.', $this->min));
        }

        if($this->max > 0 && $number > $this->max)
        {
            return $this->makeError(t('The maximum value is %1$s.', $this->max));
        }

        return true;
    }
}