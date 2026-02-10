<?php

use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper_Exception;

class UI_PropertiesGrid_Property_Boolean extends UI_PropertiesGrid_Property
{
    const string TYPE_TRUEFALSE = 'truefalse';
    const string TYPE_YESNO = 'yesno';
    const string TYPE_ENABLEDDISABLED = 'enableddisabled';
    const string TYPE_ACTIVEINACTIVE = 'activeinactive';
    
    const string COLORS_DEFAULT = 'default';
    const string COLORS_NEUTRAL = 'neutral';

    /**
     * @var string
     */
    protected $labelTrue;

    /**
     * @var string
     */
    protected $labelFalse;

    /**
     * @var string
     */
    protected $type = self::TYPE_TRUEFALSE;

    /**
     * @var string
     */
    protected $colors = self::COLORS_DEFAULT;
    
    protected function init() : void
    {
        $this->setLabels(t('True'), t('False'));
    }

    /**
     * @param mixed $value
     * @return UI_StringBuilder
     * @throws Application_Exception
     * @throws ConvertHelper_Exception
     */
    protected function filterValue($value) : UI_StringBuilder
    {
        $bool = ConvertHelper::string2bool($value);
        
        $result = sb();
        
        $label = UI::label('');
        
        if($bool) 
        {
            $label
            ->setLabel($this->labelTrue)
            ->setIcon(UI::icon()->ok())
            ->makeSuccess();
        } 
        else 
        {
            $label
            ->setLabel($this->labelFalse)
            ->setIcon(UI::icon()->disabled())
            ->makeDangerous();
            
            switch($this->type) 
            {
                case self::TYPE_ACTIVEINACTIVE:
                case self::TYPE_ENABLEDDISABLED:
                    $label->makeInactive();
                    break;
            }
        }
        
        if($this->colors === self::COLORS_NEUTRAL)
        {
            $label->makeInactive();
        }
        
        return sb()->add($label);
    }
    
    public function makeColorsNeutral() : UI_PropertiesGrid_Property_Boolean
    {
        $this->colors = self::COLORS_NEUTRAL;
        return $this;
    }

    /**
     * @return $this
     */
    public function makeYesNo()
    {
        $this->type = self::TYPE_YESNO;
        return $this->setLabels(t('Yes'), t('No'));
    }

    /**
     * @return $this
     */
    public function makeEnabledDisabled()
    {
        $this->type = self::TYPE_ENABLEDDISABLED;
        return $this->setLabels(t('Enabled'), t('Disabled'));
    }

    /**
     * @return $this
     */
    public function makeActiveInactive()
    {
        $this->type = self::TYPE_ACTIVEINACTIVE;
        return $this->setLabels(t('Active'), t('Inactive'));
    }

    /**
     * @param string $labelTrue
     * @param string $labelFalse
     * @return $this
     */
    public function setLabels(string $labelTrue, string $labelFalse)
    {
        $this->labelTrue = $labelTrue;
        $this->labelFalse = $labelFalse;
        return $this;
    }
}
