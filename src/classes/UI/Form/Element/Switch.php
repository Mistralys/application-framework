<?php
/**
 * File containing the {@link HTML_QuickForm2_Element_Switch} class.
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @custom
 */

/**
 * Twitter Bootstrap-based switch element that acts like a checkbox.
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @custom
 */
class HTML_QuickForm2_Element_Switch extends HTML_QuickForm2_Element_Input
{
    protected $attributes = array('type' => 'checkbox');

    protected $onLabel;

    protected $offLabel;
    
    protected $onValue = 'true';
    
    protected $offValue = 'false';

    /**
     * @var UI
     */
    protected $ui;

    public function __construct($name = null, $attributes = null, array $data = array())
    {
        parent::__construct($name, $attributes, $data);

        $this->ui = UI::getInstance();

        $this->onLabel = t('On');
        $this->offLabel = t('Off');

        $this->ui->addJavascript('forms/switch.js');
    }

    /**
     * Sets the label for the ON state of the button
     * @param string $label
     */
    public function setOnLabel($label)
    {
        $this->onLabel = $label;
    }

    /**
     * Sets the label for the OFF state of the button
     * @param string $label
     */
    public function setOffLabel($label)
    {
        $this->offLabel = $label;
    }

    /**
     * Checks if the switch is checked/active.
     * @return boolean
     */
    public function isChecked()
    {
        if ($this->getValue() == $this->onValue) {
            return true;
        }

        return false;
    }

    public function __toString()
    {
        if ($this->frozen) {
            return $this->__toString_frozen();
        }

        $attribs = $this->getAttributes();

        $value = null;
        $onClass = 'btn-default';
        $offClass = 'btn-default';

        if ($this->isChecked()) {
            $onClass = 'btn-success active';
            $value = $this->onValue;
        } else {
            $offClass = 'btn-danger active';
            $value = $this->offValue;
        }
        
        if (isset($this->buttonSize)) {
            $onClass .= ' btn-' . $this->buttonSize;
            $offClass .= ' btn-' . $this->buttonSize;
        }

        $id = $this->resolveID();
        $category = $this->getAttribute('category');
        if (empty($category)) {
            $category = '_uncategorized';
        }

        $this->ui->addJavascriptHeadStatement('switchElement.register', $id, $category);

        $html =
        '<div class="btn-group bootstrap-switch" id="'.$id.'" data-value-on="'.$this->onValue.'" data-value-off="'.$this->offValue.'">' .
            '<button id="' . $id . '-on" class="btn ' . $onClass . '" type="button" onclick="switchElement.turnOn(\'' . $id . '\')">' . $this->onLabel . '</button>' .
            '<input id="' . $id . '-storage" type="hidden" name="' . $attribs['name'] . '" value="' . $value . '"/>' .
            '<button id="' . $id . '-off" class="btn ' . $offClass . '" type="button" onclick="switchElement.turnOff(\'' . $id . '\')">' . $this->offLabel . '</button>' .
        '</div>';

        if (isset($this->onChangeHandler)) {
            $data = $this->onChangeHandler['data'];
            if (empty($data)) {
                $data = 'null';
            }

            $this->ui->addJavascriptOnload('switchElement.onChangeHandler(\'' . $id . '\', ' . $this->onChangeHandler['statement'] . ', ' . $data . ')');
        }

        return $html;
    }

    protected $jsID;

    protected function resolveID()
    {
        if (isset($this->jsID)) {
            return $this->jsID;
        }

        $attribs = $this->getAttributes();

        if (isset($attribs['id'])) {
            $this->jsID = $attribs['id'];
        } else {
            $this->jsID = nextJSID();
        }

        return $this->jsID;
    }

    protected $onChangeHandler;

    public function setOnchangeHandler($statement, $data = null)
    {
        $this->onChangeHandler = array(
            'statement' => $statement,
            'data' => $data
        );
    }

    /**
     * Frozen variant of the element
     * @return string
     */
    protected function __toString_frozen()
    {
        $attribs = $this->getAttributes();

        $id = $this->resolveID();

        if ($this->isChecked()) {
            return
                UI::icon()->yes()->makeSuccess() . ' ' . $this->onLabel .
                '<input id="' . $id . '-storage" type="hidden" name="' . $attribs['name'] . '" value="' . $attribs['value'] . '"/>';
        }

        return
            UI::icon()->no()->makeDangerous() . ' ' . $this->offLabel;
    }

    protected function updateValue()
    {
        $name = $this->getName();
        foreach ($this->getDataSources() as $ds) {
            $value = $ds->getValue($name);
            if (null !== $value || $ds instanceof HTML_QuickForm2_DataSource_Submit) {
                $this->setValue($value);
                return;
            }
        }

        $this->setValue($this->offValue);
    }

    public function setValue($value)
    {
        if (empty($value) || ($value != $this->onValue && $value != $this->offValue)) {
            $value = $this->offValue;
        } 
        
        $this->setAttribute('value', $value);
        
        return $this;
    }

    protected $buttonSize = 'small';

    public function makeSmall()
    {
        $this->buttonSize = 'small';
    }

   /**
    * Makes the switch display "yes" and "no" instead of the
    * default "on" and "off" button labels.
    * 
    * NOTE: does not change the internal values: these stay
    * "true" and "false".
    * 
    * @return HTML_QuickForm2_Element_Switch
    */
    public function makeYesNo()
    {
        $this->setOnLabel(t('Yes'));
        $this->setOffLabel(t('No'));
        return $this;
    }

    public function getValue()
    {
        $val = $this->getRawValue();
        if ($val==$this->onValue) {
            return $this->onValue;
        }

        return $this->offValue;
    }
    
    public function setOnValue($value)
    {
        // convert the currently stored value
        if($this->getAttribute('value')==$this->onValue) {
            $this->setAttribute('value', $value);
        }
        
        $this->onValue = $value;
        
        // and re-update the value, since the update is done
        // before this method is called.
        $this->updateValue();
        return $this;
    }
    
    public function setOffValue($value)
    {
        // convert the currently stored value
        if($this->getAttribute('value')==$this->offValue) {
            $this->setAttribute('value', $value);
        }
        
        $this->offValue = $value;

        // and re-update the value, since the update is done
        // before this method is called.
        $this->updateValue();
        return $this;
    }
    
    public function setValues($onValue, $offValue)
    {
        $this->setOnValue($onValue);
        $this->setOffValue($offValue);
        return $this;
    }
}
