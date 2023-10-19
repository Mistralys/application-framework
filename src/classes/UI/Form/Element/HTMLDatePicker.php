<?php
/**
 * File containing the {@see HTML_QuickForm2_Element_HTMLDatePicker} class.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @see HTML_QuickForm2_Element_HTMLDatePicker
 */

/**
 * Element that is used to handle generate HTML input with type date.
 * All browsers (except IE) will open calendar as input
 *
 * @package User Interface
 * @subpackage Form Elements
 * @author Emre Celebi <emre.celebi@ionos.com>
 */
class HTML_QuickForm2_Element_HTMLDatePicker extends HTML_QuickForm2_Element_Input
{
    public const REGEX_GROUP_DATE = '([0-9]{4}-[0-9]{2}-[0-9]{2})';
    public const ERROR_INVALID_DATE_VALUE = 145801;

    protected array $attributes = array(
        'type' => 'date'
    );

    protected function initNode(): void
    {
        parent::initNode();

        UI::getInstance()->addStylesheet(HTML_QuickForm2_Element_HTMLDateTimePicker::CSS_FILE_NAME);

        $this->addClass('date-picker');
    }

    public function getType() : string
    {
        return 'date';
    }

    public static function isValidDateString(string $date): bool
    {
        return preg_match('/'.self::REGEX_GROUP_DATE.'/', $date);
    }

    /**
     * @return DateTime|null
     * @throws UI_Exception {@see self::ERROR_INVALID_DATE_VALUE}
     */
    public function getDate() : ?DateTime
    {
        $date = $this->getValue();

        if(empty($date) || !self::isValidDateString($date))
        {
            return null;
        }

        try
        {
            return new DateTime($date);
        }
        catch (Exception $e)
        {
            throw new UI_Exception(
                'Invalid date value.',
                sprintf(
                    'The date value [%s] is not a valid date.',
                    $date
                ),
                self::ERROR_INVALID_DATE_VALUE,
                $e
            );
        }
    }

    public function getYear() : ?int
    {
        $date = $this->getDate();

        if($date !== null) {
            return (int)$date->format('Y');
        }

        return null;
    }

    public function getMonth() : ?int
    {
        $date = $this->getDate();

        if($date !== null) {
            return (int)$date->format('m');
        }

        return null;
    }

    public function getDay() : ?int
    {
        $date = $this->getDate();

        if($date !== null) {
            return (int)$date->format('d');
        }

        return null;
    }

    protected function validate(): bool
    {
        $value = $this->getValue();

        if(empty($value)) {
            return parent::validate();
        }

        if(!self::isValidDateString((string)$value)) {
            $this->setError((string)sb()
                ->t('Not a valid date.')
                ->t('Expected the format %1$s.', t('YYYY-MM-DD'))
            );
            return false;
        }

        try
        {
            new DateTime($value);
            return true;
        }
        catch (Exception $e)
        {
            $this->setError(t('The date format is correct, but the date does not exist.'));
            return false;
        }
    }

    /**
     * @param string|DateTime|NULL $value
     * @return $this
     */
    public function setValue($value): self
    {
        if($value instanceof DateTime) {
            $value = $value->format('Y-m-d');
        }

        parent::setValue($value);

        return $this;
    }
}
