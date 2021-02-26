<?php
/**
 * File containing the {@link UI_Form_Validator_Date} class.
 *
 * @package Application
 * @subpackage Forms
 * @see UI_Form_Validator_Date
 */

use AppUtils\ConvertHelper;

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
class UI_Form_Validator_ISODate extends UI_Form_Validator
{
    protected function checkConfig() : void
    {
    }

    public function getDataType(): string
    {
        return 'iso-date';
    }

    public function getDefaultValue(): string
    {
        return '';
    }

    protected function applyFilters($value): string
    {
        $value = trim(strval($value));
        $value = str_replace('/', '-', $value);

        return $value;
    }

    protected function _validate(): bool
    {
        if(!preg_match('/\A(?:[0-9]{4}-[0-9]{2}-[0-9]{2})\z/', $this->value))
        {
            return $this->makeError(t('Please enter a valid date.'));
        }

        $parts = explode('-', $this->value);

        $month = intval($parts[1]);
        $year = intval($parts[0]);
        $day = intval($parts[2]);

        if($month < 1 || $month > 12)
        {
            return $this->makeError(t('There is no month with the number %1$s.', $month));
        }

        if($day < 1 || $day > 31)
        {
            return $this->makeError(t('There is no day with the number %1$s.', $day));
        }

        if(!checkdate($month, $day, $year))
        {
            return $this->makeError((string)sb()
                ->t(
                    'The day %1$s does not exist in %2$s of %3$s.',
                    $day,
                    ConvertHelper::month2string($month),
                    $year
                )
            );
        }

        $date = new DateTime($this->value);

        $year = intval($date->format('Y'));
        $current = intval(date('Y'));
        $min = $current - 100;
        $max = $current + 100;

        if($year < $min || $year > $max)
        {
            return $this->makeError(t(
                'The year is out of bounds (%1$s to %2$s).',
                $min,
                $max
            ));
        }

        return true;
    }
}
