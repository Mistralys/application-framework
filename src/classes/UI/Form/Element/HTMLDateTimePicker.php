<?php
/**
 * File containing the {@see HTML_QuickForm2_Element_HTMLDateTimePicker} class.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @see HTML_QuickForm2_Element_HTMLDateTimePicker
 */

/**
 * EXPERIMENTAL! Element that is used to handle generate HTML input with type date and time together.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @author Emre Celebi <emre.celebi@ionos.com>
 */
class HTML_QuickForm2_Element_HTMLDateTimePicker extends HTML_QuickForm2_Element
{
    private HTML_QuickForm2_Element_HTMLDatePicker $htmlDatePicker;

    private HTML_QuickForm2_Element_HTMLTimePicker $htmlTimePicker;

    protected array $watchedAttributes = array('id', 'name', 'type');

    public function __construct($name = null, $attributes = null, array $data = array())
    {
        parent::__construct($name, $attributes, $data);

        $this->htmlDatePicker = new HTML_QuickForm2_Element_HTMLDatePicker($name.'_date', $attributes, $data);
        $this->htmlTimePicker = new HTML_QuickForm2_Element_HTMLTimePicker($name.'_time', $attributes, $data);
    }

    protected function onAttributeChange(string $name, $value = null) : void
    {
        if ('type' === $name)
        {
            throw new HTML_QuickForm2_InvalidArgumentException(
                "Attribute 'type' is read-only"
            );
        }
        parent::onAttributeChange($name, $value);
    }

    public function getType() : string
    {
        return 'datetime-local';
    }

    public function setValue($value) : self
    {
        $this->setAttribute('value', (string)$value);
        return $this;
    }

    public function getRawValue()
    {
        return $this->getAttribute('disabled') ? null : $this->getAttribute('value');
    }

    public function __toString()
    {
        $dateTimeHTML = $this->htmlDatePicker->__toString().'&nbsp'.$this->htmlTimePicker->__toString();
        return $dateTimeHTML;
    }
}
