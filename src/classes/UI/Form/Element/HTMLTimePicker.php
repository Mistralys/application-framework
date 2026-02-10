<?php
/**
 * File containing the {@see HTML_QuickForm2_Element_HTMLTimePicker} class.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @see HTML_QuickForm2_Element_HTMLTimePicker
 */

use Application\UI\Form\Element\DateTimePicker\BasicTime;

/**
 * Element that is used to handle generate HTML input with type time.
 * All browsers(except IE) will open time selection menu as input
 *
 * @package User Interface
 * @subpackage Form Elements
 * @author Emre Celebi <emre.celebi@ionos.com>
 */
class HTML_QuickForm2_Element_HTMLTimePicker extends HTML_QuickForm2_Element_Input
{
    public const string REGEX_GROUP_TIME = '([0-9]{2}):([0-9]{2})';

    protected array $attributes = array(
        'type' => 'time'
    );

    public function getType() : string
    {
        return 'time';
    }

    protected function initNode(): void
    {
        parent::initNode();

        UI::getInstance()->addStylesheet(HTML_QuickForm2_Element_HTMLDateTimePicker::CSS_FILE_NAME);

        $this->addClass('time-picker');
    }

    public function getTime() : ?BasicTime
    {
        $parsed = self::parseTimeString($this->getValue());

        if($parsed !== null) {
            return new BasicTime($parsed['hour'], $parsed['time']);
        }

        return null;
    }

    public function getHour() : ?int
    {
        $time = $this->getTime();
        if($time !== null) {
            return $time->getHour();
        }

        return null;
    }

    public function getMinutes() : ?int
    {
        $time = $this->getTime();
        if($time !== null) {
            return $time->getMinutes();
        }

        return null;
    }

    public static function parseTimeString(string $string) : ?array
    {
        $string = trim($string);

        preg_match('/'.self::REGEX_GROUP_TIME.'/', $string, $matches);

        if(empty($matches)) {
            return null;
        }

        return array(
            'hour' => (int)$matches[1],
            'time' => (int)$matches[2],
        );
    }

    public function getValue() : string
    {
        return (string)parent::getValue();
    }

    protected function validate(): bool
    {
        $value = $this->getValue();

        if(empty($value)) {
            return parent::validate();
        }

        $time = self::parseTimeString($value);
        if($time === null) {
            $this->setError((string)sb()
                ->t('Not a valid time.')
                ->t('Expected the format %1$s.', t('HH:MM'))
            );
            return false;
        }

        try
        {
            new BasicTime($time['hour'], $time['time']);
            return true;
        }
        catch (UI_Exception $e)
        {
            $this->setError(t('The time format is correct, but the time does not exist.'));
            return false;
        }
    }

    /**
     * @param string|DateTime|BasicTime|NULL $value
     * @return $this
     */
    public function setValue($value) : self
    {
        if($value instanceof BasicTime)
        {
            $value = $value->getAsString();
        }
        else if($value instanceof DateTime)
        {
            $value = $value->format('H:i');
        }

        parent::setValue($value);

        return $this;
    }
}
