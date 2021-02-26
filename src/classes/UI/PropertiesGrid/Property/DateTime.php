<?php

class UI_PropertiesGrid_Property_DateTime extends UI_PropertiesGrid_Property_Regular
{
   /**
    * @var bool
    */
    protected $withTime = false;
    
   /**
    * @var bool
    */
    protected $withDiff = false;
    
    protected function init()
    {
        $this->ifEmpty(sb()->muted('('.t('No date available').')'));
    }
    
    public function withTime() : UI_PropertiesGrid_Property_DateTime
    {
        $this->withTime = true;
        return $this;
    }
    
    public function withDiff() : UI_PropertiesGrid_Property_DateTime
    {
        $this->withDiff = true;
        return $this;
    }
    
    protected function filterValue($value) : UI_StringBuilder
    {
        $result = sb();
        
        if($value instanceof DateTime)
        {
            $result->add($value->format('d.m.Y'));
            
            if($this->withTime)
            {
                $result->add($value->format('H:i:s'));
            }
            
            if($this->withDiff)
            {
                $result->muted(sprintf(
                    '(%s)',
                    \AppUtils\ConvertHelper::duration2string($value)
                ));
            }
        }
        
        return $result;
    }
}
