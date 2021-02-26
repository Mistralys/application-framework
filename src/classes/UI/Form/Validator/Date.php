<?php
/**
 * File containing the {@link UI_Form_Validator_Date} class.
 *
 * @package Application
 * @subpackage Forms
 * @see UI_Form_Validator_Date
 */

/**
 * Specialized validator class used for date input fields. Validates
 * the entry format, as well as the date itself.
 * 
 * Also sets the data type so the type hints are displayed in
 * the UI for the field type.
 * 
 * @package Application
 * @subpackage Forms
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see UI_Form::addRuleDate()
 */
class UI_Form_Validator_Date extends UI_Form_Validator
{
    const ERROR_INVALID_DATE_ELEMENT_TYPE = 553001;
    
    protected function checkConfig() : void
    {
        if($this->element instanceof HTML_QuickForm2_Element_InputText) {
            return;
        }

        throw new Application_Exception(
            'Invalid date element type',
            sprintf(
                'Only text input fields can be validated as a date string, [%s] given.',
                get_class($this->element)
            ),
            self::ERROR_INVALID_DATE_ELEMENT_TYPE
        );
    }

    public function getDataType(): string
    {
        return 'date';
    }

    public function getDefaultValue(): string
    {
        return '';
    }

    protected function applyFilters($value): string
    {
        $value = trim(strval($value));
        $value = str_replace('-', '/', $value);

        return $value;
    }

    protected function _validate(): bool
    {
        $date = $this->form->parseDate($this->value);

        if(!$date)
        {
            return $this->makeError(t('Not a valid date.'));
        }

        return true;
    }
}
