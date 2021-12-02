<?php
/**
 * File containing the {@link HTML_QuickForm2_Element_Switch} class.
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @custom
 */

declare(strict_types=1);

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
    /**
     * @var array<string,mixed>
     */
    protected $attributes = array(
        'type' => 'checkbox'
    );

    /**
     * @var string
     */
    protected $onLabel;

    /**
     * @var string
     */
    protected $offLabel;

    /**
     * @var string
     */
    protected $onValue = 'true';

    /**
     * @var string
     */
    protected $offValue = 'false';

    /**
     * @var UI
     */
    protected $ui;

    /**
     * @var bool
     */
    private $useIcons = false;

    /**
     * @var UI_Icon
     */
    private $onIcon;

    /**
     * @var UI_Icon
     */
    private $offIcon;

    /**
     * @var string
     */
    protected $buttonSize;

    public function __construct(string $name, ?array $attributes = null, array $data = array())
    {
        parent::__construct($name, $attributes, $data);

        $this->ui = UI::getInstance();

        $this->makeOnOff();
        $this->makeSmall();

        $this->ui->addJavascript('forms/switch.js');
    }

    /**
     * Sets the label for the ON state of the button
     * @param string $label
     * @return HTML_QuickForm2_Element_Switch
     */
    public function setOnLabel(string $label) : HTML_QuickForm2_Element_Switch
    {
        $this->onLabel = $label;
        return $this;
    }

    /**
     * Sets the label for the OFF state of the button
     * @param string $label
     * @return HTML_QuickForm2_Element_Switch
     */
    public function setOffLabel(string $label) : HTML_QuickForm2_Element_Switch
    {
        $this->offLabel = $label;
        return $this;
    }

    /**
     * Checks if the switch is checked/active.
     * @return boolean
     */
    public function isChecked() : bool
    {
        return $this->getValue() === $this->onValue;
    }

    public function __toString()
    {
        if ($this->frozen) {
            return $this->_toString_frozen();
        }

        $id = $this->resolveID();

        $this->injectJS($id);

        $btnON = UI::button($this->onLabel)
            ->setID($id.'-on')
            ->makeSize($this->buttonSize)
            ->click(sprintf(
                "switchElement.turnOn('%s')",
                $id
            ));

        $btnOFF = UI::button($this->offLabel)
            ->setID($id.'-off')
            ->makeSize($this->buttonSize)
            ->click(sprintf(
                "switchElement.turnOff('%s')",
                $id
            ));

        if ($this->isChecked())
        {
            $btnON->makeActive();
            $btnON->makeSuccess();

            $value = $this->onValue;
        }
        else
        {
            $btnOFF->makeActive();
            $btnOFF->makeDangerous();
            $value = $this->offValue;
        }

        if($this->useIcons)
        {
            $btnON->setIcon($this->onIcon);
            $btnOFF->setIcon($this->offIcon);
        }

        $group = $this->ui->createButtonGroup()
            ->setID($id)
            ->addClass('bootstrap-switch')
            ->addButton($btnON)
            ->addButton($btnOFF)
            ->setAttribute('data-value-on', $this->onValue)
            ->setAttribute('data-value-off', $this->offValue);

        return
            $group->render().
            '<input id="' . $id . '-storage" type="hidden" name="' . $this->getName() . '" value="' . $value . '"/>';
    }

    private function resolveCategory() : string
    {
        $category = (string)$this->getAttribute('category');

        if ($category !== '')
        {
            return $category;
        }

        return '_uncategorized';
    }

    /**
     * @var string
     */
    protected $jsID;

    protected function resolveID() : string
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

    /**
     * @var array{statement:string,data:string}|NULL
     */
    protected $onChangeHandler;

    /**
     * Sets the clientside javascript statement to execute when
     * the switch value changes.
     *
     * Example:
     *
     * <pre>
     * $switch->setOnchangeHandler(
     *     'SomeClass.MethodName()',
     *     '"string"'
     * );
     * </pre>
     *
     * @param string $statement
     * @param string|null $data A javascript compatible value as a string.
     * @return $this
     */
    public function setOnchangeHandler(string $statement, ?string $data = null) : HTML_QuickForm2_Element_Switch
    {
        if(empty($data))
        {
            $data = 'null';
        }

        $this->onChangeHandler = array(
            'statement' => $statement,
            'data' => $data
        );

        return $this;
    }

    /**
     * Frozen variant of the element
     * @return string
     */
    protected function _toString_frozen() : string
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

    protected function updateValue() : HTML_QuickForm2_Element_Switch
    {
        $name = $this->getName();

        foreach ($this->getDataSources() as $ds)
        {
            $value = $ds->getValue($name);

            if ($value !== null || $ds instanceof HTML_QuickForm2_DataSource_Submit)
            {
                return $this->setValue($value);
            }
        }

        return $this->setValue($this->offValue);
    }

    public function setValue($value) : HTML_QuickForm2_Element_Switch
    {
        if (empty($value) || ($value !== $this->onValue && $value !== $this->offValue))
        {
            $value = $this->offValue;
        } 
        
        $this->setAttribute('value', $value);
        
        return $this;
    }

    public function makeLarge() : HTML_QuickForm2_Element_Switch
    {
        return $this->makeSize(UI_Button::SIZE_LARGE);
    }

    public function makeMini() : HTML_QuickForm2_Element_Switch
    {
        return $this->makeSize(UI_Button::SIZE_MINI);
    }

    public function makeSmall() : HTML_QuickForm2_Element_Switch
    {
        return $this->makeSize(UI_Button::SIZE_SMALL);
    }

    public function makeSize(string $size) : HTML_QuickForm2_Element_Switch
    {
        UI_Button::requireValidSize($size);

        $this->buttonSize = $size;

        return $this;
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
    public function makeYesNo() : HTML_QuickForm2_Element_Switch
    {
        $this->setOnLabel(t('Yes'));
        $this->setOffLabel(t('No'));
        $this->setOnIcon(UI::icon()->yes());
        $this->setOffIcon(UI::icon()->no());

        return $this;
    }

    public function makeEnabledDisabled() : HTML_QuickForm2_Element_Switch
    {
        $this->setOnLabel(t('Enabled'));
        $this->setOffLabel(t('Disabled'));
        $this->setOnIcon(UI::icon()->enabled());
        $this->setOffIcon(UI::icon()->disabled());

        return $this;
    }

    public function makeActiveInactive() : HTML_QuickForm2_Element_Switch
    {
        $this->setOnLabel(t('Active'));
        $this->setOffLabel(t('Inactive'));

        return $this;
    }

    public function makeOnOff() : HTML_QuickForm2_Element_Switch
    {
        $this->setOnLabel(t('On'));
        $this->setOffLabel(t('Off'));
        $this->setOnIcon(UI::icon()->on());
        $this->setOffIcon(UI::icon()->off());

        return $this;
    }

    public function setOnIcon(UI_Icon $icon) : HTML_QuickForm2_Element_Switch
    {
        $this->onIcon = $icon;
        return $this;
    }

    public function setOffIcon(UI_Icon $icon) : HTML_QuickForm2_Element_Switch
    {
        $this->offIcon = $icon;
        return $this;
    }

    public function makeWithIcons(bool $useIcons=true) : HTML_QuickForm2_Element_Switch
    {
        $this->useIcons = $useIcons;
        return $this;
    }

    public function getValue() : string
    {
        $val = (string)$this->getRawValue();

        if ($val === $this->onValue)
        {
            return $this->onValue;
        }

        return $this->offValue;
    }
    
    public function setOnValue(string $value) : HTML_QuickForm2_Element_Switch
    {
        // convert the currently stored value
        if($this->getAttribute('value') === $this->onValue)
        {
            $this->setAttribute('value', $value);
        }
        
        $this->onValue = $value;
        
        // and re-update the value, since the update is done
        // before this method is called.
        $this->updateValue();
        return $this;
    }
    
    public function setOffValue(string $value) : HTML_QuickForm2_Element_Switch
    {
        // convert the currently stored value
        if($this->getAttribute('value') === $this->offValue)
        {
            $this->setAttribute('value', $value);
        }
        
        $this->offValue = $value;

        // and re-update the value, since the update is done
        // before this method is called.
        $this->updateValue();
        return $this;
    }
    
    public function setValues(string $onValue, string $offValue) : HTML_QuickForm2_Element_Switch
    {
        $this->setOnValue($onValue);
        $this->setOffValue($offValue);

        return $this;
    }

    /**
     * @param string $id
     */
    private function injectJS(string $id) : void
    {
        $this->ui->addJavascriptHeadStatement(
            'switchElement.register',
            $id,
            $this->resolveCategory()
        );

        if (isset($this->onChangeHandler))
        {
            $this->ui->addJavascriptOnload(sprintf(
                "switchElement.onChangeHandler('%s', '%s', %s)",
                $id,
                $this->onChangeHandler['statement'],
                $this->onChangeHandler['data']
            ));
        }
    }
}
