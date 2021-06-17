<?php
/**
 * File containing the {@link HTML_QuickForm2_Element_HTMLDatePicker} class.
 *
 * @category HTML
 * @package  HTML_QuickForm2
 */

/**
 * Element that is used to handle generate HTML input with type date.
 * All browsers(except IE) will open calendar as input
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Emre Celebi <emre.celebi@ionos.com>
 */
class HTML_QuickForm2_Element_HTMLDatePicker extends HTML_QuickForm2_Element
{
    /**
     * 'type' attribute should not be changeable
     * @var array
     */
    protected $watchedAttributes = array('id', 'name', 'type');

    public function __construct($name = null, $attributes = null, array $data = array())
    {
        parent::__construct($name, $attributes, $data);
    }

    protected function onAttributeChange($name, $value = null)
    {
        if ('type' == $name)
        {
            throw new HTML_QuickForm2_InvalidArgumentException(
                "Attribute 'type' is read-only"
            );
        }
        parent::onAttributeChange($name, $value);
    }

    public function getType()
    {
        return 'datetime-local';
    }

    public function setValue($value)
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
        return '<input type="date"' . $this->getAttributes(true) . ' />';
    }
}

?>