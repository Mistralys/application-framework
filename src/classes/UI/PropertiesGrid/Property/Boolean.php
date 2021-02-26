<?php

class UI_PropertiesGrid_Property_Boolean extends UI_PropertiesGrid_Property
{
    const TYPE_TRUEFALSE = 'truefalse';
    
    const TYPE_YESNO = 'yesno';
    
    const TYPE_ENABLEDDISABLED = 'enableddisabled';
    
    const TYPE_ACTIVEINACTIVE = 'activeinactive';
    
    const COLORS_DEFAULT = 'default';
    
    const COLORS_NEUTRAL = 'neutral';
    
    protected $labelTrue;
    
    protected $labelFalse;

    protected $type = self::TYPE_TRUEFALSE;
    
    protected $colors = self::COLORS_DEFAULT;
    
    protected function init()
    {
        $this->setLabels(t('True'), t('False'));
    }
    
    protected function filterValue($value) : UI_StringBuilder
    {
        $bool = \AppUtils\ConvertHelper::string2bool($value);
        
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
    
    public function makeYesNo()
    {
        $this->type = self::TYPE_YESNO;
        return $this->setLabels(t('Yes'), t('No'));
    }
    
    public function makeEnabledDisabled()
    {
        $this->type = self::TYPE_ENABLEDDISABLED;
        return $this->setLabels(t('Enabled'), t('Disabled'));
    }
    
    public function makeActiveInactive()
    {
        $this->type = self::TYPE_ACTIVEINACTIVE;
        return $this->setLabels(t('Active'), t('Inactive'));
    }
    
    public function setLabels($labelTrue, $labelFalse)
    {
        $this->labelTrue = $labelTrue;
        $this->labelFalse = $labelFalse;
        return $this;
    }
}
