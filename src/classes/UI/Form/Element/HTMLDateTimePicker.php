<?php
/**
 * File containing the {@see HTML_QuickForm2_Element_HTMLDateTimePicker} class.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @see HTML_QuickForm2_Element_HTMLDateTimePicker
 */

use AppUtils\ClassHelper;

/**
 * EXPERIMENTAL! Element that is used to handle generate HTML input with type date and time together.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @author Emre Celebi <emre.celebi@ionos.com>
 */
class HTML_QuickForm2_Element_HTMLDateTimePicker extends HTML_QuickForm2_Container_Group
{
    public const ELEMENT_NAME_DATE = 'date';
    public const ELEMENT_NAME_TIME = 'time';
    public const CSS_FILE_NAME = 'forms/date-picker.css';

    private HTML_QuickForm2_Element_HTMLDatePicker $datePicker;

    private HTML_QuickForm2_Element_HTMLTimePicker $timePicker;
    private bool $timeOptional = false;

    protected function initNode(): void
    {
        $this->setAttribute('rel', getClassTypeName(UI_Form_Renderer_RenderType_SelfRenderingGroup::class));

        UI::getInstance()->addStylesheet(self::CSS_FILE_NAME);

        $this->datePicker = ClassHelper::requireObjectInstanceOf(
            HTML_QuickForm2_Element_HTMLDatePicker::class,
            $this->addElement('HTMLDatePicker', self::ELEMENT_NAME_DATE)
        );

        $this->timePicker = ClassHelper::requireObjectInstanceOf(
            HTML_QuickForm2_Element_HTMLTimePicker::class,
            $this->addElement('HTMLTimePicker', self::ELEMENT_NAME_TIME)
        );

        UI_Form::setElementLabelID($this, $this->datePicker->getId());
    }

    public function getType() : string
    {
        return 'datetime';
    }

    /**
     * @param string|DateTime|array|NULL $value A date string in the format <code>Y-m-d H:i</code>,
     *          a <code>DateTime</code> instance, or an array with keys
     *          <code>date</code> and <code>time</code>.
     * @return $this
     */
    public function setValue($value) : self
    {
        if(is_array($value))
        {
            parent::setValue($value);
            return $this;
        }

        $date = array(
            'date' => null,
            'time' => null
        );

        if($value instanceof DateTime)
        {
            $date['date'] = $value->format('Y-m-d');
            $date['time'] = $value->format('H:i');
        }
        else if(is_string($value))
        {
            $parsed = self::parseDateTimeString($value, $this->timeOptional);

            if($parsed !== null) {
                $date = $parsed;
            }
        }

        parent::setValue(array(
            self::ELEMENT_NAME_DATE => $date['date'],
            self::ELEMENT_NAME_TIME => $date['time']
        ));

        return $this;
    }

    protected function updateValue(): void
    {
        // Update from a string if needed
        $ds = $this->getActiveDataSource();
        if($ds !== null) {
            $this->updateValueFromString($ds);
        }

        parent::updateValue();
    }

    /**
     * Special case: The element's value has been specified
     * as a string instead of an array with child element
     * values. In this case, we need to parse the string
     * and set the child element values accordingly.
     *
     * This is to make it possible for anyone using the
     * element to work solely with date strings, while the
     * element internally continues using arrays.
     *
     * @param HTML_QuickForm2_DataSource $ds
     * @return void
     */
    private function updateValueFromString(HTML_QuickForm2_DataSource $ds) : void
    {
        $value = $ds->getValue($this->getName());

        if(empty($value) || !is_string($value)) {
            return;
        }

        $parsed = self::parseDateTimeString($value, $this->timeOptional);
        if($parsed === null) {
            return;
        }

        $this->datePicker->setValue($parsed['date']);
        $this->timePicker->setValue($parsed['time']);
    }

    private function getActiveDataSource() : ?HTML_QuickForm2_DataSource
    {
        $name = $this->getName();

        foreach ($this->getDataSources() as $ds) {
            if (
                (
                    !$ds instanceof HTML_QuickForm2_DataSource_Submit
                    &&
                    $ds->getValue($name) !== null
                )
                ||
                (
                    $ds instanceof HTML_QuickForm2_DataSource_NullAware
                    &&
                    $ds->hasValue($name)
                )
            ) {
                return $ds;
            }
        }

        return null;
    }

    public function getDateElement() : HTML_QuickForm2_Element_HTMLDatePicker
    {
        return $this->datePicker;
    }

    public function getTimeElement() : HTML_QuickForm2_Element_HTMLTimePicker
    {
        return $this->timePicker;
    }

    public static function parseDateTimeString(string $string, bool $timeOptional) : ?array
    {
        preg_match('/'.HTML_QuickForm2_Element_HTMLDatePicker::REGEX_GROUP_DATE.' '.HTML_QuickForm2_Element_HTMLTimePicker::REGEX_GROUP_TIME.'/', $string, $matches);

        if(empty($matches)) {
            if(!$timeOptional) {
                return null;
            }

            preg_match('/'.HTML_QuickForm2_Element_HTMLDatePicker::REGEX_GROUP_DATE.'/', $string, $matches);
            if(empty($matches)) {
                return null;
            }

            return array(
                'date' => $matches[1],
                'time' => null,
            );
        }

        return array(
            'date' => $matches[1],
            'time' => $matches[2].':'.$matches[3],
        );
    }

    public function getDate() : ?DateTime
    {
        $date = $this->getDateString();
        if($date !== null) {
            return new DateTime($date);
        }

        return null;
    }

    public function getDateString() : ?string
    {
        $date = $this->datePicker->getValue();
        $time = $this->timePicker->getValue();

        if(!empty($date) && !empty($time)) {
            return $date.' '.$time;
        }

        if(!empty($date)) {
            return $date;
        }

        return null;
    }

    public function getValue() : ?string
    {
        return $this->getDateString();
    }

    /**
     * @return string
     * @see UI_Form_Renderer_RenderType_SelfRenderingGroup Rendered like a regular element.
     */
    public function __toString()
    {
        return sprintf(
            '%s %s %s',
            $this->datePicker,
            $this->timePicker,
            UI::button('')
                ->setIcon(UI::icon()->deleteSign())
                ->setTooltip(t('Clear the date'))
                ->click(sprintf(
                    "$('#%s').val('');$('#%s').val('');",
                    $this->datePicker->getId(),
                    $this->timePicker->getId()
                ))
        );
    }

    public function setTimeOptional(bool $optional=true) : self
    {
        $this->timeOptional = $optional;
        return $this;
    }

    protected function validate(): bool
    {
        $date = $this->datePicker->getDate();
        $time = $this->timePicker->getTime();

        // Handled by the required rule
        if($date === null && $time === null) {
            return true;
        }

        if($this->timeOptional && $date !== null && $time === null) {
            return true;
        }

        if(($date !== null || $time !== null) && ($date === null || $time === null))
        {
            $this->setError(t('Please enter a date and a time.'));
            return false;
        }

        return parent::validate();
    }
}
