<?php
/**
 * File containing the {@see HTML_QuickForm2_Element_Switch} class.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @see HTML_QuickForm2_Element_Switch
 */

declare(strict_types=1);

use UI\Interfaces\ButtonSizeInterface;
use UI\Traits\ButtonSizeTrait;

/**
 * Twitter Bootstrap-based switch element that acts like a checkbox.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class HTML_QuickForm2_Element_Switch
    extends HTML_QuickForm2_Element_Input
    implements ButtonSizeInterface
{
    use ButtonSizeTrait;

    public const ELEMENT_TYPE = 'switch';

    /**
     * @var array<string,string>
     */
    protected array $attributes = array(
        'type' => 'checkbox'
    );

    protected string $onLabel = '';
    protected string $offLabel = '';
    protected string $onValue = 'true';
    protected string $offValue = 'false';
    protected UI $ui;
    private bool $useIcons = false;
    private ?UI_Icon $onIcon = null;
    private ?UI_Icon $offIcon = null;
    protected ?string $jsID = null;

    /**
     * @var array{statement:string,data:string}|NULL
     */
    protected ?array $onChangeHandler = null;

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
     * @return $this
     */
    public function setOnLabel(string $label) : self
    {
        $this->onLabel = $label;
        return $this;
    }

    /**
     * Sets the label for the OFF state of the button
     * @param string $label
     * return $this
     */
    public function setOffLabel(string $label) : self
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

        UI_Form::setElementLabelID($this, $id.'-on');

        $this->injectJS($id);

        $btnON = UI::button($this->onLabel)
            ->setID($id.'-on')
            ->makeSize((string)$this->buttonSize)
            ->click(sprintf(
                "switchElement.turnOn('%s')",
                $id
            ));

        $btnOFF = UI::button($this->offLabel)
            ->setID($id.'-off')
            ->makeSize((string)$this->buttonSize)
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
            sprintf(
                '<input id="%s-storage" type="hidden" name="%s" value="%s"/>',
                $id,
                $this->getName(),
                $value
            );
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
     * Sets the clientside javascript statement to execute when
     * the switch value changes.
     *
     * Example:
     *
     * ```php
     * $switch->setOnchangeHandler(
     *     'SomeClass.MethodName()',
     *     '"string"'
     * );
     * ```
     *
     * @param string $statement
     * @param string|null $data A javascript compatible value as a string.
     * @return $this
     */
    public function setOnchangeHandler(string $statement, ?string $data = null) : self
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

    protected function updateValue() : void
    {
        $name = $this->getName();

        foreach ($this->getDataSources() as $ds)
        {
            $value = $ds->getValue($name);

            if ($value !== null || $ds instanceof HTML_QuickForm2_DataSource_Submit)
            {
                $this->setValue($value);
                return;
            }
        }

        $this->setValue($this->offValue);
    }

    public function setValue($value) : self
    {
        if (empty($value) || ($value !== $this->onValue && $value !== $this->offValue))
        {
            $value = $this->offValue;
        } 
        
        $this->setAttribute('value', $value);
        
        return $this;
    }

   /**
    * Makes the switch display "yes" and "no" instead of the
    * default "on" and "off" button labels.
    * 
    * NOTE: does not change the internal values: these stay
    * "true" and "false", unless the $includeValue parameter
    * is set to true.
    *
    * @param bool $includeValue If true, the values will be set to "yes" and "no".
    * @return $this
    */
    public function makeYesNo(bool $includeValue=false) : self
    {
        $this->setOnLabel(t('Yes'));
        $this->setOffLabel(t('No'));
        $this->setOnIcon(UI::icon()->yes());
        $this->setOffIcon(UI::icon()->no());

        if($includeValue) {
            $this->setOnValue('yes');
            $this->setOffValue('no');
        }

        return $this;
    }

    public function makeEnabledDisabled() : self
    {
        $this->setOnLabel(t('Enabled'));
        $this->setOffLabel(t('Disabled'));
        $this->setOnIcon(UI::icon()->enabled());
        $this->setOffIcon(UI::icon()->disabled());

        return $this;
    }

    public function makeActiveInactive() : self
    {
        $this->setOnLabel(t('Active'));
        $this->setOffLabel(t('Inactive'));

        return $this;
    }

    public function makeOnOff() : self
    {
        $this->setOnLabel(t('On'));
        $this->setOffLabel(t('Off'));
        $this->setOnIcon(UI::icon()->on());
        $this->setOffIcon(UI::icon()->off());

        return $this;
    }

    public function setOnIcon(UI_Icon $icon) : self
    {
        $this->onIcon = $icon;
        return $this;
    }

    public function setOffIcon(UI_Icon $icon) : self
    {
        $this->offIcon = $icon;
        return $this;
    }

    public function makeWithIcons(bool $useIcons=true) : self
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
    
    public function setOnValue(string $value) : self
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
    
    public function setOffValue(string $value) : self
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
    
    public function setValues(string $onValue, string $offValue) : self
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
